<?php
/**
 * UNESCO LUMINA-X - Secure Session Termination
 * Destroys scholar session state, clears cookie headers, and routes to login gateway.
 */
session_start();
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();
header("location: login.php?notice=session_terminated");
exit();
?>
