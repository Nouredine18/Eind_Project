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

// Fetch current preferences to pre-fill the form
$current_preferences = ['transport' => '', 'destination' => ''];
$sql_get_prefs = "SELECT preferences FROM users WHERE user_id = ?";
$stmt_get_prefs = $conn->prepare($sql_get_prefs);
if ($stmt_get_prefs) {
    $stmt_get_prefs->bind_param("i", $userId);
    $stmt_get_prefs->execute();
    $result_prefs = $stmt_get_prefs->get_result();
    if ($row_prefs = $result_prefs->fetch_assoc()) {
        if (!empty($row_prefs['preferences'])) {
            $decoded_prefs = json_decode($row_prefs['preferences'], true);
            if (is_array($decoded_prefs)) {
                $current_preferences = array_merge($current_preferences, $decoded_prefs);
            }
        }
    }
    $stmt_get_prefs->close();
}


// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $preferred_transport = $_POST['transport'];
    $preferred_destination = $_POST['destination'];

    // Save or process the preferences (e.g., in a database)
    $sql = "UPDATE users SET preferences = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $error_message = "Error preparing statement: " . $conn->error;
    } else {
        $preferences_json = json_encode(['transport' => $preferred_transport, 'destination' => $preferred_destination]);
        $stmt->bind_param("si", $preferences_json, $userId);
        if ($stmt->execute()) {
            $success_message = "Travel preferences have been updated successfully!";
            // Update current preferences for display
            $current_preferences['transport'] = $preferred_transport;
            $current_preferences['destination'] = $preferred_destination;
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
        <h3 class="fw-bold mb-3">Set Travel Preferences</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="../home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="../profile.php">Profile</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="preferences.php">Travel Preferences</a></li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Your Travel Preferences</h4>
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

                    <form action="preferences.php" method="post">
                        <div class="mb-3">
                            <label for="transport" class="form-label">Preferred mode of transport:</label>
                            <select name="transport" id="transport" class="form-select">
                                <option value="auto" <?php echo ($current_preferences['transport'] == 'auto') ? 'selected' : ''; ?>>Car</option>
                                <option value="vliegtuig" <?php echo ($current_preferences['transport'] == 'vliegtuig') ? 'selected' : ''; ?>>Plane</option>
                                <option value="trein" <?php echo ($current_preferences['transport'] == 'trein') ? 'selected' : ''; ?>>Train</option>
                                <option value="bus" <?php echo ($current_preferences['transport'] == 'bus') ? 'selected' : ''; ?>>Bus</option>
                                <option value="fiets" <?php echo ($current_preferences['transport'] == 'fiets') ? 'selected' : ''; ?>>Bicycle</option>
                                <option value="" <?php echo ($current_preferences['transport'] == '') ? 'selected' : ''; ?>>No Preference</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="destination" class="form-label">Preferred type of destination (e.g., City, Nature, Beach):</label>
                            <input type="text" class="form-control" id="destination" name="destination" value="<?php echo htmlspecialchars($current_preferences['destination']); ?>" required>
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