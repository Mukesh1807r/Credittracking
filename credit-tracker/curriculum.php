<?php
include "config.php";
$id = $_SESSION['id'];

$stu = $conn->query("SELECT * FROM students WHERE id=$id")->fetch_assoc();

$subs = $conn->query("
SELECT c.*, p.completed
FROM curriculum c
LEFT JOIN progress p
ON c.id = p.subject_id AND p.student_id=$id
WHERE department='{$stu['department']}'
AND regulation='{$stu['regulation']}'
");
?>

<form method="post" action="update_progress.php">
<?php while($s=$subs->fetch_assoc()){ ?>
  <div>
    <input type="checkbox" name="sub[]"
      value="<?= $s['id'] ?>"
      <?= $s['completed']?'checked':'' ?>>
    <?= $s['course_code'] ?> -
    <?= $s['course_title'] ?>
    (<?= $s['category'] ?> | <?= $s['credits'] ?>)
  </div>
<?php } ?>
<button>Save Progress</button>
</form>
