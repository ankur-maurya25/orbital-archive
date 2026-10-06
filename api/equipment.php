<?php
/**
 * REST API: Equipment & Exhibit Payload
 * GET /api/equipment.php
 * GET /api/equipment.php?id=9
 * GET /api/equipment.php?id=perseverance
 * GET /api/equipment.php?is_relic=1
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();

$paramId = isset($_GET['id']) ? trim($_GET['id']) : (isset($_GET['slug']) ? trim($_GET['slug']) : null);
$missionId = isset($_GET['mission_id']) ? (int)$_GET['mission_id'] : null;
$isRelic = isset($_GET['is_relic']) ? (int)$_GET['is_relic'] : null;
$category = isset($_GET['relic_category']) ? trim($_GET['relic_category']) : null;

try {
    if (!empty($paramId)) {
        // Query by either integer ID or slug
        if (ctype_digit($paramId)) {
            $stmt = $pdo->prepare("
                SELECT e.*, 
                       m.id AS m_id, m.name AS m_name, m.official_name AS m_official, m.launch_date, m.launch_vehicle,
                       m.launch_location, m.arrival_date, m.status AS mission_status, m.objective AS mission_objective,
                       a.id AS a_id, a.name AS a_name, a.short_name AS a_code, a.country AS a_country, a.website_url AS a_url,
                       d.id AS d_id, d.name AS d_name, d.type AS d_type, d.distance_from_earth
                FROM equipment e
                JOIN missions m ON e.mission_id = m.id
                LEFT JOIN agencies a ON m.agency_id = a.id
                LEFT JOIN destinations d ON m.destination_id = d.id
                WHERE e.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => (int)$paramId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT e.*, 
                       m.id AS m_id, m.name AS m_name, m.official_name AS m_official, m.launch_date, m.launch_vehicle,
                       m.launch_location, m.arrival_date, m.status AS mission_status, m.objective AS mission_objective,
                       a.id AS a_id, a.name AS a_name, a.short_name AS a_code, a.country AS a_country, a.website_url AS a_url,
                       d.id AS d_id, d.name AS d_name, d.type AS d_type, d.distance_from_earth
                FROM equipment e
                JOIN missions m ON e.mission_id = m.id
                LEFT JOIN agencies a ON m.agency_id = a.id
                LEFT JOIN destinations d ON m.destination_id = d.id
                WHERE e.slug = :slug OR LOWER(e.name) = LOWER(:name)
                LIMIT 1
            ");
            $stmt->execute(['slug' => $paramId, 'name' => $paramId]);
        }

        $row = $stmt->fetch();

        if (!$row) {
            jsonResponse(['success' => false, 'error' => 'Equipment record not found'], 404);
        }

        $eqId = (int)$row['id'];
        $mId = (int)$row['m_id'];

        // Structured Equipment Object
        $equipment = [
            'id' => $row['id'],
            'slug' => $row['slug'],
            'name' => $row['name'],
            'official_name' => $row['official_name'],
            'type' => $row['type'],
            'mass' => $row['mass'] ?? 'Unknown',
            'dimensions' => $row['dimensions'] ?? 'Unknown',
            'power' => $row['power'] ?? 'Unknown',
            'mobility' => $row['mobility'] ?? 'Not applicable',
            'robotic_arm' => $row['robotic_arm'] ?? 'Not applicable',
            'autonomy' => $row['autonomy'] ?? 'Not applicable',
            'sample_caching' => $row['sample_caching'] ?? 'Not applicable',
            'purpose' => $row['purpose'],
            'technology' => $row['technology'],
            'communication' => $row['communication'],
            'operational_period' => $row['operational_period'],
            'journey_days' => $row['journey_days'],
            'current_status' => $row['current_status'],
            'current_location' => $row['current_location'],
            'primary_region' => $row['primary_region'],
            'mission_phase' => $row['mission_phase'],
            'is_relic' => (bool)$row['is_relic'],
            'relic_category' => $row['relic_category'],
            'description' => $row['description'],
            'discoveries' => $row['discoveries'],
            'legacy' => $row['legacy'],
            'verification_status' => $row['verification_status'] ?? 'CONFIRMED',
            'last_verified' => $row['last_verified']
        ];

        $mission = [
            'id' => $row['m_id'],
            'name' => $row['m_name'],
            'official_name' => $row['m_official'],
            'launch_date' => $row['launch_date'],
            'launch_vehicle' => $row['launch_vehicle'],
            'launch_location' => $row['launch_location'],
            'arrival_date' => $row['arrival_date'],
            'status' => $row['mission_status'],
            'objective' => $row['mission_objective']
        ];

        $agency = [
            'id' => $row['a_id'],
            'name' => $row['a_name'],
            'short_name' => $row['a_code'],
            'country' => $row['a_country'],
            'website_url' => $row['a_url']
        ];

        $destination = [
            'id' => $row['d_id'],
            'name' => $row['d_name'],
            'type' => $row['d_type'],
            'distance_from_earth' => $row['distance_from_earth']
        ];

        // Fetch normalized instruments
        $instStmt = $pdo->prepare("
            SELECT i.* 
            FROM instruments i
            JOIN equipment_instruments ei ON i.id = ei.instrument_id
            WHERE ei.equipment_id = :id
            ORDER BY i.id ASC
        ");
        $instStmt->execute(['id' => $eqId]);
        $instruments = $instStmt->fetchAll();

        // Fetch images
        $imgStmt = $pdo->prepare("
            SELECT * FROM images 
            WHERE equipment_id = :eq_id OR mission_id = :m_id
            ORDER BY FIELD(image_type, 'hero', 'launch', 'surface', 'operation') ASC, id ASC
        ");
        $imgStmt->execute(['eq_id' => $eqId, 'm_id' => $mId]);
        $images = $imgStmt->fetchAll();

        // Fetch timeline events
        $timeStmt = $pdo->prepare("
            SELECT * FROM timeline_events 
            WHERE equipment_id = :eq_id OR mission_id = :m_id
            ORDER BY year ASC, event_date ASC, id ASC
        ");
        $timeStmt->execute(['eq_id' => $eqId, 'm_id' => $mId]);
        $timeline = $timeStmt->fetchAll();

        // Fetch sources
        $srcStmt = $pdo->prepare("
            SELECT * FROM sources 
            WHERE equipment_id = :eq_id OR mission_id = :m_id
            ORDER BY id ASC
        ");
        $srcStmt->execute(['eq_id' => $eqId, 'm_id' => $mId]);
        $sources = $srcStmt->fetchAll();

        // Fetch related equipment (same destination or sibling rovers)
        $relStmt = $pdo->prepare("
            SELECT e.id, e.slug, e.name, e.type, e.current_status, e.is_relic,
                   m.name AS mission_name, d.name AS destination_name,
                   (SELECT image_url FROM images WHERE equipment_id = e.id AND image_type = 'hero' LIMIT 1) AS image_url
            FROM equipment e
            JOIN missions m ON e.mission_id = m.id
            JOIN destinations d ON m.destination_id = d.id
            WHERE e.id != :eq_id AND (m.destination_id = :d_id OR e.type = :type)
            ORDER BY e.is_relic DESC, e.id ASC
            LIMIT 6
        ");
        $relStmt->execute(['eq_id' => $eqId, 'd_id' => $row['d_id'], 'type' => $row['type']]);
        $related = $relStmt->fetchAll();

        jsonResponse([
            'success' => true,
            'data' => [
                'equipment' => $equipment,
                'mission' => $mission,
                'agency' => $agency,
                'destination' => $destination,
                'instruments' => $instruments,
                'images' => $images,
                'timeline' => $timeline,
                'sources' => $sources,
                'related' => $related
            ]
        ]);
    }

    // List mode
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
