<?php
include "../config.php";
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$reg_no = $_GET['reg_no'] ?? '';
$student = null;

if ($reg_no) {
    $student = $conn->query("SELECT * FROM students WHERE reg_no = '$reg_no'")->fetch_assoc();
}

if ($student) {
    $sid = $student['id'];
    // Fetch progress summary for this student
    $progress = $conn->query("SELECT COUNT(*) as completed, SUM(c.credits) as total_cr 
                              FROM progress p 
                              JOIN curriculum c ON p.subject_id = c.id 
                              WHERE p.student_id = $sid AND p.completed = 1")->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Oversight | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">
    <div class="nav">
        <button onclick="window.location.href='dashboard.php'" class="btn-nav">⬅ Dashboard</button>
    </div>

    <div class="container">
        <h1>🔍 Student Oversight</h1>

        <div class="admin-card">
            <form method="GET">
                <input name="reg_no" placeholder="Enter Register No" value="<?= $reg_no ?>" style="padding:10px; border-radius:8px; border:1px solid #ddd;">
                <button type="submit" class="pill">Search Student</button>
            </form>
        </div>

        <?php if ($student): ?>
            <div class="admin-card" style="margin-top:20px; border-left: 5px solid #4f46e5;">
                <h2><?= strtoupper($student['name']) ?></h2>
                <p> Dept: <?= $student['department'] ?> | Reg: <?= $student['regulation'] ?></p>
                <div class="stats-grid">
                    <p><b>Credits Earned:</b> <?= $progress['total_cr'] ?? 0 ?></p>
                    <p><b>Subjects Completed:</b> <?= $progress['completed'] ?></p>
                </div>
                <button onclick="window.location.href='../index.php?impersonate=<?= $student['id'] ?>'" class="btn-nav" style="background:#6366f1; color:white; margin-top:15px;">
                    View Live Dashboard
                </button>
            </div>
        <?php elseif ($reg_no): ?>
            <p style="color:red; margin-top:20px;">No student found with that Register Number.</p>
        <?php endif; ?>
    </div>
</body>
</html>