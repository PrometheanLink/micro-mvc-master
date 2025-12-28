-- Add moderation columns to wildlife_sightings table
-- Run this after the base wildlife_sightings table exists

ALTER TABLE `wildlife_sightings`
ADD COLUMN `user_id` INT NULL DEFAULT NULL AFTER `observer_id`,
ADD COLUMN `status` ENUM('pending', 'approved', 'rejected', 'flagged') NOT NULL DEFAULT 'pending' AFTER `user_id`,
ADD COLUMN `moderated_by` INT NULL DEFAULT NULL AFTER `status`,
ADD COLUMN `moderated_at` TIMESTAMP NULL DEFAULT NULL AFTER `moderated_by`,
ADD COLUMN `rejection_reason` VARCHAR(255) NULL DEFAULT NULL AFTER `moderated_at`,
ADD INDEX `idx_status` (`status`),
ADD INDEX `idx_user_id` (`user_id`),
ADD INDEX `idx_moderated_by` (`moderated_by`);

-- Update existing sightings to approved status (grandfathered in)
UPDATE `wildlife_sightings` SET `status` = 'approved' WHERE `status` = 'pending' OR `status` IS NULL;
