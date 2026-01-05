<?php
include "../config.php"; // Adjust path based on your folder structure

// Secure this page so only admins can access it
if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }

/* Handle Reply Submission */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_text'])) {
    $msg_id = (int)$_POST['message_id'];
    $reply = $conn->real_escape_string($_POST['reply_text']);
    
    // Update the database with your reply and change status
    $conn->query("UPDATE messages SET admin_reply = '$reply', status = 'replied' WHERE id = $msg_id");
    header("Location: admin_messages.php?status=success");
    exit;
}

$all_messages = $conn->query("SELECT m.*, s.name FROM messages m JOIN students s ON m.student_id = s.id ORDER BY m.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin | Manage Inquiries</title>
    <style>
        body { font-family: sans-serif; background: #f1f5f9; padding: 40px; }
        .admin-container { max-width: 1000px; margin: 0 auto; }
        .message-card { background: white; padding: 25px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .student-name { color: #6366f1; font-weight: bold; }
        textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-top: 10px; box-sizing: border-box; }
        .btn-reply { background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>

<div class="admin-container">
    <h2>📩 Student Inquiries</h2>
    
    <?php while($msg = $all_messages->fetch_assoc()): ?>
        <div class="message-card">
            <div style="display:flex; justify-content: space-between;">
                <span class="student-name"><?= htmlspecialchars($msg['name']) ?></span>
                <small><?= $msg['created_at'] ?></small>
            </div>
            <p><strong>Subject:</strong> <?= htmlspecialchars($msg['subject']) ?></p>
            <p><strong>Message:</strong> <?= htmlspecialchars($msg['message']) ?></p>

            <form method="POST">
                <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                <textarea name="reply_text" rows="3" placeholder="Type your reply here..."><?= htmlspecialchars($msg['admin_reply'] ?? '') ?></textarea>
                <button type="submit" class="btn-reply">Send Reply</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>