<?php
include('connect.php');

$message = '';
$message_type = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate token and check expiry
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        // Token is valid, allow password reset
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

            // Update the password and clear the token/expiry
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE user_id = ?");
            $stmt->bind_param("si", $new_password, $user['user_id']);
            $stmt->execute();

            $message = "Password has been updated successfully!";
            $message_type = 'success';
        }
    } else {
        $message = "Invalid or expired token.";
        $message_type = 'error';
    }
} else {
    $message = "No token provided.";
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles/reset_password.css">
    <title>Reset Password</title>
</head>
<body>
    <form method="post" action="">
        <h3>Reset Password</h3>
        <input type="password" name="new_password" placeholder="New Password" required>
        <button type="submit">Reset Password</button>
    </form>

    <?php if (!empty($message)): ?>
        <div class="message message-<?php echo htmlspecialchars($message_type); ?>">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($message_type === 'success'): ?>
        <p><a href="login.php">Go back to login page</a></p>
    <?php endif; ?>
</body>
</html>
