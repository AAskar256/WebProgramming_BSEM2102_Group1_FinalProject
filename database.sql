-- SQLite database schema for Sierra Leone Football Agency

-- Users table with different roles
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('admin', 'player', 'agent', 'club_manager')),
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    status VARCHAR(10) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'suspended')),
    profile_image VARCHAR(255)
);

-- Additional tables for each role
CREATE TABLE IF NOT EXISTS players (
    player_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE NOT NULL,
    date_of_birth DATE,
    nationality VARCHAR(50),
    position VARCHAR(50),
    current_club VARCHAR(100),
    market_value DECIMAL(12,2),
    height_cm INTEGER,
    weight_kg INTEGER,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS agents (
    agent_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE NOT NULL,
    license_number VARCHAR(50),
    agency_name VARCHAR(100),
    years_experience INTEGER,
    total_players INTEGER DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS club_managers (
    manager_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER UNIQUE NOT NULL,
    club_name VARCHAR(100),
    club_country VARCHAR(50),
    club_league VARCHAR(50),
    club_budget DECIMAL(15,2),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sessions table for tracking logins
CREATE TABLE IF NOT EXISTS user_sessions (
    session_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Password reset tokens
CREATE TABLE IF NOT EXISTS password_resets (
    reset_id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert Sierra Leone specific data
INSERT OR IGNORE INTO users (email, password, role, full_name, phone, status) VALUES
-- Admin (using your contact info)
('amryaseraskar@gmail.com', 'admin123', 'admin', 'Amr Yaser Askar', '+232 34 498656', 'active'),

-- Players from players.html
('mohamed.kamara@slfa.com', 'player123', 'player', 'Mohamed Kamara', '+232 70 123456', 'active'),
('alhaji.kamara@slfa.com', 'player123', 'player', 'Alhaji Kamara', '+232 70 123457', 'active'),
('umaru.bangura@slfa.com', 'player123', 'player', 'Umaru Bangura', '+232 70 123458', 'active'),
('kei.kamara@slfa.com', 'player123', 'player', 'Kei Kamara', '+232 70 123459', 'active'),
('john.kamara@slfa.com', 'player123', 'player', 'John Kamara', '+232 70 123460', 'active'),
('musa.tombo@slfa.com', 'player123', 'player', 'Musa Tombo', '+232 70 123461', 'active'),

-- Leadership from aboutus.html as agents/managers
('babadi.kamara@slfa.com', 'agent123', 'agent', 'Babadi Kamara', '+232 70 123462', 'active'),
('alie.tarawallie@slfa.com', 'agent123', 'agent', 'Alie Tarawallie', '+232 70 123463', 'active'),
('mohamed.kallon@slfa.com', 'club_manager', 'club_manager', 'Mohamed Kallon', '+232 70 123464', 'active'),

-- Club Managers from matches.html
('fc.leone.stars@slfa.com', 'manager123', 'club_manager', 'FC Leone Stars Manager', '+232 70 123465', 'active'),
('diamond.united@slfa.com', 'manager123', 'club_manager', 'Diamond United Manager', '+232 70 123466', 'active'),
('mountain.fc@slfa.com', 'manager123', 'club_manager', 'Mountain FC Manager', '+232 70 123467', 'active'),
('ocean.rangers@slfa.com', 'manager123', 'club_manager', 'Ocean Rangers Manager', '+232 70 123468', 'active'),
('eastern.warriors@slfa.com', 'manager123', 'club_manager', 'Eastern Warriors Manager', '+232 70 123469', 'active'),
('western.united@slfa.com', 'manager123', 'club_manager', 'Western United Manager', '+232 70 123470', 'active');

-- Insert player data
INSERT OR IGNORE INTO players (user_id, date_of_birth, nationality, position, current_club, market_value, height_cm, weight_kg) 
SELECT id, '1999-01-15', 'Sierra Leonean', 'Goalkeeper', 'FC Leone Stars', 5000000, 188, 80 
FROM users WHERE email = 'mohamed.kamara@slfa.com';

INSERT OR IGNORE INTO players (user_id, date_of_birth, nationality, position, current_club, market_value, height_cm, weight_kg) 
SELECT id, '1997-04-12', 'Sierra Leonean', 'Forward', 'FC Leone Stars', 8000000, 182, 75 
FROM users WHERE email = 'alhaji.kamara@slfa.com';

INSERT OR IGNORE INTO players (user_id, date_of_birth, nationality, position, current_club, market_value, height_cm, weight_kg) 
SELECT id, '1993-07-08', 'Sierra Leonean', 'Defender', 'Diamond United', 6000000, 185, 78 
FROM users WHERE email = 'umaru.bangura@slfa.com';

INSERT OR IGNORE INTO players (user_id, date_of_birth, nationality, position, current_club, market_value, height_cm, weight_kg) 
SELECT id, '1995-11-22', 'Sierra Leonean', 'Midfielder', 'Mountain FC', 7000000, 178, 72 
FROM users WHERE email = 'kei.kamara@slfa.com';

INSERT OR IGNORE INTO players (user_id, date_of_birth, nationality, position, current_club, market_value, height_cm, weight_kg) 
SELECT id, '2001-03-30', 'Sierra Leonean', 'Forward', 'Ocean Rangers', 4000000, 180, 74 
FROM users WHERE email = 'john.kamara@slfa.com';

INSERT OR IGNORE INTO players (user_id, date_of_birth, nationality, position, current_club, market_value, height_cm, weight_kg) 
SELECT id, '1998-09-18', 'Sierra Leonean', 'Forward', 'Eastern Warriors', 5500000, 176, 70 
FROM users WHERE email = 'musa.tombo@slfa.com';

-- Insert agent data
INSERT OR IGNORE INTO agents (user_id, license_number, agency_name, years_experience, total_players) 
SELECT id, 'SLFA-AGENT-001', 'Sierra Leone Football Agency', 15, 25 
FROM users WHERE email = 'babadi.kamara@slfa.com';

INSERT OR IGNORE INTO agents (user_id, license_number, agency_name, years_experience, total_players) 
SELECT id, 'SLFA-AGENT-002', 'SLFA Agency', 10, 15 
FROM users WHERE email = 'alie.tarawallie@slfa.com';

-- Insert club manager data
INSERT OR IGNORE INTO club_managers (user_id, club_name, club_country, club_league, club_budget) 
SELECT id, 'FC Leone Stars', 'Sierra Leone', 'Sierra Leone Premier League', 5000000 
FROM users WHERE email = 'fc.leone.stars@slfa.com';

INSERT OR IGNORE INTO club_managers (user_id, club_name, club_country, club_league, club_budget) 
SELECT id, 'Diamond United', 'Sierra Leone', 'Sierra Leone Premier League', 4500000 
FROM users WHERE email = 'diamond.united@slfa.com';

INSERT OR IGNORE INTO club_managers (user_id, club_name, club_country, club_league, club_budget) 
SELECT id, 'Mountain FC', 'Sierra Leone', 'Sierra Leone Premier League', 4000000 
FROM users WHERE email = 'mountain.fc@slfa.com';

INSERT OR IGNORE INTO club_managers (user_id, club_name, club_country, club_league, club_budget) 
SELECT id, 'Ocean Rangers', 'Sierra Leone', 'Sierra Leone Premier League', 3800000 
FROM users WHERE email = 'ocean.rangers@slfa.com';

INSERT OR IGNORE INTO club_managers (user_id, club_name, club_country, club_league, club_budget) 
SELECT id, 'Eastern Warriors', 'Sierra Leone', 'Sierra Leone Premier League', 4200000 
FROM users WHERE email = 'eastern.warriors@slfa.com';

INSERT OR IGNORE INTO club_managers (user_id, club_name, club_country, club_league, club_budget) 
SELECT id, 'Western United', 'Sierra Leone', 'Sierra Leone Premier League', 3500000 
FROM users WHERE email = 'western.united@slfa.com';

-- Mohamed Kallon as special manager (Head Coach)
INSERT OR IGNORE INTO club_managers (user_id, club_name, club_country, club_league, club_budget) 
SELECT id, 'Sierra Leone National Team', 'Sierra Leone', 'International', 10000000 
FROM users WHERE email = 'mohamed.kallon@slfa.com';