<?php
/**
 * REST API: Global Search across Missions, Equipment, Instruments, Agencies, Destinations
 * GET /api/search.php?q=perseverance
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    jsonResponse([
        'success' => true,
        'query' => $query,
        'results' => [
            'missions' => [],
            'equipment' => [],
            'instruments' => [],
            'agencies' => [],
            'destinations' => []
        ]
    ]);
}

$likeParam = '%' . $query . '%';

try {
    // 1. Search missions
    $mStmt = $pdo->prepare("
        SELECT m.id, m.name, m.official_name, m.status, YEAR(m.launch_date) AS launch_year,
               a.short_name AS agency_code, a.name AS agency_name, d.name AS destination_name
        FROM missions m
        LEFT JOIN agencies a ON m.agency_id = a.id
        LEFT JOIN destinations d ON m.destination_id = d.id
        WHERE m.name LIKE :q1 OR m.official_name LIKE :q2 OR m.objective LIKE :q3 OR m.description LIKE :q4 OR a.short_name LIKE :q5 OR d.name LIKE :q6
        LIMIT 10
    ");
    $mStmt->execute(['q1' => $likeParam, 'q2' => $likeParam, 'q3' => $likeParam, 'q4' => $likeParam, 'q5' => $likeParam, 'q6' => $likeParam]);
    $missions = $mStmt->fetchAll();

    // 2. Search equipment & relics
    $eqStmt = $pdo->prepare("
        SELECT e.id, e.slug, e.name, e.official_name, e.type, e.current_status, e.is_relic, e.relic_category,
               m.name AS mission_name, a.short_name AS agency_code, a.name AS agency_name, d.name AS destination_name
        FROM equipment e
        JOIN missions m ON e.mission_id = m.id
        LEFT JOIN agencies a ON m.agency_id = a.id
        LEFT JOIN destinations d ON m.destination_id = d.id
        WHERE e.name LIKE :q1 OR e.official_name LIKE :q2 OR e.type LIKE :q3 OR e.description LIKE :q4 OR e.slug LIKE :q5 OR m.name LIKE :q6 OR a.short_name LIKE :q7 OR d.name LIKE :q8
        LIMIT 15
    ");
    $eqStmt->execute(['q1' => $likeParam, 'q2' => $likeParam, 'q3' => $likeParam, 'q4' => $likeParam, 'q5' => $likeParam, 'q6' => $likeParam, 'q7' => $likeParam, 'q8' => $likeParam]);
    $allEquipment = $eqStmt->fetchAll();

    $equipment = [];
    $relics = [];
    foreach ($allEquipment as $eqItem) {
        if ($eqItem['is_relic']) {
            $relics[] = $eqItem;
        } else {
            $equipment[] = $eqItem;
        }
    }

    // 3. Search instruments
    $instStmt = $pdo->prepare("
        SELECT i.id, i.name, i.official_name, i.type, i.purpose,
               a.short_name AS agency_code,
               (SELECT e.slug FROM equipment e JOIN equipment_instruments ei ON e.id = ei.equipment_id WHERE ei.instrument_id = i.id LIMIT 1) AS equipment_slug,
               (SELECT e.id FROM equipment e JOIN equipment_instruments ei ON e.id = ei.equipment_id WHERE ei.instrument_id = i.id LIMIT 1) AS equipment_id,
               (SELECT e.name FROM equipment e JOIN equipment_instruments ei ON e.id = ei.equipment_id WHERE ei.instrument_id = i.id LIMIT 1) AS equipment_name,
               (SELECT d.name FROM equipment e JOIN equipment_instruments ei ON e.id = ei.equipment_id JOIN missions m ON e.mission_id = m.id JOIN destinations d ON m.destination_id = d.id WHERE ei.instrument_id = i.id LIMIT 1) AS destination_name
        FROM instruments i
        LEFT JOIN agencies a ON i.agency_id = a.id
        WHERE i.name LIKE :q1 OR i.official_name LIKE :q2 OR i.type LIKE :q3 OR i.purpose LIKE :q4 OR i.description LIKE :q5 OR i.specifications LIKE :q6
        LIMIT 10
    ");
    $instStmt->execute(['q1' => $likeParam, 'q2' => $likeParam, 'q3' => $likeParam, 'q4' => $likeParam, 'q5' => $likeParam, 'q6' => $likeParam]);
    $instruments = $instStmt->fetchAll();

    // 4. Search agencies
    $aStmt = $pdo->prepare("
        SELECT id, name, short_name, country, type
        FROM agencies
        WHERE name LIKE :q1 OR short_name LIKE :q2 OR country LIKE :q3
        LIMIT 5
    ");
    $aStmt->execute(['q1' => $likeParam, 'q2' => $likeParam, 'q3' => $likeParam]);
    $agencies = $aStmt->fetchAll();

    // 5. Search destinations
    $dStmt = $pdo->prepare("
        SELECT id, name, type, distance_from_earth
        FROM destinations
        WHERE name LIKE :q1 OR type LIKE :q2 OR description LIKE :q3
        LIMIT 5
    ");
    $dStmt->execute(['q1' => $likeParam, 'q2' => $likeParam, 'q3' => $likeParam]);
    $destinations = $dStmt->fetchAll();

    $totalMatches = count($missions) + count($equipment) + count($relics) + count($instruments) + count($agencies) + count($destinations);

    jsonResponse([
        'success' => true,
        'query' => $query,
        'total' => $totalMatches,
        'results' => [
            'missions' => $missions,
            'equipment' => $equipment,
            'relics' => $relics,
            'instruments' => $instruments,
            'agencies' => $agencies,
            'destinations' => $destinations
        ]
    ]);
} catch (PDOException $e) {
    error_log("Search API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Archive search could not be executed.'], 500);
}
