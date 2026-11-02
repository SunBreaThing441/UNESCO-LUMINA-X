<?php
session_start();
if (empty($_SESSION['user'])) {
    header("location: login.php?error=unauthorized_access");
    exit();
}

require_once("connect.php");    

$aa = isset($_GET['aadhaar']) ? mysqli_real_escape_string($connect, $_GET['aadhaar']) : '';
if (empty($aa)) {
    header("location: data.php");
    exit();
}

$selq = "SELECT email FROM personal_details WHERE aadhaar = '$aa'";
$q = mysqli_query($connect, $selq);

$sql = "SELECT * FROM `personal_details` WHERE aadhaar = '$aa'";
$result1 = mysqli_query($connect, $sql);
$a = $result1 ? $result1->fetch_assoc() : null;

if ($result = mysqli_fetch_assoc($q)) {
    $to_email = $result['email'];
    $subject = "ACCOUNT REJECTED - UNESCO LUMINA-X";
    $body = "Hello " . ($a['name'] ?? 'Candidate') . ",\n\nYour scholarship application has been reviewed and rejected by the Sovereign Verification Desk. Please re-verify your submission documents and refill the application before the designated deadline.\n\nUNESCO LUMINA-X Verification Authority";
    $header = "From: sovereign-desk@lumina-x.org";

    // Attempt notification delivery
    @mail($to_email, $subject, $body, $header);

    // Delete student record: MariaDB automatically purges attachment, bank_details,
    // college_details, and hostel_details via native ON DELETE CASCADE foreign key constraints
    $delq = "DELETE FROM personal_details WHERE aadhaar = '$aa'";
    $del_res = mysqli_query($connect, $delq);

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'action' => 'rejected', 'aadhaar' => $aa]);
        exit();
    }
    
    header("location: data.php?action=rejected");
    exit();
} else {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json', true, 404);
        echo json_encode(['success' => false, 'error' => 'not_found']);
        exit();
    }
    header("location: data.php?error=not_found");
    exit();
}
?>