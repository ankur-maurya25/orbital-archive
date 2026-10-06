<?php
/**
 * REST API: Equipment
 * GET /api/equipment.php
 * GET /api/equipment.php?id=1
 * GET /api/equipment.php?is_relic=1
 * GET /api/equipment.php?mission_id=2
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$missionId = isset($_GET['mission_id']) ? (int)$_GET['mission_id'] : null;
$isRelic = isset($_GET['is_relic']) ? (int)$_GET['is_relic'] : null;
$category = isset($_GET['relic_category']) ? trim($_GET['relic_category']) : null;

try {
    if ($id) {
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   m.name AS mission_name, m.launch_date, m.launch_vehicle, m.status AS mission_status,
                   a.id AS agency_id, a.name AS agency_name, a.short_name AS agency_code, a.country AS agency_country,
                   d.id AS destination_id, d.name AS destination_name, d.distance_from_earth
            FROM equipment e
            JOIN missions m ON e.mission_id = m.id
            LEFT JOIN agencies a ON m.agency_id = a.id
            LEFT JOIN destinations d ON m.destination_id = d.id
            WHERE e.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();

        if (!$item) {
            jsonResponse(['success' => false, 'error' => 'Equipment not found'], 404);
        }

        // Fetch associated images
        $imgStmt = $pdo->prepare("SELECT * FROM images WHERE equipment_id = :id OR mission_id = :mission_id");
        $imgStmt->execute(['id' => $id, 'mission_id' => $item['mission_id']]);
        $item['images'] = $imgStmt->fetchAll();

        // Fetch associated sources
        $srcStmt = $pdo->prepare("SELECT * FROM sources WHERE equipment_id = :id OR mission_id = :mission_id");
        $srcStmt->execute(['id' => $id, 'mission_id' => $item['mission_id']]);
        $item['sources'] = $srcStmt->fetchAll();

        jsonResponse(['success' => true, 'data' => $item]);
    }

    $sql = "
        SELECT e.*, 
               m.name AS mission_name, m.launch_date,
               a.short_name AS agency_code,
               d.name AS destination_name,
               (SELECT image_url FROM images WHERE equipment_id = e.id LIMIT 1) AS image_url
        FROM equipment e
        JOIN missions m ON e.mission_id = m.id
        LEFT JOIN agencies a ON m.agency_id = a.id
        LEFT JOIN destinations d ON m.destination_id = d.id
        WHERE 1=1
    ";
    $params = [];

    if ($missionId) {
        $sql .= " AND e.mission_id = :mission_id";
        $params['mission_id'] = $missionId;
    }
    if ($isRelic !== null) {
        $sql .= " AND e.is_relic = :is_relic";
        $params['is_relic'] = $isRelic;
    }
    if ($category) {
        $sql .= " AND e.relic_category = :category";
        $params['category'] = $category;
    }

    $sql .= " ORDER BY e.id ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    jsonResponse(['success' => true, 'count' => count($items), 'data' => $items]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
