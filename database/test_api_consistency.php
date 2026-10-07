<?php
/**
 * Test all API endpoints for Phase 4.1 consistency
 */

$baseUrl = 'http://localhost:8000';
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

function fetchJson($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'data' => json_decode($res, true)];
}

echo "=== 1. TESTING /api/equipment.php FOR ALL 11 OBJECTS ===\n";
foreach ($slugs as $slug) {
    $res = fetchJson("$baseUrl/api/equipment.php?id=$slug");
    $ok = ($res['code'] === 200 && ($res['data']['success'] ?? false) === true);
    $eq = $res['data']['data']['equipment'] ?? [];
    $inst = $res['data']['data']['instruments'] ?? [];
    $img = $res['data']['data']['images'] ?? [];
    $src = $res['data']['data']['sources'] ?? [];
    $time = $res['data']['data']['timeline'] ?? [];
    
    printf(
        "[%s] HTTP %3d | Slug: %-22s | Name: %-25s | Status: %-11s | Relic: %d | Inst: %d | Img: %d | Src: %d | Time: %d\n",
        $ok ? 'OK' : 'FAIL', $res['code'], $slug, $eq['name'] ?? 'NULL', $eq['current_status'] ?? 'NULL',
        $eq['is_relic'] ? 1 : 0, count($inst), count($img), count($src), count($time)
    );
}

echo "\n=== 2. TESTING CORE SYSTEM APIS ===\n";
$coreApis = [
    '/api/instruments.php',
    '/api/timeline.php',
    '/api/relics.php',
    '/api/destinations.php',
    '/api/search.php?q=mars',
    '/api/stats.php'
];

foreach ($coreApis as $endpoint) {
    $res = fetchJson("$baseUrl$endpoint");
    $ok = ($res['code'] === 200 && ($res['data']['success'] ?? false) === true);
    $count = $res['data']['count'] ?? ($res['data']['total'] ?? (isset($res['data']['data']) && is_array($res['data']['data']) ? count($res['data']['data']) : 'N/A'));
    printf("[%s] HTTP %3d | %-25s | Count/Total: %s\n", $ok ? 'OK' : 'FAIL', $res['code'], $endpoint, $count);
}

echo "\n=== 3. TESTING EXHIBITION HTML PAGES (HTTP 200) ===\n";
foreach ($slugs as $slug) {
    $ch = curl_init("$baseUrl/equipment.php?id=$slug");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $html = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $hasTitle = preg_match('/<title>(.*?)<\/title>/', $html, $matches);
    $title = $hasTitle ? $matches[1] : 'No Title';
    printf("[%s] HTTP %3d | Slug: %-22s | Title: %s\n", $code === 200 ? 'OK' : 'FAIL', $code, $slug, $title);
}
