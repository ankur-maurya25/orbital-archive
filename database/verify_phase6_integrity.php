<?php
/**
 * ORBITAL ARCHIVE - Phase 6 Relational Data Integrity Audit
 * Performs deep semantic, relational, and archival integrity checks across MySQL database
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

$report = [
    'checks' => [],
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0
];

function recordCheck($id, $rule, $status, $object = 'DATABASE', $problem = '', $severity = 'LOW') {
    global $report;
    if ($status === 'PASS') {
        $report['passed']++;
    } elseif ($status === 'FAIL') {
        $report['failed']++;
    } else {
        $report['warnings']++;
    }
    $report['checks'][] = [
        'id' => $id,
        'rule' => $rule,
        'status' => $status,
        'object' => $object,
        'problem' => $problem,
        'severity' => $severity
    ];
}

echo "====================================================================\n";
echo "ORBITAL ARCHIVE — PHASE 6 DATA INTEGRITY & RELATION AUDIT\n";
echo "====================================================================\n\n";

// Rule 1: Every equipment record has a valid mission
$stmt = $pdo->query("SELECT e.id, e.name FROM equipment e LEFT JOIN missions m ON e.mission_id = m.id WHERE m.id IS NULL");
$orphans = $stmt->fetchAll();
if (empty($orphans)) {
    recordCheck(1, "Equipment-Mission Integrity", "PASS", "equipment", "All equipment map to valid missions");
} else {
    foreach ($orphans as $o) {
        recordCheck(1, "Equipment-Mission Integrity", "FAIL", "equipment ID {$o['id']}", "Missing mission reference", "HIGH");
    }
}

// Rule 2: Every equipment record has a valid agency (via mission or direct)
$stmt = $pdo->query("SELECT e.id, e.name, m.agency_id, a.id as a_id FROM equipment e JOIN missions m ON e.mission_id = m.id LEFT JOIN agencies a ON m.agency_id = a.id WHERE a.id IS NULL");
$missingAgencies = $stmt->fetchAll();
if (empty($missingAgencies)) {
    recordCheck(2, "Equipment-Agency Integrity", "PASS", "equipment", "All equipment map to valid agencies");
} else {
    foreach ($missingAgencies as $m) {
        recordCheck(2, "Equipment-Agency Integrity", "FAIL", "equipment ID {$m['id']}", "Mission agency reference missing", "HIGH");
    }
}

// Rule 3: Every equipment record has a valid destination
$stmt = $pdo->query("SELECT e.id, e.name, m.destination_id, d.id as d_id FROM equipment e JOIN missions m ON e.mission_id = m.id LEFT JOIN destinations d ON m.destination_id = d.id WHERE d.id IS NULL");
$missingDests = $stmt->fetchAll();
if (empty($missingDests)) {
    recordCheck(3, "Equipment-Destination Integrity", "PASS", "equipment", "All equipment map to valid planetary destinations");
} else {
    foreach ($missingDests as $d) {
        recordCheck(3, "Equipment-Destination Integrity", "FAIL", "equipment ID {$d['id']}", "Destination reference missing", "HIGH");
    }
}

// Rule 4: No orphan instruments (instruments not mapped to any equipment)
$stmt = $pdo->query("SELECT i.id, i.name FROM instruments i LEFT JOIN equipment_instruments ei ON i.id = ei.instrument_id WHERE ei.instrument_id IS NULL");
$orphanInsts = $stmt->fetchAll();
if (empty($orphanInsts)) {
    recordCheck(4, "Instrument Mapping Integrity", "PASS", "instruments", "All instruments map to hardware");
} else {
    foreach ($orphanInsts as $oi) {
        recordCheck(4, "Instrument Mapping Integrity", "WARN", "instrument ID {$oi['id']} ({$oi['name']})", "Instrument unattached to equipment", "MEDIUM");
    }
}

// Rule 5: No orphan equipment_instruments relations
$stmt = $pdo->query("
    SELECT ei.* FROM equipment_instruments ei 
    LEFT JOIN equipment e ON ei.equipment_id = e.id 
    LEFT JOIN instruments i ON ei.instrument_id = i.id 
    WHERE e.id IS NULL OR i.id IS NULL
");
$brokenJunctions = $stmt->fetchAll();
if (empty($brokenJunctions)) {
    recordCheck(5, "Equipment-Instrument Junction Integrity", "PASS", "equipment_instruments", "Zero orphan junction foreign keys");
} else {
    recordCheck(5, "Equipment-Instrument Junction Integrity", "FAIL", "equipment_instruments", count($brokenJunctions) . " broken junction relations", "HIGH");
}

// Rule 6: No broken source relationships
$stmt = $pdo->query("
    SELECT s.id, s.source_name FROM sources s 
    LEFT JOIN missions m ON s.mission_id = m.id 
    LEFT JOIN equipment e ON s.equipment_id = e.id 
    WHERE (s.mission_id IS NOT NULL AND m.id IS NULL) OR (s.equipment_id IS NOT NULL AND e.id IS NULL)
");
$brokenSources = $stmt->fetchAll();
if (empty($brokenSources)) {
    recordCheck(6, "Source Foreign Key Integrity", "PASS", "sources", "All sources reference valid missions/equipment");
} else {
    recordCheck(6, "Source Foreign Key Integrity", "FAIL", "sources", count($brokenSources) . " broken source relations", "HIGH");
}

// Rule 7: No broken image relationships
$stmt = $pdo->query("
    SELECT img.id, img.title FROM images img 
    LEFT JOIN missions m ON img.mission_id = m.id 
    LEFT JOIN equipment e ON img.equipment_id = e.id 
    WHERE (img.mission_id IS NOT NULL AND m.id IS NULL) OR (img.equipment_id IS NOT NULL AND e.id IS NULL)
");
$brokenImages = $stmt->fetchAll();
if (empty($brokenImages)) {
    recordCheck(7, "Image Foreign Key Integrity", "PASS", "images", "All images reference valid missions/equipment");
} else {
    recordCheck(7, "Image Foreign Key Integrity", "FAIL", "images", count($brokenImages) . " broken image relations", "HIGH");
}

// Rule 8: No broken timeline relationships
$stmt = $pdo->query("
    SELECT t.id, t.title FROM timeline_events t 
    LEFT JOIN missions m ON t.mission_id = m.id 
    LEFT JOIN equipment e ON t.equipment_id = e.id 
    WHERE (t.mission_id IS NOT NULL AND m.id IS NULL) OR (t.equipment_id IS NOT NULL AND e.id IS NULL)
");
$brokenTimeline = $stmt->fetchAll();
if (empty($brokenTimeline)) {
    recordCheck(8, "Timeline Foreign Key Integrity", "PASS", "timeline_events", "All milestones reference valid missions/equipment");
} else {
    recordCheck(8, "Timeline Foreign Key Integrity", "FAIL", "timeline_events", count($brokenTimeline) . " broken timeline relations", "HIGH");
}

// Flagship Objects List (11 Flagships)
$flagships = [
    'perseverance' => 'Perseverance Rover',
    'curiosity' => 'Curiosity Rover',
    'opportunity' => 'Opportunity Rover',
    'spirit' => 'Spirit Rover',
    'apollo-15-lrv' => 'Apollo 15 Lunar Roving Vehicle',
    'voyager-1' => 'Voyager 1 Spacecraft',
    'chandrayaan-3-vikram' => 'Chandrayaan-3 Vikram Lander',
    'chandrayaan-3-pragyan' => 'Chandrayaan-3 Pragyan Rover',
    'hayabusa2' => 'Hayabusa2 Spacecraft',
    'rosetta' => 'Rosetta Spacecraft',
    'philae' => 'Philae Lander'
];

// Rule 9: Every flagship has at least one source
foreach ($flagships as $slug => $name) {
    $eq = $pdo->query("SELECT id, mission_id FROM equipment WHERE slug = '$slug'")->fetch();
    if ($eq) {
        $count = $pdo->query("SELECT COUNT(*) FROM sources WHERE equipment_id = {$eq['id']} OR mission_id = {$eq['mission_id']}")->fetchColumn();
        if ($count > 0) {
            recordCheck(9, "Flagship Source Coverage", "PASS", "$name ($slug)", "$count verified sources linked");
        } else {
            recordCheck(9, "Flagship Source Coverage", "FAIL", "$name ($slug)", "Zero verified sources linked", "HIGH");
        }
    } else {
        recordCheck(9, "Flagship Source Coverage", "FAIL", "$name ($slug)", "Record missing from database", "CRITICAL");
    }
}

// Rule 10: Every flagship has at least one image
foreach ($flagships as $slug => $name) {
    $eq = $pdo->query("SELECT id, mission_id FROM equipment WHERE slug = '$slug'")->fetch();
    if ($eq) {
        $count = $pdo->query("SELECT COUNT(*) FROM images WHERE equipment_id = {$eq['id']} OR (mission_id = {$eq['mission_id']} AND equipment_id IS NULL)")->fetchColumn();
        if ($count > 0) {
            recordCheck(10, "Flagship Image Coverage", "PASS", "$name ($slug)", "$count documentary frames linked");
        } else {
            recordCheck(10, "Flagship Image Coverage", "FAIL", "$name ($slug)", "Zero images linked", "HIGH");
        }
    }
}

// Rule 11: No duplicate semantic slugs
$stmt = $pdo->query("SELECT slug, COUNT(*) as c FROM equipment WHERE slug IS NOT NULL AND slug != '' GROUP BY slug HAVING c > 1");
$dupeSlugs = $stmt->fetchAll();
if (empty($dupeSlugs)) {
    recordCheck(11, "Slug Uniqueness", "PASS", "equipment.slug", "All equipment slugs are unique");
} else {
    foreach ($dupeSlugs as $ds) {
        recordCheck(11, "Slug Uniqueness", "FAIL", "slug '{$ds['slug']}'", "Duplicate slug detected ({$ds['c']} instances)", "HIGH");
    }
}

// Rule 12: No impossible status/fate combinations & Relic taxonomy check
$stmt = $pdo->query("SELECT id, slug, name, current_status, fate, is_relic FROM equipment");
$allEq = $stmt->fetchAll();
$inconsistentStatus = [];
foreach ($allEq as $item) {
    $st = strtoupper($item['current_status'] ?? '');
    $fate = trim($item['fate'] ?? '');
    // If it's a relic and status is active, it must be Voyager 1 or New Horizons or similar operational relic
    if ($item['is_relic'] && ($st === 'OPERATIONAL' || $st === 'ACTIVE')) {
        if (!in_array($item['slug'], ['voyager-1', 'voyager-2', 'new-horizons'])) {
            $inconsistentStatus[] = "Relic '{$item['name']}' has active status but is not a deep space probe";
        }
    }
    // Check if degree symbol encoding is corrupted
    if (strpos($item['fate'] ?? '', '┬░') !== false || strpos($item['current_location'] ?? '', '┬░') !== false) {
        $inconsistentStatus[] = "Equipment '{$item['name']}' has corrupted degree symbol encoding ('┬░')";
    }
}
if (empty($inconsistentStatus)) {
    recordCheck(12, "Status & Fate Taxonomy Consistency", "PASS", "equipment", "Relic and operational states are consistent");
} else {
    foreach ($inconsistentStatus as $is) {
        recordCheck(12, "Status & Fate Taxonomy Consistency", "FAIL", "equipment", $is, "MEDIUM");
    }
}

// Rule 13: No blank public-facing names
$stmt = $pdo->query("SELECT id FROM equipment WHERE name IS NULL OR trim(name) = ''");
$blankEq = $stmt->fetchAll();
$stmtM = $pdo->query("SELECT id FROM missions WHERE name IS NULL OR trim(name) = ''");
$blankM = $stmtM->fetchAll();
if (empty($blankEq) && empty($blankM)) {
    recordCheck(13, "Public Identity Fields", "PASS", "equipment & missions", "Zero blank names");
} else {
    recordCheck(13, "Public Identity Fields", "FAIL", "database", "Blank names detected", "HIGH");
}

// Rule 14: No invalid dates (e.g. 0000-00-00 or NULL)
$stmt = $pdo->query("SELECT id, name, launch_date FROM missions WHERE launch_date IS NULL OR CAST(launch_date AS CHAR) = '0000-00-00'");
$badDates = $stmt->fetchAll();
if (empty($badDates)) {
    recordCheck(14, "Mission Launch Date Validity", "PASS", "missions.launch_date", "All launch dates valid");
} else {
    recordCheck(14, "Mission Launch Date Validity", "FAIL", "missions", count($badDates) . " invalid launch dates", "HIGH");
}

// Rule 15: No future launch dates
$today = date('Y-m-d');
$stmt = $pdo->query("SELECT id, name, launch_date FROM missions WHERE launch_date > '$today'");
$futureMissions = $stmt->fetchAll();
if (empty($futureMissions)) {
    recordCheck(15, "Historical Launch Date Consistency", "PASS", "missions.launch_date", "No future launch dates in historical archive");
} else {
    foreach ($futureMissions as $fm) {
        recordCheck(15, "Historical Launch Date Consistency", "FAIL", "mission '{$fm['name']}'", "Future launch date: {$fm['launch_date']}", "HIGH");
    }
}

// Rule 16: No accidental "real-time" telemetry language in database text
$forbiddenPhrases = ['real-time position', 'live telemetry', 'real-time coordinates'];
$foundRealTime = [];
foreach ($forbiddenPhrases as $fp) {
    $res = $pdo->query("SELECT id, name FROM equipment WHERE description LIKE '%$fp%' OR current_location LIKE '%$fp%' OR fate LIKE '%$fp%'")->fetchAll();
    if (!empty($res)) {
        foreach ($res as $r) {
            $foundRealTime[] = "Equipment {$r['name']} contains '$fp'";
        }
    }
}
if (empty($foundRealTime)) {
    recordCheck(16, "Telemetry Language Guardrail", "PASS", "equipment", "Zero occurrences of 'real-time' claims in records");
} else {
    foreach ($foundRealTime as $fr) {
        recordCheck(16, "Telemetry Language Guardrail", "FAIL", "equipment", $fr, "HIGH");
    }
}

// Rule 17: No prohibited Cheyava Falls terminology in database records
$prohibitedCheyava = ['life discovered', 'evidence of life', 'confirmed biosignature'];
$foundCheyavaBad = [];
foreach ($prohibitedCheyava as $pc) {
    $res = $pdo->query("SELECT id, name FROM equipment WHERE discoveries LIKE '%$pc%' OR description LIKE '%$pc%'")->fetchAll();
    if (!empty($res)) {
        foreach ($res as $r) {
            $foundCheyavaBad[] = "Record {$r['name']} contains '$pc'";
        }
    }
}
if (empty($foundCheyavaBad)) {
    recordCheck(17, "Cheyava Astrobiology Guardrail", "PASS", "equipment.discoveries", "Strict candidate wording preserved");
} else {
    foreach ($foundCheyavaBad as $fb) {
        recordCheck(17, "Cheyava Astrobiology Guardrail", "FAIL", "equipment", $fb, "CRITICAL");
    }
}

// Rule 18: No empty exhibition sections caused by missing relational data on flagships
$emptySections = [];
foreach ($flagships as $slug => $name) {
    $eq = $pdo->query("SELECT e.*, m.name as m_name FROM equipment e JOIN missions m ON e.mission_id = m.id WHERE e.slug = '$slug'")->fetch();
    if (!$eq) continue;
    $eqId = $eq['id'];
    $mId = $eq['mission_id'];
    $instCount = $pdo->query("SELECT COUNT(*) FROM equipment_instruments WHERE equipment_id = $eqId")->fetchColumn();
    $timelineCount = $pdo->query("SELECT COUNT(*) FROM timeline_events WHERE equipment_id = $eqId OR mission_id = $mId")->fetchColumn();
    if ($instCount == 0 && !in_array($slug, ['apollo-15-lrv'])) { // Apollo 15 LRV carried surface deployment gear
        $emptySections[] = "$name has 0 instruments";
    }
    if ($timelineCount == 0) {
        $emptySections[] = "$name has 0 timeline milestones";
    }
}
if (empty($emptySections)) {
    recordCheck(18, "Flagship Exhibition Completeness", "PASS", "11 Flagships", "All flagships have complete relational datasets");
} else {
    foreach ($emptySections as $es) {
        recordCheck(18, "Flagship Exhibition Completeness", "FAIL", "flagships", $es, "HIGH");
    }
}

// ------------------------------------------------------------------
// REPORT OUTPUT
// ------------------------------------------------------------------
foreach ($report['checks'] as $c) {
    $statusTag = $c['status'] === 'PASS' ? '[PASS]' : ($c['status'] === 'WARN' ? '[WARN]' : '[FAIL]');
    echo sprintf("  %-6s | Rule %-2d: %-38s | %-24s | %s\n", $statusTag, $c['id'], $c['rule'], substr($c['object'], 0, 24), $c['problem']);
}

echo "\n====================================================================\n";
echo "INTEGRITY AUDIT SUMMARY:\n";
echo "  Total Checks: " . count($report['checks']) . "\n";
echo "  PASSED:       {$report['passed']}\n";
echo "  WARNINGS:     {$report['warnings']}\n";
echo "  FAILED:       {$report['failed']}\n";
echo "====================================================================\n";

if ($report['failed'] === 0) {
    echo "AUDIT RESULT: DATA INTEGRITY CHECKS PASSED!\n";
    exit(0);
} else {
    echo "AUDIT RESULT: " . $report['failed'] . " INTEGRITY CHECKS FAILED. SEE DETAILS ABOVE.\n";
    exit(1);
}
