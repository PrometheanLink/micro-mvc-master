-- Nature Watch Users Table
-- Supports trust-based hybrid moderation system

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` TINYINT NOT NULL DEFAULT 1 COMMENT '0=banned, 1=registered, 2=trusted, 5=moderator, 10=admin',
    `status` ENUM('pending', 'active', 'suspended', 'banned') NOT NULL DEFAULT 'pending',
    `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `verification_token` VARCHAR(64) DEFAULT NULL,
    `verification_expires` TIMESTAMP NULL DEFAULT NULL,
    `approved_count` INT NOT NULL DEFAULT 0 COMMENT 'Track approved submissions for auto-trust promotion',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP NULL DEFAULT NULL,
    `reset_token` VARCHAR(64) DEFAULT NULL,
    `reset_expires` TIMESTAMP NULL DEFAULT NULL,
    UNIQUE KEY `uk_email` (`email`),
    UNIQUE KEY `uk_username` (`username`),
    INDEX `idx_role` (`role`),
    INDEX `idx_status` (`status`),
    INDEX `idx_verification` (`verification_token`),
    INDEX `idx_reset` (`reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role constants for reference:
-- ROLE_BANNED = 0      (Cannot access anything)
-- ROLE_REGISTERED = 1  (New user, submissions need approval)
-- ROLE_TRUSTED = 2     (3+ approved, auto-publish)
-- ROLE_MODERATOR = 5   (Can approve/reject submissions)
-- ROLE_ADMIN = 10      (Full control)
