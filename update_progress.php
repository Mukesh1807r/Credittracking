<?php
include "config.php";
if(!isset($_SESSION['id'])) exit;

$sid = $_SESSION['id'];
$sub = $_POST['subject_id'];
$done = isset($_POST['completed']) ? 1 : 0;
$grade = $_POST['grade'] ?? null;

$check = $conn->query("
  SELECT * FROM progress
  WHERE student_id=$sid AND subject_id=$sub
");

if($check->num_rows){
  $conn->query("
    UPDATE progress
    SET completed=$done, grade='$grade'
    WHERE student_id=$sid AND subject_id=$sub
  ");
}else{
  $conn->query("
    INSERT INTO progress VALUES
    ($sid,$sub,$done,'$grade')
  ");
}

header("Location:index.php");
