<?php
session_start();
include 'connect.php';

// Zorg dat de gebruiker ingelogd is
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get project ID from query parameters
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

<<<<<<< Updated upstream
// Fetch project details from the database
=======
// Haal projectdetails op
>>>>>>> Stashed changes
$sql = "SELECT * FROM compensationprojects WHERE project_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $project_id);
$stmt->execute();
<<<<<<< Updated upstream
$result = $stmt->get_result();
$project = $result->fetch_assoc();
=======
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handel review-indiening af
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['review_text'], $_POST['rating']) && !isset($_SESSION['review_submitted'])) {
    $review_text = trim($_POST['review_text']);
    $rating = intval($_POST['rating']);

    if ($rating >= 1 && $rating <= 5) {
        $sql = "INSERT INTO reviews (user_id, project_id, rating, review_text) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", $user_id, $project_id, $rating, $review_text);
        $stmt->execute();
        $stmt->close();
        $_SESSION['review_submitted'] = true; // Stel sessievariabele in om opnieuw indienen te voorkomen
        // Sla een succesbericht op in de sessie
        $_SESSION['review_message'] = "Your review has been submitted successfully!";
        header("Location: project_details.php?project_id=$project_id"); // Stuur door naar dezelfde pagina
        exit();
    } else {
        // Sla een foutmelding op in de sessie
        $_SESSION['review_error_message'] = "Invalid rating. Please choose a rating between 1 and 5.";
        header("Location: project_details.php?project_id=$project_id"); // Stuur door naar dezelfde pagina
        exit();
    }
}

// Reset de sessievariabele nadat de pagina opnieuw is geladen
if (isset($_SESSION['review_submitted'])) {
    unset($_SESSION['review_submitted']);
}

// Haal bestaande reviews op
$sql = "SELECT r.rating, r.review_text, u.first_name, u.last_name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.project_id = ? ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$reviews = $stmt->get_result();
>>>>>>> Stashed changes
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Details</title>
<<<<<<< Updated upstream
    <!-- Include necessary CSS and JS files -->
    <link rel="stylesheet" href="path/to/your/css/file.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
    <style>
        .card-img-top {
            height: 300px;
            object-fit: cover;
        }
        .card-body {
            height: 100%;
        }
    </style>
</head>
<body>
  <?php include('side_bar_template.php') ?>
    <!-- Your existing HTML content -->

    <div class="container">
        <div class="page-inner">
            <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                    <h3 class="fw-bold mb-3">Project Details</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php if ($project) { ?>
                        <div class="card">
                            <img src="<?= htmlspecialchars($project['compensationProjectImage']) ?>" class="card-img-top" alt="<?= htmlspecialchars($project['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($project['name']) ?></h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                                <p><strong>Project Type:</strong> <?= htmlspecialchars($project['project_type']) ?></p>
                                <p><strong>Effectiveness:</strong> <?= htmlspecialchars($project['effectiveness']) ?>%</p>
                                <p><strong>Created At:</strong> <?= htmlspecialchars($project['created_at']) ?></p>
                                <p><strong>Last Updated:</strong> <?= htmlspecialchars($project['updated_at']) ?></p>
                                <form action="select_payment_method.php" method="post">
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Amount to Invest</label>
                                        <input type="number" class="form-control" id="amount" name="amount" required>
                                    </div>
                                    <input type="hidden" name="project_id" value="<?= htmlspecialchars($project_id) ?>">
                                    <button type="submit" class="btn btn-primary">Pay</button>
                                </form>
                                <a href="compensation_projects.php" class="btn btn-secondary mt-3">Go Back</a>
                            </div>
                        </div>
                    <?php } else { ?>
                        <p>Project not found.</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
=======
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        .project-card-img {
            width: 100%;
            max-height: 400px; 
            object-fit: cover;
        }
    </style>
</head>
<body>
<?php include('side_bar_template.php'); ?>

<div class="main-content"> 
    <div class="container py-4">
        <h2 class="mb-4">Project Details</h2>

        <?php if ($project): ?>
            <div class="card mb-4">
                <img src="<?= htmlspecialchars($project['compensationProjectImage']) ?>" class="card-img-top project-card-img" alt="<?= htmlspecialchars($project['name']) ?>">
                <div class="card-body">
                    <h4 class="card-title"><?= htmlspecialchars($project['name']) ?></h4>
                    <p class="card-text"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                Project not found.
            </div>
        <?php endif; ?>

        <?php if ($project): // Toon donatie en review alleen als project bestaat ?>
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">Make a Donation</h4>
                <form action="select_payment_method.php" method="post">
                    <input type="hidden" name="project_id" value="<?= htmlspecialchars($project_id) ?>">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (€):</label>
                        <input type="number" id="amount" name="amount" min="1" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Proceed to Pay</button>
                </form>
            </div>
        </div>
        
        <button onclick="window.location.href='compensation_projects.php';" class="btn btn-secondary mb-4">Go Back to Projects</button>

        <hr class="my-4">

        <h2 class="mb-4">Reviews</h2>

        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Leave a Review</h4>
                        <?php
                        // Toon berichten over review-indiening
                        if (isset($_SESSION['review_message'])) {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['review_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            unset($_SESSION['review_message']); // Wis bericht na weergave
                            unset($_SESSION['review_submitted']); // Wis indieningsvlag
                        }
                        if (isset($_SESSION['review_error_message'])) {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['review_error_message'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                            unset($_SESSION['review_error_message']); // Wis bericht na weergave
                        }
                        ?>
                        <form method="post" action="project_details.php?project_id=<?= htmlspecialchars($project_id) ?>">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating (1-5):</label>
                                <input type="number" id="rating" name="rating" min="1" max="5" required class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="review_text" class="form-label">Your Review:</label>
                                <textarea id="review_text" name="review_text" required class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 mb-4">
                <h4 class="mb-3">User Reviews</h4>
                <?php if ($reviews->num_rows > 0): ?>
                    <?php while ($review = $reviews->fetch_assoc()): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?>
                                    <span class="ms-2 badge bg-primary">Rating: <?= $review['rating'] ?>/5</span>
                                </h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <p class="card-text mb-0">No reviews yet for this project. Be the first to leave a review!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

>>>>>>> Stashed changes
</body>
</html>
