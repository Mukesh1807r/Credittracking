<?php
include "config.php";
if (!isset($_SESSION['id'])) { header("Location: login.php"); exit; }

$studentId = (int)$_SESSION['id'];
$student = $conn->query("SELECT * FROM students WHERE id=$studentId")->fetch_assoc();
$dept = $student['department']; 
$reg = $student['regulation'];
$entryType = $student['entry_type'] ?? 'REGULAR';

// 1. Fetch Curriculum JSON
$subjects = json_decode(file_get_contents("curriculum/{$dept}_{$reg}.json"), true);

// 2. Map Database IDs to Course Codes
$dbMap = [];
$dbRows = $conn->query("SELECT id, course_code FROM curriculum");
while($row = $dbRows->fetch_assoc()) { $dbMap[$row['course_code']] = $row['id']; }

// 3. Fetch Completed Progress
$progressRes = $conn->query("SELECT subject_id FROM progress WHERE student_id = $studentId AND completed = 1");
$doneIds = [];
while($r = $progressRes->fetch_assoc()) { $doneIds[] = $r['subject_id']; }

// 4. Fetch Requirements
$requirements = [];
$rq = $conn->query("SELECT category, required_credits FROM credit_requirements WHERE department='$dept' AND regulation='$reg' AND entry_type='$entryType'");
while ($r = $rq->fetch_assoc()) { $requirements[$r['category']] = $r['required_credits']; }

// 5. Calculate earned per category
$catEarned = [];
foreach ($subjects as $s) {
    $db_id = $dbMap[$s['courseCode']] ?? null;
    if ($db_id && in_array($db_id, $doneIds)) {
        $catEarned[$s['category']] = ($catEarned[$s['category']] ?? 0) + $s['credits'];
    }
}

// 6. Identify Deficits & Weights
$deficits = [];
foreach ($requirements as $cat => $req) {
    $earned = $catEarned[$cat] ?? 0;
    if ($earned < $req) { $deficits[$cat] = $req - $earned; }
}
arsort($deficits); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Planner | AcademicPRO</title>
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
            color: #1e293b;
        }

        .container { max-width: 1100px; margin: 0 auto; padding: 40px 20px; }

        .planner-hero { 
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            padding: 50px; 
            border-radius: 30px; 
            text-align: center; 
            margin-bottom: 40px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .planner-hero h1 { 
            font-size: 2.5rem; 
            margin: 0; 
            background: linear-gradient(to right, #4f46e5, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .target-input {
            padding: 15px 25px;
            border-radius: 15px;
            border: 2px solid #e2e8f0;
            width: 250px;
            font-size: 1.1rem;
            outline: none;
            transition: 0.3s;
        }

        .target-input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }

        .btn-plan {
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px 35px;
            border-radius: 15px;
            font-weight: 800;
            cursor: pointer;
            margin-left: 10px;
            transition: 0.3s;
        }

        .btn-plan:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3); }

        .option-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; }

        .option-card { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(10px);
            border-radius: 24px; 
            padding: 25px; 
            border: 1px solid rgba(255,255,255,0.4); 
            transition: 0.3s; 
            display: flex; 
            flex-direction: column; 
        }

        .option-card:hover { transform: translateY(-10px); background: white; box-shadow: 0 20px 30px rgba(0,0,0,0.15); }

        .option-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
        }

        .credit-tag { background: #dcfce7; color: #166534; padding: 6px 15px; border-radius: 12px; font-weight: 800; font-size: 0.9rem; }

        .subject-item { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            margin-bottom: 12px; 
            padding: 12px; 
            background: #f8fafc; 
            border-radius: 15px;
            border: 1px solid transparent;
            transition: 0.2s;
        }

        .subject-item:hover { border-color: var(--primary); background: white; }

        .cat-badge { 
            font-size: 0.7rem; 
            padding: 4px 8px; 
            border-radius: 8px; 
            color: white; 
            font-weight: bold; 
            background: var(--primary);
            min-width: 30px;
            text-align: center;
        }
        
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <div class="container">
        <a href="home.php" class="btn-back">← Back to Portal</a>

        <div class="planner-hero">
            <h1>✨ Smart Course Planner</h1>
            <p style="color: #64748b; font-weight: 500;">AI-driven semester optimization based on your graduation requirements.</p>
            
            <form method="GET" style="margin-top: 30px;">
                <input type="number" name="target" class="target-input" 
                       placeholder="Target Credits (e.g. 22)" 
                       value="<?= isset($_GET['target']) ? htmlspecialchars($_GET['target']) : '' ?>" required>
                <button type="submit" class="btn-plan">Generate Plans 🚀</button>
            </form>
        </div>

        <?php if (isset($_GET['target'])): 
            $target = (int)$_GET['target'];
            echo "<div class='option-grid'>";
            
            for ($opt = 1; $opt <= 3; $opt++):
                $currentCredits = 0;
                $plan = [];
                
                // Prioritize available subjects based on deficit importance
                $available = [];
                foreach ($subjects as $s) {
                    $db_id = $dbMap[$s['courseCode']] ?? null;
                    if ($db_id && !in_array($db_id, $doneIds)) {
                        // Assign weight based on how much this category is needed
                        $s['weight'] = $deficits[$s['category']] ?? 0;
                        $available[] = $s;
                    }
                }

                // Sort by weight (highest deficit first) but add randomness for different options
                usort($available, function($a, $b) {
                    return ($b['weight'] <=> $a['weight']);
                });

                // Algorithm: Pick best fits for categories we need most
                foreach ($available as $key => $s) {
                    // Slight variation for Option 2 and 3 so they aren't identical
                    if ($opt > 1 && rand(1, 10) > 7) continue; 

                    if ($currentCredits + $s['credits'] <= $target + 2) {
                        $plan[] = $s;
                        $currentCredits += $s['credits'];
                        unset($available[$key]);
                    }
                }
        ?>
            <div class="option-card">
                <div class="option-header">
                    <h3 style="margin:0; color: var(--primary);">Option <?= $opt ?></h3>
                    <span class="credit-tag"><?= $currentCredits ?> Credits</span>
                </div>
                
                <div class="plan-list">
                    <?php if(empty($plan)): ?>
                        <p style="color: #94a3b8; text-align: center;">No subjects found.</p>
                    <?php else: ?>
                        <?php foreach ($plan as $ps): ?>
                            <div class="subject-item">
                                <span class="cat-badge" style="background: <?= ($ps['weight'] > 5) ? 'var(--accent)' : 'var(--primary)' ?>">
                                    <?= $ps['category'] ?>
                                </span>
                                <div style="flex: 1;">
                                    <p style="font-size: 0.9rem; font-weight: 700; margin: 0;"><?= htmlspecialchars($ps['courseTitle']) ?></p>
                                    <small style="color: #64748b;"><?= htmlspecialchars($ps['courseCode']) ?></small>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-weight: 800; color: #10b981;">+<?= $ps['credits'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #cbd5e1; font-size: 0.85rem; color: #64748b;">
                            <strong>Goal:</strong> This plan focuses on 
                            <?php 
                                $topCat = array_count_values(array_column($plan, 'category'));
                                arsort($topCat);
                                echo array_key_first($topCat);
                            ?> requirements.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endfor; echo "</div>"; endif; ?>
    </div>
</body>
</html>