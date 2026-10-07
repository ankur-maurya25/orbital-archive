<?php
/**
 * REST API: Instruments
 * GET /api/instruments.php
 * GET /api/instruments.php?id=1
 * GET /api/instruments.php?equipment_id=9
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$equipmentId = isset($_GET['equipment_id']) ? (int)$_GET['equipment_id'] : null;

try {
    if ($id) {
        $stmt = $pdo->prepare("
            SELECT i.*, a.name AS agency_name, a.short_name AS agency_code
            FROM instruments i
            LEFT JOIN agencies a ON i.agency_id = a.id
            WHERE i.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $instrument = $stmt->fetch();

        if (!$instrument) {
            jsonResponse(['success' => false, 'error' => 'Instrument not found'], 404);
        }

        // Fetch associated equipment
        $eqStmt = $pdo->prepare("
            SELECT e.id, e.name, e.slug, e.type, m.name AS mission_name
            FROM equipment e
            JOIN equipment_instruments ei ON e.id = ei.equipment_id
            JOIN missions m ON e.mission_id = m.id
            WHERE ei.instrument_id = :id
        ");
        $eqStmt->execute(['id' => $id]);
        $instrument['equipment'] = $eqStmt->fetchAll();

        jsonResponse(['success' => true, 'data' => $instrument]);
    }

    if ($equipmentId) {
        $stmt = $pdo->prepare("
            SELECT i.*, a.short_name AS agency_code
            FROM instruments i
            JOIN equipment_instruments ei ON i.id = ei.instrument_id
            LEFT JOIN agencies a ON i.agency_id = a.id
            WHERE ei.equipment_id = :eq_id
            ORDER BY i.id ASC
        ");
        $stmt->execute(['eq_id' => $equipmentId]);
        $instruments = $stmt->fetchAll();

        jsonResponse(['success' => true, 'count' => count($instruments), 'data' => $instruments]);
    }

    // List all instruments
    $stmt = $pdo->query("
        SELECT i.*, a.short_name AS agency_code,
               (SELECT COUNT(*) FROM equipment_instruments WHERE instrument_id = i.id) AS equipment_count
        FROM instruments i
        LEFT JOIN agencies a ON i.agency_id = a.id
        ORDER BY i.name ASC
    ");
    $instruments = $stmt->fetchAll();

    jsonResponse(['success' => true, 'count' => count($instruments), 'data' => $instruments]);
} catch (PDOException $e) {
    error_log("Instruments API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Instrument catalog could not be retrieved.'], 500);
}
