<?php
session_start();
include 'connect.php'; // Establishes $conn

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure this path is correct

// Function to generate a random code
function generateRandomCode($length = 15) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{}|;:,.<>?';
    $charactersLength = strlen($characters);
    $randomCode = '';
    for ($i = 0; $i < $length; $i++) {
        $randomCode .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomCode;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$user_points = 0;

// Fetch current user's points
$sql_user_points = "SELECT total_points FROM users WHERE user_id = ?";
$stmt_user_points = $conn->prepare($sql_user_points);
if ($stmt_user_points) {
    $stmt_user_points->bind_param("i", $current_user_id);
    $stmt_user_points->execute();
    $result_user_points = $stmt_user_points->get_result();
    if ($row = $result_user_points->fetch_assoc()) {
        $user_points = $row['total_points'];
    }
    $stmt_user_points->close();
}

// Define available rewards (static for now)
// US072: Als gebruiker wil ik punten kunnen inwisselen voor beloningen zoals kortingen op parkeerplaatsen zodat ik voordeel krijg voor mijn bijdragen.
// US073: Als gebruiker wil ik toegang hebben tot exclusieve beloningen zoals toegang tot luchthavenlounges zodat ik gemotiveerd word om meer te compenseren.
$rewards = [
    [
        'id' => 1,
        'name' => 'Parking Discount Voucher',
        'description' => 'Get a 10% discount on your next airport parking booking. A small token for your contribution.',
        'points_required' => 50,
        'type' => 'Discount',
        'icon' => 'fas fa-parking' // Font Awesome icon class
    ],
    [
        'id' => 2,
        'name' => 'Airport Lounge Access Pass',
        'description' => 'Enjoy complimentary access to an exclusive airport lounge on your next trip. Travel in style!',
        'points_required' => 200,
        'type' => 'Exclusive Access',
        'icon' => 'fas fa-couch' // Font Awesome icon class
    ],
    [
        'id' => 3,
        'name' => 'Eco-Friendly Travel Kit',
        'description' => 'Receive a travel kit with sustainable items like a reusable water bottle and bamboo toothbrush.',
        'points_required' => 100,
        'type' => 'Merchandise',
        'icon' => 'fas fa-leaf' // Font Awesome icon class
    ],
    [
        'id' => 4,
        'name' => 'Carbon Offset Contribution Boost',
        'description' => 'We\'ll double your next 50 points earned towards carbon offset projects.',
        'points_required' => 75,
        'type' => 'Contribution Boost',
        'icon' => 'fas fa-seedling'
    ]
];

// Handle redemption
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_reward_id'])) {
    $reward_id_to_redeem = (int)$_POST['redeem_reward_id'];
    $selected_reward = null;
    foreach ($rewards as $reward) {
        if ($reward['id'] === $reward_id_to_redeem) {
            $selected_reward = $reward;
            break;
        }
    }

    if ($selected_reward && $user_points >= $selected_reward['points_required']) {
        // Fetch user's email for notification
        $user_email = '';
        $user_first_name = '';
        $sql_user_email = "SELECT email, first_name FROM users WHERE user_id = ?";
        $stmt_user_email = $conn->prepare($sql_user_email);
        if ($stmt_user_email) {
            $stmt_user_email->bind_param("i", $current_user_id);
            $stmt_user_email->execute();
            $result_user_email = $stmt_user_email->get_result();
            if ($row_email = $result_user_email->fetch_assoc()) {
                $user_email = $row_email['email'];
                $user_first_name = $row_email['first_name'];
            }
            $stmt_user_email->close();
        }

        if (empty($user_email)) {
            $_SESSION['reward_message'] = "Could not find user email. Redemption aborted.";
            header('Location: rewards.php');
            exit();
        }

        $conn->begin_transaction(); // Start transaction

        try {
            $new_points = $user_points - $selected_reward['points_required'];
            
            // Update user points in DB
            $sql_update_points = "UPDATE users SET total_points = ? WHERE user_id = ?";
            $stmt_update_points = $conn->prepare($sql_update_points);
            if (!$stmt_update_points) {
                throw new Exception("Failed to prepare points update statement: " . $conn->error);
            }
            $stmt_update_points->bind_param("di", $new_points, $current_user_id);
            if (!$stmt_update_points->execute()) {
                throw new Exception("Failed to update points: " . $stmt_update_points->error);
            }
            $stmt_update_points->close();

            // TODO: Log redemption in a new table:
            // e.g., INSERT INTO user_rewards_log (user_id, reward_id, points_spent, redeemed_at, redemption_code) VALUES (?, ?, ?, NOW(), ?)
            
            $conn->commit(); // Commit transaction

            // Generate a unique redemption code
            $redemption_code = generateRandomCode(15);

            // Send confirmation email
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
                $mail->setFrom('info@ecoligocollective.com', 'Ecoligo Collective');
                $mail->addAddress($user_email, $user_first_name); 
                $mail->addReplyTo('info@ecoligocollective.com', 'Ecoligo Collective');

                // Email content
                $mail->isHTML(true);
                $mail->Subject = "Reward Redeemed: " . htmlspecialchars($selected_reward['name']);
                $mail->Body    = "<h3>Dear " . htmlspecialchars($user_first_name) . ",</h3>
                                  <p>You have successfully redeemed the reward: <strong>" . htmlspecialchars($selected_reward['name']) . "</strong>.</p>
                                  <p>Points spent: " . number_format($selected_reward['points_required']) . "</p>
                                  <p>Your new point balance is: " . number_format($new_points, 2) . "</p>
                                  <p>Your unique redemption code for this reward is: <strong>" . htmlspecialchars($redemption_code) . "</strong></p>
                                  <p>Please keep this code safe if it's needed to claim your reward.</p>
                                  <p>Thank you for being a part of Ecoligo Collective!</p>";
                $mail->AltBody = "Dear " . htmlspecialchars($user_first_name) . ",\n\nYou have successfully redeemed the reward: " . htmlspecialchars($selected_reward['name']) . ".\nPoints spent: " . number_format($selected_reward['points_required']) . "\nYour new point balance is: " . number_format($new_points, 2) . "\nYour unique redemption code for this reward is: " . htmlspecialchars($redemption_code) . "\nPlease keep this code safe if it's needed to claim your reward.\n\nThank you for being a part of Ecoligo Collective!";

                $mail->send();
                $_SESSION['reward_message'] = "Successfully redeemed '" . htmlspecialchars($selected_reward['name']) . "'! A confirmation email with your redemption code has been sent. Your new point balance is " . number_format($new_points, 2) . ".";
            } catch (Exception $e_mail) {
                // Email sending failed, but redemption was successful. Log email error.
                error_log("Reward redemption email failed for user $current_user_id (Code: $redemption_code): " . $mail->ErrorInfo);
                $_SESSION['reward_message'] = "Successfully redeemed '" . htmlspecialchars($selected_reward['name']) . "' (email confirmation failed: " . $mail->ErrorInfo . "). Your redemption code is " . htmlspecialchars($redemption_code) . ". Your new point balance is " . number_format($new_points, 2) . ".";
            }
            
            header('Location: rewards.php'); // Redirect to show updated points and clear POST
            exit();

        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction on error
            error_log("Reward redemption failed for user $current_user_id: " . $e->getMessage());
            $_SESSION['reward_message'] = "An error occurred while redeeming the reward. Please try again."; // Generic message for user
            header('Location: rewards.php');
            exit();
        }

    } elseif ($selected_reward) {
        $_SESSION['reward_message'] = "Not enough points to redeem '" . htmlspecialchars($selected_reward['name']) . "'.";
        header('Location: rewards.php');
        exit();
    } else {
        $_SESSION['reward_message'] = "Invalid reward selected.";
        header('Location: rewards.php');
        exit();
    }
}

include 'side_bar_template.php';
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Redeem Rewards</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="rewards.php">Rewards</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card card-stats card-primary card-round">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Your Points</p>
                                <h4 class="card-title"><?php echo number_format($user_points, 2); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['reward_message'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['reward_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['reward_message']); ?>
    <?php endif; ?>

    <div class="row">
        <?php if (empty($rewards)): ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <p class="text-center text-muted">No rewards available at the moment. Check back later!</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($rewards as $reward): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="reward-icon mb-3">
                                <i class="<?php echo htmlspecialchars($reward['icon']); ?> fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($reward['name']); ?></h5>
                            <p class="card-text text-muted small"><?php echo htmlspecialchars($reward['description']); ?></p>
                            <p class="card-text"><strong>Points: <?php echo number_format($reward['points_required']); ?></strong></p>
                            <p class="card-text"><small class="text-muted">Type: <?php echo htmlspecialchars($reward['type']); ?></small></p>
                            
                            <form method="POST" action="rewards.php">
                                <input type="hidden" name="redeem_reward_id" value="<?php echo $reward['id']; ?>">
                                <?php if ($user_points >= $reward['points_required']): ?>
                                    <button type="submit" class="btn btn-primary btn-round">Redeem</button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary btn-round" disabled>Not Enough Points</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .reward-icon i {
        font-size: 3rem; /* Ensure icons are large enough */
    }
    .card {
        margin-bottom: 20px;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    .card-stats .card-title {
        font-size: 2em; /* Make points display larger */
    }
</style>

<?php
// The side_bar_template.php includes closing tags for main-panel, wrapper, body, html,
// and also includes common JavaScript files at the end of its body.
?>
