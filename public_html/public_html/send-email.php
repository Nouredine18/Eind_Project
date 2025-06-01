<?php
// Get form data from the POST request
$name = $_POST["name"];
$email = $_POST["email"];
$subject = $_POST["subject"] ?? '';
$message = $_POST["message"] ?? '';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com'; // Your SMTP server
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@ecoligocollective.com'; // Your SMTP username
    $mail->Password   = 'Nouredinetah18!';   // Your SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Use SSL
    $mail->Port       = 465; // 465 for SSL, 587 for TLS

    // Sender and recipient settings
    $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective'); // Use your SMTP email here

    // Send a copy to the submitter (Nouredine's email)
    $mail->addAddress($email, $name); // Send the email to Nouredine's email address
    $mail->addReplyTo('info@ecoligocollective.com', 'Ecoligo Collective'); // Reply-to address, which can be your business email

    // Optionally, send a copy to your administrative email (info@ecoligocollective.com)
    $mail->addAddress('info@ecoligocollective.com'); // Send an email to your admin email as well

    // Email content
    $mail->isHTML(true);
    $mail->Subject = "Confirmation: {$subject}"; // Subject from the form
    $mail->Body    = "<h3>Dear {$name},</h3><p>Thank you for reaching out to Ecoligo Collective. You have submitted the following message:</p><p><strong>Subject:</strong> {$subject}</p><p><strong>Your Message:</strong><br>{$message}</p><p>We will get back to you soon!</p>";
    $mail->AltBody = "Dear {$name},\n\nThank you for reaching out to Ecoligo Collective. You have submitted the following message:\n\nSubject: {$subject}\nYour Message: {$message}\n\nWe will get back to you soon!";

    // Send the email
    $mail->send();
    echo 'Message has been sent successfully';

    // Redirect to a thank you page
    header("Location: sent.html");
    exit(); // Ensure no further code is executed
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
