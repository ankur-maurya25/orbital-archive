<?php
/**
 * ORBITAL ARCHIVE — PHASE 4.1 DATA INTEGRITY AUDITOR
 */
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

$slugs = [
    'perseverance',
    'curiosity',
    'opportunity',
    'spirit',
    'apollo-15-lrv',
    'voyager-1',
    'chandrayaan-3-vikram',
    'chandrayaan-3-pragyan',
    'hayabusa2',
    'rosetta',
    'philae'
];

echo "========================================================================================\n";
echo "1. FLAGSHIP OBJECT OVERVIEW & METRICS\n";
echo "========================================================================================\n";

foreach ($slugs as $slug) {
    $stmt = $pdo->prepare("
        SELECT e.*, 
               m.id AS m_id, m.name AS m_name, m.launch_date, m.arrival_date,
               a.short_name AS agency, d.name AS destination
        FROM equipment e
        JOIN missions m ON e.mission_id = m.id
        LEFT JOIN agencies a ON m.agency_id = a.id
        LEFT JOIN destinations d ON m.destination_id = d.id
        WHERE e.slug = ?
    ");
    $stmt->execute([$slug]);
    $e = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$e) {
        echo "MISSING RECORD: $slug\n";
        continue;
    }

    $id = (int)$e['id'];
    $instCount = (int)$pdo->query("SELECT count(*) FROM equipment_instruments WHERE equipment_id = $id")->fetchColumn();
    $timeCount = (int)$pdo->query("SELECT count(*) FROM timeline_events WHERE equipment_id = $id")->fetchColumn();
    $imgCount  = (int)$pdo->query("SELECT count(*) FROM images WHERE equipment_id = $id")->fetchColumn();
    $srcCount  = (int)$pdo->query("SELECT count(*) FROM sources WHERE equipment_id = $id")->fetchColumn();

    printf(
        "%-22s | ID: %2d | %-5s | %-12s | Relic: %d (%-20s) | Inst: %2d | Time: %2d | Img: %2d | Src: %2d\n",
        $slug, $id, $e['agency'], $e['current_status'], $e['is_relic'], $e['relic_category'] ?? 'None',
        $instCount, $timeCount, $imgCount, $srcCount
    );
}

echo "\n========================================================================================\n";
echo "2. DETAILED BREAKDOWN PER OBJECT\n";
echo "========================================================================================\n";

foreach ($slugs as $slug) {
    $stmt = $pdo->prepare("
        SELECT e.*, m.name AS m_name, m.launch_date, m.arrival_date, a.short_name AS agency, d.name AS destination
        FROM equipment e
        JOIN missions m ON e.mission_id = m.id
        LEFT JOIN agencies a ON m.agency_id = a.id
        LEFT JOIN destinations d ON m.destination_id = d.id
        WHERE e.slug = ?
    ");
    $stmt->execute([$slug]);
    $e = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = (int)$e['id'];

    echo "\n----------------------------------------------------------------------------------------\n";
    echo "OBJECT: {$e['name']} [{$slug}] (ID: {$id})\n";
    echo "Mission: {$e['m_name']} | Agency: {$e['agency']} | Dest: {$e['destination']}\n";
    echo "Launch: " . ($e['launch_date'] ?? 'NULL') . " | Arrival: " . ($e['arrival_date'] ?? 'NULL') . "\n";
    echo "Status: {$e['current_status']} | Relic: {$e['is_relic']} ({$e['relic_category']})\n";
    echo "Location: {$e['current_location']}\n";
    echo "Primary Region: {$e['primary_region']} | Phase: {$e['mission_phase']}\n";
    echo "Fate: " . ($e['fate'] ?? 'NULL') . "\n";
    echo "Verification: {$e['verification_status']} | Last Verified: " . ($e['last_verified'] ?? 'NULL') . "\n";

    // Images
    $images = $pdo->query("SELECT id, title, image_url, image_type, credit, license, source FROM images WHERE equipment_id = $id")->fetchAll(PDO::FETCH_ASSOC);
    echo "Images (" . count($images) . "):\n";
    foreach ($images as $img) {
        echo "  - [#{$img['id']}] [{$img['image_type']}] {$img['title']}\n";
        echo "    URL: {$img['image_url']}\n";
        echo "    Credit: {$img['credit']} | Lic: {$img['license']} | Src: {$img['source']}\n";
    }

    // Sources
    $sources = $pdo->query("SELECT * FROM sources WHERE equipment_id = $id")->fetchAll(PDO::FETCH_ASSOC);
    echo "Sources (" . count($sources) . "):\n";
    foreach ($sources as $s) {
        $sName = $s['source_name'] ?? $s['title'] ?? 'Source';
        $sUrl  = $s['source_url'] ?? $s['url'] ?? '';
        $sOrg  = $s['organization'] ?? 'Unknown Org';
        echo "  - [#{$s['id']}] [{$s['source_type']}] {$sName} ({$sOrg})\n";
        echo "    URL: {$sUrl}\n";
    }

    // Instruments
    $instruments = $pdo->query("
        SELECT i.*
        FROM instruments i
        JOIN equipment_instruments ei ON i.id = ei.instrument_id
        WHERE ei.equipment_id = $id
    ")->fetchAll(PDO::FETCH_ASSOC);
    echo "Instruments (" . count($instruments) . "):\n";
    foreach ($instruments as $inst) {
        $type = $inst['type'] ?? 'Instrument';
        $instOrg = $inst['manufacturer'] ?? ($inst['organization'] ?? 'Unknown');
        $mass = $inst['mass_kg'] ?? 'NULL';
        $pwr = $inst['power_watts'] ?? 'NULL';
        echo "  - [#{$inst['id']}] {$inst['name']} [{$type}] | Lead: {$instOrg}\n";
    }

    // Timeline Events
    $events = $pdo->query("SELECT * FROM timeline_events WHERE equipment_id = $id ORDER BY year ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
    echo "Timeline Events (" . count($events) . "):\n";
    foreach ($events as $ev) {
        $eDate = $ev['event_date'] ?? $ev['year'];
        $eType = $ev['event_type'] ?? ($ev['importance'] ?? 'Event');
        echo "  - [#{$ev['id']}] [{$eDate}] {$ev['title']} ({$eType})\n";
    }
}
