<?php
include "config.php";

if($_SERVER['REQUEST_METHOD']==='POST'){
  $reg_no = $_POST['reg_no'] ?? '';

  $res = $conn->query("SELECT id FROM students WHERE reg_no='$reg_no'");
  if($res->num_rows>0){

    $otp = rand(100000,999999);
    $expiry = date("Y-m-d H:i:s", time()+300); // 5 min

    $conn->query("
      UPDATE students
      SET reset_otp='$otp', otp_expiry='$expiry'
      WHERE reg_no='$reg_no'
    ");

    // OTP shown on screen (for demo)
    $success = "Your OTP is: $otp";
  }else{
    $error = "Register number not found";
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-box">
  <h2>🔐 Forgot Password</h2>

  <?php if(!empty($error)): ?>
    <p style="color:red"><?= $error ?></p>
  <?php endif; ?>

  <?php if(!empty($success)): ?>
    <p style="color:green;font-weight:bold"><?= $success ?></p>
    <a href="reset_password.php">Continue</a>
  <?php endif; ?>

  <form method="post">
    <input name="reg_no" placeholder="Register Number" required>
    <button type="submit">Generate OTP</button>
  </form>

  <p style="text-align:center;margin-top:12px">
    <a href="login.php">Back to Login</a>
  </p>
</div>

</body>
</html>
