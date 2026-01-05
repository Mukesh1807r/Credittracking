<?php
include "config.php";

// Session check to ensure only logged-in users access this
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['id']) && !isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

/**
 * FETCH ALL SUBJECTS
 * Pulls the code, name, and category from the curriculum table
 */
$subjects = $conn->query("SELECT course_code, course_name, category, credits FROM curriculum ORDER BY course_code ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Catalog | Credit Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .catalog-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .controls-area { 
            background: white; 
            padding: 25px; 
            border-radius: 15px; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); 
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .search-input { 
            width: 100%; 
            padding: 12px 20px; 
            border-radius: 10px; 
            border: 2px solid #eef2ff; 
            font-size: 1rem;
            outline: none;
        }
        .search-input:focus { border-color: #6366f1; }
        
        .filter-group { display: flex; gap: 10px; flex-wrap: wrap; }
        .filter-btn { 
            padding: 8px 20px; 
            border-radius: 50px; 
            border: 2px solid #6366f1; 
            background: transparent; 
            color: #6366f1; 
            font-weight: bold; 
            cursor: pointer; 
            transition: 0.3s;
        }
        .filter-btn.active { background: #6366f1; color: white; }

        .subject-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 20px; 
        }
        .subject-card { 
            background: white; 
            padding: 20px; 
            border-radius: 12px; 
            border-left: 5px solid #cbd5e1;
            transition: 0.3s;
        }
        .subject-card.fc { border-left-color: #4f46e5; } /* Foundational color */
        .subject-card.sbc { border-left-color: #ec4899; } /* Skill-based color */
        
        .category-badge {
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            font-weight: 800;
            margin-bottom: 10px;
            display: inline-block;
        }
        .bg-fc { background: #eef2ff; color: #4f46e5; }
        .bg-sbc { background: #fdf2f8; color: #ec4899; }
    </style>
</head>
<body class="bg-light">

<div class="catalog-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>📚 Subject Catalog</h1>
        <button onclick="window.history.back()" class="btn-nav">⬅ Back</button>
    </div>

    <div class="controls-area">
        <input type="text" id="subjectSearch" class="search-input" placeholder="🔍 Search by Code (e.g., 19CS) or Subject Name..." onkeyup="runSearchFilter()">
        
        <div class="filter-group">
            <button class="filter-btn active" onclick="setCategory('all', this)">All Subjects</button>
            <button class="filter-btn" onclick="setCategory('FC', this)">Foundational (FC)</button>
            <button class="filter-btn" onclick="setCategory('SBC', this)">Skill Based (SBC)</button>
        </div>
    </div>

    <div class="subject-grid" id="subjectsList">
        <?php while($row = $subjects->fetch_assoc()): 
            $cat = $row['category'] ?? 'FC';
        ?>
        <div class="subject-card <?= strtolower($cat) ?>" data-category="<?= $cat ?>">
            <span class="category-badge bg-<?= strtolower($cat) ?>"><?= $cat ?></span>
            <h3 style="margin: 5px 0; color: #1e293b;"><?= htmlspecialchars($row['course_code']) ?></h3>
            <p style="color: #64748b; font-weight: 500;"><?= htmlspecialchars($row['course_name']) ?></p>
            <div style="margin-top: 15px; font-size: 0.85rem; color: #94a3b8;">
                Credits: <strong><?= $row['credits'] ?></strong>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
let currentCategory = 'all';

function setCategory(cat, btn) {
    // UI Update: Change active button
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    currentCategory = cat;
    runSearchFilter();
}

function runSearchFilter() {
    const searchText = document.getElementById('subjectSearch').value.toLowerCase();
    const cards = document.querySelectorAll('.subject-card');

    cards.forEach(card => {
        const categoryMatch = (currentCategory === 'all' || card.getAttribute('data-category') === currentCategory);
        const textMatch = card.innerText.toLowerCase().includes(searchText);

        // Filter and Search Logic
        if (categoryMatch && textMatch) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });
}
</script>
</body>
</html>