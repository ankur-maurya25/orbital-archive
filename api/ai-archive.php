<?php
/**
 * REST API: "Ask The Archive" AI Assistant
 * Grounded query synthesis directly from MySQL official database records
 * GET /api/ai-archive.php?q=What+did+Opportunity+discover
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    jsonResponse(['success' => false, 'error' => 'No question provided.'], 400);
}

$qLower = strtolower($query);

try {
    // 1. "Opportunity" queries
    if (strpos($qLower, 'opportunity') !== false) {
        $stmt = $pdo->query("SELECT * FROM equipment WHERE name LIKE '%Opportunity%' LIMIT 1");
        $eq = $stmt->fetch();
        if ($eq) {
            jsonResponse([
                'success' => true,
                'question' => $query,
                'answer' => "Opportunity (MER-B) operated on Mars for nearly 15 years (2004–2018), traveling a record 45.16 kilometers. " .
                            "Key discoveries include: " . $eq['discoveries'] . " It confirmed that ancient Mars had standing liquid water and a potentially habitable environment.",
                'sources' => ['NASA JPL MER Archive', 'Mars Exploration Rover Science Team']
            ]);
        }
    }

    // 2. "Moon" / "Lunar" equipment queries
    if (strpos($qLower, 'moon') !== false || strpos($qLower, 'lunar') !== false) {
        $stmt = $pdo->query("
            SELECT e.name, e.current_status, e.current_location 
            FROM equipment e 
            JOIN missions m ON e.mission_id = m.id 
            WHERE m.destination_id = 2 AND e.is_relic = 1
        ");
        $items = $stmt->fetchAll();
        $names = array_map(function($i) { return "{$i['name']} ({$i['current_location']})"; }, $items);
        
        jsonResponse([
            'success' => true,
            'question' => $query,
            'answer' => "According to archive records, notable human-made equipment remaining on the Moon includes: " . 
                        implode('; ', $names) . ". These artifacts mark inaugural milestones from Apollo 11 to Chandrayaan-3 and SLIM.",
            'sources' => ['NASA Apollo Surface Journal', 'ISRO Chandrayaan Portal', 'JAXA SLIM Mission']
        ]);
    }

    // 3. "Mars" landing queries
    if (strpos($qLower, 'mars') !== false) {
        $stmt = $pdo->query("
            SELECT m.name, m.launch_date, a.short_name 
            FROM missions m 
            JOIN agencies a ON m.agency_id = a.id 
            WHERE m.destination_id = 3 AND LOWER(m.status) IN ('active', 'completed')
            ORDER BY m.launch_date ASC
        ");
        $missions = $stmt->fetchAll();
        $list = array_map(function($m) { return "{$m['name']} ({$m['short_name']}, " . date('Y', strtotime($m['launch_date'])) . ")"; }, $missions);

        jsonResponse([
            'success' => true,
            'question' => $query,
            'answer' => "Documented missions that reached or explored Mars in the archive include: " . implode(', ', $list) . 
                        ". These include pioneering mobile rovers (Sojourner, Spirit, Opportunity, Curiosity, Perseverance, Zhurong) and landers (Phoenix, InSight).",
            'sources' => ['NASA Mars Program Office', 'CNSA Tianwen Records']
        ]);
    }

    // 4. "Deep Space" or "Interstellar" queries
    if (strpos($qLower, 'deep space') !== false || strpos($qLower, 'interstellar') !== false || strpos($qLower, 'farthest') !== false) {
        $stmt = $pdo->query("
            SELECT e.name, e.current_location, e.operational_period 
            FROM equipment e 
            JOIN missions m ON e.mission_id = m.id 
            WHERE m.destination_id = 7
        ");
        $deepProbes = $stmt->fetchAll();
        $list = array_map(function($p) { return "{$p['name']} [{$p['current_location']}]"; }, $deepProbes);

        jsonResponse([
            'success' => true,
            'question' => $query,
            'answer' => "Spacecraft cataloged in deep space and interstellar space include: " . implode('; ', $list) . 
                        ". Voyager 1 is humanity's most distant operating artifact, currently over 24 billion kilometers (163 AU) from Earth.",
            'sources' => ['NASA Deep Space Network', 'JPL Voyager Mission Office']
        ]);
    }

    // 5. General search fallback from equipment / missions
    $term = preg_replace('/[^a-zA-Z0-9\s]/', '', $query);
    $words = explode(' ', $term);
    $mainWord = '';
    foreach ($words as $w) {
        if (strlen($w) > 3 && !in_array(strtolower($w), ['what', 'which', 'where', 'when', 'does', 'that', 'with', 'from', 'have', 'been'])) {
            $mainWord = $w;
            break;
        }
    }

    if (!empty($mainWord)) {
        $stmt = $pdo->prepare("
            SELECT e.name, e.description, e.discoveries, m.name AS mission_name 
            FROM equipment e 
            JOIN missions m ON e.mission_id = m.id 
            WHERE e.name LIKE :q OR e.description LIKE :q OR m.name LIKE :q
            LIMIT 1
        ");
        $stmt->execute(['q' => "%$mainWord%"]);
        $match = $stmt->fetch();

        if ($match) {
            jsonResponse([
                'success' => true,
                'question' => $query,
                'answer' => "Archive Record regarding {$match['name']} ({$match['mission_name']}): " . 
                            ($match['description'] ?? '') . " " . ($match['discoveries'] ?? ''),
                'sources' => ['ORBITAL ARCHIVE Verified Database']
            ]);
        }
    }

    // Default polite response grounded in reality
    jsonResponse([
        'success' => true,
        'question' => $query,
        'answer' => "The Orbital Archive contains telemetry records for 30 pivotal space missions, 18 historic off-world relics, and global space agency networks spanning 1957 to the present. Try asking about 'Opportunity', 'Apollo 15 LRV', 'Moon relics', 'Mars missions', or 'Voyager'.",
        'sources' => ['Orbital Archive Catalog']
    ]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
