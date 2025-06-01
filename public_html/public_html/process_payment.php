<?php
session_start();
include 'connect.php';
<<<<<<< Updated upstream
require 'vendor/autoload.php'; // Ensure you have installed PHPMailer and TCPDF
=======
require 'vendor/autoload.php'; // Zorg ervoor dat je PHPMailer hebt geïnstalleerd
>>>>>>> Stashed changes

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use TCPDF;

// Zorg dat de gebruiker ingelogd is
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0.0;
$payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';

<<<<<<< Updated upstream
// Fetch user's email and name from the database
=======
if ($project_id <= 0 || $amount <= 0 || empty($payment_method)) {
    die("Invalid project ID, amount, or payment method.");
}

// Haal e-mail en naam van gebruiker op uit de database
>>>>>>> Stashed changes
$sql = "SELECT email, first_name, last_name FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

$user_email = $user['email'];
$user_name = $user['first_name'] . ' ' . $user['last_name'];

// Haal projectnaam op uit de database
$sql = "SELECT name FROM compensationprojects WHERE project_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

if (!$project) {
    die("Project not found.");
}

$project_name = $project['name'];

<<<<<<< Updated upstream
// Validate input
if ($project_id > 0 && $amount > 0 && !empty($payment_method)) {
    // Insert the donation record
    $sql = "INSERT INTO donations (user_id, project_id, amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("iid", $user_id, $project_id, $amount);
    if ($stmt->execute()) {
        // Generate PDF certificate
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $certificate_id = uniqid('cert_');
        $signature_path = __DIR__ . '/assets/img/examples/signature.svg';
        $html = "
            <html lang='en'>
            <head>
              <meta charset='UTF-8'>
              <meta name='viewport' content='width=device-width, initial-scale=1.0'>
              <title>Certificate of Achievement</title>
            </head>
            <body style='font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f9f9f9; display: flex; justify-content: center; align-items: center; min-height: 100vh;'>
              <div style='width: 800px; background: #fff; border: 5px solid #d4af37; border-radius: 10px; padding: 20px; text-align: center; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); position: relative;'>
                <div style='margin-bottom: 20px;'>
                  <img src='logo.png' alt='Ecoligo Collective Logo' style='width: 50px;'>
                  <h1>Certificate of Donation</h1>
                </div>
                <div style='width: 150px; height: 150px; background: radial-gradient(circle, #f9e7c7, #d4af37); border: 5px solid #d4af37; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 20px auto; position: relative; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);'>
                  <div style='text-align: center; font-size: 16px; font-weight: bold; color: #000;'>
                    <span>CO2 Compensation</span>
                    <span>" . date('F, Y') . "</span>
                  </div>
                </div>
                <p style='font-size: 18px; margin: 10px 0; color: #555;'>This Certificate is Awarded to</p>
                <h2 style='font-family: Cursive, sans-serif; font-size: 32px; margin: 10px 0; color: #000;'>$user_name</h2>
                <p style='font-size: 16px; color: #333; margin: 20px 0;'>Thank you for your generous donation to the $project_name project. Your contribution of $$amount helps us make a significant impact.</p>
                <div style='display: flex; justify-content: space-around; margin-top: 30px;'>
                  <div style='text-align: center;'>
                    <img src='$signature_path' alt='Signature' style='width: 100px;'>
                    <p>Nouredine Tahrioui</p>
                    <span style='display: block; font-size: 14px; color: #666; margin-top: 5px;'>Manager</span>
                  </div>
                </div>
              </div>
            </body>
            </html>
        ";
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf_file = 'certificate_' . $user_id . '_' . time() . '.pdf';
        $pdf->Output(__DIR__ . '/' . $pdf_file, 'F');

        // Send email with PHPMailer
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
            $mail->addAddress($user_email); // Add the user's email as the recipient

            // Attachments
            $mail->addAttachment(__DIR__ . '/' . $pdf_file);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Donation Invoice and Certificate';
            $mail->Body = "
                <h1>Thank you for your donation!</h1>
                <p>Attached is your invoice and certificate of donation.</p>
            ";

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        // Redirect to a success page or project details page
        header('Location: project_details.php?project_id=' . $project_id);
        exit();
    } else {
        die("Error executing statement: " . $stmt->error);
    }
    $stmt->close();
=======
// Stuur door naar de juiste betaalpagina
if ($payment_method === 'stripe') {
    header('Location: stripe_payment.php?project_id=' . $project_id . '&amount=' . $amount);
    exit();
} elseif ($payment_method === 'paypal') {
    header('Location: paypal_payment.php?project_id=' . $project_id . '&amount=' . $amount);
    exit();
>>>>>>> Stashed changes
} else {
    die("Invalid input.");
}
?>
