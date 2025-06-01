<?php
session_start();
include 'connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.0;

// Fetch project details from the database
$sql = "SELECT * FROM compensationprojects WHERE project_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Payment Method</title>
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
                    <h3 class="fw-bold mb-3">Select Payment Method</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php if ($project) { ?>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Select Payment Method</h5>
                                <form action="process_payment.php" method="post">
                                    <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">
                                    <input type="hidden" name="project_id" value="<?= htmlspecialchars($project_id) ?>">
                                    <div class="mb-3">
                                        <button type="submit" name="payment_method" value="paypal" class="btn btn-primary">Pay with PayPal</button>
                                    </div>
                                    <div class="mb-3">
                                        <button type="submit" name="payment_method" value="stripe" class="btn btn-primary">Pay with Stripe</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php } else { ?>
                        <p>Project not found.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
