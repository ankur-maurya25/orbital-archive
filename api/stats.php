<?php
/**
 * REST API: Global Telemetry Statistics
 * Returns strictly verified database-backed figures
 * GET /api/stats.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();

try {
    $missionsCount = (int)$pdo->query("SELECT COUNT(*) FROM missions")->fetchColumn();
    $equipmentCount = (int)$pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
    $activeMissions = (int)$pdo->query("SELECT COUNT(*) FROM missions WHERE LOWER(status) = 'active'")->fetchColumn();
    $relicsCount = (int)$pdo->query("SELECT COUNT(*) FROM equipment WHERE is_relic = 1")->fetchColumn();
    $agenciesCount = (int)$pdo->query("SELECT COUNT(*) FROM agencies")->fetchColumn();
    $destinationsCount = (int)$pdo->query("SELECT COUNT(*) FROM destinations")->fetchColumn();
    $instrumentsCount = (int)$pdo->query("SELECT COUNT(*) FROM instruments")->fetchColumn();

    // Deep space missions (destinations outside Earth/Moon)
    $deepSpaceCount = (int)$pdo->query("
        SELECT COUNT(*) FROM missions m 
        JOIN destinations d ON m.destination_id = d.id 
        WHERE d.name IN ('Deep Space / Interstellar', 'Outer Solar System', 'Asteroids & Comets')
    ")->fetchColumn();

    jsonResponse([
        'success' => true,
        'data' => [
            'missions_count' => $missionsCount,
            'equipment_count' => $equipmentCount,
            'active_missions' => $activeMissions,
            'relics_count' => $relicsCount,
            'agencies_count' => $agenciesCount,
            'destinations_count' => $destinationsCount,
            'instruments_count' => $instrumentsCount,
            'deep_space_count' => $deepSpaceCount,
            'timestamp' => date('Y-m-d H:i:s T')
        ]
    ]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
