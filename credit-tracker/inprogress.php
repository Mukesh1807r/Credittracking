<?php
include "config.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$studentId = (int)$_SESSION['id'];
$student = $conn->query("SELECT * FROM students WHERE id=".$studentId)->fetch_assoc();

/* =========================
    FETCH IN-PROGRESS DATA
========================= */
// We use a LEFT JOIN to pull details from the curriculum table based on subject_id
$progressRes = $conn->query("
    SELECT p.subject_id, p.grade, c.* FROM progress p
    LEFT JOIN curriculum c ON p.subject_id = c.id
    WHERE p.student_id = $studentId 
    AND p.completed = 1 
    AND (p.grade IS NULL OR p.grade = '')
");

$dynamicCategories = [];
$subjectsArray = [];

if ($progressRes) {
    while($row = $progressRes->fetch_assoc()) {
        $subjectsArray[] = $row;
        if (!empty($row['category']) && !in_array($row['category'], $dynamicCategories)) {
            $dynamicCategories[] = $row['category'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>In-Progress Subjects | AcademicTracker</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary: #6366f1;
            --warning: #f59e0b;
        }
        .ip-container { max-width: 1100px; margin: 40px auto; padding: 20px; }
        .ip-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        
        .ip-card { 
            background: white; padding: 24px; border-radius: 18px; 
            border-left: 6px solid var(--warning); 
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .ip-card:hover { transform: translateY(-5px); }

        .search-box { width: 100%; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px; font-size: 16px; box-sizing: border-box; }
        .filter-group { margin-bottom: 25px; display: flex; gap: 10px; flex-wrap: wrap; }
        .status-tag { background: #fff7ed; color: #c2410c; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .category-label { color: var(--primary); font-weight: 800; font-size: 0.9rem; }
        
        .grade-select { 
            width: 100%; margin-top: 15px; padding: 10px; border-radius: 8px; 
            border: 1px solid #cbd5e1; background-color: #f8fafc; font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">

<div class="ip-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="margin:0;">⏳ In-Progress Subjects</h1>
        <button onclick="window.location.href='index.php'" class="btn-nav" style="background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer;">⬅ Back to Dashboard</button>
    </div>

    <input type="text" id="ipSearch" class="search-box" placeholder="🔍 Search code or name..." onkeyup="filterIP()">
    
    <div class="filter-group">
        <button class="pill active" onclick="filterCat('all', this)">All Categories</button>
        <?php foreach($dynamicCategories as $cat): ?>
            <button class="pill" onclick="filterCat('<?= $cat ?>', this)"><?= $cat ?></button>
        <?php endforeach; ?>
    </div>

    

    <div class="ip-grid" id="ipGrid">
        <?php if (!empty($subjectsArray)): ?>
            <?php foreach($subjectsArray as $row): 
                // Attempt to find the course title across multiple common column names
                $displayName = $row['courseTitle'] ?? $row['course_name'] ?? $row['course_title'] ?? $row['subject_name'] ?? "";
                $displayCode = $row['course_code'] ?? $row['courseCode'] ?? "ID: " . $row['subject_id'];
            ?>
                <div class="ip-card" data-category="<?= htmlspecialchars($row['category'] ?? 'Uncategorized') ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="status-tag">In Progress</span>
                        <span class="category-label"><?= htmlspecialchars($row['category'] ?? 'N/A') ?></span>
                    </div>
                    
                    <h3 style="margin: 20px 0 5px 0; color: #1e293b;"><?= htmlspecialchars($displayCode) ?></h3>
                    
                    <?php if ($displayName): ?>
                        <p style="color: #64748b; font-weight: 500; min-height: 40px; margin: 0;"><?= htmlspecialchars($displayName) ?></p>
                    <?php else: ?>
                        <p style="color: #ef4444; font-size: 0.8rem; font-style: italic; margin: 0;">⚠️ Subject details missing in Curriculum table.</p>
                    <?php endif; ?>
                    
                    <form method="post" action="update_progress.php">
                        <input type="hidden" name="subject_id" value="<?= $row['subject_id'] ?>">
                        <input type="hidden" name="completed" value="1">
                        <select name="grade" onchange="this.form.submit()" class="grade-select">
                            <option value="">Assign Final Grade</option>
                            <?php foreach(['O','A+','A','B+','B','C'] as $g): ?>
                                <option value="<?= $g ?>"><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 80px 20px; background: white; border-radius: 20px; border: 2px dashed #e2e8f0;">
                <span style="font-size: 3rem; display: block; margin-bottom: 10px;">☕</span>
                <h2 style="color: #94a3b8; margin:0;">All caught up! No subjects currently in progress.</h2>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterIP() {
    let input = document.getElementById('ipSearch').value.toLowerCase();
    let cards = document.getElementsByClassName('ip-card');
    for (let card of cards) {
        card.style.display = card.textContent.toLowerCase().includes(input) ? "block" : "none";
    }
}

function filterCat(cat, btn) {
    document.querySelectorAll('.pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    let cards = document.getElementsByClassName('ip-card');
    for (let card of cards) {
        let cardCat = card.getAttribute('data-category');
        if (cat === 'all' || cardCat === cat) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    }
}
</script>
</body>
</html>