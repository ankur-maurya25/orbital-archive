<?php
/**
 * ORBITAL ARCHIVE - Digital Museum Equipment Exhibit
 * Exhibition Experience Engine powering all cataloged spacecraft, rovers, and landers
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
    'credit' => htmlspecialchars($eq['a_name'] ?? 'Space Agency Record'),
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

// Fetch related equipment with intelligent weighting
$relStmt = $pdo->prepare("
    SELECT e.id, e.slug, e.name, e.type, e.current_status, e.is_relic,
           m.name AS mission_name, d.name AS destination_name,
           (SELECT image_url FROM images WHERE equipment_id = e.id AND image_type = 'hero' LIMIT 1) AS image_url
    FROM equipment e
    JOIN missions m ON e.mission_id = m.id
    JOIN destinations d ON m.destination_id = d.id
    WHERE e.id != :eq_id AND (e.mission_id = :m_id OR m.destination_id = :d_id OR e.type = :type OR m.agency_id = :a_id)
    ORDER BY (e.mission_id = :m_id2) DESC, (m.destination_id = :d_id2) DESC, e.is_relic DESC, e.id ASC
    LIMIT 6
");
$relStmt->execute([
    'eq_id' => $eqId,
    'm_id' => $eq['mission_id'],
    'd_id' => $eq['d_id'],
    'type' => $eq['type'],
    'a_id' => $eq['a_id'],
    'm_id2' => $eq['mission_id'],
    'd_id2' => $eq['d_id']
]);
$related = $relStmt->fetchAll();

// Universal Dynamic SEO & Open Graph Metadata
$pageTitle = "ORBITAL ARCHIVE — " . $eq['name'];
$metaDescription = !empty($eq['description']) 
    ? substr(strip_tags($eq['description']), 0, 160) . '...' 
    : "Comprehensive scientific and technical exhibition for {$eq['name']} in ORBITAL ARCHIVE.";
$ogTitle = $pageTitle;
$ogDescription = $metaDescription;
$ogImage = $heroImage['image_url'];
$canonicalUrl = (isset($_SERVER['HTTP_HOST']) ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] : '') . '/equipment.php?id=' . urlencode($eq['slug'] ?? $eq['id']);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 6.5rem;">
  <div style="max-width: 1280px; margin: 0 auto; padding: 1.5rem 2rem 6rem;">

    <!-- ==============================================================
         01 — ARCHIVE HEADER & BREADCRUMBS
         ============================================================== -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
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
         02 — HERO SECTION
         ============================================================== -->
    <section style="margin-bottom: 4rem;">
      <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
          <div class="mono-label" style="color: var(--accent-blue); margin-bottom: 0.4rem;">
            <?= htmlspecialchars($eq['a_name']) ?> <span class="hud-bracket">//</span> MISSION: <?= strtoupper(htmlspecialchars($eq['m_name'])) ?>
          </div>
          <h1 style="font-size: 3.5rem; font-weight: 800; letter-spacing: -0.01em; color: #fff; text-transform: uppercase; line-height: 1.1;">
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
        <img src="<?= htmlspecialchars($heroImage['image_url']) ?>" alt="<?= htmlspecialchars($heroImage['title'] ?? $eq['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;" loading="eager">
        <div class="relic-vignette"></div>

        <!-- Image Credit Bar -->
        <div style="position: absolute; bottom: 12px; left: 16px; right: 16px; display: flex; justify-content: space-between; align-items: center; background: rgba(5,7,11,0.85); backdrop-filter: blur(8px); padding: 6px 14px; border-radius: 2px; border: 1px solid rgba(255,255,255,0.08); flex-wrap: wrap; gap: 6px;">
          <span style="font-size: 0.75rem; color: var(--text-secondary);">
            <?= htmlspecialchars($heroImage['title'] ?? $eq['name']) ?>
          </span>
          <span class="mono-label" style="font-size: 0.65rem; color: var(--text-muted);">
            CREDIT: <?= htmlspecialchars($heroImage['credit'] ?? 'Space Agency Documentation') ?> // LICENSE: <?= htmlspecialchars($heroImage['license'] ?? 'Public Domain') ?>
          </span>
        </div>
      </div>

      <!-- Metadata HUD Strip -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; background: rgba(11, 17, 24, 0.85); border: 1px solid var(--border-color); padding: 1.5rem 2rem; border-radius: 4px;">
        <div>
          <div class="relic-meta-label">OPERATIONAL STATUS</div>
          <div class="mono-value" style="font-size: 1.15rem; color: <?= (strtoupper($eq['current_status']) === 'OPERATIONAL' || strtoupper($eq['current_status']) === 'ACTIVE') ? 'var(--accent-emerald)' : 'var(--accent-cyan)' ?>; margin-top: 4px; font-weight: 700;">
            <?= strtoupper(htmlspecialchars($eq['current_status'])) ?>
          </div>
        </div>
        <div>
          <div class="relic-meta-label">PRIMARY TARGET</div>
          <div class="mono-value" style="font-size: 1.15rem; color: var(--accent-orange); margin-top: 4px; font-weight: 700;">
            <?= strtoupper(htmlspecialchars($eq['d_name'])) ?>
          </div>
        </div>
        <div>
          <div class="relic-meta-label">MISSION DOSSIER</div>
          <div class="mono-value" style="font-size: 1.15rem; color: #fff; margin-top: 4px;">
            <?= htmlspecialchars($eq['m_name']) ?>
          </div>
        </div>
        <div>
          <div class="relic-meta-label">TARGET ARRIVAL / EPOCH</div>
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
          <div class="mono-label" style="color: var(--accent-blue); margin-bottom: 0.8rem;">MISSION METRICS & OBJECTIVE</div>
          <div style="margin-bottom: 1rem;">
            <span class="mono-label" style="color: var(--accent-cyan); font-size: 0.7rem;">OFFICIAL OBJECTIVE</span>
            <p style="font-size: 0.88rem; color: #fff; margin-top: 4px; line-height: 1.6;">
              <?= htmlspecialchars($eq['mission_objective'] ?? $eq['purpose'] ?? 'Planetary exploration and scientific investigation') ?>
            </p>
          </div>
          <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.8rem; font-size: 0.85rem; color: var(--text-secondary); border-top: 1px solid rgba(255,255,255,0.05); padding-top: 0.8rem;">
            <li style="display: flex; justify-content: space-between;">
              <span class="mono-label">PRIMARY REGION:</span>
              <span style="color: #fff; font-weight: 600; text-align: right;"><?= htmlspecialchars($eq['primary_region'] ?? $eq['d_name']) ?></span>
            </li>
            <li style="display: flex; justify-content: space-between;">
              <span class="mono-label">OPERATING PERIOD:</span>
              <span style="color: var(--accent-orange); font-weight: 600; text-align: right;"><?= htmlspecialchars($eq['operational_period'] ?? 'Active') ?></span>
            </li>
            <li style="display: flex; justify-content: space-between;">
              <span class="mono-label">LEAD AGENCY:</span>
              <span style="color: var(--accent-blue); font-weight: 600; text-align: right;"><?= htmlspecialchars($eq['a_code'] ?? $eq['a_name']) ?></span>
            </li>
            <li style="display: flex; justify-content: space-between;">
              <span class="mono-label">VERIFICATION:</span>
              <span style="color: var(--accent-emerald); font-weight: 700;"><?= htmlspecialchars($eq['verification_status'] ?? 'CONFIRMED') ?></span>
            </li>
          </ul>
        </div>
      </div>
    </section>

    <!-- ==============================================================
         04 — JOURNEY TIMELINE
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.5rem; letter-spacing: 0.2em;">
        [02] INTERPLANETARY JOURNEY & ARRIVAL
      </h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1.2rem; background: rgba(11, 17, 24, 0.7); border: 1px solid var(--border-color); padding: 2rem; border-radius: 4px;">
        <div style="border-right: 1px solid var(--border-color); padding-right: 1rem;">
          <span class="mono-label" style="color: var(--accent-blue);">STAGE 01</span>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">EARTH ORIGIN</div>
          <div style="font-size: 0.82rem; color: var(--text-secondary);"><?= htmlspecialchars($eq['launch_location'] ?? 'Space Launch Complex') ?></div>
        </div>

        <div style="border-right: 1px solid var(--border-color); padding-right: 1rem;">
          <span class="mono-label" style="color: var(--accent-orange);">STAGE 02</span>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">LIFTOFF</div>
          <div style="font-size: 0.82rem; color: var(--text-secondary);"><?= formatTelemetryDate($eq['launch_date']) ?> // <?= htmlspecialchars($eq['launch_vehicle'] ?? 'Orbital Launch Vehicle') ?></div>
        </div>

        <div style="border-right: 1px solid var(--border-color); padding-right: 1rem;">
          <span class="mono-label" style="color: var(--accent-cyan);">STAGE 03</span>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">
            <?= !empty($eq['journey_days']) ? $eq['journey_days'] . '-DAY TRANSIT' : 'CRUISE PHASE' ?>
          </div>
          <div style="font-size: 0.82rem; color: var(--text-secondary);">
            <?= htmlspecialchars($eq['trajectory_type'] ?? 'Hohmann / Direct Interplanetary Transfer Arc') ?>
          </div>
        </div>

        <div style="border-right: 1px solid var(--border-color); padding-right: 1rem;">
          <span class="mono-label" style="color: var(--accent-emerald);">STAGE 04</span>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">
            <?php
              if (stripos($eq['type'], 'Rover') !== false || stripos($eq['type'], 'Lander') !== false) {
                  echo 'DESCENT & LANDING';
              } elseif (stripos($eq['type'], 'Orbiter') !== false) {
                  echo 'ORBIT INSERTION';
              } else {
                  echo 'ENCOUNTER & TRAJECTORY';
              }
            ?>
          </div>
          <div style="font-size: 0.82rem; color: var(--text-secondary);">
            <?= !empty($eq['technology']) ? htmlspecialchars(substr($eq['technology'], 0, 75)) . '...' : 'Target Encounter Sequence' ?>
          </div>
        </div>

        <div>
          <span class="mono-label" style="color: var(--accent-orange);">STAGE 05</span>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 4px 0;">
            <?= strtoupper(htmlspecialchars($eq['d_name'])) ?>
          </div>
          <div style="font-size: 0.82rem; color: var(--text-secondary);">
            <?= !empty($eq['arrival_date']) ? 'Arrival: ' . formatTelemetryDate($eq['arrival_date']) : 'Operational Regime' ?>
          </div>
        </div>
      </div>
    </section>

    <!-- ==============================================================
         05 — THE MACHINE // ENGINEERING & SUBSYSTEMS
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.5rem; letter-spacing: 0.2em;">
        [03] THE MACHINE // ENGINEERING & SUBSYSTEMS
      </h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        
        <!-- Mass & Dimensions (Always shown) -->
        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-blue);">DIMENSIONS & MASS</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">
            <?= htmlspecialchars($eq['mass'] ?? 'Specification Verified') ?>
          </div>
          <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6;">
            <?= htmlspecialchars($eq['dimensions'] ?? 'Dimensions archived in engineering records.') ?>
          </p>
        </div>

        <!-- Power System -->
        <?php if (!empty($eq['power']) && $eq['power'] !== 'Unknown'): ?>
        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-orange);">POWER SYSTEM</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">ELECTRICAL BUS</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6;">
            <?= htmlspecialchars($eq['power']) ?>
          </p>
        </div>
        <?php endif; ?>

        <!-- Mobility System (Conditional) -->
        <?php if (!empty($eq['mobility']) && $eq['mobility'] !== 'Not applicable' && $eq['mobility'] !== 'None'): ?>
        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-cyan);">PROPULSION / MOBILITY</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">LOCOMOTION SYSTEM</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6;">
            <?= htmlspecialchars($eq['mobility']) ?>
          </p>
        </div>
        <?php endif; ?>

        <!-- Robotic Arm / Manipulator (Conditional) -->
        <?php if (!empty($eq['robotic_arm']) && $eq['robotic_arm'] !== 'Not applicable' && $eq['robotic_arm'] !== 'None'): ?>
        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-emerald);">ROBOTIC MANIPULATOR</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">ARTICULATED ARM</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6;">
            <?= htmlspecialchars($eq['robotic_arm']) ?>
          </p>
        </div>
        <?php endif; ?>

        <!-- Autonomous Navigation (Conditional) -->
        <?php if (!empty($eq['autonomy']) && $eq['autonomy'] !== 'Not applicable' && $eq['autonomy'] !== 'None'): ?>
        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-blue);">AUTONOMOUS GUIDANCE</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">COMPUTATION & AUTONAV</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6;">
            <?= htmlspecialchars($eq['autonomy']) ?>
          </p>
        </div>
        <?php endif; ?>

        <!-- Sample Caching / Storage (Conditional) -->
        <?php if (!empty($eq['sample_caching']) && $eq['sample_caching'] !== 'Not applicable' && $eq['sample_caching'] !== 'None'): ?>
        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-orange);">SAMPLE CACHING / RETURN</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">RETRIEVAL ASSEMBLY</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6;">
            <?= htmlspecialchars($eq['sample_caching']) ?>
          </p>
        </div>
        <?php endif; ?>

        <!-- Communications -->
        <?php if (!empty($eq['communication'])): ?>
        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-cyan);">COMMUNICATIONS SUITE</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">TELEMETRY LINK</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6;">
            <?= htmlspecialchars($eq['communication']) ?>
          </p>
        </div>
        <?php endif; ?>

        <!-- Technology Overview -->
        <?php if (!empty($eq['technology'])): ?>
        <div class="relic-hud-card">
          <div class="mono-label" style="color: var(--accent-emerald);">ARCHIVAL ARCHITECTURE</div>
          <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 6px 0;">ENTRY & CRUISE TECH</div>
          <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.6;">
            <?= htmlspecialchars($eq['technology']) ?>
          </p>
        </div>
        <?php endif; ?>

      </div>
    </section>

    <!-- ==============================================================
         06 — NORMALIZED SCIENTIFIC INSTRUMENTS GRID
         ============================================================== -->
    <?php if (!empty($instruments)): ?>
    <section style="margin-bottom: 4.5rem;" id="instruments-section">
      <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 8px;">
        <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); letter-spacing: 0.2em;">
          [04] SCIENTIFIC PAYLOAD // NORMALIZED INSTRUMENT SYSTEM (<?= count($instruments) ?>)
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
                MANUFACTURER / LEAD: <?= htmlspecialchars($inst['manufacturer']) ?>
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
         07 — DISCOVERIES & SCIENTIFIC FINDINGS
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.5rem; letter-spacing: 0.2em;">
        [05] SCIENTIFIC DISCOVERIES & FINDINGS
      </h2>
      <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2.5rem; align-items: start;">
        <div>
          
          <!-- Perseverance Special Astrobiology Card -->
          <?php if ($eq['slug'] === 'perseverance' || $eqId === 9): ?>
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
          <?php endif; ?>

          <!-- Dynamic Discoveries Box -->
          <div class="relic-hud-card">
            <h4 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 1rem;">
              CONFIRMED SCIENTIFIC MILESTONES
            </h4>
            <div style="font-size: 0.95rem; color: var(--text-secondary); line-height: 1.8;">
              <?php 
                $discText = $eq['discoveries'] ?? 'Scientific telemetry and findings documented in archival logs.';
                $discLines = explode("\n", $discText);
                foreach ($discLines as $line):
                  $trimmed = trim($line);
                  if (empty($trimmed)) continue;
              ?>
                <div style="display: flex; gap: 10px; margin-bottom: 0.8rem; align-items: flex-start;">
                  <span style="color: var(--accent-cyan); margin-top: 2px;">✦</span>
                  <span style="color: var(--text-primary);"><?= htmlspecialchars($trimmed) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div>
          <!-- Mission Longevity / Output Metrics -->
          <div class="relic-hud-card" style="margin-bottom: 1.5rem;">
            <div class="mono-label" style="color: var(--accent-emerald);">OPERATIONAL PERIOD</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #fff; margin: 6px 0;">
              <?= htmlspecialchars($eq['operational_period'] ?? 'Active Exploration') ?>
            </div>
            <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;">
              Destination: <strong><?= htmlspecialchars($eq['d_name']) ?></strong> (<?= htmlspecialchars($eq['primary_region'] ?? 'Target Area') ?>).
            </p>
          </div>

          <div class="relic-hud-card">
            <div class="mono-label" style="color: var(--accent-blue);">EXPLORATION FOOTPRINT</div>
            <div style="font-size: 1.2rem; font-weight: 700; color: #fff; margin: 6px 0;">
              <?= strtoupper(htmlspecialchars($eq['current_status'])) ?>
            </div>
            <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;">
              Current Location: <span style="color: #fff;"><?= htmlspecialchars($eq['current_location'] ?? 'Space Coordinates Archived') ?></span>
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
        [06] MISSION CHRONOLOGY // VERIFIED TIMELINE (<?= count($timeline) ?> MILESTONES)
      </h2>
      <div style="position: relative; padding-left: 2.5rem; border-left: 1px solid var(--border-color); margin-left: 1rem;">
        <?php foreach ($timeline as $t): ?>
          <div style="position: relative; margin-bottom: 2.5rem;">
            <div style="position: absolute; left: -3.05rem; top: 0.3rem; width: 14px; height: 14px; border-radius: 50%; background: var(--bg-primary); border: 2px solid var(--accent-blue); box-shadow: 0 0 10px rgba(110, 168, 255, 0.4);"></div>
            <div class="relic-hud-card" style="padding: 1.4rem;">
              <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px; flex-wrap: wrap; gap: 6px;">
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
         09 — CURRENT STATUS & FATE
         ============================================================== -->
    <section style="margin-bottom: 4.5rem;">
      <h2 class="mono-label" style="font-size: 0.85rem; color: var(--accent-cyan); margin-bottom: 1.5rem; letter-spacing: 0.2em;">
        [07] OPERATIONAL STATUS & FATE
      </h2>
      <?php 
        $statusUpper = strtoupper($eq['current_status']);
        $borderColor = ($statusUpper === 'OPERATIONAL' || $statusUpper === 'ACTIVE') 
          ? 'var(--accent-emerald)' 
          : (($statusUpper === 'LOST' || $statusUpper === 'DESTROYED') ? 'var(--accent-rose)' : 'var(--accent-orange)');
      ?>
      <div class="relic-hud-card" style="border-left: 3px solid <?= $borderColor ?>; padding: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1.5rem;">
          <div style="max-width: 800px;">
            <div class="mono-label" style="color: <?= $borderColor ?>;">
              MISSION STATUS: <?= $statusUpper ?> // <?= $eq['is_relic'] ? 'CLASSIFICATION: OFF-WORLD RELIC' : 'ACTIVE FLIGHT PLATFORM' ?>
            </div>
            <h3 style="font-size: 1.8rem; font-weight: 800; color: #fff; margin: 6px 0;">
              LATEST VERIFIED REGION: <?= htmlspecialchars($eq['primary_region'] ?? $eq['d_name']) ?>
            </h3>
            <div style="font-size: 1rem; color: var(--text-primary); margin-top: 0.4rem;">
              Coordinates / Site: <strong><?= htmlspecialchars($eq['current_location']) ?></strong>
            </div>
            
            <?php if (!empty($eq['fate'])): ?>
              <div style="background: rgba(5,7,11,0.6); padding: 12px 16px; border-radius: 2px; border-left: 2px solid <?= $borderColor ?>; margin-top: 1rem;">
                <div class="mono-label" style="color: var(--accent-cyan); font-size: 0.68rem; margin-bottom: 4px;">FINAL FATE & DISPOSITION:</div>
                <p style="font-size: 0.92rem; color: var(--text-secondary); line-height: 1.6; margin: 0;">
                  <?= nl2br(htmlspecialchars($eq['fate'])) ?>
                </p>
              </div>
            <?php endif; ?>
          </div>

          <div style="text-align: right;">
            <div class="mono-label" style="color: var(--text-muted);">TELEMETRY INTEGRITY</div>
            <div class="mono-value" style="font-size: 1.1rem; color: var(--accent-cyan); font-weight: 700;">
              LAST VERIFIED: <?= formatTelemetryDate($eq['last_verified'] ?? '2026-03-20') ?>
            </div>
            <div class="mono-label" style="font-size: 0.65rem; color: var(--text-muted); margin-top: 4px;">
              SOURCE: <?= strtoupper(htmlspecialchars($eq['a_code'] ?? 'OFFICIAL AGENCY')) ?> ARCHIVAL REGISTRY
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
        [08] LEGACY & HISTORICAL IMPACT
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
        [09] VERIFIED ARCHIVAL SOURCES & REPOSITORIES (<?= count($sources) ?>)
      </h2>
      <div style="display: flex; flex-direction: column; gap: 0.8rem;">
        <?php foreach ($sources as $s): ?>
          <a href="<?= htmlspecialchars($s['source_url']) ?>" target="_blank" rel="noopener" class="search-item-card">
            <div>
              <div style="font-weight: 700; color: #fff; font-size: 0.95rem;">
                <?= htmlspecialchars($s['source_name']) ?>
              </div>
              <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;">
                Organization: <strong><?= htmlspecialchars($s['organization'] ?? 'Official Registry') ?></strong> // <?= htmlspecialchars($s['description'] ?? 'Authoritative Record') ?>
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
                Mission: <?= htmlspecialchars($rel['mission_name']) ?> // <?= htmlspecialchars($rel['destination_name']) ?>
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
