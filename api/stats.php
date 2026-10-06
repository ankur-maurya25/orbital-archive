<?php
/**
 * REST API: Global Telemetry Statistics
 * GET /api/stats.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();

try {
    $missionsCount = $pdo->query("SELECT COUNT(*) FROM missions")->fetchColumn();
    $equipmentCount = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
    $activeMissions = $pdo->query("SELECT COUNT(*) FROM missions WHERE LOWER(status) = 'active'")->fetchColumn();
    $relicsCount = $pdo->query("SELECT COUNT(*) FROM equipment WHERE is_relic = 1")->fetchColumn();
    
    // Total deep space missions (destinations > 5 or distance marked as Deep Space)
    $deepSpaceCount = $pdo->query("
        SELECT COUNT(*) FROM missions m 
        JOIN destinations d ON m.destination_id = d.id 
        WHERE d.name IN ('Deep Space / Interstellar', 'Outer Solar System', 'Asteroids & Comets')
    ")->fetchColumn();

    // Orbital objects calculation (base official catalog estimate + DB tracked items)
    $totalObjectsArchived = 14280 + (int)$equipmentCount;

    jsonResponse([
        'success' => true,
        'data' => [
            'objects_archived' => $totalObjectsArchived,
            'missions_count' => (int)$missionsCount,
            'active_missions' => (int)$activeMissions,
            'equipment_count' => (int)$equipmentCount,
            'relics_count' => (int)$relicsCount,
            'deep_space_count' => (int)$deepSpaceCount,
            'timestamp' => date('Y-m-d H:i:s T')
        ]
    ]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
