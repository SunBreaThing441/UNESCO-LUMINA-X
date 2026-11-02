<?php
/**
 * UNESCO LUMINA-X - Sovereign Administrative Session Termination
 * Destroys administrative session, purges session cookies, and routes to admin gateway.
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
header("location: login.php?notice=sovereign_session_terminated");
exit();
?>
