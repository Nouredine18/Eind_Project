<?php
session_start();
include 'connect.php';

// Zorg dat de gebruiker ingelogd is
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Voeg hoofd paginastructuur, sidebar, topbar, en algemene JS/CSS toe
// Dit genereert <html>, <head>, <body>, sidebar, topbar, en opent <div class="main-panel">
include('side_bar_template.php');
?>

<<<<<<< Updated upstream
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <!-- Include necessary CSS and JS files -->
    <link rel="stylesheet" href="path/to/your/css/file.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
</head>
<body>
  <?php include('side_bar_template.php') ?>
    <!-- Your existing HTML content -->

    <div class="container">
        <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                    <h3 class="fw-bold mb-3">Dashboard</h3>
                    <h6 class="op-7 mb-2">Free Bootstrap 5 Admin Dashboard</h6>
                </div>
                <div class="ms-md-auto py-2 py-md-0">
                    <a href="#" class="btn btn-label-info btn-round me-2">Manage</a>
                    <a href="#" class="btn btn-primary btn-round">Add Customer</a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-primary bubble-shadow-small">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <?php
                                // Prepare the SQL query to calculate total CO2 emissions
                                $sql = "SELECT SUM(co2_emissions) AS total_emissions 
                                        FROM travelhistory
                                        WHERE user_id = ?";
                                $stmt = $conn->prepare($sql);
                                if ($stmt === false) {
                                    die("Error preparing statement: " . $conn->error);
                                }
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result->num_rows > 0) {
                                    $data = $result->fetch_assoc();
                                    $totalEmissions = $data['total_emissions'] ?? 0;
                                } else {
                                    $totalEmissions = 0;
                                }
                                $stmt->close();
                                ?>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">CO-2 Emission</p>
                                        <h4 class="card-title"><p>CO2 uitstoot: <?= number_format($totalEmissions, 2) ?> kg</p>
                                        <?php if ($totalEmissions > 1000) { ?>
                                            <p style="color: red;">Overweeg duurzamere reisopties!</p>
                                        <?php } ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-info bubble-shadow-small">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Subscribers</p>
                                        <h4 class="card-title">1303</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-success bubble-shadow-small">
                                        <i class="fas fa-luggage-cart"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Sales</p>
                                        <h4 class="card-title">$ 1,345</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                        <i class="far fa-check-circle"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Order</p>
                                        <h4 class="card-title">576</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-round">
                        <div class="card-header">
                            <div class="card-head-row">
                                <div class="card-title">CO2 Emissions for the Past Year</div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="min-height: 375px">
                                <canvas id="co2EmissionsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    // Fetch CO2 data from the backend
                    fetch('fetch_co2_data.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                console.error(data.error);
                                return;
                            }

                            // Prepare data for the chart
                            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                            const emissionsData = new Array(12).fill(0); // Initialize with 0s for each month

                            data.forEach(item => {
                                emissionsData[item.month - 1] = item.emissions;
                            });

                            // Create the chart
                            const ctx = document.getElementById('co2EmissionsChart').getContext('2d');
                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: months,
                                    datasets: [{
                                        label: 'CO2 Emissions (kg)',
                                        data: emissionsData,
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
                        })
                        .catch(error => console.error('Error fetching CO2 data:', error));
                });
            </script>
=======
<!-- Dit HTML wordt weergegeven binnen de <div class="main-panel"> van side_bar_template.php -->
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Dashboard</h3>
            <h6 class="op-7 mb-2">Welcome to your Ecoligo Dashboard</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <!-- Optionele knoppen:
            <a href="#" class="btn btn-label-info btn-round me-2">Beheren</a>
            <a href="#" class="btn btn-primary btn-round">Klant Toevoegen</a>
            -->
>>>>>>> Stashed changes
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-leaf"></i>
                            </div>
                        </div>
                        <?php
                        // Bereid SQL query voor om totale CO2-uitstoot te berekenen
                        $sql_emissions = "SELECT SUM(co2_emissions) AS total_emissions 
                                FROM travelhistory
                                WHERE user_id = ?";
                        $stmt_emissions = $conn->prepare($sql_emissions);
                        $totalEmissions = 0; 
                        if ($stmt_emissions) {
                            $stmt_emissions->bind_param("i", $user_id);
                            $stmt_emissions->execute();
                            $result_emissions = $stmt_emissions->get_result();
                            if ($result_emissions->num_rows > 0) {
                                $data_emissions = $result_emissions->fetch_assoc();
                                $totalEmissions = $data_emissions['total_emissions'] ?? 0;
                            }
                            $stmt_emissions->close();
                        } else {
                            echo "Error preparing emissions statement: " . $conn->error;
                        }
                        ?>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">CO₂ Emissions</p>
                                <h4 class="card-title"><?= number_format($totalEmissions, 2) ?> kg</h4>
                                <?php if ($totalEmissions > 1000): ?>
                                    <small class="text-danger">Consider sustainable travel!</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <?php
                        // Haal aantal volgers op
                        $sql_followers = "SELECT COUNT(*) as count FROM user_followers WHERE following_id = ?";
                        $stmt_followers = $conn->prepare($sql_followers);
                        $follower_count = 0;
                        if ($stmt_followers) {
                            $stmt_followers->bind_param("i", $user_id);
                            $stmt_followers->execute();
                            $result_followers = $stmt_followers->get_result()->fetch_assoc();
                            $follower_count = $result_followers['count'] ?? 0;
                            $stmt_followers->close();
                        }
                        ?>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Followers</p>
                                <h4 class="card-title"><?php echo $follower_count; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-user-plus"></i>
                            </div>
                        </div>
                         <?php
                        // Haal aantal gevolgden op
                        $sql_following = "SELECT COUNT(*) as count FROM user_followers WHERE follower_id = ?";
                        $stmt_following = $conn->prepare($sql_following);
                        $following_count = 0;
                        if ($stmt_following) {
                            $stmt_following->bind_param("i", $user_id);
                            $stmt_following->execute();
                            $result_following = $stmt_following->get_result()->fetch_assoc();
                            $following_count = $result_following['count'] ?? 0;
                            $stmt_following->close();
                        }
                        ?>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Following</p>
                                <h4 class="card-title"><?php echo $following_count; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <?php
                        // Haal totaal aantal punten op
                        $sql_points = "SELECT total_points FROM users WHERE user_id = ?";
                        $stmt_points = $conn->prepare($sql_points);
                        $user_total_points = 0;
                        if ($stmt_points) {
                            $stmt_points->bind_param("i", $user_id);
                            $stmt_points->execute();
                            $result_points = $stmt_points->get_result()->fetch_assoc();
                            $user_total_points = $result_points['total_points'] ?? 0;
                            $stmt_points->close();
                            $_SESSION['total_points'] = $user_total_points; // Update sessie
                        }
                        ?>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">My Points</p>
                                <h4 class="card-title"><?php echo number_format($user_total_points, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">CO2 Emissions for the Past Year</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 375px">
                        <canvas id="co2EmissionsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End of page-inner -->

<!-- Page-specific JavaScript for home.php -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Haal CO2-data op van de backend
    fetch('fetch_co2_data.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Error from fetch_co2_data.php:', data.error);
                const chartContainer = document.getElementById('co2EmissionsChart').parentElement;
                chartContainer.innerHTML = '<p class="text-danger text-center">Could not load CO2 emissions data.</p>';
                return;
            }

            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const emissionsData = new Array(12).fill(0);

            if (Array.isArray(data)) {
                data.forEach(item => {
                    if (item.month >= 1 && item.month <= 12) {
                         emissionsData[item.month - 1] = parseFloat(item.emissions) || 0;
                    }
                });
            } else {
                console.error('CO2 data is not in the expected array format:', data);
                const chartContainer = document.getElementById('co2EmissionsChart').parentElement;
                chartContainer.innerHTML = '<p class="text-danger text-center">CO2 emissions data format error.</p>';
                return;
            }

            const ctx = document.getElementById('co2EmissionsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'CO2 Emissions (kg)',
                        data: emissionsData,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y.toFixed(2) + ' kg';
                                    }
                                    return label;
                                }
                            }
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
        })
        .catch(error => {
            console.error('Error fetching or processing CO2 data:', error);
            const chartContainer = document.getElementById('co2EmissionsChart').parentElement;
            chartContainer.innerHTML = `<p class="text-danger text-center">Failed to load chart data: ${error.message}</p>`;
        });
});
</script>

<?php
// side_bar_template.php bevat de sluitende </div> voor main-panel,
// dan </div> voor wrapper, en dan </body> en </html>.
?>