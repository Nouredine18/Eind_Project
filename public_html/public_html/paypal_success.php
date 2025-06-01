<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

<<<<<<< Updated upstream
=======
include 'connect.php';
require 'vendor/autoload.php'; 
require 'generate_certificate.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest; // Toegevoegd voor het vastleggen van de bestelling

// Initialiseer variabelen voor de HTML-template
$paymentSuccess = false;
$user_name_for_page = '';
$user_email_for_page = '';
$project_name_for_page = '';
$amount_for_page = 0;
$paypal_capture_error = ''; // Om specifieke PayPal-vastlegfouten op te slaan

// Valideer GET-parameters
>>>>>>> Stashed changes
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$paypalOrderId = isset($_GET['token']) ? $_GET['token'] : null; // PayPal voegt 'token' toe, wat de Order ID is
$payerId = isset($_GET['PayerID']) ? $_GET['PayerID'] : null; // PayPal voegt ook PayerID toe

<<<<<<< Updated upstream
// Include PayPal SDK
require 'vendor/autoload.php';

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

// Set up PayPal API context
$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        'YOUR_PAYPAL_CLIENT_ID',     // ClientID
        'YOUR_PAYPAL_CLIENT_SECRET'  // ClientSecret
    )
);
=======
if ($amount <= 0 || $project_id <= 0 || empty($paypalOrderId) || empty($payerId)) {
    error_log("paypal_success.php: Invalid parameters. Amount: $amount, Project ID: $project_id, Token: $paypalOrderId, PayerID: $payerId");
    echo '<div style="text-align: center; margin-top: 20px;">
            <a href="home.php" style="text-decoration: none; color: white; background-color: #007BFF; padding: 10px 20px; border-radius: 5px;">Go Back to Home</a>
          </div>';
    die("Invalid payment parameters received from PayPal.");
}
$amount_for_page = $amount;

// API-gegevens
$clientId = 'AUXm42rXnu72q2qaEpJ3BnHIXoY1_6rJ3l3BYXlNRorp6TfZXCW53js36gPCCYjbOEc_yDjBKhKSqYMK';
$clientSecret = 'EPXABRvirlS_t8j6afINfcJCfVuy51rMj6FVbMTQDnoxp687TJtwu4xgQnxrUmmKr8yZehCbarHabOJH';
$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);
>>>>>>> Stashed changes


if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    $payment = Payment::get($paymentId, $apiContext);
    $execution = new PaymentExecution();
    $execution->setPayerId($payerId);

    try {
<<<<<<< Updated upstream
        $result = $payment->execute($execution, $apiContext);
        $paymentSuccess = true;
    } catch (Exception $ex) {
        die($ex);
=======
        error_log("paypal_success.php: Attempting to capture PayPal Order ID: $paypalOrderId for user_id: $userId");
        
        // Maak een verzoek om de bestelling vast te leggen
        $request = new OrdersCaptureRequest($paypalOrderId);
        $request->prefer('return=representation');
        
        // Voer het verzoek uit
        $response = $client->execute($request);
        
        // Controleer of het vastleggen succesvol was
        if ($response->statusCode == 201 || $response->statusCode == 200) { // 201 CREATED for capture, 200 OK if already captured
            $paypal_order_status = $response->result->status;
            if ($paypal_order_status == 'COMPLETED') {
                error_log("paypal_success.php: PayPal Order ID: $paypalOrderId CAPTURED successfully. Status: $paypal_order_status");
                $paymentSuccess = true; // Betaling succesvol vastgelegd
            } else {
                // Als status niet COMPLETED is (bijv. PENDING, etc.)
                $paypal_capture_error = "PayPal payment status is $paypal_order_status, not COMPLETED.";
                error_log("paypal_success.php: PayPal Order ID: $paypalOrderId capture status: $paypal_order_status. Error: $paypal_capture_error");
                $paymentSuccess = false;
            }
        } else {
            $paypal_capture_error = "Failed to capture PayPal payment. Status Code: " . $response->statusCode;
            error_log("paypal_success.php: PayPal Order ID: $paypalOrderId. Error: $paypal_capture_error. Response: " . json_encode($response));
            $paymentSuccess = false;
        }

        if ($paymentSuccess) {
            error_log("paypal_success.php: Starting post-payment processing for user_id: $userId, project_id: $project_id, amount: $amount");

            // Haal e-mail en naam van gebruiker op uit de database
            error_log("paypal_success.php: Attempting to fetch user details.");
            $sql_user = "SELECT email, first_name, last_name FROM users WHERE user_id = ?";
            $stmt_user = $conn->prepare($sql_user);
            if ($stmt_user === false) {
                throw new Exception("Error preparing statement for fetching user: " . $conn->error);
            }
            $stmt_user->bind_param("i", $userId);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();
            $user = $result_user->fetch_assoc();
            $stmt_user->close();

            if (!$user) {
                throw new Exception("User not found with ID: " . $userId);
            }
            $user_email_for_page = $user['email'];
            $user_name_for_page = $user['first_name'] . ' ' . $user['last_name'];
            error_log("paypal_success.php: Successfully fetched user details for: " . $user_name_for_page);

            // Haal projectnaam op uit de database
            error_log("paypal_success.php: Attempting to fetch project details.");
            $sql_project = "SELECT name FROM compensationprojects WHERE project_id = ?";
            $stmt_project = $conn->prepare($sql_project);
            if ($stmt_project === false) {
                throw new Exception("Error preparing statement for fetching project: " . $conn->error);
            }
            $stmt_project->bind_param("i", $project_id);
            $stmt_project->execute();
            $result_project = $stmt_project->get_result();
            $project = $result_project->fetch_assoc();
            $stmt_project->close();

            if (!$project) {
                throw new Exception("Project not found with ID: " . $project_id);
            }
            $project_name_for_page = $project['name'];
            error_log("paypal_success.php: Successfully fetched project: " . $project_name_for_page);

            // Voeg donatiedetails toe aan de database - AANGEPAST om overeen te komen met Stripe-flow
            error_log("paypal_success.php: Attempting to insert into donations table.");
            $sql_donation = "INSERT INTO donations (user_id, project_id, amount, donation_date) VALUES (?, ?, ?, NOW())"; // payment_id verwijderd
            $stmt_donation = $conn->prepare($sql_donation);
            if ($stmt_donation === false) {
                throw new Exception("Error preparing statement for inserting donation: " . $conn->error);
            }
            // Bind parameters: user_id, project_id, bedrag
            $stmt_donation->bind_param("iid", $userId, $project_id, $amount); // Binding aangepast
            if (!$stmt_donation->execute()) {
                throw new Exception("Error executing statement for inserting donation: " . $stmt_donation->error);
            }
            $stmt_donation->close();
            error_log("paypal_success.php: Successfully inserted into donations table.");

            // Bereken en ken punten toe
            error_log("paypal_success.php: Attempting to update user points.");
            $points_earned = $amount * 1.32;
            $sql_update_points = "UPDATE users SET total_points = total_points + ? WHERE user_id = ?";
            $stmt_update_points = $conn->prepare($sql_update_points);
            if ($stmt_update_points) {
                $stmt_update_points->bind_param("di", $points_earned, $userId);
                $stmt_update_points->execute();
                $stmt_update_points->close();
                $_SESSION['total_points'] = ($_SESSION['total_points'] ?? 0) + $points_earned;
                error_log("paypal_success.php: Successfully updated user points.");
            } else {
                // Dit else-blok wordt mogelijk niet bereikt als prepare mislukt, omdat het een fout zou genereren als $conn->error wordt gecontroleerd.
                // Echter, als prepare false retourneert en er geen fout is ingesteld op $conn, kan dit log nuttig zijn.
                error_log("paypal_success.php: Error preparing statement to update points: " . $conn->error);
            }

            // Genereer het certificaat
            error_log("paypal_success.php: Attempting to generate certificate.");
            $certificatePath = 'certificates/' . uniqid() . '.pdf';
            generateCertificate($user_name_for_page, $project_name_for_page, $amount, $certificatePath);
            error_log("paypal_success.php: Certificate supposedly generated at: " . $certificatePath);

            // Stuur e-mail met certificaat
            error_log("paypal_success.php: Attempting to send email.");
            $mail = new PHPMailer(true);
            try {
                // Serverinstellingen
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'info@ecoligocollective.com'; 
                $mail->Password = 'Nouredinetah18!'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                //Ontvangers
                $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective');
                $mail->addAddress($user_email_for_page, $user_name_for_page);

                // Inhoud
                $mail->isHTML(true);
                $mail->Subject = 'Payment Successful - Certificate'; // Verwijderd (Gesimuleerd)
                $mail->Body    = "Dear $user_name_for_page,<br><br>Thank you for your payment of €$amount for the project '$project_name_for_page'.<br><br>Attached is your certificate.<br><br>Best regards,<br>CO2 Compensation Team";
                $mail->addAttachment($certificatePath);
                $mail->send();
                error_log("paypal_success.php: Email sent successfully to: " . $user_email_for_page);
                
            } catch (PHPMailerException $e_mail) {
                error_log("paypal_success.php: Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                // Bepaal of dit $paymentSuccess false moet maken of alleen de fout moet loggen
                // Voor nu gaan we ervan uit dat een e-mailfout niet betekent dat de betaling is mislukt als het vastleggen OK was.
            } finally {
                if (file_exists($certificatePath)) {
                    unlink($certificatePath); 
                }
            }
        } 
    } catch (Exception $ex) { // Vangt uitzonderingen op van PayPal SDK, DB-operaties, certificaat, etc.
        error_log("paypal_success.php: Processing error: " . $ex->getMessage() . " | PayPal Order ID: " . ($paypalOrderId ?? 'N/A') . " | Trace: " . $ex->getTraceAsString());
        if (empty($paypal_capture_error)) { // Als er geen specifieke PayPal-fout is, gebruik dan een generieke
             $paypal_capture_error = "An internal error occurred: " . $ex->getMessage();
        }
        $paymentSuccess = false; 
>>>>>>> Stashed changes
    }
} else {
    error_log("paypal_success.php: Payment failed: Missing user_id in session. PayPal Order ID: " . ($paypalOrderId ?? 'N/A'));
    $paypal_capture_error = "User session expired or not found.";
    $paymentSuccess = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Status</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; text-align: center; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); max-width: 500px; width: 100%; }
        .checkmark { width: 100px; height: 100px; border-radius: 50%; display: block; stroke-width: 2; stroke: #4CAF50; stroke-miterlimit: 10; margin: 20px auto; box-shadow: inset 0px 0px 0px #4CAF50; animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; }
        .checkmark__circle { stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 2; stroke-miterlimit: 10; stroke: #4CAF50; fill: #fff; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards; }
        .checkmark__check { transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.6s forwards; }
        @keyframes stroke { 100% { stroke-dashoffset: 0; } }
        @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
        @keyframes fill { 100% { box-shadow: inset 0px 0px 0px 30px #4CAF50; } }
        h2 { margin-top: 0; }
        p { color: #555; line-height: 1.6; }
        .error-icon { color: red; font-size: 50px; margin-bottom: 20px; }
        .button { text-decoration: none; color: white; background-color: #007BFF; padding: 10px 20px; border-radius: 5px; display: inline-block; margin-top: 20px; }
        .button:hover { background-color: #0056b3; }
    </style>
    <?php if ($paymentSuccess): ?>
    <script>
        setTimeout(function() {
            window.location.href = 'compensation_projects.php';
        }, 4000);
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <?php if ($paymentSuccess): ?>
            <div class="checkmark">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            <h2>Payment Successful!</h2>
            <p>Dear <?php echo htmlspecialchars($user_name_for_page); ?>,</p>
            <p>Thank you for your payment of €<?php echo htmlspecialchars(number_format($amount_for_page, 2)); ?> for the project '<?php echo htmlspecialchars($project_name_for_page); ?>'.</p>
            <p>Your certificate has been sent to <?php echo htmlspecialchars($user_email_for_page); ?>.</p>
            <p>You will be redirected shortly.</p>
        <?php else: ?>
            <div class="error-icon">⚠️</div>
            <h2 style="color: red;">Payment Failed!</h2>
            <p>We encountered an issue while processing your PayPal payment.</p>
            <?php if (!empty($paypal_capture_error)): ?>
                <p><strong>Details:</strong> <?php echo htmlspecialchars($paypal_capture_error); ?></p>
            <?php endif; ?>
            <p>If the problem persists, please contact our support team.</p>
        <?php endif; ?>
        <a href="home.php" class="button">Go Back to Home</a>
    </div>
</body>
</html>