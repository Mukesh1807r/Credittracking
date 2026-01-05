<?php
include "config.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id'])) {
    $student_id = (int)$_SESSION['id'];
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);

    $sql = "INSERT INTO messages (student_id, subject, message) VALUES ('$student_id', '$subject', '$message')";
    
    if ($conn->query($sql)) {
        header("Location: contact.php?status=sent");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>