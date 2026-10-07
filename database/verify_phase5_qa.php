<?php
/**
 * ORBITAL ARCHIVE - Phase 5 QA Automated Verification Suite
 * Tests REST APIs, Flagship Exhibitions, Security, Integrity & Language Controls
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$baseUrl = 'http://localhost:8000';

$results = [
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
    'tests' => []
];

function assertTest($name, $condition, $details = '') {
    global $results;
    if ($condition) {
        $results['passed']++;
        $results['tests'][] = ['status' => 'PASS', 'name' => $name, 'details' => $details];
        echo "  [PASS] $name\n";
    } else {
        $results['failed']++;
        $results['tests'][] = ['status' => 'FAIL', 'name' => $name, 'details' => $details];
        echo "  [FAIL] $name - $details\n";
    }
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
echo "ORBITAL ARCHIVE — PHASE 5 FULL AUTOMATED QA VERIFICATION SUITE\n";
echo "====================================================================\n\n";

// ------------------------------------------------------------------
// SECTION 1: REST API INTEGRITY & SANITIZATION
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
    assertTest("REST: $name (HTTP 200 & valid JSON)", $ok, "Status: {$res['code']}");
}

// Test 404 handling and error sanitization
$errRes = httpGet("$baseUrl/api/missions.php?id=999999");
$errJson = json_decode($errRes['body'], true);
assertTest(
    "REST: Invalid ID returns 404 without SQL leak",
    $errRes['code'] === 404 && isset($errJson['error']) && strpos($errJson['error'], 'SQL') === false,
    "Status: {$errRes['code']}"
);

// ------------------------------------------------------------------
// SECTION 2: 11 FLAGSHIP EXHIBITION DETAIL PAGES
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

    assertTest("Exhibition: $displayName renders complete exhibit", 
        $has200 && $hasTitle && $hasContext && $hasJourney && $hasSubsystems && $hasStatus && $hasVerifiedBadge,
        "HTTP {$res['code']}, Context:$hasContext, Title:$hasTitle"
    );

    // Rover vs Spacecraft specificity test
    if (in_array($slug, ['voyager-1', 'rosetta', 'hayabusa2'])) {
        // Spacecraft must not render locomotion or robotic arm cards
        $hasNoLocomotion = (strpos($html, 'MOBILITY & LOCOMOTION') === false);
        $hasNoRoboticArm = (strpos($html, 'ROBOTIC MANIPULATOR') === false);
        assertTest("Specification Hygiene: $displayName suppresses rover-specific mechanisms",
            $hasNoLocomotion && $hasNoRoboticArm,
            "No Locomotion: $hasNoLocomotion, No Arm: $hasNoRoboticArm"
        );
    }
}

// ------------------------------------------------------------------
// SECTION 3: SCIENTIFIC RIGOR & TERMINOLOGY CONTROLS
// ------------------------------------------------------------------
echo "\n--- 3. SCIENTIFIC TERMINOLOGY & CHEYAVA FALLS AUDIT ---\n";

$persevRes = httpGet("$baseUrl/equipment.php?id=perseverance");
$persevHtml = $persevRes['body'];

$hasBiosignatureCandidate = (stripos($persevHtml, 'Potential biosignature candidate') !== false);
$hasUnconfirmedOrigin = (stripos($persevHtml, 'Biological origin remains unconfirmed') !== false);

assertTest("Terminology: Exact phrase 'Potential biosignature candidate' present", $hasBiosignatureCandidate);
assertTest("Terminology: Exact phrase 'Biological origin remains unconfirmed' present", $hasUnconfirmedOrigin);

// Check zero occurrences of prohibited sensational terms
$forbiddenTerms = ['life discovered', 'evidence of life', 'confirmed biosignature', 'real-time position'];
foreach ($forbiddenTerms as $term) {
    $found = (stripos($persevHtml, $term) !== false);
    assertTest("Guardrail: Zero occurrences of prohibited claim '$term'", !$found, "Term found: " . ($found ? 'YES' : 'NO'));
}

// ------------------------------------------------------------------
// SECTION 3.5: ROOT PAGES & PUBLIC VIEWS AUDIT
// ------------------------------------------------------------------
echo "\n--- 3.5. ROOT PAGES & PUBLIC VIEWS AUDIT ---\n";

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
    assertTest("View: $pageName renders successfully (HTTP 200)", $ok, "Status: {$res['code']}");
}

// ------------------------------------------------------------------
// SECTION 4: SEO, SITEMAP, ROBOTS & ASSETS
// ------------------------------------------------------------------
echo "\n--- 4. SEO, SITEMAP, ROBOTS & ASSETS AUDIT ---\n";

$robots = httpGet("$baseUrl/robots.txt");
assertTest("SEO: robots.txt accessible (HTTP 200)", 
    $robots['code'] === 200 && strpos($robots['body'], 'Sitemap:') !== false,
    "Status: {$robots['code']}"
);

$sitemap = httpGet("$baseUrl/sitemap.xml");
$sitemapXml = simplexml_load_string($sitemap['body']);
$urlCount = $sitemapXml ? count($sitemapXml->url) : 0;
assertTest("SEO: sitemap.xml valid XML and contains catalog URLs",
    $sitemap['code'] === 200 && $urlCount >= 80,
    "Count: $urlCount URLs"
);

// ------------------------------------------------------------------
// SECTION 5: ACCESSIBILITY & DESIGN SYSTEM AUDIT
// ------------------------------------------------------------------
echo "\n--- 5. ACCESSIBILITY & CSS AUDIT ---\n";

$cssMain = file_get_contents(__DIR__ . '/../assets/css/main.css');
$hasUniversalFocus = (strpos($cssMain, ':focus-visible') !== false);
$hasKeyboardSelected = (strpos($cssMain, '.search-item-card.keyboard-selected') !== false);
$hasWebglFallback = (strpos($cssMain, '.webgl-fallback-stage') !== false);

assertTest("CSS: Universal :focus-visible rules defined in main.css", $hasUniversalFocus);
assertTest("CSS: Search keyboard navigation highlight defined", $hasKeyboardSelected);
assertTest("CSS: WebGL fallback stage styles defined", $hasWebglFallback);

$cssResp = file_get_contents(__DIR__ . '/../assets/css/responsive.css');
$hasReducedMotion = (strpos($cssResp, 'prefers-reduced-motion') !== false);
$hasMobileBreakpoint = (strpos($cssResp, 'max-width: 480px') !== false);
$hasMicroBreakpoint = (strpos($cssResp, 'max-width: 360px') !== false);

assertTest("Responsive: prefers-reduced-motion handled in responsive.css", $hasReducedMotion);
assertTest("Responsive: 480px mobile breakpoint defined", $hasMobileBreakpoint);
assertTest("Responsive: 360px compact mobile breakpoint defined", $hasMicroBreakpoint);

$earthJs = file_get_contents(__DIR__ . '/../assets/js/earth.js');
$hasWebglDetection = (strpos($earthJs, 'isWebGLAvailable') !== false);
$hasPointerEventsNone = (strpos($earthJs, "pointerEvents = 'none'") !== false);

assertTest("WebGL: isWebGLAvailable fallback mechanism in earth.js", $hasWebglDetection);
assertTest("UX/Scroll: pointerEvents = 'none' prevents scroll hijacking in earth.js", $hasPointerEventsNone);

// ------------------------------------------------------------------
// SUMMARY REPORT
// ------------------------------------------------------------------
echo "\n====================================================================\n";
echo "PHASE 5 QA RESULTS SUMMARY\n";
echo "Total Tests Executed: " . ($results['passed'] + $results['failed']) . "\n";
echo "PASSED: " . $results['passed'] . "\n";
echo "FAILED: " . $results['failed'] . "\n";
echo "====================================================================\n";

if ($results['failed'] === 0) {
    echo "STATUS: ALL PHASE 5 AUTOMATED VERIFICATION CHECKS PASSED PERFECTLY!\n";
    exit(0);
} else {
    echo "STATUS: SOME TESTS FAILED. PLEASE REVIEW LOG ABOVE.\n";
    exit(1);
}
