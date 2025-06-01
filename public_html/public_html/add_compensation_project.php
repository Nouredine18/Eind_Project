<?php
session_start();
include 'connect.php';

// check of gebruiker ingelogd is
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// afhandelen formulierinzending
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $project_type = trim($_POST['project_type']);
    $effectiveness = floatval($_POST['effectiveness']);
    $image_path = trim($_POST['image_path']);

    if (!empty($name) && !empty($description) && !empty($project_type) && $effectiveness > 0 && !empty($image_path)) {
        $sql = "INSERT INTO compensationprojects (name, description, project_type, effectiveness, created_at, updated_at, compensationProjectImage) VALUES (?, ?, ?, ?, NOW(), NOW(), ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("sssds", $name, $description, $project_type, $effectiveness, $image_path);
        if ($stmt->execute()) {
            // Stuur door naar news.php voor e-mailnotificaties over nieuw project
            header('Location: news.php');
            exit();
        } else {
            die("Error executing statement: " . $stmt->error);
        }
        $stmt->close();
    } else {
        echo "Please fill in all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Compensation Project</title>
    <!-- Include necessary CSS and JS files -->
    <link rel="stylesheet" href="path/to/your/css/file.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.min.js"></script>
</head>
<body>
<?php include('side_bar_template.php') ?>
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Add Compensation Project</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form method="post" class="form-group">
                    <label for="name">Project Name:</label>
                    <input type="text" id="name" name="name" required class="form-control mb-2">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required class="form-control mb-2"></textarea>
                    <label for="project_type">Project Type:</label>
                    <input type="text" id="project_type" name="project_type" required class="form-control mb-2">
                    <label for="effectiveness">Effectiveness (%):</label>
                    <input type="number" id="effectiveness" name="effectiveness" min="0" max="100" step="0.01" required class="form-control mb-2">
                    <label for="image_path">Image Path:</label>
                    <input type="text" id="image_path" name="image_path" required class="form-control mb-2">
                    <button type="submit" class="btn btn-primary">Add Project</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
