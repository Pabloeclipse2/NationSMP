-- =========================================================
--  MineCommunity — Database Schema
--  Engine: MySQL 8+ / MariaDB 10.4+
--  Charset: utf8mb4
-- =========================================================

CREATE DATABASE IF NOT EXISTS minecommunity
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE minecommunity;

-- ---------------------------------------------------------
-- USERS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(32)  NOT NULL UNIQUE,
    email           VARCHAR(120) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    minecraft_uuid  VARCHAR(36)  DEFAULT NULL,
    role            ENUM('member','moderator','admin') NOT NULL DEFAULT 'member',
    avatar_url      VARCHAR(255) DEFAULT NULL,
    bio             VARCHAR(500) DEFAULT NULL,
    status          ENUM('active','banned') NOT NULL DEFAULT 'active',
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at   TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- CATEGORIES  (forum sections)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug            VARCHAR(64)  NOT NULL UNIQUE,
    name            VARCHAR(100) NOT NULL,
    description     VARCHAR(255) DEFAULT NULL,
    icon            VARCHAR(10)  DEFAULT '📁',   -- emoji glyph used in UI
    is_private      TINYINT(1)   NOT NULL DEFAULT 0, -- 1 = applications/appeals style category
    sort_order      INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- THREADS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS threads (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED NOT NULL,
    user_id         INT UNSIGNED NOT NULL,
    title           VARCHAR(150) NOT NULL,
    body            TEXT NOT NULL,
    type            ENUM('discussion','application','appeal') NOT NULL DEFAULT 'discussion',
    status          ENUM('open','pending','accepted','denied','closed') NOT NULL DEFAULT 'open',
    is_private      TINYINT(1)   NOT NULL DEFAULT 0, -- visible only to author + staff
    is_pinned       TINYINT(1)   NOT NULL DEFAULT 0,
    views           INT UNSIGNED NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_user (user_id),
    INDEX idx_private (is_private)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- REPLIES  (posts within a thread, incl. staff replies on
-- private application/appeal threads)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS replies (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    thread_id       INT UNSIGNED NOT NULL,
    user_id         INT UNSIGNED NOT NULL,
    body            TEXT NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)    ON DELETE CASCADE,
    INDEX idx_thread (thread_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- SEED DATA — default forum categories
-- ---------------------------------------------------------
INSERT INTO categories (slug, name, description, icon, is_private, sort_order) VALUES
('news',        'News & Announcements', 'Official updates straight from the staff team.',        '📰', 0, 1),
('general',     'General Discussion',   'Talk about anything related to the server.',            '💬', 0, 2),
('suggestions', 'Suggestions',          'Pitch ideas to improve the server.',                     '💡', 0, 3),
('staff-apps',  'Staff Applications',   'Apply to join the moderation or building team.',        '🛡️', 1, 4),
('appeals',     'Unban Appeals',        'Submit an appeal if you believe you were wrongly banned.', '⚖️', 1, 5)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ---------------------------------------------------------
-- Example admin account (change password immediately!)
-- Default password: "ChangeMe123!" (hashed with password_hash/BCRYPT)
-- ---------------------------------------------------------
-- INSERT INTO users (username, email, password_hash, role)
-- VALUES ('Admin', 'admin@example.com', '$2y$10$replaceWithRealHash........................', 'admin');
