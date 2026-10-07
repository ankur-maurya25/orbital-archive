<?php
/**
 * ORBITAL ARCHIVE - Mission Detail Exhibit Page
 * Displays deep museum-style documentation for a specific mission
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 7; // Default to Opportunity if unspecified

$stmt = $pdo->prepare("
  SELECT m.*, 
         a.name AS agency_name, a.short_name AS agency_code, a.country AS agency_country, a.website_url AS agency_site,
         d.name AS destination_name, d.type AS destination_type, d.distance_from_earth
  FROM missions m
  LEFT JOIN agencies a ON m.agency_id = a.id
  LEFT JOIN destinations d ON m.destination_id = d.id
  WHERE m.id = :id
");
$stmt->execute(['id' => $id]);
$mission = $stmt->fetch();

if (!$mission) {
  http_response_code(404);
  require_once __DIR__ . '/404.php';
  exit;
}

// Fetch equipment for this mission
$eqStmt = $pdo->prepare("SELECT * FROM equipment WHERE mission_id = :id");
$eqStmt->execute(['id' => $id]);
$equipmentList = $eqStmt->fetchAll();

// Fetch images
$imgStmt = $pdo->prepare("SELECT * FROM images WHERE mission_id = :id");
$imgStmt->execute(['id' => $id]);
$images = $imgStmt->fetchAll();

// Fetch sources
$srcStmt = $pdo->prepare("SELECT * FROM sources WHERE mission_id = :id");
$srcStmt->execute(['id' => $id]);
$sources = $srcStmt->fetchAll();

$pageTitle = htmlspecialchars($mission['name']) . " // Mission Exhibit — ORBITAL ARCHIVE";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 7rem;">
  <div style="max-width: 1200px; margin: 0 auto; padding: 2rem 2rem 6rem;">
    
    <!-- Top breadcrumbs & back link -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <a href="missions.php" class="relic-cta-link">
        ← BACK TO ALL MISSIONS
      </a>
      <div class="mono-label">
        MISSION TELEMETRY ID // <?= sprintf('%04d', $mission['id']) ?>
      </div>
    </div>

    <!-- Header Exhibition Banner -->
    <div style="border-left: 3px solid var(--accent-blue); padding-left: 1.5rem; margin-bottom: 3rem;">
      <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 0.4rem;">
        <?= htmlspecialchars($mission['agency_code']) ?> // <?= strtoupper(htmlspecialchars($mission['destination_name'])) ?>
      </div>
      <h1 style="font-size: 3.5rem; font-weight: 800; letter-spacing: -0.01em; color: #fff; text-transform: uppercase;">
        <?= htmlspecialchars($mission['name']) ?>
      </h1>
      <div style="font-size: 1.1rem; color: var(--text-secondary); margin-top: 0.5rem;">
        <?= htmlspecialchars($mission['official_name'] ?? $mission['name']) ?>
      </div>
    </div>

    <!-- Telemetry Key Stats Strip -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; background: rgba(11, 17, 24, 0.85); border: 1px solid var(--border-color); padding: 1.5rem 2rem; border-radius: 4px; margin-bottom: 3.5rem;">
      <div>
        <div class="relic-meta-label">LAUNCH DATE</div>
        <div class="mono-value" style="font-size: 1.1rem; color: #fff; margin-top: 4px;">
          <?= formatTelemetryDate($mission['launch_date']) ?>
        </div>
      </div>
      <div>
        <div class="relic-meta-label">LAUNCH VEHICLE</div>
        <div class="mono-value" style="font-size: 1.1rem; color: #fff; margin-top: 4px;">
          <?= htmlspecialchars($mission['launch_vehicle'] ?? 'TBD') ?>
        </div>
      </div>
      <div>
        <div class="relic-meta-label">DESTINATION</div>
        <div class="mono-value" style="font-size: 1.1rem; color: var(--accent-orange); margin-top: 4px;">
          <?= htmlspecialchars($mission['destination_name']) ?>
        </div>
      </div>
      <div>
        <div class="relic-meta-label">STATUS</div>
        <div class="mono-value" style="font-size: 1.1rem; color: var(--accent-cyan); margin-top: 4px;">
          <?= strtoupper(htmlspecialchars($mission['status'])) ?>
        </div>
      </div>
    </div>

    <!-- Main Exhibition Sections -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3.5rem;">
      <div>
        <!-- Section 1: The Mission -->
        <section style="margin-bottom: 3rem;">
          <h2 class="mono-label" style="font-size: 0.9rem; color: var(--accent-blue); margin-bottom: 1rem;">
            [01] THE MISSION
          </h2>
          <p style="font-size: 1.15rem; line-height: 1.8; color: var(--text-primary); margin-bottom: 1.5rem;">
            <?= nl2br(htmlspecialchars($mission['objective'] ?? '')) ?>
          </p>
          <p style="font-size: 1.05rem; line-height: 1.7; color: var(--text-secondary);">
            <?= nl2br(htmlspecialchars($mission['description'] ?? '')) ?>
          </p>
        </section>

        <!-- Section 2: Associated Equipment & Relics -->
        <section style="margin-bottom: 3rem;">
          <h2 class="mono-label" style="font-size: 0.9rem; color: var(--accent-blue); margin-bottom: 1.5rem;">
            [02] DEPLOYED EQUIPMENT & LANDERS
          </h2>
          <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($equipmentList as $eq): ?>
              <div style="background: rgba(16, 26, 38, 0.6); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 4px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                  <div>
                    <h3 style="font-size: 1.3rem; font-weight: 700; color: #fff;">
                      <?= htmlspecialchars($eq['name']) ?>
                    </h3>
                    <div class="mono-label" style="color: var(--accent-orange); margin: 4px 0;">
                      <?= htmlspecialchars($eq['type']) ?> <span class="hud-bracket">//</span> <?= htmlspecialchars($eq['current_status']) ?>
                    </div>
                  </div>
                  <?php if ($eq['is_relic']): ?>
                    <span style="font-family: var(--font-mono); font-size: 0.68rem; padding: 2px 8px; border: 1px solid var(--accent-cyan); color: var(--accent-cyan); border-radius: 2px;">
                      OFF-WORLD RELIC
                    </span>
                  <?php endif; ?>
                </div>
                <p style="font-size: 0.95rem; color: var(--text-secondary); margin: 0.8rem 0;">
                  <?= htmlspecialchars($eq['description']) ?>
                </p>
                <a href="equipment.php?id=<?= $eq['id'] ?>" class="relic-cta-link" style="font-size: 0.75rem;">
                  INSPECT EQUIPMENT TELEMETRY →
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- Section 3: Discoveries & Legacy -->
        <section style="margin-bottom: 3rem;">
          <h2 class="mono-label" style="font-size: 0.9rem; color: var(--accent-blue); margin-bottom: 1rem;">
            [03] DISCOVERIES & LEGACY
          </h2>
          <div style="background: rgba(11, 17, 24, 0.7); border-left: 3px solid var(--accent-cyan); padding: 1.5rem; border-radius: 0 4px 4px 0;">
            <p style="font-size: 1.05rem; line-height: 1.7; color: var(--text-primary);">
              <?= nl2br(htmlspecialchars($mission['legacy'] ?? 'Archived scientific breakthrough for human space flight.')) ?>
            </p>
          </div>
        </section>

        <!-- Official Sources -->
        <section>
          <h2 class="mono-label" style="font-size: 0.9rem; color: var(--accent-blue); margin-bottom: 1rem;">
            [04] VERIFIED ARCHIVAL SOURCES
          </h2>
          <div style="display: flex; flex-direction: column; gap: 0.6rem;">
            <?php if (!empty($sources)): ?>
              <?php foreach ($sources as $s): ?>
                <a href="<?= htmlspecialchars($s['source_url']) ?>" target="_blank" rel="noopener" class="search-item-card">
                  <div>
                    <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($s['source_name']) ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary);"><?= htmlspecialchars($s['description'] ?? 'Official Registry') ?></div>
                  </div>
                  <span class="mono-label" style="color: var(--accent-cyan);">OFFICIAL LINK ↗</span>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="search-item-card">
                <div>
                  <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($mission['agency_name']) ?> Public Archives</div>
                  <div style="font-size: 0.75rem; color: var(--text-secondary);">Direct flight dynamics & telemetry records</div>
                </div>
                <a href="<?= htmlspecialchars($mission['agency_site'] ?? 'https://www.nasa.gov') ?>" target="_blank" class="mono-label" style="color: var(--accent-cyan);">AGENCY PORTAL ↗</a>
              </div>
            <?php endif; ?>
          </div>
        </section>
      </div>

      <!-- Right Sidebar: Trajectory & Spaceport Telemetry -->
      <div>
        <div class="relic-hud-card" style="position: sticky; top: 7rem;">
          <div class="mono-label" style="color: var(--accent-cyan);">LAUNCH SPECIFICATIONS</div>
          
          <div class="relic-meta-row">
            <span class="relic-meta-label">SPACEPORT</span>
            <span class="relic-meta-val" style="font-size: 0.75rem;"><?= htmlspecialchars($mission['launch_location'] ?? 'KSC, FL') ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">TRAJECTORY</span>
            <span class="relic-meta-val"><?= htmlspecialchars($mission['trajectory_type'] ?? 'Robotic probe') ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">AGENCY</span>
            <span class="relic-meta-val"><?= htmlspecialchars($mission['agency_name']) ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">DISTANCE FROM EARTH</span>
            <span class="relic-meta-val"><?= htmlspecialchars($mission['distance_from_earth'] ?? 'Varies') ?></span>
          </div>

          <div style="padding-top: 1rem; border-top: 1px solid var(--border-color);">
            <div class="mono-label" style="margin-bottom: 0.5rem;">QUICK ACTIONS</div>
            <a href="explore.php" class="cta-button" style="width: 100%; justify-content: center; font-size: 0.72rem; padding: 10px;">
              SEARCH RELATED MISSIONS
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
