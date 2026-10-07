<?php
/**
 * ORBITAL ARCHIVE - Phase 6 Safe Relic Taxonomy & Encoding Migration
 * Applies verified research-backed fate, location, and status distinctions
 * Adheres strictly to Phase 6 safety rules
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

echo "====================================================================\n";
echo "ORBITAL ARCHIVE — PHASE 6 RELIC TAXONOMY & ENCODING MIGRATION\n";
echo "====================================================================\n\n";

$relicUpdates = [
    1 => [
        'name' => 'Lunar Module Eagle',
        'current_status' => 'COMPLETED',
        'primary_region' => 'Mare Tranquillitatis, Moon',
        'current_location' => 'Tranquility Base, Mare Tranquillitatis, Moon (0.67408° N, 23.47297° E)',
        'fate' => 'Descent stage remains preserved at Tranquility Base following July 1969 lunar liftoff; ascent stage was intentionally jettisoned into lunar orbit.',
        'last_verified' => '1969-07-24'
    ],
    4 => [
        'name' => 'Voyager 2 Spacecraft',
        'current_status' => 'OPERATIONAL',
        'primary_region' => 'Local Interstellar Medium (Southern Celestial Hemisphere)',
        'current_location' => 'Interstellar Space (~136 AU / 20.3 billion km from the Sun)',
        'fate' => 'In perpetual heliocentric hyperbolic escape trajectory into the interstellar medium (~136 AU from Earth, traversing south of the ecliptic plane). Continues transmitting plasma science data via NASA Deep Space Network; RTG electrical output expected to sustain instruments through ~2026+.',
        'last_verified' => '2026-03-20'
    ],
    5 => [
        'name' => 'Sojourner Rover',
        'current_status' => 'COMPLETED',
        'primary_region' => 'Ares Vallis, Mars',
        'current_location' => 'Carl Sagan Memorial Station, Ares Vallis, Mars (19.33° N, 33.55° W)',
        'fate' => 'Stationary near the Carl Sagan Memorial Station landing platform in Ares Vallis. Successfully completed 83 Sols of robotic exploration and APXS geochemical measurements before communications ceased on 27 September 1997. Preserved on Mars.',
        'last_verified' => '1997-09-27'
    ],
    10 => [
        'name' => 'Ingenuity Helicopter',
        'current_status' => 'COMPLETED',
        'primary_region' => 'Valinor Hills, Jezero Crater, Mars',
        'current_location' => 'Airfield Chi, Valinor Hills, Jezero Crater, Mars (18.44° N, 77.45° E)',
        'fate' => 'Stationary at "Valinor Hills" after sustaining rotor blade damage on Flight 72 on 18 January 2024. Successfully completed 72 flights spanning 17.0 km and 128.8 flight minutes (far exceeding 5-flight tech demo goal). Now functions as a stationary long-term avionics test and surface weather monitoring station.',
        'last_verified' => '2024-01-25'
    ],
    11 => [
        'name' => 'Phoenix Lander',
        'current_status' => 'COMPLETED',
        'primary_region' => 'Green Valley, Vastitas Borealis, Mars',
        'current_location' => 'Green Valley, Vastitas Borealis, Mars (68.2188° N, 234.2508° E)',
        'fate' => 'Concluded 157 Sols of polar regolith investigation in November 2008 following declining solar power during the Martian arctic winter. Confirmed subsurface water ice and detected perchlorates. Solar panels encased by seasonal carbon dioxide ice; preserved on the northern plains.',
        'last_verified' => '2008-11-02'
    ],
    12 => [
        'name' => 'InSight Lander',
        'current_status' => 'COMPLETED',
        'primary_region' => 'Elysium Planitia, Mars',
        'current_location' => 'Elysium Planitia, Mars (4.5024° N, 135.6234° E)',
        'fate' => 'Concluded 1,440 Sols of planetary seismology on 15 December 2022 after extensive dust accumulation on solar arrays starved the lander of electrical power. Detected over 1,300 marsquakes and mapped the crust, mantle, and core of Mars. Preserved in situ at Elysium Planitia.',
        'last_verified' => '2022-12-15'
    ],
    19 => [
        'name' => 'New Horizons Spacecraft',
        'current_status' => 'OPERATIONAL',
        'primary_region' => 'Outer Kuiper Belt & Heliopause Transition Zone',
        'current_location' => 'Outer Kuiper Belt (~58 AU / 8.7 billion km from the Sun)',
        'fate' => 'Traversing the outer Kuiper Belt on a hyperbolic escape trajectory at ~14 km/s. Continues conducting heliospheric, plasma, and astrophysical observations in its Second Extended Mission (KEM2); RTG power projected to support telemetry through the 2030s.',
        'last_verified' => '2026-03-20'
    ],
    27 => [
        'name' => 'Zhurong Rover',
        'current_status' => 'COMPLETED',
        'primary_region' => 'Southern Utopia Planitia, Mars',
        'current_location' => 'Southern Utopia Planitia, Mars (25.10° N, 109.90° E)',
        'fate' => 'Traversed 1,921 meters across Utopia Planitia before entering planned hibernation on 18 May 2022 ahead of Martian winter. Failed to wake up in December 2022 due to heavy dust accumulation on solar arrays and extreme cold. Preserved on Mars.',
        'last_verified' => '2022-05-18'
    ],
    28 => [
        'name' => 'SLIM Lander',
        'current_status' => 'COMPLETED',
        'primary_region' => 'Shioli Crater, Mare Nectaris, Moon',
        'current_location' => 'Shioli Crater rim slope, Moon (13.3160° S, 25.2510° E)',
        'fate' => 'Achieved historic pinpoint landing within 55 meters of target on 19 January 2024. Successfully operated multi-band camera and survived three extreme two-week lunar nights despite resting nose-down. Final telemetry session concluded in April 2024; preserved on the Moon.',
        'last_verified' => '2024-04-29'
    ],
    29 => [
        'name' => 'Surveyor 3 Lander',
        'current_status' => 'COMPLETED',
        'primary_region' => 'Oceanus Procellarum, Moon',
        'current_location' => 'Surveyor Crater, Oceanus Procellarum, Moon (3.0163° S, 23.4180° W)',
        'fate' => 'Soft-landed on 20 April 1967; transmitted 6,315 television frames and conducted soil mechanics tests. Visited on 19 November 1969 by Apollo 12 astronauts (Pete Conrad and Alan Bean), who examined the hardware in situ and returned its television camera and scoop to Earth for materials analysis. Preserved on the Moon.',
        'last_verified' => '1969-11-20'
    ]
];

echo "STEP 1: DISPLAYING PENDING RELIC TAXONOMY UPDATES:\n";
foreach ($relicUpdates as $id => $data) {
    echo "  [ID $id] {$data['name']}\n";
    echo "    STATUS:   {$data['current_status']}\n";
    echo "    REGION:   {$data['primary_region']}\n";
    echo "    LOCATION: {$data['current_location']}\n";
    echo "    VERIFIED: {$data['last_verified']}\n";
    echo "    FATE:     " . substr($data['fate'], 0, 70) . "...\n";
}

echo "\nSTEP 2: EXECUTING SAFE RELIC UPDATES VIA PREPARED STATEMENTS...\n";
$stmt = $pdo->prepare("
    UPDATE equipment 
    SET current_status = :status,
        primary_region = :region,
        current_location = :location,
        fate = :fate,
        last_verified = :verified
    WHERE id = :id
");

foreach ($relicUpdates as $id => $d) {
    $stmt->execute([
        'status'   => $d['current_status'],
        'region'   => $d['primary_region'],
        'location' => $d['current_location'],
        'fate'     => $d['fate'],
        'verified' => $d['last_verified'],
        'id'       => $id
    ]);
    echo "  -> Updated ID $id ({$d['name']})\n";
}

echo "\nSTEP 3: CLEANING DEGREE SYMBOL ENCODING ACROSS ENTIRE CATALOG...\n";
// Clean all corrupted degree symbols in equipment
$pdo->exec("UPDATE equipment SET current_location = REPLACE(current_location, '┬░', '°') WHERE current_location LIKE '%┬░%'");
$pdo->exec("UPDATE equipment SET primary_region = REPLACE(primary_region, '┬░', '°') WHERE primary_region LIKE '%┬░%'");
$pdo->exec("UPDATE equipment SET fate = REPLACE(fate, '┬░', '°') WHERE fate LIKE '%┬░%'");
$pdo->exec("UPDATE equipment SET dimensions = REPLACE(dimensions, '┬░', '°') WHERE dimensions LIKE '%┬░%'");

$remainingCorrupted = $pdo->query("SELECT COUNT(*) FROM equipment WHERE current_location LIKE '%┬░%' OR primary_region LIKE '%┬░%' OR fate LIKE '%┬░%'")->fetchColumn();
echo "  -> Remaining corrupted degree symbols: $remainingCorrupted\n";

echo "\nSTEP 4: POST-MIGRATION INTEGRITY CHECKS...\n";
$dupeSlugs = $pdo->query("SELECT slug, COUNT(*) c FROM equipment GROUP BY slug HAVING c > 1")->fetchAll();
echo "  -> Duplicate slugs: " . count($dupeSlugs) . "\n";

$orphans = $pdo->query("SELECT COUNT(*) FROM equipment e LEFT JOIN missions m ON e.mission_id = m.id WHERE m.id IS NULL")->fetchColumn();
echo "  -> Orphan equipment: $orphans\n";

echo "\nSUCCESS: Phase 6 relic taxonomy & encoding migration complete.\n";
