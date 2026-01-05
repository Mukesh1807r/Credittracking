<?php
include "config.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

/* =========================
    FETCH STUDENT DATA
========================= */
$studentId = (int)$_SESSION['id'];
$student = $conn->query(
    "SELECT * FROM students WHERE id=".$studentId
)->fetch_assoc();

$entryType = $student['entry_type'] ?? 'REGULAR';
$dept = $student['department'];
$reg  = $student['regulation'];

/* =========================
    LOAD CURRICULUM FROM JSON
========================= */
$jsonFile = "curriculum/{$dept}_{$reg}.json";
if (!file_exists($jsonFile)) {
    die("Curriculum file not found for $dept $reg");
}

$subjects = json_decode(file_get_contents($jsonFile), true);

/* =========================
    INITIALIZE VARIABLES
========================= */
$categoryTotal = [];
$categoryCompleted = [];
$catCredits = [];
$totalCredits = 0;
$gradeSum = 0;
$gradeCredits = 0;

$gradeMap = [
    'O' => 10, 'A+' => 9, 'A' => 8, 
    'B+' => 7, 'B' => 6, 'C' => 5, 'U' => 0
];

/* =========================
    OPTIMIZATION: FETCH ALL MAPPINGS ONCE
========================= */
// We fetch the entire curriculum table into an array at once to save queries
$dbMap = [];
$mapRows = $conn->query("SELECT id, course_code FROM curriculum");
while($row = $mapRows->fetch_assoc()) {
    $dbMap[$row['course_code']] = $row['id'];
}

/* =========================
    FETCH PROGRESS FROM DATABASE
========================= */
$progressRes = $conn->query("SELECT * FROM progress WHERE student_id = $studentId");
$userProgress = [];
while($row = $progressRes->fetch_assoc()){
    $userProgress[$row['subject_id']] = $row;
}

/* =========================
    MERGE & CALCULATE PROGRESS
========================= */
foreach ($subjects as &$s) {
    $code = $s['courseCode']; 
    
    // OPTIMIZED: Use the pre-fetched array instead of a new SQL query
    $db_id = $dbMap[$code] ?? 0; 
    
    $s['db_id'] = $db_id;
    $s['completed'] = $userProgress[$db_id]['completed'] ?? 0;
    $s['grade'] = $userProgress[$db_id]['grade'] ?? null;

    $cat = $s['category'];
    $categoryTotal[$cat] = ($categoryTotal[$cat] ?? 0) + $s['credits'];

    if ($s['completed']) {
        $totalCredits += $s['credits'];
        $categoryCompleted[$cat] = ($categoryCompleted[$cat] ?? 0) + $s['credits'];
        $catCredits[$cat] = ($catCredits[$cat] ?? 0) + $s['credits'];

        if ($s['grade'] && isset($gradeMap[$s['grade']])) {
            $gradeSum += $s['credits'] * $gradeMap[$s['grade']];
            $gradeCredits += $s['credits'];
        }
    }
}
unset($s);

$gpa = $gradeCredits ? round($gradeSum / $gradeCredits, 2) : 0;

/* =========================
    DYNAMIC CREDIT REQUIREMENTS 
========================= */
$requirements = [];
$requiredTotal = 0;

$rq = $conn->query("
    SELECT category, required_credits 
    FROM credit_requirements 
    WHERE department='$dept' 
    AND regulation='$reg' 
    AND entry_type='$entryType'
");

if ($rq && $rq->num_rows > 0) {
    while ($r = $rq->fetch_assoc()) {
        $requirements[$r['category']] = $r['required_credits'];
        $requiredTotal += $r['required_credits'];
    }
}

/* =========================
    CHECK FOR MILESTONES
========================= */
$completedCategory = "";
foreach($requirements as $cat => $need) {
    $done = $catCredits[$cat] ?? 0;
    if ($need > 0 && $done >= $need) {
        $completedCategory = $cat; 
    }
}

/* =========================
    SMART SUBJECT SUGGESTIONS
========================= */
$suggestedSubjects = [];
foreach ($requirements as $cat => $required) {
    $earned = $catCredits[$cat] ?? 0;
    if ($earned < $required) {
        foreach ($subjects as $sub) {
            if ($sub['category'] === $cat && !$sub['completed']) {
                $suggestedSubjects[] = $sub;
            }
        }
    }
}
$creditDeficit = max(0, $requiredTotal - $totalCredits);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Credit Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #ec4899;
            --glass: rgba(255, 255, 255, 0.7);
        }

        /* HERO HEADER UI */
        .hero-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            padding: 2.5rem;
            border-radius: 24px;
            color: white;
            display: flex;
            align-items: center;
            gap: 25px;
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.2);
            margin: 20px 0 40px 0;
        }

        .hero-avatar {
            width: 90px;
            height: 90px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 800;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        /* Special button styling */
        .btn-special {
            background: linear-gradient(135deg, #6366f1, #ec4899) !important;
            color: white !important;
            border: none !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .btn-special:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(236, 72, 153, 0.4);
        }
        /* STAT CARDS UI */
        .stats-grid-new {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .modern-stat-card {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            text-align: center;
            transition: transform 0.3s;
        }
        .modern-stat-card:hover { transform: translateY(-5px); }

        .modern-stat-card .label { color: #64748b; font-size: 0.9rem; font-weight: 600; display: block; }
        .modern-stat-card .value { font-size: 1.8rem; font-weight: 800; color: #1e293b; margin: 10px 0; display: block; }

        /* SUBJECT GRID UI */
        #subject-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .subject-card-pro {
            background: white;
            padding: 24px;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
            position: relative;
        }

        .subject-card-pro:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        /* PROGRESS BAR UI */
        .modern-progress-bg { background: #f1f5f9; height: 12px; border-radius: 10px; overflow: hidden; margin-top: 8px; }
        .modern-progress-fill { height: 100%; border-radius: 10px; background: linear-gradient(90deg, #6366f1, #a855f7); }

        /* NOTIFICATION STYLES */
        .toast {
            background: white;
            color: #1e293b;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid var(--primary);
            animation: slideIn 0.3s ease-out forwards;
            min-width: 250px;
        }
        .toast.success { border-left-color: #10b981; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            to { opacity: 0; transform: translateY(10px); }
        }

        .sim-tag { display: none; color: #ec4899; font-weight: bold; font-size: 0.8rem; }
        .badge { display: inline-block; color: white; padding: 4px 10px; border-radius: 20px; font-size: 11px; margin-top: 5px; font-weight: bold; }
        .prediction-container { display: none; }
    </style>
</head>
<body class="bg-light">

<div class="nav">
    <div class="nav-left">
        <button onclick="window.location.href='home.php'" class="btn-nav">🏠 Home</button>
        <button onclick="window.location.href='completion.php'" class="btn-nav">🎓 Completion</button>
        <button onclick="window.location.href='planner.php'" class="btn-nav btn-special">
            <span style="margin-right: 5px;">✨</span> Smart Planner
        </button>
    </div>
    <div class="nav-right">
        <button onclick="toggleDark()" class="btn-nav">🌙 Dark Mode</button>
        <a href="logout.php" class="btn-nav logout-btn">Logout</a>
    </div>
</div>

<h1>Academic Credit Dashboard</h1>

<div class="card" id="report">
    <div class="student-header">
        <div class="avatar"><?= strtoupper(substr($student['name'] ?? 'S', 0, 1)) ?></div>
        <div class="student-info">
            <h2><?= strtoupper($student['name']) ?></h2>
            <p>
                Register No: <span><?= $student['reg_no'] ?></span><br>
                <?= $student['department'] ?> • <?= $student['regulation'] ?> • <?= $entryType ?>
            </p>
            <?php
            if(!empty($catCredits)) {
                $topCat = array_search(max($catCredits), $catCredits);
                $badgeName = "Academic Scholar"; $badgeCol = "#6366f1";
                if($topCat == 'PC') { $badgeName = "Core Specialist"; $badgeCol = "#4f46e5"; }
                elseif($topCat == 'BS') { $badgeName = "Science Foundation"; $badgeCol = "#10b981"; }
                elseif($topCat == 'PE') { $badgeName = "Elective Explorer"; $badgeCol = "#f59e0b"; }
                echo "<span class='badge' style='background:$badgeCol;'>$badgeName</span>";
            }
            ?>
        </div>
    </div>

    <div class="stats-grid-new">
        <div class="modern-stat-card">
            <span class="label">Earned Credits</span>
            <span class="value"><?= $totalCredits ?> / <?= $requiredTotal ?></span>
        </div>
        <div class="modern-stat-card" style="border-bottom: 4px solid var(--primary);">
            <span class="label">Current GPA</span>
            <span class="value" id="live-gpa"><?= $gpa ?></span>
            <span id="sim-indicator" class="sim-tag">(Simulated)</span>
        </div>
        <div class="modern-stat-card">
            <span class="label">Remaining</span>
            <span class="value" style="color: <?= $creditDeficit > 0 ? '#f59e0b' : '#10b981' ?>;"><?= $creditDeficit ?> Cr</span>
        </div>
    </div>

    <div class="card" style="border-radius: 24px;">
        <h3>📊 Category Progress</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 25px;">
        <?php foreach($requirements as $cat => $need): 
            $done = $catCredits[$cat] ?? 0;
            $percent = ($need > 0) ? round(($done / $need) * 100) : 0;
        ?>
            <div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 0.9rem;">
                    <span><?= $cat ?></span>
                    <span><?= $done ?>/<?= $need ?> <?= $done >= $need ? "✅" : "" ?></span>
                </div>
                <div class="modern-progress-bg">
                    <div class="modern-progress-fill" style="width: <?= min($percent, 100) ?>%; background: <?= $done >= $need ? '#10b981' : '' ?>"></div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

<div class="card suggestion-box" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(168, 85, 247, 0.1)); border: 1px solid rgba(99, 102, 241, 0.2); margin-top:20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 24px;">✨</span>
            <h3 style="margin: 0; color: #4f46e5;">Smart Recommendations</h3>
        </div>
        <button onclick="window.location.href='planner.php'" style="background: #4f46e5; color: white; border: none; padding: 5px 15px; border-radius: 20px; font-size: 12px; cursor: pointer; font-weight: bold;">
            Open Full Planner →
        </button>
    </div>
    
    <p>You need <strong><?= $creditDeficit ?> more credits</strong>. Below are priority subjects.</p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;">
        <?php if (!empty($suggestedSubjects)): ?>
            <?php 
            $displayLimit = array_slice($suggestedSubjects, 0, 6);
            foreach ($displayLimit as $s): 
            ?>
                <div style="background: white; padding: 12px; border-radius: 12px; display: flex; align-items: center; gap: 10px; border: 1px solid #eef2ff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <div style="background: #4f46e5; color: white; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 6px;"><?= $s['category'] ?></div>
                    <div style="flex: 1;">
                        <p style="font-size: 12px; font-weight: bold; color: #6366f1; margin: 0;"><?= $s['courseCode'] ?></p>
                        <p style="font-size: 13px; color: #1e293b; margin: 0; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 150px;"><?= $s['courseTitle'] ?></p>
                    </div>
                    <div style="font-size: 13px; font-weight: bold; color: #10b981;">+<?= $s['credits'] ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #10b981; font-weight: bold;">🎉 All category requirements are fulfilled!</p>
        <?php endif; ?>
    </div>
</div>

<div class="summary">
    <h3>Credit Distribution</h3>
    <div class="chart-box"><canvas id="chart"></canvas></div>
</div>

<div class="card">
    <h3>📊 Category-wise Progress</h3>
    <?php foreach($categoryTotal as $cat => $total): 
        $done = $categoryCompleted[$cat] ?? 0;
        $percent = $total > 0 ? round(($done / $total) * 100) : 0;
    ?>
        <div class="cat-row">
            <div class="cat-head">
                <b><?= $cat ?></b>
                <span><?= $done ?> / <?= $total ?> credits</span>
            </div>
            <div class="progress-bar" style="background:#eee; height:10px; border-radius:5px;">
                <div class="progress-fill" style="width:<?= $percent ?>%; background:#2563eb; height:100%; border-radius:5px;"></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;"></div>
<button onclick="window.location.href='inprogress.php'" class="btn-nav">⏳ In Progress</button>
<button onclick="window.location.href='completion.php'" class="btn-nav">✅ Completed</button>
<div class="search-box">
    <input type="text" id="search" placeholder="🔍 Search subject by code or name" onkeyup="filterSubjects()">
</div>

<div class="filter-section">
    <p class="filter-title">Filter by Category</p>
    <div class="filter-container">
        <button class="pill active" onclick="filterCategory('all', this)">All Subjects</button>
        <?php foreach(array_keys($categoryTotal) as $cat): ?>
            <button class="pill" onclick="filterCategory('<?= $cat ?>', this)">
                <?= $cat ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<h2>Subjects</h2>
    <div id="subject-list">
    <?php foreach($subjects as $s): ?>
        <div class="subject-card-pro subject" data-category="<?= $s['category'] ?>">
            <form method="post" action="update_progress.php">
                <input type="hidden" name="subject_id" value="<?= $s['db_id'] ?>">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="completed" onchange="this.form.submit()" <?= $s['completed'] ? 'checked' : '' ?> style="width: 20px; height: 20px; margin-top: 4px;">
                    <div>
                        <b style="font-size: 1.1rem;"><?= htmlspecialchars($s['courseCode']) ?></b><br>
                        <span style="color: #64748b;"><?= htmlspecialchars($s['courseTitle']) ?></span>
                    </div>
                </label>
                
                <div style="margin-top: 10px;">
                    <?php if ($s['completed'] && empty($s['grade'])): ?>
                        <span class="badge" style="background: #f59e0b;">⏳ In Progress</span>
                    <?php elseif ($s['completed']): ?>
                        <span class="badge" style="background: #10b981;">✅ Completed</span>
                    <?php endif; ?>
                </div>

                <div style="font-size: 0.8rem; font-weight: bold; color: var(--primary); margin-top: 10px; text-transform: uppercase;">
                    <?= htmlspecialchars($s['category']) ?> • <?= (int)$s['credits'] ?> Credits
                </div>
                
                <?php if ($s['completed']): ?>
                    <select name="grade" onchange="this.form.submit()" class="grade-select">
                        <option value="">Assigned Grade</option>
                        <?php foreach(['O','A+','A','B+','B','C'] as $g): 
                            $sel = ($s['grade'] == $g) ? 'selected' : ''; ?>
                            <option <?= $sel ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </form>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<div class="summary">
    <button onclick="downloadPDF()">Download PDF Report</button>
</div>

<script>
if (localStorage.getItem('theme') === 'dark') {
    document.body.classList.add('dark');
}

function toggleDark(){ 
    document.body.classList.toggle("dark"); 
    localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
}

/* TOGGLE PREDICTOR VISIBILITY */
function togglePredictor(checkbox) {
    const container = checkbox.closest('.subject').querySelector('.prediction-container');
    if (checkbox.checked) {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
        const select = container.querySelector('.sim-grade-select');
        if(select) select.value = "0";
        calculateSimulatedGPA();
    }
}

/* GPA SIMULATION LOGIC */
function calculateSimulatedGPA() {
    let totalPoints = <?= $gradeSum ?>;
    let totalCredits = <?= $gradeCredits ?>;
    let isSimulating = false;

    document.querySelectorAll('.sim-grade-select').forEach(select => {
        let points = parseFloat(select.value);
        let credits = parseFloat(select.closest('.subject-card-pro').dataset.credits);

        if (points > 0) {
            totalPoints += (points * credits);
            totalCredits += credits;
            isSimulating = true;
        }
    });

    if (totalCredits > 0) {
        let newGPA = (totalPoints / totalCredits).toFixed(2);
        document.getElementById('live-gpa').innerText = newGPA;
        document.getElementById('sim-indicator').style.display = isSimulating ? 'inline' : 'none';
        document.getElementById('live-gpa').style.color = isSimulating ? '#ec4899' : '';
    }
}

/* CHART LOGIC */
<?php if(!empty($catCredits)): ?>
new Chart(document.getElementById("chart"), {
    type: "pie",
    data: {
        labels: <?= json_encode(array_keys($catCredits)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($catCredits)) ?>,
            backgroundColor: ["#2563eb","#22c55e","#f97316","#dc2626","#8b5cf6","#14b8a6"]
        }]
    }
});
<?php endif; ?>

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(20);
    doc.setTextColor(40);
    doc.text("Academic Credit Report", 14, 22);

    doc.setFontSize(11);
    doc.setTextColor(100);
    const name = "<?= strtoupper($student['name']) ?>";
    const regNo = "<?= $student['reg_no'] ?>";
    const dept = "<?= $student['department'] ?> (<?= $student['regulation'] ?>)";
    
    doc.text(`Student: ${name}`, 14, 32);
    doc.text(`Registration No: ${regNo}`, 14, 38);
    doc.text(`Department: ${dept}`, 14, 44);
    doc.text(`Overall GPA: <?= $gpa ?>`, 14, 50);
    doc.text(`Total Credits: <?= $totalCredits ?> / <?= $requiredTotal ?>`, 14, 56);

    const tableRows = [];
    <?php foreach($subjects as $s): ?>
        tableRows.push([
            "<?= $s['courseCode'] ?>",
            "<?= $s['courseTitle'] ?>",
            "<?= $s['category'] ?>",
            "<?= $s['credits'] ?>",
            "<?= $s['completed'] ? 'Completed' : 'Pending' ?>",
            "<?= $s['grade'] ?? '-' ?>"
        ]);
    <?php endforeach; ?>

    doc.autoTable({
        startY: 65,
        head: [['Code', 'Course Title', 'Category', 'Credits', 'Status', 'Grade']],
        body: tableRows,
        theme: 'striped',
        headStyles: { fillColor: [99, 102, 241] },
        styles: { fontSize: 9, cellPadding: 3 },
        columnStyles: {
            0: { cellWidth: 25 },
            1: { cellWidth: 'auto' },
            2: { halign: 'center' },
            3: { halign: 'center' },
            4: { halign: 'center' },
            5: { halign: 'center' }
        }
    });

    doc.save(`Credit_Report_${regNo}.pdf`);
}

function filterSubjects(){
    const q = document.getElementById("search").value.toLowerCase();
    document.querySelectorAll(".subject").forEach(s => {
        s.style.display = s.innerText.toLowerCase().includes(q) ? "block" : "none";
    });
}

function filterCategory(category, element) {
    document.querySelectorAll('.pill').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
    const subjects = document.querySelectorAll('.subject-card-pro');
    subjects.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        if (category === 'all' || cardCategory === category) {
            card.style.display = "block";
            card.style.animation = "fadeInUp 0.4s ease forwards";
        } else {
            card.style.display = "none";
        }
    });
}

/* NOTIFICATION LOGIC */
function showNotify(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span>${type === 'success' ? '✅' : 'ℹ️'}</span><span style="font-weight: 600; font-size: 14px;">${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.5s ease forwards';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// Auto-trigger when page reloads after an update
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    
    // 1. Check for general update
    if (status === 'updated') {
        showNotify('Academic progress saved!');
    }

    // 2. Check for category completion
    <?php if ($completedCategory !== ""): ?>
        setTimeout(() => {
            showNotify('🏆 Goal Met: ' + '<?= $completedCategory ?>' + ' Requirement Fulfilled!', 'success');
        }, 800); 
    <?php endif; ?>
});
</script>
</body>
</html>