<?php


// Function to execute a query and return the result
function executeQuery($conn, $query, $params = []) {
    $stmt = $conn->prepare($query);
    if ($params) {
        $types = str_repeat('s', count($params)); // Assuming all params are strings
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// Function to fetch all rows from a result set
function fetchAllRows($result) {
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch a single row from a result set
function fetchSingleRow($result) {
    return $result->fetch_assoc();
}

// Function to close the database connection
function closeDatabaseConnection($conn) {
    $conn->close();
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate password (e.g., min 8 characters, one uppercase, one number)
function isValidPassword($password) {
    return preg_match('/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/', $password);
}

// Function to check if a user exists by email
function userExists($conn, $email) {
    $query = "SELECT user_id FROM users WHERE email = ?";
    $result = executeQuery($conn, $query, [$email]);
    return fetchSingleRow($result) !== null;
}

// Function to insert a new user into the Users table
function insertUser($conn, $email, $password, $first_name, $last_name, $verificationToken) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (email, password, first_name, last_name, verification_token, email_verified) VALUES (?, ?, ?, ?, ?, 0)";
    return executeQuery($conn, $query, [$email, $hashedPassword, $first_name, $last_name, $verificationToken]);
}

// Function to verify user login
function verifyUser($conn, $email, $password) {
    $query = "SELECT * FROM users WHERE email = ?";
    $result = executeQuery($conn, $query, [$email]);
    $user = fetchSingleRow($result);

    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Function to update email verification status
function verifyEmail($conn, $email) {
    $query = "UPDATE users SET email_verified = 1 WHERE email = ?";
    return executeQuery($conn, $query, [$email]);
}

// Function to log errors
function logError($error_message) {
    error_log(date('[Y-m-d H:i:s]') . " Error: $error_message\n", 3, 'error_log.txt');
}

// Function to send verification email (to be reused)
function sendVerificationEmail($email, $token) {
    $verificationLink = "http://localhost/project/public_html/verify.php?email={$email}&token={$token}";
    // Note: Use PHPMailer as you've configured
    // Placeholder here
    return mail($email, "Verify Email", "Click the link to verify your email: $verificationLink");
}
?>
