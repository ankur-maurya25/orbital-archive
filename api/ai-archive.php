<?php
/**
 * REST API: "Ask The Archive" AI Assistant
 * Grounded query synthesis directly from MySQL official database records
 * GET /api/ai-archive.php?q=What+did+Perseverance+discover
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
    // 1. "Perseverance" / "Cheyava Falls" queries
    if (strpos($qLower, 'perseverance') !== false || strpos($qLower, 'cheyava') !== false) {
        if (strpos($qLower, 'life') !== false || strpos($qLower, 'cheyava') !== false || strpos($qLower, 'biosignature') !== false || strpos($qLower, 'discover') !== false) {
            jsonResponse([
                'success' => true,
                'question' => $query,
                'answer' => "In July 2024 at Cheyava Falls in Neretva Vallis, NASA's Perseverance rover identified millimeter-sized reaction rings ('leopard spots') containing iron and phosphate alongside organic signatures. This has been classified by NASA as a potential biosignature candidate. Biological origin remains unconfirmed. Further laboratory analysis of returned samples would be required to determine whether the observed features have a biological origin.",
                'sources' => ['NASA Press Release (July 25, 2024)', 'NASA JPL Mars 2020 Science Team']
            ]);
        }

        if (strpos($qLower, 'instrument') !== false || strpos($qLower, 'payload') !== false) {
            $stmt = $pdo->query("
                SELECT i.name, i.type, i.purpose 
                FROM instruments i
                JOIN equipment_instruments ei ON i.id = ei.instrument_id
                WHERE ei.equipment_id = 9
            ");
            $insts = $stmt->fetchAll();
            $list = array_map(function($i) { return "{$i['name']} ({$i['type']})"; }, $insts);
            jsonResponse([
                'success' => true,
                'question' => $query,
                'answer' => "Perseverance carries 7 primary scientific instruments: " . implode('; ', $list) . ". These enable imaging, subsurface radar sounding, deep-UV and X-ray spectroscopy, weather analysis, and in-situ oxygen generation.",
                'sources' => ['NASA JPL Mars 2020 Spacecraft Specs']
            ]);
        }

        if (strpos($qLower, 'where') !== false || strpos($qLower, 'location') !== false || strpos($qLower, 'status') !== false) {
            $eq = $pdo->query("SELECT current_location, primary_region, current_status, last_verified FROM equipment WHERE id = 9")->fetch();
            jsonResponse([
                'success' => true,
                'question' => $query,
                'answer' => "Perseverance is currently {$eq['current_status']} on Mars in its Extended Mission. Latest verified region: {$eq['current_location']} within {$eq['primary_region']}. (Last verified: {$eq['last_verified']}).",
                'sources' => ['NASA JPL Mars 2020 Mission Dashboard']
            ]);
        }

        // General Perseverance overview
        $eq = $pdo->query("SELECT description, discoveries FROM equipment WHERE id = 9")->fetch();
        jsonResponse([
            'success' => true,
            'question' => $query,
            'answer' => "{$eq['description']} Major scientific findings: {$eq['discoveries']}",
            'sources' => ['NASA Mars 2020 Mission Archive']
        ]);
    }

    // 2. "Opportunity" queries
    if (strpos($qLower, 'opportunity') !== false) {
        $stmt = $pdo->query("SELECT * FROM equipment WHERE slug = 'opportunity' OR name LIKE '%Opportunity%' LIMIT 1");
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

    // 3. "AI" / "AI drive" queries
    if (strpos($qLower, 'ai') !== false && (strpos($qLower, 'drive') !== false || strpos($qLower, 'mars') !== false)) {
        jsonResponse([
            'success' => true,
            'question' => $query,
            'answer' => "In February 2025, NASA reported the first AI-planned drive on Mars for the Perseverance rover. Vision-capable generative AI was utilized to assist human mission planners in evaluating terrain hazards and charting traversal routes across challenging Martian crater slopes. Human engineers maintain supervisory oversight over all rover movements.",
            'sources' => ['NASA JPL Autonomous Systems Division (Feb 2025)']
        ]);
    }

    // 4. "Moon" / "Lunar" equipment queries
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
            'answer' => "According to verified archive records, notable human-made equipment remaining on the Moon includes: " . 
                        implode('; ', $names) . ". These artifacts mark inaugural milestones from Apollo 11 to Chandrayaan-3 and SLIM.",
            'sources' => ['NASA Apollo Surface Journal', 'ISRO Chandrayaan Portal', 'JAXA SLIM Mission']
        ]);
    }

    // 5. "Mars" landing queries
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

    // 6. "Deep Space" or "Interstellar" queries
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

    // 7. General search fallback from equipment / missions
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

    // Default response
    jsonResponse([
        'success' => true,
        'question' => $query,
        'answer' => "The Orbital Archive contains verified telemetry records for 30 pivotal space missions, 30 hardware records, and 18 off-world relics spanning 1957 to the present. Try asking: 'What did Perseverance discover?', 'Tell me about Cheyava Falls', 'Which instruments are on Perseverance?', or 'Where is Perseverance currently located?'.",
        'sources' => ['Orbital Archive Catalog']
    ]);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
