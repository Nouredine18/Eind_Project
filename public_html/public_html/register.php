<?php
include('connect.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = connectToDatabase($host, $username, $password, $database);

    // Sanitizeer en valideer input
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $verificationToken = bin2hex(random_bytes(32)); // Genereer een veilige token van 64 tekens
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $security_question = trim($_POST['security_question']);
    $security_answer = password_hash(trim($_POST['security_answer']), PASSWORD_BCRYPT);

    // Stap 1: Valideer e-mail syntaxis en MX-records
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?message=Invalid email format.&type=error");
        exit();
    }

    if (!checkMXRecords($email)) {
        header("Location: login.php?message=Email domain is not valid.&type=error");
        exit();
    }

    // Controleer of het e-mailadres al bestaat
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: login.php?message=This email is already registered.&type=error");
        exit();
    } else {
        // Voeg gebruiker toe aan de database met de verificatietoken
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO users (email, password, verification_token, email_verified, first_name, last_name, security_question, security_answer_hash) VALUES (?, ?, ?, 0, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $email, $hashedPassword, $verificationToken, $first_name, $last_name, $security_question, $security_answer);

        if ($stmt->execute()) {
            // Stuur de verificatie-e-mail
            if (sendVerificationEmail($email, $verificationToken, $security_question, $security_answer)) {
                header("Location: login.php?message=Registration successful! Please check your email to verify your account.&type=success");
            } else {
                header("Location: login.php?message=Error: Could not send verification email.&type=error");
            }
        } else {
            header("Location: login.php?message=Error: Could not register user.&type=error");
            error_log("Database Error: " . $stmt->error); // Log eventuele databasefouten
        }
        $stmt->close();
    }

    $conn->close();
}

// Functie om de verificatie-e-mail te sturen
function sendVerificationEmail($email, $token, $security_question, $security_answer) {
    $mail = new PHPMailer(true);

    try {
        // Serverinstellingen
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@ecoligocollective.com';
        $mail->Password   = 'Nouredinetah18!';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Afzender- en ontvangerinstellingen
        $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective');
        $mail->addAddress($email);

        // E-mailinhoud
        $verificationLink = "http://ecoligocollective.com/verify.php?email={$email}&token={$token}";
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification - Ecoligo Collective';
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
                    <h3>Welcome to Ecoligo Collective!</h3>
                    <p>Please verify your email address by clicking the link below:</p>
                    <a href='{$verificationLink}'>Verify Email</a>
                    <p>Your security question is: {$security_question}</p>
                    <p>Your security answer is: {$security_answer}</p>
                </div>
            </body>
            </html>";
        $mail->AltBody = "Please verify your email by clicking the link: {$verificationLink}";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo); // Log e-mailfouten
        return false;
    }
}

// Functie om MX-records te controleren
function checkMXRecords($email) {
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, 'MX');
}
?>
