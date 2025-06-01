<?php
// logout.php

// Start de sessie
session_start();

// Maak alle sessievariabelen leeg
$_SESSION = array();


// Als het gewenst is om de sessie te beëindigen, verwijder dan ook de sessiecookie.
// Opmerking: Dit vernietigt de sessie, en niet alleen de sessiegegevens!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Vernietig de sessie
session_destroy();

// Stuur door naar de inlogpagina of een andere pagina
header("Location: login.php");
exit;
?>