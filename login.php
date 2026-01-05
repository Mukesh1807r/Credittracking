<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $reg_no   = $_POST['reg_no'] ?? '';
  $password = $_POST['password'] ?? '';

  $res = $conn->query("
    SELECT * FROM students WHERE reg_no='$reg_no'
  ");

  if ($res->num_rows > 0) {
    $user = $res->fetch_assoc();

    if (password_verify($password, $user['password'])) {
      $_SESSION['id'] = $user['id'];
      header("Location: index.php");
      exit;
    }
  }

  $error = "Invalid Register Number or Password";
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-box">
  <h2>🎓 Student Login</h2>

  <?php if(!empty($error)): ?>
    <p style="color:red"><?= $error ?></p>
  <?php endif; ?>

  <form method="post">
    <input name="reg_no" placeholder="Register Number" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
  <p style="text-align:center;margin-top:10px">
  <a href="forgot_password.php">Forgot Password?</a>
</p>


  <p style="text-align:center;margin-top:14px">
    New here? <a href="register.php">Create Account</a>
  </p>
</div>

</body>
</html>
