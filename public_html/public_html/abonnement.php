<?php
session_start();
include 'connect.php';

// check of gebruiker ingelogd is
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// haal alle compensatieprojecten op
$sql = "SELECT * FROM compensationprojects";
$result = $conn->query($sql);
$projects = $result->fetch_all(MYSQLI_ASSOC);

// Haal actieve abonnementen (project_id, startdatum, volgende betaaldatum) vd gebruiker op
$sql = "SELECT project_id, created_at, next_payment_date FROM subscriptions WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$activeSubscriptions = $result->fetch_all(MYSQLI_ASSOC);
$activeProjectIds = array_column($activeSubscriptions, 'project_id');
$subscriptionDates = array_column($activeSubscriptions, 'created_at', 'project_id');
$nextPaymentDates = array_column($activeSubscriptions, 'next_payment_date', 'project_id');
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subscription</title>
    <!-- Include necessary CSS and JS files -->
    <link rel="stylesheet" href="path/to/your/css/file.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
</head>
<body>
<?php include('side_bar_template.php') ?>
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Subscription for CO2 Compensation</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Description</th>
                            <th>Effectiveness</th>
                            <th>Subscription</th>
                            <th>Next Payment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project) { ?>
                            <tr>
                                <td><?= htmlspecialchars($project['name']) ?></td>
                                <td><?= htmlspecialchars($project['description']) ?></td>
                                <td><?= htmlspecialchars($project['effectiveness']) ?>%</td>
                                <td>
                                    <?php if (in_array($project['project_id'], $activeProjectIds)) { ?>
                                        <form action="cancel_subscription.php" method="post">
                                            <input type="hidden" name="project_id" value="<?= htmlspecialchars($project['project_id']) ?>">
                                            <button type="submit" class="btn btn-danger">Cancel Subscription</button>
                                        </form>
                                    <?php } else { ?>
                                        <form action="start_subscription.php" method="post">
                                            <input type="hidden" name="project_id" value="<?= htmlspecialchars($project['project_id']) ?>">
                                            <button type="submit" class="btn btn-primary">Start Subscription</button>
                                        </form>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if (in_array($project['project_id'], $activeProjectIds)) { 
                                        echo htmlspecialchars($nextPaymentDates[$project['project_id']]);
                                    } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
