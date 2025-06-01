<?php
session_start();
include 'connect.php'; // Maakt $conn aan

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$profile_user_id = null;
$user_data = null;
$error_message = null;

if (isset($_GET['user_id']) && filter_var($_GET['user_id'], FILTER_VALIDATE_INT) && $_GET['user_id'] > 0) {
    $profile_user_id = (int)$_GET['user_id'];

    // Haal openbare informatie van de gebruiker op
    $sql_user = "SELECT user_id, first_name, last_name, profile_picture_url, total_points FROM users WHERE user_id = ?"; 
    $stmt_user = $conn->prepare($sql_user);
    if ($stmt_user) {
        $stmt_user->bind_param("i", $profile_user_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        if ($result_user->num_rows > 0) {
            $user_data = $result_user->fetch_assoc();

            // Haal totale CO2-uitstoot op voor de gebruiker
            $total_emissions = 0;
            $sql_emissions = "SELECT SUM(co2_emissions) AS total_emissions FROM travelhistory WHERE user_id = ?";
            $stmt_emissions = $conn->prepare($sql_emissions);
            if ($stmt_emissions) {
                $stmt_emissions->bind_param("i", $profile_user_id);
                $stmt_emissions->execute();
                $result_emissions = $stmt_emissions->get_result()->fetch_assoc();
                $total_emissions = $result_emissions['total_emissions'] ?? 0;
                $stmt_emissions->close();
            }
            $user_data['total_emissions_kg'] = $total_emissions;

            // Haal totaal gecompenseerde CO2 op voor de gebruiker
            $total_compensated = 0;
            // Ga ervan uit dat 'donations.amount' de gecompenseerde CO2 in kg opslaat.
            $sql_compensated = "SELECT SUM(amount) AS total_compensated FROM donations WHERE user_id = ?";
            $stmt_compensated = $conn->prepare($sql_compensated);
            if ($stmt_compensated) {
                $stmt_compensated->bind_param("i", $profile_user_id);
                $stmt_compensated->execute();
                $result_compensated = $stmt_compensated->get_result()->fetch_assoc();
                $total_compensated = $result_compensated['total_compensated'] ?? 0;
                $stmt_compensated->close();
            } else {
                error_log("Failed to prepare statement for donations (compensation): " . $conn->error); 
            }
            $user_data['total_compensated_kg'] = $total_compensated;

        } else {
            $error_message = "User not found.";
        }
        $stmt_user->close();
    } else {
        $error_message = "Error preparing user data query: " . $conn->error;
    }
} else {
    $error_message = "Invalid user ID provided.";
}

include 'side_bar_template.php'; // Includes HTML head, sidebar, and topbar
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">User Profile Details</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="following_list.php">Following Details</a></li> 
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Profile Details</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <?php if ($error_message): ?>
                <div class="card">
                    <div class="card-body">
                        <p class="text-center text-danger"><?php echo htmlspecialchars($error_message); ?></p>
                        <p class="text-center"><a href="javascript:history.back()" class="btn btn-primary">Back to Previous Page</a></p>
                    </div>
                </div>
            <?php elseif ($user_data): ?>
                <div class="card card-profile">
                    <div class="card-header" style="background-image: url('assets/img/blogpost.jpg'); height: 200px;">
                        <div class="profile-picture" style="bottom: -50px;">
                            <div class="avatar avatar-xxl">
                                <img src="<?php echo htmlspecialchars(trim((string)$user_data['profile_picture_url']) ?: 'assets/img/kaiadmin/default.jpg'); ?>" alt="Profile Picture" class="avatar-img rounded-circle">
                            </div>
                        </div>
                    </div>
                    <div class="card-body" style="padding-top: 60px;">
                        <div class="user-profile text-center">
                            <div class="name fs-2 fw-bold"><?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?></div>
                            <hr class="my-4">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <h5 class="fw-bold">CO₂ Emissions</h5>
                                    <p class="fs-4"><?php echo number_format($user_data['total_emissions_kg'], 2); ?> kg</p>
                                </div>
                                <div class="col-md-3">
                                    <h5 class="fw-bold">CO₂ Compensated</h5>
                                    <p class="fs-4"><?php echo number_format($user_data['total_compensated_kg'], 2); ?> kg</p>
                                </div>
                                <div class="col-md-3">
                                    <?php 
                                    $balance = $user_data['total_emissions_kg'] - $user_data['total_compensated_kg'];
                                    $balance_status = $balance > 0 ? "Needs Compensation" : "Compensated";
                                    $balance_class = $balance > 0 ? "text-danger" : "text-success";
                                    ?>
                                    <h5 class="fw-bold">Balance</h5>
                                    <p class="fs-4 <?php echo $balance_class; ?>">
                                        <?php echo number_format(abs($balance), 2); ?> kg
                                        <br><small>(<?php echo $balance_status; ?>)</small>
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <h5 class="fw-bold">Total Points</h5>
                                    <p class="fs-4"><?php echo number_format($user_data['total_points'] ?? 0, 2); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="javascript:history.back()" class="btn btn-primary">Back to Previous Page</a>
                    </div>
                </div>
            <?php else: // Zou niet moeten gebeuren als error_message ook null is, maar als fallback ?>
                 <div class="card">
                    <div class="card-body">
                        <p class="text-center text-muted">Could not load user details.</p>
                         <p class="text-center"><a href="javascript:history.back()" class="btn btn-primary">Back to Previous Page</a></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<style>
    .avatar-xxl img { width: 100px; height: 100px; }
    .card-profile .profile-picture {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }
</style>
<?php
// side_bar_template.php bevat sluitende tags voor main-panel, wrapper, body, html,
// en ook algemene JavaScript-bestanden aan het einde van de body.
?>
