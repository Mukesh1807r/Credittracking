<?php
include "config.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$sid = (int)$_SESSION['id'];

/* LOAD COMMON SUBJECTS (FC/SBC/HS/etc.) */
$query = "
    SELECT cs.*, cp.completed, cp.grade
    FROM common_subjects cs
    LEFT JOIN common_progress cp
      ON cs.id = cp.subject_id AND cp.student_id = $sid
    WHERE cs.active = 1
    ORDER BY cs.course_nature ASC, cs.semester ASC
";
$subjects = $conn->query($query);

// Collect dynamic categories for filter pills
$categories = [];
$subjectList = [];
if ($subjects) {
    while($row = $subjects->fetch_assoc()) {
        $subjectList[] = $row;
        if (!in_array($row['course_nature'], $categories)) {
            $categories[] = $row['course_nature'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FC / SBC Subjects | Credit Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Filter Pills Styling */
        .filter-container { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .pill { 
            padding: 8px 20px; border-radius: 25px; border: 1px solid #e2e8f0; 
            background: white; cursor: pointer; transition: 0.3s; font-weight: 600;
        }
        .pill.active { background: #6366f1; color: white; border-color: #6366f1; }

        /* Grid Layout */
        .subject-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        
        .subject-card-pro {
            background: white; padding: 24px; border-radius: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border-top: 5px solid #e2e8f0; transition: transform 0.3s;
        }
        .subject-card-pro:hover { transform: translateY(-5px); }
        .card-completed { border-top-color: #10b981 !important; }
        .card-inprogress { border-top-color: #f59e0b !important; }

        .status-badge { font-size: 11px; padding: 4px 10px; border-radius: 20px; color: white; font-weight: bold; }
        .nature-label { color: #6366f1; font-weight: 800; font-size: 13px; }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <div class="header-flex">
        <h1>📘 Foundation & Skill Based Courses</h1>
        <button onclick="window.location.href='index.php'" class="btn-nav">← Dashboard</button>
    </div>

    <div class="filter-container">
        <button class="pill active" onclick="filterNature('all', this)">All Courses</button>
        <?php foreach($categories as $cat): ?>
            <button class="pill" onclick="filterNature('<?= $cat ?>', this)"><?= $cat ?></button>
        <?php endforeach; ?>
    </div>

    <div class="subject-grid" id="subject-list">
        <?php foreach($subjectList as $s): 
            $statusClass = '';
            if($s['completed'] && !empty($s['grade'])) $statusClass = 'card-completed';
            elseif($s['completed']) $statusClass = 'card-inprogress';
        ?>
        <div class="subject-card-pro <?= $statusClass ?>" data-nature="<?= $s['course_nature'] ?>">
            <form method="post" action="update_common_progress.php">
                <input type="hidden" name="subject_id" value="<?= $s['id'] ?>">

                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                    <span class="nature-label"><?= $s['course_nature'] ?></span>
                    <?php if($s['completed'] && !empty($s['grade'])): ?>
                        <span class="status-badge" style="background:#10b981">Completed</span>
                    <?php elseif($s['completed']): ?>
                        <span class="status-badge" style="background:#f59e0b">In Progress</span>
                    <?php endif; ?>
                </div>

                <label style="display: flex; gap: 12px; cursor: pointer;">
                    <input type="checkbox" name="completed" onchange="this.form.submit()" 
                           <?= $s['completed'] ? 'checked' : '' ?> style="width:20px; height:20px;">
                    <div>
                        <b style="font-size: 1.1rem;"><?= htmlspecialchars($s['course_code']) ?></b><br>
                        <span style="color: #64748b; font-size: 0.95rem;"><?= htmlspecialchars($s['course_title']) ?></span>
                    </div>
                </label>

                <div style="margin-top: 15px; font-size: 0.85rem; color: #94a3b8; font-weight: 600;">
                    Credits: <?= $s['credits'] ?> • Sem <?= $s['semester'] ?>
                </div>

                <?php if($s['completed']): ?>
                    <select name="grade" onchange="this.form.submit()" class="grade-select" style="width: 100%; margin-top: 15px;">
                        <option value="">Select Final Grade</option>
                        <?php foreach(['O','A+','A','B+','B','C'] as $g): ?>
                            <option <?= ($s['grade']==$g?'selected':'') ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;"></div>

<script>
function filterNature(nature, btn) {
    document.querySelectorAll('.pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    document.querySelectorAll('.subject-card-pro').forEach(card => {
        if (nature === 'all' || card.getAttribute('data-nature') === nature) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });
}

/* Notification Trigger */
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'updated') {
        const toast = document.createElement('div');
        toast.className = 'toast success';
        toast.innerHTML = '✅ Progress Updated!';
        document.getElementById('toast-container').appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
});
</script>
</body>
</html>