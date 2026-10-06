-- ==============================================================
-- ORBITAL ARCHIVE - MySQL Database Schema
-- Database: orbital_archive
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `orbital_archive` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `orbital_archive`;

-- 1. AGENCIES
CREATE TABLE IF NOT EXISTS `agencies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `short_name` VARCHAR(50) NOT NULL,
  `country` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) DEFAULT 'National', -- 'National', 'International', 'Commercial'
  `description` TEXT,
  `logo_url` VARCHAR(500),
  `website_url` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. DESTINATIONS
CREATE TABLE IF NOT EXISTS `destinations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `type` VARCHAR(100), -- 'Earth Orbit', 'Lunar Surface', 'Mars', 'Venus', 'Outer Solar System', 'Deep Space'
  `description` TEXT,
  `distance_from_earth` VARCHAR(100),
  `mission_count` INT DEFAULT 0,
  `image_url` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. MISSIONS
CREATE TABLE IF NOT EXISTS `missions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `official_name` VARCHAR(255),
  `agency_id` INT,
  `launch_date` DATE,
  `launch_vehicle` VARCHAR(100),
  `launch_location` VARCHAR(255),
  `launch_lat` DECIMAL(9,6) DEFAULT NULL,
  `launch_lng` DECIMAL(9,6) DEFAULT NULL,
  `arrival_date` DATE,
  `destination_id` INT,
  `objective` TEXT,
  `status` VARCHAR(50) DEFAULT 'Active', -- 'Active', 'Completed', 'Lost', 'Historic'
  `trajectory_type` VARCHAR(50) DEFAULT 'Robotic probe', -- 'Crewed', 'Robotic probe', 'Satellite'
  `mission_end_date` DATE DEFAULT NULL,
  `end_reason` VARCHAR(255) DEFAULT NULL,
  `description` TEXT,
  `legacy` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`agency_id`) REFERENCES `agencies`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`destination_id`) REFERENCES `destinations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. EQUIPMENT
CREATE TABLE IF NOT EXISTS `equipment` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mission_id` INT,
  `name` VARCHAR(255) NOT NULL,
  `official_name` VARCHAR(255),
  `type` VARCHAR(100), -- 'Rover', 'Lander', 'Orbiter', 'Flyby Probe', 'Space Station', 'Space Telescope', 'Capsule'
  `purpose` TEXT,
  `technology` TEXT,
  `instruments` TEXT,
  `communication` TEXT,
  `operational_period` VARCHAR(100),
  `current_status` VARCHAR(100), -- 'Operational', 'Decommissioned', 'Abandoned', 'Lost', 'Returned to Earth', 'Destroyed'
  `current_location` VARCHAR(255),
  `is_relic` TINYINT(1) DEFAULT 0,
  `relic_category` VARCHAR(100) DEFAULT NULL,
  `description` TEXT,
  `discoveries` TEXT,
  `legacy` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`mission_id`) REFERENCES `missions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. IMAGES
CREATE TABLE IF NOT EXISTS `images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `equipment_id` INT NULL,
  `mission_id` INT NULL,
  `image_url` VARCHAR(1000) NOT NULL,
  `page_url` VARCHAR(1000),
  `title` VARCHAR(255),
  `description` TEXT,
  `credit` VARCHAR(255),
  `license` VARCHAR(100),
  `source` VARCHAR(255),
  `image_type` VARCHAR(50) DEFAULT 'hero', -- 'hero', 'launch', 'surface', 'operation', 'historical'
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`equipment_id`) REFERENCES `equipment`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`mission_id`) REFERENCES `missions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TIMELINE EVENTS
CREATE TABLE IF NOT EXISTS `timeline_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `year` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `mission_id` INT NULL,
  `equipment_id` INT NULL,
  `description` TEXT,
  `image_url` VARCHAR(1000),
  `importance` VARCHAR(50) DEFAULT 'High',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`mission_id`) REFERENCES `missions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`equipment_id`) REFERENCES `equipment`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. SOURCES
CREATE TABLE IF NOT EXISTS `sources` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `mission_id` INT NULL,
  `equipment_id` INT NULL,
  `source_name` VARCHAR(255) NOT NULL,
  `source_url` VARCHAR(1000) NOT NULL,
  `source_type` VARCHAR(50) DEFAULT 'official', -- 'official', 'scientific', 'secondary', 'image'
  `description` TEXT,
  `verified_date` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`mission_id`) REFERENCES `missions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`equipment_id`) REFERENCES `equipment`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. USERS (Admin Authentication)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) UNIQUE NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) DEFAULT 'admin',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
