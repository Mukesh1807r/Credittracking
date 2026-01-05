<?php
include "../config.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (isset($_GET['id']) && isset($_SESSION['admin'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE messages SET is_read = 1 WHERE id = $id");
    header("Location: dashboard.php");
    exit;
}
?>