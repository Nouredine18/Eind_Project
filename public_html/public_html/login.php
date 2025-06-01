<?php
include('connect.php');
include('functions/functies.php');
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Google Inloggen
require __DIR__ . '/vendor/autoload.php';

use Google\Client as Google_Client;

$client = new Google_Client();
$client->setClientId("1017173810756-avqs2m8qrs4kt12tqp0lkmuqc2e8beqe.apps.googleusercontent.com");
$client->setClientSecret("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");
$client->setRedirectUri('https://ecoligocollective.com/google_callback.php');
$client->addScope("email");
$client->addScope("profile");

$googleAuthUrl = $client->createAuthUrl(); // Dit genereert de Google-authenticatie-URL

// Initialiseer foutmelding
$error_message = "";

// Controleer of het formulier is ingediend
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Valideer e-mail- en wachtwoordinvoer
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Controleer of e-mail en wachtwoord niet leeg zijn
        if (empty($email) || empty($password)) {
            $error_message = "Please enter email and password.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email address.";
        } else {
            // Zorg ervoor dat de databaseverbinding tot stand is gebracht
            if ($conn && !$conn->connect_error) {
                // Bereid de SQL-instructie voor
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    // Voer de voorbereide instructie uit
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();

                        // Controleer of gebruiker bestaat en wachtwoord overeenkomt
                        if ($user) {
                            // Controleer of het e-mailadres is bevestigd
                            if ($user['email_verified'] == 0) {
                                $error_message = "Please verify your email before logging in.";
                            } elseif (password_verify($password, $user['password'])) {
                                // Stel sessievariabelen in voor de ingelogde gebruiker
                                session_regenerate_id(true);
                                $_SESSION['user_id'] = $user['user_id'];
                                $_SESSION['email'] = $user['email'];
                                $_SESSION['first_name'] = $user['first_name'];
                                $_SESSION['last_name'] = $user['last_name'];
                                $_SESSION['birthdate'] = $user['birthdate'];
                                $_SESSION['address'] = $user['address'];

                                // Werk laatste inlogtijd bij
                                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                                $update_stmt->bind_param("i", $user['user_id']);
                                $update_stmt->execute();
                                $update_stmt->close();

                                // Stuur door naar de startpagina na succesvol inloggen
                                header('Location: home.php');
                                exit();
                                } else {
                                $error_message = "Invalid email or password.";
                            }
                        } else {
                            $error_message = "Invalid email or password.";
                        }
                    } else {
                        $error_message = "Database query error: " . $stmt->error;
                    }
                    // Sluit de instructie
                    $stmt->close();
                } else {
                    $error_message = "Failed to prepare the SQL statement.";
                }
            } else {
                $error_message = "Database connection error: " . $conn->connect_error;
            }
        }
    } else {
        $error_message = "Please fill in all the required fields.";
    }
}

// Handel berichten van andere pagina's af
$message = isset($_GET['message']) ? $_GET['message'] : '';
$message_type = isset($_GET['type']) ? $_GET['type'] : '';
?>

<?php
// Facebook Inloggen
$fb = new Facebook\Facebook([
    'app_id' => '593933119633236', // je app-id
    'app_secret' => '20744f332f44687ece8c1718b99f6d35', // je app-geheim
    'default_graph_version' => 'v21.0',
]);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email']; // optioneel
$facebookAuthUrl = $helper->getLoginUrl('https://rename-online.com/facebook-login-using-php/', $permissions);

try {
    if (isset($_SESSION['facebook_access_token'])) {
        $accessToken = $_SESSION['facebook_access_token'];
    } else {
        $accessToken = $helper->getAccessToken();
    }
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // Wanneer Graph een fout retourneert
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // Wanneer validatie mislukt of andere lokale problemen
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
if (isset($accessToken)) {
    if (isset($_SESSION['facebook_access_token'])) {
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    } else {
        // verkrijgen van kortlevende toegangstoken
        $_SESSION['facebook_access_token'] = (string) $accessToken;
        // OAuth 2.0 client handler
        $oAuth2Client = $fb->getOAuth2Client();
        // Wisselt een kortlevende toegangstoken om voor een langlevende
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
        // instellen van standaard toegangstoken voor gebruik in script
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }
    // stuur de gebruiker door naar de profielpagina als het de "code" GET-variabele heeft
    if (isset($_GET['code'])) {
        header('Location: profile.php');
    }
    // verkrijgen van basisinformatie over gebruiker
    try {
        $profile_request = $fb->get('/me?fields=name,first_name,last_name,email');
        $profile = $profile_request->getGraphUser();
        $fbid = $profile->getProperty('id');           // Om Facebook ID te krijgen
        $fbfullname = $profile->getProperty('name');   // Om volledige Facebook naam te krijgen
        $fbemail = $profile->getProperty('email');    //  Om Facebook e-mail te krijgen
        $fbpic = "<img src='https://graph.facebook.com/$fbid/picture?redirect=true'>";
        // sla de gebruikersinformatie op in sessievariabele
        $_SESSION['fb_id'] = $fbid;
        $_SESSION['fb_name'] = $fbfullname;
        $_SESSION['fb_email'] = $fbemail;
        $_SESSION['fb_pic'] = $fbpic;
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // Wanneer Graph een fout retourneert
        echo 'Graph returned an error: ' . $e->getMessage();
        session_destroy();
        // gebruiker terugsturen naar de inlogpagina van de app
        header("Location: ./");
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // Wanneer validatie mislukt of andere lokale problemen
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
} else {
    $facebookAuthUrl = $helper->getLoginUrl('https://rename-online.com/facebook-login-using-php/', $permissions);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" /> 
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous"> 
    <link rel="stylesheet" href="styles/login_form.css">
    <link rel="stylesheet" href="styles/messages.css">
    <style>
        .message-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .message-registration-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
    </style>
</head>
<body>
<div class="container" id="container">
    <!-- Sign up form -->
    <div class="form-container sign-up-container">
        <form action="register.php" method="POST">
        <br>
            <h1>Create Account</h1>
            <div class="social-container">
                <a href="<?= $facebookAuthUrl ?>" class="social"><i class="fab fa-facebook-f"></i></a>
                <a href="<?= $googleAuthUrl ?>" class="social"><i class="fab fa-google-plus-g"></i></a>
                <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <span>or use your email for registration</span>
            <div class="scrollable-sidebar">
                <input type="text" name="first_name" placeholder="First Name" required />
                <input type="text" name="last_name" placeholder="Last Name" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <input type="test" name="security_question" placeholder="Security Question" required />
                <input type="test" name="security_answer" placeholder="Security Answer" required />
            </div>
            <button type="submit">Sign Up</button>
        </form>
    </div>

    <!-- Sign in form -->
    <div class="form-container sign-in-container">
        <form action="login.php" method="POST">
            <br>
            <br>
            <br>
            <h1>Sign in</h1>
            <div class="social-container">
                <a href="<?= $facebookAuthUrl ?>" class="social"><i class="fab fa-facebook-f"></i></a>
                <a href="<?= $googleAuthUrl ?>" class="social"><i class="fab fa-google-plus-g"></i></a>
                <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
            </div>
            <span>or use your account</span>
            <div class="scrollable-sidebar">
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
            </div>
            <a href="forgot_password.php">Forgot your password?</a>
            <button type="submit">Sign In</button>
        </form>
    </div>
    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Hello, welcome to Ecoligo Collective!</h1>
                <p>To keep connected with us please login with your personal info</p>
                <button class="ghost" id="signIn">Sign In</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>Welcome Back!</h1>
                <p>Enter your personal details and start your journey with us</p>                
                <button class="ghost" id="signUp">Sign Up</button>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($error_message)): ?>
    <div class="message message-error">
        <p><?php echo $error_message; ?></p>
    </div>
<?php endif; ?>

<?php if (!empty($message)): ?>
    <div class="message message-<?php echo htmlspecialchars($message_type); ?>">
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<script>
const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const container = document.getElementById('container');

signUpButton.addEventListener('click', () => {
    container.classList.add("right-panel-active");
});

signInButton.addEventListener('click', () => {
    container.classList.remove("right-panel-active");
});
</script>
</body>
</html>