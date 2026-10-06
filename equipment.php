<?php
/**
 * ORBITAL ARCHIVE - Digital Museum Equipment Exhibit
 * Exhibition Experience for Mars 2020 Perseverance Rover and Spacecraft Archive
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$paramId = isset($_GET['id']) ? trim($_GET['id']) : (isset($_GET['slug']) ? trim($_GET['slug']) : 'perseverance');

// Query by integer ID or slug
if (ctype_digit($paramId)) {
    $stmt = $pdo->prepare("
        SELECT e.*, 
               m.id AS m_id, m.name AS m_name, m.official_name AS m_official, m.launch_date, m.launch_vehicle,
               m.launch_location, m.arrival_date, m.status AS mission_status, m.objective AS mission_objective,
               m.description AS mission_desc, m.trajectory_type,
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
               m.description AS mission_desc, m.trajectory_type,
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

$eq = $stmt->fetch();

if (!$eq) {
    header('Location: relics.php');
    exit;
}

$eqId = (int)$eq['id'];
$mId = (int)$eq['m_id'];

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
$heroImage = !empty($images) ? $images[0] : [
    'image_url' => 'https://images-assets.nasa.gov/image/PIA24426/PIA24426~orig.jpg',
    'title' => htmlspecialchars($eq['name']),
    'credit' => 'NASA / JPL-Caltech',
    'license' => 'Public Domain'
];

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

// Fetch related equipment
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
$relStmt->execute(['eq_id' => $eqId, 'd_id' => $eq['d_id'], 'type' => $eq['type']]);
$related = $relStmt->fetchAll();

$pageTitle = htmlspecialchars($eq['name']) . " — Museum Exhibit // ORBITAL ARCHIVE";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 6.5rem;">
  <div style="max-width: 1280px; margin: 0 auto; padding: 1.5rem 2rem 6rem;">

    <!-- ==============================================================
         01 — ARCHIVE HEADER & BREADCRUMBS
         ============================================================== -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
      <div style="display: flex; align-items: center; gap: 8px;">
        <a href="index.php" style="color: var(--text-muted); font-size: 0.8rem; font-family: var(--font-mono);">ORBITAL ARCHIVE</a>
        <span class="hud-bracket">//</span>
        <a href="destinations.php?id=<?= $eq['d_id'] ?>" style="color: var(--text-muted); font-size: 0.8rem; font-family: var(--font-mono);"><?= strtoupper(htmlspecialchars($eq['d_name'])) ?></a>
        <span class="hud-bracket">//</span>
        <span class="mono-label" style="color: var(--accent-cyan); font-size: 0.8rem;"><?= strtoupper(htmlspecialchars($eq['type'])) ?></span>
      </div>
      <div class="mono-label" style="color: var(--text-muted); font-size: 0.72rem;">
        VERIFICATION STATUS: <span style="color: var(--accent-emerald); font-weight: 700;"><?= htmlspecialchars($eq['verification_status'] ?? 'CONFIRMED') ?></span>
      </div>
    </div>

    <!-- ==============================================================
         02 — HERO
         ============================================================== -->
    <section style="margin-bottom: 4rem;">
      <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
          <div class="mono-label" style="color: var(--accent-blue); margin-bottom: 0.4rem;">
            <?= htmlspecialchars($eq['a_name']) ?> <span class="hud-bracket">//</span> MISSION: <?= strtoupper(htmlspecialchars($eq['m_name'])) ?>
          </div>
          <h1 style="font-size: 4rem; font-weight: 800; letter-spacing: -0.01em; color: #fff; text-transform: uppercase; line-height: 1;">
            <?= htmlspecialchars($eq['name']) ?>
          </h1>
          <div style="font-size: 1.2rem; color: var(--text-secondary); margin-top: 0.6rem;">
            <?= htmlspecialchars($eq['official_name'] ?? $eq['name']) ?>
          </div>
        </div>

        <div style="text-align: right;">
          <div class="mono-label" style="color: var(--text-muted);">MISSION PHASE</div>
          <div class="mono-value" style="font-size: 1.1rem; color: var(--accent-orange); font-weight: 700;">
            <?= htmlspecialchars($eq['mission_phase'] ?? 'Active Science Operations') ?>
          </div>
        </div>
      </div>

      <!-- Cinematic Image Canvas -->
      <div style="position: relative; width: 100%; height: 560px; border-radius: 4px; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 25px 80px rgba(0,0,0,0.85); margin-bottom: 1.5rem;">
        <img src="<?= htmlspecialchars($heroImage['image_url']) ?>" alt="<?= htmlspecialchars($heroImage['title'] ?? $eq['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
        <div class="relic-vignette"></div>

        <!-- Image Credit Bar -->
        <div style="position: absolute; bottom: 12px; left: 16px; right: 16px; display: flex; justify-content: space-between; align-items: center; background: rgba(5,7,11,0.85); backdrop-filter: blur(8px); padding: 6px 14px; border-radius: 2px; border: 1px solid rgba(255,255,255,0.08);">
          <span style="font-size: 0.75rem; color: var(--text-secondary);">
            <?= htmlspecialchars($heroImage['title'] ?? $eq['name']) ?>
          </span>
          <span class="mono-label" style="font-size: 0.65rem; color: var(--text-muted);">
            CREDIT: <?= htmlspecialchars($heroImage['credit'] ?? 'NASA/JPL-Caltech') ?> // LICENSE: <?= htmlspecialchars($heroImage['license'] ?? 'Public Domain') ?>
          </span>
        </div>
      </div>

      <!-- Metadata HUD Strip -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; background: rgba(11, 17, 24, 0.85); border: 1px solid var(--border-color); padding: 1.5rem 2rem; border-radius: 4px;">
        <div>
          <div class="relic-meta-label">STATUS</div>
          <div class="mono-value" style="font-size: 1.15rem; color: <?= strtolower($eq['current_status']) === 'operational' ? 'var(--accent-emerald)' : 'var(--accent-cyan)' ?>; margin-top: 4px; font-weight: 700;">
            <?= strtoupper(htmlspecialchars($eq['current_status'])) ?>
          </div>
        </div>
        <div>
          <div class="relic-meta-label">DESTINATION</div>
          <div class="mono-value" style="font-size: 1.15rem; color: var(--accent-orange); margin-top: 4px; font-weight: 700;">
            <?= strtoupper(htmlspecialchars($eq['d_name'])) ?>
          </div>
        </div>
        <div>
          <div class="relic-meta-label">MISSION</div>
          <div class="mono-value" style="font-size: 1.15rem; color: #fff; margin-top: 4px;">
            <?= htmlspecialchars($eq['m_name']) ?>
          </div>
        </div>
        <div>
          <div class="relic-meta-label">ARRIVED ON TARGET</div>
          <div class="mono-value" style="font-size: 1.15rem; color: var(--accent-cyan); margin-top: 4px;">
            <?= formatTelemetryDate($eq['arrival_date'] ?? $eq['launch_date']) ?>
          </div>
        </div>
      </div>
    </section>

    <!-- ==============================================================
         03 — MISSION CONTEXT
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.2rem; letter-spacing: 0.2em;">
        [01] MISSION CONTEXT & SCIENTIFIC OBJECTIVES
      </h2>
      <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem; align-items: start;">
        <div>
          <p style="font-size: 1.2rem; line-height: 1.8; color: var(--text-primary); margin-bottom: 1.5rem;">
            <?= nl2br(htmlspecialchars($eq['purpose'] ?? $eq['description'])) ?>
          </p>
          <p style="font-size: 1.05rem; line-height: 1.7; color: var(--text-secondary);">
            <?= nl2br(htmlspecialchars($eq['description'])) ?>
          </p>
        </div>

        <div class="relic-hud-card" style="padding: 1.8rem;">
          <div class="mono-label" style="color: var(--accent-blue); margin-bottom: 0.8rem;">PRIMARY SCIENCE GOALS</div>
          <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.8rem; font-size: 0.88rem; color: var(--text-secondary);">
            <li style="display: flex; gap: 10px;">
              <span style="color: var(--accent-cyan);">✦</span>
              <span><strong>Habitability:</strong> Identify past ancient Martian environments capable of supporting microbial life.</span>
            </li>
            <li style="display: flex; gap: 10px;">
              <span style="color: var(--accent-cyan);">✦</span>
              <span><strong>Biosignatures:</strong> Seek signs of ancient biology in specific rock types known to preserve evidence over billions of years.</span>
            </li>
            <li style="display: flex; gap: 10px;">
              <span style="color: var(--accent-cyan);">✦</span>
              <span><strong>Sample Caching:</strong> Collect and hermetically seal core samples for future Mars Sample Return.</span>
            </li>
            <li style="display: flex; gap: 10px;">
              <span style="color: var(--accent-cyan);">✦</span>
              <span><strong>Human Preparation:</strong> Test oxygen production (MOXIE) and characterize environmental conditions.</span>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- ==============================================================
         04 — JOURNEY
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.5rem; letter-spacing: 0.2em;">
        [02] INTERPLANETARY JOURNEY & ARRIVAL
      </h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1.2rem; background: rgba(11, 17, 24, 0.7); border: 1px solid var(--border-color); padding: 2rem; border-radius: 4px;">
        <div style="border-right: 1px solid var(--border-color); padding-right: 1rem;">
          <span class="mono-label" style="color: var(--accent-blue);">STAGE 01</span>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">EARTH ORIGIN</div>
          <div style="font-size: 0.82rem; color: var(--text-secondary);">Cape Canaveral Space Force Station SLC-41</div>
        </div>

        <div style="border-right: 1px solid var(--border-color); padding-right: 1rem;">
          <span class="mono-label" style="color: var(--accent-orange);">STAGE 02</span>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">LIFTOFF</div>
          <div style="font-size: 0.82rem; color: var(--text-secondary);"><?= formatTelemetryDate($eq['launch_date']) ?> // Atlas V 541</div>
        </div>

        <div style="border-right: 1px solid var(--border-color); padding-right: 1rem;">
          <span class="mono-label" style="color: var(--accent-cyan);">STAGE 03</span>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">203-DAY CRUISE</div>
          <div style="font-size: 0.82rem; color: var(--text-secondary);">471 Million km Interplanetary Transfer Arc</div>
        </div>

        <div style="border-right: 1px solid var(--border-color); padding-right: 1rem;">
          <span class="mono-label" style="color: var(--accent-emerald);">STAGE 04</span>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">EDL / SKY CRANE</div>
          <div style="font-size: 0.82rem; color: var(--text-secondary);">Terrain-Relative Navigation Guided Descent</div>
        </div>

        <div>
          <span class="mono-label" style="color: var(--accent-orange);">STAGE 05</span>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">JEZERO CRATER</div>
          <div style="font-size: 0.82rem; color: var(--text-secondary);">Touchdown 18 Feb 2021 // Octavia E. Butler Site</div>
        </div>
      </div>
    </section>

    <!-- ==============================================================
         05 — THE MACHINE
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.5rem; letter-spacing: 0.2em;">
        [03] THE MACHINE // ENGINEERING & SUBSYSTEMS
      </h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-blue);">DIMENSIONS & MASS</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">
            <?= htmlspecialchars($eq['mass'] ?? '1,025 kg') ?>
          </div>
          <p style="font-size: 0.82rem; color: var(--text-secondary);">
            <?= htmlspecialchars($eq['dimensions'] ?? '3.0 m × 2.7 m × 2.2 m') ?>. Heaviest and most capable mobile rover dispatched to Mars.
          </p>
        </div>

        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-orange);">POWER SYSTEM</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">MMRTG</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary);">
            <?= htmlspecialchars($eq['power'] ?? 'Plutonium-238 RTG') ?>
          </p>
        </div>

        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-cyan);">MOBILITY CHASSIS</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">ROCKER-BOGIE</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary);">
            <?= htmlspecialchars($eq['mobility'] ?? '6-wheel suspension with titanium cleats') ?>
          </p>
        </div>

        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-emerald);">ROBOTIC ARM & CORER</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">2.1-METER ARTICULATED</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary);">
            <?= htmlspecialchars($eq['robotic_arm'] ?? '5-jointed arm carrying rotary-percussive drill') ?>
          </p>
        </div>

        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-blue);">AUTONOMOUS NAVIGATION</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">AUTONAV & TRN</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary);">
            <?= htmlspecialchars($eq['autonomy'] ?? 'Machine vision real-time hazard avoidance') ?>
          </p>
        </div>

        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-orange);">SAMPLE CACHING ASSEMBLY</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">43 TITANIUM TUBES</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary);">
            <?= htmlspecialchars($eq['sample_caching'] ?? 'Hermetic sealing robotics in rover belly') ?>
          </p>
        </div>
      </div>
    </section>

    <!-- ==============================================================
         06 — NORMALIZED SCIENTIFIC INSTRUMENTS GRID
         ============================================================== -->
    <?php if (!empty($instruments)): ?>
    <section style="margin-bottom: 4.5rem;" id="instruments-section">
      <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1.5rem;">
        <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); letter-spacing: 0.2em;">
          [04] SCIENTIFIC PAYLOAD // NORMALIZED INSTRUMENT SYSTEM
        </h2>
        <span class="mono-label" style="color: var(--text-muted);">CLICK AN INSTRUMENT TO EXPAND SPECIFICATIONS</span>
      </div>

      <!-- Interactive Instruments Grid -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.2rem;">
        <?php foreach ($instruments as $idx => $inst): ?>
          <div class="relic-hud-card instrument-card" style="cursor: pointer; transition: all 0.2s;" data-inst-id="<?= $inst['id'] ?>" onclick="toggleInstrumentDetail(this)">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <div>
                <span class="mono-label" style="color: var(--accent-orange); font-size: 0.68rem;">
                  <?= htmlspecialchars($inst['type']) ?>
                </span>
                <h3 style="font-size: 1.4rem; font-weight: 700; color: #fff; margin: 4px 0;">
                  <?= htmlspecialchars($inst['name']) ?>
                </h3>
              </div>
              <span class="mono-label" style="color: var(--accent-cyan); font-size: 0.72rem;">[0<?= $idx + 1 ?>]</span>
            </div>

            <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0.8rem 0; line-height: 1.6;">
              <?= htmlspecialchars($inst['purpose']) ?>
            </p>

            <!-- Expandable detail drawer -->
            <div class="inst-drawer" style="display: none; padding-top: 1rem; border-top: 1px solid var(--border-color); margin-top: 0.8rem;">
              <div style="font-size: 0.85rem; color: var(--text-primary); line-height: 1.6; margin-bottom: 0.8rem;">
                <?= htmlspecialchars($inst['description']) ?>
              </div>
              <?php if (!empty($inst['specifications'])): ?>
                <div style="background: rgba(5,7,11,0.6); padding: 8px 12px; border-radius: 2px; border: 1px solid rgba(255,255,255,0.05); font-family: var(--font-mono); font-size: 0.72rem; color: var(--accent-blue); margin-bottom: 0.6rem;">
                  SPECS: <?= htmlspecialchars($inst['specifications']) ?>
                </div>
              <?php endif; ?>
              <div class="mono-label" style="color: var(--text-muted); font-size: 0.65rem;">
                MANUFACTURER: <?= htmlspecialchars($inst['manufacturer']) ?>
              </div>
            </div>

            <div style="text-align: right; margin-top: 0.5rem;">
              <span class="inst-toggle-label mono-label" style="font-size: 0.68rem; color: var(--accent-cyan);">+ VIEW DETAILS</span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- ==============================================================
         07 — DISCOVERIES & CHEYAVA FALLS
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.5rem; letter-spacing: 0.2em;">
        [05] SCIENTIFIC DISCOVERIES & FINDINGS
      </h2>
      <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2.5rem; align-items: start;">
        <div>
          <!-- Cheyava Falls Scientific Alert Card -->
          <div style="background: rgba(16, 26, 38, 0.7); border: 1px solid var(--accent-orange); padding: 2rem; border-radius: 4px; margin-bottom: 2rem; position: relative;">
            <div class="mono-label" style="color: var(--accent-orange); margin-bottom: 0.6rem; font-weight: 700;">
              ASTROBIOLOGY INVESTIGATION // CHEYAVA FALLS
            </div>
            <h3 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin-bottom: 1rem;">
              Potential Biosignature Candidate
            </h3>
            <p style="font-size: 1rem; line-height: 1.8; color: var(--text-primary); margin-bottom: 1rem;">
              In July 2024, Perseverance examined the <strong>'Cheyava Falls'</strong> rock in Neretva Vallis, an ancient river valley entering Jezero Crater. The rover identified organic molecules alongside millimeter-scale reaction halos (referred to as 'leopard spots') rich in iron and phosphate. In terrestrial environments, similar geochemical features can be associated with microbial metabolic reduction of hematite.
            </p>
            <div style="background: rgba(5,7,11,0.85); border-left: 3px solid var(--accent-cyan); padding: 12px 16px; margin: 1rem 0;">
              <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 4px;">STRICT SCIENTIFIC CLARIFICATION:</div>
              <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6; margin: 0;">
                <strong>Biological origin remains unconfirmed.</strong> Non-biological chemical reactions involving acidic groundwater or hydrothermal alteration could also produce these features. Further laboratory analysis of returned samples would be required to determine whether the observed features have a biological origin.
              </p>
            </div>
          </div>

          <div class="relic-hud-card">
            <h4 style="font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 0.6rem;">ANCIENT DELTA SEDIMENTS & CRATER FLOOR</h4>
            <p style="font-size: 0.92rem; color: var(--text-secondary); line-height: 1.7;">
              Perseverance confirmed that Jezero Crater was once filled with a deep lake that sustained a dynamic river delta system ~3.5 billion years ago. The rover has drilled cores from mudstones, sandstones, and volcanic igneous rocks, establishing that liquid water persisted long enough to alter mineral structures.
            </p>
          </div>
        </div>

        <div>
          <div class="relic-hud-card" style="margin-bottom: 1.5rem;">
            <div class="mono-label" style="color: var(--accent-emerald);">MOXIE EXPERIMENT</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 6px 0;">122 GRAMS OXYGEN PRODUCED</div>
            <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;">
              Extracted breathable oxygen at 98% purity from atmospheric CO2 across 16 runs in varying Martian seasons, proving in-situ propellant and life-support production for future human crews.
            </p>
          </div>

          <div class="relic-hud-card">
            <div class="mono-label" style="color: var(--accent-blue);">INGENUITY COMPANION</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 6px 0;">72 POWERED FLIGHTS</div>
            <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;">
              Deployed from Perseverance in April 2021, Ingenuity demonstrated aerial reconnaissance on another world, flying 17 km across 128 minutes before mission retirement in January 2024.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ==============================================================
         08 — MISSION CHRONOLOGY (TIMELINE EVENTS)
         ============================================================== -->
    <?php if (!empty($timeline)): ?>
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.5rem; letter-spacing: 0.2em;">
        [06] MISSION CHRONOLOGY // VERIFIED TIMELINE
      </h2>
      <div style="position: relative; padding-left: 2.5rem; border-left: 1px solid var(--border-color); margin-left: 1rem;">
        <?php foreach ($timeline as $t): ?>
          <div style="position: relative; margin-bottom: 2.5rem;">
            <div style="position: absolute; left: -3.05rem; top: 0.3rem; width: 14px; height: 14px; border-radius: 50%; background: var(--bg-primary); border: 2px solid var(--accent-blue); box-shadow: 0 0 10px rgba(110, 168, 255, 0.4);"></div>
            <div class="relic-hud-card" style="padding: 1.4rem;">
              <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                <span class="mono-label" style="color: var(--accent-cyan); font-size: 0.85rem; font-weight: 700;">
                  <?= !empty($t['event_date']) ? formatTelemetryDate($t['event_date']) : $t['year'] ?>
                </span>
                <span class="mono-label" style="color: var(--accent-orange); font-size: 0.65rem;">
                  <?= htmlspecialchars($t['importance'] ?? 'HIGH') ?>
                </span>
              </div>
              <h3 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 4px 0 8px;">
                <?= htmlspecialchars($t['title']) ?>
              </h3>
              <p style="font-size: 0.9rem; color: var(--text-secondary); line-height: 1.7;">
                <?= htmlspecialchars($t['description']) ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- ==============================================================
         09 — CURRENT STATUS / LATEST VERIFIED REGION
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.5rem; letter-spacing: 0.2em;">
        [07] CURRENT STATUS & LATEST VERIFIED REGION
      </h2>
      <div class="relic-hud-card" style="border-left: 3px solid var(--accent-emerald); padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
          <div>
            <div class="mono-label" style="color: var(--accent-emerald);">MISSION STATUS: <?= strtoupper(htmlspecialchars($eq['current_status'])) ?></div>
            <h3 style="font-size: 1.8rem; font-weight: 800; color: #fff; margin: 6px 0;">
              LATEST VERIFIED REGION: <?= htmlspecialchars($eq['primary_region'] ?? 'Jezero Crater') ?>
            </h3>
            <div style="font-size: 1rem; color: var(--text-primary); margin-top: 0.4rem;">
              Location: <strong><?= htmlspecialchars($eq['current_location']) ?></strong>
            </div>
            <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.4rem;">
              Perseverance is scaling the steep western rim of Jezero Crater toward ancient pre-impact terrains and the Lac de Charmes area.
            </div>
          </div>

          <div style="text-align: right;">
            <div class="mono-label" style="color: var(--text-muted);">TELEMETRY INTEGRITY</div>
            <div class="mono-value" style="font-size: 1.1rem; color: var(--accent-cyan); font-weight: 700;">
              LAST VERIFIED: <?= formatTelemetryDate($eq['last_verified'] ?? '2026-03-15') ?>
            </div>
            <div class="mono-label" style="font-size: 0.65rem; color: var(--text-muted); margin-top: 4px;">
              SOURCE: NASA JPL MARS 2020 MISSION
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ==============================================================
         10 — LEGACY
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.2rem; letter-spacing: 0.2em;">
        [08] LEGACY & FUTURE MARS EXPLORATION
      </h2>
      <div style="background: rgba(11, 17, 24, 0.7); border: 1px solid var(--border-color); padding: 2rem; border-radius: 4px;">
        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-primary);">
          <?= nl2br(htmlspecialchars($eq['legacy'])) ?>
        </p>
      </div>
    </section>

    <!-- ==============================================================
         11 — VERIFIED ARCHIVAL SOURCES
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.2rem; letter-spacing: 0.2em;">
        [09] VERIFIED ARCHIVAL SOURCES & REPOSITORIES
      </h2>
      <div style="display: flex; flex-direction: column; gap: 0.8rem;">
        <?php foreach ($sources as $s): ?>
          <a href="<?= htmlspecialchars($s['source_url']) ?>" target="_blank" rel="noopener" class="search-item-card">
            <div>
              <div style="font-weight: 700; color: #fff; font-size: 0.95rem;">
                <?= htmlspecialchars($s['source_name']) ?>
              </div>
              <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;">
                Organization: <strong><?= htmlspecialchars($s['organization'] ?? 'NASA') ?></strong> // <?= htmlspecialchars($s['description'] ?? 'Official Registry') ?>
              </div>
            </div>
            <div style="text-align: right;">
              <span class="mono-label" style="color: var(--accent-cyan);">VERIFIED LINK ↗</span>
              <span class="mono-value" style="display: block; font-size: 0.68rem; color: var(--text-muted); margin-top: 2px;">
                ACCESSED: <?= formatTelemetryDate($s['accessed_at'] ?? $s['verified_date']) ?>
              </span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ==============================================================
         12 — RELATED ARCHIVE
         ============================================================== -->
    <?php if (!empty($related)): ?>
    <section style="margin-bottom: 2rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.5rem; letter-spacing: 0.2em;">
        [10] RELATED EXPLORATION HARDWARE
      </h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1.5rem;">
        <?php foreach ($related as $rel): ?>
          <a href="equipment.php?id=<?= !empty($rel['slug']) ? htmlspecialchars($rel['slug']) : $rel['id'] ?>" class="relic-hud-card" style="display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, border-color 0.2s;">
            <div>
              <span class="mono-label" style="color: var(--accent-blue); font-size: 0.65rem;"><?= htmlspecialchars($rel['type']) ?></span>
              <h4 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">
                <?= htmlspecialchars($rel['name']) ?>
              </h4>
              <div style="font-size: 0.78rem; color: var(--text-secondary);">
                Mission: <?= htmlspecialchars($rel['mission_name']) ?>
              </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 0.6rem;">
              <span class="mono-label" style="font-size: 0.65rem; color: var(--accent-cyan);">
                <?= strtoupper(htmlspecialchars($rel['current_status'])) ?>
              </span>
              <span class="relic-cta-link" style="font-size: 0.7rem;">INSPECT →</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  </div>
</main>

<script>
// Interactive instrument card toggle
function toggleInstrumentDetail(card) {
  const drawer = card.querySelector('.inst-drawer');
  const label = card.querySelector('.inst-toggle-label');
  if (drawer.style.display === 'none' || drawer.style.display === '') {
    drawer.style.display = 'block';
    label.textContent = '− CLOSE DETAILS';
    card.style.borderColor = 'var(--accent-cyan)';
  } else {
    drawer.style.display = 'none';
    label.textContent = '+ VIEW DETAILS';
    card.style.borderColor = 'var(--border-color)';
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
