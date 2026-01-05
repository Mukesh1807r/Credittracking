<?php
/**
 * Automatically syncs a JSON curriculum file to the SQL database.
 * Prevents duplicates and ensures data integrity.
 */
function syncCurriculumToDB($conn, $dept, $reg) {
    // Determine path based on whether the function is called from root or /admin/
    $basePath = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) ? "../" : "";
    $file = $basePath . "curriculum/{$dept}_{$reg}.json";

    if (!file_exists($file)) return false;

    $jsonContent = file_get_contents($file);
    $data = json_decode($jsonContent, true);

    if (!is_array($data)) return false;

    foreach ($data as $s) {
        $code  = $conn->real_escape_string($s['courseCode']);
        $title = $conn->real_escape_string($s['courseTitle']);
        $cat   = $conn->real_escape_string($s['category']);
        $cred  = (int)$s['credits'];
        $sem   = isset($s['semester']) ? (int)$s['semester'] : 0;

        // Automated UPSERT logic using course_code, department, and regulation as identifiers
        // Note: This assumes you have a unique index on (department, regulation, course_code)
        $sql = "INSERT INTO curriculum 
                (department, regulation, course_code, course_title, category, credits, semester)
                VALUES ('$dept', '$reg', '$code', '$title', '$cat', $cred, $sem)
                ON DUPLICATE KEY UPDATE 
                course_title='$title', category='$cat', credits=$cred, semester=$sem";
        
        $conn->query($sql);
    }
    return true;
}
?>