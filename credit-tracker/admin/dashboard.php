<?php
include "../config.php";

// Security Guard: Prevent non-admins from entering
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit;
}

/* =========================
    FETCH SYSTEM ANALYTICS
========================= */
// Count total students
$totalStudents = 0;
$studentCountQuery = $conn->query("SELECT COUNT(*) as total FROM students");
if ($studentCountQuery) {
    $totalStudents = $studentCountQuery->fetch_assoc()['total'];
}

// Calculate Global Average GPA
$globalGpa = 0.00;
$globalGpaRes = $conn->query("
    SELECT AVG(CASE WHEN grade = 'O' THEN 10 WHEN grade = 'A+' THEN 9 WHEN grade = 'A' THEN 8 
    WHEN grade = 'B+' THEN 7 WHEN grade = 'B' THEN 6 WHEN grade = 'C' THEN 5 ELSE 0 END) as avg_gpa 
    FROM progress WHERE completed = 1 AND grade IS NOT NULL AND grade != ''
");

if ($globalGpaRes) {
    $row = $globalGpaRes->fetch_assoc();
    $globalGpa = $row['avg_gpa'] ? round($row['avg_gpa'], 2) : 0.00;
}

// Count curriculum files dynamically
$curriculumFiles = count(glob("../curriculum/*.json"));

/* =========================
    FETCH STUDENT MESSAGES
========================= */
$messagesQuery = $conn->query("
    SELECT m.*, s.name as student_name 
    FROM messages m 
    JOIN students s ON m.student_id = s.id 
    ORDER BY m.created_at DESC LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .admin-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: bold; color: #64748b; }
        .pill { padding: 5px 12px; border-radius: 50px; border: none; background: #4f46e5; color: white; cursor: pointer; font-size: 0.75rem; }
        
        /* New Styles for Reply Form */
        .reply-input { padding: 8px; border: 1px solid #ddd; border-radius: 6px; width: 180px; font-size: 0.8rem; }
        .btn-send-reply { background: #10b981; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<aside class="sidebar">
    <h2 style="color: #6366f1; margin-bottom: 30px;">AdminPRO</h2>
    <nav>
        <a href="dashboard.php" class="admin-nav-item active">📊 Dashboard</a>
        <a href="manage_students.php" class="admin-nav-item">👥 Manage Students</a>
        <a href="curriculum_editor.php" class="admin-nav-item">📚 Curriculum Editor</a>
        <a href="settings.php" class="admin-nav-item">⚙️ Settings</a>
        <a href="logout.php" class="admin-nav-item" style="color: #f87171; margin-top: 50px;">🚪 Logout</a>
    </nav>
</aside>

<main class="main-content">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>System Overview</h1>
        <div style="background: white; padding: 10px 20px; border-radius: 50px; font-weight: bold; color: #4f46e5;">
            Welcome, Admin 👋
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
            <h2 style="font-size: 2rem; margin: 10px 0;"><?= $curriculumFiles ?></h2>
        </div>
    </div>

    <div class="admin-card">
        <h3>👥 Recent Student Progress</h3>
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
                $recent = $conn->query("SELECT * FROM students ORDER BY id DESC LIMIT 5");
                if ($recent && $recent->num_rows > 0):
                    while($row = $recent->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['reg_no']) ?></td>
                    <td><b><?= strtoupper(htmlspecialchars($row['name'])) ?></b></td>
                    <td><?= htmlspecialchars($row['department']) ?></td>
                    <td><span style="color: #10b981; font-weight: bold;">Active</span></td>
                    <td><button class="pill" onclick="window.location.href='manage_students.php?id=<?= $row['id'] ?>'">View</button></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5" style="text-align:center;">No students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-card">
        <h3>📩 Student Queries & Quick Reply</h3>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Query Details</th>
                    <th>Date</th>
                    <th>Quick Reply</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($messagesQuery && $messagesQuery->num_rows > 0): ?>
                    <?php while($msg = $messagesQuery->fetch_assoc()): 
                        $replied = !empty($msg['admin_reply']); 
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($msg['student_name']) ?></td>
                        <td>
                            <div style="font-weight:bold; color:#4f46e5;"><?= htmlspecialchars($msg['subject']) ?></div>
                            <div style="font-size:0.8rem; color:#64748b; cursor:pointer;" onclick="alert('Full Message: <?= addslashes(htmlspecialchars($msg['message'])) ?>')">
                                <?= htmlspecialchars(substr($msg['message'], 0, 40)) ?>... (Read)
                            </div>
                        </td>
                        <td><?= date('M d, H:i', strtotime($msg['created_at'])) ?></td>
                        <td>
                            <?php if($replied): ?>
                                <span style="color: #10b981; font-weight: bold; font-size: 0.8rem;">✅ Replied</span>
                            <?php else: ?>
                                <form method="POST" action="process_reply.php" style="display: flex; gap: 5px;">
                                    <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                    <input type="text" name="reply_text" class="reply-input" placeholder="Type reply..." required>
                                    <button type="submit" class="btn-send-reply">Send</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center;">No new messages.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>