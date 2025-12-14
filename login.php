<?php
// login.php - Handle user authentication for Sierra Leone Football Agency
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $role = sanitize_input($_POST['role']);
    
    // Validate input
    if (empty($email) || empty($password) || empty($role)) {
        header('Location: index.php?error=All fields are required');
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: index.php?error=Invalid email format');
        exit();
    }
    
    // Check role validity
    $valid_roles = ['admin', 'player', 'agent', 'club_manager'];
    if (!in_array($role, $valid_roles)) {
        header('Location: index.php?error=Invalid role selected');
        exit();
    }
    
    // Connect to database
    $db = get_db_connection();
    
    // Prepare query for database users
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND role = :role AND status = 'active'");
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':role', $role, SQLITE3_TEXT);
    
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    
    // Sierra Leone specific demo credentials
    $demo_credentials = [
        // Administrator
        'amryaseraskar@gmail.com' => [
            'password' => 'admin123', 
            'role' => 'admin',
            'name' => 'Amr Yaser Askar'
        ],
        
        // Players
        'mohamed.kamara@slfa.com' => [
            'password' => 'player123',
            'role' => 'player',
            'name' => 'Mohamed Kamara'
        ],
        'alhaji.kamara@slfa.com' => [
            'password' => 'player123',
            'role' => 'player',
            'name' => 'Alhaji Kamara'
        ],
        'umaru.bangura@slfa.com' => [
            'password' => 'player123',
            'role' => 'player',
            'name' => 'Umaru Bangura'
        ],
        'kei.kamara@slfa.com' => [
            'password' => 'player123',
            'role' => 'player',
            'name' => 'Kei Kamara'
        ],
        'john.kamara@slfa.com' => [
            'password' => 'player123',
            'role' => 'player',
            'name' => 'John Kamara'
        ],
        'musa.tombo@slfa.com' => [
            'password' => 'player123',
            'role' => 'player',
            'name' => 'Musa Tombo'
        ],
        
        // Agents (Leadership)
        'babadi.kamara@slfa.com' => [
            'password' => 'agent123',
            'role' => 'agent',
            'name' => 'Babadi Kamara'
        ],
        'alie.tarawallie@slfa.com' => [
            'password' => 'agent123',
            'role' => 'agent',
            'name' => 'Alie Tarawallie'
        ],
        
        // Club Managers
        'mohamed.kallon@slfa.com' => [
            'password' => 'manager123',
            'role' => 'club_manager',
            'name' => 'Mohamed Kallon'
        ],
        'fc.leone.stars@slfa.com' => [
            'password' => 'manager123',
            'role' => 'club_manager',
            'name' => 'FC Leone Stars Manager'
        ],
        'diamond.united@slfa.com' => [
            'password' => 'manager123',
            'role' => 'club_manager',
            'name' => 'Diamond United Manager'
        ],
        'mountain.fc@slfa.com' => [
            'password' => 'manager123',
            'role' => 'club_manager',
            'name' => 'Mountain FC Manager'
        ],
        'ocean.rangers@slfa.com' => [
            'password' => 'manager123',
            'role' => 'club_manager',
            'name' => 'Ocean Rangers Manager'
        ]
    ];
    
    // Check if it's a demo user
    if (isset($demo_credentials[$email]) && 
        $demo_credentials[$email]['password'] === $password && 
        $demo_credentials[$email]['role'] === $role) {
        
        // Demo user - create session data
        $_SESSION['user_id'] = rand(1000, 9999);
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['full_name'] = $demo_credentials[$email]['name'];
        $_SESSION['demo_user'] = true;
        $_SESSION['login_time'] = time();
        
        // Log login attempt
        error_log("Sierra Leone Agency login: $email as $role");
        
        // Redirect to dashboard
        header('Location: dashboard.php');
        exit();
        
    } elseif ($user) {
        // Real user from database - with password verification
        
        // Check if we have a hashed password in database
        if (isset($user['password'])) {
            // If password is hashed (starting with $2y$), use password_verify
            if (strpos($user['password'], '$2y$') === 0) {
                $password_valid = password_verify($password, $user['password']);
            } else {
                // Plain text password (for development only)
                $password_valid = ($password === $user['password']);
            }
            
            if ($password_valid) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['demo_user'] = false;
                
                // Update last login
                $update_stmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
                $update_stmt->bindValue(':id', $user['id'], SQLITE3_INTEGER);
                $update_stmt->execute();
                
                $_SESSION['login_time'] = time();
                
                // Log successful login
                $log_stmt = $db->prepare("INSERT INTO user_sessions (user_id, ip_address, user_agent, expires_at) VALUES (:user_id, :ip, :agent, DATETIME('now', '+30 minutes'))");
                $log_stmt->bindValue(':user_id', $user['id'], SQLITE3_INTEGER);
                $log_stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown', SQLITE3_TEXT);
                $log_stmt->bindValue(':agent', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown', SQLITE3_TEXT);
                $log_stmt->execute();
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                // Invalid password
                header('Location: index.php?error=Invalid password');
                exit();
            }
        } else {
            // No password in database (shouldn't happen)
            header('Location: index.php?error=Account configuration error');
            exit();
        }
        
    } else {
        // Invalid credentials - user not found
        header('Location: index.php?error=Invalid email, password, or role combination');
        exit();
    }
} else {
    // Not a POST request - redirect to login page
    header('Location: index.php');
    exit();
}
?>