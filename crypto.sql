-- =====================================================
-- CRYPTO DATABASE SETUP
-- =====================================================
-- This script initializes the database schema
-- Run this ONLY ONCE to set up the database

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS crypto;
USE crypto;

-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Normal User', 'Administrator') DEFAULT 'Normal User',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FILES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_uploaded_at (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FILE SHARES TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS file_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT NOT NULL,
    shared_with INT NOT NULL,
    permission ENUM('View', 'Download', 'Access') DEFAULT 'View',
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_share (file_id, shared_with),
    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_with) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_file_id (file_id),
    INDEX idx_shared_with (shared_with),
    INDEX idx_shared_at (shared_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PERMISSIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('Normal User', 'Administrator') NOT NULL,
    module VARCHAR(50) NOT NULL,
    permission VARCHAR(50) NOT NULL,
    access_level ENUM('Read', 'Write', 'All') DEFAULT 'Read',
    UNIQUE KEY unique_permission (role, module, permission),
    INDEX idx_role (role),
    INDEX idx_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- AUDIT LOGS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    module VARCHAR(50) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DEFAULT DATA INSERTION
-- =====================================================
-- Admin user: password is '192372092'
-- Hash: $2y$10$wUOaFLFa6/gv501ZEiqd6uCvSiWlrXni9pIFZ2ZwUpzJqgA9ISaBW
INSERT IGNORE INTO users (id, name, email, username, password, role, status) VALUES 
(1, 'System Admin', 'karthikvenkat062@gmail.com', 'karthikvenkat062@gmail.com', '$2y$10$wUOaFLFa6/gv501ZEiqd6uCvSiWlrXni9pIFZ2ZwUpzJqgA9ISaBW', 'Administrator', 'Active');

-- Demo user: password is 'demo123'
-- Hash: $2y$10$GGocrsNoIRPkBlAyqJuHH.uiobI7bnjAcAGG9E2.lQT2YlQvgAoqu
INSERT IGNORE INTO users (id, name, email, username, password, role, status) VALUES 
(2, 'Demo Shared User', 'demo.user@gmail.com', 'demo_user', '$2y$10$GGocrsNoIRPkBlAyqJuHH.uiobI7bnjAcAGG9E2.lQT2YlQvgAoqu', 'Normal User', 'Active');

-- =====================================================
-- DEFAULT PERMISSIONS
-- =====================================================
INSERT IGNORE INTO permissions (role, module, permission, access_level) VALUES
-- Administrator permissions
('Administrator', 'Dashboard', 'View', 'All'),
('Administrator', 'Users', 'Manage', 'All'),
('Administrator', 'Files', 'Manage', 'All'),
('Administrator', 'Permissions', 'Manage', 'All'),
('Administrator', 'Audit', 'View', 'Read'),
('Administrator', 'Reports', 'View', 'Read'),

-- Normal User permissions
('Normal User', 'Dashboard', 'View', 'Read'),
('Normal User', 'Files', 'Manage', 'Write'),
('Normal User', 'Profile', 'Manage', 'Write'),
('Normal User', 'Audit', 'View', 'Read');
