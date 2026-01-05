<?php
// ERROR FIX: Ensure session is started if config.php doesn't do it
include "config.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reg_no = $_POST['reg_no'] ?? '';
    $otp    = $_POST['otp'] ?? '';
    $pass1  = $_POST['password'] ?? '';
    $pass2  = $_POST['confirm_password'] ?? '';

    // Sanitize input to prevent SQL Injection
    $safe_reg = $conn->real_escape_string($reg_no);
    $safe_otp = $conn->real_escape_string($otp);

    if ($pass1 !== $pass2) {
        $error = "Passwords do not match! 🧐";
    } else {
        // Verify OTP and Expiry
        $check = $conn->query("
            SELECT * FROM students 
            WHERE reg_no='$safe_reg' 
            AND reset_otp='$safe_otp' 
            AND otp_expiry >= NOW()
        ");

        if ($check && $check->num_rows > 0) {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);

            // Update password and clear OTP fields
            $conn->query("
                UPDATE students 
                SET password='$hash', 
                    reset_otp=NULL, 
                    otp_expiry=NULL 
                WHERE reg_no='$safe_reg'
            ");

            header("Location: login.php?status=reset_success");
            exit;
        } else {
            $error = "Invalid or expired OTP. 😲";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Credit Tracker</title>
    <style>
        :root {
            --primary: #6366f1;
            --accent: #ec4899;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
        }

        /* Modern Glassmorphism Container */
        .reset-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }

        .header h1 { 
            font-size: 28px; 
            margin: 0; 
            background: linear-gradient(to right, #4f46e5, #ec4899); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            font-weight: 800; 
        }

        .error-msg { 
            background: #fee2e2; 
            color: #dc2626; 
            padding: 12px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            font-weight: 600; 
            font-size: 14px;
            border: 1px solid #fecaca; 
        }

        .form-group { text-align: left; margin-bottom: 15px; }
        label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 6px; margin-left: 4px; color: #475569; }

        input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: white;
            font-size: 15px;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); }

        /* Emoji Toggle Wrapper */
        .pw-wrapper { position: relative; }
        .eye-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            user-select: none;
        }

        .btn-reset {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 15px;
            transition: 0.3s;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
        }

        .btn-reset:hover { 
            background: var(--accent); 
            transform: translateY(-2px); 
            box-shadow: 0 15px 20px -5px rgba(236, 72, 153, 0.4); 
        }

        .footer { margin-top: 25px; font-size: 14px; color: #64748b; font-weight: 600; }
        .footer a { color: var(--primary); text-decoration: none; }
    </style>
</head>
<body>

<div class="reset-card">
    <div class="header">
        <h1>New Start! 🔁</h1>
        <p style="color: #64748b; margin-top: 5px;">Secure your account with a new password.</p>
    </div>

    <?php if($error): ?>
        <div class="error-msg"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>Register Number</label>
            <input name="reg_no" placeholder="Enter ID" required>
        </div>

        <div class="form-group">
            <label>OTP Code</label>
            <input name="otp" placeholder="Enter 6-digit OTP" required>
        </div>

        <div class="form-group">
            <label>New Password</label>
            <div class="pw-wrapper">
                <input type="password" name="password" id="p1" placeholder="••••••••" required>
                <span class="eye-toggle" onclick="toggle('p1', this)">🙈</span>
            </div>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <div class="pw-wrapper">
                <input type="password" name="confirm_password" id="p2" placeholder="••••••••" required>
                <span class="eye-toggle" onclick="toggle('p2', this)">🙈</span>
            </div>
        </div>

        <button type="submit" class="btn-reset">Update Password ✨</button>
    </form>

    <div class="footer">
        Remembered it? <a href="login.php">Back to Login</a>
    </div>
</div>

<script>
function toggle(id, btn) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        btn.innerHTML = "👁️"; // Open eye when showing
    } else {
        input.type = "password";
        btn.innerHTML = "🙈"; // Monkey when hidden
    }
}
</script>

</body>
</html>