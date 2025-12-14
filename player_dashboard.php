<?php
// player_dashboard.php - Sierra Leone Player Dashboard
require_once 'config.php';
// Add session verification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_role('player');

$db = get_db_connection();
$user_id = $_SESSION['user_id'];

// Helper function to safely get player data
function getPlayerValue($player, $key, $default = '') {
    if ($player && isset($player[$key]) && $player[$key] !== null) {
        return $player[$key];
    }
    return $default;
}

// Get player data
$player = false;
$stmt = $db->prepare("
    SELECT u.*, p.* 
    FROM users u 
    LEFT JOIN players p ON u.id = p.user_id 
    WHERE u.id = :user_id
");
if ($stmt) {
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    if ($result) {
        $player = $result->fetchArray(SQLITE3_ASSOC);
    }
}

// If no player data found, create an empty array to prevent errors
if (!$player) {
    $player = [];
}

// Handle profile update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $date_of_birth = sanitize_input($_POST['date_of_birth']);
        $nationality = sanitize_input($_POST['nationality']);
        $position = sanitize_input($_POST['position']);
        $current_club = sanitize_input($_POST['current_club']);
        $market_value = floatval($_POST['market_value']);
        $height_cm = intval($_POST['height_cm']);
        $weight_kg = intval($_POST['weight_kg']);
        $phone = sanitize_input($_POST['phone']);
        
        // Update users table
        $stmt = $db->prepare("UPDATE users SET phone = :phone WHERE id = :id");
        $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
        $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
        $stmt->execute();
        
        // Update players table
        $stmt = $db->prepare("
            INSERT OR REPLACE INTO players 
            (user_id, date_of_birth, nationality, position, current_club, market_value, height_cm, weight_kg) 
            VALUES (:user_id, :dob, :nationality, :position, :club, :value, :height, :weight)
        ");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(':dob', $date_of_birth, SQLITE3_TEXT);
        $stmt->bindValue(':nationality', $nationality, SQLITE3_TEXT);
        $stmt->bindValue(':position', $position, SQLITE3_TEXT);
        $stmt->bindValue(':club', $current_club, SQLITE3_TEXT);
        $stmt->bindValue(':value', $market_value, SQLITE3_FLOAT);
        $stmt->bindValue(':height', $height_cm, SQLITE3_INTEGER);
        $stmt->bindValue(':weight', $weight_kg, SQLITE3_INTEGER);
        $stmt->execute();
        
        // Refresh player data
        $stmt = $db->prepare("
            SELECT u.*, p.* 
            FROM users u 
            LEFT JOIN players p ON u.id = p.user_id 
            WHERE u.id = :user_id
        ");
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        if ($result) {
            $player = $result->fetchArray(SQLITE3_ASSOC);
        }
        
        $message = 'Profile updated successfully!';
    }
}

// Sierra Leone specific offers
$offers = [
    ['club' => 'FC Leone Stars', 'value' => '€5M', 'status' => 'Active', 'date' => '2024-01-15'],
    ['club' => 'Diamond United', 'value' => '€4.5M', 'status' => 'Negotiating', 'date' => '2024-01-10'],
    ['club' => 'Mountain FC', 'value' => '€4.2M', 'status' => 'Pending', 'date' => '2024-01-05'],
    ['club' => 'Eastern Warriors', 'value' => '€4.8M', 'status' => 'Accepted', 'date' => '2024-01-01']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sierra Leone Football Agency - Player Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sierra-header {
            background: linear-gradient(135deg, #1a472a, #00a859);
            color: white;
        }
        
        .player-stats .stat-box {
            border-top: 4px solid #1a472a;
        }
        
        .player-stats .stat-box i {
            color: #1a472a;
        }
        
        .btn-player {
            background: #1a472a;
        }
        
        .btn-player:hover {
            background: #00a859;
        }
        
        .sierra-badge {
            background: #00a859;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="player-container">
        <div class="player-header sierra-header">
            <div class="player-info">
                <h1><i class="fas fa-running"></i> Sierra Leone Player Dashboard</h1>
                <p>Welcome, <strong><?php echo htmlspecialchars(getPlayerValue($player, 'full_name', 'Player')); ?></strong></p>
                <div style="margin-top: 10px;">
                    <span class="sierra-badge">
                        <i class="fas fa-flag"></i> Sierra Leone Professional Player
                    </span>
                    <span class="sierra-badge" style="background: #3498db; margin-left: 10px;">
                        <i class="fas fa-trophy"></i> <?php echo htmlspecialchars(getPlayerValue($player, 'current_club', 'Free Agent')); ?>
                    </span>
                </div>
            </div>
            <div>
                <a href="dashboard.php" class="btn-player">
                    <i class="fas fa-arrow-left"></i> Main Dashboard
                </a>
                <a href="logout.php" class="btn-player" style="background: #e74c3c; margin-left: 10px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
                <a href="static_pages/players.html" class="btn-player" style="background: #9b59b6; margin-left: 10px;">
                    <i class="fas fa-users"></i> Public Players Page
                </a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Player Stats -->
        <div class="player-stats">
            <div class="stat-box">
                <i class="fas fa-chart-line"></i>
                <h3>Market Value</h3>
                <p style="font-size: 1.5rem; font-weight: bold; color: #00a859;">
                    <?php 
                    $market_value = getPlayerValue($player, 'market_value', 0);
                    echo $market_value > 0 ? '€' . number_format($market_value) : '€0'; 
                    ?>
                </p>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-trophy"></i>
                <h3>Current Club</h3>
                <p style="font-size: 1.2rem; font-weight: bold;">
                    <?php echo htmlspecialchars(getPlayerValue($player, 'current_club', 'Free Agent')); ?>
                </p>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-bullseye"></i>
                <h3>Position</h3>
                <p style="font-size: 1.2rem; font-weight: bold;">
                    <?php echo htmlspecialchars(getPlayerValue($player, 'position', 'Not specified')); ?>
                </p>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-flag"></i>
                <h3>Nationality</h3>
                <p style="font-size: 1.2rem; font-weight: bold;">
                    <?php echo htmlspecialchars(getPlayerValue($player, 'nationality', 'Sierra Leonean')); ?>
                </p>
            </div>
        </div>
        
        <!-- Profile Form -->
        <div class="profile-form">
            <h2><i class="fas fa-user-edit"></i> Update Your Player Profile</h2>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="date_of_birth"><i class="fas fa-birthday-cake"></i> Date of Birth</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars(getPlayerValue($player, 'date_of_birth')); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nationality"><i class="fas fa-flag"></i> Nationality</label>
                        <input type="text" id="nationality" name="nationality" value="<?php echo htmlspecialchars(getPlayerValue($player, 'nationality', 'Sierra Leonean')); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="position"><i class="fas fa-bullseye"></i> Position</label>
                        <select id="position" name="position" required>
                            <option value="">Select Position</option>
                            <option value="Goalkeeper" <?php echo getPlayerValue($player, 'position') === 'Goalkeeper' ? 'selected' : ''; ?>>Goalkeeper</option>
                            <option value="Defender" <?php echo getPlayerValue($player, 'position') === 'Defender' ? 'selected' : ''; ?>>Defender</option>
                            <option value="Midfielder" <?php echo getPlayerValue($player, 'position') === 'Midfielder' ? 'selected' : ''; ?>>Midfielder</option>
                            <option value="Forward" <?php echo getPlayerValue($player, 'position') === 'Forward' ? 'selected' : ''; ?>>Forward</option>
                            <option value="Striker" <?php echo getPlayerValue($player, 'position') === 'Striker' ? 'selected' : ''; ?>>Striker</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="current_club"><i class="fas fa-trophy"></i> Current Club</label>
                        <select id="current_club" name="current_club">
                            <option value="">Select Club</option>
                            <option value="FC Leone Stars" <?php echo getPlayerValue($player, 'current_club') === 'FC Leone Stars' ? 'selected' : ''; ?>>FC Leone Stars</option>
                            <option value="Diamond United" <?php echo getPlayerValue($player, 'current_club') === 'Diamond United' ? 'selected' : ''; ?>>Diamond United</option>
                            <option value="Mountain FC" <?php echo getPlayerValue($player, 'current_club') === 'Mountain FC' ? 'selected' : ''; ?>>Mountain FC</option>
                            <option value="Ocean Rangers" <?php echo getPlayerValue($player, 'current_club') === 'Ocean Rangers' ? 'selected' : ''; ?>>Ocean Rangers</option>
                            <option value="Eastern Warriors" <?php echo getPlayerValue($player, 'current_club') === 'Eastern Warriors' ? 'selected' : ''; ?>>Eastern Warriors</option>
                            <option value="Western United" <?php echo getPlayerValue($player, 'current_club') === 'Western United' ? 'selected' : ''; ?>>Western United</option>
                            <option value="Free Agent" <?php echo getPlayerValue($player, 'current_club') === 'Free Agent' ? 'selected' : ''; ?>>Free Agent</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="market_value"><i class="fas fa-euro-sign"></i> Market Value (€)</label>
                        <input type="number" id="market_value" name="market_value" 
                               value="<?php echo htmlspecialchars(getPlayerValue($player, 'market_value')); ?>" 
                               step="100000" min="0" placeholder="5000000">
                    </div>
                    
                    <div class="form-group">
                        <label for="height_cm"><i class="fas fa-ruler-vertical"></i> Height (cm)</label>
                        <input type="number" id="height_cm" name="height_cm" 
                               value="<?php echo htmlspecialchars(getPlayerValue($player, 'height_cm')); ?>" 
                               min="150" max="220" placeholder="180">
                    </div>
                    
                    <div class="form-group">
                        <label for="weight_kg"><i class="fas fa-weight"></i> Weight (kg)</label>
                        <input type="number" id="weight_kg" name="weight_kg" 
                               value="<?php echo htmlspecialchars(getPlayerValue($player, 'weight_kg')); ?>" 
                               min="50" max="120" placeholder="75">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars(getPlayerValue($player, 'phone', '+232')); ?>" 
                               placeholder="+232 34 498656">
                    </div>
                </div>
                
                <button type="submit" name="update_profile" class="btn-player">
                    <i class="fas fa-save"></i> Save Profile Updates
                </button>
            </form>
        </div>
        
        <!-- Current Offers -->
        <div class="offers-table">
            <h2><i class="fas fa-file-contract"></i> Current Transfer Offers (Sierra Leone Clubs)</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: left;">Club</th>
                        <th style="padding: 12px; text-align: left;">Offer Value</th>
                        <th style="padding: 12px; text-align: left;">Status</th>
                        <th style="padding: 12px; text-align: left;">Date</th>
                        <th style="padding: 12px; text-align: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($offers as $offer): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; font-weight: bold;"><?php echo htmlspecialchars($offer['club']); ?></td>
                        <td style="padding: 12px; font-weight: bold; color: #00a859;"><?php echo htmlspecialchars($offer['value']); ?></td>
                        <td style="padding: 12px;">
                            <?php 
                            $status_color = [
                                'Pending' => '#f39c12',
                                'Negotiating' => '#3498db',
                                'Accepted' => '#27ae60',
                                'Active' => '#1a472a'
                            ][$offer['status']] ?? '#666';
                            ?>
                            <span style="padding: 5px 15px; border-radius: 20px; background: <?php echo $status_color; ?>; color: white;">
                                <?php echo htmlspecialchars($offer['status']); ?>
                            </span>
                        </td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($offer['date']); ?></td>
                        <td style="padding: 12px;">
                            <button class="btn-player" style="padding: 8px 15px; font-size: 0.9rem;">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <?php if ($offer['status'] === 'Negotiating' || $offer['status'] === 'Pending'): ?>
                            <button class="btn-player" style="padding: 8px 15px; font-size: 0.9rem; background: #27ae60;">
                                <i class="fas fa-check"></i> Accept
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Current Contract -->
        <div class="contract-card" style="background: linear-gradient(135deg, #1a472a, #00a859);">
            <h3><i class="fas fa-file-signature"></i> Current Contract Status</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <div>
                    <p style="color: rgba(255,255,255,0.8);">Contract Expires</p>
                    <p style="font-size: 1.2rem; font-weight: bold;">June 30, 2025</p>
                </div>
                <div>
                    <p style="color: rgba(255,255,255,0.8);">Weekly Salary</p>
                    <p style="font-size: 1.2rem; font-weight: bold; color: #ffd700;">€<?php echo number_format(rand(5000, 20000)); ?></p>
                </div>
                <div>
                    <p style="color: rgba(255,255,255,0.8);">Release Clause</p>
                    <?php
                    $market_value = getPlayerValue($player, 'market_value', 0);
                    $release_clause = $market_value * 1.5;
                    ?>
                    <p style="font-size: 1.2rem; font-weight: bold; color: #ff6b6b;">€<?php echo number_format($release_clause); ?></p>
                </div>
                <div>
                    <p style="color: rgba(255,255,255,0.8);">Agent</p>
                    <p style="font-size: 1.2rem; font-weight: bold;">
                        <?php 
                        $agents = ['Babadi Kamara', 'Alie Tarawallie', 'Mohamed Kallon'];
                        echo htmlspecialchars($agents[array_rand($agents)]);
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div style="display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap;">
            <a href="static_pages/matches.html" class="btn-player" style="background: #3498db;">
                <i class="fas fa-futbol"></i> View Matches
            </a>
            <button class="btn-player" style="background: #e67e22;">
                <i class="fas fa-chart-bar"></i> Performance Stats
            </button>
            <button class="btn-player" style="background: #1abc9c;">
                <i class="fas fa-calendar-alt"></i> Training Schedule
            </button>
            <a href="static_pages/contact.html" class="btn-player" style="background: #34495e;">
                <i class="fas fa-headset"></i> Contact Support
            </a>
        </div>
        
        <!-- Public Website Link -->
        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px; text-align: center;">
            <p>Visit our public website for news, matches, and player profiles:</p>
            <div style="display: flex; gap: 10px; justify-content: center; margin-top: 10px;">
                <a href="static_pages/index.html" class="btn-player" style="background: #1a472a;">
                    <i class="fas fa-globe"></i> Public Homepage
                </a>
                <a href="static_pages/players.html" class="btn-player" style="background: #3498db;">
                    <i class="fas fa-users"></i> All Players
                </a>
                <a href="static_pages/matches.html" class="btn-player" style="background: #e74c3c;">
                    <i class="fas fa-futbol"></i> Upcoming Matches
                </a>
            </div>
        </div>
    </div>
</body>
</html>