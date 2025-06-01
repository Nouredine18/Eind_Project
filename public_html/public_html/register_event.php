<?php
session_start();
include 'connect.php'; 
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    $_SESSION['event_message'] = ['type' => 'danger', 'text' => 'You must be logged in to manage event registrations.'];
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? 'register'; // Standaard op registreren

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['register_event_id']) || isset($_POST['unregister_event_id']))) {
    $event_id = filter_var($_POST['register_event_id'] ?? $_POST['unregister_event_id'], FILTER_VALIDATE_INT);

    if (!$event_id) {
        $_SESSION['event_message'] = ['type' => 'danger', 'text' => 'Invalid event ID.'];
        header('Location: events_workshops.php');
        exit();
    }

    // Haal evenementdetails op
    $sql_event = "SELECT title, event_date, event_time, location, registration_deadline FROM events WHERE event_id = ? AND is_active = TRUE";
    $stmt_event = $conn->prepare($sql_event);
    $stmt_event->bind_param("i", $event_id);
    $stmt_event->execute();
    $result_event = $stmt_event->get_result();
    $event_details = $result_event->fetch_assoc();
    $stmt_event->close();

    if (!$event_details) {
        $_SESSION['event_message'] = ['type' => 'danger', 'text' => 'Event not found or no longer active.'];
        header('Location: events_workshops.php');
        exit();
    }
    
    // Haal e-mail en naam van gebruiker op
    $sql_user = "SELECT email, first_name, last_name FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $current_user_id);
    $stmt_user->execute();
    $user_info = $stmt_user->get_result()->fetch_assoc();
    $stmt_user->close();

    if (!$user_info) {
        $_SESSION['event_message'] = ['type' => 'danger', 'text' => 'User details not found. Cannot proceed.'];
        header('Location: events_workshops.php');
        exit();
    }

    if ($action === 'register') {
        // Controleer registratiedeadline
        if ($event_details['registration_deadline'] && strtotime($event_details['registration_deadline']) < time()) {
            $_SESSION['event_message'] = ['type' => 'warning', 'text' => 'The registration deadline for this event has passed.'];
            header('Location: events_workshops.php');
            exit();
        }

        // Controleer of al geregistreerd
        $sql_check_reg = "SELECT registration_id FROM user_event_registrations WHERE user_id = ? AND event_id = ?";
        $stmt_check_reg = $conn->prepare($sql_check_reg);
        $stmt_check_reg->bind_param("ii", $current_user_id, $event_id);
        $stmt_check_reg->execute();
        $result_check_reg = $stmt_check_reg->get_result();

        if ($result_check_reg->num_rows > 0) {
            $_SESSION['event_message'] = ['type' => 'info', 'text' => 'You are already registered for this event.'];
            header('Location: events_workshops.php');
            exit();
        }
        $stmt_check_reg->close();

        // Ga verder met registratie
        $sql_register = "INSERT INTO user_event_registrations (user_id, event_id) VALUES (?, ?)";
        $stmt_register = $conn->prepare($sql_register);
        $stmt_register->bind_param("ii", $current_user_id, $event_id);

        if ($stmt_register->execute()) {
            // Stuur bevestigingsmail
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.hostinger.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'info@ecoligocollective.com';
                $mail->Password   = 'Nouredinetah18!';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective Events');
                $mail->addAddress($user_info['email'], $user_info['first_name'] . ' ' . $user_info['last_name']);
                $mail->addReplyTo('info@ecoligocollective.com', 'Ecoligo Collective');

                $mail->isHTML(true);
                $mail->Subject = 'Event Registration Confirmation: ' . htmlspecialchars($event_details['title']);
                $formatted_date = date("F j, Y", strtotime($event_details['event_date']));
                $mail->Body    = "<h3>Dear " . htmlspecialchars($user_info['first_name']) . ",</h3>
                                  <p>Thank you for registering for the event: <strong>" . htmlspecialchars($event_details['title']) . "</strong>.</p>
                                  <p><strong>Event Details:</strong></p>
                                  <ul>
                                    <li>Date: " . htmlspecialchars($formatted_date) . "</li>
                                    <li>Time: " . htmlspecialchars($event_details['event_time']) . "</li>
                                    <li>Location: " . htmlspecialchars($event_details['location']) . "</li>
                                  </ul>
                                  <p>We look forward to seeing you there!</p>
                                  <p>If you have any questions, please contact us.</p>
                                  <p>Best regards,<br>The Ecoligo Collective Team</p>";
                $mail->AltBody = "Dear " . htmlspecialchars($user_info['first_name']) . ",\n\nThank you for registering for the event: " . htmlspecialchars($event_details['title']) . ".\n\nEvent Details:\nDate: " . htmlspecialchars($formatted_date) . "\nTime: " . htmlspecialchars($event_details['event_time']) . "\nLocation: " . htmlspecialchars($event_details['location']) . "\n\nWe look forward to seeing you there!\nIf you have any questions, please contact us.\n\nBest regards,\nThe Ecoligo Collective Team";

                $mail->send();
                $_SESSION['event_message'] = ['type' => 'success', 'text' => 'Successfully registered for "' . htmlspecialchars($event_details['title']) . '". A confirmation email has been sent.'];
            } catch (Exception $e) {
                $_SESSION['event_message'] = ['type' => 'success', 'text' => 'Successfully registered for "' . htmlspecialchars($event_details['title']) . '". However, the confirmation email could not be sent. Mailer Error: ' . $mail->ErrorInfo];
                error_log("Mailer Error for event registration (User ID: $current_user_id, Event ID: $event_id): " . $mail->ErrorInfo);
            }
        } else {
            $_SESSION['event_message'] = ['type' => 'danger', 'text' => 'Failed to register for the event. Please try again. Error: ' . $stmt_register->error];
            error_log("Failed to register user $current_user_id for event $event_id: " . $stmt_register->error);
        }
        $stmt_register->close();

    } elseif ($action === 'unregister') {
        // Controleer of de registratiedeadline is verstreken voor uitschrijven (optioneel, meestal kan men zich op elk moment uitschrijven)
        // In dit voorbeeld staan we uitschrijven toe, zelfs na de deadline, maar je kunt hier een controle toevoegen.

        $sql_unregister = "DELETE FROM user_event_registrations WHERE user_id = ? AND event_id = ?";
        $stmt_unregister = $conn->prepare($sql_unregister);
        $stmt_unregister->bind_param("ii", $current_user_id, $event_id);

        if ($stmt_unregister->execute()) {
            if ($stmt_unregister->affected_rows > 0) {
                // Stuur annuleringsmail
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.hostinger.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'info@ecoligocollective.com';
                    $mail->Password   = 'Nouredinetah18!';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;

                    $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective Events');
                    $mail->addAddress($user_info['email'], $user_info['first_name'] . ' ' . $user_info['last_name']);
                    $mail->addReplyTo('info@ecoligocollective.com', 'Ecoligo Collective');
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Event Registration Cancelled: ' . htmlspecialchars($event_details['title']);
                    $formatted_date = date("F j, Y", strtotime($event_details['event_date']));
                    $mail->Body    = "<h3>Dear " . htmlspecialchars($user_info['first_name']) . ",</h3>
                                      <p>Your registration for the event: <strong>" . htmlspecialchars($event_details['title']) . "</strong> has been successfully cancelled.</p>
                                      <p><strong>Event Details:</strong></p>
                                      <ul>
                                        <li>Date: " . htmlspecialchars($formatted_date) . "</li>
                                        <li>Time: " . htmlspecialchars($event_details['event_time']) . "</li>
                                        <li>Location: " . htmlspecialchars($event_details['location']) . "</li>
                                      </ul>
                                      <p>We hope to see you at future events.</p>
                                      <p>Best regards,<br>The Ecoligo Collective Team</p>";
                    $mail->AltBody = "Dear " . htmlspecialchars($user_info['first_name']) . ",\n\nYour registration for the event: " . htmlspecialchars($event_details['title']) . " has been successfully cancelled.\n\nEvent Details:\nDate: " . htmlspecialchars($formatted_date) . "\nTime: " . htmlspecialchars($event_details['event_time']) . "\nLocation: " . htmlspecialchars($event_details['location']) . "\n\nWe hope to see you at future events.\n\nBest regards,\nThe Ecoligo Collective Team";

                    $mail->send();
                    $_SESSION['event_message'] = ['type' => 'success', 'text' => 'Successfully unregistered from "' . htmlspecialchars($event_details['title']) . '". A cancellation email has been sent.'];
                } catch (Exception $e) {
                    $_SESSION['event_message'] = ['type' => 'success', 'text' => 'Successfully unregistered from "' . htmlspecialchars($event_details['title']) . '". However, the cancellation email could not be sent. Mailer Error: ' . $mail->ErrorInfo];
                    error_log("Mailer Error for event unregistration (User ID: $current_user_id, Event ID: $event_id): " . $mail->ErrorInfo);
                }
            } else {
                 $_SESSION['event_message'] = ['type' => 'info', 'text' => 'You were not registered for this event, or unregistration failed.'];
            }
        } else {
            $_SESSION['event_message'] = ['type' => 'danger', 'text' => 'Failed to unregister from the event. Please try again. Error: ' . $stmt_unregister->error];
            error_log("Failed to unregister user $current_user_id from event $event_id: " . $stmt_unregister->error);
        }
        $stmt_unregister->close();
    }

} else {
    $_SESSION['event_message'] = ['type' => 'danger', 'text' => 'Invalid request.'];
}

header('Location: events_workshops.php');
exit();
?>
