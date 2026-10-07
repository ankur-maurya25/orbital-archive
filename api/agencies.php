<?php
/**
 * REST API: Agencies
 * GET /api/agencies.php
 * GET /api/agencies.php?id=1
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();
$param = isset($_GET['id']) ? trim($_GET['id']) : (isset($_GET['slug']) ? trim($_GET['slug']) : null);

try {
    if ($param !== null && $param !== '') {
        if (ctype_digit($param)) {
            $stmt = $pdo->prepare("SELECT * FROM agencies WHERE id = :id");
            $stmt->execute(['id' => (int)$param]);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM agencies WHERE LOWER(short_name) = LOWER(:slug1) OR LOWER(name) = LOWER(:slug2)");
            $stmt->execute(['slug1' => $param, 'slug2' => $param]);
        }
        $agency = $stmt->fetch();

        if (!$agency) {
            jsonResponse(['success' => false, 'error' => 'Agency not found'], 404);
        }

        $id = $agency['id'];

        // Fetch missions belonging to agency
        $mStmt = $pdo->prepare("
            SELECT m.*, d.name AS destination_name 
            FROM missions m
            LEFT JOIN destinations d ON m.destination_id = d.id
            WHERE m.agency_id = :id
            ORDER BY m.launch_date DESC
        ");
        $mStmt->execute(['id' => $id]);
        $agency['missions'] = $mStmt->fetchAll();

        // Fetch equipment count
        $eStmt = $pdo->prepare("
            SELECT COUNT(e.id) AS total_equipment
            FROM equipment e
            JOIN missions m ON e.mission_id = m.id
            WHERE m.agency_id = :id
        ");
        $eStmt->execute(['id' => $id]);
        $agency['total_equipment'] = (int)($eStmt->fetchColumn() ?: 0);

        jsonResponse(['success' => true, 'data' => $agency]);
    }

    $stmt = $pdo->query("
        SELECT a.*, 
               COUNT(DISTINCT m.id) AS total_missions,
               COUNT(DISTINCT e.id) AS total_equipment,
               SUM(CASE WHEN LOWER(m.status) = 'active' THEN 1 ELSE 0 END) AS active_missions,
               SUM(CASE WHEN LOWER(m.status) = 'completed' THEN 1 ELSE 0 END) AS completed_missions,
               (SELECT GROUP_CONCAT(m2.name ORDER BY m2.launch_date DESC SEPARATOR ', ')
                FROM (SELECT name, launch_date, agency_id FROM missions LIMIT 50) m2
                WHERE m2.agency_id = a.id
               ) AS top_missions
        FROM agencies a
        LEFT JOIN missions m ON a.id = m.agency_id
        LEFT JOIN equipment e ON m.id = e.mission_id
        GROUP BY a.id
        ORDER BY total_missions DESC, a.id ASC
    ");
    $agencies = $stmt->fetchAll();

    jsonResponse(['success' => true, 'count' => count($agencies), 'data' => $agencies]);
} catch (PDOException $e) {
    error_log("Agencies API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Agency network telemetry could not be retrieved.'], 500);
}
