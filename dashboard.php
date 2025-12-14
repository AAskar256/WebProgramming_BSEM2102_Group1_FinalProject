<?php
// dashboard.php - Sierra Leone Football Agency Dashboard
require_once 'config.php';
// Add session verification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_login();

$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];

// Role-specific dashboard links
$role_dashboards = [
    'admin' => 'admin_dashboard.php',
    'player' => 'player_dashboard.php',
    'agent' => 'agent_dashboard.php',
    'club_manager' => 'manager_dashboard.php'
];

// Redirect to role-specific dashboard if accessing main dashboard
if (isset($_GET['redirect']) && $_GET['redirect'] === 'true') {
    header('Location: ' . $role_dashboards[$role]);
    exit();
}

$role_data = [
    'admin' => [
        'title' => 'Sierra Leone Agency Administrator',
        'description' => 'Manage Sierra Leone Football Agency operations',
        'icon' => 'fas fa-crown',
        'color' => '#1a472a',
        'permissions' => ['Manage Users', 'View Reports', 'Agency Settings', 'Audit Logs'],
        'dashboard_link' => 'admin_dashboard.php'
    ],
    'player' => [
        'title' => 'Sierra Leone Player Dashboard',
        'description' => 'Manage your Sierra Leone football career',
        'icon' => 'fas fa-running',
        'color' => '#00a859',
        'permissions' => ['View Offers', 'Update Profile', 'View Stats', 'Contact Agent'],
        'dashboard_link' => 'player_dashboard.php'
    ],
    'agent' => [
        'title' => 'Sierra Leone Agent Dashboard',
        'description' => 'Manage Sierra Leone players and contracts',
        'icon' => 'fas fa-briefcase',
        'color' => '#2ecc71',
        'permissions' => ['Manage Players', 'View Contracts', 'Negotiate Offers', 'Commission Reports'],
        'dashboard_link' => 'agent_dashboard.php'
    ],
    'club_manager' => [
        'title' => 'Sierra Leone Club Manager',
        'description' => 'Manage Sierra Leone club operations and transfers',
        'icon' => 'fas fa-trophy',
        'color' => '#9b59b6',
        'permissions' => ['View Players', 'Make Offers', 'Budget Management', 'Team Reports'],
        'dashboard_link' => 'manager_dashboard.php'
    ]
];

$data = $role_data[$role];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sierra Leone Football Agency - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-top: 30px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 10px 30px rgba(26, 71, 42, 0.1);
        }
        
        .user-info {
            background: linear-gradient(135deg, <?php echo $data['color']; ?>, <?php echo adjustBrightness($data['color'], -20); ?>);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .role-badge {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .dashboard-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            border-left: 5px solid <?php echo $data['color']; ?>;
            transition: transform 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            border: 1px solid #e9ecef;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .dashboard-card h3 {
            color: <?php echo $data['color']; ?>;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .permissions-list {
            list-style: none;
            padding: 0;
        }
        
        .permissions-list li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .permissions-list li i {
            color: <?php echo $data['color']; ?>;
        }
        
        .btn-dashboard {
            display: inline-block;
            padding: 12px 30px;
            background: <?php echo $data['color']; ?>;
            color: white;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
        }
        
        .btn-dashboard:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .demo-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quick-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .sierra-nav {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .sierra-nav .btn-dashboard {
            padding: 8px 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <div class="header">
                <div class="logo" style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                    <i class="fas fa-futbol" style="color: <?php echo $data['color']; ?>; font-size: 2.5rem;"></i>
                    <div>
                        <h1 style="color: #1a472a; margin: 0;">Sierra Leone Football Agency</h1>
                        <p class="subtitle" style="color: #666; margin: 5px 0 0 0;">Professional Management Portal</p>
                    </div>
                </div>
                
                <!-- Sierra Leone Navigation -->
                <div class="sierra-nav">
                    <a href="static_pages/index.html" class="btn-dashboard" style="background: #1a472a;">
                        <i class="fas fa-globe"></i> Public Website
                    </a>
                    <a href="static_pages/players.html" class="btn-dashboard" style="background: #3498db;">
                        <i class="fas fa-users"></i> Players Directory
                    </a>
                    <a href="static_pages/matches.html" class="btn-dashboard" style="background: #e74c3c;">
                        <i class="fas fa-futbol"></i> Match Schedule
                    </a>
                    <a href="static_pages/aboutus.html" class="btn-dashboard" style="background: #9b59b6;">
                        <i class="fas fa-info-circle"></i> About Agency
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['demo_user'])): ?>
                <div class="demo-notice">
                    <i class="fas fa-info-circle"></i>
                    <strong>Demo Mode:</strong> You are logged in as a demo user for Sierra Leone Football Agency.
                </div>
            <?php endif; ?>
            
            <div class="user-info">
                <div>
                    <h2><i class="<?php echo $data['icon']; ?>"></i> <?php echo $data['title']; ?></h2>
                    <p>Welcome back, <strong><?php echo htmlspecialchars($full_name); ?></strong>!</p>
                    <div class="role-badge">
                        <i class="fas fa-user-tag"></i> Sierra Leone <?php echo ucfirst($role); ?>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="<?php echo $data['dashboard_link']; ?>" class="btn-dashboard">
                        <i class="fas fa-tachometer-alt"></i> Go to <?php echo ucfirst($role); ?> Dashboard
                    </a>
                    <a href="logout.php" class="btn-dashboard" style="background: #e74c3c;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <a href="<?php echo $data['dashboard_link']; ?>" class="dashboard-card">
                    <h3><i class="fas fa-tachometer-alt"></i> Full Dashboard</h3>
                    <p>Access your complete Sierra Leone <?php echo $role; ?> dashboard with all features and tools.</p>
                    <div style="margin-top: 15px; color: <?php echo $data['color']; ?>;">
                        <i class="fas fa-arrow-right"></i> Click to enter dashboard
                    </div>
                </a>
                
                <div class="dashboard-card">
                    <h3><i class="fas fa-user-shield"></i> Your Permissions</h3>
                    <ul class="permissions-list">
                        <?php foreach ($data['permissions'] as $permission): ?>
                            <li><i class="fas fa-check-circle"></i> <?php echo $permission; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h3><i class="fas fa-bell"></i> Recent Activity</h3>
                <div style="margin-top: 15px;">
                    <p><i class="fas fa-sign-in-alt" style="color: <?php echo $data['color']; ?>;"></i> Last login: <?php echo date('Y-m-d H:i:s'); ?></p>
                    <p><i class="fas fa-user-check" style="color: <?php echo $data['color']; ?>;"></i> Account status: <span style="color: #27ae60;">● Active</span></p>
                    <p><i class="fas fa-envelope" style="color: <?php echo $data['color']; ?>;"></i> Unread messages: <strong>3</strong></p>
                    <p><i class="fas fa-flag" style="color: <?php echo $data['color']; ?>;"></i> Agency: Sierra Leone Football Agency</p>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h3><i class="fas fa-tachometer-alt"></i> Quick Actions</h3>
                <div class="quick-actions">
                    <?php if ($role === 'admin'): ?>
                        <a href="admin_dashboard.php#add-user" class="btn-dashboard">
                            <i class="fas fa-user-plus"></i> Add User
                        </a>
                        <a href="admin_dashboard.php#users" class="btn-dashboard" style="background: #3498db;">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                        <a href="static_pages/players.html" class="btn-dashboard" style="background: #00a859;">
                            <i class="fas fa-eye"></i> View Players
                        </a>
                    <?php elseif ($role === 'player'): ?>
                        <a href="player_dashboard.php" class="btn-dashboard">
                            <i class="fas fa-user-edit"></i> Update Profile
                        </a>
                        <a href="player_dashboard.php" class="btn-dashboard" style="background: #3498db;">
                            <i class="fas fa-file-contract"></i> View Offers
                        </a>
                        <a href="static_pages/matches.html" class="btn-dashboard" style="background: #e74c3c;">
                            <i class="fas fa-futbol"></i> Match Schedule
                        </a>
                    <?php elseif ($role === 'agent'): ?>
                        <a href="agent_dashboard.php" class="btn-dashboard">
                            <i class="fas fa-user-plus"></i> Add Player
                        </a>
                        <a href="agent_dashboard.php" class="btn-dashboard" style="background: #3498db;">
                            <i class="fas fa-file-contract"></i> View Contracts
                        </a>
                        <a href="static_pages/players.html" class="btn-dashboard" style="background: #00a859;">
                            <i class="fas fa-users"></i> All Players
                        </a>
                    <?php elseif ($role === 'club_manager'): ?>
                        <a href="manager_dashboard.php" class="btn-dashboard">
                            <i class="fas fa-search"></i> Find Players
                        </a>
                        <a href="manager_dashboard.php" class="btn-dashboard" style="background: #3498db;">
                            <i class="fas fa-handshake"></i> Make Offer
                        </a>
                        <a href="static_pages/matches.html" class="btn-dashboard" style="background: #e74c3c;">
                            <i class="fas fa-calendar"></i> Fixtures
                        </a>
                    <?php endif; ?>
                    
                    <a href="static_pages/contact.html" class="btn-dashboard" style="background: #9b59b6;">
                        <i class="fas fa-envelope"></i> Contact Support
                    </a>
                    <a href="static_pages/aboutus.html" class="btn-dashboard" style="background: #e67e22;">
                        <i class="fas fa-info-circle"></i> About Agency
                    </a>
                </div>
            </div>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                <p>Need help? Contact Sierra Leone Football Agency support at <a href="mailto:amryaseraskar@gmail.com">amryaseraskar@gmail.com</a></p>
                <p>Phone: <strong>+232 34 498656</strong> | Address: <strong>68 Willkinson Street, Freetown, Sierra Leone</strong></p>
                <p style="font-size: 0.9rem; color: #666; margin-top: 20px;">
                    © 2024 Sierra Leone Football Agency Limited • Version 2.0 • Role: <?php echo ucfirst($role); ?>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Helper function to adjust color brightness
function adjustBrightness($hex, $steps) {
    $steps = max(-255, min(255, $steps));
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    }
    $color_parts = str_split($hex, 2);
    $return = '#';
    foreach ($color_parts as $color) {
        $color = hexdec($color);
        $color = max(0,min(255,$color + $steps));
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT);
    }
    return $return;
}
?>