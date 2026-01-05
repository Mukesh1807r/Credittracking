<?php
include "../config.php";

// Standard session management
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Security Guard: Check both general admin session and the Particular Password unlock status
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true || !isset($_SESSION['admin_unlocked'])) {
    header("Location: dashboard.php");
    exit;
}

$success = "";
$error = "";

// Logic to change the Particular Password
// Note: In a production app, you would save this to a database table.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass === $confirm_pass) {
        // Here you would typically run an UPDATE query on an 'admin_config' table
        // For this demo, we'll simulate success
        $success = "Particular Password updated successfully!";
    } else {
        $error = "Passwords do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Settings | AdminPRO</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { display: flex; min-height: 100vh; background: #f1f5f9; }
        .sidebar { width: 260px; background: #1e293b; color: white; padding: 20px; }
        .main-content { flex: 1; padding: 40px; }
        .settings-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 500px; }
        input { width: 100%; padding: 12px; margin: 10px 0 20px 0; border: 1px solid #ddd; border-radius: 8px; }
        .btn-save { background: #4f46e5; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; }
    </style>
</head>
<body>

<aside class="sidebar">
    <h2 style="color: #6366f1; margin-bottom: 30px;">AdminPRO</h2>
    <nav>
        <a href="dashboard.php" class="admin-nav-item">📊 Dashboard</a>
        <a href="manage_students.php" class="admin-nav-item">👥 Manage Students</a>
        <a href="curriculum_editor.php" class="admin-nav-item">📚 Curriculum Editor</a>
        <a href="settings.php" class="admin-nav-item active">⚙️ Settings</a>
        <a href="logout.php" class="admin-nav-item" style="color: #f87171; margin-top: 50px;">🚪 Logout</a>
    </nav>
</aside>

<main class="main-content">
    <h1>⚙️ System Settings</h1>
    <p style="color: #64748b; margin-bottom: 30px;">Manage your administrative security and preferences.</p>

    <div class="settings-card">
        <h3>Change Particular Password</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 20px;">This password is required to unlock the dashboard on any new device.</p>
        
        <?php if($success): ?> <p style="color: green; font-weight: bold;"><?= $success ?></p> <?php endif; ?>
        <?php if($error): ?> <p style="color: red;"><?= $error ?></p> <?php endif; ?>

        <form method="POST">
            <label>New Particular Password</label>
            <input type="password" name="new_password" required>
            
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required>
            
            <button type="submit" class="btn-save">Update Security Key</button>
        </form>
    </div>
</main>

</body>
</html>