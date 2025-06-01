<?php
include 'connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$today = date('Y-m-d');
$midMonth = date('Y-m-d', strtotime('+15 days'));
$endMonth = date('Y-m-d', strtotime('+1 month'));

$sql = "SELECT s.user_id, s.project_id, s.next_payment_date, cp.name AS project_name, u.email
        FROM subscriptions s
        JOIN compensationprojects cp ON s.project_id = cp.project_id
        JOIN users u ON s.user_id = u.user_id
        WHERE s.next_payment_date IN ('$today', '$midMonth', '$endMonth')";
$result = $conn->query($sql);

echo "<pre>";
print_r($result->fetch_all(MYSQLI_ASSOC));
echo "</pre>";

if ($result->num_rows > 0) {
    $result->data_seek(0); // resultaat pointer terug naar begin
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
        $project_name = $row['project_name'];
        $next_payment_date = $row['next_payment_date'];
        $email = $row['email'];

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
            $mail->Subject = 'Subscription Payment Reminder';
            $mail->Body = "Reminder: Your subscription for the project '$project_name' is due on $next_payment_date.";

            $mail->send();
            echo 'Message has been sent to ' . $email . '<br>';
        } catch (Exception $e) {
            echo "Message could not be sent to $email. Mailer Error: {$mail->ErrorInfo}<br>";
            error_log("Mailer Error: " . $mail->ErrorInfo); 
        }
    }
} else {
    echo "No subscriptions due today.";
}
?>
