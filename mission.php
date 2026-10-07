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
$eqStmt = $pdo->prepare("SELECT * FROM equipment WHERE mission_id = :id ORDER BY is_relic DESC, id ASC");
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

// Distance Specification Label Logic
$destNameUpper = strtoupper(trim($mission['destination_name'] ?? ''));
if ($destNameUpper === 'MOON') {
    $distanceSpecLabel = 'AVERAGE EARTH–MOON DISTANCE';
} elseif (!empty($destNameUpper)) {
    $distanceSpecLabel = "AVERAGE EARTH–{$destNameUpper} DISTANCE";
} else {
    $distanceSpecLabel = 'AVERAGE DISTANCE FROM EARTH';
}

$pageTitle = htmlspecialchars($mission['name']) . " // Mission Exhibit — ORBITAL ARCHIVE";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 7rem;">
  <div class="mission-main-container">
    
    <!-- Top breadcrumbs & back link -->
    <div class="mission-top-nav">
      <a href="missions.php" class="relic-cta-link">
        ← BACK TO ALL MISSIONS
      </a>
      <div class="mono-label">
        MISSION TELEMETRY ID // <?= sprintf('%04d', $mission['id']) ?>
      </div>
    </div>

    <!-- Header Exhibition Banner -->
    <header class="mission-hero-header">
      <div class="mono-label mission-hero-agency">
        <?= htmlspecialchars($mission['agency_code']) ?> // <?= strtoupper(htmlspecialchars($mission['destination_name'])) ?>
      </div>
      <h1 class="mission-hero-title">
        <?= htmlspecialchars($mission['name']) ?>
      </h1>
      <div class="mission-hero-subtitle">
        <?= htmlspecialchars($mission['official_name'] ?? $mission['name']) ?>
      </div>
    </header>

    <!-- Telemetry Key Stats Strip -->
    <div class="mission-stats-strip">
      <div class="mission-stat-item">
        <div class="relic-meta-label">LAUNCH DATE</div>
        <div class="mono-value mission-stat-value">
          <?= formatTelemetryDate($mission['launch_date']) ?>
        </div>
      </div>
      <div class="mission-stat-item">
        <div class="relic-meta-label">LAUNCH VEHICLE</div>
        <div class="mono-value mission-stat-value">
          <?= htmlspecialchars($mission['launch_vehicle'] ?? 'TBD') ?>
        </div>
      </div>
      <div class="mission-stat-item">
        <div class="relic-meta-label">DESTINATION</div>
        <div class="mono-value mission-stat-value stat-dest">
          <?= htmlspecialchars($mission['destination_name']) ?>
        </div>
      </div>
      <div class="mission-stat-item">
        <div class="relic-meta-label">STATUS</div>
        <div class="mono-value mission-stat-value stat-status">
          <?= strtoupper(htmlspecialchars($mission['status'])) ?>
        </div>
      </div>
    </div>

    <!-- Main Exhibition Sections -->
    <div class="mission-layout-grid">
      <div>
        <!-- Section 1: The Mission -->
        <section class="mission-section">
          <h2 class="mono-label mission-section-title">
            [01] THE MISSION
          </h2>
          <p class="mission-body-objective">
            <?= nl2br(htmlspecialchars($mission['objective'] ?? '')) ?>
          </p>
          <p class="mission-body-description">
            <?= nl2br(htmlspecialchars($mission['description'] ?? '')) ?>
          </p>
        </section>

        <!-- Section 2: Associated Equipment & Relics -->
        <section class="mission-section">
          <h2 class="mono-label mission-section-title">
            [02] DEPLOYED EQUIPMENT & LANDERS
          </h2>
          <div class="mission-equipment-list">
            <?php if (!empty($equipmentList)): ?>
              <?php foreach ($equipmentList as $eq): ?>
                <article class="mission-equipment-card">
                  <header class="equipment-card-header">
                    <div class="equipment-card-title-group">
                      <h3 class="equipment-card-title">
                        <?= htmlspecialchars($eq['name']) ?>
                      </h3>
                      <div class="mono-label equipment-card-subtitle">
                        <span class="eq-type"><?= htmlspecialchars($eq['type']) ?></span>
                        <?php if (!empty($eq['primary_region'])): ?>
                          <span class="hud-bracket">//</span>
                          <span class="eq-region"><?= htmlspecialchars($eq['primary_region']) ?></span>
                        <?php endif; ?>
                      </div>
                    </div>
                    
                    <div class="equipment-badges">
                      <?php if (!empty($eq['is_relic']) && (int)$eq['is_relic'] === 1): ?>
                        <span class="badge-offworld-relic" title="Verified in-situ aerospace artifact resting beyond Earth">
                          OFF-WORLD RELIC
                        </span>
                      <?php endif; ?>
                      <?php if (!empty($eq['current_status'])): ?>
                        <?php
                          $statusClean = strtoupper(trim($eq['current_status']));
                          $statusClass = 'status-' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $statusClean));
                        ?>
                        <span class="badge-operational-status <?= $statusClass ?>" title="Equipment operational condition">
                          STATUS: <?= htmlspecialchars($statusClean) ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </header>

                  <p class="equipment-card-desc">
                    <?= nl2br(htmlspecialchars($eq['description'])) ?>
                  </p>

                  <!-- Explicit Separation: Location & Physical Fate -->
                  <?php if (!empty($eq['current_location']) || !empty($eq['fate'])): ?>
                    <div class="equipment-spec-grid">
                      <?php if (!empty($eq['current_location'])): ?>
                        <div class="equipment-spec-item">
                          <span class="equipment-spec-label">CURRENT LOCATION</span>
                          <span class="equipment-spec-value"><?= htmlspecialchars($eq['current_location']) ?></span>
                        </div>
                      <?php endif; ?>
                      <?php if (!empty($eq['fate'])): ?>
                        <div class="equipment-spec-item">
                          <span class="equipment-spec-label">PHYSICAL FATE / DISPOSITION</span>
                          <span class="equipment-spec-value"><?= htmlspecialchars($eq['fate']) ?></span>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <footer class="equipment-card-footer">
                    <a href="equipment.php?id=<?= (int)$eq['id'] ?>" class="equipment-inspect-btn">
                      <span>INSPECT EQUIPMENT TELEMETRY</span>
                      <span class="btn-arrow" aria-hidden="true">→</span>
                    </a>
                  </footer>
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="mission-legacy-box">
                <p class="mono-label" style="color: var(--text-muted); margin: 0;">
                  NO SPECIFIC HARDWARE SUB-SYSTEMS SEPARATELY CATALOGED FOR THIS MISSION
                </p>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <!-- Section 3: Discoveries & Legacy -->
        <section class="mission-section">
          <h2 class="mono-label mission-section-title">
            [03] DISCOVERIES & LEGACY
          </h2>
          <div class="mission-legacy-box">
            <p class="mission-legacy-text">
              <?= nl2br(htmlspecialchars($mission['legacy'] ?? 'Archived scientific breakthrough for human space flight.')) ?>
            </p>
          </div>
        </section>

        <!-- Section 4: Verified Archival Sources -->
        <section class="mission-section">
          <h2 class="mono-label mission-section-title">
            [04] VERIFIED ARCHIVAL SOURCES
          </h2>
          <div style="display: flex; flex-direction: column; gap: 0.6rem;">
            <?php if (!empty($sources)): ?>
              <?php foreach ($sources as $s): ?>
                <a href="<?= htmlspecialchars($s['source_url']) ?>" target="_blank" rel="noopener" class="search-item-card source-card-link">
                  <div>
                    <div class="source-card-title"><?= htmlspecialchars($s['source_name']) ?></div>
                    <div class="source-card-desc"><?= htmlspecialchars($s['description'] ?? 'Official Registry') ?></div>
                  </div>
                  <span class="mono-label source-card-action">OFFICIAL LINK ↗</span>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="search-item-card source-card-link">
                <div>
                  <div class="source-card-title"><?= htmlspecialchars($mission['agency_name']) ?> Public Archives</div>
                  <div class="source-card-desc">Direct flight dynamics & telemetry records</div>
                </div>
                <a href="<?= htmlspecialchars($mission['agency_site'] ?? 'https://www.nasa.gov') ?>" target="_blank" rel="noopener" class="mono-label source-card-action">AGENCY PORTAL ↗</a>
              </div>
            <?php endif; ?>
          </div>
        </section>
      </div>

      <!-- Right Sidebar: Trajectory & Spaceport Telemetry -->
      <div>
        <aside class="relic-hud-card" style="position: sticky; top: 7rem;">
          <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 0.5rem;">LAUNCH SPECIFICATIONS</div>
          
          <div class="relic-meta-row">
            <span class="relic-meta-label">SPACEPORT</span>
            <span class="relic-meta-val" style="font-size: 0.78rem;"><?= htmlspecialchars($mission['launch_location'] ?? 'KSC, FL') ?></span>
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
            <span class="relic-meta-label"><?= htmlspecialchars($distanceSpecLabel) ?></span>
            <span class="relic-meta-val"><?= htmlspecialchars($mission['distance_from_earth'] ?? 'Varies') ?></span>
          </div>

          <div style="padding-top: 1rem; border-top: 1px solid var(--border-color); margin-top: 0.5rem;">
            <div class="mono-label" style="margin-bottom: 0.5rem;">QUICK ACTIONS</div>
            <a href="explore.php" class="cta-button" style="width: 100%; justify-content: center; font-size: 0.75rem; padding: 10px;">
              SEARCH RELATED MISSIONS
            </a>
          </div>
        </aside>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
