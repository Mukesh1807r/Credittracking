<?php
// ERROR FIX: session_start() must be the very first line for sessions to work
include "config.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_no   = $_POST['reg_no'] ?? '';
    $password = $_POST['password'] ?? '';

    // Protect against SQL injection
    $safe_reg = $conn->real_escape_string($reg_no);
    
    $res = $conn->query("SELECT * FROM students WHERE reg_no='$safe_reg'");

    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();

        // Verify the hashed password
        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            header("Location: index.php");
            exit;
        }
    }

    $error = "Invalid Register Number or Password 😕";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login | Credit Tracker</title>
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
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
        }

        /* Modern Glassmorphism Card */
        .login-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }

        .header h2 { 
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
            padding: 10px; 
            border-radius: 12px; 
            margin: 20px 0; 
            font-size: 14px; 
            font-weight: 600; 
        }

        .form-group { text-align: left; margin-bottom: 20px; }
        
        label { 
            display: block; 
            font-size: 14px; 
            font-weight: 700; 
            margin-bottom: 8px; 
            color: #475569; 
        }

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

        input:focus { 
            border-color: var(--primary); 
            outline: none; 
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); 
        }

        /* Fun Emoji Password Wrapper */
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

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
        }

        .btn-login:hover { 
            background: var(--accent); 
            transform: translateY(-2px); 
            box-shadow: 0 15px 20px -5px rgba(236, 72, 153, 0.4); 
        }

        .footer { margin-top: 25px; font-size: 14px; color: #64748b; font-weight: 600; }
        .footer a { color: var(--primary); text-decoration: none; font-weight: 800; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="header">
        <h2>Welcome Back! 👋</h2>
        <p style="color: #64748b; margin-top: 5px;">Login to track your progress</p>
    </div>

    <?php if($error): ?>
        <div class="error-msg"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>Register Number</label>
            <input name="reg_no" placeholder="Enter your ID" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <div class="pw-wrapper">
                <input type="password" name="password" id="login_pw" placeholder="••••••••" required>
                <span class="eye-toggle" onclick="togglePW()">🙈</span>
            </div>
            <div style="text-align: right; margin-top: 8px;">
                <a href="forgot_password.php" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 700;">Forgot Password?</a>
            </div>
        </div>

        <button type="submit" class="btn-login">Login to Dashboard 🚀</button>
    </form>

    <div class="footer">
        Don't have an account? <a href="register.php">Create one ✨</a>
    </div>
</div>

<script>
function togglePW() {
    const p = document.getElementById("login_pw");
    const btn = document.querySelector(".eye-toggle");
    if (p.type === "password") {
        p.type = "text";
        btn.innerHTML = "👁️"; // Open eye
    } else {
        p.type = "password";
        btn.innerHTML = "🙈"; // Monkey
    }
}
</script>

</body>
</html>