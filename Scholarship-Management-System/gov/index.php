<?php
session_start();
if (!empty($_SESSION['user'])) {
    header("location: data.php");
    exit();
} else {
    header("location: login.php");
    exit();
}
?>