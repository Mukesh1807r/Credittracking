<?php
include "../config.php";


// Security Check
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$file = $_GET['file'] ?? '';
$filePath = "../curriculum/" . $file;

// Validate file exists and is a JSON file
if (empty($file) || !file_exists($filePath)) {
    die("Error: Curriculum file not found.");
}

// Load existing data
$jsonContent = file_get_contents($filePath);
$subjects = json_decode($jsonContent, true);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $updatedSubjects = $_POST['subjects'] ?? [];
    
    // Convert numerical strings back to integers where necessary
    foreach ($updatedSubjects as &$s) {
        $s['credits'] = (int)$s['credits'];
        if(isset($s['semester']) && $s['semester'] !== '') {
            $s['semester'] = (int)$s['semester'];
        }
    }

    if (file_put_contents($filePath, json_encode(array_values($updatedSubjects), JSON_PRETTY_PRINT))) {
        $success = "Curriculum updated successfully!";
        // Refresh data
        $subjects = $updatedSubjects;
    } else {
        $error = "Failed to save changes. Check file permissions.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Curriculum | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .edit-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .subject-row { display: grid; grid-template-columns: 1fr 2fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 10px; align-items: center; }
        .header-row { font-weight: bold; background: #f1f5f9; padding: 10px; border-radius: 8px; margin-bottom: 15px; }
        input { padding: 8px; border: 1px solid #ddd; border-radius: 5px; width: 100%; }
        .btn-add { 
            background: #10b981; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: bold;
            margin-bottom: 20px;
        }
        .btn-add:hover { background: #059669; }
    </style>
</head>
<body class="bg-light">
    <div class="nav">
        <button onclick="window.location.href='curriculum_editor.php'" class="btn-nav">⬅ Back to Editor</button>
    </div>

    <div class="edit-container">
        <div class="card">
            <h1>Editing: <?= htmlspecialchars($file) ?></h1>
            
            <?php if(isset($success)): ?>
                <div style="color: green; margin-bottom: 20px; font-weight: bold;">✅ <?= $success ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div style="color: red; margin-bottom: 20px; font-weight: bold;">❌ <?= $error ?></div>
            <?php endif; ?>

            <button type="button" class="btn-add" onclick="addSubjectRow()">➕ Add New Subject Row</button>

            <form method="POST">
                <div class="subject-row header-row">
                    <div>Code</div>
                    <div>Title</div>
                    <div>Category</div>
                    <div>Credits</div>
                    <div>Semester</div>
                </div>

                <div id="subject-inputs">
                    <?php foreach($subjects as $index => $s): ?>
                        <div class="subject-row">
                            <input type="text" name="subjects[<?= $index ?>][courseCode]" value="<?= htmlspecialchars($s['courseCode']) ?>" required>
                            <input type="text" name="subjects[<?= $index ?>][courseTitle]" value="<?= htmlspecialchars($s['courseTitle']) ?>" required>
                            <input type="text" name="subjects[<?= $index ?>][category]" value="<?= htmlspecialchars($s['category']) ?>" required>
                            <input type="number" name="subjects[<?= $index ?>][credits]" value="<?= $s['credits'] ?>" required>
                            <input type="number" name="subjects[<?= $index ?>][semester]" value="<?= $s['semester'] ?? '' ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" name="save" class="btn-special">💾 Save Curriculum</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addSubjectRow() {
            const container = document.getElementById('subject-inputs');
            const index = container.getElementsByClassName('subject-row').length;
            
            const newRow = document.createElement('div');
            newRow.className = 'subject-row';
            newRow.style.marginTop = "10px";
            
            newRow.innerHTML = `
                <input type="text" name="subjects[${index}][courseCode]" placeholder="Code" required>
                <input type="text" name="subjects[${index}][courseTitle]" placeholder="Title" required>
                <input type="text" name="subjects[${index}][category]" placeholder="Cat" required>
                <input type="number" name="subjects[${index}][credits]" placeholder="Cr" required>
                <input type="number" name="subjects[${index}][semester]" placeholder="Sem">
            `;
            
            container.appendChild(newRow);
            newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>
</body>
</html>