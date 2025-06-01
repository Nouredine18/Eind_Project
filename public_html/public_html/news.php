<?php
include 'connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Haal het nieuwste compensatieproject op
$sql = "SELECT * FROM compensationprojects ORDER BY created_at DESC LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $project = $result->fetch_assoc();
    $project_name = $project['name'];
    $project_description = $project['description'];

    // Haal alle gebruikers op
    $sql = "SELECT email, first_name, last_name FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($user = $result->fetch_assoc()) {
            $user_email = $user['email'];
            $user_name = $user['first_name'] . ' ' . $user['last_name'];

            // Stuur e-mailnotificatie
            $mail = new PHPMailer(true);
            try {
                //Serverinstellingen
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com'; // Stel de SMTP-server in om via te verzenden
                $mail->SMTPAuth = true;
                $mail->Username = 'info@ecoligocollective.com'; // SMTP-gebruikersnaam
                $mail->Password = 'Nouredinetah18!'; // SMTP-wachtwoord
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                //Ontvangers
                $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective');
                $mail->addAddress($user_email, $user_name);

                // Inhoud
                $mail->isHTML(true);
                $mail->Subject = 'New Compensation Project Added';
                $mail->Body    = "Dear $user_name,<br><br>We are excited to announce a new compensation project: '$project_name'.<br><br>$project_description<br><br>Best regards,<br>CO2 Compensation Team";

                $mail->send();
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
} else {
    echo "No new compensation project found.";
}

// Stuur terug naar add_compensation_project.php
header('Location: add_compensation_project.php');
exit();
?>
