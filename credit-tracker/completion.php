<?php
include "config.php";

// Standard Session Management for Live Servers
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['id'])) { header("Location: login.php"); exit; }

$studentId = (int)$_SESSION['id'];
$student = $conn->query("SELECT * FROM students WHERE id=$studentId")->fetch_assoc();

/* =========================
    LOAD CURRICULUM FROM JSON
========================= */
$dept = $student['department'];
$reg = $student['regulation'];
// Using __DIR__ ensures the server finds the folder regardless of hosting paths
$jsonFile = __DIR__ . "/curriculum/{$dept}_{$reg}.json";

if (!file_exists($jsonFile)) {
    die("Curriculum file missing for: " . htmlspecialchars($dept) . " " . htmlspecialchars($reg));
}

$subjects = json_decode(file_get_contents($jsonFile), true);

/* =========================
    FETCH COMPLETED PROGRESS
========================= */
// We fetch only completed records for this student
$progressRes = $conn->query("SELECT * FROM progress WHERE student_id = $studentId AND completed = 1");
$doneData = [];
while($r = $progressRes->fetch_assoc()) { 
    $doneData[$r['subject_id']] = $r['grade']; 
}

$earnedCredits = 0;

// Fetch total required credits for the progress bar calculation
$reqRes = $conn->query("SELECT SUM(required_credits) as total FROM credit_requirements WHERE department='$dept' AND regulation='$reg' AND entry_type='{$student['entry_type']}'");
$reqRow = $reqRes->fetch_assoc();
$requiredTotal = (int)($reqRow['total'] ?? 160); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Record | Credit Tracker</title>
    <style>
        :root {
            --primary: #6366f1;
            --accent: #10b981;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: var(--bg-gradient);
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #1e293b;
        }

        .container { max-width: 950px; margin: 40px auto; padding: 0 20px; }

        /* Modern Glass Card */
        .glass-panel {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(15px);
            padding: 45px;
            border-radius: 35px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .header-title { text-align: center; margin-bottom: 35px; }
        .header-title h2 { 
            font-size: 2.4rem; 
            margin: 0; 
            background: linear-gradient(to right, #166534, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800; 
        }

        /* Progress Card */
        .summary-box {
            background: #f0fdf4;
            padding: 25px;
            border-radius: 24px;
            margin-bottom: 35px;
            border: 1px solid #bbf7d0;
            text-align: center;
        }
        .progress-track { background: #dcfce7; height: 14px; border-radius: 20px; margin: 15px 0; overflow: hidden; }
        .progress-fill { background: var(--accent); height: 100%; transition: width 1.2s cubic-bezier(0.22, 1, 0.36, 1); }

        /* Elegant Table Design */
        .academic-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; margin-top: 10px; }
        .academic-table th { 
            text-align: left; 
            padding: 15px; 
            color: #64748b; 
            font-size: 0.8rem; 
            text-transform: uppercase; 
            letter-spacing: 1.5px;
        }
        .academic-table td { padding: 18px 15px; background: white; transition: 0.3s; }
        .academic-table td:first-child { border-radius: 15px 0 0 15px; }
        .academic-table td:last-child { border-radius: 0 15px 15px 0; text-align: center; }

        .academic-table tr:hover td { background: #f8fafc; transform: scale(1.005); }

        .grade-pill {
            background: #dcfce7;
            color: #166534;
            padding: 6px 14px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgba(22, 101, 52, 0.1);
        }
        
        .btn-portal {
            display: inline-block;
            margin-bottom: 25px;
            color: white;
            text-decoration: none;
            font-weight: 700;
            background: rgba(255,255,255,0.15);
            padding: 10px 20px;
            border-radius: 12px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: 0.3s;
        }
        .btn-portal:hover { background: rgba(255,255,255,0.25); }

        @media print {
            .btn-portal, .btn-print, .progress-box { display: none; }
            body { background: white; }
            .glass-panel { box-shadow: none; border: none; padding: 0; }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="home.php" class="btn-portal">← Back to Portal</a>

    <div class="glass-panel">
        <div class="header-title">
            <h2>✅ Academic Completion</h2>
            <p style="color: #64748b; font-weight: 500;">Verified record of earned credits and assigned grades.</p>
        </div>

        <?php 
        // We generate the table content first to get the total earned credits for the bar above it
        $rows = "";
        foreach($subjects as $s) {
            $code = $s['courseCode'];
            // Same logic as your working version
            $db_sub = $conn->query("SELECT id FROM curriculum WHERE course_code='$code'")->fetch_assoc();
            $db_id = $db_sub['id'] ?? 0;

            if(isset($doneData[$db_id])) {
                $earnedCredits += $s['credits'];
                $grade = $doneData[$db_id] ?: 'P'; // 'P' for pass if no grade selected
                $rows .= "
                <tr>
                    <td><b style='color: var(--primary);'>$code</b></td>
                    <td style='font-weight: 500;'>{$s['courseTitle']}</td>
                    <td style='text-align:center; font-weight: 700;'>{$s['credits']}</td>
                    <td><span class='grade-pill'>$grade</span></td>
                </tr>";
            }
        }
        $percent = ($requiredTotal > 0) ? round(($earnedCredits / $requiredTotal) * 100) : 0;
        ?>

        

        <div class="summary-box">
            <div style="display:flex; justify-content: space-between; font-weight: 800; font-size: 1.1rem;">
                <span style="color: #166534;">Degree Progress</span>
                <span style="color: var(--primary);"><?= $earnedCredits ?> / <?= $requiredTotal ?> Credits</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width: <?= min($percent, 100) ?>%"></div>
            </div>
            <p style="margin:0; font-weight: 700; color: #166534;">
                🎉 You have successfully completed <?= $percent ?>% of your curriculum.
            </p>
        </div>

        <table class="academic-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Subject Title</th>
                    <th style="text-align:center;">Credits</th>
                    <th style="text-align:center;">Grade</th>
                </tr>
            </thead>
            <tbody>
                <?= $rows ?: "<tr><td colspan='4' style='text-align:center; padding:50px; color:#94a3b8;'>No subjects have been marked as completed yet.</td></tr>" ?>
            </tbody>
        </table>

        <div style="text-align:center; margin-top: 40px;">
            <button onclick="window.print()" class="btn-print" style="background: var(--primary); color: white; border: none; padding: 15px 40px; border-radius: 15px; font-weight: 800; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4); transition: 0.3s;">
                🖨️ Download Academic Statement
            </button>
        </div>
    </div>
</div>

</body>
</html>