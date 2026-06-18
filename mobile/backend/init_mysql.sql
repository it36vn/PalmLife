-- Init SQL for MAMP / MySQL
-- Run via phpMyAdmin or mysql CLI: mysql -u root -p < init_mysql.sql
-- Init SQL for MAMP / MySQL
-- Run via phpMyAdmin or mysql CLI: mysql -u root -p < init_mysql.sql

CREATE DATABASE IF NOT EXISTS xemchitay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE xemchitay;

-- Users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255),
  password_salt VARCHAR(255),
  is_admin TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  email_verified TINYINT(1) DEFAULT 0,
  locale VARCHAR(8) DEFAULT 'vi',
  birth_date DATE,
  gender VARCHAR(32),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Authentication tokens (optional simple token store)
CREATE TABLE IF NOT EXISTS auth_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(512) NOT NULL UNIQUE,
  expires_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Quota status per user (used by API responses)
CREATE TABLE IF NOT EXISTS quotas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  used INT DEFAULT 0,
  limit_count INT DEFAULT NULL,
  remaining INT DEFAULT NULL,
  period VARCHAR(64) DEFAULT 'lifetime',
  expires_at DATETIME DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subscription plans
CREATE TABLE IF NOT EXISTS subscription_plans (
  code VARCHAR(64) PRIMARY KEY,
  name_vi VARCHAR(255) NOT NULL,
  name_en VARCHAR(255) NOT NULL,
  price_vnd INT NOT NULL DEFAULT 0,
  description_vi TEXT,
  description_en TEXT,
  apple_product_id VARCHAR(255),
  google_product_id VARCHAR(255),
  store_product_type VARCHAR(64) DEFAULT 'subscription',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Purchases / store verifications
CREATE TABLE IF NOT EXISTS purchases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  platform VARCHAR(32),
  product_id VARCHAR(255),
  purchase_token TEXT,
  transaction_id VARCHAR(255),
  verified TINYINT(1) DEFAULT 0,
  raw_response JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reading / analysis history
CREATE TABLE IF NOT EXISTS analyses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  type VARCHAR(64) NOT NULL,
  locale VARCHAR(8) NOT NULL DEFAULT 'vi',
  result JSON NOT NULL,
  image_hash VARCHAR(128),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title_vi VARCHAR(255) NOT NULL,
  title_en VARCHAR(255) NOT NULL,
  body_vi TEXT NOT NULL,
  body_en TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reminders (if you plan to persist server-side reminders)
CREATE TABLE IF NOT EXISTS reminders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  title VARCHAR(255) NOT NULL,
  body TEXT,
  scheduled_at DATETIME NOT NULL,
  priority ENUM('normal','important','critical') DEFAULT 'normal',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indexes
CREATE INDEX idx_analyses_user ON analyses(user_id);
CREATE INDEX idx_reminders_user ON reminders(user_id);
CREATE INDEX idx_purchases_user ON purchases(user_id);

-- Seed data
INSERT INTO users (name, email, password_hash, is_admin, email_verified, locale)
VALUES ('Admin', 'admin@example.com', '$2y$12$W0zkbsNtOey7yNJiIKVu8ujQJXj590XG9/2QgGgX8PmwEj60mhGgS', 1, 1, 'vi')
ON DUPLICATE KEY UPDATE
  name=VALUES(name),
  password_hash=VALUES(password_hash);

-- Free + paid plans
INSERT INTO subscription_plans (code, name_vi, name_en, price_vnd, description_vi, description_en)
VALUES
  ('free', 'Miễn phí', 'Free', 0, 'Gói miễn phí', 'Free plan'),
  ('month', 'Gói tháng', 'Monthly', 49000, 'Gói 1 tháng', '1-month plan'),
  ('year', 'Gói năm', 'Yearly', 490000, 'Gói 1 năm', '1-year plan')
ON DUPLICATE KEY UPDATE name_vi=VALUES(name_vi);

-- Example notification
INSERT INTO notifications (title_vi, title_en, body_vi, body_en)
VALUES ('Chào mừng', 'Welcome', 'Chào mừng bạn đến với Xem Chỉ Tay', 'Welcome to Palm Life')
ON DUPLICATE KEY UPDATE title_vi=VALUES(title_vi);

-- Example quota for admin
INSERT INTO quotas (user_id, used, limit_count, remaining, period)
SELECT id, 0, NULL, NULL, 'lifetime' FROM users WHERE email='admin@example.com'
ON DUPLICATE KEY UPDATE updated_at=NOW();

-- Done
SELECT 'init completed' AS status;
