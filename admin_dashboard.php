<?php
// admin_dashboard.php - Sierra Leone Football Agency Admin Dashboard
require_once 'config.php';
// Add session verification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_role('admin');

$db = get_db_connection();
$stats = get_user_stats();

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $email = sanitize_input($_POST['email']);
        $full_name = sanitize_input($_POST['full_name']);
        $role = sanitize_input($_POST['role']);
        $phone = sanitize_input($_POST['phone']);
        
        // Generate random password
        $password = generate_password(8);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $db->prepare("INSERT INTO users (email, password, role, full_name, phone) VALUES (:email, :password, :role, :full_name, :phone)");
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
            $stmt->bindValue(':role', $role, SQLITE3_TEXT);
            $stmt->bindValue(':full_name', $full_name, SQLITE3_TEXT);
            $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                // Insert into role-specific table if needed
                if ($role === 'player') {
                    $user_id = $db->lastInsertRowID();
                    $stmt = $db->prepare("INSERT INTO players (user_id) VALUES (:user_id)");
                    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
                    $stmt->execute();
                } elseif ($role === 'agent') {
                    $user_id = $db->lastInsertRowID();
                    $stmt = $db->prepare("INSERT INTO agents (user_id) VALUES (:user_id)");
                    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
                    $stmt->execute();
                } elseif ($role === 'club_manager') {
                    $user_id = $db->lastInsertRowID();
                    $stmt = $db->prepare("INSERT INTO club_managers (user_id) VALUES (:user_id)");
                    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
                    $stmt->execute();
                }
                
                $message = "User created successfully! Password: $password";
                $message_type = 'success';
            }
        } catch (Exception $e) {
            $message = "Error creating user: " . $e->getMessage();
            $message_type = 'error';
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        
        try {
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
            $stmt->execute();
            
            $message = "User deleted successfully!";
            $message_type = 'success';
        } catch (Exception $e) {
            $message = "Error deleting user: " . $e->getMessage();
            $message_type = 'error';
        }
    } elseif (isset($_POST['reset_password'])) {
        $user_id = intval($_POST['user_id']);
        $new_password = generate_password(8);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
            $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
            $stmt->execute();
            
            $message = "Password reset successfully! New password: $new_password";
            $message_type = 'success';
        } catch (Exception $e) {
            $message = "Error resetting password: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get all users
$users = get_all_users();

// Get recent activity
$recent_activity = [];
$result = $db->query("SELECT * FROM user_sessions ORDER BY login_time DESC LIMIT 10");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $recent_activity[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sierra Leone Football Agency - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
            min-height: 100vh;
            background: #f5f7fa;
        }
        
        .admin-sidebar {
            background: linear-gradient(135deg, #1a472a, #00a859);
            color: white;
            padding: 30px 20px;
            border-radius: 15px;
            margin: 20px 0 20px 20px;
            box-shadow: 0 4px 15px rgba(26, 71, 42, 0.3);
        }
        
        .admin-logo {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .admin-logo i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #ffd700;
        }
        
        .admin-nav ul {
            list-style: none;
            padding: 0;
        }
        
        .admin-nav li {
            margin-bottom: 10px;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .admin-nav a:hover,
        .admin-nav a.active {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
            border-left: 3px solid #ffd700;
        }
        
        .admin-main {
            padding: 30px;
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
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
            border-top: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stat-card.admin { 
            border-top-color: #1a472a; 
            color: #1a472a;
        }
        .stat-card.player { 
            border-top-color: #00a859; 
            color: #00a859;
        }
        .stat-card.agent { 
            border-top-color: #2ecc71; 
            color: #2ecc71;
        }
        .stat-card.club_manager { 
            border-top-color: #9b59b6; 
            color: #9b59b6;
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #1a472a;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-admin { background: #1a472a; color: white; }
        .badge-player { background: #00a859; color: white; }
        .badge-agent { background: #2ecc71; color: white; }
        .badge-manager { background: #9b59b6; color: white; }
        
        .form-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #1a472a;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-success {
            background: #00a859;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .sierra-header {
            background: linear-gradient(135deg, #1a472a, #0f2d1c);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .sierra-header h1 {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .sierra-header h1 i {
            color: #ffd700;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #00a859;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #e74c3c;
        }
        
        .public-nav {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .public-nav .btn {
            padding: 8px 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="admin-logo">
                <i class="fas fa-crown"></i>
                <h2>Sierra Leone Admin</h2>
                <p style="font-size: 0.9rem; opacity: 0.9;">Football Agency Management</p>
            </div>
            
            <div class="admin-user" style="padding: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; margin-bottom: 30px;">
                <p><strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></p>
                <p class="role-badge badge-admin">Administrator</p>
                <p style="font-size: 0.9rem; margin-top: 10px; opacity: 0.9;">
                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['email']); ?>
                </p>
            </div>
            
            <nav class="admin-nav">
                <ul>
                    <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="#users"><i class="fas fa-users"></i> User Management</a></li>
                    <li><a href="#add-user"><i class="fas fa-user-plus"></i> Add New User</a></li>
                    <li><a href="#reports"><i class="fas fa-chart-bar"></i> Agency Reports</a></li>
                    <li><a href="#settings"><i class="fas fa-cog"></i> Agency Settings</a></li>
                    <li><a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Main</a></li>
                    <li><a href="static_pages/index.html"><i class="fas fa-globe"></i> Public Website</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <p style="font-size: 0.8rem; opacity: 0.8;">
                    <i class="fas fa-phone"></i> +232 34 498656<br>
                    <i class="fas fa-map-marker-alt"></i> Freetown, Sierra Leone
                </p>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <div class="sierra-header">
                <h1><i class="fas fa-crown"></i> Sierra Leone Football Agency - Administrator Dashboard</h1>
                <p>Manage all Sierra Leone agency operations and users</p>
                
                <!-- Public Navigation -->
                <div class="public-nav">
                    <a href="static_pages/index.html" class="btn" style="background: #1a472a;">
                        <i class="fas fa-home"></i> Public Home
                    </a>
                    <a href="static_pages/players.html" class="btn" style="background: #00a859;">
                        <i class="fas fa-users"></i> Players Directory
                    </a>
                    <a href="static_pages/matches.html" class="btn" style="background: #3498db;">
                        <i class="fas fa-futbol"></i> Match Schedule
                    </a>
                    <a href="static_pages/aboutus.html" class="btn" style="background: #9b59b6;">
                        <i class="fas fa-info-circle"></i> About Agency
                    </a>
                    <a href="static_pages/contact.html" class="btn" style="background: #e67e22;">
                        <i class="fas fa-envelope"></i> Contact
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card admin">
                    <i class="fas fa-crown"></i>
                    <h3><?php echo $stats['admin'] ?? 0; ?></h3>
                    <p>Sierra Leone Administrators</p>
                </div>
                
                <div class="stat-card player">
                    <i class="fas fa-running"></i>
                    <h3><?php echo $stats['player'] ?? 0; ?></h3>
                    <p>Sierra Leone Players</p>
                </div>
                
                <div class="stat-card agent">
                    <i class="fas fa-briefcase"></i>
                    <h3><?php echo $stats['agent'] ?? 0; ?></h3>
                    <p>Sierra Leone Agents</p>
                </div>
                
                <div class="stat-card club_manager">
                    <i class="fas fa-trophy"></i>
                    <h3><?php echo $stats['club_manager'] ?? 0; ?></h3>
                    <p>Sierra Leone Club Managers</p>
                </div>
            </div>
            
            <!-- Add New User Form -->
            <div id="add-user" class="form-container">
                <h2><i class="fas fa-user-plus"></i> Add New Sierra Leone User</h2>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" id="email" name="email" required placeholder="user@sierraleonefa.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name"><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" id="full_name" name="full_name" required placeholder="Full Name">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="role"><i class="fas fa-user-tag"></i> Role in Sierra Leone Agency</label>
                            <select id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Administrator</option>
                                <option value="player">Sierra Leone Player</option>
                                <option value="agent">Sierra Leone Agent</option>
                                <option value="club_manager">Sierra Leone Club Manager</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Phone Number (Sierra Leone)</label>
                            <input type="tel" id="phone" name="phone" placeholder="+232">
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="add_user" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create Sierra Leone User
                        </button>
                        <a href="static_pages/players.html" class="btn" style="background: #00a859;">
                            <i class="fas fa-eye"></i> View Public Players
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Users Table -->
            <div id="users" class="table-container">
                <h2><i class="fas fa-users"></i> All Sierra Leone Agency Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="role-badge badge-<?php echo str_replace('club_manager', 'manager', $user['role']); ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="color: <?php echo $user['status'] === 'active' ? '#00a859' : '#e74c3c'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                            <td class="action-buttons">
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Reset password for this Sierra Leone user?')">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="reset_password" class="action-btn" style="background: #3498db; color: white;">
                                        <i class="fas fa-key"></i> Reset
                                    </button>
                                </form>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this Sierra Leone user? This action cannot be undone.')">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="action-btn" style="background: #e74c3c; color: white;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Recent Activity -->
            <div class="table-container">
                <h2><i class="fas fa-history"></i> Recent Sierra Leone Agency Activity</h2>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>IP Address</th>
                            <th>Login Time</th>
                            <th>Last Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activity as $activity): ?>
                        <tr>
                            <td><?php echo $activity['user_id']; ?></td>
                            <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($activity['login_time'])); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($activity['last_activity'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Agency Information -->
            <div class="form-container" style="background: linear-gradient(135deg, #1a472a, #0f2d1c); color: white;">
                <h2><i class="fas fa-info-circle"></i> Sierra Leone Football Agency Information</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                    <div>
                        <h3><i class="fas fa-map-marker-alt"></i> Address</h3>
                        <p>68 Willkinson Street<br>Freetown, Sierra Leone</p>
                    </div>
                    <div>
                        <h3><i class="fas fa-phone"></i> Contact</h3>
                        <p>+232 34 498656<br>amryaseraskar@gmail.com</p>
                    </div>
                    <div>
                        <h3><i class="fas fa-globe"></i> Website</h3>
                        <p>www.SLFootballAgencyLimited.com</p>
                    </div>
                    <div>
                        <h3><i class="fas fa-users"></i> Leadership</h3>
                        <p>Babadi Kamara - President<br>Alie Tarawallie - Vice President</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Smooth scroll to sections
        document.querySelectorAll('.admin-nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href.startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });
        
        // Auto-generate password suggestion
        document.getElementById('role').addEventListener('change', function() {
            const role = this.value;
            const roleColors = {
                'admin': '#1a472a',
                'player': '#00a859',
                'agent': '#2ecc71',
                'club_manager': '#9b59b6'
            };
            if (roleColors[role]) {
                document.querySelectorAll('.form-container h2 i').forEach(icon => {
                    icon.style.color = roleColors[role];
                });
            }
        });
    </script>
</body>
</html>