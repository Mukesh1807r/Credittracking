<?php
include "../config.php";

// Standard session management for universal device access
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Access is granted based on the 'admin' session key, allowing access from any device
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { 
    header("Location: login.php"); 
    exit; 
}

$dir = "../curriculum/";

/* =========================
    ADD NEW DEPARTMENT LOGIC
========================= */
if (isset($_POST['add_dept'])) {
    // Sanitize inputs
    $dept_name = strtoupper(preg_replace('/[^A-Za-z0-9_]/', '', $_POST['dept_name']));
    $reg_year = preg_replace('/[^0-9]/', '', $_POST['reg_year']);
    $newFileName = "{$dept_name}_R{$reg_year}.json";

    if (!file_exists($dir . $newFileName)) {
        // Create an empty JSON array file
        file_put_contents($dir . $newFileName, json_encode([], JSON_PRETTY_PRINT));
        $success_msg = "Department file $newFileName created successfully!";
    } else {
        $error_msg = "Error: This Department/Regulation already exists.";
    }
}

// Scans the directory for existing JSON curriculum files
$files = array_diff(scandir($dir), array('.', '..'));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum Editor | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">
    <div class="container" style="margin-top: 40px;">
        <button onclick="window.location.href='dashboard.php'" class="btn-nav">⬅ Back</button>
        <h1>📚 Curriculum Editor</h1>
        
        <div class="admin-card" style="margin-bottom: 30px; border-top: 4px solid #6366f1;">
            <h3>➕ Add New Department/Regulation</h3>
            <form method="POST" style="display: flex; gap: 10px; margin-top: 15px;">
                <input type="text" name="dept_name" placeholder="Dept Code (e.g. MECH)" required>
                <input type="text" name="reg_year" placeholder="Regulation (e.g. 2021)" required>
                <button type="submit" name="add_dept" class="btn-special">Create Department</button>
            </form>
            <?php if(isset($success_msg)) echo "<p style='color:green; font-weight:bold;'>$success_msg</p>"; ?>
            <?php if(isset($error_msg)) echo "<p style='color:red;'>$error_msg</p>"; ?>
        </div>

        <div class="admin-card">
            <h3>Available Curriculum Files</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
                <?php foreach($files as $file): ?>
                    <div class="card" style="text-align: center; padding: 20px;">
                        <p style="font-weight: bold;"><?= htmlspecialchars($file) ?></p>
                        <button class="pill" onclick="window.location.href='edit_file.php?file=<?= urlencode($file) ?>'">Edit Subjects</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>