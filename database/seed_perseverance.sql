-- ==============================================================
-- ORBITAL ARCHIVE - Phase 2 Seed Data: Verified Instruments & Perseverance Record
-- Backed by official NASA JPL, NASA Science, and peer-reviewed mission archives.
-- ==============================================================

USE `orbital_archive`;

-- 1. Insert Normalized Instruments for Perseverance & Mars 2020
INSERT INTO `instruments` (`id`, `name`, `official_name`, `type`, `purpose`, `description`, `specifications`, `manufacturer`, `agency_id`) VALUES
(1, 'Mastcam-Z', 'Mastcam-Z Mast-Mounted Multispectral Stereoscopic Imaging System', 'Multispectral Stereoscopic Camera',
 'Principal science cameras providing 3D stereoscopic imaging, multispectral zoom photography, and panoramic terrain characterization.',
 'Dual zoom cameras mounted on the rover remote sensing mast separated by 24.2 cm. Zooms from 26 mm to 110 mm with focus from 1 meter to infinity, detecting 11 science color bands.',
 'Resolution: 1600×1200 pixels; Focal length: 26–110 mm (4× optical zoom); Mass: 4.0 kg; Power: 17.4 W; Color bands: 11 multispectral filters (400–1000 nm).',
 'Malin Space Science Systems / Arizona State University', 1),

(2, 'SuperCam', 'SuperCam Laser-Induced Breakdown Spectroscopy, Raman, and Luminescence Suite', 'Remote Micro-Imager & Laser Spectrometer',
 'Remote chemical, mineral, and atomic composition analysis of rocks and soils from up to 7 meters away.',
 'Fires an infrared laser to vaporize microscopic rock points into glowing plasma (LIBS), analyzing emitted light. Features a green laser for remote Raman spectroscopy, a microphone to record laser acoustic shockwaves and Martian wind sounds, and an infrared spectrometer.',
 'Range: 7 meters (LIBS), infinite (passive VISIR); Wavelengths: 1064 nm (pulsed IR) & 532 nm (green laser); Microphone: electret condenser (100 Hz–10 kHz); Mass: 10.6 kg; Power: 17.9 W.',
 'Los Alamos National Laboratory (USA) / IRAP / CNES (France)', 1),

(3, 'PIXL', 'Planetary Instrument for X-ray Lithochemistry', 'Micro-Focus X-Ray Fluorescence Spectrometer',
 'Fine-scale elemental chemical composition mapping of abraded rock surfaces and salt crusts.',
 'Mounted on the robotic arm turret. Directs an ultra-fine 120-micrometer X-ray beam at target rocks to detect and quantify 20+ chemical elements from sodium through uranium, matching elemental chemistry directly with sub-millimeter rock textures.',
 'Beam spot: 0.12 mm diameter; Energy range: 0.9 to 28 keV; Spatial step size: down to 50 micrometers; Mass: 4.3 kg (turret sensor head) + 1.6 kg (chassis electronics); Power: ~25 W.',
 'NASA Jet Propulsion Laboratory (JPL)', 1),

(4, 'SHERLOC', 'Scanning Habitable Environments with Raman & Luminescence for Organics & Chemicals', 'Deep-UV Raman & Fluorescence Spectrometer',
 'Detection and spatial mapping of organic carbon molecules and minerals altered by aqueous environments.',
 'Mounted on the arm turret, uses a deep-ultraviolet (248.6 nm) laser to excite native fluorescence and resonance Raman scattering from organic compounds without destroying the sample. Paired with WATSON (Wide Angle Topographic Sensor for Operations and eNgineering) camera for micro-scale imaging.',
 'Laser wavelength: 248.6 nm pulsed DUV; Spatial resolution: 50-micrometer laser footprint; Context imaging: WATSON 10-micron scale; Mass: 3.11 kg (turret head) + 1.61 kg (electronics); Power: 48.8 W.',
 'NASA Jet Propulsion Laboratory (JPL)', 1),

(5, 'MEDA', 'Mars Environmental Dynamics Analyzer', 'Meteorological & Atmospheric Sensor Suite',
 'Continuous measurement of weather, ambient temperature, pressure, wind velocity, relative humidity, solar radiation, and airborne dust properties.',
 'A distributed environmental sensor station across the rover mast, deck, and interior. Records diurnal atmospheric cycles, characterization of airborne dust particle shapes, optical depth, and ground thermal heat balance.',
 'Sensors: 5 air temperature sensors, 2 wind sensors, relative humidity sensor, thermal infrared ground sensor, barometer, radiation and dust sensor (RDS); Mass: 5.5 kg; Power: 17 W.',
 'Centro de Astrobiología (CAB) / INTA-CSIC (Spain)', 1),

(6, 'RIMFAX', 'Radar Imager for Mars\' Subsurface Experiment', 'Ground-Penetrating Radar',
 'Centimeter-scale structural imaging of geologic layers, buried crater rims, and structural strata beneath the rover up to 10+ meters deep.',
 'Ultra-wideband frequency-modulated ground-penetrating radar with an antenna mounted on the rover\'s lower rear exterior. Transmits electromagnetic pulses into the Martian regolith as the rover drives, providing continuous vertical cross-sections of subsurface strata.',
 'Frequency range: 150 to 1200 MHz; Penetration depth: >10 meters in dry basalt; Vertical resolution: 15–30 cm; Mass: ~3 kg; Power: 5–10 W.',
 'Forsvarets forskningsinstitutt (FFI / Norwegian Defence Research Establishment, Norway)', 1),

(7, 'MOXIE', 'Mars Oxygen In-Situ Resource Utilization Experiment', 'Atmospheric Chemical In-Situ Extraction Reactor',
 'Technology demonstration of extracting breathable molecular oxygen (O2) from the carbon dioxide (CO2) atmosphere of Mars.',
 'Draws in thin Martian atmospheric air, compresses it to 1 atm, and heats it to ~800°C within a Solid Oxide Electrolyzer Cell (SOEC) to split CO2 into oxygen and carbon monoxide. Completed 16 successful runs producing 122 grams of oxygen before planned mission conclusion in 2023.',
 'Production rate: 6–12 grams O2 per hour (98% purity); Operating temperature: ~800°C; Mass: 17.1 kg; Power: ~300 W.',
 'Massachusetts Institute of Technology (MIT) / NASA JPL', 1)
ON DUPLICATE KEY UPDATE 
  `name`=VALUES(`name`), `official_name`=VALUES(`official_name`), `type`=VALUES(`type`), 
  `purpose`=VALUES(`purpose`), `description`=VALUES(`description`), `specifications`=VALUES(`specifications`), 
  `manufacturer`=VALUES(`manufacturer`), `agency_id`=VALUES(`agency_id`);

-- 2. Link Instruments to Perseverance (equipment_id = 9)
INSERT IGNORE INTO `equipment_instruments` (`equipment_id`, `instrument_id`) VALUES
(9, 1),
(9, 2),
(9, 3),
(9, 4),
(9, 5),
(9, 6),
(9, 7);

-- 3. Update Perseverance Equipment Record (ID = 9) with Research-Backed Details
UPDATE `equipment` SET
  `slug` = 'perseverance',
  `name` = 'Perseverance',
  `official_name` = 'Mars 2020 Perseverance Rover',
  `type` = 'Mars Rover',
  `mass` = '1,025 kg (2,260 lb)',
  `dimensions` = '3.0 m × 2.7 m × 2.2 m (10 ft × 9 ft × 7 ft)',
  `power` = 'Multi-Mission Radioisotope Thermoelectric Generator (MMRTG) using plutonium-238 (~110W electrical power at landing, decaying ~few percent per year)',
  `mobility` = '6-wheel rocker-bogie mobility system; 52.5 cm machined aluminum wheels with 48 curved titanium cleats; top surface speed 152 meters per hour (0.09 mph)',
  `robotic_arm` = '2.1-meter (7-foot) 5-jointed articulated arm carrying a 45 kg turret with PIXL, SHERLOC, WATSON, rotary-percussive coring drill, and ground-contact sensor',
  `autonomy` = 'AutoNav vision computing allowing autonomous path selection without waiting for Earth ground-in-the-loop; Terrain-Relative Navigation (TRN) for pinpoint entry, descent, and landing',
  `sample_caching` = 'Adaptive Caching Assembly (ACA) located in rover belly; 43 ultra-clean titanium sample tubes with hermetic sealing mechanism for future Mars Sample Return',
  `communication` = 'X-band high-gain steerable dish antenna direct to Earth (DSN); ultra-high frequency (UHF) antenna transmitting at ~2 Mbps relay to MRO, MAVEN, and ESA TGO orbiters',
  `operational_period` = 'July 30, 2020 – Present (Active on Mars since Feb 18, 2021)',
  `journey_days` = 203,
  `current_status` = 'Operational',
  `current_location` = 'Western Jezero Crater rim / Lac de Charmes area (18.44° N, 77.45° E)',
  `primary_region` = 'Jezero Crater, Mars (Neretva Vallis paleoriverbed, Bright Angel, Western Rim)',
  `mission_phase` = 'Extended Mission (Jezero Crater Rim Campaign)',
  `purpose` = 'Investigate ancient habitability, seek potential biosignature candidates in sedimentary rocks, seal cached geological cores, and test technologies for human exploration.',
  `technology` = 'Terrain-Relative Navigation (TRN), Sky Crane landing system, Multi-Mission Radioisotope Thermoelectric Generator (MMRTG), AutoNav self-driving coprocessor, MEDLI2 heat shield sensor instrumentation.',
  `description` = 'A car-sized robotic science rover operating inside Jezero Crater on Mars. Developed by NASA\'s Jet Propulsion Laboratory, Perseverance is tasked with investigating astrobiologically relevant environments, characterizing geological processes, testing in-situ resource utilization, and caching core samples for the joint NASA-ESA Mars Sample Return campaign.',
  `discoveries` = 'Documented ancient deltaic conglomerates and lacustrine mudstones deposited by water 3.5+ billion years ago in Jezero Crater. Confirmed igneous intrusive floor units altered by water. Successfully generated 122 grams of breathable oxygen with MOXIE. In July 2024 at Cheyava Falls in Neretva Vallis, identified organic molecules and millimeter-sized reaction rings (\'leopard spots\') containing iron and phosphate—classified as a potential biosignature candidate. Biological origin remains unconfirmed; further laboratory analysis of returned samples would be required to determine whether the observed features have a biological origin.',
  `legacy` = 'Pioneered the first off-world aerial flight (Ingenuity helicopter), the first in-situ planetary oxygen production (MOXIE), and the assembly of the first cached planetary core library. Demonstrates autonomous planetary driving and lays foundational engineering ground for future human expeditions to Mars.',
  `verification_status` = 'CONFIRMED',
  `last_verified` = '2026-03-15'
WHERE `id` = 9;

-- 4. Update slugs on other key equipment
UPDATE `equipment` SET `slug` = 'apollo-11-eagle' WHERE `id` = 1 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'apollo-15-lrv' WHERE `id` = 2 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'voyager-1' WHERE `id` = 3 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'voyager-2' WHERE `id` = 4 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'sojourner' WHERE `id` = 5 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'spirit' WHERE `id` = 6 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'opportunity' WHERE `id` = 7 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'curiosity' WHERE `id` = 8 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'ingenuity' WHERE `id` = 10 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'phoenix' WHERE `id` = 11 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'insight' WHERE `id` = 12 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'chandrayaan-1' WHERE `id` = 13 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'chandrayaan-3-vikram' WHERE `id` = 14 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'chandrayaan-3-pragyan' WHERE `id` = 15 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'hayabusa2' WHERE `id` = 16 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'rosetta' WHERE `id` = 17 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'philae' WHERE `id` = 18 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'new-horizons' WHERE `id` = 19 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'cassini' WHERE `id` = 20 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'hubble' WHERE `id` = 21 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'james-webb' WHERE `id` = 22 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'sputnik-1' WHERE `id` = 23 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'aditya-l1' WHERE `id` = 24 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'astrosat' WHERE `id` = 25 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'gaia' WHERE `id` = 26 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'zhurong' WHERE `id` = 27 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'slim' WHERE `id` = 28 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'surveyor-3' WHERE `id` = 29 AND `slug` IS NULL;
UPDATE `equipment` SET `slug` = 'iss' WHERE `id` = 30 AND `slug` IS NULL;

-- 5. Insert Verified Archival Sources for Perseverance
INSERT INTO `sources` (`mission_id`, `equipment_id`, `source_name`, `organization`, `source_url`, `source_type`, `description`, `publication_date`, `verified_date`, `accessed_at`) VALUES
(9, 9, 'NASA Mars 2020 Mission Overview', 'NASA', 'https://mars.nasa.gov/mars2020/', 'official', 'Official NASA mission overview, flight telemetry, and engineering documentation.', '2021-02-18', '2026-03-15', '2026-03-15'),
(9, 9, 'NASA JPL Perseverance Science Instruments', 'NASA Jet Propulsion Laboratory', 'https://mars.nasa.gov/mars2020/spacecraft/instruments/', 'official', 'Comprehensive scientific specifications for Mastcam-Z, SuperCam, PIXL, SHERLOC, MEDA, RIMFAX, and MOXIE.', '2020-07-30', '2026-03-15', '2026-03-15'),
(9, 9, 'NASA Press Release: Cheyava Falls Potential Biosignature Candidate', 'NASA', 'https://www.nasa.gov/news-release/nasas-perseverance-rover-scientists-find-intriguing-mars-rock/', 'scientific', 'Official announcement regarding organic molecules and reaction structures in Neretva Vallis rock. Clarifies potential biosignature candidate status and that biological origin remains unconfirmed.', '2024-07-25', '2026-03-15', '2026-03-15'),
(9, 9, 'NASA JPL Mars 2020 Sample Caching System', 'NASA Jet Propulsion Laboratory', 'https://mars.nasa.gov/mars2020/spacecraft/rover/sample-handling/', 'official', 'Architecture of the Adaptive Caching Assembly and hermetically sealed titanium sample tubes for Mars Sample Return.', '2021-09-06', '2026-03-15', '2026-03-15');

-- 6. Insert Verified Timeline Events for Perseverance
-- Clean old Perseverance timeline items if any
DELETE FROM `timeline_events` WHERE `equipment_id` = 9;

INSERT INTO `timeline_events` (`year`, `event_date`, `title`, `mission_id`, `equipment_id`, `description`, `image_url`, `importance`) VALUES
(2020, '2020-07-30', 'Launch from Cape Canaveral', 9, 9, 
 'Perseverance lifts off aboard a United Launch Alliance Atlas V 541 rocket from Space Launch Complex 41 at Cape Canaveral, Florida, embarking on a 203-day interplanetary cruise to Mars.', 
 'https://images-assets.nasa.gov/image/PIA24040/PIA24040~orig.jpg', 'Crucial'),

(2021, '2021-02-18', 'Landing at Jezero Crater', 9, 9, 
 'Perseverance executes a successful entry, descent, and landing (EDL) using Terrain-Relative Navigation and the sky crane touchdown system, touching down at Octavia E. Butler Landing in Jezero Crater.', 
 'https://images-assets.nasa.gov/image/PIA24426/PIA24426~orig.jpg', 'Crucial'),

(2021, '2021-04-19', 'First Powered Flight by Ingenuity', 9, 9, 
 'The Ingenuity Mars Helicopter, deployed from Perseverance\'s belly, achieves the first powered, controlled aerodynamic flight on another planet, hovering 3 meters above Martian regolith for 39.1 seconds.', 
 'https://images-assets.nasa.gov/image/PIA24657/PIA24657~orig.jpg', 'Crucial'),

(2021, '2021-09-01', 'First Mars Rock Sample Collected', 9, 9, 
 'Perseverance successfully cores and hermetically seals its first rock sample, designated \'Montdenier\', from an igneous rock on the Jezero crater floor into titanium sample tube #266.', 
 'https://images-assets.nasa.gov/image/PIA24836/PIA24836~orig.jpg', 'High'),

(2024, '2024-05-15', 'SHERLOC Instrument Recovery', 9, 9, 
 'NASA JPL engineers successfully regain operational focus of the SHERLOC deep-UV spectrometer on the robotic arm after diagnosing and resolving an internal movable lens cover anomaly.', 
 'https://images-assets.nasa.gov/image/PIA23962/PIA23962~orig.jpg', 'High'),

(2024, '2024-07-18', 'Cheyava Falls Investigation', 9, 9, 
 'At \'Cheyava Falls\' in Neretva Vallis, Perseverance discovers millimeter-sized reaction rings (\'leopard spots\') containing iron and phosphate alongside organic compounds. Classified as a potential biosignature candidate. Biological origin remains unconfirmed; further laboratory analysis of returned samples would be required to determine whether the observed features have a biological origin.', 
 'https://images-assets.nasa.gov/image/PIA26368/PIA26368~orig.jpg', 'Crucial'),

(2024, '2024-08-20', 'Ascent of Western Jezero Crater Rim', 9, 9, 
 'Perseverance begins ascending the steep slopes of Witch Hazel Hill toward the western rim of Jezero Crater, targeting geological units that predate the impact that formed Jezero Crater.', 
 'https://images-assets.nasa.gov/image/PIA26367/PIA26367~orig.jpg', 'High'),

(2025, '2025-01-10', 'Broom Point Investigation', 9, 9, 
 'Perseverance completes high-resolution multispectral analysis of ancient layered bedrock at Broom Point on the upper crater slope, discovering evidence of diverse sedimentary transport mechanisms.', 
 'https://images-assets.nasa.gov/image/PIA26367/PIA26367~orig.jpg', 'High'),

(2025, '2025-02-12', 'First AI-Planned Drive on Mars', 9, 9, 
 'NASA reported the use of vision-capable generative AI to plan rover drives, assisting human mission planners in evaluating terrain hazards and charting multi-sol transit routes across challenging Martian crater slopes.', 
 'https://images-assets.nasa.gov/image/PIA24426/PIA24426~orig.jpg', 'High'),

(2026, '2026-03-01', 'Exploration of Lac de Charmes & Jezero Rim', 9, 9, 
 'Continuing its extended mission, Perseverance conducts scientific reconnaissance around the western Jezero rim and Lac de Charmes area, sampling ancient crustal rocks that may hold records of the earliest Noachian epoch on Mars.', 
 'https://images-assets.nasa.gov/image/PIA24426/PIA24426~orig.jpg', 'Crucial');

-- 7. High-Resolution Official NASA Images for Perseverance
INSERT INTO `images` (`equipment_id`, `mission_id`, `image_url`, `page_url`, `title`, `description`, `credit`, `license`, `source`, `source_organization`, `image_type`) VALUES
(9, 9, 'https://images-assets.nasa.gov/image/PIA24426/PIA24426~orig.jpg', 'https://images.nasa.gov/details/PIA24426', 'Perseverance High-Resolution Self-Portrait at Mont Mercou', 'Full mosaic self-portrait of the Mars 2020 Perseverance rover taken by the WATSON camera at the \'Mont Mercou\' rock outcrop in Jezero Crater.', 'NASA/JPL-Caltech/MSSS', 'Public Domain', 'NASA Image and Video Library', 'NASA/JPL', 'hero'),
(9, 9, 'https://images-assets.nasa.gov/image/PIA24040/PIA24040~orig.jpg', 'https://images.nasa.gov/details/PIA24040', 'Mars 2020 Perseverance Liftoff', 'United Launch Alliance Atlas V 541 rocket launches carrying Perseverance rover from Cape Canaveral Space Force Station SLC-41.', 'NASA / Joel Kowsky', 'Public Domain', 'NASA Image and Video Library', 'NASA', 'launch'),
(9, 9, 'https://images-assets.nasa.gov/image/PIA26368/PIA26368~orig.jpg', 'https://images.nasa.gov/details/PIA26368', 'Cheyava Falls Rock Close-Up with Leopard Spots', 'Close-up image from the SHERLOC WATSON camera of the Cheyava Falls rock showing millimeter-scale white calcium sulfate veins and dark reaction halos with iron and phosphate.', 'NASA/JPL-Caltech/MSSS', 'Public Domain', 'NASA JPL Photojournal', 'NASA/JPL', 'surface'),
(9, 9, 'https://images-assets.nasa.gov/image/PIA24657/PIA24657~orig.jpg', 'https://images.nasa.gov/details/PIA24657', 'Ingenuity Helicopter Operating on Mars', 'Ingenuity helicopter photographed in flight by Mastcam-Z on the Perseverance rover during its aerial campaign.', 'NASA/JPL-Caltech/ASU/MSSS', 'Public Domain', 'NASA JPL Photojournal', 'NASA/JPL', 'operation');
