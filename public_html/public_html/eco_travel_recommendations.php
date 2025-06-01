<?php
session_start();
include 'connect.php'; // Maakt $conn aan

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$points_per_recommendation = 10;

// Haal reeds voltooide aanbevelingen op uit de database
$completed_recommendations_db = [];
$sql_fetch_completed = "SELECT recommendation_id FROM user_eco_recommendations_completed WHERE user_id = ?";
$stmt_fetch_completed = $conn->prepare($sql_fetch_completed);
if ($stmt_fetch_completed) {
    $stmt_fetch_completed->bind_param("i", $current_user_id);
    $stmt_fetch_completed->execute();
    $result_completed = $stmt_fetch_completed->get_result();
    while ($row = $result_completed->fetch_assoc()) {
        $completed_recommendations_db[$row['recommendation_id']] = true;
    }
    $stmt_fetch_completed->close();
} else {
    // Handel fout af indien nodig, voor nu, ga ervan uit dat niets voltooid is als query mislukt
    error_log("Failed to fetch completed eco recommendations: " . $conn->error);
}

$recommendations = [
    0 => "Choose direct flights to reduce takeoff and landing emissions.",
    1 => "Pack light to reduce aircraft fuel consumption.",
    2 => "Use public transportation, walk, or cycle at your destination.",
    3 => "Stay in eco-friendly accommodations (e.g., those with green certifications).",
    4 => "Offset your travel carbon footprint through certified projects.",
    5 => "Avoid single-use plastics: bring a reusable water bottle and shopping bag.",
    6 => "Respect local cultures and environments.",
    7 => "Support local economies by buying local products and services.",
    8 => "Conserve water and energy in your accommodation.",
    9 => "Choose trains or buses over short-haul flights where possible.",
    10 => "Educate yourself about the environmental impact of tourism.",
    11 => "Opt for digital tickets and guides instead of paper."
];

$feedback_message = '';
$points_awarded_this_submission = 0;
$newly_redeemed_details = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_checked'])) {
    if (isset($_POST['recommendations_checked']) && is_array($_POST['recommendations_checked'])) {
        $conn->begin_transaction(); // Start transactie
        try {
            foreach ($_POST['recommendations_checked'] as $recommendation_index_str) {
                $recommendation_index = (int)$recommendation_index_str;
                // Check of deze tip bestaat en nog niet ingewisseld is vanuit DB
                if (isset($recommendations[$recommendation_index]) && !isset($completed_recommendations_db[$recommendation_index])) {
                    // Voeg toe aan DB
                    $sql_insert_completed = "INSERT INTO user_eco_recommendations_completed (user_id, recommendation_id) VALUES (?, ?)";
                    $stmt_insert = $conn->prepare($sql_insert_completed);
                    if ($stmt_insert) {
                        $stmt_insert->bind_param("ii", $current_user_id, $recommendation_index);
                        if ($stmt_insert->execute()) {
                            $points_awarded_this_submission += $points_per_recommendation;
                            $completed_recommendations_db[$recommendation_index] = true; // Update onze lokale cache
                            $newly_redeemed_details[] = $recommendations[$recommendation_index];
                        } else {
                            throw new Exception("Failed to record recommendation completion: " . $stmt_insert->error);
                        }
                        $stmt_insert->close();
                    } else {
                        throw new Exception("Failed to prepare statement for recording completion: " . $conn->error);
                    }
                }
            }

            if ($points_awarded_this_submission > 0) {
                $sql_update_points = "UPDATE users SET total_points = total_points + ? WHERE user_id = ?";
                $stmt_update_points = $conn->prepare($sql_update_points);
                if ($stmt_update_points) {
                    $stmt_update_points->bind_param("di", $points_awarded_this_submission, $current_user_id);
                    if ($stmt_update_points->execute()) {
                        $_SESSION['total_points'] = ($_SESSION['total_points'] ?? 0) + $points_awarded_this_submission;
                        $feedback_message = "You've earned " . $points_awarded_this_submission . " points for completing new eco-travel recommendations!";
                        if (!empty($newly_redeemed_details)) {
                            $feedback_message .= "<br><strong>Redeemed for:</strong><ul>";
                            foreach($newly_redeemed_details as $detail) {
                                $feedback_message .= "<li>" . htmlspecialchars($detail) . "</li>";
                            }
                            $feedback_message .= "</ul>";
                        }
                    } else {
                        throw new Exception("Error updating points: " . $stmt_update_points->error);
                    }
                    $stmt_update_points->close();
                } else {
                    throw new Exception("Error preparing to update points: " . $conn->error);
                }
            } elseif (!empty($_POST['recommendations_checked'])) {
                $feedback_message = "All selected items have already been redeemed for points.";
            } else {
                $feedback_message = "No new items were selected for redemption.";
            }
            $conn->commit(); // Commit transactie
        } catch (Exception $e) {
            $conn->rollback(); // Rollback bij fout
            $feedback_message = "An error occurred: " . $e->getMessage();
            // Haal voltooide items opnieuw op, want transactie kan deels geslaagd zijn voor rollback/fout
            $completed_recommendations_db = []; // Reset en haal opnieuw op
            $stmt_refetch = $conn->prepare("SELECT recommendation_id FROM user_eco_recommendations_completed WHERE user_id = ?");
            if ($stmt_refetch) {
                $stmt_refetch->bind_param("i", $current_user_id);
                $stmt_refetch->execute();
                $result_refetch = $stmt_refetch->get_result();
                while ($row_refetch = $result_refetch->fetch_assoc()) {
                    $completed_recommendations_db[$row_refetch['recommendation_id']] = true;
                }
                $stmt_refetch->close();
            }
            $points_awarded_this_submission = 0; // Reset toegekende punten omdat transactie mislukt is
        }
    } else {
        $feedback_message = "No items were checked.";
    }
}

include 'side_bar_template.php';
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Eco-Friendly Travel Recommendations</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="eco_travel_recommendations.php">Eco Travel Tips</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Travel Smarter, Greener!</h4>
                    <p class="card-category">
                        Check off the eco-friendly travel practices you follow and redeem points!
                        Each newly checked item awards <?php echo $points_per_recommendation; ?> points.
                    </p>
                </div>
                <div class="card-body">
                    <?php if ($feedback_message): ?>
                        <div class="alert <?php echo ($points_awarded_this_submission > 0 && strpos(strtolower($feedback_message), 'error') === false) ? 'alert-success' : 'alert-info'; ?>" role="alert">
                            <?php echo $feedback_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="eco_travel_recommendations.php">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recommendations as $index => $tip): ?>
                                <?php
                                $is_redeemed_from_db = isset($completed_recommendations_db[$index]);
                                ?>
                                <li class="list-group-item">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="recommendations_checked[]" 
                                               value="<?php echo $index; ?>" 
                                               id="recommendationCheck<?php echo $index; ?>"
                                               <?php if ($is_redeemed_from_db) echo 'checked disabled'; ?>>
                                        <label class="form-check-label <?php if ($is_redeemed_from_db) echo 'text-muted'; ?>" for="recommendationCheck<?php echo $index; ?>">
                                            <?php echo htmlspecialchars($tip); ?>
                                            <?php if ($is_redeemed_from_db) echo ' (Points redeemed)'; ?>
                                        </label>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="text-center mt-4">
                            <button type="submit" name="redeem_checked" class="btn btn-success btn-round">Redeem Checked Items</button>
                        </div>
                    </form>
                    <p class="mt-3 text-muted small">
                        Note: Points are awarded once per item.
                    </p>
                </div>
                <div class="card-footer text-center">
                    <a href="compensation_projects.php" class="btn btn-primary">Offset Your Emissions Now</a>
                    <a href="rewards.php" class="btn btn-info">View Your Rewards</a>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .form-check-label {
        padding-left: 0.5rem; /* Voeg wat ruimte toe tussen checkbox en label */
    }
    .list-group-item {
        border-left: 0;
        border-right: 0;
    }
    .list-group-item:first-child {
        border-top-left-radius: .25rem;
        border-top-right-radius: .25rem;
        border-top: 0;
    }
    .list-group-item:last-child {
        border-bottom-left-radius: .25rem;
        border-bottom-right-radius: .25rem;
        border-bottom: 0;
    }
    .form-check-input:disabled + .form-check-label {
        color: #6c757d; /* Gedempte kleur voor uitgeschakelde items */
    }
</style>

<?php
// side_bar_template.php bevat sluitende HTML tags
?>
