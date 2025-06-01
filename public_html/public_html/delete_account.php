<?php
session_start();  

// Check of gebruiker ingelogd is
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    // Indien niet ingelogd, stuur door naar login pagina
    header("Location: login.php");
    exit();
}

include('connect.php');

$user_id = $_SESSION['user_id'];
$email = $_SESSION['email'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        $security_answer = trim($_POST['security_answer']);

        // Haal hash v beveiligingsantwoord op
        $stmt = $conn->prepare("SELECT security_answer_hash FROM users WHERE user_id = ? AND email = ?");
        $stmt->bind_param("is", $user_id, $email);
        $stmt->execute();
        $stmt->bind_result($security_answer_hash);
        $stmt->fetch();
        $stmt->close();

        // Verifieer beveiligingsantwoord
        if (password_verify($security_answer, $security_answer_hash)) {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND email = ?");
            $stmt->bind_param("is", $user_id, $email);
            
            if ($stmt->execute()) {
                session_destroy();  
                header("Location: login.php?message=Account successfully deleted.&type=success");
                exit();
            } else {
                header("Location: login.php?message=Error deleting account. Please try again.&type=error");
            }
            $stmt->close();
        } else {
            header("Location: login.php?message=Invalid security answer.&type=error");
        }
    } else {
        header("Location: login.php?message=Account deletion was not confirmed.&type=error");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <link rel="stylesheet" href="styles/delete_account.css">
    <link rel="stylesheet" href="styles/messages.css">
</head>
<body>
    <div class="container">
        <div class="delete-account-box">
            <h1>Delete Your Account</h1>

            <?php if (isset($error_message)): ?>
                <div class="message message-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <p>Are you sure you want to delete your account? This action cannot be undone.</p>
            
            <form method="post">
                <div class="form-group">
                    <label for="confirm_delete">Type "yes" to confirm account deletion:</label>
                    <input type="text" name="confirm_delete" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="security_answer">Enter your security answer:</label>
                    <input type="text" name="security_answer" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-danger">Delete Account</button>
                <a href="home.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
