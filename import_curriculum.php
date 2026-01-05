<?php
include "config.php";

$data = json_decode(
  file_get_contents("curriculum/AIML_R2024.json"),
  true
);

foreach($data as $s){
  $conn->query("
    INSERT INTO curriculum
    (department, regulation, course_code, course_title, category, credits)
    VALUES
    ('AIML','R2024',
     '{$s['courseCode']}',
     '{$s['courseTitle']}',
     '{$s['category']}',
     {$s['credits']}
    )
  ");
}

echo "Curriculum Imported Successfully";
