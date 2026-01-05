<?php
include "../config.php";
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$dir = "../curriculum/";
$files = array_diff(scandir($dir), array('.', '..'));

$selectedFile = $_GET['file'] ?? '';
$curriculumData = [];

if ($selectedFile && file_exists($dir . $selectedFile)) {
    $curriculumData = json_decode(file_get_contents($dir . $selectedFile), true);
}

// Save Logic
if (isset($_POST['save_curriculum'])) {
    $updatedData = $_POST['subjects']; // Array from form
    file_put_contents($dir . $selectedFile, json_encode($updatedData, JSON_PRETTY_PRINT));
    echo "<script>alert('Curriculum Updated Successfully!');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Curriculum Editor | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">
    <div class="nav">
        <button onclick="window.location.href='dashboard.php'" class="btn-nav">⬅ Back to Admin</button>
    </div>

    <div class="container">
        <h1>📚 Curriculum Manager</h1>
        
        <div class="admin-card" style="margin-bottom: 20px;">
            <form method="GET">
                <label>Select Curriculum File:</label>
                <select name="file" onchange="this.form.submit()" style="padding: 10px; border-radius: 8px;">
                    <option value="">-- Select File --</option>
                    <?php foreach($files as $f): ?>
                        <option value="<?= $f ?>" <?= $selectedFile == $f ? 'selected' : '' ?>><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($selectedFile): ?>
        <form method="POST">
            <div class="admin-card">
                <h3>Editing: <?= $selectedFile ?></h3>
                <table id="curriculum-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Credits</th>
                            <th>Semester</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($curriculumData as $index => $sub): ?>
                        <tr>
                            <td><input name="subjects[<?= $index ?>][courseCode]" value="<?= $sub['courseCode'] ?>"></td>
                            <td><input name="subjects[<?= $index ?>][courseTitle]" value="<?= $sub['courseTitle'] ?>" style="width: 250px;"></td>
                            <td><input name="subjects[<?= $index ?>][category]" value="<?= $sub['category'] ?>" style="width: 50px;"></td>
                            <td><input type="number" name="subjects[<?= $index ?>][credits]" value="<?= $sub['credits'] ?>" style="width: 50px;"></td>
                            <td><input type="number" name="subjects[<?= $index ?>][semester]" value="<?= $sub['semester'] ?? '' ?>" style="width: 50px;"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="save_curriculum" class="btn-special" style="margin-top: 20px;">💾 Save All Changes</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>