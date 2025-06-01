<?php
session_start();
include '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['travel_id'])) {
    $userId = $_SESSION['user_id'];
    $travelId = intval($_GET['travel_id']); // Voorkom SQL-injectie door intval()

    $sql = "DELETE FROM travelhistory WHERE travel_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $travelId, $userId);
    $stmt->execute();
}

header("Location: view_travel_history.php");
exit();
?>
