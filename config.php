<?php
// config.php - Sierra Leone Football Agency Database Configuration

// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
// Choose between MySQL and SQLite by setting USE_SQLITE to true/false
define('USE_SQLITE', true); // Changed to true for easier setup
define('DB_FILE', 'football_agency.db');

if (USE_SQLITE) {
    // SQLite Configuration
    
    // Get SQLite database connection
    function get_db_connection() {
        static $db = null;
        
        if ($db === null) {
            try {
                $db = new SQLite3(DB_FILE);
                $db->enableExceptions(true);
                // Enable foreign keys
                $db->exec('PRAGMA foreign_keys = ON');
                
                // Check if tables exist, if not create them
                $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
                if (!$result->fetchArray()) {
                    // Tables don't exist, create them
                    if (file_exists('database.sql')) {
                        $sql = file_get_contents('database.sql');
                        $db->exec($sql);
                    }
                }
            } catch (Exception $e) {
                die("SQLite connection failed: " . $e->getMessage());
            }
        }
        
        return $db;
    }
    
    // Set $pdo variable for compatibility with existing code
    try {
        $pdo = new PDO("sqlite:" . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("SQLite PDO connection failed: " . $e->getMessage());
    }
    
} else {
    // MySQL Configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'sierra_leone_football');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    
    // Create MySQL connection
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        // If database doesn't exist, create it
        if ($e->getCode() == 1049) { // Database doesn't exist
            $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE " . DB_NAME);
            
            // Create tables from database.sql
            if (file_exists('database.sql')) {
                $sql = file_get_contents('database.sql');
                $pdo->exec($sql);
            }
        } else {
            die("MySQL connection failed: " . $e->getMessage());
        }
    }
    
    // Get database connection function for MySQL
    function get_db_connection() {
        global $pdo;
        return $pdo;
    }
}

// Site Configuration
define('SITE_NAME', 'Sierra Leone Football Agency');
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Set timezone
date_default_timezone_set('Africa/Freetown');

// ===== HELPER FUNCTIONS =====

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && (isset($_SESSION['user_type']) || isset($_SESSION['role']));
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php?error=Please login first');
        exit();
    }
}

// Check role-based access (ENHANCED VERSION)
function require_role($allowed_role) {
    require_login();
    
    $session_role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? '';
    
    // Admin can access everything
    if ($session_role === 'admin') {
        return true;
    }
    
    // Check if user has the required role
    $role_map = [
        'admin' => ['admin'],
        'player' => ['player'],
        'agent' => ['agent'],
        'club_manager' => ['club_manager', 'manager'],
        'manager' => ['club_manager', 'manager']
    ];
    
    $allowed_roles = isset($role_map[$allowed_role]) ? $role_map[$allowed_role] : [$allowed_role];
    
    if (!in_array($session_role, $allowed_roles)) {
        header('Location: dashboard.php?error=Access denied for your role');
        exit();
    }
}

// Get user by ID
function get_user_by_id($user_id) {
    $db = get_db_connection();
    
    if (USE_SQLITE) {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
}

// Get user by email
function get_user_by_email($email) {
    $db = get_db_connection();
    
    if (USE_SQLITE) {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}

// Get all users (FIXED VERSION)
function get_all_users($user_type = null) {
    $db = get_db_connection();
    
    if (USE_SQLITE) {
        if ($user_type) {
            $stmt = $db->prepare("SELECT * FROM users WHERE role = :user_type ORDER BY created_at DESC");
            $stmt->bindValue(':user_type', $user_type, SQLITE3_TEXT);
        } else {
            $stmt = $db->prepare("SELECT * FROM users ORDER BY created_at DESC");
        }
        
        $result = $stmt->execute();
        $users = [];
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $users[] = $row;
        }
        
        return $users;
    } else {
        if ($user_type) {
            $stmt = $db->prepare("SELECT * FROM users WHERE user_type = ? ORDER BY created_at DESC");
            $stmt->execute([$user_type]);
        } else {
            $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
        }
        
        return $stmt->fetchAll();
    }
}

// Get players (with additional player info)
function get_all_players() {
    $db = get_db_connection();
    
    if (USE_SQLITE) {
        $stmt = $db->prepare("
            SELECT u.*, p.* 
            FROM users u 
            LEFT JOIN players p ON u.id = p.user_id 
            WHERE u.role = 'player'
            ORDER BY u.created_at DESC
        ");
        $result = $stmt->execute();
        $players = [];
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $players[] = $row;
        }
        
        return $players;
    } else {
        $stmt = $db->query("
            SELECT u.*, p.* 
            FROM users u 
            LEFT JOIN players p ON u.id = p.user_id 
            WHERE u.user_type = 'player'
            ORDER BY u.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}

// Get agents
function get_all_agents() {
    $db = get_db_connection();
    
    if (USE_SQLITE) {
        $stmt = $db->prepare("
            SELECT u.*, a.* 
            FROM users u 
            LEFT JOIN agents a ON u.id = a.user_id 
            WHERE u.role = 'agent'
            ORDER BY u.created_at DESC
        ");
        $result = $stmt->execute();
        $agents = [];
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $agents[] = $row;
        }
        
        return $agents;
    } else {
        $stmt = $db->query("
            SELECT u.*, a.* 
            FROM users u 
            LEFT JOIN agents a ON u.id = a.user_id 
            WHERE u.user_type = 'agent'
            ORDER BY u.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}

// Get user statistics (FIXED VERSION)
function get_user_stats() {
    $db = get_db_connection();
    $stats = [];
    
    if (USE_SQLITE) {
        // Count by role (SQLite uses 'role' column)
        $roles = ['admin', 'player', 'agent', 'club_manager'];
        foreach ($roles as $role) {
            $result = $db->query("SELECT COUNT(*) as count FROM users WHERE role = '$role'");
            $row = $result->fetchArray(SQLITE3_ASSOC);
            $stats[$role] = $row['count'];
        }
        
        // Total users
        $result = $db->query("SELECT COUNT(*) as total FROM users");
        $stats['total'] = $result->fetchArray(SQLITE3_ASSOC)['total'];
        
    } else {
        // Count by user_type (MySQL uses 'user_type' column)
        $roles = ['admin', 'player', 'agent', 'manager'];
        foreach ($roles as $role) {
            $db_role = ($role === 'manager') ? 'club_manager' : $role;
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE user_type = ?");
            $stmt->execute([$db_role]);
            $stats[$role] = $stmt->fetchColumn();
        }
        
        // Total users
        $stmt = $db->query("SELECT COUNT(*) as total FROM users");
        $stats['total'] = $stmt->fetchColumn();
    }
    
    return $stats;
}

// Sanitize input
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Log activity
function log_activity($user_id, $action) {
    $db = get_db_connection();
    
    if (USE_SQLITE) {
        $stmt = $db->prepare("INSERT INTO user_sessions (user_id, action) VALUES (:user_id, :action)");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':action', $action, SQLITE3_TEXT);
        $stmt->execute();
    } else {
        $stmt = $db->prepare("INSERT INTO user_sessions (user_id, action) VALUES (?, ?)");
        $stmt->execute([$user_id, $action]);
    }
}

// Generate random password
function generate_random_password($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Generate password (for compatibility with admin_dashboard.php) - ONLY ONCE!
function generate_password($length = 8) {
    return generate_random_password($length);
}

// Check session timeout
function check_session_timeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header('Location: index.php?error=Session expired');
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// Initialize session timeout check
check_session_timeout();

// Get current session user's data (renamed from get_current_user)
function get_session_user() {
    if (is_logged_in()) {
        return get_user_by_id($_SESSION['user_id']);
    }
    return null;
}

// Get user's full name
function get_user_full_name($user_id = null) {
    if ($user_id === null && is_logged_in()) {
        $user_id = $_SESSION['user_id'];
    }
    
    $user = get_user_by_id($user_id);
    return $user ? $user['full_name'] : 'Unknown User';
}

// Format date for display
function format_date_display($date, $format = 'F j, Y, g:i a') {
    if (empty($date)) return 'N/A';
    try {
        $date_obj = new DateTime($date);
        return $date_obj->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

// Get role name
function get_role_display_name($role) {
    $role_names = [
        'admin' => 'Administrator',
        'player' => 'Player',
        'agent' => 'Agent',
        'club_manager' => 'Club Manager',
        'manager' => 'Club Manager'
    ];
    
    return $role_names[$role] ?? ucfirst($role);
}

// Check if user can access resource
function can_user_access_resource($resource_user_id) {
    if (!is_logged_in()) return false;
    
    // Admins can access everything
    if (($_SESSION['user_type'] ?? $_SESSION['role'] ?? '') === 'admin') return true;
    
    // Users can access their own resources
    if ($_SESSION['user_id'] == $resource_user_id) return true;
    
    return false;
}

// Add CSRF protection token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Redirect with message
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header('Location: ' . $url);
    exit();
}

// Get flash message
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Generate navigation based on user role
function generate_user_navigation() {
    if (!is_logged_in()) {
        return [
            ['name' => 'Login', 'url' => 'index.php'],
            ['name' => 'Public Website', 'url' => 'static_pages/index.html']
        ];
    }
    
    $nav = [
        ['name' => 'Dashboard', 'url' => 'dashboard.php']
    ];
    
    $role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? '';
    
    switch ($role) {
        case 'admin':
            $nav = array_merge($nav, [
                ['name' => 'Admin Dashboard', 'url' => 'admin_dashboard.php'],
                ['name' => 'Users', 'url' => 'admin_dashboard.php#users'],
                ['name' => 'Add User', 'url' => 'admin_dashboard.php#add-user']
            ]);
            break;
            
        case 'player':
            $nav = array_merge($nav, [
                ['name' => 'My Profile', 'url' => 'player_dashboard.php'],
                ['name' => 'Update Profile', 'url' => 'player_dashboard.php']
            ]);
            break;
            
        case 'agent':
            $nav = array_merge($nav, [
                ['name' => 'Agent Dashboard', 'url' => 'agent_dashboard.php'],
                ['name' => 'My Players', 'url' => 'agent_dashboard.php']
            ]);
            break;
            
        case 'club_manager':
        case 'manager':
            $nav = array_merge($nav, [
                ['name' => 'Manager Dashboard', 'url' => 'manager_dashboard.php'],
                ['name' => 'Transfer Offers', 'url' => 'manager_dashboard.php']
            ]);
            break;
    }
    
    $nav[] = ['name' => 'Logout', 'url' => 'logout.php'];
    $nav[] = ['name' => 'Public Site', 'url' => 'static_pages/index.html'];
    
    return $nav;
}

// Display flash message if exists
function display_flash_message() {
    $flash = get_flash_message();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        
        if ($type === 'error') {
            echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($message) . '</div>';
        } else {
            echo '<div class="success-message"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($message) . '</div>';
        }
    }
}

// Check if user is admin
function is_admin() {
    return is_logged_in() && (($_SESSION['user_type'] ?? $_SESSION['role'] ?? '') === 'admin');
}

// Check if user is player
function is_player() {
    return is_logged_in() && (($_SESSION['user_type'] ?? $_SESSION['role'] ?? '') === 'player');
}

// Check if user is agent
function is_agent() {
    return is_logged_in() && (($_SESSION['user_type'] ?? $_SESSION['role'] ?? '') === 'agent');
}

// Check if user is manager
function is_manager() {
    $role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? '';
    return is_logged_in() && ($role === 'club_manager' || $role === 'manager');
}

// Get greeting based on time of day
function get_greeting() {
    $hour = date('G');
    
    if ($hour < 12) {
        return 'Good morning';
    } elseif ($hour < 18) {
        return 'Good afternoon';
    } else {
        return 'Good evening';
    }
}

// Simple database initialization check
function check_database_tables() {
    $db = get_db_connection();
    
    if (USE_SQLITE) {
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if (!$result->fetchArray()) {
            return false;
        }
    } else {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM users");
            $stmt->fetchColumn();
        } catch (Exception $e) {
            return false;
        }
    }
    
    return true;
}

// Check if database exists (for quick check)
function check_database_exists() {
    if (USE_SQLITE) {
        return file_exists(DB_FILE);
    }
    return true; // MySQL assumed to exist
}

// Generate CSRF token on page load
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if database needs initialization
$current_page = basename($_SERVER['PHP_SELF']);
if (!check_database_exists() && $current_page !== 'create_db.php' && $current_page !== 'index.php') {
    header('Location: create_db.php?setup=required');
    exit();
}
?>