<?php
include "config.php";

if (!isset($_SESSION['id'])) { 
    header("Location: login.php"); 
    exit; 
}

$studentId = $_SESSION['id'];
$student = $conn->query("SELECT name, department, reg_no FROM students WHERE id=$studentId")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | Home</title>
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
            font-family: 'Segoe UI', system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #1e293b;
        }

        .portal-container {
            width: 100%;
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
            box-sizing: border-box;
        }

        /* HEADER GLASS CARD */
        .welcome-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 30px;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .welcome-text h1 { 
            margin: 0; 
            font-size: 2.2rem; 
            background: linear-gradient(to right, #4f46e5, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .welcome-text p { color: #64748b; font-weight: 500; margin-top: 5px; }

        .logout-link {
            padding: 10px 20px;
            background: #fee2e2;
            color: #dc2626;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            transition: 0.3s;
        }
        .logout-link:hover { background: #dc2626; color: white; }

        /* MENU GRID */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .menu-card { 
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(5px);
            padding: 30px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .menu-card:hover { 
            transform: translateY(-10px); 
            background: white;
            box-shadow: 0 20px 30px rgba(0,0,0,0.15);
            border-color: var(--primary);
        }

        .card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }
        .menu-card:hover .card-icon { transform: scale(1.2); }

        .menu-card h3 { margin: 10px 0; font-size: 1.3rem; color: #1e293b; }
        .menu-card p { color: #64748b; font-size: 0.9rem; line-height: 1.5; margin: 0; }

        .admin-footer {
            margin-top: 60px;
            padding: 20px;
            text-align: center;
        }

        .btn-admin {
            background: rgba(30, 41, 59, 0.7);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            backdrop-filter: blur(5px);
            transition: 0.3s;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-admin:hover { background: #1e293b; letter-spacing: 1px; }

    </style>
</head>
<body>

    <div class="portal-container">
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>Hello, <?= htmlspecialchars(explode(' ', $student['name'])[0]) ?>! 👋</h1>
                <p>Welcome to your personal Academic Command Center.</p>
            </div>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>

        <div class="menu-grid">
            <div class="menu-card" onclick="window.location.href='index.php'">
                <div class="card-icon">📊</div>
                <h3>Credit Tracker</h3>
                <p>Manage subjects, assign grades, and watch your GPA grow in real-time.</p>
            </div>

            <div class="menu-card" onclick="window.location.href='completion.php'">
                <div class="card-icon">🎓</div>
                <h3>My Progress</h3>
                <p>Check your completion status and see how many credits you've earned.</p>
            </div>

            <div class="menu-card" onclick="window.location.href='planner.php'">
                <div class="card-icon">✨</div>
                <h3>Smart Planner</h3>
                <p>Get AI-powered suggestions to help you reach your credit goals faster.</p>
            </div>

            <div class="menu-card" onclick="window.location.href='contact.php'">
                <div class="card-icon">📞</div>
                <h3>Support</h3>
                <p>Need a hand? Connect with your department office directly.</p>
            </div>
        </div>

        <div class="admin-footer">
            <a href="admin/login.php" class="btn-admin">🔐 Access Admin Portal</a>
        </div>
    </div>

</body>
</html>