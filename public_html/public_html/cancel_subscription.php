<?php
session_start();
include 'connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// check of gebruiker ingelogd is
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$project_id = intval($_POST['project_id']);

// verwijder abonnement uit de database
$sql = "DELETE FROM subscriptions WHERE user_id = ? AND project_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $project_id);
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $email = $_SESSION['email']; // E-mailadres wordt uit sessie gehaald
        $subject = 'Subscription Canceled';
        $body = "You have canceled your subscription for project ID: $project_id.";
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@ecoligocollective.com';
            $mail->Password   = 'Nouredinetah18!';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            echo 'Message has been sent to ' . $email . '<br>';
        } catch (Exception $e) {
            echo "Message could not be sent to $email. Mailer Error: {$mail->ErrorInfo}<br>";
            error_log("Mailer Error: " . $mail->ErrorInfo); // log e-mailfouten
        }
    } else {
        echo "No subscription found to cancel.";
    }
} else {
    echo "Error canceling subscription: " . $stmt->error;
}
$stmt->close();

// stuur terug naar abonnementspagina
header('Location: abonnement.php');
exit();
?>
