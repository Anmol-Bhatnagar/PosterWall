-- ============================================================
-- POSTERWALL.IN v2.0 — DATABASE SETUP
-- Run: mysql -u root -p < setup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS posterwall2
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE posterwall2;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) UNIQUE NOT NULL,
    mobile     VARCHAR(15)  DEFAULT NULL,
    password   VARCHAR(255) DEFAULT NULL,
    google_id  VARCHAR(100) DEFAULT NULL,
    avatar     VARCHAR(255) DEFAULT NULL,
    role       ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Wallets
CREATE TABLE IF NOT EXISTS wallets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL UNIQUE,
    balance    DECIMAL(10,2) DEFAULT 0.00,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Transactions
CREATE TABLE IF NOT EXISTS transactions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    rzp_order   VARCHAR(100) DEFAULT NULL,
    rzp_payment VARCHAR(100) DEFAULT NULL,
    amount      DECIMAL(10,2) NOT NULL,
    type        ENUM('credit','debit') NOT NULL,
    status      ENUM('pending','success','failed') DEFAULT 'pending',
    note        VARCHAR(255) DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Digital Pages
CREATE TABLE IF NOT EXISTS pages (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    token         VARCHAR(16) UNIQUE NOT NULL,
    mobile        VARCHAR(15)  DEFAULT NULL,
    business_type VARCHAR(50)  DEFAULT 'general',
    business_name VARCHAR(200) DEFAULT NULL,
    photo_url     VARCHAR(500) DEFAULT NULL,
    html_content  LONGTEXT NOT NULL,
    meta_title    VARCHAR(200) DEFAULT NULL,
    meta_desc     VARCHAR(300) DEFAULT NULL,
    theme         VARCHAR(50)  DEFAULT 'auto',
    views         INT DEFAULT 0,
    whatsapp_no   VARCHAR(15)  DEFAULT NULL,
    phone_no      VARCHAR(15)  DEFAULT NULL,
    address       TEXT DEFAULT NULL,
    is_active     TINYINT(1) DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- AI Generation Log
CREATE TABLE IF NOT EXISTS generations (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    type       VARCHAR(50) DEFAULT 'photo_to_page',
    cost       DECIMAL(10,2) DEFAULT 9.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Admin Account
-- Password: PosterWall@Admin2025
INSERT INTO users (name, email, password, role) VALUES (
    'PosterWall Admin',
    'posterwall.in@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
) ON DUPLICATE KEY UPDATE role='admin';

-- Admin wallet ₹999
INSERT INTO wallets (user_id, balance)
SELECT id, 999.00 FROM users WHERE email='posterwall.in@gmail.com'
ON DUPLICATE KEY UPDATE balance=999.00;

SELECT '✅ PosterWall v2.0 Database Ready!' AS status;
SELECT 'Admin: posterwall.in@gmail.com / PosterWall@Admin2025' AS login;
SELECT 'Visit /reset-admin.php to fix password hash' AS note;
