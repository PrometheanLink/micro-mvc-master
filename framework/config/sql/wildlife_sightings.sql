-- ============================================
-- PHOENIX Nature Watch - Wildlife Sightings Table
-- ============================================
-- Run this SQL in phpMyAdmin or MySQL client
-- Database: micro_mvc
-- ============================================

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
    `sighting_date` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_species` (`species`),
    INDEX `idx_category` (`species_category`),
    INDEX `idx_location` (`latitude`, `longitude`),
    INDEX `idx_date` (`sighting_date`),
    INDEX `idx_observer` (`observer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Sample data for testing (optional)
-- ============================================

-- INSERT INTO `wildlife_sightings`
--     (`species`, `species_category`, `notes`, `latitude`, `longitude`, `location_name`, `observer_name`, `observer_id`, `sighting_date`)
-- VALUES
--     ('White-tailed Deer', 'mammals', 'Adult doe with two fawns grazing near the creek', 40.7128, -74.0060, 'Central Park', 'NatureWatcher', 'nw_001', NOW()),
--     ('Eastern Gray Squirrel', 'mammals', 'Very active, collecting acorns', 40.7135, -74.0055, 'Oak Street', 'Anonymous', NULL, NOW()),
--     ('Red-tailed Hawk', 'birds', 'Circling above the meadow, hunting', 40.7140, -74.0070, 'Meadow Trail', 'BirdLover', 'bl_002', NOW());
