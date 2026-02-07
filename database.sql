CREATE DATABASE IF NOT EXISTS anatolia_voting;
USE anatolia_voting;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    has_voted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    class VARCHAR(50) NOT NULL,
    bio TEXT NOT NULL,
    mission TEXT NOT NULL,
    photo VARCHAR(255),
    vote_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS election_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL
);

-- Initial settings
INSERT INTO election_settings (setting_key, setting_value) VALUES 
('election_title', 'Chairman Election 2024'),
('election_status', 'pending'), -- pending, active, closed
('start_time', ''),
('end_time', '');

-- Default Admin (password: admin123)
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample Students (password: student123)
INSERT INTO students (student_id, email, password, full_name) VALUES 
('AS-2024-0001', 's1@anatolia.edu.gr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Smith'),
('AS-2024-0002', 's2@anatolia.edu.gr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Doe');
