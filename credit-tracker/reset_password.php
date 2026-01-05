<?php
session_start();
include "config.php";

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_no = $conn->real_escape_string($_POST['reg_no']);
    
    // Verify if the student exists
    $check = $conn->query("SELECT id FROM students WHERE reg_no = '$reg_no'");
    
    if ($check && $check->num_rows > 0) {
        // Generate a 6-digit random OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);  
        // Set expiry for 10 minutes from now
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        
        // Store OTP in the database
        $sql = "UPDATE students SET reset_otp = '$otp', otp_expiry = '$expiry' WHERE reg_no = '$reg_no'";
        
        if ($conn->query($sql)) {
            // In a real system, you'd email this. For now, we display it securely for testing.
            $message = "OTP Generated! For demo purposes, your code is: <b style='font-size:1.2rem;'>$otp</b> 🔑";
        }
    } else {
        $error = "We couldn't find that Register Number. 🔍";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Credit Tracker</title>
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

        .forgot-card {
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

        .header h1 { 
            font-size: 28px; 
            margin: 0; 
            background: linear-gradient(to right, #4f46e5, #ec4899); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            font-weight: 800; 
        }

        .status-msg { 
            background: #dcfce7; 
            color: #166534; 
            padding: 15px; 
            border-radius: 12px; 
            margin: 20px 0; 
            font-size: 14px; 
            font-weight: 600; 
            border: 1px solid #bbf7d0;
        }

        .error-msg { 
            background: #fee2e2; 
            color: #dc2626; 
            padding: 15px; 
            border-radius: 12px; 
            margin: 20px 0; 
            font-size: 14px; 
            font-weight: 600; 
            border: 1px solid #fecaca;
        }

        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 8px; color: #475569; }

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

        .btn-request {
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

        .btn-request:hover { 
            background: var(--accent); 
            transform: translateY(-2px); 
            box-shadow: 0 15px 20px -5px rgba(236, 72, 153, 0.4); 
        }

        .footer { margin-top: 25px; font-size: 14px; color: #64748b; font-weight: 600; }
        .footer a { color: var(--primary); text-decoration: none; font-weight: 800; }
    </style>
</head>
<body>

<div class="forgot-card">
    <div class="header">
        <h1>Lost Access? 🔐</h1>
        <p style="color: #64748b; margin-top: 5px;">Enter your ID to get a reset code.</p>
    </div>

    <?php if($message): ?>
        <div class="status-msg"><?= $message ?></div>
        <button onclick="window.location.href='reset_password.php'" class="btn-request" style="background:var(--accent);">Go to Reset Page ✨</button>
    <?php elseif($error): ?>
        <div class="error-msg"><?= $error ?></div>
    <?php endif; ?>

    <?php if(!$message): ?>
    <form method="post">
        <div class="form-group">
            <label>Register Number</label>
            <input name="reg_no" placeholder="Enter your registered ID" required>
        </div>
        <button type="submit" class="btn-request">Generate OTP 🚀</button>
    </form>
    <?php endif; ?>

    <div class="footer">
        Back to <a href="login.php">Login</a>
    </div>
</div>

</body>
</html>