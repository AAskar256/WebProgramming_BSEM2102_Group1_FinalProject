<?php
// create_db.php - Initialize Sierra Leone Football Agency Database (SQLite Version)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants locally since config.php might not exist yet
define('USE_SQLITE', true);
define('DB_FILE', 'football_agency.db');
define('DB_NAME', 'sierra_leone_football'); // For display purposes

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Sierra Leone Football Agency - Database Setup</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, #1a472a, #00a859);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header .logo {
            font-size: 32px;
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .header .logo span {
            color: #00ff88;
        }
        
        .content {
            background: white;
            padding: 30px;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .success-box {
            background: #d4edda;
            border-left: 5px solid #28a745;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        
        .error-box {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th {
            background: #1a472a;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            display: inline-block;
        }
        
        .badge-admin {
            background: #e74c3c;
            color: white;
        }
        
        .badge-player {
            background: #3498db;
            color: white;
        }
        
        .badge-agent {
            background: #2ecc71;
            color: white;
        }
        
        .badge-manager {
            background: #9b59b6;
            color: white;
        }
        
        .login-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 30px 0;
        }
        
        .login-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn-admin {
            background: #e74c3c;
        }
        
        .btn-player {
            background: #3498db;
        }
        
        .btn-agent {
            background: #2ecc71;
        }
        
        .btn-manager {
            background: #9b59b6;
        }
        
        .btn-primary {
            background: #1a472a;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #00a859;
            transform: translateY(-3px);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .database-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #1a472a;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 4px solid #00a859;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            color: #1a472a;
            margin-bottom: 15px;
        }
        
        .stat-card h3 {
            color: #1a472a;
            margin: 10px 0;
            font-size: 1.1rem;
        }
        
        .stat-card p {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }
        
        .refresh-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 20px 0;
        }
        
        .refresh-btn:hover {
            background: #2980b9;
        }
        
        .setup-steps {
            background: #e8f4fc;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }
        
        .step-number {
            background: #1a472a;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <div class='logo'>
                <i class='fas fa-futbol'></i>
                SL<span>Football</span>Agency
            </div>
            <h1>Database Setup & Initialization</h1>
            <p>Sierra Leone Football Agency Management System</p>
        </div>
        
        <div class='content'>";

try {
    // Check if database.sql file exists
    if (!file_exists('database.sql')) {
        echo "<div class='error-box'>
                <h3><i class='fas fa-exclamation-triangle'></i> Missing SQL File</h3>
                <p>The database.sql file was not found in the root directory.</p>
                <p>Please ensure the database.sql file exists with the required SQL schema.</p>
              </div>";
        exit();
    }
    
    // Read SQL file
    $sql = file_get_contents('database.sql');
    if ($sql === false) {
        throw new Exception("Error reading database.sql file");
    }
    
    // For SQLite
    if (USE_SQLITE) {
        // Remove database file if exists
        if (file_exists(DB_FILE)) {
            unlink(DB_FILE);
        }
        
        // Create new SQLite database
        $db = new SQLite3(DB_FILE);
        $db->enableExceptions(true);
        
        // Enable foreign keys
        $db->exec('PRAGMA foreign_keys = ON');
        
        // Execute SQL statements
        $db->exec($sql);
        
        echo "<div class='success-box'>
                <h3><i class='fas fa-check-circle'></i> SQLite Database Initialized Successfully!</h3>
                <p>All tables have been created with Sierra Leone specific data in: <strong>" . DB_FILE . "</strong></p>
              </div>";
        
        // Get database connection for queries
        $db = new SQLite3(DB_FILE);
        
    } else {
        // For MySQL (if you switch later)
        echo "<div class='error-box'>
                <h3><i class='fas fa-exclamation-triangle'></i> MySQL Not Configured</h3>
                <p>Please set USE_SQLITE = true in config.php for this setup script.</p>
              </div>";
        exit();
    }
    
    echo "<div class='database-info'>
            <h3><i class='fas fa-database'></i> Database Information</h3>
            <p><strong>Database Type:</strong> SQLite 3</p>
            <p><strong>Database File:</strong> " . DB_FILE . "</p>
            <p><strong>Status:</strong> <span style='color: #27ae60; font-weight: bold;'>Connected & Ready</span></p>
          </div>";
    
    // Setup steps
    echo "<div class='setup-steps'>
            <h3><i class='fas fa-list-ol'></i> Setup Complete - Next Steps:</h3>
            <div class='step'>
                <div class='step-number'>1</div>
                <div>Database created successfully ✓</div>
            </div>
            <div class='step'>
                <div class='step-number'>2</div>
                <div>Demo users and data inserted ✓</div>
            </div>
            <div class='step'>
                <div class='step-number'>3</div>
                <div>Click login buttons below to test system</div>
            </div>
            <div class='step'>
                <div class='step-number'>4</div>
                <div>Visit public website for front-end</div>
            </div>
        </div>";
    
    // Get statistics
    echo "<div class='stats-grid'>";
    
    // Count total users
    $result = $db->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $userCount = $row['count'];
    
    echo "<div class='stat-card'>
            <i class='fas fa-users'></i>
            <h3>Total Users</h3>
            <p>" . $userCount . "</p>
          </div>";
    
    // Count players
    $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'player'");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $playerCount = $row['count'];
    
    echo "<div class='stat-card'>
            <i class='fas fa-running'></i>
            <h3>Players</h3>
            <p>" . $playerCount . "</p>
          </div>";
    
    // Count agents
    $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'agent'");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $agentCount = $row['count'];
    
    echo "<div class='stat-card'>
            <i class='fas fa-briefcase'></i>
            <h3>Agents</h3>
            <p>" . $agentCount . "</p>
          </div>";
    
    // Count managers
    $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'club_manager'");
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $managerCount = $row['count'];
    
    echo "<div class='stat-card'>
            <i class='fas fa-trophy'></i>
            <h3>Managers</h3>
            <p>" . $managerCount . "</p>
          </div>";
    
    echo "</div>";
    
    // Display all users
    echo "<h3><i class='fas fa-users'></i> Users Created in Database</h3>";
    echo "<p style='color: #666; margin-bottom: 20px;'>All demo passwords follow the pattern: <strong>role+123</strong> (e.g., admin123, player123)</p>";
    
    echo "<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th>Demo Password</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>";
    
    $result = $db->query("SELECT id, email, role, full_name, phone, status FROM users ORDER BY role, email");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $role = $row['role'];
        $password = $role . "123"; // All demo passwords are role+123
        
        // Determine badge class based on role
        $badgeClass = 'badge-' . str_replace('club_manager', 'manager', $role);
        
        echo "<tr>
                <td>{$row['id']}</td>
                <td><strong>{$row['email']}</strong></td>
                <td><span class='role-badge {$badgeClass}'>{$row['role']}</span></td>
                <td>{$row['full_name']}</td>
                <td>{$row['phone']}</td>
                <td><code>{$password}</code></td>
                <td><span style='color: #27ae60; font-weight: bold;'>{$row['status']}</span></td>
              </tr>";
    }
    
    echo "</tbody></table>";
    
    // Quick login buttons
    echo "<h3 style='margin-top: 40px;'><i class='fas fa-sign-in-alt'></i> Quick Login Links</h3>";
    echo "<div class='login-buttons'>";
    echo "<a href='index.php?autoEmail=admin@slfa.com&autoPassword=admin123&autoRole=admin' class='login-btn btn-admin'>
            <i class='fas fa-crown'></i> Login as Administrator
          </a>";
    echo "<a href='index.php?autoEmail=player1@slfa.com&autoPassword=password123&autoRole=player' class='login-btn btn-player'>
            <i class='fas fa-running'></i> Login as Player
          </a>";
    echo "<a href='index.php?autoEmail=agent1@slfa.com&autoPassword=password123&autoRole=agent' class='login-btn btn-agent'>
            <i class='fas fa-briefcase'></i> Login as Agent
          </a>";
    echo "<a href='index.php?autoEmail=manager1@slfa.com&autoPassword=password123&autoRole=club_manager' class='login-btn btn-manager'>
            <i class='fas fa-trophy'></i> Login as Manager
          </a>";
    echo "</div>";
    
    // Action buttons
    echo "<div class='action-buttons'>";
    echo "<a href='index.php' class='btn-primary'>
            <i class='fas fa-sign-in-alt'></i> Go to Login Page
          </a>";
    echo "<a href='static_pages/index.html' class='btn-primary' style='background: #00a859;'>
            <i class='fas fa-globe'></i> View Public Website
          </a>";
    echo "<a href='create_db.php' class='btn-primary' style='background: #3498db;'>
            <i class='fas fa-redo'></i> Reset Database
          </a>";
    echo "</div>";
    
    echo "<script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('Credentials copied to clipboard!');
                });
            }
            
            // Add click handlers to table rows
            document.addEventListener('DOMContentLoaded', function() {
                const rows = document.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    row.addEventListener('click', function() {
                        const email = this.cells[1].textContent;
                        const password = this.cells[5].textContent;
                        const role = this.cells[2].querySelector('.role-badge').textContent;
                        
                        copyToClipboard('Email: ' + email + '\\nPassword: ' + password + '\\nRole: ' + role);
                    });
                });
            });
          </script>";

} catch (Exception $e) {
    echo "<div class='error-box'>
            <h3><i class='fas fa-exclamation-triangle'></i> Database Error</h3>
            <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    
    if (strpos($e->getMessage(), 'Unable to open database file') !== false) {
        echo "<p><strong>Possible Solution:</strong> Check folder permissions. The web server needs write access to create the database file.</p>";
    } elseif (strpos($e->getMessage(), 'syntax error') !== false) {
        echo "<p><strong>Possible Solution:</strong> Check database.sql file for SQL syntax errors.</p>";
    }
    
    echo "</div>";
    
    echo "<div class='action-buttons'>";
    echo "<a href='index.php' class='btn-primary'>
            <i class='fas fa-home'></i> Go to Home
          </a>";
    echo "<a href='create_db.php' class='btn-primary' style='background: #e74c3c;'>
            <i class='fas fa-redo'></i> Try Again
          </a>";
    echo "</div>";
}

echo "    </div>
    </div>
</body>
</html>";