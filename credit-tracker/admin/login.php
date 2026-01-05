<?php
include "../config.php";

// Security Guard: Prevent non-admins from entering
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit;
}

/* =========================
    FETCH SYSTEM ANALYTICS
========================= */
// Count total students
$totalStudents = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];

// Calculate Global Average GPA
$globalGpaRes = $conn->query("
    SELECT AVG(CASE WHEN grade = 'O' THEN 10 WHEN grade = 'A+' THEN 9 WHEN grade = 'A' THEN 8 
    WHEN grade = 'B+' THEN 7 WHEN grade = 'B' THEN 6 WHEN grade = 'C' THEN 5 ELSE 0 END) as avg_gpa 
    FROM progress WHERE completed = 1
");
$globalGpa = round($globalGpaRes->fetch_assoc()['avg_gpa'], 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel | Credit Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { display: flex; min-height: 100vh; background: #f1f5f9; }
        .sidebar { width: 260px; background: #1e293b; color: white; padding: 20px; }
        .main-content { flex: 1; padding: 40px; }
        .admin-nav-item { display: block; padding: 12px; color: #cbd5e1; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .admin-nav-item:hover { background: #334155; color: white; }
        .admin-nav-item.active { background: #4f46e5; color: white; }
        .stat-grid-admin { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .admin-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: bold; color: #64748b; }
    </style>
</head>
<body>

<aside class="sidebar">
    <h2 style="color: #6366f1; margin-bottom: 30px;">AdminPRO</h2>
    <nav>
        <a href="#" class="admin-nav-item active">📊 Dashboard</a>
        <a href="#" class="admin-nav-item">👥 Manage Students</a>
        <a href="#" class="admin-nav-item">📚 Curriculum Editor</a>
        <a href="#" class="admin-nav-item">⚙️ Settings</a>
        <a href="logout.php" class="admin-nav-item" style="color: #f87171; margin-top: 50px;">🚪 Logout</a>
    </nav>
</aside>

<main class="main-content">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>System Overview</h1>
        <div style="background: white; padding: 10px 20px; border-radius: 50px; font-weight: bold; color: #4f46e5;">
            Welcome, Head Admin 👋
        </div>
    </header>

    <div class="stat-grid-admin">
        <div class="admin-card">
            <p style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Total Students</p>
            <h2 style="font-size: 2rem; margin: 10px 0;"><?= $totalStudents ?></h2>
        </div>
        <div class="admin-card">
            <p style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Global Avg GPA</p>
            <h2 style="font-size: 2rem; margin: 10px 0; color: #10b981;"><?= $globalGpa ?></h2>
        </div>
        <div class="admin-card">
            <p style="color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Curriculum Files</p>
            <h2 style="font-size: 2rem; margin: 10px 0;">12</h2>
        </div>
    </div>

    <div class="admin-card">
        <h3>Recent Student Progress</h3>
        <table>
            <thead>
                <tr>
                    <th>Reg No</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $recent = $conn->query("SELECT * FROM students LIMIT 5");
                while($row = $recent->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['reg_no'] ?></td>
                    <td><b><?= strtoupper($row['name']) ?></b></td>
                    <td><?= $row['department'] ?></td>
                    <td><span style="color: #10b981; font-weight: bold;">Active</span></td>
                    <td><button class="pill" style="padding: 5px 12px; font-size: 0.7rem;">View Details</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>