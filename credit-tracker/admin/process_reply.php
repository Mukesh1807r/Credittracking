<?php
include "../config.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Security check
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { exit("Unauthorized"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg_id = (int)$_POST['message_id'];
    $reply = $conn->real_escape_string($_POST['reply_text']);

    // Update the record with the reply
    $sql = "UPDATE messages SET admin_reply = '$reply', status = 'replied' WHERE id = $msg_id";
    
    if ($conn->query($sql)) {
        header("Location: dashboard.php?status=replied");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>