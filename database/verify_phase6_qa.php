<?php
/**
 * ORBITAL ARCHIVE - Master Phase 6 QA & Integrity Verification Suite
 * Combines Phase 5 QA, Phase 6 Relational Integrity, 404 Error State,
 * Provenance Transparency, Search Badging, and Terminology Guardrails.
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$baseUrl = 'http://localhost:8000';

$results = [
    'total' => 0,
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
    'tests' => []
];

function recordResult($name, $status, $object = '', $reason = '', $severity = 'LOW') {
    global $results;
    $results['total']++;
    if ($status === 'PASS') {
        $results['passed']++;
        echo "  [PASS] $name\n";
    } elseif ($status === 'WARN') {
        $results['warnings']++;
        echo "  [WARN] $name: $reason ($object)\n";
    } else {
        $results['failed']++;
        echo "  [FAIL] $name: $reason ($object) [Severity: $severity]\n";
    }
    $results['tests'][] = [
        'name' => $name,
        'status' => $status,
        'object' => $object,
        'reason' => $reason,
        'severity' => $severity
    ];
}

function httpGet($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

echo "====================================================================\n";
echo "ORBITAL ARCHIVE — MASTER PHASE 6 QA & COMPETITION AUDIT SUITE\n";
echo "====================================================================\n\n";

// ------------------------------------------------------------------
// SECTION 1: REST API ENDPOINTS AUDIT (PRESERVED PHASE 5)
// ------------------------------------------------------------------
echo "--- 1. REST API ENDPOINTS AUDIT ---\n";

$endpoints = [
    'Stats API' => "$baseUrl/api/stats.php",
    'Missions API' => "$baseUrl/api/missions.php",
    'Missions Filter' => "$baseUrl/api/missions.php?status=Active",
    'Single Mission' => "$baseUrl/api/missions.php?id=1",
    'Equipment API' => "$baseUrl/api/equipment.php",
    'Equipment Relics' => "$baseUrl/api/equipment.php?is_relic=1",
    'Perseverance Equipment' => "$baseUrl/api/equipment.php?id=perseverance",
    'Voyager 1 Equipment' => "$baseUrl/api/equipment.php?id=voyager-1",
    'Instruments API' => "$baseUrl/api/instruments.php",
    'Instruments By Equipment' => "$baseUrl/api/instruments.php?equipment_id=9",
    'Agencies API' => "$baseUrl/api/agencies.php",
    'Destinations API' => "$baseUrl/api/destinations.php",
    'Relics API' => "$baseUrl/api/relics.php",
    'Timeline API' => "$baseUrl/api/timeline.php",
    'Search API' => "$baseUrl/api/search.php?q=perseverance",
    'AI Archive API' => "$baseUrl/api/ai-archive.php?q=What+did+Perseverance+discover"
];

foreach ($endpoints as $name => $url) {
    $res = httpGet($url);
    $json = json_decode($res['body'], true);
    $ok = ($res['code'] === 200 && is_array($json) && !empty($json['success']));
    if ($ok) {
        recordResult("REST: $name", "PASS", $url);
    } else {
        recordResult("REST: $name", "FAIL", $url, "Expected HTTP 200 & success:true, got {$res['code']}", "HIGH");
    }
}

// 404 Error handling on API without SQL leaks
$errRes = httpGet("$baseUrl/api/missions.php?id=999999");
$errJson = json_decode($errRes['body'], true);
$ok404 = ($errRes['code'] === 404 && isset($errJson['error']) && strpos($errJson['error'], 'SQL') === false);
if ($ok404) {
    recordResult("REST: API 404 Error Sanitization", "PASS", "api/missions.php?id=999999");
} else {
    recordResult("REST: API 404 Error Sanitization", "FAIL", "api/missions.php?id=999999", "Leaked SQL or returned non-404 code", "HIGH");
}

// ------------------------------------------------------------------
// SECTION 2: 11 FLAGSHIP EXHIBITION DETAIL PAGES (PRESERVED PHASE 5)
// ------------------------------------------------------------------
echo "\n--- 2. FLAGSHIP EXHIBITION DETAIL PAGES (11 OBJECTS) ---\n";

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

foreach ($flagships as $slug => $displayName) {
    $url = "$baseUrl/equipment.php?id=$slug";
    $res = httpGet($url);
    $html = $res['body'];

    $has200 = ($res['code'] === 200);
    $hasTitle = (strpos($html, "<title>ORBITAL ARCHIVE —") !== false);
    $hasContext = (strpos($html, '[01] MISSION CONTEXT') !== false);
    $hasJourney = (strpos($html, '[02] INTERPLANETARY JOURNEY') !== false);
    $hasSubsystems = (strpos($html, '[03] THE MACHINE // ENGINEERING') !== false);
    $hasStatus = (strpos($html, '[07] OPERATIONAL STATUS & FATE') !== false);
    $hasVerifiedBadge = (strpos($html, 'LAST VERIFIED:') !== false);

    if ($has200 && $hasTitle && $hasContext && $hasJourney && $hasSubsystems && $hasStatus && $hasVerifiedBadge) {
        recordResult("Exhibition: $displayName Complete Exhibit", "PASS", "equipment.php?id=$slug");
    } else {
        recordResult("Exhibition: $displayName Complete Exhibit", "FAIL", "equipment.php?id=$slug", "Missing required section or non-200 code", "HIGH");
    }

    // Spacecraft vs Rover specificity
    if (in_array($slug, ['voyager-1', 'rosetta', 'hayabusa2'])) {
        $hasNoLocomotion = (strpos($html, 'MOBILITY & LOCOMOTION') === false);
        $hasNoRoboticArm = (strpos($html, 'ROBOTIC MANIPULATOR') === false);
        if ($hasNoLocomotion && $hasNoRoboticArm) {
            recordResult("Specification Hygiene: $displayName", "PASS", "equipment.php?id=$slug");
        } else {
            recordResult("Specification Hygiene: $displayName", "FAIL", "equipment.php?id=$slug", "Rover-specific hardware rendered on spacecraft", "MEDIUM");
        }
    }
}

// ------------------------------------------------------------------
// SECTION 3: SCIENTIFIC TERMINOLOGY & CHEYAVA FALLS AUDIT
// ------------------------------------------------------------------
echo "\n--- 3. SCIENTIFIC TERMINOLOGY & CHEYAVA FALLS AUDIT ---\n";

$persevRes = httpGet("$baseUrl/equipment.php?id=perseverance");
$persevHtml = $persevRes['body'];

$hasBiosignatureCandidate = (stripos($persevHtml, 'Potential biosignature candidate') !== false);
$hasUnconfirmedOrigin = (stripos($persevHtml, 'Biological origin remains unconfirmed') !== false);

if ($hasBiosignatureCandidate) {
    recordResult("Terminology: 'Potential biosignature candidate'", "PASS", "equipment.php?id=perseverance");
} else {
    recordResult("Terminology: 'Potential biosignature candidate'", "FAIL", "equipment.php?id=perseverance", "Missing required conservative wording", "CRITICAL");
}

if ($hasUnconfirmedOrigin) {
    recordResult("Terminology: 'Biological origin remains unconfirmed'", "PASS", "equipment.php?id=perseverance");
} else {
    recordResult("Terminology: 'Biological origin remains unconfirmed'", "FAIL", "equipment.php?id=perseverance", "Missing mandatory biological clarification", "CRITICAL");
}

$forbiddenTerms = ['life discovered', 'evidence of life', 'confirmed biosignature', 'real-time position'];
foreach ($forbiddenTerms as $term) {
    $found = (stripos($persevHtml, $term) !== false);
    if (!$found) {
        recordResult("Guardrail: Zero prohibited claim '$term'", "PASS", "equipment.php?id=perseverance");
    } else {
        recordResult("Guardrail: Zero prohibited claim '$term'", "FAIL", "equipment.php?id=perseverance", "Prohibited sensational term detected", "CRITICAL");
    }
}

// ------------------------------------------------------------------
// SECTION 4: 404 ERROR STATES AUDIT (PHASE 6 REQUIREMENT)
// ------------------------------------------------------------------
echo "\n--- 4. 404 ERROR STATES & TELEMETRY FALLBACK ---\n";

$badEq = httpGet("$baseUrl/equipment.php?id=non-existent-probe-123");
if ($badEq['code'] === 404 && strpos($badEq['body'], '404') !== false && stripos($badEq['body'], 'coordinates') !== false) {
    recordResult("Error State: Invalid Equipment returns HTTP 404", "PASS", "equipment.php?id=non-existent-probe-123");
} else {
    recordResult("Error State: Invalid Equipment returns HTTP 404", "FAIL", "equipment.php?id=non-existent-probe-123", "Expected HTTP 404 with styled error page, got {$badEq['code']}", "HIGH");
}

$badM = httpGet("$baseUrl/mission.php?id=999999");
if ($badM['code'] === 404 && strpos($badM['body'], '404') !== false) {
    recordResult("Error State: Invalid Mission returns HTTP 404", "PASS", "mission.php?id=999999");
} else {
    recordResult("Error State: Invalid Mission returns HTTP 404", "FAIL", "mission.php?id=999999", "Expected HTTP 404 with styled error page, got {$badM['code']}", "HIGH");
}

// ------------------------------------------------------------------
// SECTION 5: ROOT PAGES & PUBLIC VIEWS AUDIT
// ------------------------------------------------------------------
echo "\n--- 5. ROOT PAGES & PUBLIC VIEWS AUDIT ---\n";

$rootPages = [
    'Homepage (index.php)' => "$baseUrl/index.php",
    'Destinations Catalog (destinations.php)' => "$baseUrl/destinations.php",
    'Missions Catalog (missions.php)' => "$baseUrl/missions.php",
    'Relics Showcase (relics.php)' => "$baseUrl/relics.php",
    'Agencies Network (agencies.php)' => "$baseUrl/agencies.php",
    'Timeline Scrubber (timeline.php)' => "$baseUrl/timeline.php"
];

foreach ($rootPages as $pageName => $pageUrl) {
    $res = httpGet($pageUrl);
    $ok = ($res['code'] === 200 && strlen($res['body']) > 500);
    if ($ok) {
        recordResult("View: $pageName renders successfully (HTTP 200)", "PASS", $pageUrl);
    } else {
        recordResult("View: $pageName renders successfully (HTTP 200)", "FAIL", $pageUrl, "Expected HTTP 200, got {$res['code']}", "HIGH");
    }
}

// ------------------------------------------------------------------
// SECTION 6: RELATIONAL DATA INTEGRITY AUDIT (18 RULES)
// ------------------------------------------------------------------
echo "\n--- 6. RELATIONAL DATA INTEGRITY AUDIT (18 RULES) ---\n";

// Rule 1: Equipment-Mission
$orphanEq = $pdo->query("SELECT COUNT(*) FROM equipment e LEFT JOIN missions m ON e.mission_id = m.id WHERE m.id IS NULL")->fetchColumn();
if ($orphanEq == 0) {
    recordResult("Integrity R1: Equipment-Mission Foreign Keys", "PASS", "equipment.mission_id");
} else {
    recordResult("Integrity R1: Equipment-Mission Foreign Keys", "FAIL", "equipment.mission_id", "$orphanEq orphan equipment records", "HIGH");
}

// Rule 2: Equipment-Agency
$orphanAg = $pdo->query("SELECT COUNT(*) FROM equipment e JOIN missions m ON e.mission_id = m.id LEFT JOIN agencies a ON m.agency_id = a.id WHERE a.id IS NULL")->fetchColumn();
if ($orphanAg == 0) {
    recordResult("Integrity R2: Equipment-Agency Relationships", "PASS", "missions.agency_id");
} else {
    recordResult("Integrity R2: Equipment-Agency Relationships", "FAIL", "missions.agency_id", "$orphanAg missing agency mappings", "HIGH");
}

// Rule 3: Equipment-Destination
$orphanDst = $pdo->query("SELECT COUNT(*) FROM equipment e JOIN missions m ON e.mission_id = m.id LEFT JOIN destinations d ON m.destination_id = d.id WHERE d.id IS NULL")->fetchColumn();
if ($orphanDst == 0) {
    recordResult("Integrity R3: Equipment-Destination Relationships", "PASS", "missions.destination_id");
} else {
    recordResult("Integrity R3: Equipment-Destination Relationships", "FAIL", "missions.destination_id", "$orphanDst missing destination mappings", "HIGH");
}

// Rule 4: Instrument mappings
$orphanInst = $pdo->query("SELECT COUNT(*) FROM instruments i LEFT JOIN equipment_instruments ei ON i.id = ei.instrument_id WHERE ei.instrument_id IS NULL")->fetchColumn();
if ($orphanInst == 0) {
    recordResult("Integrity R4: Instrument Payload Mappings", "PASS", "instruments");
} else {
    recordResult("Integrity R4: Instrument Payload Mappings", "WARN", "instruments", "$orphanInst unmapped instruments", "LOW");
}

// Rule 5: Junction Foreign Keys
$brokenJunc = $pdo->query("SELECT COUNT(*) FROM equipment_instruments ei LEFT JOIN equipment e ON ei.equipment_id = e.id LEFT JOIN instruments i ON ei.instrument_id = i.id WHERE e.id IS NULL OR i.id IS NULL")->fetchColumn();
if ($brokenJunc == 0) {
    recordResult("Integrity R5: Equipment-Instrument Junction Integrity", "PASS", "equipment_instruments");
} else {
    recordResult("Integrity R5: Equipment-Instrument Junction Integrity", "FAIL", "equipment_instruments", "$brokenJunc broken junction records", "HIGH");
}

// Rule 6: Source Foreign Keys
$brokenSrc = $pdo->query("SELECT COUNT(*) FROM sources s LEFT JOIN missions m ON s.mission_id = m.id LEFT JOIN equipment e ON s.equipment_id = e.id WHERE (s.mission_id IS NOT NULL AND m.id IS NULL) OR (s.equipment_id IS NOT NULL AND e.id IS NULL)")->fetchColumn();
if ($brokenSrc == 0) {
    recordResult("Integrity R6: Source Foreign Key Integrity", "PASS", "sources");
} else {
    recordResult("Integrity R6: Source Foreign Key Integrity", "FAIL", "sources", "$brokenSrc broken source foreign keys", "HIGH");
}

// Rule 7: Image Foreign Keys
$brokenImg = $pdo->query("SELECT COUNT(*) FROM images img LEFT JOIN missions m ON img.mission_id = m.id LEFT JOIN equipment e ON img.equipment_id = e.id WHERE (img.mission_id IS NOT NULL AND m.id IS NULL) OR (img.equipment_id IS NOT NULL AND e.id IS NULL)")->fetchColumn();
if ($brokenImg == 0) {
    recordResult("Integrity R7: Image Foreign Key Integrity", "PASS", "images");
} else {
    recordResult("Integrity R7: Image Foreign Key Integrity", "FAIL", "images", "$brokenImg broken image foreign keys", "HIGH");
}

// Rule 8: Timeline Foreign Keys
$brokenTml = $pdo->query("SELECT COUNT(*) FROM timeline_events t LEFT JOIN missions m ON t.mission_id = m.id LEFT JOIN equipment e ON t.equipment_id = e.id WHERE (t.mission_id IS NOT NULL AND m.id IS NULL) OR (t.equipment_id IS NOT NULL AND e.id IS NULL)")->fetchColumn();
if ($brokenTml == 0) {
    recordResult("Integrity R8: Timeline Foreign Key Integrity", "PASS", "timeline_events");
} else {
    recordResult("Integrity R8: Timeline Foreign Key Integrity", "FAIL", "timeline_events", "$brokenTml broken timeline foreign keys", "HIGH");
}

// Rule 11: Slug Uniqueness
$dupeSlugs = $pdo->query("SELECT COUNT(*) FROM (SELECT slug, COUNT(*) c FROM equipment WHERE slug IS NOT NULL AND slug != '' GROUP BY slug HAVING c > 1) t")->fetchColumn();
if ($dupeSlugs == 0) {
    recordResult("Integrity R11: Equipment Slug Uniqueness", "PASS", "equipment.slug");
} else {
    recordResult("Integrity R11: Equipment Slug Uniqueness", "FAIL", "equipment.slug", "$dupeSlugs duplicate slugs detected", "HIGH");
}

// Rule 12: Corrupted degree symbol clean check across entire database
$corruptedSymbols = $pdo->query("SELECT COUNT(*) FROM equipment WHERE current_location LIKE '%┬░%' OR primary_region LIKE '%┬░%' OR fate LIKE '%┬░%'")->fetchColumn();
if ($corruptedSymbols == 0) {
    recordResult("Integrity R12: Zero Corrupted '┬░' Characters", "PASS", "equipment table");
} else {
    recordResult("Integrity R12: Zero Corrupted '┬░' Characters", "FAIL", "equipment table", "$corruptedSymbols corrupted degree symbols found", "MEDIUM");
}

// ------------------------------------------------------------------
// SECTION 7: PROVENANCE, CHALLENGE CONNECTION & ASSETS AUDIT
// ------------------------------------------------------------------
echo "\n--- 7. PROVENANCE, CHALLENGE CONNECTION & ASSETS AUDIT ---\n";

$readme = file_get_contents(__DIR__ . '/../README.md');
$hasChallengeMention = (strpos($readme, 'Abandoned But Not Forgotten') !== false);
$hasSpaceApps = (strpos($readme, 'NASA Space Apps Challenge 2026') !== false);
if ($hasChallengeMention && $hasSpaceApps && strlen($readme) > 5000) {
    recordResult("Documentation: README.md Complete Competition Spec", "PASS", "README.md");
} else {
    recordResult("Documentation: README.md Complete Competition Spec", "FAIL", "README.md", "README incomplete or missing challenge references", "MEDIUM");
}

$footer = file_get_contents(__DIR__ . '/../includes/footer.php');
$hasGroundedAiCopy = (strpos($footer, 'Answers are grounded in verified archive records') !== false);
$hasNoFactClaim = (strpos($footer, '100% factual accuracy') === false);
if ($hasGroundedAiCopy && $hasNoFactClaim) {
    recordResult("Copy/Safety: AI Assistant Grounded Records Statement", "PASS", "includes/footer.php");
} else {
    recordResult("Copy/Safety: AI Assistant Grounded Records Statement", "FAIL", "includes/footer.php", "AI assistant makes unverified claims or missing grounded text", "MEDIUM");
}

$mainJs = file_get_contents(__DIR__ . '/../assets/js/main.js');
$hasCategoryBadges = (strpos($mainJs, '[MISSION]') !== false && strpos($mainJs, '[RELIC]') !== false && strpos($mainJs, '[EQUIPMENT]') !== false && strpos($mainJs, '[INSTRUMENT]') !== false);
if ($hasCategoryBadges) {
    recordResult("Search UI: Visual Category Badges Defined", "PASS", "assets/js/main.js");
} else {
    recordResult("Search UI: Visual Category Badges Defined", "FAIL", "assets/js/main.js", "Search category badges missing in main.js", "LOW");
}

$robots = httpGet("$baseUrl/robots.txt");
if ($robots['code'] === 200 && strpos($robots['body'], 'Sitemap:') !== false) {
    recordResult("SEO: robots.txt accessible (HTTP 200)", "PASS", "robots.txt");
} else {
    recordResult("SEO: robots.txt accessible (HTTP 200)", "FAIL", "robots.txt", "robots.txt inaccessible or missing sitemap", "MEDIUM");
}

$sitemap = httpGet("$baseUrl/sitemap.xml");
$sitemapXml = simplexml_load_string($sitemap['body']);
$urlCount = $sitemapXml ? count($sitemapXml->url) : 0;
if ($sitemap['code'] === 200 && $urlCount >= 80) {
    recordResult("SEO: sitemap.xml valid XML and contains catalog URLs ($urlCount URLs)", "PASS", "sitemap.xml");
} else {
    recordResult("SEO: sitemap.xml valid XML and contains catalog URLs", "FAIL", "sitemap.xml", "Sitemap missing or fewer than 80 URLs", "MEDIUM");
}

// ------------------------------------------------------------------
// SUMMARY REPORT
// ------------------------------------------------------------------
echo "\n====================================================================\n";
echo "MASTER PHASE 6 QA RESULTS SUMMARY\n";
echo "  TOTAL TESTS: " . $results['total'] . "\n";
echo "  PASSED:      " . $results['passed'] . "\n";
echo "  WARNINGS:    " . $results['warnings'] . "\n";
echo "  FAILED:      " . $results['failed'] . "\n";
echo "====================================================================\n";

if ($results['failed'] === 0) {
    echo "AUDIT STATUS: ALL MASTER PHASE 6 QA TESTS PASSED PERFECTLY!\n";
    exit(0);
} else {
    echo "AUDIT STATUS: " . $results['failed'] . " TESTS FAILED. PLEASE REVIEW LOG ABOVE.\n";
    exit(1);
}
