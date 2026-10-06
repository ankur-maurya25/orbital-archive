-- ORBITAL ARCHIVE: Phase 4 Flagship Expansion Migration
-- Adds fate column to equipment and corrects foreign keys

-- 1. Add fate column if not exists
ALTER TABLE equipment ADD COLUMN IF NOT EXISTS fate TEXT AFTER legacy;

-- 2. Correct mission_id foreign keys for mismatched initial seed records
-- Pragyan Rover belongs to Chandrayaan-3 (mission 14)
UPDATE equipment SET mission_id = 14 WHERE id = 15;

-- Hayabusa2 Spacecraft belongs to Hayabusa2 (mission 15)
UPDATE equipment SET mission_id = 15 WHERE id = 16;

-- Rosetta Spacecraft belongs to Rosetta (mission 16)
UPDATE equipment SET mission_id = 16 WHERE id = 17;

-- Philae Lander belongs to Rosetta / Philae (mission 16)
UPDATE equipment SET mission_id = 16 WHERE id = 18;
