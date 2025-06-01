<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit();
}

$userId = $_SESSION['user_id'];

// Haal totale CO2-uitstoot op voor de afgelopen 12 maanden
$sql = "SELECT MONTH(travel_date) as month, SUM(co2_emissions) as total_emissions 
        FROM travelhistory 
        WHERE user_id = ? AND travel_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
        GROUP BY MONTH(travel_date)
        ORDER BY MONTH(travel_date)";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Error preparing statement: ' . $conn->error]);
    exit();
}
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$co2Data = [];
while ($row = $result->fetch_assoc()) {
    $co2Data[] = [
        'month' => $row['month'],
        'emissions' => (float)$row['total_emissions']
    ];
}

$stmt->close();
echo json_encode($co2Data);
?>