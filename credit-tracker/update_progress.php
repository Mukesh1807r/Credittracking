<?php
include "config.php";
session_start();

if (!isset($_SESSION['id'])) {
    exit("Unauthorized");
}

$sid = (int)$_SESSION['id'];
$sub = (int)($_POST['subject_id'] ?? 0);
$type = $_POST['type'] ?? 'normal'; // normal | common
$done = isset($_POST['completed']) ? 1 : 0;
$grade = !empty($_POST['grade'])
    ? "'" . $conn->real_escape_string($_POST['grade']) . "'"
    : "NULL";

if ($sub <= 0) {
    header("Location: index.php");
    exit;
}

/* =========================
   SELECT TABLE BASED ON TYPE
========================= */
$table = ($type === 'common') ? 'common_progress' : 'progress';

/* =========================
   UPSERT (INSERT or UPDATE)
========================= */
$sql = "
INSERT INTO $table (student_id, subject_id, completed, grade)
VALUES ($sid, $sub, $done, $grade)
ON DUPLICATE KEY UPDATE
  completed = $done,
  grade = $grade
";

if (!$conn->query($sql)) {
    die("DB Error: " . $conn->error);
}

/* =========================
   SMART REDIRECT
========================= */
$redirect = "index.php";

if ($type === 'common') {
    $redirect = "common_subjects.php";
} elseif (
    isset($_SERVER['HTTP_REFERER']) &&
    strpos($_SERVER['HTTP_REFERER'], 'inprogress.php') !== false
) {
    $redirect = "inprogress.php";
}

header("Location: $redirect?status=updated");
exit;
