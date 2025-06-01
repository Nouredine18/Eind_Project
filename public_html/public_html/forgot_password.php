<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Laad PHPMailer's autoload bestand

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Ga ervan uit dat je een databaseverbinding hebt
    include('connect.php');

    // Genereer een veilige token en stel vervaltijd in (1 uur)
    $reset_token = bin2hex(random_bytes(32));  // Genereer een veilige, willekeurige token
    $reset_token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));  // Token vervalt over 1 uur

    // Sla de token en vervaltijd op in de database
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
    $stmt->bind_param("sss", $reset_token, $reset_token_expiry, $email);
    $stmt->execute();

    // Check of de update succesvol was
    if ($stmt->affected_rows > 0) {
        try {
            $mail = new PHPMailer(true);

            // Server instellingen
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'info@ecoligocollective.com';
            $mail->Password   = 'Nouredinetah18!';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Afzender en ontvanger
            $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective');
            $mail->addAddress($email);  // E-mailadres van de gebruiker

            // E-mail inhoud
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "
                <html>
                <head>
                    <style>
                        .email-container {
                            font-family: 'Montserrat', sans-serif;
                            background-color: #f6f5f7;
                            padding: 20px;
                            border-radius: 10px;
                            text-align: center;
                        }
                        .email-container h3 {
                            color: #FF4B2B;
                        }
                        .email-container p {
                            font-size: 14px;
                            color: #333;
                        }
                        .email-container a {
                            display: inline-block;
                            padding: 10px 20px;
                            margin-top: 20px;
                            background-color: #FF4B2B;
                            color: #fff;
                            text-decoration: none;
                            border-radius: 5px;
                        }
                    </style>
                </head>
                <body>
                    <div class='email-container'>
                        <h3>Password Reset Request</h3>
                        <p>Click the following link to reset your password:</p>
                        <a href='http://ecoligocollective.com/reset_password.php?token={$reset_token}'>Reset Password</a>
                    </div>
                </body>
                </html>";
            $mail->AltBody = "Click the following link to reset your password: http://ecoligocollective.com/reset_password.php?token={$reset_token}";

            // Verstuur de e-mail
            $mail->send();
            $message = 'Password reset email has been sent.';
            $message_type = 'success';
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $message_type = 'error';
        }
    } else {
        $message = "No user found with that email address.";
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/forgot_password.css"> <!-- Link to the new CSS file -->
    <title>Document</title>
</head>
<body>
    
<form method="POST">
    <label for="email">Enter your email to reset password:</label>
    <input type="email" id="email" name="email" required>
    <button type="submit">Send Reset Link</button>
    <p><a href="login.php">Go back to login page</a></p> <!-- Moved link here -->
</form>

<?php if (!empty($message)): ?>
    <div class="message message-<?php echo htmlspecialchars($message_type); ?>">
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

</body>
</html>

