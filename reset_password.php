<?php
include "config.php";

if($_SERVER['REQUEST_METHOD']==='POST'){

  $reg_no = $_POST['reg_no'] ?? '';
  $otp    = $_POST['otp'] ?? '';
  $pass1  = $_POST['password'] ?? '';
  $pass2  = $_POST['confirm_password'] ?? '';

  if($pass1 !== $pass2){
    $error = "Passwords do not match";
  }else{

    $check = $conn->query("
      SELECT * FROM students
      WHERE reg_no='$reg_no'
      AND reset_otp='$otp'
      AND otp_expiry >= NOW()
    ");

    if($check->num_rows>0){
      $hash = password_hash($pass1, PASSWORD_DEFAULT);

      $conn->query("
        UPDATE students
        SET password='$hash',
            reset_otp=NULL,
            otp_expiry=NULL
        WHERE reg_no='$reg_no'
      ");

      header("Location: login.php");
      exit;
    }else{
      $error = "Invalid or expired OTP";
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-box">
  <h2>🔁 Reset Password</h2>

  <?php if(!empty($error)): ?>
    <p style="color:red"><?= $error ?></p>
  <?php endif; ?>

  <form method="post">
    <input name="reg_no" placeholder="Register Number" required>
    <input name="otp" placeholder="Enter OTP" required>

    <input type="password" name="password"
           placeholder="New Password" required>

    <input type="password" name="confirm_password"
           placeholder="Confirm Password" required>

    <button type="submit">Reset Password</button>
  </form>
</div>

</body>
</html>
