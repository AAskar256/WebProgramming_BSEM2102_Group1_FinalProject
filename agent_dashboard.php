<?php
// agent_dashboard.php - Sierra Leone Football Agency Agent Dashboard
require_once 'config.php';
// Add session verification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_role('agent');

$db = get_db_connection();
$user_id = $_SESSION['user_id'];

// Get agent data
$stmt = $db->prepare("
    SELECT u.*, a.* 
    FROM users u 
    LEFT JOIN agents a ON u.id = a.user_id 
    WHERE u.id = :user_id
");
$stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$agent = $result->fetchArray(SQLITE3_ASSOC);

// Get agent's players (Sierra Leone players)
$stmt = $db->prepare("
    SELECT p.*, u.full_name, u.email 
    FROM players p 
    JOIN users u ON p.user_id = u.id 
    WHERE u.role = 'player'
    ORDER BY p.market_value DESC 
    LIMIT 10
");
$result = $stmt->execute();
$players = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $players[] = $row;
}

// Sierra Leone specific contracts
$contracts = [
    ['player' => 'Mohamed Kamara', 'club' => 'FC Leone Stars', 'value' => '€5M', 'status' => 'Active', 'commission' => '€250,000'],
    ['player' => 'Alhaji Kamara', 'club' => 'Diamond United', 'value' => '€4.5M', 'status' => 'Active', 'commission' => '€225,000'],
    ['player' => 'Umaru Bangura', 'club' => 'Mountain FC', 'value' => '€4M', 'status' => 'Pending', 'commission' => '€200,000'],
    ['player' => 'Kei Kamara', 'club' => 'Ocean Rangers', 'value' => '€4.2M', 'status' => 'Active', 'commission' => '€210,000'],
    ['player' => 'John Kamara', 'club' => 'Eastern Warriors', 'value' => '€3.8M', 'status' => 'Negotiating', 'commission' => '€190,000'],
    ['player' => 'Musa Tombo', 'club' => 'Western United', 'value' => '€4.1M', 'status' => 'Active', 'commission' => '€205,000']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sierra Leone Football Agency - Agent Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .agent-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .agent-header {
            background: linear-gradient(135deg, #1a472a, #00a859);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .agent-stats {
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
            border-top: 4px solid #00a859;
        }
        
        .stat-box i {
            font-size: 2.5rem;
            color: #00a859;
            margin-bottom: 15px;
        }
        
        .players-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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
        
        .player-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #1a472a, #00a859);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .commission-badge {
            background: #f39c12;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .btn-agent {
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
        
        .btn-agent:hover {
            background: #00a859;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .contracts-table {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
            border: 1px solid #e9ecef;
        }
        
        .commission-stats {
            background: linear-gradient(135deg, #1a472a, #0f2d1c);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin: 20px 0;
        }
        
        .sierra-agent-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
        }
        
        .sierra-agent-info .badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
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
    <div class="agent-container">
        <div class="agent-header">
            <div class="agent-info">
                <h1><i class="fas fa-briefcase"></i> Sierra Leone Agent Dashboard</h1>
                <p>Welcome back, <strong><?php echo htmlspecialchars($agent['full_name']); ?></strong></p>
                <div class="sierra-agent-info">
                    <span class="badge">
                        <i class="fas fa-building"></i> <?php echo $agent['agency_name'] ?? 'Sierra Leone Football Agency'; ?>
                    </span>
                    <span class="badge">
                        <i class="fas fa-id-card"></i> License: <?php echo $agent['license_number'] ?? 'SLFA-AGENT-001'; ?>
                    </span>
                    <span class="badge">
                        <i class="fas fa-calendar"></i> <?php echo $agent['years_experience'] ?? '10'; ?> years experience
                    </span>
                </div>
            </div>
            <div>
                <a href="dashboard.php" class="btn-agent">
                    <i class="fas fa-arrow-left"></i> Main Dashboard
                </a>
                <a href="logout.php" class="btn-agent" style="background: #e74c3c; margin-left: 10px;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Public Navigation -->
        <div class="public-nav">
            <a href="static_pages/index.html" class="btn-agent" style="background: #1a472a;">
                <i class="fas fa-globe"></i> Public Website
            </a>
            <a href="static_pages/players.html" class="btn-agent" style="background: #00a859;">
                <i class="fas fa-users"></i> Players Directory
            </a>
            <a href="static_pages/matches.html" class="btn-agent" style="background: #3498db;">
                <i class="fas fa-futbol"></i> Match Schedule
            </a>
            <a href="static_pages/aboutus.html" class="btn-agent" style="background: #9b59b6;">
                <i class="fas fa-info-circle"></i> About Agency
            </a>
        </div>
        
        <!-- Agent Statistics -->
        <div class="agent-stats">
            <div class="stat-box">
                <i class="fas fa-users"></i>
                <h3><?php echo count($players); ?></h3>
                <p>Managed Sierra Leone Players</p>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-handshake"></i>
                <h3>€<?php echo number_format(array_sum(array_column($players, 'market_value'))); ?></h3>
                <p>Total Contract Value</p>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-chart-line"></i>
                <h3><?php echo $agent['years_experience'] ?? '10'; ?> years</h3>
                <p>Sierra Leone Experience</p>
            </div>
            
            <div class="stat-box">
                <i class="fas fa-money-bill-wave"></i>
                <h3>€<?php echo number_format((array_sum(array_column($players, 'market_value')) * 0.05)); ?></h3>
                <p>Estimated Commission</p>
            </div>
        </div>
        
        <!-- Commission Statistics -->
        <div class="commission-stats">
            <h3><i class="fas fa-chart-pie"></i> Commission Overview (Sierra Leone)</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <div>
                    <p style="opacity: 0.9;">This Month</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">€45,000</p>
                </div>
                <div>
                    <p style="opacity: 0.9;">This Year</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">€540,000</p>
                </div>
                <div>
                    <p style="opacity: 0.9;">Pending</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">€120,000</p>
                </div>
                <div>
                    <p style="opacity: 0.9;">Success Rate</p>
                    <p style="font-size: 1.5rem; font-weight: bold;">88%</p>
                </div>
            </div>
        </div>
        
        <!-- Managed Players -->
        <div>
            <h2><i class="fas fa-user-friends"></i> Managed Sierra Leone Players</h2>
            <div class="players-grid">
                <?php foreach ($players as $player): ?>
                <div class="player-card">
                    <div class="player-avatar">
                        <?php echo strtoupper(substr($player['full_name'], 0, 1)); ?>
                    </div>
                    <h3><?php echo htmlspecialchars($player['full_name']); ?></h3>
                    <p style="color: #666; margin: 10px 0;">
                        <i class="fas fa-bullseye"></i> <?php echo $player['position'] ?? 'Not specified'; ?>
                        • <i class="fas fa-trophy"></i> <?php echo $player['current_club'] ?? 'Free Agent'; ?>
                    </p>
                    <p style="font-weight: bold; color: #00a859;">
                        <i class="fas fa-euro-sign"></i> <?php echo number_format($player['market_value'] ?? 0); ?>
                    </p>
                    <div style="margin-top: 15px; display: flex; gap: 10px;">
                        <button class="btn-agent" style="padding: 8px 15px; font-size: 0.9rem;">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn-agent" style="padding: 8px 15px; font-size: 0.9rem; background: #3498db;">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-agent" style="padding: 8px 15px; font-size: 0.9rem; background: #f39c12;">
                            <i class="fas fa-file-contract"></i> Contract
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Contracts Table -->
        <div class="contracts-table">
            <h2><i class="fas fa-file-contract"></i> Active Sierra Leone Contracts</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: left;">Player</th>
                        <th style="padding: 12px; text-align: left;">Sierra Leone Club</th>
                        <th style="padding: 12px; text-align: left;">Contract Value</th>
                        <th style="padding: 12px; text-align: left;">Status</th>
                        <th style="padding: 12px; text-align: left;">Your Commission</th>
                        <th style="padding: 12px; text-align: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $contract): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; font-weight: bold;"><?php echo $contract['player']; ?></td>
                        <td style="padding: 12px;"><?php echo $contract['club']; ?></td>
                        <td style="padding: 12px; font-weight: bold; color: #00a859;"><?php echo $contract['value']; ?></td>
                        <td style="padding: 12px;">
                            <?php 
                            $status_color = [
                                'Active' => '#00a859',
                                'Pending' => '#f39c12',
                                'Negotiating' => '#3498db'
                            ][$contract['status']];
                            ?>
                            <span style="padding: 5px 15px; border-radius: 20px; background: <?php echo $status_color; ?>; color: white;">
                                <?php echo $contract['status']; ?>
                            </span>
                        </td>
                        <td style="padding: 12px; font-weight: bold; color: #f39c12;"><?php echo $contract['commission']; ?></td>
                        <td style="padding: 12px;">
                            <button class="btn-agent" style="padding: 8px 15px; font-size: 0.9rem;">
                                <i class="fas fa-edit"></i> Renegotiate
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Negotiation Panel -->
        <div style="background: white; border-radius: 15px; padding: 30px; margin: 30px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #e9ecef;">
            <h2><i class="fas fa-comments-dollar"></i> Sierra Leone Negotiation Panel</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                <div style="padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <h3>Pending Negotiations</h3>
                    <ul style="list-style: none; padding: 0; margin-top: 15px;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Mohamed Kamara</strong> → FC Leone Stars<br>
                            <small style="color: #666;">Last update: 2 days ago • Contract renewal</small>
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Alhaji Kamara</strong> → Diamond United<br>
                            <small style="color: #666;">Waiting for response • Transfer offer</small>
                        </li>
                        <li style="padding: 10px 0;">
                            <strong>John Kamara</strong> → Eastern Warriors<br>
                            <small style="color: #666;">Initial discussion • Salary negotiation</small>
                        </li>
                    </ul>
                </div>
                
                <div style="padding: 20px; background: #f8f9fa; border-radius: 10px;">
                    <h3>Upcoming Sierra Leone Meetings</h3>
                    <ul style="list-style: none; padding: 0; margin-top: 15px;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>FC Leone Stars</strong> - Jan 20, 2024<br>
                            <small style="color: #666;">Transfer discussion • Freetown Stadium</small>
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #ddd;">
                            <strong>Diamond United</strong> - Jan 22, 2024<br>
                            <small style="color: #666;">Contract renewal • Bo City</small>
                        </li>
                        <li style="padding: 10px 0;">
                            <strong>Sierra Leone FA</strong> - Jan 25, 2024<br>
                            <small style="color: #666;">National team contracts • FA Headquarters</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Agency Information -->
        <div style="background: linear-gradient(135deg, #1a472a, #0f2d1c); color: white; padding: 25px; border-radius: 15px; margin: 20px 0;">
            <h3><i class="fas fa-info-circle"></i> Sierra Leone Football Agency</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 15px;">
                <div>
                    <p><i class="fas fa-map-marker-alt"></i> 68 Willkinson Street</p>
                    <p style="opacity: 0.9;">Freetown, Sierra Leone</p>
                </div>
                <div>
                    <p><i class="fas fa-phone"></i> +232 34 498656</p>
                    <p style="opacity: 0.9;">amryaseraskar@gmail.com</p>
                </div>
                <div>
                    <p><i class="fas fa-users"></i> Leadership</p>
                    <p style="opacity: 0.9;">Babadi Kamara - President</p>
                </div>
                <div>
                    <p><i class="fas fa-globe"></i> Website</p>
                    <p style="opacity: 0.9;">www.SLFootballAgencyLimited.com</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div style="display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap;">
            <a href="static_pages/players.html" class="btn-agent" style="background: #3498db;">
                <i class="fas fa-user-plus"></i> View All Players
            </a>
            <button class="btn-agent" style="background: #9b59b6;">
                <i class="fas fa-file-contract"></i> Create New Contract
            </button>
            <button class="btn-agent" style="background: #e67e22;">
                <i class="fas fa-chart-line"></i> Commission Report
            </button>
            <button class="btn-agent" style="background: #34495e;">
                <i class="fas fa-calendar-plus"></i> Schedule Meeting
            </button>
            <a href="static_pages/matches.html" class="btn-agent" style="background: #e74c3c;">
                <i class="fas fa-futbol"></i> Match Schedule
            </a>
        </div>
    </div>
</body>
</html>