<?php
include "config.php"; 


/* =========================
   CHECK LOGIN
========================= */
if (!isset($_SESSION['id'])) {
  die("User not logged in");
}

/* =========================
   GET DEPARTMENT & REGULATION
   FROM LOGGED-IN USER
========================= */
$sid = $_SESSION['id'];

$student = $conn->query("
  SELECT department, regulation
  FROM students
  WHERE id = $sid
")->fetch_assoc();

if (!$student) {
  die("Student not found");
}

$dept = $student['department'];   // e.g. AIML
$reg  = $student['regulation'];   // e.g. R2024

/* =========================
   LOAD JSON FILE
========================= */
$file = "curriculum/{$dept}_{$reg}.json";

if (!file_exists($file)) {
  die("Curriculum file not found: $file");
}

$data = json_decode(file_get_contents($file), true);

if (!is_array($data)) {
  die("Invalid JSON format");
}

/* =========================
   INSERT INTO DATABASE
========================= */
foreach ($data as $s) {

  $code  = $conn->real_escape_string($s['courseCode']);
  $title = $conn->real_escape_string($s['courseTitle']);
  $cat   = $conn->real_escape_string($s['category']);
  $cred  = (int)$s['credits'];
  $sem   = isset($s['semester']) ? (int)$s['semester'] : 0;

  // Prevent duplicate inserts
  $exists = $conn->query("
    SELECT 1 FROM curriculum
    WHERE department='$dept'
    AND regulation='$reg'
    AND course_code='$code'
  ");

  if ($exists->num_rows == 0) {
    $conn->query("
      INSERT INTO curriculum
      (department, regulation, course_code, course_title, category, credits, semester)
      VALUES
      ('$dept','$reg','$code','$title','$cat',$cred,$sem)
    ");
  }
}

echo "✅ Curriculum Imported Successfully for $dept $reg";
