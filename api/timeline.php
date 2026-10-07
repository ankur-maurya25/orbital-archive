<?php
/**
 * REST API: Timeline Events
 * GET /api/timeline.php
 * GET /api/timeline.php?year=1969
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();
$year = isset($_GET['year']) ? (int)$_GET['year'] : null;

try {
    if ($year) {
        $stmt = $pdo->prepare("
            SELECT t.*, 
                   m.name AS mission_name, m.official_name AS mission_official, m.launch_date, m.status AS mission_status,
                   e.id AS equipment_id, e.slug AS equipment_slug, e.name AS equipment_name, e.type AS equipment_type, e.current_status, e.current_location,
                   a.short_name AS agency_code,
                   d.name AS destination_name,
                   (SELECT s.source_name FROM sources s WHERE s.equipment_id = t.equipment_id OR s.mission_id = t.mission_id ORDER BY s.id ASC LIMIT 1) AS source_name,
                   (SELECT s.source_url FROM sources s WHERE s.equipment_id = t.equipment_id OR s.mission_id = t.mission_id ORDER BY s.id ASC LIMIT 1) AS source_url
            FROM timeline_events t
            LEFT JOIN missions m ON t.mission_id = m.id
            LEFT JOIN equipment e ON t.equipment_id = e.id
            LEFT JOIN agencies a ON m.agency_id = a.id
            LEFT JOIN destinations d ON m.destination_id = d.id
            WHERE t.year = :year
            ORDER BY t.id ASC
            LIMIT 1
        ");
        $stmt->execute(['year' => $year]);
        $event = $stmt->fetch();

        if (!$event) {
            // Find closest event
            $cStmt = $pdo->prepare("
                SELECT t.*, 
                       m.name AS mission_name, m.status AS mission_status,
                       e.id AS equipment_id, e.slug AS equipment_slug, e.name AS equipment_name, e.type AS equipment_type, e.current_status, e.current_location,
                       a.short_name AS agency_code,
                       d.name AS destination_name,
                       (SELECT s.source_name FROM sources s WHERE s.equipment_id = t.equipment_id OR s.mission_id = t.mission_id ORDER BY s.id ASC LIMIT 1) AS source_name,
                       (SELECT s.source_url FROM sources s WHERE s.equipment_id = t.equipment_id OR s.mission_id = t.mission_id ORDER BY s.id ASC LIMIT 1) AS source_url
                FROM timeline_events t
                LEFT JOIN missions m ON t.mission_id = m.id
                LEFT JOIN equipment e ON t.equipment_id = e.id
                LEFT JOIN agencies a ON m.agency_id = a.id
                LEFT JOIN destinations d ON m.destination_id = d.id
                ORDER BY ABS(t.year - :year) ASC
                LIMIT 1
            ");
            $cStmt->execute(['year' => $year]);
            $event = $cStmt->fetch();
        }

        jsonResponse(['success' => true, 'data' => $event]);
    }

    $stmt = $pdo->query("
        SELECT t.*, 
               m.name AS mission_name, m.status AS mission_status,
               e.id AS equipment_id, e.slug AS equipment_slug, e.name AS equipment_name, e.type AS equipment_type, e.current_status, e.current_location,
               a.short_name AS agency_code,
               d.name AS destination_name,
               (SELECT s.source_name FROM sources s WHERE s.equipment_id = t.equipment_id OR s.mission_id = t.mission_id ORDER BY s.id ASC LIMIT 1) AS source_name,
               (SELECT s.source_url FROM sources s WHERE s.equipment_id = t.equipment_id OR s.mission_id = t.mission_id ORDER BY s.id ASC LIMIT 1) AS source_url
        FROM timeline_events t
        LEFT JOIN missions m ON t.mission_id = m.id
        LEFT JOIN equipment e ON t.equipment_id = e.id
        LEFT JOIN agencies a ON m.agency_id = a.id
        LEFT JOIN destinations d ON m.destination_id = d.id
        ORDER BY t.year ASC
    ");
    $events = $stmt->fetchAll();

    jsonResponse(['success' => true, 'count' => count($events), 'data' => $events]);
} catch (PDOException $e) {
    error_log("Timeline API Error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Archive timeline data could not be retrieved.'], 500);
}
