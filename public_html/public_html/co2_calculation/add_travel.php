<?php
session_start();
include '../connect.php';

function saveNotification($conn, $userId, $message, $type) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, notification_type, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $userId, $message, $type);
    $stmt->execute();
    $stmt->close();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$userId = $_SESSION['user_id'];

function calculateCO2Emissions($distance, $mode) {
    $co2_per_km = 0;

    switch ($mode) {
        case 'auto':
            $co2_per_km = 0.192;  
            break;
        case 'trein':
            $co2_per_km = 0.041; 
            break;
        case 'vliegtuig':
            $co2_per_km = 0.257; 
            break;
        case 'fiets':
            $co2_per_km = 0;  
            break;
        case 'bus':
            $co2_per_km = 0.105;  
            break;
    }

    return $co2_per_km * $distance;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $origin = htmlspecialchars($_POST['origin']);
    $destination = htmlspecialchars($_POST['destination']);
    $distance = floatval($_POST['distance_km']);
    $mode = htmlspecialchars($_POST['transport_mode']);
    $travelDate = htmlspecialchars($_POST['travel_date']);

    if (!empty($origin) && !empty($destination) && $distance > 0 && !empty($mode) && !empty($travelDate)) {
        $co2Emissions = calculateCO2Emissions($distance, $mode);

        $sql = "INSERT INTO travelhistory (user_id, origin, destination, distance_km, transport_mode, travel_date, co2_emissions) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("issdssd", $userId, $origin, $destination, $distance, $mode, $travelDate, $co2Emissions);
            if ($stmt->execute()) {
                $notification_message = "You have added a new travel destination: $origin to $destination";
                saveNotification($conn, $userId, $notification_message, 'Travel Destination Added');
                
                $_SESSION['travel_history_message'] = ['type' => 'success', 'text' => 'Travel successfully added. CO2 Emissions: ' . number_format($co2Emissions, 2) . ' kg.'];
                header("Location: view_travel_history.php");
                exit();
            } else {
                $error = "Er is een fout opgetreden bij het opslaan van de gegevens: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Er is een fout opgetreden bij het voorbereiden van de query: " . $conn->error;
        }
    } else {
        $error = "Alle velden zijn verplicht en afstand moet groter zijn dan 0.";
    }
}

include('../side_bar_template.php');
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Add New Travel</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="../home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="../profile.php">Profile</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="view_travel_history.php">Travel History</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="add_travel.php">Add Travel</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Enter Your Travel Details</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="add_travel.php">
                        <div class="mb-3">
                            <label for="origin" class="form-label">Origin:</label>
                            <input type="text" class="form-control" id="origin" name="origin" placeholder="E.g., Amsterdam" required value="<?php echo isset($_POST['origin']) ? htmlspecialchars($_POST['origin']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="destination" class="form-label">Destination:</label>
                            <input type="text" class="form-control" id="destination" name="destination" placeholder="E.g., Paris" required value="<?php echo isset($_POST['destination']) ? htmlspecialchars($_POST['destination']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="distance_km" class="form-label">Distance (km):</label>
                            <input type="number" class="form-control" id="distance_km" name="distance_km" step="0.01" placeholder="E.g., 500.50" required value="<?php echo isset($_POST['distance_km']) ? htmlspecialchars($_POST['distance_km']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="transport_mode" class="form-label">Transport Mode:</label>
                            <select class="form-select" id="transport_mode" name="transport_mode" required>
                                <option value="">Select</option>
                                <option value="auto" <?php echo (isset($_POST['transport_mode']) && $_POST['transport_mode'] == 'auto') ? 'selected' : ''; ?>>Car</option>
                                <option value="trein" <?php echo (isset($_POST['transport_mode']) && $_POST['transport_mode'] == 'trein') ? 'selected' : ''; ?>>Train</option>
                                <option value="vliegtuig" <?php echo (isset($_POST['transport_mode']) && $_POST['transport_mode'] == 'vliegtuig') ? 'selected' : ''; ?>>Plane</option>
                                <option value="fiets" <?php echo (isset($_POST['transport_mode']) && $_POST['transport_mode'] == 'fiets') ? 'selected' : ''; ?>>Bicycle</option>
                                <option value="bus" <?php echo (isset($_POST['transport_mode']) && $_POST['transport_mode'] == 'bus') ? 'selected' : ''; ?>>Bus</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="travel_date" class="form-label">Travel Date:</label>
                            <input type="date" class="form-control" id="travel_date" name="travel_date" required value="<?php echo isset($_POST['travel_date']) ? htmlspecialchars($_POST['travel_date']) : ''; ?>">
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Add Travel</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                     <a href="view_travel_history.php" class="btn btn-secondary">Back to Travel History</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// The side_bar_template.php includes closing HTML tags and scripts
?>