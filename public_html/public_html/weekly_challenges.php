<?php
session_start();
include 'connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$points_per_challenge = 25; 


$completed_challenges_db = [];
$sql_fetch_completed_challenges = "SELECT challenge_id FROM user_weekly_challenges_completed WHERE user_id = ?";
$stmt_fetch_completed_challenges = $conn->prepare($sql_fetch_completed_challenges);
if ($stmt_fetch_completed_challenges) {
    $stmt_fetch_completed_challenges->bind_param("i", $current_user_id);
    $stmt_fetch_completed_challenges->execute();
    $result_completed_challenges = $stmt_fetch_completed_challenges->get_result();
    while ($row = $result_completed_challenges->fetch_assoc()) {
        $completed_challenges_db[$row['challenge_id']] = true;
    }
    $stmt_fetch_completed_challenges->close();
} else {
    error_log("Failed to fetch completed weekly challenges: " . $conn->error);
}

$current_week_challenges = [
    1 => [
        'title' => "Meatless Monday & Local Sourcing",
        'description' => "For one day this week, avoid meat. For all your meals, try to source at least 50% of your ingredients locally.",
        'details_id' => 'challenge_detail_1' 
    ],
    2 => [
        'title' => "Zero Single-Use Plastic Week",
        'description' => "Attempt to go the entire week without using any single-use plastics. This includes bags, bottles, cutlery, and packaging.",
        'details_id' => 'challenge_detail_2'
    ],
    3 => [
        'title' => "Active Commute Challenge",
        'description' => "If your commute is less than 5km, try walking or cycling instead of driving at least 3 times this week.",
        'details_id' => 'challenge_detail_3'
    ],
    4 => [
        'title' => "Energy Conservation Evening",
        'description' => "Dedicate one evening to minimizing energy use: use candles instead of lights (safely!), unplug unused electronics, and avoid screens.",
        'details_id' => 'challenge_detail_4'
    ]
];

$feedback_message = '';
$points_awarded_this_submission = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_challenge'])) {
    $challenge_id_completed = isset($_POST['challenge_id']) ? (int)$_POST['challenge_id'] : 0;

    if (isset($current_week_challenges[$challenge_id_completed]) && !isset($completed_challenges_db[$challenge_id_completed])) {
        $conn->begin_transaction();
        try {
            
            $sql_insert_challenge = "INSERT INTO user_weekly_challenges_completed (user_id, challenge_id) VALUES (?, ?)";
            $stmt_insert_challenge = $conn->prepare($sql_insert_challenge);
            if ($stmt_insert_challenge) {
                $stmt_insert_challenge->bind_param("ii", $current_user_id, $challenge_id_completed);
                if (!$stmt_insert_challenge->execute()) {
                    throw new Exception("Failed to record challenge completion: " . $stmt_insert_challenge->error);
                }
                $stmt_insert_challenge->close();
            } else {
                throw new Exception("Failed to prepare statement for recording challenge completion: " . $conn->error);
            }

            $points_awarded_this_submission = $points_per_challenge;
            
            $sql_update_points = "UPDATE users SET total_points = total_points + ? WHERE user_id = ?";
            $stmt_update_points = $conn->prepare($sql_update_points);
            if ($stmt_update_points) {
                $stmt_update_points->bind_param("di", $points_awarded_this_submission, $current_user_id);
                if ($stmt_update_points->execute()) {
                    $_SESSION['total_points'] = ($_SESSION['total_points'] ?? 0) + $points_awarded_this_submission;
                    $feedback_message = "Congratulations! You've earned " . $points_awarded_this_submission . " points for completing the challenge: '" . htmlspecialchars($current_week_challenges[$challenge_id_completed]['title']) . "'.";
                    $completed_challenges_db[$challenge_id_completed] = true; 
                } else {
                    throw new Exception("Error updating points: " . $stmt_update_points->error);
                }
                $stmt_update_points->close();
            } else {
                throw new Exception("Error preparing to update points: " . $conn->error);
            }
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $feedback_message = "An error occurred: " . $e->getMessage();
            $completed_challenges_db = []; 
            $stmt_refetch_challenges = $conn->prepare("SELECT challenge_id FROM user_weekly_challenges_completed WHERE user_id = ?");
            if ($stmt_refetch_challenges) {
                $stmt_refetch_challenges->bind_param("i", $current_user_id);
                $stmt_refetch_challenges->execute();
                $result_refetch_challenges = $stmt_refetch_challenges->get_result();
                while ($row_refetch = $result_refetch_challenges->fetch_assoc()) {
                    $completed_challenges_db[$row_refetch['challenge_id']] = true;
                }
                $stmt_refetch_challenges->close();
            }
            $points_awarded_this_submission = 0;
        }

    } elseif (isset($current_week_challenges[$challenge_id_completed]) && isset($completed_challenges_db[$challenge_id_completed])) {
        $feedback_message = "You have already completed this challenge and received points for it.";
    } else {
        $feedback_message = "Invalid challenge selected or challenge not found.";
    }
}


include 'side_bar_template.php';
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Weekly Sustainability Challenges</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="weekly_challenges.php">Weekly Challenges</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">This Week's Challenges</h4>
                    <p class="card-category">
                        Develop sustainable habits and earn points! Complete a challenge to earn <?php echo $points_per_challenge; ?> points.
                    </p>
                </div>
                <div class="card-body">
                    <?php if ($feedback_message): ?>
                        <div class="alert <?php echo ($points_awarded_this_submission > 0 && strpos(strtolower($feedback_message), 'error') === false) ? 'alert-success' : 'alert-info'; ?>" role="alert">
                            <?php echo $feedback_message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($current_week_challenges)): ?>
                        <p class="text-center text-muted">No challenges available this week. Check back soon!</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($current_week_challenges as $id => $challenge): ?>
                                <?php $is_completed_from_db = isset($completed_challenges_db[$id]); ?>
                                <div class="list-group-item list-group-item-action flex-column align-items-start <?php if($is_completed_from_db) echo 'list-group-item-success'; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($challenge['title']); ?></h5>
                                        <small><?php if($is_completed_from_db) echo 'Completed ('.$points_per_challenge.' pts earned)'; else echo $points_per_challenge.' pts'; ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($challenge['description']); ?></p>
                                    <form method="POST" action="weekly_challenges.php" class="mt-2">
                                        <input type="hidden" name="challenge_id" value="<?php echo $id; ?>">
                                        <button type="submit" name="complete_challenge" class="btn btn-sm <?php echo $is_completed_from_db ? 'btn-secondary' : 'btn-primary'; ?>" <?php if ($is_completed_from_db) echo 'disabled'; ?>>
                                            <?php echo $is_completed_from_db ? 'Points Redeemed' : 'Mark as Completed'; ?>
                                        </button>
                        
                                        <!-- <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#<?php echo $challenge['details_id']; ?>">Details</button> -->
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                     <p class="mt-3 text-muted small">
                        Note: Points are awarded once per challenge.
                    </p>
                </div>
                 <div class="card-footer text-center">
                    <a href="rewards.php" class="btn btn-info">View Your Rewards</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!--
<div class="modal fade" id="challenge_detail_1" tabindex="-1" aria-labelledby="challengeDetail1Label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="challengeDetail1Label">Details for Meatless Monday & Local Sourcing</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>More detailed instructions and tips for the 'Meatless Monday & Local Sourcing' challenge...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
-->
<?php
?>
