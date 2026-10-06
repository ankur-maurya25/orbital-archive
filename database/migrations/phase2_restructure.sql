-- ==============================================================
-- ORBITAL ARCHIVE - Phase 2 Database Schema Migration
-- Adds normalized instruments system, equipment specifications,
-- and verification audit metadata.
-- ==============================================================

USE `orbital_archive`;

-- 1. Create INSTRUMENTS table
CREATE TABLE IF NOT EXISTS `instruments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `official_name` VARCHAR(255) NULL,
  `type` VARCHAR(100) NULL,
  `purpose` TEXT NULL,
  `description` TEXT NULL,
  `manufacturer` VARCHAR(255) NULL,
  `agency_id` INT NULL,
  `specifications` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`agency_id`) REFERENCES `agencies`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Create EQUIPMENT_INSTRUMENTS relation table
CREATE TABLE IF NOT EXISTS `equipment_instruments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `equipment_id` INT NOT NULL,
  `instrument_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`equipment_id`) REFERENCES `equipment`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`instrument_id`) REFERENCES `instruments`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `uk_equipment_instrument` (`equipment_id`, `instrument_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Enhance EQUIPMENT table with aerospace specifications and verification fields
-- Using stored procedure / conditional alter to ensure idempotent execution
DROP PROCEDURE IF EXISTS `AddEquipmentPhase2Columns`;
DELIMITER $$
CREATE PROCEDURE `AddEquipmentPhase2Columns`()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='slug') THEN
    ALTER TABLE `equipment` ADD COLUMN `slug` VARCHAR(100) NULL UNIQUE AFTER `name`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='mass') THEN
    ALTER TABLE `equipment` ADD COLUMN `mass` VARCHAR(100) NULL AFTER `type`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='dimensions') THEN
    ALTER TABLE `equipment` ADD COLUMN `dimensions` VARCHAR(255) NULL AFTER `mass`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='power') THEN
    ALTER TABLE `equipment` ADD COLUMN `power` VARCHAR(255) NULL AFTER `dimensions`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='mobility') THEN
    ALTER TABLE `equipment` ADD COLUMN `mobility` TEXT NULL AFTER `power`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='robotic_arm') THEN
    ALTER TABLE `equipment` ADD COLUMN `robotic_arm` TEXT NULL AFTER `mobility`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='autonomy') THEN
    ALTER TABLE `equipment` ADD COLUMN `autonomy` TEXT NULL AFTER `robotic_arm`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='sample_caching') THEN
    ALTER TABLE `equipment` ADD COLUMN `sample_caching` TEXT NULL AFTER `autonomy`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='primary_region') THEN
    ALTER TABLE `equipment` ADD COLUMN `primary_region` VARCHAR(255) NULL AFTER `current_location`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='mission_phase') THEN
    ALTER TABLE `equipment` ADD COLUMN `mission_phase` VARCHAR(100) NULL AFTER `primary_region`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='journey_days') THEN
    ALTER TABLE `equipment` ADD COLUMN `journey_days` INT NULL AFTER `operational_period`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='verification_status') THEN
    ALTER TABLE `equipment` ADD COLUMN `verification_status` ENUM('CONFIRMED', 'APPROXIMATE', 'UNKNOWN', 'CONFLICTING') DEFAULT 'CONFIRMED' AFTER `legacy`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='equipment' AND column_name='last_verified') THEN
    ALTER TABLE `equipment` ADD COLUMN `last_verified` DATE NULL AFTER `verification_status`;
  END IF;
END$$
DELIMITER ;
CALL `AddEquipmentPhase2Columns`();
DROP PROCEDURE IF EXISTS `AddEquipmentPhase2Columns`;

-- 4. Enhance SOURCES table
DROP PROCEDURE IF EXISTS `AddSourcesPhase2Columns`;
DELIMITER $$
CREATE PROCEDURE `AddSourcesPhase2Columns`()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='sources' AND column_name='organization') THEN
    ALTER TABLE `sources` ADD COLUMN `organization` VARCHAR(255) NULL AFTER `source_name`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='sources' AND column_name='publication_date') THEN
    ALTER TABLE `sources` ADD COLUMN `publication_date` DATE NULL AFTER `description`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='sources' AND column_name='accessed_at') THEN
    ALTER TABLE `sources` ADD COLUMN `accessed_at` DATE NULL AFTER `verified_date`;
  END IF;
END$$
DELIMITER ;
CALL `AddSourcesPhase2Columns`();
DROP PROCEDURE IF EXISTS `AddSourcesPhase2Columns`;

-- 5. Enhance TIMELINE_EVENTS table
DROP PROCEDURE IF EXISTS `AddTimelinePhase2Columns`;
DELIMITER $$
CREATE PROCEDURE `AddTimelinePhase2Columns`()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='timeline_events' AND column_name='event_date') THEN
    ALTER TABLE `timeline_events` ADD COLUMN `event_date` DATE NULL AFTER `year`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='timeline_events' AND column_name='source_id') THEN
    ALTER TABLE `timeline_events` ADD COLUMN `source_id` INT NULL AFTER `importance`;
  END IF;
END$$
DELIMITER ;
CALL `AddTimelinePhase2Columns`();
DROP PROCEDURE IF EXISTS `AddTimelinePhase2Columns`;

-- 6. Enhance IMAGES table
DROP PROCEDURE IF EXISTS `AddImagesPhase2Columns`;
DELIMITER $$
CREATE PROCEDURE `AddImagesPhase2Columns`()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='images' AND column_name='source_organization') THEN
    ALTER TABLE `images` ADD COLUMN `source_organization` VARCHAR(255) NULL AFTER `source`;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='orbital_archive' AND table_name='images' AND column_name='source_id') THEN
    ALTER TABLE `images` ADD COLUMN `source_id` INT NULL AFTER `source_organization`;
  END IF;
END$$
DELIMITER ;
CALL `AddImagesPhase2Columns`();
DROP PROCEDURE IF EXISTS `AddImagesPhase2Columns`;
