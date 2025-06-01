<?php
session_start();
include '../connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Adjusted path
    exit();
}

$userId = $_SESSION['user_id'];
$success_message = null;
$error_message = null;

// Fetch current preferences
$current_comm_preferences = ['email' => 'Nee', 'sms' => 'Nee']; // Default to No
$sql_get_comm_prefs = "SELECT communication_preferences FROM users WHERE user_id = ?";
$stmt_get_comm_prefs = $conn->prepare($sql_get_comm_prefs);
if ($stmt_get_comm_prefs) {
    $stmt_get_comm_prefs->bind_param("i", $userId);
    $stmt_get_comm_prefs->execute();
    $result_comm_prefs = $stmt_get_comm_prefs->get_result();
    if ($row_comm_prefs = $result_comm_prefs->fetch_assoc()) {
        if (!empty($row_comm_prefs['communication_preferences'])) {
            $decoded_comm_prefs = json_decode($row_comm_prefs['communication_preferences'], true);
            if (is_array($decoded_comm_prefs)) {
                 $current_comm_preferences['email'] = $decoded_comm_prefs['email'] ?? 'Nee';
                 $current_comm_preferences['sms'] = $decoded_comm_prefs['sms'] ?? 'Nee';
            }
        }
    }
    $stmt_get_comm_prefs->close();
}


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_preference = isset($_POST['email']) ? 'Ja' : 'Nee';
    $sms_preference = isset($_POST['sms']) ? 'Ja' : 'Nee';

    $sql = "UPDATE users SET communication_preferences = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $error_message = "Error preparing statement: " . $conn->error;
    } else {
        $communication_preferences_json = json_encode(['email' => $email_preference, 'sms' => $sms_preference]);
        $stmt->bind_param("si", $communication_preferences_json, $userId);
        if ($stmt->execute()) {
            $success_message = "Communication preferences have been updated successfully!";
            $current_comm_preferences['email'] = $email_preference;
            $current_comm_preferences['sms'] = $sms_preference;
        } else {
            $error_message = "An error occurred while saving preferences: " . $stmt->error;
        }
        $stmt->close();
    }
}
include('../side_bar_template.php');
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Set Communication Preferences</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="../home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="../profile.php">Profile</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="communication_preferences.php">Communication Preferences</a></li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Your Communication Preferences</h4>
                </div>
                <div class="card-body">
                    <?php if ($success_message): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="communication_preferences.php" method="post">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="email" id="email" <?php echo ($current_comm_preferences['email'] == 'Ja') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="email">
                                Receive emails?
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="sms" id="sms" <?php echo ($current_comm_preferences['sms'] == 'Ja') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="sms">
                                Receive SMS messages?
                            </label>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Save Preferences</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                     <a href="../profile.php" class="btn btn-secondary">Back to Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// The side_bar_template.php includes closing HTML tags and scripts
?>