<?php
session_start();
include '../connect.php';

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Haal CO2-uitstoot op voor de ingelogde gebruiker
$sql = "SELECT SUM(c.co2_emissions) AS total_emissions 
        FROM travelhistory t
        JOIN co2calculations c ON t.travel_id = c.travel_id
        WHERE t.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$totalEmissions = $data['total_emissions'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>CO2 Samenvatting</title>
</head>
<body>
<p>Totaal CO2-uitstoot: <?= number_format($totalEmissions, 2) ?> kg CO2</p>
<?php if ($totalEmissions > 1000) { ?>
    <p style="color: red;">Overweeg duurzamere reisopties!</p>
<?php } ?>

</body>
</html>
