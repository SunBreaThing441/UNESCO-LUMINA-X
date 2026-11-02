<?php
/**
 * UNESCO LUMINA-X - Sovereign Administrative Session Probe
 */
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (!empty($_SESSION['user'])) {
    echo json_encode([
        'authenticated' => true,
        'role' => 'sovereign',
        'user' => $_SESSION['user'],
        'timestamp' => time()
    ]);
} elseif (!empty($_SESSION['email'])) {
    // Student session active inside gov workspace - violation
    echo json_encode([
        'authenticated' => true,
        'role' => 'scholar',
        'email' => $_SESSION['email'],
        'timestamp' => time(),
        'isolation_violation' => true
    ]);
} else {
    echo json_encode([
        'authenticated' => false,
        'role' => null,
        'timestamp' => time()
    ]);
}
exit();
?>
