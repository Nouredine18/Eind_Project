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

// Haal info op van de gebruiker wiens volgerslijst wordt bekeken
$page_user_info_sql = "SELECT user_id, first_name, last_name FROM users WHERE user_id = ?";
$page_user_info_stmt = $conn->prepare($page_user_info_sql);
$page_user_info_stmt->bind_param("i", $page_user_id);
$page_user_info_stmt->execute();
$page_user_info_result = $page_user_info_stmt->get_result();
$page_user_info = $page_user_info_result->fetch_assoc();
$page_user_info_stmt->close();

if (!$page_user_info) {
    include 'side_bar_template.php';
    echo "<div class='page-inner'><div class='alert alert-danger'>User not found.</div></div>";
    exit();
}

$followers_details = [];

// Haal gebruikers op die $page_user_id volgen
$sql_followers = "SELECT u.user_id, u.first_name, u.last_name, u.profile_picture_url, u.total_points
                  FROM users u
                  JOIN user_followers uf ON u.user_id = uf.follower_id
                  WHERE uf.following_id = ?"; // following_id is degene die gevolgd wordt
$stmt_followers = $conn->prepare($sql_followers);

if ($stmt_followers) {
    $stmt_followers->bind_param("i", $page_user_id);
    $stmt_followers->execute();
    $result_followers = $stmt_followers->get_result();

    while ($user = $result_followers->fetch_assoc()) {
        $follower_user_id = $user['user_id'];
        $user_details = $user;

        // Haal totale CO2-uitstoot op voor de volger
        $total_emissions = 0;
        $sql_emissions = "SELECT SUM(co2_emissions) AS total_emissions FROM travelhistory WHERE user_id = ?";
        $stmt_emissions = $conn->prepare($sql_emissions);
        if ($stmt_emissions) {
            $stmt_emissions->bind_param("i", $follower_user_id);
            $stmt_emissions->execute();
            $result_emissions = $stmt_emissions->get_result()->fetch_assoc();
            $total_emissions = $result_emissions['total_emissions'] ?? 0;
            $stmt_emissions->close();
        }
        $user_details['total_emissions_kg'] = $total_emissions;

        // Haal totaal gecompenseerde CO2 op voor de volger
        $total_compensated = 0;
        // Ga ervan uit dat 'donations.amount' de gecompenseerde CO2 in kg opslaat.
        $sql_compensated = "SELECT SUM(amount) AS total_compensated FROM donations WHERE user_id = ?";
        $stmt_compensated = $conn->prepare($sql_compensated);
        if ($stmt_compensated) {
            $stmt_compensated->bind_param("i", $follower_user_id);
            $stmt_compensated->execute();
            $result_compensated = $stmt_compensated->get_result()->fetch_assoc();
            $total_compensated = $result_compensated['total_compensated'] ?? 0;
            $stmt_compensated->close();
        } else {
            error_log("Failed to prepare statement for donations (compensation): " . $conn->error); 
        }
        $user_details['total_compensated_kg'] = $total_compensated;
        
        $followers_details[] = $user_details;
    }
    $stmt_followers->close();
} else {
    error_log("Failed to prepare statement to fetch followers: " . $conn->error);
}

include 'side_bar_template.php';
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">
            <?php echo ($page_user_id == $current_user_id_session) ? "Your Followers" : "Followers of " . htmlspecialchars($page_user_info['first_name'] . " " . $page_user_info['last_name']); ?>
        </h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="profile.php<?php echo ($page_user_id != $current_user_id_session) ? '?user_id='.$page_user_id : ''; ?>">Profile<?php echo ($page_user_id != $current_user_id_session) ? ' of '.htmlspecialchars($page_user_info['first_name']) : ''; ?></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="followers_list.php?user_id=<?php echo $page_user_id; ?>">Followers</a></li>
        </ul>
    </div>

    <div class="row">
        <?php if (empty($followers_details)): ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center text-muted">
                             <?php echo ($page_user_id == $current_user_id_session) ? "You have no followers yet." : htmlspecialchars($page_user_info['first_name']) . " has no followers yet."; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($followers_details as $detail): ?>
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
                                    <div class="mt-2">
                                        <strong>Points:</strong> <span class="badge bg-warning text-dark"><?php echo number_format($detail['total_points'] ?? 0, 2); ?></span>
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
