<?php
/**
 * REST API: Missions
 * GET /api/missions.php
 * GET /api/missions.php?id=1
 * GET /api/missions.php?status=Active
 * GET /api/missions.php?agency_id=1
 * GET /api/missions.php?destination_id=2
 * GET /api/missions.php?trajectory_type=Crewed
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$status = isset($_GET['status']) ? trim($_GET['status']) : null;
$agencyId = isset($_GET['agency_id']) ? (int)$_GET['agency_id'] : null;
$destinationId = isset($_GET['destination_id']) ? (int)$_GET['destination_id'] : null;
$trajectory = isset($_GET['trajectory_type']) ? trim($_GET['trajectory_type']) : null;
$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 100;

try {
    if ($id) {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   a.name AS agency_name, a.short_name AS agency_code, a.country AS agency_country,
                   d.name AS destination_name, d.type AS destination_type, d.distance_from_earth
            FROM missions m
            LEFT JOIN agencies a ON m.agency_id = a.id
            LEFT JOIN destinations d ON m.destination_id = d.id
            WHERE m.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $mission = $stmt->fetch();

        if (!$mission) {
            jsonResponse(['success' => false, 'error' => 'Mission not found'], 404);
        }

        // Fetch equipment
        $eqStmt = $pdo->prepare("SELECT * FROM equipment WHERE mission_id = :id");
        $eqStmt->execute(['id' => $id]);
        $mission['equipment'] = $eqStmt->fetchAll();

        // Fetch images
        $imgStmt = $pdo->prepare("SELECT * FROM images WHERE mission_id = :id");
        $imgStmt->execute(['id' => $id]);
        $mission['images'] = $imgStmt->fetchAll();

        // Fetch sources
        $srcStmt = $pdo->prepare("SELECT * FROM sources WHERE mission_id = :id");
        $srcStmt->execute(['id' => $id]);
        $mission['sources'] = $srcStmt->fetchAll();

        jsonResponse(['success' => true, 'data' => $mission]);
    }

    $sql = "
        SELECT m.id, m.name, m.official_name, m.launch_date, m.launch_vehicle, 
               m.launch_location, m.launch_lat, m.launch_lng, m.arrival_date,
               m.status, m.trajectory_type, m.objective, m.description,
               a.id AS agency_id, a.name AS agency_name, a.short_name AS agency_code, a.country AS agency_country,
               d.id AS destination_id, d.name AS destination_name, d.type AS destination_type
        FROM missions m
        LEFT JOIN agencies a ON m.agency_id = a.id
        LEFT JOIN destinations d ON m.destination_id = d.id
        WHERE 1=1
    ";
    $params = [];

    if ($status && in_array(strtolower($status), ['active', 'completed', 'lost', 'historic'])) {
        $sql .= " AND LOWER(m.status) = LOWER(:status)";
        $params['status'] = $status;
    }
    if ($agencyId) {
        $sql .= " AND m.agency_id = :agency_id";
        $params['agency_id'] = $agencyId;
    }
    if ($destinationId) {
        $sql .= " AND m.destination_id = :destination_id";
        $params['destination_id'] = $destinationId;
    }
    if ($trajectory) {
        $sql .= " AND LOWER(m.trajectory_type) = LOWER(:trajectory_type)";
        $params['trajectory_type'] = $trajectory;
    }

    $sql .= " ORDER BY m.launch_date DESC LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $missions = $stmt->fetchAll();

    jsonResponse(['success' => true, 'count' => count($missions), 'data' => $missions]);
} catch (PDOException $e) {
    error_log("Missions API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Mission data could not be retrieved.'], 500);
}
