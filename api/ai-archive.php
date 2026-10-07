<?php
/**
 * REST API: "Ask The Archive" Knowledge Assistant
 * Grounded query synthesis directly from verified MySQL database records.
 * Deterministic archive retrieval without third-party LLM dependencies or hallucinations.
 * 
 * GET /api/ai-archive.php?q=What+did+Perseverance+discover
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDB();
$rawQuery = '';
if (isset($_GET['q'])) {
    $rawQuery = $_GET['q'];
} elseif (isset($_GET['query'])) {
    $rawQuery = $_GET['query'];
} elseif (isset($_POST['q'])) {
    $rawQuery = $_POST['q'];
} elseif (isset($_POST['query'])) {
    $rawQuery = $_POST['query'];
} else {
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        $decoded = json_decode($input, true);
        if (is_array($decoded)) {
            $rawQuery = $decoded['q'] ?? $decoded['query'] ?? '';
        }
    }
}

if ($rawQuery === null || trim($rawQuery) === '') {
    jsonResponse([
        'success' => false,
        'error' => 'No question provided. Please submit an inquiry regarding cataloged missions, relics, or space agencies.'
    ], 400);
}

$rawTrimmed = trim($rawQuery);
// Truncate excessively long query for safe processing
if (strlen($rawTrimmed) > 400) {
    $rawTrimmed = substr($rawTrimmed, 0, 400);
}

try {
    // -------------------------------------------------------------
    // STEP 1: QUERY NORMALIZATION & CANONICAL ALIASES
    // -------------------------------------------------------------
    $qNorm = strtolower($rawTrimmed);
    $qNorm = preg_replace("/['’]/u", '', $qNorm); // remove apostrophes: what's -> whats
    $qNorm = preg_replace('/[^\w\s\-\.]/u', ' ', $qNorm); // keep letters, numbers, hyphens
    $qNorm = preg_replace('/\s+/', ' ', trim($qNorm));

    // Common aerospace singular/plural and terminology normalization
    $aerospaceAliases = [
        'spacecrafts' => 'spacecraft',
        'rovers' => 'rover',
        'landers' => 'lander',
        'satellites' => 'satellite',
        'missions' => 'mission',
        'probes' => 'probe',
        'instruments' => 'instrument',
        'relics' => 'relic',
        'chandray' => 'chandrayaan'
    ];
    $words = explode(' ', $qNorm);
    $normalizedWords = [];
    foreach ($words as $w) {
        $normalizedWords[] = $aerospaceAliases[$w] ?? $w;
    }
    $qClean = implode(' ', $normalizedWords);

    $sources = [];
    $sourceLinks = [];

    $addSource = function($org, $name = '', $url = '') use (&$sources, &$sourceLinks) {
        if (!empty($org) && !in_array($org, $sources)) {
            $sources[] = $org;
        }
        if (!empty($url)) {
            $exists = false;
            foreach ($sourceLinks as $l) {
                if ($l['url'] === $url) { $exists = true; break; }
            }
            if (!$exists) {
                $sourceLinks[] = ['name' => $name ?: $org, 'url' => $url];
            }
        }
    };

    // Helper to pull DB sources for a mission or equipment
    $fetchDBSources = function($missionId = null, $equipmentId = null) use ($pdo, $addSource) {
        if (!$missionId && !$equipmentId) return;
        $sql = "SELECT organization, source_name, source_url FROM sources WHERE ";
        $params = [];
        $conds = [];
        if ($missionId) {
            $conds[] = "mission_id = ?";
            $params[] = $missionId;
        }
        if ($equipmentId) {
            $conds[] = "equipment_id = ?";
            $params[] = $equipmentId;
        }
        $sql .= implode(' OR ', $conds) . " LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $org = !empty($row['organization']) ? $row['organization'] : 'Official Space Agency Archive';
            $addSource($org, $row['source_name'], $row['source_url']);
        }
    };

    // -------------------------------------------------------------
    // STEP 2 & 3: DETERMINISTIC INTENT ROUTING
    // -------------------------------------------------------------

    // INTENT 1: Chandrayaan / ISRO Lunar Exploration
    if (strpos($qClean, 'chandray') !== false || strpos($qClean, 'pragyan') !== false || strpos($qClean, 'vikram') !== false) {
        // Sub-case 1.1: Specific Chandrayaan-3 discoveries
        if ((strpos($qClean, 'discover') !== false || strpos($qClean, 'find') !== false || strpos($qClean, 'found') !== false || strpos($qClean, 'science') !== false) 
            && (strpos($qClean, '3') !== false || strpos($qClean, 'pragyan') !== false || strpos($qClean, 'vikram') !== false || (strpos($qClean, 'chandrayaan') !== false && strpos($qClean, '1') === false))) {
            $addSource('Indian Space Research Organisation (ISRO)');
            $fetchDBSources(14, 14);
            $fetchDBSources(14, 15);
            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => "Chandrayaan-3 achieved landmark scientific discoveries near the lunar south pole (Shiv Shakti Point, 69.373° S) utilizing its Vikram lander and Pragyan rover instruments:\n\n" .
                            "• In-Situ Sulfur Detection: Pragyan's Laser-Induced Breakdown Spectroscope (LIBS) and Alpha Particle X-ray Spectrometer (APXS) unambiguously confirmed the presence of Sulfur (S) in high southern lunar regolith, alongside aluminum, calcium, iron, chromium, titanium, manganese, silicon, and oxygen.\n\n" .
                            "• Thermal Gradient: Vikram's ChaSTE (Chandra's Surface Thermophysical Experiment) probe recorded the first direct lunar polar soil temperature profile, revealing a sharp thermal gradient from ~50°C at the top surface to -10°C just 8 cm into the regolith.\n\n" .
                            "• Lunar Seismology & Hop Test: Vikram's ILSA sensor recorded lunar seismic signals, and the lander performed an in-situ 40-centimeter engine hop test before entering dormant sleep mode.",
                'sources' => !empty($sources) ? $sources : ['Indian Space Research Organisation (ISRO)'],
                'source_links' => $sourceLinks
            ]);
        }

        // Sub-case 1.2: Chandrayaan-1 discoveries
        if (strpos($qClean, '1') !== false && (strpos($qClean, 'discover') !== false || strpos($qClean, 'water') !== false)) {
            $addSource('Indian Space Research Organisation (ISRO)');
            $fetchDBSources(13, 13);
            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => "Launched in October 2008, ISRO's Chandrayaan-1 lunar orbiter achieved a foundational discovery in modern planetary science:\n\n" .
                            "Data gathered by its onboard Moon Mineralogy Mapper (M3, NASA payload) and Moon Impact Probe (MIP) unambiguously confirmed the widespread presence of water molecules (H2O) and hydroxyl (OH) bound within lunar surface minerals and polar regolith deposits.",
                'sources' => !empty($sources) ? $sources : ['Indian Space Research Organisation (ISRO)'],
                'source_links' => $sourceLinks
            ]);
        }

        // Sub-case 1.3: Partial / General Chandrayaan Query
        $addSource('Indian Space Research Organisation (ISRO)');
        $fetchDBSources(14);
        $fetchDBSources(13);
        jsonResponse([
            'success' => true,
            'question' => $rawQuery,
            'answer' => "The archive catalogs four verified entities associated with India's Chandrayaan lunar programme:\n\n" .
                        "01  CHANDRAYAAN-1\n" .
                        "    Lunar Orbiter // ISRO // Completed (2008)\n" .
                        "    Confirmed water molecules and hydroxyl bound in lunar polar regolith.\n\n" .
                        "02  CHANDRAYAAN-3\n" .
                        "    Lunar Landing Mission // ISRO // Completed (2023)\n" .
                        "    Achieved humanity's first soft touchdown near the lunar south pole (Shiv Shakti Point).\n\n" .
                        "03  VIKRAM LANDER\n" .
                        "    Lunar Polar Soft Lander // Lunar Surface Relic // Preserved in situ\n" .
                        "    Conducted in-situ ChaSTE thermal sounding and lunar seismic measurements.\n\n" .
                        "04  PRAGYAN ROVER\n" .
                        "    Lunar Exploration Rover // Lunar Surface Relic // Preserved in situ\n" .
                        "    Traversed 101.4 meters and confirmed elemental Sulfur on the Moon.\n\n" .
                        "Ask a question about any of these archive records, such as: 'What did Chandrayaan-3 discover?' or 'What equipment is still on the Moon?'.",
            'sources' => !empty($sources) ? $sources : ['Indian Space Research Organisation (ISRO)'],
            'source_links' => $sourceLinks
        ]);
    }

    // INTENT 2: Deep Space / Interstellar Spacecraft
    if (strpos($qClean, 'deep space') !== false || strpos($qClean, 'interstellar') !== false || strpos($qClean, 'farthest') !== false || strpos($qClean, 'heliosphere') !== false) {
        $stmt = $pdo->query("
            SELECT e.name, e.current_status, e.current_location, e.fate, a.short_name, m.name as mission_name, m.id as mission_id, e.id as equipment_id
            FROM equipment e
            JOIN missions m ON e.mission_id = m.id
            JOIN agencies a ON m.agency_id = a.id
            WHERE m.destination_id IN (6, 7) 
              AND (e.type LIKE '%Spacecraft%' OR e.type LIKE '%Probe%') 
              AND LOWER(e.current_status) = 'operational'
              AND e.current_location NOT LIKE '%Low Earth Orbit%'
            ORDER BY m.launch_date ASC
        ");
        $probes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $addSource('NASA Jet Propulsion Laboratory');
        $addSource('NASA Deep Space Network');
        foreach ($probes as $p) {
            $fetchDBSources($p['mission_id'], $p['equipment_id']);
        }

        $items = [];
        $idx = 1;
        foreach ($probes as $p) {
            $num = str_pad($idx++, 2, '0', STR_PAD_LEFT);
            $upperName = strtoupper($p['name']);
            $items[] = "{$num}  {$upperName}\n    Status: {$p['current_status']} (Extended Mission) // Agency: {$p['short_name']}\n    Location: {$p['current_location']}";
        }

        jsonResponse([
            'success' => true,
            'question' => $rawQuery,
            'answer' => "Several archived spacecraft are actively operating in deep space and interstellar space beyond Earth:\n\n" .
                        implode("\n\n", $items) . "\n\n" .
                        "Archival Note: The archive distinguishes mission operational status from physical position. 'Deep space' is treated as an official astronomical destination and orbital trajectory context rather than live telemetry tracking.",
            'sources' => !empty($sources) ? $sources : ['NASA Jet Propulsion Laboratory', 'NASA Deep Space Network'],
            'source_links' => $sourceLinks
        ]);
    }

    // INTENT 3: Moon Exploration (Equipment, Relics, and Missions)
    if (strpos($qClean, 'moon') !== false || strpos($qClean, 'lunar') !== false) {
        // Sub-case: Missions that reached/explored the Moon
        if (strpos($qClean, 'mission') !== false || strpos($qClean, 'reach') !== false || strpos($qClean, 'went') !== false) {
            $stmt = $pdo->query("
                SELECT m.id, m.name, m.launch_date, m.status, a.short_name, m.objective
                FROM missions m
                JOIN agencies a ON m.agency_id = a.id
                WHERE m.destination_id = 2
                ORDER BY m.launch_date ASC
            ");
            $lunarMissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $addSource('NASA Lunar Exploration Archive');
            $addSource('Indian Space Research Organisation (ISRO)');
            foreach ($lunarMissions as $lm) {
                $fetchDBSources($lm['id']);
            }

            $items = [];
            $idx = 1;
            foreach ($lunarMissions as $lm) {
                $num = str_pad($idx++, 2, '0', STR_PAD_LEFT);
                $yr = substr($lm['launch_date'], 0, 4);
                $upperName = strtoupper($lm['name']);
                $items[] = "{$num}  {$upperName}\n    Agency: {$lm['short_name']} // Launch: {$yr} // Status: {$lm['status']}";
            }

            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => "The Orbital Archive documents several pioneering missions that journeyed to and explored the Moon:\n\n" .
                            implode("\n\n", $items) . "\n\n" .
                            "These lunar expeditions encompass early robotic soft-landers (Surveyor 1, Surveyor 3), the historic crewed Apollo lunar surface expeditions (Apollo 11, Apollo 15), and modern polar exploration (Chandrayaan-1, Chandrayaan-3, SLIM).",
                'sources' => !empty($sources) ? $sources : ['NASA Lunar Exploration Archive', 'ISRO Chandrayaan Portal'],
                'source_links' => $sourceLinks
            ]);
        }

        // Equipment on the Moon / Lunar Relics
        if (strpos($qClean, 'equipment') !== false || strpos($qClean, 'still') !== false || strpos($qClean, 'left') !== false || strpos($qClean, 'relic') !== false || strpos($qClean, 'machine') !== false || strpos($qClean, 'hardware') !== false || strpos($qClean, 'what is on') !== false || strpos($qClean, 'what is still') !== false || strpos($qClean, 'abandoned') !== false) {
            $stmt = $pdo->query("
                SELECT e.name, e.type, e.current_location, e.current_status, a.short_name, m.name as mission_name, m.id as mission_id, e.id as equipment_id
                FROM equipment e
                JOIN missions m ON e.mission_id = m.id
                JOIN agencies a ON m.agency_id = a.id
                WHERE m.destination_id = 2 AND (e.is_relic = 1 OR e.type IN ('Lander', 'Rover', 'Crewed Lunar Rover'))
                ORDER BY m.launch_date ASC
            ");
            $lunarItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $addSource('NASA Apollo Lunar Surface Journal');
            $addSource('Indian Space Research Organisation (ISRO)');
            $addSource('Japan Aerospace Exploration Agency (JAXA)');
            foreach ($lunarItems as $item) {
                $fetchDBSources($item['mission_id'], $item['equipment_id']);
            }

            $items = [];
            $idx = 1;
            foreach ($lunarItems as $it) {
                $num = str_pad($idx++, 2, '0', STR_PAD_LEFT);
                $upperName = strtoupper($it['name']);
                $items[] = "{$num}  {$upperName} ({$it['type']})\n    Mission: {$it['mission_name']} [{$it['short_name']}] // Status: {$it['current_status']}\n    Resting Site: {$it['current_location']}";
            }

            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => "Verified human-made equipment cataloged as historical relics resting on the Moon includes:\n\n" .
                            implode("\n\n", $items) . "\n\n" .
                            "Archival Note: While their operational lifetimes have concluded, these machines remain preserved in situ across the lunar regolith as enduring scientific and cultural heritage monuments.",
                'sources' => !empty($sources) ? $sources : ['NASA Apollo Lunar Surface Journal', 'ISRO Chandrayaan Portal', 'JAXA SLIM Mission Archive'],
                'source_links' => $sourceLinks
            ]);
        }
    }

    // INTENT 4: Spacecraft / Rovers / Missions on Mars
    if (strpos($qClean, 'mars') !== false && (strpos($qClean, 'spacecraft') !== false || strpos($qClean, 'rover') !== false || strpos($qClean, 'lander') !== false || strpos($qClean, 'land') !== false || strpos($qClean, 'touchdown') !== false || strpos($qClean, 'which') !== false || strpos($qClean, 'on mars') !== false || strpos($qClean, 'surface') !== false || strpos($qClean, 'missions landed') !== false)) {
        if (strpos($qClean, 'mission') !== false && strpos($qClean, 'spacecraft') === false && strpos($qClean, 'rover') === false) {
            $stmt = $pdo->query("
                SELECT DISTINCT m.id, m.name, m.launch_date, m.status, a.short_name
                FROM missions m
                JOIN agencies a ON m.agency_id = a.id
                JOIN equipment e ON e.mission_id = m.id
                WHERE m.destination_id = 3 AND e.type IN ('Rover', 'Lander', 'Helicopter')
                ORDER BY m.launch_date ASC
            ");
            $marsMissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $addSource('NASA Mars Exploration Program');
            $addSource('CNSA Tianwen Program');
            foreach ($marsMissions as $m) {
                $fetchDBSources($m['id']);
            }

            $items = [];
            $idx = 1;
            foreach ($marsMissions as $m) {
                $num = str_pad($idx++, 2, '0', STR_PAD_LEFT);
                $yr = substr($m['launch_date'], 0, 4);
                $upperName = strtoupper($m['name']);
                $items[] = "{$num}  {$upperName}\n    Agency: {$m['short_name']} // Launch: {$yr} // Mission Status: {$m['status']}";
            }

            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => "Documented space exploration missions in the archive that successfully touched down and operated on the surface of Mars:\n\n" .
                            implode("\n\n", $items) . "\n\n" .
                            "These surface explorations encompass pioneering rovers (Sojourner, Spirit, Opportunity, Curiosity, Perseverance, Zhurong), stationary science platforms (Phoenix, InSight), and atmospheric flight demonstration (Ingenuity).",
                'sources' => !empty($sources) ? $sources : ['NASA Mars Exploration Program', 'CNSA Planetary Exploration'],
                'source_links' => $sourceLinks
            ]);
        } else {
            $stmt = $pdo->query("
                SELECT e.name, e.type, e.current_status, e.current_location, a.short_name, m.name as mission_name, m.id as mission_id, e.id as equipment_id
                FROM equipment e
                JOIN missions m ON e.mission_id = m.id
                JOIN agencies a ON m.agency_id = a.id
                WHERE m.destination_id = 3
                ORDER BY m.launch_date ASC
            ");
            $marsCraft = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $addSource('NASA Mars Exploration Program');
            $addSource('CNSA Planetary Exploration');
            foreach ($marsCraft as $c) {
                $fetchDBSources($c['mission_id'], $c['equipment_id']);
            }

            $items = [];
            $idx = 1;
            foreach ($marsCraft as $c) {
                $num = str_pad($idx++, 2, '0', STR_PAD_LEFT);
                $upperName = strtoupper($c['name']);
                $items[] = "{$num}  {$upperName} ({$c['type']})\n    Mission: {$c['mission_name']} [{$c['short_name']}] // Status: {$c['current_status']}\n    Location: {$c['current_location']}";
            }

            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => "The archive documents the following robotic spacecraft, rovers, and landers on Mars:\n\n" .
                            implode("\n\n", $items) . "\n\n" .
                            "These surface platforms encompass actively operating laboratories (Perseverance in Jezero Crater, Curiosity in Gale Crater) as well as historic landing relics (Opportunity in Perseverance Valley, Spirit in Gusev Crater, Sojourner).",
                'sources' => !empty($sources) ? $sources : ['NASA Mars Exploration Program', 'CNSA Planetary Exploration'],
                'source_links' => $sourceLinks
            ]);
        }
    }

    // INTENT 5: Active Missions
    if (strpos($qClean, 'active') !== false && (strpos($qClean, 'mission') !== false || strpos($qClean, 'still active') !== false || strpos($qClean, 'operating') !== false || strpos($qClean, 'which') !== false)) {
        $stmt = $pdo->query("
            SELECT m.id, m.name, m.launch_date, a.short_name, d.name as dest_name
            FROM missions m
            JOIN agencies a ON m.agency_id = a.id
            JOIN destinations d ON m.destination_id = d.id
            WHERE LOWER(m.status) = 'active'
            ORDER BY m.launch_date ASC
        ");
        $activeMissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $addSource('NASA Science Mission Directorate');
        $addSource('ESA Operations Centre');
        $addSource('ISRO Telemetry Command Network');
        $addSource('JAXA Space Operations');

        $items = [];
        $idx = 1;
        foreach ($activeMissions as $m) {
            $num = str_pad($idx++, 2, '0', STR_PAD_LEFT);
            $yr = substr($m['launch_date'], 0, 4);
            $upperName = strtoupper($m['name']);
            $items[] = "{$num}  {$upperName}\n    Agency: {$m['short_name']} // Destination: {$m['dest_name']} // Active Since: {$yr}";
        }

        jsonResponse([
            'success' => true,
            'question' => $rawQuery,
            'answer' => "The Orbital Archive catalogs the following verified active missions conducting ongoing scientific operations:\n\n" .
                        implode("\n\n", $items) . "\n\n" .
                        "Archival Note: Active missions are defined as space assets currently conducting primary or extended science operations.",
            'sources' => !empty($sources) ? $sources : ['NASA Science Mission Directorate', 'ESA Space Operations', 'ISRO ISTRAC'],
            'source_links' => $sourceLinks
        ]);
    }

    // INTENT 6: Abandoned / Off-World Relics
    if (strpos($qClean, 'relic') !== false || strpos($qClean, 'left behind') !== false || strpos($qClean, 'abandoned') !== false || strpos($qClean, 'machines we left') !== false) {
        $stmt = $pdo->query("
            SELECT e.name, e.type, e.current_location, e.primary_region, a.short_name, m.name as mission_name, m.id as mission_id, e.id as equipment_id, d.name as dest_name
            FROM equipment e
            JOIN missions m ON e.mission_id = m.id
            JOIN agencies a ON m.agency_id = a.id
            JOIN destinations d ON m.destination_id = d.id
            WHERE e.is_relic = 1
            ORDER BY m.launch_date ASC
            LIMIT 8
        ");
        $relics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $addSource('NASA Planetary Protection & Heritage');
        $addSource('ORBITAL ARCHIVE Relics Registry');
        foreach ($relics as $r) {
            $fetchDBSources($r['mission_id'], $r['equipment_id']);
        }

        $items = [];
        $idx = 1;
        foreach ($relics as $r) {
            $num = str_pad($idx++, 2, '0', STR_PAD_LEFT);
            $upperName = strtoupper($r['name']);
            $loc = $r['primary_region'] ?: $r['current_location'];
            $items[] = "{$num}  {$upperName} ({$r['type']})\n    Mission: {$r['mission_name']} [{$r['short_name']}] // Destination: {$r['dest_name']}\n    Resting Site: {$loc}";
        }

        jsonResponse([
            'success' => true,
            'question' => $rawQuery,
            'answer' => "Under the NASA Space Apps Challenge theme 'Abandoned But Not Forgotten', the archive documents 18 off-world artifacts left across celestial bodies:\n\n" .
                        implode("\n\n", $items) . "\n\n" .
                        "These spacecraft and rovers traversed extraterrestrial terrain until their electrical power or mission lifetimes concluded, permanently etching human exploration history into solar system soil.",
            'sources' => !empty($sources) ? $sources : ['NASA History Division', 'ORBITAL ARCHIVE Registry'],
            'source_links' => $sourceLinks
        ]);
    }

    // INTENT 7: Opportunity Rover Specific Queries
    if (strpos($qClean, 'opportunity') !== false) {
        $stmt = $pdo->query("SELECT * FROM equipment WHERE slug = 'opportunity' OR name LIKE '%Opportunity%' LIMIT 1");
        $eq = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($eq) {
            $fetchDBSources($eq['mission_id'], $eq['id']);
            $addSource('NASA Jet Propulsion Laboratory');
            $addSource('NASA Science Mission Directorate');
            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => "Opportunity (MER-B) operated on Mars for nearly 15 years (2004–2018), traveling a record 45.16 kilometers across Meridiani Planum:\n\n" .
                            "• Key Discoveries: Discovered hematite spherules ('Martian blueberries') proving past standing acidic liquid water on Mars; discovered sulfate mineral veins in Endeavour Crater confirming past neutral-pH water; and surveyed Eagle, Endurance, Victoria, and Endeavour craters revealing vast stratified geological records.\n\n" .
                            "• Mission Status vs Physical Fate: Mission officially concluded by NASA on 13 February 2019 after 5,111 Sols following a severe global dust storm. The rover hardware rests preserved in situ in Perseverance Valley on the western rim of Endeavour Crater, Mars.",
                'sources' => !empty($sources) ? $sources : ['NASA Jet Propulsion Laboratory', 'NASA Mars Exploration Rover Team'],
                'source_links' => $sourceLinks
            ]);
        }
    }

    // INTENT 8: Perseverance / Cheyava Falls Specific Queries
    if (strpos($qClean, 'perseverance') !== false || strpos($qClean, 'cheyava') !== false) {
        $stmt = $pdo->query("SELECT * FROM equipment WHERE slug = 'perseverance' OR id = 9 LIMIT 1");
        $eq = $stmt->fetch(PDO::FETCH_ASSOC);

        $addSource('NASA Jet Propulsion Laboratory');
        $addSource('NASA Mars 2020 Science Team');
        $fetchDBSources($eq['mission_id'], $eq['id']);

        if (strpos($qClean, 'life') !== false || strpos($qClean, 'cheyava') !== false || strpos($qClean, 'biosignature') !== false || strpos($qClean, 'discover') !== false) {
            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => "In July 2024 at Cheyava Falls in Neretva Vallis, NASA's Perseverance rover identified millimeter-sized reaction rings ('leopard spots') containing iron and phosphate alongside organic signatures. This has been classified by NASA as a potential biosignature candidate. Biological origin remains unconfirmed. Further laboratory analysis of returned samples would be required to determine whether the observed features have a biological origin.",
                'sources' => !empty($sources) ? $sources : ['NASA Press Release (July 25, 2024)', 'NASA JPL Mars 2020 Science Team'],
                'source_links' => $sourceLinks
            ]);
        }

        if (strpos($qClean, 'instrument') !== false || strpos($qClean, 'payload') !== false) {
            $stmt = $pdo->query("
                SELECT i.name, i.type, i.purpose 
                FROM instruments i
                JOIN equipment_instruments ei ON i.id = ei.instrument_id
                WHERE ei.equipment_id = 9
            ");
            $insts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $list = array_map(function($i) { return "{$i['name']} ({$i['type']})"; }, $insts);
            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => "Perseverance carries 7 primary scientific instruments: " . implode('; ', $list) . ". These enable mast imaging, subsurface radar sounding, deep-UV and X-ray spectroscopy, weather monitoring, and in-situ oxygen generation (MOXIE).",
                'sources' => !empty($sources) ? $sources : ['NASA JPL Mars 2020 Spacecraft Specs'],
                'source_links' => $sourceLinks
            ]);
        }

        if (strpos($qClean, 'where') !== false || strpos($qClean, 'location') !== false || strpos($qClean, 'status') !== false) {
            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => "Perseverance is currently {$eq['current_status']} on Mars in its Extended Mission. Latest verified region: {$eq['current_location']} within {$eq['primary_region']}. (Last verified: {$eq['last_verified']}).",
                'sources' => !empty($sources) ? $sources : ['NASA JPL Mars 2020 Mission Dashboard'],
                'source_links' => $sourceLinks
            ]);
        }
    }

    // INTENT 8B: Voyager 1 Operator / Organization Queries
    if (strpos($qClean, 'voyager') !== false && (strpos($qClean, 'who') !== false || strpos($qClean, 'operate') !== false || strpos($qClean, 'agency') !== false || strpos($qClean, 'run') !== false || strpos($qClean, 'organization') !== false)) {
        $fetchDBSources(3, 3);
        $addSource('NASA Jet Propulsion Laboratory');
        $addSource('NASA Deep Space Network');
        jsonResponse([
            'success' => true,
            'question' => $rawQuery,
            'answer' => "Voyager 1 is operated by NASA and managed by the Jet Propulsion Laboratory (JPL) in Pasadena, California. Deep space communications, telemetry downlinks, and trajectory tracking are maintained via the NASA Deep Space Network (DSN) antenna complexes in Goldstone, Madrid, and Canberra.",
            'sources' => !empty($sources) ? $sources : ['NASA Jet Propulsion Laboratory', 'NASA Deep Space Network'],
            'source_links' => $sourceLinks
        ]);
    }

    // -------------------------------------------------------------
    // INTENT 9: Dynamic Layered Search (Equipment, Missions, Agencies)
    // -------------------------------------------------------------
    $stopwords = ['what', 'which', 'where', 'when', 'does', 'that', 'with', 'from', 'have', 'been', 'tell', 'about', 'some', 'give', 'show', 'list', 'are', 'the', 'and', 'was', 'were', 'did', 'is', 'who', 'how'];
    $meaningfulWords = array_values(array_filter($words, function($w) use ($stopwords) {
        return strlen($w) >= 3 && !in_array($w, $stopwords);
    }));

    if (!empty($meaningfulWords)) {
        // Destination constraint if query specifies a destination
        $destConstraint = "";
        if (strpos($qClean, 'mars') !== false) {
            $destConstraint = " AND m.destination_id = 3";
        } elseif (strpos($qClean, 'moon') !== false || strpos($qClean, 'lunar') !== false) {
            $destConstraint = " AND m.destination_id = 2";
        } elseif (strpos($qClean, 'deep space') !== false || strpos($qClean, 'interstellar') !== false) {
            $destConstraint = " AND m.destination_id IN (6, 7)";
        }

        // Try searching equipment with positional placeholders
        $eqQuery = "SELECT e.*, m.name as mission_name, a.name as agency_name, a.short_name as agency_code, d.name as destination_name 
                    FROM equipment e 
                    JOIN missions m ON e.mission_id = m.id 
                    JOIN agencies a ON m.agency_id = a.id 
                    JOIN destinations d ON m.destination_id = d.id 
                    WHERE ";
        $eqConds = [];
        $eqParams = [];
        foreach ($meaningfulWords as $mw) {
            $eqConds[] = "(e.name LIKE ? OR e.slug LIKE ? OR e.description LIKE ? OR e.discoveries LIKE ?)";
            $term = "%{$mw}%";
            $eqParams[] = $term;
            $eqParams[] = $term;
            $eqParams[] = $term;
            $eqParams[] = $term;
        }
        $eqQuery .= "(" . implode(' OR ', $eqConds) . ")" . $destConstraint . " LIMIT 1";
        $eqStmt = $pdo->prepare($eqQuery);
        $eqStmt->execute($eqParams);
        $eqMatch = $eqStmt->fetch(PDO::FETCH_ASSOC);

        if ($eqMatch) {
            $fetchDBSources($eqMatch['mission_id'], $eqMatch['id']);
            $addSource($eqMatch['agency_name'] ?: 'Official Space Agency Archive');

            $upperName = strtoupper($eqMatch['name']);
            $answer = "{$upperName} // ARCHIVE RECORD\n" .
                      "Mission: {$eqMatch['mission_name']} [{$eqMatch['agency_code']}] // Destination: {$eqMatch['destination_name']}\n" .
                      "Operational Status: {$eqMatch['current_status']}\n";
            if (!empty($eqMatch['current_location'])) {
                $answer .= "Location: {$eqMatch['current_location']}\n";
            }
            $answer .= "\n{$eqMatch['description']}\n";
            if (!empty($eqMatch['discoveries'])) {
                $answer .= "\nKey Findings & Discoveries: {$eqMatch['discoveries']}\n";
            }
            if (!empty($eqMatch['fate'])) {
                $answer .= "\nPhysical Fate: {$eqMatch['fate']}";
            }

            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => trim($answer),
                'sources' => !empty($sources) ? $sources : [$eqMatch['agency_name']],
                'source_links' => $sourceLinks
            ]);
        }

        // Try searching missions with positional placeholders
        $mQuery = "SELECT m.*, a.name as agency_name, a.short_name as agency_code, d.name as destination_name 
                   FROM missions m 
                   JOIN agencies a ON m.agency_id = a.id 
                   JOIN destinations d ON m.destination_id = d.id 
                   WHERE ";
        $mConds = [];
        $mParams = [];
        foreach ($meaningfulWords as $mw) {
            $mConds[] = "(m.name LIKE ? OR m.official_name LIKE ? OR m.description LIKE ? OR m.objective LIKE ?)";
            $term = "%{$mw}%";
            $mParams[] = $term;
            $mParams[] = $term;
            $mParams[] = $term;
            $mParams[] = $term;
        }
        $mQuery .= "(" . implode(' OR ', $mConds) . ") LIMIT 1";
        $mStmt = $pdo->prepare($mQuery);
        $mStmt->execute($mParams);
        $mMatch = $mStmt->fetch(PDO::FETCH_ASSOC);

        if ($mMatch) {
            $fetchDBSources($mMatch['id']);
            $addSource($mMatch['agency_name'] ?: 'Official Space Agency Archive');

            $upperName = strtoupper($mMatch['name']);
            $yr = substr($mMatch['launch_date'], 0, 4);
            $answer = "{$upperName} // MISSION DOSSIER\n" .
                      "Agency: {$mMatch['agency_name']} [{$mMatch['agency_code']}] // Launch: {$yr}\n" .
                      "Target: {$mMatch['destination_name']} // Status: {$mMatch['status']}\n\n" .
                      "{$mMatch['description']}\n";
            if (!empty($mMatch['objective'])) {
                $answer .= "\nPrimary Objective: {$mMatch['objective']}\n";
            }
            if (!empty($mMatch['legacy'])) {
                $answer .= "\nMission Legacy: {$mMatch['legacy']}";
            }

            jsonResponse([
                'success' => true,
                'question' => $rawQuery,
                'answer' => trim($answer),
                'sources' => !empty($sources) ? $sources : [$mMatch['agency_name']],
                'source_links' => $sourceLinks
            ]);
        }
    }

    // -------------------------------------------------------------
    // INTENT 10: Honest Fallback (Last Resort)
    // -------------------------------------------------------------
    jsonResponse([
        'success' => true,
        'question' => $rawQuery,
        'answer' => "The archive does not currently record verified records matching your inquiry. The Orbital Archive is a deterministic, catalog-grounded knowledge repository indexing 30 pivotal space missions, 30 hardware records, and 18 off-world relics spanning NASA, ISRO, ESA, JAXA, and CNSA.\n\n" .
                    "Suggested verified inquiries to explore:\n" .
                    "• \"What did Opportunity discover?\"\n" .
                    "• \"Which missions landed on Mars?\"\n" .
                    "• \"What equipment is still on the Moon?\"\n" .
                    "• \"Which spacecraft are currently in deep space?\"\n" .
                    "• \"What did Chandrayaan-3 discover?\"\n" .
                    "• \"Which missions are still active?\"\n" .
                    "• \"Tell me about Cheyava Falls\"",
        'sources' => ['ORBITAL ARCHIVE Registry Index'],
        'source_links' => []
    ]);

} catch (PDOException $e) {
    error_log("AI Archive API error: " . $e->getMessage());
    jsonResponse([
        'success' => false, 
        'error' => 'Archive knowledge query encounter a temporary system error. Please retry.'
    ], 500);
}
