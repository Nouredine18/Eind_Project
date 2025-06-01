<?php
include('connect.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Valideer token tegen db & vervaltijd
    $stmt = $conn->prepare("SELECT * FROM users WHERE delete_token = ? AND delete_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user['user_id']);
        $stmt->execute();

        echo "Je account is succesvol verwijderd!";
    } else {
        echo "Ongeldige of verlopen token.";
    }
} else {
    echo "Geen token opgegeven.";
}

?>
