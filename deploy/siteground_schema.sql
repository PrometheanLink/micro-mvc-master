-- Nature Watch Complete Database Schema for SiteGround
-- Import this file via phpMyAdmin on SiteGround
-- =====================================================

-- Create users table
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

-- Create wildlife sightings table
CREATE TABLE IF NOT EXISTS `wildlife_sightings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `species` VARCHAR(100) NOT NULL,
    `species_category` VARCHAR(50) NOT NULL DEFAULT 'other',
    `photo_path` VARCHAR(255) DEFAULT NULL,
    `notes` TEXT,
    `latitude` DECIMAL(10, 8) NOT NULL,
    `longitude` DECIMAL(11, 8) NOT NULL,
    `location_name` VARCHAR(255) DEFAULT NULL,
    `observer_name` VARCHAR(100) DEFAULT 'Anonymous',
    `observer_id` VARCHAR(50) DEFAULT NULL,
    `user_id` INT NULL DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected', 'flagged') NOT NULL DEFAULT 'pending',
    `moderated_by` INT NULL DEFAULT NULL,
    `moderated_at` TIMESTAMP NULL DEFAULT NULL,
    `rejection_reason` VARCHAR(255) NULL DEFAULT NULL,
    `sighting_date` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_species` (`species`),
    INDEX `idx_category` (`species_category`),
    INDEX `idx_location` (`latitude`, `longitude`),
    INDEX `idx_date` (`sighting_date`),
    INDEX `idx_observer` (`observer_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_moderated_by` (`moderated_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- OPTIONAL: Create your first admin user
-- Change the password hash below or create via /en/register
-- =====================================================
-- INSERT INTO users (email, username, password_hash, role, status, email_verified)
-- VALUES ('your@email.com', 'admin', '$2y$12$YOUR_BCRYPT_HASH_HERE', 10, 'active', 1);
