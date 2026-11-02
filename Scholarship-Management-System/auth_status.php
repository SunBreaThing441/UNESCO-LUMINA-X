<?php
/**
 * UNESCO LUMINA-X - Real-time Session State Probe
 * Provides lightweight JSON session introspection for client-side navigation guards.
 */
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (!empty($_SESSION['email']) && !empty($_SESSION['aadh'])) {
    echo json_encode([
        'authenticated' => true,
        'role' => 'scholar',
        'email' => $_SESSION['email'],
        'aadhaar' => $_SESSION['aadh'],
        'name' => $_SESSION['name'] ?? 'Scholar',
        'phone' => $_SESSION['phone'] ?? '',
        'timestamp' => time()
    ]);
} elseif (!empty($_SESSION['user'])) {
    echo json_encode([
        'authenticated' => true,
        'role' => 'sovereign',
        'user' => $_SESSION['user'],
        'timestamp' => time()
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
