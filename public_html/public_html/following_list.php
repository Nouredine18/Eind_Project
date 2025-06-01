<?php
session_start();
include 'connect.php'; // Maakt $conn aan

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id_session = $_SESSION['user_id'];
$page_user_id = $current_user_id_session; // Standaard ingelogde gebruiker

if (isset($_GET['user_id']) && filter_var($_GET['user_id'], FILTER_VALIDATE_INT)) {
    $page_user_id = (int)$_GET['user_id']; // Gebruik user_id uit URL indien aanwezig
}

// Haal info op van de gebruiker wiens "volgend" lijst wordt bekeken
$page_user_info_sql = "SELECT user_id, first_name, last_name FROM users WHERE user_id = ?";
$page_user_info_stmt = $conn->prepare($page_user_info_sql);
$page_user_info_stmt->bind_param("i", $page_user_id);
$page_user_info_stmt->execute();
$page_user_info_result = $page_user_info_stmt->get_result();
$page_user_info = $page_user_info_result->fetch_assoc();
$page_user_info_stmt->close();

if (!$page_user_info) {
    // Handel gebruiker niet gevonden af
    // Voor nu, ga ervan uit dat side_bar_template dit afhandelt of redirect
    include 'side_bar_template.php';
    echo "<div class='page-inner'><div class='alert alert-danger'>User not found.</div></div>";
    exit();
}


$followed_users_details = [];

// Haal gebruikers op die gevolgd worden door $page_user_id
$sql_followed = "SELECT u.user_id, u.first_name, u.last_name, u.profile_picture_url
                 FROM users u
                 JOIN user_followers uf ON u.user_id = uf.following_id
                 WHERE uf.follower_id = ?"; // follower_id is degene die volgt
$stmt_followed = $conn->prepare($sql_followed);

if ($stmt_followed) {
    $stmt_followed->bind_param("i", $page_user_id);
    $stmt_followed->execute();
    $result_followed = $stmt_followed->get_result();

    while ($user = $result_followed->fetch_assoc()) {
        $followed_user_id = $user['user_id'];
        $user_details = $user;

        // Haal totale CO2-uitstoot op voor de gevolgde gebruiker
        $total_emissions = 0;
        $sql_emissions = "SELECT SUM(co2_emissions) AS total_emissions FROM travelhistory WHERE user_id = ?";
        $stmt_emissions = $conn->prepare($sql_emissions);
        if ($stmt_emissions) {
            $stmt_emissions->bind_param("i", $followed_user_id);
            $stmt_emissions->execute();
            $result_emissions = $stmt_emissions->get_result()->fetch_assoc();
            $total_emissions = $result_emissions['total_emissions'] ?? 0;
            $stmt_emissions->close();
        }
        $user_details['total_emissions_kg'] = $total_emissions;

        // Haal totaal gecompenseerde CO2 op voor de gevolgde gebruiker
        $total_compensated = 0;
        // Ga ervan uit dat 'donations.amount' de gecompenseerde CO2 in kg opslaat.
        $sql_compensated = "SELECT SUM(amount) AS total_compensated FROM donations WHERE user_id = ?";
        $stmt_compensated = $conn->prepare($sql_compensated);
        if ($stmt_compensated) {
            $stmt_compensated->bind_param("i", $followed_user_id);
            $stmt_compensated->execute();
            $result_compensated = $stmt_compensated->get_result()->fetch_assoc();
            $total_compensated = $result_compensated['total_compensated'] ?? 0;
            $stmt_compensated->close();
        } else {
            error_log("Failed to prepare statement for donations (compensation): " . $conn->error); 
        }
        $user_details['total_compensated_kg'] = $total_compensated;
        
        $followed_users_details[] = $user_details;
    }
    $stmt_followed->close();
} else {
    error_log("Failed to prepare statement to fetch followed users: " . $conn->error);
}

include 'side_bar_template.php'; 
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            <?php echo ($page_user_id == $current_user_id_session) ? "Users You Are Following" : htmlspecialchars($page_user_info['first_name'] . " " . $page_user_info['last_name']) . " Is Following"; ?>
        </h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="profile.php<?php echo ($page_user_id != $current_user_id_session) ? '?user_id='.$page_user_id : ''; ?>">Profile<?php echo ($page_user_id != $current_user_id_session) ? ' of '.htmlspecialchars($page_user_info['first_name']) : ''; ?></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="following_list.php?user_id=<?php echo $page_user_id; ?>">Following</a></li>
        </ul>
    </div>

    <div class="row">
        <?php if (empty($followed_users_details)): ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center text-muted">
                            <?php echo ($page_user_id == $current_user_id_session) ? "You are not following anyone yet." : htmlspecialchars($page_user_info['first_name']) . " is not following anyone yet."; ?>
                        </p>
                        <?php if ($page_user_id == $current_user_id_session): ?>
                            <p class="text-center"><a href="search_users.php" class="btn btn-primary">Find Users to Follow</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($followed_users_details as $detail): ?>
                <div class="col-md-4">
                    <a href="follow_profile_details.php?user_id=<?php echo $detail['user_id']; ?>" class="profile-card-link">
                        <div class="card card-profile">
                            <div class="card-header" style="background-image: url('assets/img/blogpost.jpg')">
                                <div class="profile-picture">
                                    <div class="avatar avatar-xl">
                                        <img src="<?php echo htmlspecialchars(trim((string)$detail['profile_picture_url']) ?: 'assets/img/kaiadmin/default.jpg'); ?>" alt="Profile Picture" class="avatar-img rounded-circle">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="user-profile text-center">
                                    <div class="name"><?php echo htmlspecialchars($detail['first_name'] . ' ' . $detail['last_name']); ?></div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <strong>CO₂ Emissions</strong>
                                            <p><?php echo number_format($detail['total_emissions_kg'], 2); ?> kg</p>
                                        </div>
                                        <div class="col-6">
                                            <strong>CO₂ Compensated</strong>
                                            <p><?php echo number_format($detail['total_compensated_kg'], 2); ?> kg</p>
                                        </div>
                                    </div>
                                    <?php 
                                    $balance = $detail['total_emissions_kg'] - $detail['total_compensated_kg'];
                                    $balance_status = $balance > 0 ? "Needs Compensation" : "Compensated";
                                    $balance_class = $balance > 0 ? "text-danger" : "text-success";
                                    ?>
                                    <div class="mt-2">
                                        <strong>Balance:</strong> <span class="<?php echo $balance_class; ?>"><?php echo number_format(abs($balance), 2); ?> kg (<?php echo $balance_status; ?>)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row user-stats text-center">
                                    <div class="col">
                                         <!-- View Profile button removed -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="row mt-4">
        <div class="col-md-12 text-center">
            <a href="profile.php?user_id=<?php echo $page_user_id; ?>" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to <?php echo htmlspecialchars($page_user_info['first_name'] . " " . $page_user_info['last_name']); ?>'s Profile
            </a>
        </div>
    </div>
</div>
<style>
    .card-profile .card-header {
        height: 150px;
        background-size: cover;
        background-position: center;
    }
    .card-profile .profile-picture {
        position: absolute;
        bottom: -30px; /* Adjust as needed */
        left: 50%;
        transform: translateX(-50%);
    }
    .card-profile .card-body {
        padding-top: 40px; /* Space for profile picture */
    }
    .card-profile:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }
    .profile-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .profile-card-link:hover {
        text-decoration: none;
        color: inherit;
    }
</style>

<?php
// side_bar_template.php bevat sluitende tags voor main-panel, wrapper, body, html,
// en ook algemene JavaScript-bestanden aan het einde van de body.
?>
