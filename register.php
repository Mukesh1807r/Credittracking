<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $name       = $_POST['name'] ?? '';
  $reg_no     = $_POST['reg_no'] ?? '';
  $department = $_POST['department'] ?? '';
  $regulation = $_POST['regulation'] ?? '';
  $entry_type = $_POST['entry_type'] ?? 'REGULAR';
  $password   = $_POST['password'] ?? '';

  if ($name && $reg_no && $password) {

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $check = $conn->query("SELECT id FROM students WHERE reg_no='$reg_no'");
    if ($check->num_rows == 0) {

      $col = $conn->query("SHOW COLUMNS FROM students LIKE 'entry_type'");

if ($col->num_rows > 0) {
  // entry_type exists
  $conn->query("
    INSERT INTO students
    (name, reg_no, department, regulation, entry_type, password)
    VALUES
    ('$name','$reg_no','$department','$regulation','$entry_type','$hash')
  ");
} else {
  // entry_type does NOT exist
  $conn->query("
    INSERT INTO students
    (name, reg_no, department, regulation, password)
    VALUES
    ('$name','$reg_no','$department','$regulation','$hash')
  ");
}


      header("Location: login.php");
      exit;
    }

    $error = "Register number already exists";
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Register</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-box">
  <h2>📝 Create Account</h2>

  <?php if(!empty($error)): ?>
    <p style="color:red"><?= $error ?></p>
  <?php endif; ?>

  <form method="post">
    <input name="name" placeholder="Full Name" required>
    <input name="reg_no" placeholder="Register Number" required>

    <select name="department" required>
      <option value="">Department</option>
      <option>AIML</option>
      <option>CSE</option>
    </select>

    <select name="regulation" required>
      <option value="">Regulation</option>
      <option>R2024</option>
      <option>R2019</option>
    </select>

    <select name="entry_type">
      <option value="REGULAR">Regular</option>
      <option value="LATERAL">Lateral Entry</option>
    </select>

    <input type="password" name="password" placeholder="Password" required>

    <button type="submit">Create Account</button>
  </form>

  <p style="text-align:center;margin-top:14px">
    Already registered? <a href="login.php">Login</a>
  </p>
</div>

</body>
</html>
