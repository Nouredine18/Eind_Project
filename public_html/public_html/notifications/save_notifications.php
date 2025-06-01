<?php
include '../connect.php';

function saveNotification($conn, $userId, $message, $notificationType) {
    // Insert into notifications table
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, notification_type, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("iss", $userId, $notificationType, $message);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
    
    $stmt->close();
}

$conn->close();
?>