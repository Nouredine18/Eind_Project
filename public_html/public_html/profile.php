<?php
include('connect.php');
session_start();

// Zorg dat gebruiker ingelogd is
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$current_view_user_id = $_SESSION['user_id']; // Ga ervan uit dat eigen profiel wordt bekeken, pas aan indien anderen worden bekeken

// Haal gebruikersdata op voor weergave en stel sessievariabelen in
$sql_fetch_user = "SELECT first_name, last_name, email, birthdate, address, profile_picture_url, total_points FROM users WHERE user_id = ?"; // total_points toegevoegd
$stmt_fetch_user = $conn->prepare($sql_fetch_user);
if ($stmt_fetch_user) {
    $stmt_fetch_user->bind_param("i", $current_view_user_id);
    $stmt_fetch_user->execute();
    $stmt_fetch_user->bind_result($first_name_display, $last_name_display, $email_display, $birthdate_display, $address_display, $profile_picture_url_display, $total_points_display); // $total_points_display toegevoegd
    $stmt_fetch_user->fetch();
    $stmt_fetch_user->close();

    $_SESSION['first_name'] = $first_name_display;
    $_SESSION['last_name'] = $last_name_display;
    $_SESSION['email'] = $email_display;
    $_SESSION['birthdate'] = $birthdate_display;
    $_SESSION['address'] = $address_display;
    $_SESSION['profile_picture_url'] = trim($profile_picture_url_display ?? '') ?: 'assets/img/kaiadmin/default.jpg';
    $_SESSION['total_points'] = $total_points_display ?? 0.00; // Sla punten op in sessie
} else {
    error_log("Failed to prepare statement to fetch user data: " . $conn->error);
    $_SESSION['first_name'] = 'User';
    $_SESSION['last_name'] = '';
    $_SESSION['email'] = '';
    $_SESSION['birthdate'] = '';
    $_SESSION['address'] = '';
    $_SESSION['profile_picture_url'] = 'assets/img/kaiadmin/default.jpg';
    $_SESSION['total_points'] = 0.00; // Standaard punten
}

// Definieer paginaspecifieke CSS-bestanden om op te nemen in de <head>
$page_specific_css = [
    "https://fonts.googleapis.com/css2?family=Agdasima&display=swap",
    "https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap",
    "assets/css/profile.css"
];

// Voeg hoofd paginastructuur, zijbalk, bovenbalk en algemene JS/CSS toe
include('side_bar_template.php');

// PHP-logica voor het afhandelen van profielupdates (formulierindiening)
$error_message_display = ""; // Gebruikt om fouten te verzamelen voor weergave in HTML
$success_script_display = ""; // Gebruikt om succes-scripts te verzamelen

if (isset($_POST['edit_profile_information'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $birthdate = $_POST['birthdate'];
    $address = $_POST['address'];
    $password = $_POST['password'];

    $sql_pic = "SELECT profile_picture_url FROM users WHERE user_id = ?";
    $stmt_pic = $conn->prepare($sql_pic);
    $stmt_pic->bind_param("i", $current_view_user_id);
    $stmt_pic->execute();
    $stmt_pic->bind_result($current_profile_picture_url);
    $stmt_pic->fetch();
    $stmt_pic->close();

    $profile_picture_path = $current_profile_picture_url;

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $profile_picture = $_FILES['profile_picture'];
        $profile_picture_name = basename($profile_picture['name']);
        $profile_directory = 'profile_directory/';

        if (!is_dir($profile_directory)) {
            mkdir($profile_directory, 0755, true);
        }

        $profile_picture_path = $profile_directory . uniqid() . '-' . $profile_picture_name;

        if (!move_uploaded_file($profile_picture['tmp_name'], $profile_picture_path)) {
            $error_message_display = "Failed to upload profile picture.";
        }
    }

    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $sql_update = "UPDATE users SET first_name = ?, last_name = ?, email = ?, birthdate = ?, address = ?, password = ?, profile_picture_url = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssssssi", $first_name, $last_name, $email, $birthdate, $address, $password_hash, $profile_picture_path, $current_view_user_id);
    } else {
        $sql_update = "UPDATE users SET first_name = ?, last_name = ?, email = ?, birthdate = ?, address = ?, profile_picture_url = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssssssi", $first_name, $last_name, $email, $birthdate, $address, $profile_picture_path, $current_view_user_id);
    }

    if ($stmt_update->execute()) {
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['birthdate'] = $birthdate;
        $_SESSION['address'] = $address;
        $_SESSION['profile_picture_url'] = trim($profile_picture_path ?? '') ?: 'assets/img/kaiadmin/default.jpg';

        $notification_message = "You have updated your profile. Check your profile.";
        saveNotification($conn, $current_view_user_id, $notification_message, 'profile_update');

        $success_script_display = "<script>
                $(document).ready(function() {
                    var content = {};
                    content.message = 'You have updated your profile. Check your profile.';
                    content.title = 'Profile Updated';
                    content.icon = 'fa fa-bell';
                    content.url = 'profile.php';
                    content.target = '_self';

                    $.notify(content, {
                        type: 'success',
                        placement: {
                            from: 'top',
                            align: 'center'
                        },
                        time: 1000,
                        delay: 3000
                    });
                });
              </script>";
    } else {
        $error_message_display = "Database query error: " . $stmt_update->error;
    }
    $stmt_update->close();
}

function saveNotification($conn, $userId, $message, $type) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, notification_type, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $userId, $message, $type);
    $stmt->execute();
    $stmt->close();
}

$follower_count = 0;
$stmt_followers = $conn->prepare("SELECT COUNT(*) as count FROM user_followers WHERE following_id = ?");
if ($stmt_followers) {
    $stmt_followers->bind_param("i", $current_view_user_id);
    $stmt_followers->execute();
    $follower_result = $stmt_followers->get_result()->fetch_assoc();
    $follower_count = $follower_result['count'] ?? 0;
    $stmt_followers->close();
}

$following_count = 0;
$stmt_following = $conn->prepare("SELECT COUNT(*) as count FROM user_followers WHERE follower_id = ?");
if ($stmt_following) {
    $stmt_following->bind_param("i", $current_view_user_id);
    $stmt_following->execute();
    $following_result = $stmt_following->get_result()->fetch_assoc();
    $following_count = $following_result['count'] ?? 0;
    $stmt_following->close();
}

$total_points_for_display = $_SESSION['total_points'] ?? 0.00; // Gebruik sessiewaarde of haal opnieuw op indien gewenst

$sql_co2 = "SELECT SUM(co2_emissions) AS total_emissions FROM travelhistory WHERE user_id = ?";
$stmt_co2 = $conn->prepare($sql_co2);
$co2_error_for_display = null; 
if ($stmt_co2 === false) {
    $co2_error_for_display = "Error preparing CO2 statement: " . $conn->error;
    $totalEmissions = 0;
} else {
    $stmt_co2->bind_param("i", $current_view_user_id);
    $stmt_co2->execute();   
    $result_co2 = $stmt_co2->get_result();
    if ($result_co2->num_rows > 0) {
        $data_co2 = $result_co2->fetch_assoc();
        $totalEmissions = $data_co2['total_emissions'] ?? 0;
    } else {
        $totalEmissions = 0;
    }
    $stmt_co2->close();
}
?>

<style>
body,
.wrapper,
.main-panel,
.main-header {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
</style>

<<<<<<< Updated upstream
        <div class="row gutters-sm">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center text-center">
                            <img src="<?php echo htmlspecialchars($profile_picture_url); ?>" alt="Profile Picture" class="rounded-circle" width="150">
                            <div class="mt-3">
                                <h4><?php echo $_SESSION['first_name'] . " " . $_SESSION["last_name"]; ?></h4>
                                <p class="text-secondary mb-1">Full Stack Developer</p>
                                <div class="row">
                                      <div class="col-md-12">
                                          <div class="card mb-3">
                                              <div class="card-body">
                                                  <h4>CO2 Emissions Summary</h4>
                                                  <p>Totaal CO2-uitstoot: <?= number_format($totalEmissions, 2) ?> kg CO2</p>
                                                  <?php
                                                   if ($totalEmissions > 1000) { ?>
                                                      <p style="color: red;">Overweeg duurzamere reisopties!</p>
                                                  <?php } ?>
=======
<div class="page-inner">
    <div id="profile-page-content"> 
        <div class="container">
            <div class="main-body">
                <nav aria-label="breadcrumb" class="main-breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0)">User</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User Profile</li>
                    </ol>
                </nav>

                <?php
                if ($error_message_display) {
                    echo "<div class='alert alert-danger'>" . htmlspecialchars($error_message_display) . "</div>";
                }
                if ($co2_error_for_display) {
                    echo "<div class='alert alert-danger'>" . htmlspecialchars($co2_error_for_display) . "</div>";
                }
                if ($success_script_display) {
                    echo $success_script_display;
                }
                ?>

                <div class="row gutters-sm">
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center text-center">
                                    <img src="<?php echo htmlspecialchars($_SESSION['profile_picture_url']); ?>" alt="Profile Picture" class="rounded-circle" width="150">
                                    <div class="mt-3">
                                        <h4><?php echo htmlspecialchars(($_SESSION['first_name'] ?? '') . " " . ($_SESSION["last_name"] ?? '')); ?></h4>
                                        <p class="text-secondary mb-1" style="color: green !important;">Ecoligo Member</p>
                                        <p class="text-muted font-size-sm"><?php echo htmlspecialchars($_SESSION['address'] ?? ''); ?></p>
                                        <div class="mt-2">
                                            <a href="followers_list.php?user_id=<?php echo $current_view_user_id; ?>" class="badge bg-primary text-decoration-none">Followers: <?php echo $follower_count; ?></a>
                                            <a href="following_list.php?user_id=<?php echo $current_view_user_id; ?>" class="badge bg-info text-decoration-none me-1">Following: <?php echo $following_count; ?></a>
                                            <span class="badge bg-warning text-dark">Points: <?php echo number_format($total_points_for_display, 2); ?></span>
                                        </div>
                                        <div class="row mt-3">
                                              <div class="col-md-12">
                                                  <div class="card mb-3">
                                                      <div class="card-body">
                                                          <h4>CO2 Emissions Summary</h4>
                                                          <p>Totaal CO2-uitstoot: <?= number_format($totalEmissions, 2) ?> kg CO2</p>
                                                          <?php
                                                           if ($totalEmissions > 1000) { ?>
                                                              <p style="color: red;">Consider more sustainable travel options!</p>
                                                          <?php } ?>
                                                      </div>
                                                  </div>
>>>>>>> Stashed changes
                                              </div>
                                             <a href = "delete_account.php">Delete Account</a>
                                          </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card mb-3">
                            <div class="card-body">
                                <form action="profile.php" method="post" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">First Name</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($_SESSION['first_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Last Name</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($_SESSION['last_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Email</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Address</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($_SESSION['address'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Birthdate</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="date" class="form-control" name="birthdate" value="<?php echo htmlspecialchars($_SESSION['birthdate'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Password</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="password" class="form-control" name="password" placeholder="Enter new password if you want to change it">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Profile Picture</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="file" class="form-control" name="profile_picture" accept="image/*">
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <button type="submit" id="displayNotifButton" class="btn btn-info" name="edit_profile_information">Update Profile</button>
                                        </div>
                                    </div>
                                    <hr>
                                    <h3>Settings</h3>
                                    <div class="row">
                                        <div class="col-sm-12">
                                        <table class="table table-bordered">
                                            <tr>
                                                <td><a href="co2_calculation/view_travel_history.php">View History</a></td>
                                                    </tr>
                                                <tr>
                                            <td><a href="co2_calculation/add_travel.php">Add travel destination</a></td>
                                                </tr>
                                                    <tr>
                                                <td><a href="co2_calculation/preferences.php">Add preferences</a></td>
                                            </tr>
                                                <tr>
                                                    <td><a href="co2_calculation/communication_preferences.php">Add Communication preferences</a></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>
<?php
?>
