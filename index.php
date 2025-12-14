<?php
// index.php - Sierra Leone Football Agency Professional Portal Login (FIXED)
session_start();

// Simple database check before requiring config
if (!file_exists('football_agency.db') && basename($_SERVER['PHP_SELF']) !== 'create_db.php') {
    // Redirect to setup if database doesn't exist
    header('Location: create_db.php?setup=required');
    exit();
}

require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_type'] ?? $_SESSION['role'] ?? '';
    
    switch ($role) {
        case 'player':
            header('Location: player_dashboard.php');
            break;
        case 'agent':
            header('Location: agent_dashboard.php');
            break;
        case 'club_manager':
        case 'manager':
            header('Location: manager_dashboard.php');
            break;
        case 'admin':
            header('Location: admin_dashboard.php');
            break;
        default:
            header('Location: dashboard.php');
    }
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    try {
        // For SQLite (using 'role' column)
        if (USE_SQLITE) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
            $stmt->execute([$email, $role]);
        } else {
            // For MySQL (try both user_type and role)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND (user_type = ? OR role = ?)");
            $stmt->execute([$email, $role, $role]);
        }
        
        $user = $stmt->fetch();
        
        if ($user) {
            // Check password - accept plain text for demo or hashed
            $password_valid = false;
            
            if (isset($user['password'])) {
                // Check if password is hashed
                if (strpos($user['password'], '$2y$') === 0) {
                    $password_valid = password_verify($password, $user['password']);
                } else {
                    // Plain text password (for demo)
                    $password_valid = ($password === $user['password']);
                }
            }
            
            if ($password_valid) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'] ?? $user['user_type'] ?? $role;
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['last_activity'] = time();
                
                // Redirect based on role
                $redirect_role = $_SESSION['role'];
                switch ($redirect_role) {
                    case 'player':
                        header('Location: player_dashboard.php');
                        break;
                    case 'agent':
                        header('Location: agent_dashboard.php');
                        break;
                    case 'club_manager':
                    case 'manager':
                        header('Location: manager_dashboard.php');
                        break;
                    case 'admin':
                        header('Location: admin_dashboard.php');
                        break;
                    default:
                        header('Location: dashboard.php');
                }
                exit();
            } else {
                $error = 'Invalid password. Try: ' . $role . '123';
            }
        } else {
            // Try demo credentials (fallback if database empty)
            $demo_credentials = [
                'admin@slfa.com' => ['password' => 'admin123', 'role' => 'admin', 'name' => 'Admin User'],
                'player1@slfa.com' => ['password' => 'player123', 'role' => 'player', 'name' => 'Demo Player'],
                'agent1@slfa.com' => ['password' => 'agent123', 'role' => 'agent', 'name' => 'Demo Agent'],
                'manager1@slfa.com' => ['password' => 'manager123', 'role' => 'club_manager', 'name' => 'Demo Manager']
            ];
            
            if (isset($demo_credentials[$email]) && 
                $demo_credentials[$email]['password'] === $password && 
                $demo_credentials[$email]['role'] === $role) {
                
                $_SESSION['user_id'] = rand(1000, 9999);
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                $_SESSION['full_name'] = $demo_credentials[$email]['name'];
                $_SESSION['demo_user'] = true;
                $_SESSION['last_activity'] = time();
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid email, password, or role selection. Try demo credentials.';
            }
        }
    } catch (Exception $e) {
        $error = 'Login error: ' . $e->getMessage();
    }
}

// Check for auto-fill parameters
$autoEmail = $_GET['autoEmail'] ?? '';
$autoPassword = $_GET['autoPassword'] ?? '';
$autoRole = $_GET['autoRole'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sierra Leone Football Agency - Professional Portal Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Add these styles to your existing style.css or keep them here */
        body {
            background: linear-gradient(rgba(26, 71, 42, 0.9), rgba(15, 45, 28, 0.9)), 
                        url('images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .login-header {
            background: linear-gradient(135deg, #1a472a, #00a859);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            text-align: center;
            margin: -40px -40px 30px -40px;
        }
        
        .login-header .logo {
            font-size: 28px;
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .login-header .logo span {
            color: #00ff88;
        }
        
        .site-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .site-link {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .site-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .quick-login-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 20px 0;
        }
        
        .quick-login-btn {
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            border: none;
            outline: none;
            color: white;
        }
        
        .quick-login-btn.admin { background: #e74c3c; }
        .quick-login-btn.player { background: #3498db; }
        .quick-login-btn.agent { background: #2ecc71; }
        .quick-login-btn.manager { background: #9b59b6; }
        
        .quick-login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            opacity: 0.9;
        }
        
        .login-form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .login-form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1a472a;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .login-form-group input,
        .login-form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .login-form-group input:focus,
        .login-form-group select:focus {
            outline: none;
            border-color: #1a472a;
            box-shadow: 0 0 0 3px rgba(26, 71, 42, 0.1);
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 40px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
        }
        
        .toggle-password:hover {
            color: #1a472a;
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #1a472a, #0f2d1c);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            background: linear-gradient(135deg, #0f2d1c, #1a472a);
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(26, 71, 42, 0.2);
        }
        
        .demo-credentials {
            background: rgba(26, 71, 42, 0.05);
            border-left: 4px solid #00a859;
            padding: 15px;
            margin: 25px 0;
            border-radius: 8px;
        }
        
        .credentials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .credential {
            background: white;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
            font-size: 0.85rem;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 0.85rem;
            color: #666;
        }
        
        .error-message {
            background: #fee;
            border-left: 4px solid #e74c3c;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #c0392b;
        }
        
        .success-message {
            background: #dfffe0;
            border-left: 4px solid #27ae60;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #27ae60;
        }
        
        @media (max-width: 480px) {
            .quick-login-buttons {
                grid-template-columns: 1fr;
            }
            
            .site-links {
                gap: 10px;
            }
            
            .site-link {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-futbol"></i>
                    SL<span>Football</span>Agency
                </div>
                <h2 style="margin: 10px 0 5px 0;">Professional Portal</h2>
                <p class="subtitle">Sierra Leone Football Agency Management System</p>
                
                <!-- Site Navigation Links -->
                <div class="site-links">
                    <a href="static_pages/index.html" class="site-link">
                        <i class="fas fa-home"></i> Public Website
                    </a>
                    <a href="create_db.php" class="site-link">
                        <i class="fas fa-database"></i> Setup Database
                    </a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Quick Login Buttons -->
            <div class="quick-login-buttons">
                <button class="quick-login-btn admin" onclick="fillCredentials('admin@slfa.com', 'admin123', 'admin')">
                    <i class="fas fa-crown"></i> Administrator
                </button>
                <button class="quick-login-btn player" onclick="fillCredentials('player1@slfa.com', 'player123', 'player')">
                    <i class="fas fa-running"></i> Player
                </button>
                <button class="quick-login-btn agent" onclick="fillCredentials('agent1@slfa.com', 'agent123', 'agent')">
                    <i class="fas fa-briefcase"></i> Agent
                </button>
                <button class="quick-login-btn manager" onclick="fillCredentials('manager1@slfa.com', 'manager123', 'club_manager')">
                    <i class="fas fa-trophy"></i> Manager
                </button>
            </div>

            <form method="POST" action="">
                <div class="login-form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" name="email" required 
                           placeholder="Enter your email address"
                           value="<?php echo htmlspecialchars($autoEmail); ?>">
                </div>

                <div class="login-form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter your password"
                           value="<?php echo htmlspecialchars($autoPassword); ?>">
                    <span class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <div class="login-form-group">
                    <label for="role">
                        <i class="fas fa-user-tag"></i> Login As
                    </label>
                    <select id="role" name="role" required>
                        <option value="">Select your role</option>
                        <option value="admin" <?php echo ($autoRole == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                        <option value="player" <?php echo ($autoRole == 'player') ? 'selected' : ''; ?>>Player</option>
                        <option value="agent" <?php echo ($autoRole == 'agent') ? 'selected' : ''; ?>>Agent</option>
                        <option value="club_manager" <?php echo ($autoRole == 'club_manager') ? 'selected' : ''; ?>>Club Manager</option>
                    </select>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Professional Portal
                </button>
            </form>

            <div class="demo-credentials">
                <h3><i class="fas fa-info-circle"></i> Demo Credentials (Click buttons above)</h3>
                <div class="credentials-grid">
                    <div class="credential">
                        <strong>Administrator</strong>
                        <p>Email: admin@slfa.com</p>
                        <p>Password: admin123</p>
                    </div>
                    <div class="credential">
                        <strong>Player</strong>
                        <p>Email: player1@slfa.com</p>
                        <p>Password: player123</p>
                    </div>
                    <div class="credential">
                        <strong>Agent</strong>
                        <p>Email: agent1@slfa.com</p>
                        <p>Password: agent123</p>
                    </div>
                    <div class="credential">
                        <strong>Club Manager</strong>
                        <p>Email: manager1@slfa.com</p>
                        <p>Password: manager123</p>
                    </div>
                </div>
            </div>

            <div class="footer">
                <p>© 2024 Sierra Leone Football Agency Limited. All rights reserved.</p>
                <p>www.SLFootballAgencyLimited.com</p>
                <p>
                    <a href="static_pages/contact.html"><i class="fas fa-phone"></i> Contact: +232 34 498656</a> |
                    <a href="create_db.php" style="color: #00a859;"><i class="fas fa-database"></i> Initialize Database</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function fillCredentials(email, password, role) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            document.getElementById('role').value = role;
            
            // Auto-submit after 500ms
            setTimeout(() => {
                document.querySelector('form').submit();
            }, 500);
        }

        // Auto-fill demo credentials based on selected role
        document.getElementById('role').addEventListener('change', function () {
            const role = this.value;
            const demoCredentials = {
                'admin': { email: 'admin@slfa.com', password: 'admin123' },
                'player': { email: 'player1@slfa.com', password: 'player123' },
                'agent': { email: 'agent1@slfa.com', password: 'agent123' },
                'club_manager': { email: 'manager1@slfa.com', password: 'manager123' }
            };

            if (demoCredentials[role]) {
                document.getElementById('email').value = demoCredentials[role].email;
                document.getElementById('password').value = demoCredentials[role].password;
            }
        });

        // Auto-fill from URL parameters on page load
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const autoEmail = urlParams.get('autoEmail');
            const autoPassword = urlParams.get('autoPassword');
            const autoRole = urlParams.get('autoRole');

            if (autoEmail && autoPassword && autoRole) {
                document.getElementById('email').value = autoEmail;
                document.getElementById('password').value = autoPassword;
                document.getElementById('role').value = autoRole;
                
                // Auto-submit after 1 second
                setTimeout(() => {
                    document.querySelector('form').submit();
                }, 1000);
            }
        });
    </script>
</body>
</html>