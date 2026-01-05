<?php
include "config.php";
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['id'])) { 
    header("Location: login.php"); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Center | AcademicTracker</title>
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
            align-items: center;
            justify-content: center;
            color: #1e293b;
        }

        .contact-wrapper {
            width: 100%;
            max-width: 850px;
            padding: 40px 20px;
        }

        /* GLASS CARD CONTAINER */
        .glass-panel {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .header-section { text-align: center; margin-bottom: 40px; }
        .header-section h2 { 
            font-size: 2.5rem; 
            margin: 0; 
            background: linear-gradient(to right, #4f46e5, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }
        .header-section p { color: #64748b; font-weight: 500; margin-top: 10px; }

        /* CONTACT INFO TILES */
        .contact-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-bottom: 40px;
        }
        
        .info-card {
            background: rgba(255, 255, 255, 0.5);
            padding: 20px;
            border-radius: 20px;
            border: 1px solid #eef2ff;
            text-align: center;
            transition: all 0.3s ease;
        }
        .info-card:hover { 
            transform: translateY(-5px); 
            background: white; 
            border-color: var(--primary); 
        }

        .info-card .icon { font-size: 2rem; margin-bottom: 10px; display: block; }
        .info-card h4 { color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .info-card p { color: #1e293b; font-weight: 700; margin: 0; }

        /* MESSAGE FORM */
        .message-form {
            background: #f8fafc;
            padding: 30px;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
        }
        .message-form h3 { margin-top: 0; margin-bottom: 20px; font-size: 1.25rem; display: flex; align-items: center; gap: 10px; }

        .input-group { margin-bottom: 15px; }
        input, textarea {
            width: 100%;
            padding: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            box-sizing: border-box;
            transition: 0.3s;
        }
        input:focus, textarea:focus { 
            border-color: var(--primary); 
            outline: none; 
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .btn-send {
            background: var(--primary);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1rem;
            width: 100%;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }
        .btn-send:hover { 
            background: var(--accent); 
            transform: translateY(-2px);
            box-shadow: 0 15px 20px -5px rgba(236, 72, 153, 0.4);
        }

        /* HISTORY SECTION */
        .history-section {
            margin-top: 30px;
            border-top: 2px dashed #e2e8f0;
            padding-top: 30px;
        }
        .msg-bubble {
            background: #ffffff;
            padding: 20px;
            border-radius: 18px;
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .reply-bubble {
            margin-top: 15px;
            padding: 15px;
            background: #f0fdf4;
            border-radius: 12px;
            border-left: 4px solid #10b981;
        }

        .action-area { margin-top: 30px; text-align: center; }
        .btn-back {
            background: none;
            border: none;
            color: #64748b;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
        }
        .btn-back:hover { color: var(--primary); }
    </style>
</head>
<body>

<div class="contact-wrapper">
    <div class="glass-panel">
        <div class="header-section">
            <h2>Support Center</h2>
            <p>Direct assistance for curriculum and credit management</p>
        </div>

        <div class="contact-grid">
            <div class="info-card">
                <span class="icon">👤</span>
                <h4>Administrator</h4>
                <p>Mukesh</p>
            </div>

            <div class="info-card">
                <span class="icon">📧</span>
                <h4>Email Support</h4>
                <p><a href="mailto:mukeshsec1807@gmail.com" style="text-decoration:none; color:var(--primary);">mukeshsec1807@gmail.com</a></p>
            </div>
        </div>

        <div class="message-form">
            <h3>✉️ Send a Quick Message</h3>
            <form action="send_message.php" method="POST">
                <div class="input-group">
                    <input type="text" name="subject" placeholder="What can we help you with? (e.g., Credit Discrepancy)" required>
                </div>
                <div class="input-group">
                    <textarea name="message" placeholder="Provide as much detail as possible..." rows="4" required></textarea>
                </div>
                
                <button type="submit" class="btn-send">
                    Dispatch Message to Admin
                </button>
            </form>
        </div>

        <div class="history-section">
            <h3 style="color: #1e293b; margin-bottom: 20px;">📬 Message History & Replies</h3>
            
            <?php
            $studentId = (int)$_SESSION['id'];
            $history = $conn->query("SELECT * FROM messages WHERE student_id = $studentId ORDER BY created_at DESC");
            
            if ($history && $history->num_rows > 0):
                while($msg = $history->fetch_assoc()):
            ?>
                <div class="msg-bubble">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span style="font-weight: 800; color: var(--primary); font-size: 0.9rem;">
                            Subject: <?= htmlspecialchars($msg['subject']) ?>
                        </span>
                        <small style="color: #94a3b8;"><?= date('d M, Y', strtotime($msg['created_at'])) ?></small>
                    </div>
                    
                    <p style="margin: 0; font-size: 0.9rem; color: #475569;">
                        <?= htmlspecialchars($msg['message']) ?>
                    </p>
                    
                    <?php if (!empty($msg['admin_reply'])): ?>
                        <div class="reply-bubble">
                            <b style="color: #166534; font-size: 0.85rem; display: block; margin-bottom: 5px;">💬 Admin Reply (Mukesh):</b>
                            <p style="margin: 0; font-size: 0.9rem; color: #14532d;"><?= htmlspecialchars($msg['admin_reply']) ?></p>
                        </div>
                    <?php else: ?>
                        <div style="margin-top: 12px; color: #f59e0b; font-size: 0.8rem; font-weight: 700;">
                            ⏳ Awaiting Response...
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; else: ?>
                <p style="text-align: center; color: #94a3b8; font-style: italic;">No previous messages found.</p>
            <?php endif; ?>
        </div>

        <div class="action-area">
            <a href="home.php" class="btn-back">← Return to Portal Home</a>
        </div>
    </div>
</div>

</body>
</html>