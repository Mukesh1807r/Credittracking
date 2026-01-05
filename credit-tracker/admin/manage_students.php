<?php
include "../config.php";
if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM students";
if($search) {
    $query .= " WHERE name LIKE '%$search%' OR reg_no LIKE '%$search%'";
}
$students = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Students | Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="bg-light">
    <div class="container" style="margin-top: 40px;">
        <button onclick="window.location.href='dashboard.php'" class="btn-nav">⬅ Back</button>
        <h1>👥 Student Management</h1>
        
        <div class="admin-card">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by name or Reg No..." value="<?= $search ?>" style="padding: 10px; width: 300px; border-radius: 8px;">
                <button type="submit" class="pill">Search</button>
            </form>
        </div>

        <table style="width: 100%; margin-top: 20px; background: white; border-radius: 12px; overflow: hidden;">
            <thead style="background: #f8fafc;">
                <tr>
                    <th>Reg No</th>
                    <th>Name</th>
                    <th>Dept</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $students->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['reg_no'] ?></td>
                    <td><?= strtoupper($row['name']) ?></td>
                    <td><?= $row['department'] ?></td>
                    <td><button class="pill" onclick="window.location.href='view_student.php?reg_no=<?= $row['reg_no'] ?>'">View Progress</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>