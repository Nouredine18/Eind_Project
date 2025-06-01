<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

function getBadgeDefinitions() {
    // Badges: min_kg_compensated is de drempelwaarde.
    // image_filename is bestandsnaam in assets/img/badges/.
    // image_url_for_email_cid is de Content-ID voor e-mail embedding.
    $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    // Basispad voor afbeeldingen, zorg dat dit klopt met je server structuur.
    $assets_base_url = $site_url . '/project/-CO2_Compensation/public_html/assets/img/badges/';


    return [
        [
            'id' => 1, // Intern ID
            'name' => 'Bronze Compensator',
            'min_kg_compensated' => 50,
            'image_filename' => 'bronze_badge.png', 
            'image_url_for_email_cid' => 'bronze_badge', 
            'description' => 'You\'ve taken your first significant step in CO2 compensation by offsetting 50kg of CO2!'
        ],
        [
            'id' => 2,
            'name' => 'Silver Compensator',
            'min_kg_compensated' => 200,
            'image_filename' => 'silver_badge.png',
            'image_url_for_email_cid' => 'silver_badge',
            'description' => 'Amazing! You\'ve offset 200kg of CO2. Your commitment is shining bright!'
        ],
        [
            'id' => 3,
            'name' => 'Gold Compensator',
            'min_kg_compensated' => 500,
            'image_filename' => 'gold_badge.png',
            'image_url_for_email_cid' => 'gold_badge',
            'description' => 'Incredible! 500kg of CO2 offset. You are a true champion for the planet!'
        ],
    ];
}

function sendBadgeNotificationEmail($conn, $user_id, $badge) {
    $sql_user = "SELECT email, first_name FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($sql_user);
    if (!$stmt_user) {
        error_log("Failed to prepare user statement for badge email: " . $conn->error);
        return false;
    }
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if (!($user = $result_user->fetch_assoc())) {
        error_log("User not found for badge email: User ID " . $user_id);
        $stmt_user->close();
        return false;
    }
    $stmt_user->close();

    $user_email = $user['email'];
    $user_first_name = $user['first_name'];

    $mail = new PHPMailer(true);
    $badge_image_path = __DIR__ . '/assets/img/badges/' . $badge['image_filename']; // Serverpad naar afbeelding
    $badge_image_cid = $badge['image_url_for_email_cid'];


    $share_text = urlencode("I just earned the " . $badge['name'] . " badge on Ecoligo Collective for my CO2 compensation efforts! Join me in making a difference. #EcoligoCollective #" . str_replace(' ', '', $badge['name']));
    $app_url = urlencode("https://ecoligocollective.com/rewards.php"); 

    $whatsapp_share = "https://wa.me/?text=" . $share_text . "%20" . $app_url;
    $twitter_share = "https://twitter.com/intent/tweet?text=" . $share_text . "&url=" . $app_url;
    // Instagram & TikTok: directe shares lastig, moedig screenshot/download aan.
    $badge_page_url = "https://ecoligocollective.com/profile.php?user_id=" . $user_id; // Link naar profiel/badge pagina

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@ecoligocollective.com';
        $mail->Password   = 'Nouredinetah18!';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        //Ontvangers
        $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective');
        $mail->addAddress($user_email, $user_first_name);

        // Inhoud
        $mail->isHTML(true);
        $mail->Subject = "Congratulations! You've earned the " . $badge['name'] . " badge!";
        
        if (file_exists($badge_image_path)) {
            $mail->addEmbeddedImage($badge_image_path, $badge_image_cid);
        } else {
            error_log("Badge image not found for email: " . $badge_image_path);
        }

        $mail->Body    = "
            <h3>Congratulations, " . htmlspecialchars($user_first_name) . "!</h3>
            <p>You've achieved a new milestone and earned the <strong>" . htmlspecialchars($badge['name']) . "</strong> badge!</p>
            <p>" . htmlspecialchars($badge['description']) . "</p>
            <div style='text-align:center; margin:20px 0;'>
                " . (file_exists($badge_image_path) ? "<img src='cid:" . $badge_image_cid . "' alt='" . htmlspecialchars($badge['name']) . "' style='max-width:150px; height:auto;'/>" : "Badge Image Here") . "
            </div>
            <h4>Share Your Achievement:</h4>
            <p>
                <a href='" . $whatsapp_share . "' target='_blank' style='padding:10px; background-color:#25D366; color:white; text-decoration:none; border-radius:5px; margin-right:10px;'>Share on WhatsApp</a>
                <a href='" . $twitter_share . "' target='_blank' style='padding:10px; background-color:#1DA1F2; color:white; text-decoration:none; border-radius:5px;'>Share on Twitter</a>
            </p>
            <p>For Instagram & TikTok, feel free to download your badge image or take a screenshot from <a href='" . $badge_page_url . "'>your profile</a> and share your success!</p>
            <p>Thank you for your commitment to a sustainable future with Ecoligo Collective!</p>";
        
        $mail->AltBody = "Congratulations, " . htmlspecialchars($user_first_name) . "!\nYou've earned the " . htmlspecialchars($badge['name']) . " badge for your CO2 compensation efforts: " . htmlspecialchars($badge['description']) . "\nShare your achievement! Visit " . $badge_page_url;

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        error_log("Badge notification email could not be sent to $user_email. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function checkAndAwardBadges($conn, $user_id) {
    $total_compensated_kg = 0;
    $sql_total_comp = "SELECT SUM(amount) AS total_compensated FROM donations WHERE user_id = ?";
    $stmt_total_comp = $conn->prepare($sql_total_comp);
    if ($stmt_total_comp) {
        $stmt_total_comp->bind_param("i", $user_id);
        $stmt_total_comp->execute();
        $result_total_comp = $stmt_total_comp->get_result()->fetch_assoc();
        $total_compensated_kg = $result_total_comp['total_compensated'] ?? 0;
        $stmt_total_comp->close();
    } else {
        error_log("Failed to prepare statement for total compensation check: " . $conn->error);
        return;
    }

    $badge_definitions = getBadgeDefinitions();
    $newly_earned_badges = [];

    foreach ($badge_definitions as $badge) {
        if ($total_compensated_kg >= $badge['min_kg_compensated']) {
            // Check of gebruiker badge al heeft om dubbele toekenning te voorkomen
            $sql_check_badge = "SELECT user_badge_id FROM user_badges WHERE user_id = ? AND badge_name = ?";
            $stmt_check_badge = $conn->prepare($sql_check_badge);
            if ($stmt_check_badge) {
                $stmt_check_badge->bind_param("is", $user_id, $badge['name']);
                $stmt_check_badge->execute();
                $result_check_badge = $stmt_check_badge->get_result();
                
                if ($result_check_badge->num_rows == 0) {
                    // Gebruiker heeft badge nog niet: toekennen
                    $sql_award_badge = "INSERT INTO user_badges (user_id, badge_name, badge_image_url) VALUES (?, ?, ?)";
                    $stmt_award_badge = $conn->prepare($sql_award_badge);
                    if ($stmt_award_badge) {
                        // Publiek toegankelijke URL voor de badge afbeelding
                        $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
                        // Pas dit pad eventueel aan je serverconfiguratie aan
                        $public_image_url = $site_url . '/project/-CO2_Compensation/public_html/assets/img/badges/' . $badge['image_filename'];

                        $stmt_award_badge->bind_param("iss", $user_id, $badge['name'], $public_image_url);
                        if ($stmt_award_badge->execute()) {
                            $newly_earned_badges[] = $badge; 
                        } else {
                            error_log("Failed to award badge '" . $badge['name'] . "' to user $user_id: " . $stmt_award_badge->error);
                        }
                        $stmt_award_badge->close();
                    } else {
                        error_log("Failed to prepare statement for awarding badge: " . $conn->error);
                    }
                }
                $stmt_check_badge->close();
            } else {
                 error_log("Failed to prepare statement for checking badge: " . $conn->error);
            }
        }
    }

    foreach ($newly_earned_badges as $earned_badge) {
        sendBadgeNotificationEmail($conn, $user_id, $earned_badge);
    }
}
?>
