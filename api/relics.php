<?php
/**
 * REST API: Relics (The Machines We Left Behind)
 * GET /api/relics.php
 * GET /api/relics.php?id=1
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    if ($id) {
        $stmt = $pdo->prepare("
            SELECT e.*, 
                   m.name AS mission_name, m.official_name AS mission_official_name, 
                   YEAR(m.launch_date) AS launch_year, m.launch_vehicle,
                   a.name AS agency_name, a.short_name AS agency_code,
                   d.name AS destination_name, d.distance_from_earth,
                   COALESCE(img.image_url, 'https://images-assets.nasa.gov/image/as15-88-11866/as15-88-11866~orig.jpg') AS hero_image,
                   img.credit AS image_credit, img.source AS image_source
            FROM equipment e
            JOIN missions m ON e.mission_id = m.id
            LEFT JOIN agencies a ON m.agency_id = a.id
            LEFT JOIN destinations d ON m.destination_id = d.id
            LEFT JOIN images img ON (img.equipment_id = e.id AND img.image_type = 'hero')
            WHERE e.id = :id AND e.is_relic = 1
        ");
        $stmt->execute(['id' => $id]);
        $relic = $stmt->fetch();

        if (!$relic) {
            jsonResponse(['success' => false, 'error' => 'Relic not found'], 404);
        }

        jsonResponse(['success' => true, 'data' => $relic]);
    }

    $stmt = $pdo->query("
        SELECT e.id, e.slug, e.name, e.official_name, e.type, e.operational_period, 
               e.current_status, e.current_location, e.relic_category, e.description,
               e.discoveries, e.legacy,
               m.id AS mission_id, m.name AS mission_name, YEAR(m.launch_date) AS launch_year,
               a.short_name AS agency_code,
               d.name AS destination_name,
               COALESCE((SELECT image_url FROM images WHERE equipment_id = e.id AND image_type = 'hero' LIMIT 1),
                        (SELECT image_url FROM images WHERE mission_id = m.id LIMIT 1),
                        'https://images-assets.nasa.gov/image/as15-88-11866/as15-88-11866~orig.jpg') AS hero_image
        FROM equipment e
        JOIN missions m ON e.mission_id = m.id
        LEFT JOIN agencies a ON m.agency_id = a.id
        LEFT JOIN destinations d ON m.destination_id = d.id
        WHERE e.is_relic = 1
        ORDER BY FIELD(e.name, 'Apollo 15 Lunar Roving Vehicle', 'Opportunity Rover', 'Spirit Rover', 'Surveyor 3 Lander', 'Voyager 1 Spacecraft', 'Philae Lander', 'Lunar Module Eagle') DESC, e.id ASC
    ");
    $relics = $stmt->fetchAll();

    jsonResponse(['success' => true, 'count' => count($relics), 'data' => $relics]);
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
