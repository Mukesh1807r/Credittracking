<?php
include "config.php";

if (!isset($_SESSION['id'])) {
  header("Location: login.php");
  exit;
}

$student = $conn->query(
  "SELECT * FROM students WHERE id=".$_SESSION['id']
)->fetch_assoc();

/* ✅ SAFE FALLBACK FOR ENTRY TYPE */
$entryType = $student['entry_type'] ?? 'REGULAR';

/* SUBJECTS + PROGRESS */
$q = $conn->query("
SELECT c.*, p.completed, p.grade
FROM curriculum c
LEFT JOIN progress p
ON c.id=p.subject_id AND p.student_id={$student['id']}
WHERE c.department='{$student['department']}'
AND c.regulation='{$student['regulation']}'
ORDER BY c.semester
");

$totalCredits = 0;
$catCredits = [];
$gradeMap = ['O'=>10,'A+'=>9,'A'=>8,'B+'=>7,'B'=>6,'C'=>5];
$gradeSum = 0;
$gradeCredits = 0;
$subjects = [];

while($s = $q->fetch_assoc()){
  $subjects[] = $s;
  /* CATEGORY TOTAL vs COMPLETED */
$categoryTotal = [];
$categoryCompleted = [];

foreach ($subjects as $s) {
  // total credits per category
  $categoryTotal[$s['category']] =
    ($categoryTotal[$s['category']] ?? 0) + $s['credits'];

  // completed credits per category
  if ($s['completed']) {
    $categoryCompleted[$s['category']] =
      ($categoryCompleted[$s['category']] ?? 0) + $s['credits'];
  }
}


  if($s['completed']){
    $totalCredits += $s['credits'];

    $catCredits[$s['category']] =
      ($catCredits[$s['category']] ?? 0) + $s['credits'];

    if($s['grade']){
      $gradeSum += $s['credits'] * $gradeMap[$s['grade']];
      $gradeCredits += $s['credits'];
    }
  }
}

$gpa = $gradeCredits ? round($gradeSum/$gradeCredits,2) : 0;

/* CREDIT REQUIREMENTS (DEPT + REG + ENTRY TYPE) */
$requirements = [];
$requiredTotal = 0;

$checkTable = $conn->query("SHOW TABLES LIKE 'credit_requirements'");
if ($checkTable && $checkTable->num_rows > 0) {

  $rq = $conn->query("
    SELECT category, required_credits
    FROM credit_requirements
    WHERE department='{$student['department']}'
    AND regulation='{$student['regulation']}'
    AND entry_type='$entryType'
  ");

  while ($r = $rq->fetch_assoc()) {
    $requirements[$r['category']] = $r['required_credits'];
    $requiredTotal += $r['required_credits'];
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Credit Tracker</title>
<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>

<div class="nav">
  <button onclick="toggleDark()">🌙 Dark Mode</button>
  <a href="logout.php">Logout</a>
</div>

<h1>Academic Credit Dashboard</h1>

<div class="card" id="report">
  <div class="student-header">
  <div class="avatar">
    <?= strtoupper(substr($student['name'],0,1)) ?>
  </div>

  <div class="student-info">
    <h2><?= strtoupper($student['name']) ?></h2>
    <p>
      Register No: <span><?= $student['reg_no'] ?></span><br>
      <?= $student['department'] ?> • <?= $student['regulation'] ?> • <?= $entryType ?>
    </p>
  </div>
</div>

  <b>Total Earned Credits:</b> <?= $totalCredits ?><br>
  <b>Total Required Credits:</b> <?= $requiredTotal ?><br>
  <b>GPA:</b> <?= $gpa ?><br>

  <h3>Credit Validation</h3>
  <?php foreach($requirements as $cat=>$need):
    $done = $catCredits[$cat] ?? 0;
  ?>
    <p>
      <?= $cat ?> : <?= $done ?> / <?= $need ?>
      <?= $done >= $need ? "✅" : "❌" ?>
    </p>
  <?php endforeach; ?>

  <?php if($totalCredits >= $requiredTotal && $requiredTotal>0): ?>
    <p style="color:green;font-weight:bold;">
      🎓 Degree Credit Requirement Completed
    </p>
  <?php else: ?>
    <p style="color:orange;">
      ⏳ <?= max(0,$requiredTotal-$totalCredits) ?> credits remaining
    </p>
  <?php endif; ?>
</div>

<div class="summary">
  <h3>Credit Chart</h3>
  <div class="chart-box">
  <canvas id="chart"></canvas>
</div>
</div>
<div class="card">
  <h3>📊 Category-wise Credit Progress</h3>

  <?php foreach($categoryTotal as $cat => $total): 
    $done = $categoryCompleted[$cat] ?? 0;
    $percent = round(($done / $total) * 100);
  ?>
    <div class="cat-row">
      <div class="cat-head">
        <b><?= $cat ?></b>
        <span><?= $done ?> / <?= $total ?> credits</span>
      </div>

      <div class="progress-bar">
        <div class="progress-fill" style="width:<?= $percent ?>%"></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>


<div class="search-box">
  <input type="text" id="search"
         placeholder="🔍 Search subject by code or name"
         onkeyup="filterSubjects()">
</div>

<h2>Subjects</h2>
<?php foreach($subjects as $s): ?>
<div class="subject">
  <form method="post" action="update_progress.php">
    <input type="hidden" name="subject_id" value="<?= $s['id'] ?>">

    <label>
      <input type="checkbox" name="completed"
             onchange="this.form.submit()"
             <?= $s['completed'] ? 'checked' : '' ?>>
      <b><?= $s['course_code'] ?></b> – <?= $s['course_title'] ?>
    </label>

    <div>
      <?= $s['category'] ?> |
      Sem <?= $s['semester'] ?> |
      <?= $s['credits'] ?> credits
    </div>

    <?php if ($s['completed']): ?>
      <select name="grade" onchange="this.form.submit()">
        <option value="">Select Grade</option>
        <?php foreach(['O','A+','A','B+','B','C'] as $g):
          $sel = ($s['grade']==$g) ? 'selected' : ''; ?>
          <option <?= $sel ?>><?= $g ?></option>
        <?php endforeach; ?>
      </select>
    <?php endif; ?>
  </form>
</div>
<?php endforeach; ?>

<div class="summary">
  <button onclick="downloadPDF()">Download PDF</button>
</div>

<script>
function toggleDark(){
  document.body.classList.toggle("dark");
}

/* CHART */
<?php if(count($catCredits)>0): ?>
new Chart(document.getElementById("chart"),{
  type:"pie",
  data:{
    labels:<?= json_encode(array_keys($catCredits)) ?>,
    datasets:[{
      data:<?= json_encode(array_values($catCredits)) ?>,
      backgroundColor:[
        "#2563eb","#22c55e","#f97316",
        "#dc2626","#8b5cf6","#14b8a6"
      ]
    }]
  }
});
<?php endif; ?>

function downloadPDF(){
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF();
  pdf.text("Academic Credit Report",20,20);
  pdf.text(document.getElementById("report").innerText,10,40);
  pdf.save("credit-report.pdf");
}
</script>

<script>
function filterSubjects(){
  const q = document.getElementById("search").value.toLowerCase();
  document.querySelectorAll(".subject").forEach(s=>{
    s.style.display = s.innerText.toLowerCase().includes(q)
      ? "block" : "none";
  });
}
</script>


</body>
</html>
