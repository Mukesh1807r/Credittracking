<?php
include "config.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = $_POST['name'] ?? '';
    $reg_no     = $_POST['reg_no'] ?? '';
    $department = $_POST['department'] ?? '';
    $regulation = $_POST['regulation'] ?? '';
    $entry_type = $_POST['entry_type'] ?? 'REGULAR';
    $password   = $_POST['password'] ?? '';
    $confirm_p  = $_POST['confirm_password'] ?? '';

    if ($name && $reg_no && $password) {
        if ($password !== $confirm_p) {
            $error = "Oops! Passwords don't match. 🧐";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $check = $conn->query("SELECT id FROM students WHERE reg_no='$reg_no'");
            if ($check->num_rows == 0) {
                $col = $conn->query("SHOW COLUMNS FROM students LIKE 'entry_type'");
                if ($col->num_rows > 0) {
                    $conn->query("INSERT INTO students (name, reg_no, department, regulation, entry_type, password) 
                                  VALUES ('$name','$reg_no','$department','$regulation','$entry_type','$hash')");
                } else {
                    $conn->query("INSERT INTO students (name, reg_no, department, regulation, password) 
                                  VALUES ('$name','$reg_no','$department','$regulation','$hash')");
                }
                header("Location: login.php?status=success");
                exit;
            } else {
                $error = "That ID is already in our system! 😲";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Squad | Credit Tracker</title>
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

        .auth-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 480px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 32px; margin: 0; background: linear-gradient(to right, #4f46e5, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; }
        .header p { color: #64748b; font-weight: 500; margin-top: 5px; }

        .error-msg { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; text-align: center; border: 1px solid #fecaca; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }

        label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 6px; margin-left: 4px; color: #475569; }

        input, select {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: white;
            font-size: 15px;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus, select:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); }

        .pw-wrapper { position: relative; width: 100%; }
        .eye-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            user-select: none;
            background: none;
            border: none;
            padding: 5px;
        }

        .btn-join {
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

        .btn-join:hover { background: var(--accent); transform: translateY(-2px); box-shadow: 0 15px 20px -5px rgba(236, 72, 153, 0.4); }

        .footer { text-align: center; margin-top: 25px; font-size: 14px; color: #64748b; font-weight: 600; }
        .footer a { color: var(--primary); text-decoration: none; border-bottom: 2px solid transparent; transition: 0.3s; }
        .footer a:hover { border-bottom-color: var(--primary); }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="header">
        <h1>Welcome! 🚀</h1>
        <p>Let's get your degree tracker ready.</p>
    </div>

    <?php if($error): ?>
        <div class="error-msg"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-grid">
            <div class="full-width">
                <label>Full Name</label>
                <input name="name" placeholder="Alex Morgan" required>
            </div>

            <div class="full-width">
                <label>Register Number</label>
                <input name="reg_no" placeholder="21222xxxxxxx" required>
            </div>

            <div>
                <label>Department</label>
                <select name="department" required>
                    <option value="">Select...</option>
                    <option>AIML</option><option>CSE</option><option>IT</option>
                    <option>AIDS</option><option>ECE</option><option>I0T</option>
                    <option>MECH</option><option>CIVIL</option><option>EEE</option>
                    <option>BME</option><option>CHEMICAL</option>
                </select>
            </div>

            <div>
                <label>Regulation</label>
                <select name="regulation" required>
                    <option value="">Select...</option>
                    <option>R2024</option>
                    <option>R2019</option>
                </select>
            </div>

            <div class="full-width">
                <label>Entry Type</label>
                <select name="entry_type">
                    <option value="REGULAR">Regular Student</option>
                    <option value="LATERAL">Lateral Entry</option>
                </select>
            </div>

            <div class="full-width">
                <label>Password</label>
                <div class="pw-wrapper">
                    <input type="password" name="password" id="p1" required>
                    <span class="eye-toggle" onclick="toggle('p1', this)">🙈</span>
                </div>
            </div>

            <div class="full-width">
                <label>Confirm Password</label>
                <div class="pw-wrapper">
                    <input type="password" name="confirm_password" id="p2" required>
                    <span class="eye-toggle" onclick="toggle('p2', this)">🙈</span>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-join">Start My Journey ✨</button>
    </form>

    <div class="footer">
        Already have an account? <a href="login.php">Log In here</a>
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