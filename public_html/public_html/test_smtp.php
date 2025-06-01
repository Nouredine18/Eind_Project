<?php
require 'vendor/autoload.php'; // Ensure you have installed PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.hostinger.com'; // Set the SMTP server to send through
    $mail->SMTPAuth = true;
    $mail->Username = 'info@ecoligocollective.com'; // SMTP username
    $mail->Password = 'Nouredinetah18!'; // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Recipients
    $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective');
    $mail->addAddress('recipient_email@example.com'); // Add a recipient

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test';
    $mail->Body = 'This is a test email to verify SMTP settings.';

    $mail->send();
    echo 'Test email has been sent';
} catch (Exception $e) {
    echo "Test email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
