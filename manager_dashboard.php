<?php
// manager_dashboard.php - Sierra Leone Football Agency Manager Dashboard
require_once 'config.php';
// Add session verification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_role('club_manager');

$db = get_db_connection();
$user_id = $_SESSION['user_id'];

// Get manager data
$stmt = $db->prepare("
    SELECT u.*, cm.* 
    FROM users u 
    LEFT JOIN club_managers cm ON u.id = cm.user_id 
    WHERE u.id = :user_id
");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$manager = $result->fetchArray(SQLITE3_ASSOC);

// Sierra Leone available players
$available_players = [
    ['name' => 'Mohamed Kamara', 'position' => 'Goalkeeper', 'value' => '€5M', 'age' => 24, 'club' => 'FC Leone Stars'],
    ['name' => 'Alhaji Kamara', 'position' => 'Forward', 'value' => '€4.5M', 'age' => 26, 'club' => 'FC Leone Stars'],
    ['name' => 'Umaru Bangura', 'position' => 'Defender', 'value' => '€4M', 'age' => 30, 'club' => 'Diamond United'],
    ['name' => 'Kei Kamara', 'position' => 'Midfielder', 'value' => '€4.2M', 'age' => 28, 'club' => 'Mountain FC'],
    ['name' => 'John Kamara', 'position' => 'Forward', 'value' => '€3.8M', 'age' => 22, 'club' => 'Ocean Rangers'],
    ['name' => 'Musa Tombo', 'position' => 'Forward', 'value' => '€4.1M', 'age' => 25, 'club' => 'Eastern Warriors']
];

// Sierra Leone transfer offers
$transfer_offers = [
    ['player' => 'Mohamed Kamara', 'club' => 'FC Leone Stars', 'value' => '€5M', 'status' => 'Negotiating'],
    ['player' => 'Alhaji Kamara', 'club' => 'Diamond United', 'value' => '€4.5M', 'status' => 'Pending'],
    ['player' => 'Umaru Bangura', 'club' => 'Mountain FC', 'value' => '€4M', 'status' => 'Accepted']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sierra Leone Football Agency - Manager Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .manager-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .manager-header {
            background: linear-gradient(135deg, #1a472a, #00a859);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .club-info h1 {
            margin-bottom: 10px;
        }
        
        .manager-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-top: 4px solid #1a472a;
        }
        
        .stat-box i {
            font-size: 2.5rem;
            color: #1a472a;
            margin-bottom: 15px;
        }
        
        .budget-card {
            background: linear-gradient(135deg, #1a472a, #0f2d1c);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin: 20px 0;
        }
        
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .player-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .player-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-manager {
            background: #1a472a;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-manager:hover {
            background: #00a859;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .transfer-table {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            border: 1px solid #e9ecef;
        }
        
        .budget-bar {
            height: 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .budget-used {
            height: 100%;
            background: #00a859;
            border-radius: 10px;
            width: 65%;
        }
        
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }
        
        .club-badge {
            display: inline-block;
            padding: 5px 15px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 0.9rem;
            margin-left: 10px;
        }
        
        .public-nav {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="manager-container">
        <div class="manager-header">
            <div class="club-info">
                <h1><i class="fas fa-trophy"></i> Sierra Leone Club Manager Dashboard</h1>
                <p>Welcome back, <strong><?php echo htmlspecialchars($manager['full_name']); ?></strong></p>
                <p style="margin-top: 5px; font-size: 1.1rem;">
                    <i class="fas fa-flag"></i> <?php echo $manager['club_name'] ?? 'Your Sierra Leone Club'; ?>
                    <span class="club-badge">
                        <i class="fas fa-globe"></i> Sierra Leone
                    </span>
                    <span class="club-badge">
                        <i class="fas fa-futbol"></i> <?php echo $manager['club_league'] ?? 'Sierra Leone Premier League'; ?>
                    </span>
                </p>
            </div>
            <div>
                <a href="dashboard.php" class="btn-manager">
                    <i class="fas fa-arrow-left"></i> Main Dashboard
                </a>
                <a href="logout.php" class="btn-manager" style="background: #e74c3c; margin-left: 10px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Public Navigation -->
        <div class="public-nav">
            <a href="static_pages/index.html" class="btn-manager" style="background: #1a472a;">
                <i class="fas fa-globe"></i> Public Website
            </a>
            <a href="static_pages/players.html" class="btn-manager" style="background: #00a859;">
                <i class="fas fa-users"></i> Players Directory
            </a>
            <a href="static_pages/matches.html" class="btn-manager" style="background: #3498db;">
                <i class="fas fa-futbol"></i> Match Schedule
            </a>
            <a href="static_pages/aboutus.html" class="btn-manager" style="background: #9b59b6;">
                <i class="fas fa-info-circle"></i> About Agency
            </a>
        </div>
        
        <!-- Club Statistics -->
        <div class="manager-stats">
            <div class="stat-box">
                <i class="fas fa-users"></i>
                <h3>25</h3>
                <p>First Team Players</p>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-medal"></i>
                <h3><?php echo rand(3, 15); ?></h3>
                <p>Trophies Won</p>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-euro-sign"></i>
                <h3>€<?php echo number_format($manager['club_budget'] ?? 5000000); ?></h3>
                <p>Transfer Budget</p>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-chart-line"></i>
                <h3>#<?php echo rand(1, 8); ?></h3>
                <p>League Position</p>
            </div>
        </div>
        
        <!-- Budget Overview -->
        <div class="budget-card">
            <h3><i class="fas fa-money-bill-wave"></i> Sierra Leone Club Financial Overview</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <div>
                    <p style="opacity: 0.9;">Transfer Budget</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">
                        €<?php echo number_format($manager['club_budget'] ?? 5000000); ?>
                    </p>
                    <div class="budget-bar">
                        <div class="budget-used"></div>
                    </div>
                    <p style="font-size: 0.9rem;">65% used</p>
                </div>
                <div>
                    <p style="opacity: 0.9;">Wage Budget</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">€<?php echo number_format(($manager['club_budget'] ?? 5000000) * 0.7); ?>/year</p>
                    <div class="budget-bar">
                        <div class="budget-used" style="width: 80%; background: #3498db;"></div>
                    </div>
                    <p style="font-size: 0.9rem;">80% used</p>
                </div>
                <div>
                    <p style="opacity: 0.9;">Available Funds</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">€<?php echo number_format(($manager['club_budget'] ?? 5000000) * 0.35); ?></p>
                </div>
                <div>
                    <p style="opacity: 0.9;">Monthly Revenue</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">€<?php echo number_format(($manager['club_budget'] ?? 5000000) * 0.1); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Transfer Search -->
        <div class="search-box">
            <h3><i class="fas fa-search"></i> Find Sierra Leone Players</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                <input type="text" placeholder="Player name..." style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <select style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">All Positions</option>
                    <option value="Goalkeeper">Goalkeeper</option>
                    <option value="Defender">Defender</option>
                    <option value="Midfielder">Midfielder</option>
                    <option value="Forward">Forward</option>
                    <option value="Striker">Striker</option>
                </select>
                <input type="number" placeholder="Max value (€)" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <button class="btn-manager">
                    <i class="fas fa-search"></i> Search Sierra Leone Players
                </button>
            </div>
        </div>
        
        <!-- Available Players -->
        <div>
            <h2><i class="fas fa-user-plus"></i> Top Available Sierra Leone Players</h2>
            <div class="players-grid">
                <?php foreach ($available_players as $player): ?>
                <div class="player-card">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #1a472a, #00a859); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem;">
                            <?php echo strtoupper(substr($player['name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h3 style="margin: 0;"><?php echo $player['name']; ?></h3>
                            <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">
                                Age: <?php echo $player['age']; ?> • <?php echo $player['position']; ?>
                                <br>Current: <?php echo $player['club']; ?>
                            </p>
                        </div>
                    </div>
                    <p style="font-weight: bold; color: #1a472a; margin: 10px 0;">
                        <i class="fas fa-euro-sign"></i> <?php echo $player['value']; ?>
                    </p>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button class="btn-manager" style="padding: 8px 15px; font-size: 0.9rem; flex: 1;">
                            <i class="fas fa-eye"></i> Scout
                        </button>
                        <button class="btn-manager" style="padding: 8px 15px; font-size: 0.9rem; background: #00a859; flex: 1;">
                            <i class="fas fa-handshake"></i> Make Offer
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Transfer Offers -->
        <div class="transfer-table">
            <h2><i class="fas fa-exchange-alt"></i> Active Sierra Leone Transfer Offers</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: left;">Sierra Leone Player</th>
                        <th style="padding: 12px; text-align: left;">Current Club</th>
                        <th style="padding: 12px; text-align: left;">Offer Value</th>
                        <th style="padding: 12px; text-align: left;">Status</th>
                        <th style="padding: 12px; text-align: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transfer_offers as $offer): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; font-weight: bold;"><?php echo $offer['player']; ?></td>
                        <td style="padding: 12px;"><?php echo $offer['club']; ?></td>
                        <td style="padding: 12px; font-weight: bold; color: #1a472a;"><?php echo $offer['value']; ?></td>
                        <td style="padding: 12px;">
                            <?php 
                            $status_color = [
                                'Negotiating' => '#f39c12',
                                'Pending' => '#3498db',
                                'Accepted' => '#00a859'
                            ][$offer['status']];
                            ?>
                            <span style="padding: 5px 15px; border-radius: 20px; background: <?php echo $status_color; ?>; color: white;">
                                <?php echo $offer['status']; ?>
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <button class="btn-manager" style="padding: 8px 15px; font-size: 0.9rem;">
                                <i class="fas fa-edit"></i> Negotiate
                            </button>
                            <?php if ($offer['status'] === 'Negotiating' || $offer['status'] === 'Pending'): ?>
                            <button class="btn-manager" style="padding: 8px 15px; font-size: 0.9rem; background: #00a859;">
                                <i class="fas fa-check"></i> Accept
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Squad Management -->
        <div style="background: white; border-radius: 15px; padding: 30px; margin: 30px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #e9ecef;">
            <h2><i class="fas fa-chess"></i> Sierra Leone Squad Management</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                <div style="padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <h3>Injured Players (<?php echo rand(1, 5); ?>)</h3>
                    <ul style="list-style: none; padding: 0; margin-top: 15px;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Sierra Leone Defender</strong> - Hamstring<br>
                            <small style="color: #666;">Return: Feb 15, 2024 • National team</small>
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Sierra Leone Midfielder</strong> - Ankle<br>
                            <small style="color: #666;">Return: Mar 1, 2024 • League match</small>
                        </li>
                    </ul>
                </div>
                
                <div style="padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <h3>Contracts Expiring Soon</h3>
                    <ul style="list-style: none; padding: 0; margin-top: 15px;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Sierra Leone Goalkeeper</strong> - Current Club<br>
                            <small style="color: #666;">Expires: Jun 30, 2024 • Negotiation ongoing</small>
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Sierra Leone Forward</strong> - Current Club<br>
                            <small style="color: #666;">Expires: Jun 30, 2024 • Extension offered</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Agency Information -->
        <div style="background: linear-gradient(135deg, #1a472a, #0f2d1c); color: white; padding: 25px; border-radius: 15px; margin: 20px 0;">
            <h3><i class="fas fa-info-circle"></i> Sierra Leone Football Agency Contact</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 15px;">
                <div>
                    <p><i class="fas fa-map-marker-alt"></i> Agency Address</p>
                    <p style="opacity: 0.9;">68 Willkinson Street, Freetown</p>
                </div>
                <div>
                    <p><i class="fas fa-phone"></i> Contact Number</p>
                    <p style="opacity: 0.9;">+232 34 498656</p>
                </div>
                <div>
                    <p><i class="fas fa-envelope"></i> Email</p>
                    <p style="opacity: 0.9;">amryaseraskar@gmail.com</p>
                </div>
                <div>
                    <p><i class="fas fa-users"></i> Leadership</p>
                    <p style="opacity: 0.9;">Babadi Kamara - President</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div style="display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap;">
            <a href="static_pages/players.html" class="btn-manager" style="background: #3498db;">
                <i class="fas fa-user-plus"></i> View All Players
            </a>
            <button class="btn-manager" style="background: #2ecc71;">
                <i class="fas fa-chart-bar"></i> Financial Report
            </button>
            <button class="btn-manager" style="background: #e67e22;">
                <i class="fas fa-users"></i> Squad Report
            </button>
            <a href="static_pages/matches.html" class="btn-manager" style="background: #e74c3c;">
                <i class="fas fa-calendar-alt"></i> Fixtures
            </a>
            <a href="static_pages/aboutus.html" class="btn-manager" style="background: #9b59b6;">
                <i class="fas fa-info-circle"></i> About Agency
            </a>
        </div>
    </div>
</body>
</html>