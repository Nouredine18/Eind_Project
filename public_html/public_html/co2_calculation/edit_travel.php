<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

function saveNotification($conn, $userId, $message, $type) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, notification_type, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $userId, $message, $type);
    $stmt->execute();
    $stmt->close();
}

$userId = $_SESSION['user_id'];
$travelId = intval($_GET['travel_id']);

// Haal bestaande reisgegevens op
$sql = "SELECT * FROM travelhistory WHERE travel_id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $travelId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$travel = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $origin = $_POST['origin'];
    $destination = $_POST['destination'];
    $distance = floatval($_POST['distance_km']);
    $mode = $_POST['transport_mode'];

    $sql = "UPDATE travelhistory SET origin = ?, destination = ?, distance_km = ?, transport_mode = ? WHERE travel_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiii", $origin, $destination, $distance, $mode, $travelId, $userId);
    $stmt->execute();
    $notification_message = "Your original travel destination has been updated to $origin to $destination. Original travel: " . $_POST['origin'] . " to " . $_POST['destination'];    
    saveNotification($conn, $userId, $notification_message, 'Travel Destination Added');
    header("Location: view_travel_history.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Bewerk Reisgegevens</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bewerk Reisgegevens</h2>
        <form method="POST">
            <label>Vertrek:</label>
            <input type="text" name="origin" value="<?= htmlspecialchars($travel['origin']) ?>" required><br>
            <label>Bestemming:</label>
            <input type="text" name="destination" value="<?= htmlspecialchars($travel['destination']) ?>" required><br>
            <label>Afstand (km):</label>
            <input type="number" name="distance_km" step="0.01" value="<?= htmlspecialchars($travel['distance_km']) ?>" required><br>
            <label>Transport:</label>
            <input type="text" name="transport_mode" value="<?= htmlspecialchars($travel['transport_mode']) ?>" required><br>
            <button type="submit">Opslaan</button>
        </form>
        <a href="view_travel_history.php" class="back-link">Ga terug</a>
    </div>
</body>
</html>