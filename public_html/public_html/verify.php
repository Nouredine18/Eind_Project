<?php
include('connect.php');

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    $conn = connectToDatabase($host, $username, $password, $database);

    $email = $conn->real_escape_string($email);
    $token = $conn->real_escape_string($token);

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND verification_token = ?");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        echo "Email verified successfully! You can now <a href='login.php'>login</a>.";
    } else {
        echo "Invalid verification link or email already verified.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
