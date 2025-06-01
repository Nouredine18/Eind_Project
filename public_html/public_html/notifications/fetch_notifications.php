<?php
include 'database_connection.php';

$userId = $_GET['user_id'] ?? 0;

$sql = "SELECT notification_id, user_id, message, notification_type, created_at 
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode($notifications);
$stmt->close();
$conn->close();
?>
