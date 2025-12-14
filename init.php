<?php
// init.php - Sierra Leone Football Agency Initialization Script
// Run this once to set up everything

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Sierra Leone Football Agency - Complete Setup</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1a472a, #00a859);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .content {
            padding: 40px;
        }
        
        .step {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #00a859;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .btn {
            display: inline-block;
            background: #1a472a;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #00a859;
            transform: translateY(-2px);
        }
        
        .dashboard-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 30px;
        }
        
        .dashboard-btn {
            padding: 10px 20px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        
        .admin-btn { background: #e74c3c; }
        .player-btn { background: #3498db; }
        .agent-btn { background: #2ecc71; }
        .manager-btn { background: #9b59b6; }
        
        .demo-creds {
            background: #fff3cd;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🏆 Sierra Leone Football Agency</h1>
            <h2>Complete System Setup</h2>
        </div>
        
        <div class='content'>";

// Step 1: Check PHP version
echo "<div class='step'>";
echo "<h3>Step 1: PHP Version Check</h3>";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "<p class='success'>✓ PHP Version " . PHP_VERSION . " is compatible</p>";
} else {
    echo "<p class='error'>✗ PHP Version " . PHP_VERSION . " is too old. Requires 7.4+</p>";
}
echo "</div>";

// Step 2: Check for SQLite/MySQL
echo "<div class='step'>";
echo "<h3>Step 2: Database Check</h3>";
if (extension_loaded('pdo_sqlite')) {
    echo "<p class='success'>✓ SQLite PDO extension is enabled</p>";
} else {
    echo "<p class='error'>✗ SQLite PDO extension not found. Will try MySQL instead.</p>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<p class='success'>✓ MySQL PDO extension is enabled</p>";
} else {
    echo "<p class='error'>✗ MySQL PDO extension not found</p>";
}
echo "</div>";

// Step 3: File permissions check
echo "<div class='step'>";
echo "<h3>Step 3: File Permissions Check</h3>";
$required_files = ['config.php', 'database.sql', 'index.php'];
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✓ $file found</p>";
    } else {
        echo "<p class='error'>✗ $file not found</p>";
    }
}
echo "</div>";

// Step 4: Create database if needed
echo "<div class='step'>";
echo "<h3>Step 4: Database Setup</h3>";
echo "<p><a href='create_db.php' class='btn'>Initialize Database</a></p>";
echo "</div>";

// Demo Credentials
echo "<div class='demo-creds'>";
echo "<h3>📋 Demo Credentials (Click to Auto-Login)</h3>";
echo "<div class='dashboard-links'>";
echo "<a href='index.php?autoEmail=admin@slfa.com&autoPassword=admin123&autoRole=admin' class='dashboard-btn admin-btn'>Admin Login</a>";
echo "<a href='index.php?autoEmail=player1@slfa.com&autoPassword=password123&autoRole=player' class='dashboard-btn player-btn'>Player Login</a>";
echo "<a href='index.php?autoEmail=agent1@slfa.com&autoPassword=password123&autoRole=agent' class='dashboard-btn agent-btn'>Agent Login</a>";
echo "<a href='index.php?autoEmail=manager1@slfa.com&autoPassword=password123&autoRole=club_manager' class='dashboard-btn manager-btn'>Manager Login</a>";
echo "</div>";
echo "<p style='margin-top: 10px;'><strong>All passwords:</strong> role+123 (admin123, player123, etc.)</p>";
echo "</div>";

// Step 5: Quick Start Guide
echo "<div class='step'>";
echo "<h3>Step 5: Quick Start Guide</h3>";
echo "<ol>
    <li>Click 'Initialize Database' to create the database</li>
    <li>Use demo credentials above to login as different roles</li>
    <li>Access the public website at: <a href='static_pages/index.html'>static_pages/index.html</a></li>
    <li>Admin can add users in the admin dashboard</li>
</ol>";
echo "</div>";

// Step 6: Direct Links
echo "<div class='step'>";
echo "<h3>Step 6: Direct Access Links</h3>";
echo "<div class='dashboard-links'>";
echo "<a href='index.php' class='btn'>🔐 Login Page</a>";
echo "<a href='static_pages/index.html' class='btn'>🌐 Public Website</a>";
echo "<a href='admin_dashboard.php' class='btn'>👑 Admin Dashboard</a>";
echo "<a href='player_dashboard.php' class='btn'>⚽ Player Dashboard</a>";
echo "<a href='agent_dashboard.php' class='btn'>💼 Agent Dashboard</a>";
echo "<a href='manager_dashboard.php' class='btn'>🏆 Manager Dashboard</a>";
echo "</div>";
echo "</div>";

echo "</div></div></body></html>";
?>