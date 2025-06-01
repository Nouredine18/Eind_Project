<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$sql = "SELECT SUM(co2_emissions) AS total_emissions 
        FROM travelhistory
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Execution failed: " . $stmt->error);
}
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$totalEmissions = $data['total_emissions'] ?? 0;
$stmt->close();

// Bereken totaal gecompenseerd via donaties (bedrag * effectiviteit)
$sql = "SELECT d.amount, cp.effectiveness 
        FROM donations d 
        JOIN compensationprojects cp ON d.project_id = cp.project_id 
        WHERE d.user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Execution failed: " . $stmt->error);
}
$result = $stmt->get_result();

$totalCompensated = 0;
while ($row = $result->fetch_assoc()) {
    $totalCompensated += $row['amount'] * ($row['effectiveness'] / 100);
}
$stmt->close();

// Voeg compensatie via actieve abonnementen toe (vast bedrag * effectiviteit)
$sql = "SELECT cp.effectiveness 
        FROM subscriptions s 
        JOIN compensationprojects cp ON s.project_id = cp.project_id 
        WHERE s.user_id = ? AND s.next_payment_date >= CURDATE()";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
if (!$stmt->execute()) {
    die("Execution failed: " . $stmt->error);
}
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $totalCompensated += 10 * ($row['effectiveness'] / 100); // vast bedrag $10 per maand per abo
}
$stmt->close();

$totalCompensatedKg = number_format($totalCompensated, 2);

$netEmissions = $totalEmissions - $totalCompensated;
$netEmissionsKg = number_format($netEmissions, 2);

// Haal maandelijkse compensatiedata op (donaties EN actieve abo's)
$sql = "SELECT YEAR(d.donation_date) AS year, MONTH(d.donation_date) AS month, 
               SUM(d.amount * (cp.effectiveness / 100)) AS monthly_compensated
        FROM donations d 
        JOIN compensationprojects cp ON d.project_id = cp.project_id 
        WHERE d.user_id = ?
        GROUP BY YEAR(d.donation_date), MONTH(d.donation_date)
        UNION
        SELECT YEAR(s.created_at) AS year, MONTH(s.created_at) AS month, 
               SUM(10 * (cp.effectiveness / 100)) AS monthly_compensated
        FROM subscriptions s 
        JOIN compensationprojects cp ON s.project_id = cp.project_id 
        WHERE s.user_id = ? AND s.next_payment_date >= CURDATE()
        GROUP BY YEAR(s.created_at), MONTH(s.created_at)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("ii", $user_id, $user_id);
if (!$stmt->execute()) {
    die("Execution failed: " . $stmt->error);
}
$result = $stmt->get_result();

$monthlyData = [];
while ($row = $result->fetch_assoc()) {
    $monthlyData[] = [
        'year' => $row['year'],
        'month' => $row['month'],
        'compensated' => $row['monthly_compensated']
    ];
}
$stmt->close();

// Check voor nieuw compensatieniveau & stuur notificatie
$compensationLevels = [100, 200, 500, 1000]; // kg drempels voor notificaties
foreach ($compensationLevels as $level) {
    if ($totalCompensated >= $level && !isset($_SESSION['compensation_level_' . $level])) {
        $message = "Congratulations! You have reached a new compensation level of $level kg.";
        $sql = "INSERT INTO notifications (user_id, notification_type, message, is_read, created_at) VALUES (?, 'compensation_level', ?, 0, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("is", $user_id, $message);
        $stmt->execute();
        $stmt->close();

        // Markeer niveau als genotificeerd in sessie om spam te voorkomen
        $_SESSION['compensation_level_' . $level] = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Compensation Data</title>
    <!-- Include necessary CSS and JS files -->
    <link rel="stylesheet" href="path/to/your/css/file.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include('side_bar_template.php') ?>
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Your CO2 Compensation Overview</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-primary bubble-shadow-small">
                                    <i class="fas fa-leaf"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Total CO2 Emissions</p>
                                    <h4 class="card-title"><?= number_format($totalEmissions, 2) ?> kg</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-success bubble-shadow-small">
                                    <i class="fas fa-tree"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Total Compensated CO2</p>
                                    <h4 class="card-title"><?= $totalCompensatedKg ?> kg</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats card-round">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center icon-danger bubble-shadow-small">
                                    <i class="fas fa-balance-scale"></i>
                                </div>
                            </div>
                            <div class="col col-stats ms-3 ms-sm-0">
                                <div class="numbers">
                                    <p class="card-category">Net CO2 Emissions</p>
                                    <h4 class="card-title"><?= $netEmissionsKg ?> kg</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Monthly Compensation Data</div>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Year</th>
                                    <th>Month</th>
                                    <th>CO2 Compensated (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthlyData as $data) { ?>
                                    <tr>
                                        <td><?= $data['year'] ?></td>
                                        <td><?= $data['month'] ?></td>
                                        <td><?= number_format($data['compensated'], 2) ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Compensated CO2 Emissions Chart</div>
                    </div>
                    <div class="card-body">
                        <canvas id="compensatedCO2Chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('compensatedCO2Chart').getContext('2d');
        const monthlyData = <?= json_encode($monthlyData) ?>;
        const labels = monthlyData.map(data => `${data.year}-${String(data.month).padStart(2, '0')}`);
        const data = monthlyData.map(data => data.compensated);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'CO2 Compensated (kg)',
                    data: data,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'CO2 (kg)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    }
                }
            }
        });
    });
</script>
</body>
</html>