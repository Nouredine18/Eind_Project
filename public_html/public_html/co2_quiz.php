<?php
session_start();
include 'connect.php'; // Maakt $conn aan

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$quiz_points_awarded = 50;
$quiz_completed_session_key = 'co2_quiz_completed_session_' . $current_user_id;

$questions = [
    1 => [
        'question' => 'What is the most abundant greenhouse gas in Earth\'s atmosphere?',
        'options' => ['Methane (CH4)', 'Nitrous Oxide (N2O)', 'Carbon Dioxide (CO2)', 'Water Vapor (H2O)'],
        'answer' => 3 // Correct antwoord index: Water Vapor (H2O)
    ],
    2 => [
        'question' => 'Which human activity is the largest source of carbon dioxide (CO2) emissions?',
        'options' => ['Deforestation', 'Industrial processes', 'Burning fossil fuels for electricity and heat', 'Agriculture'],
        'answer' => 2 // Correct antwoord index: Burning fossil fuels...
    ],
    3 => [
        'question' => 'What does "carbon footprint" refer to?',
        'options' => [
            'The amount of carbon stored in a forest',
            'The total amount of greenhouse gases produced to directly and indirectly support human activities',
            'A type of shoe made from recycled materials',
            'The mark left by coal mining'
        ],
        'answer' => 1 // Correct antwoord index: The total amount...
    ],
    4 => [
        'question' => 'Which of these is a way to reduce your personal carbon footprint?',
        'options' => ['Eating more red meat', 'Using public transportation or cycling', 'Leaving lights on when not in use', 'Frequent air travel'],
        'answer' => 1 // Correct antwoord index: Using public transportation...
    ],
    5 => [
        'question' => 'What is "carbon offsetting"?',
        'options' => [
            'A way to ignore CO2 emissions',
            'A process of capturing CO2 directly from the atmosphere',
            'A reduction in emissions of CO2 or greenhouse gases made in order to compensate for emissions made elsewhere',
            'Planting a single tree'
        ],
        'answer' => 2 // Correct antwoord index: A reduction in emissions...
    ]
];

$score = 0;
$total_questions = count($questions);
$quiz_submitted = false;
$user_answers = [];
$feedback_messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $quiz_submitted = true;
    foreach ($questions as $id => $details) {
        if (isset($_POST['question_' . $id])) {
            $user_answer_index = (int)$_POST['question_' . $id];
            $user_answers[$id] = $user_answer_index;
            if ($user_answer_index === $details['answer']) {
                $score++;
            }
        } else {
            $user_answers[$id] = null; 
        }
    }

    // Ken punten toe als quiz is voltooid en nog niet eerder in deze sessie
    if (!isset($_SESSION[$quiz_completed_session_key]) || $_SESSION[$quiz_completed_session_key] !== true) {
        $sql_update_points = "UPDATE users SET total_points = total_points + ? WHERE user_id = ?";
        $stmt_update_points = $conn->prepare($sql_update_points);
        if ($stmt_update_points) {
            $stmt_update_points->bind_param("di", $quiz_points_awarded, $current_user_id);
            if ($stmt_update_points->execute()) {
                $_SESSION[$quiz_completed_session_key] = true; 
                // Update punten ook in de directe sessievariabele indien gebruikt
                if(isset($_SESSION['total_points'])) {
                    $_SESSION['total_points'] += $quiz_points_awarded;
                }
                $feedback_messages['points'] = "<div class='alert alert-success mt-3'>Congratulations! You've earned " . $quiz_points_awarded . " bonus points for completing the quiz.</div>";
            } else {
                $feedback_messages['points'] = "<div class='alert alert-danger mt-3'>Could not award points. Error: " . $stmt_update_points->error . "</div>";
            }
            $stmt_update_points->close();
        } else {
            $feedback_messages['points'] = "<div class='alert alert-danger mt-3'>Could not prepare to award points. Error: " . $conn->error . "</div>";
        }
    } else {
        $feedback_messages['points'] = "<div class='alert alert-info mt-3'>You have already received points for this quiz in this session.</div>";
    }
    $feedback_messages['score'] = "<div class='alert alert-info mt-4'>You scored " . $score . " out of " . $total_questions . ".</div>";
}


include 'side_bar_template.php';
?>

<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">CO2 Knowledge Quiz</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="home.php"><i class="icon-home"></i></a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="co2_quiz.php">CO2 Quiz</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Test Your Sustainability Knowledge!</h4>
                </div>
                <div class="card-body">
                    <?php if ($quiz_submitted): ?>
                        <h5 class="mb-3">Quiz Results:</h5>
                        <?php 
                        echo $feedback_messages['score'] ?? '';
                        echo $feedback_messages['points'] ?? ''; 
                        ?>

                        <?php foreach ($questions as $id => $details): ?>
                            <div class="mb-3 p-3 border rounded <?php echo (isset($user_answers[$id]) && $user_answers[$id] === $details['answer']) ? 'border-success' : 'border-danger'; ?>">
                                <p class="fw-bold"><?php echo htmlspecialchars($details['question']); ?></p>
                                <p>Your answer: <?php echo isset($user_answers[$id]) ? htmlspecialchars($details['options'][$user_answers[$id]]) : 'Not answered'; ?></p>
                                <p>Correct answer: <?php echo htmlspecialchars($details['options'][$details['answer']]); ?></p>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-4">
                            <a href="co2_quiz.php" class="btn btn-primary">Retake Quiz (No More Points This Session)</a>
                            <a href="rewards.php" class="btn btn-info">View Rewards</a>
                        </div>

                    <?php else: ?>
                        <p>Answer all questions to test your knowledge and earn <?php echo $quiz_points_awarded; ?> bonus points!</p>
                        <?php if(isset($_SESSION[$quiz_completed_session_key]) && $_SESSION[$quiz_completed_session_key] === true): ?>
                            <div class="alert alert-warning">You have already completed the quiz and received points in this session. You can retake it for practice.</div>
                        <?php endif; ?>
                        <form method="POST" action="co2_quiz.php">
                            <?php foreach ($questions as $id => $details): ?>
                                <div class="mb-4">
                                    <p class="fw-bold"><?php echo $id . ". " . htmlspecialchars($details['question']); ?></p>
                                    <?php foreach ($details['options'] as $index => $option): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $id; ?>" id="q<?php echo $id; ?>_option<?php echo $index; ?>" value="<?php echo $index; ?>" required>
                                            <label class="form-check-label" for="q<?php echo $id; ?>_option<?php echo $index; ?>">
                                                <?php echo htmlspecialchars($option); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" name="submit_quiz" class="btn btn-success btn-round">Submit Answers</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// side_bar_template.php bevat sluitende HTML tags
?>
