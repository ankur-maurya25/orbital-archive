<?php
/**
 * REST API: Destinations
 * GET /api/destinations.php
 * GET /api/destinations.php?id=1
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();
$param = isset($_GET['id']) ? trim($_GET['id']) : (isset($_GET['slug']) ? trim($_GET['slug']) : null);

try {
    if ($param !== null && $param !== '') {
        if (ctype_digit($param)) {
            $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = :id");
            $stmt->execute(['id' => (int)$param]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM destinations WHERE LOWER(name) = LOWER(:slug)");
            $stmt->execute(['slug' => $param]);
        }
        $dest = $stmt->fetch();

        if (!$dest) {
            jsonResponse(['success' => false, 'error' => 'Destination not found'], 404);
        }

        $id = $dest['id'];

        $mStmt = $pdo->prepare("
            SELECT m.*, a.short_name AS agency_code 
            FROM missions m
            LEFT JOIN agencies a ON m.agency_id = a.id
            WHERE m.destination_id = :id
            ORDER BY m.launch_date DESC
        ");
        $mStmt->execute(['id' => $id]);
        $dest['missions'] = $mStmt->fetchAll();

        jsonResponse(['success' => true, 'data' => $dest]);
    }

    $stmt = $pdo->query("
        SELECT d.*, 
               COUNT(DISTINCT m.id) AS calculated_mission_count,
               (SELECT COUNT(*) FROM equipment e JOIN missions m2 ON e.mission_id = m2.id WHERE m2.destination_id = d.id) AS calculated_equipment_count,
               (SELECT GROUP_CONCAT(e.name SEPARATOR ', ') FROM equipment e JOIN missions m3 ON e.mission_id = m3.id WHERE m3.destination_id = d.id ORDER BY e.is_relic DESC, e.id ASC LIMIT 4) AS flagship_examples
        FROM destinations d
        LEFT JOIN missions m ON d.id = m.destination_id
        GROUP BY d.id
        ORDER BY d.id ASC
    ");
    $destinations = $stmt->fetchAll();

    jsonResponse(['success' => true, 'count' => count($destinations), 'data' => $destinations]);
} catch (PDOException $e) {
    error_log("Destinations API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Destination data could not be retrieved.'], 500);
}
