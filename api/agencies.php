<?php
/**
 * REST API: Agencies
 * GET /api/agencies.php
 * GET /api/agencies.php?id=1
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM agencies WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $agency = $stmt->fetch();

        if (!$agency) {
            jsonResponse(['success' => false, 'error' => 'Agency not found'], 404);
        }

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

        jsonResponse(['success' => true, 'data' => $agency]);
    }

    $stmt = $pdo->query("
        SELECT a.*, 
               COUNT(m.id) AS total_missions,
               SUM(CASE WHEN LOWER(m.status) = 'active' THEN 1 ELSE 0 END) AS active_missions,
               SUM(CASE WHEN LOWER(m.status) = 'completed' THEN 1 ELSE 0 END) AS completed_missions
        FROM agencies a
        LEFT JOIN missions m ON a.id = m.agency_id
        GROUP BY a.id
        ORDER BY total_missions DESC, a.id ASC
    ");
    $agencies = $stmt->fetchAll();

    jsonResponse(['success' => true, 'count' => count($agencies), 'data' => $agencies]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
