<?php
include('connect.php');
include('functions/functies.php');
session_start();

require __DIR__ . '/vendor/autoload.php';

use Google\Client as Google_Client;

$client = new Google_Client();
$client->setClientId("1017173810756-avqs2m8qrs4kt12tqp0lkmuqc2e8beqe.apps.googleusercontent.com");
$client->setClientSecret("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");
$client->setRedirectUri('https://ecoligocollective.com/google_callback.php');
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Haal gebruikersprofielinformatie op
    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    $email = $userInfo->email;
    $firstName = $userInfo->givenName;
    $lastName = $userInfo->familyName;

    // Check of gebruiker bestaat in de database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        // Gebruiker bestaat niet, maak een nieuwe gebruiker aan
        $stmt = $conn->prepare("INSERT INTO users (email, first_name, last_name, email_verified) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $email, $firstName, $lastName);
        $stmt->execute();
        $user_id = $stmt->insert_id;
    } else {
        $user_id = $user['user_id'];
    }

    // Stel sessievariabelen in
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['first_name'] = $firstName;
    $_SESSION['last_name'] = $lastName;

    // Stuur door naar de homepagina
    header('Location: home.php');
    exit();
} else {
    // Handel fout af
    echo "Error during authentication.";
}
?>