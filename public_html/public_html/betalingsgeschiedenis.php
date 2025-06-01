<?php
session_start();
include 'connect.php';

// check of gebruiker ingelogd is
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Haal donatiegeschiedenis op, inclusief projectnaam via JOIN
$sql = "SELECT d.*, cp.name AS project_name 
        FROM donations d 
        JOIN compensationprojects cp ON d.project_id = cp.project_id 
        WHERE d.user_id = ? 
        ORDER BY d.donation_date DESC";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$donations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History</title>
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
                    <h3 class="fw-bold mb-3">Payment History</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php if ($donations) { ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Paid Amount</th>
                                    <th>Payment Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation) { ?>
                                    <tr>
                                        <td><?= htmlspecialchars($donation['project_name']) ?></td>
                                        <td>€<?= htmlspecialchars($donation['amount']) ?></td>
                                        <td><?= htmlspecialchars($donation['donation_date']) ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } else { ?>
                        <p>No payment history found.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
