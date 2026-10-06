<?php
/**
 * ORBITAL ARCHIVE - Missions Directory
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

$stmt = $pdo->query("
  SELECT m.*, a.short_name AS agency_code, a.country AS agency_country, d.name AS destination_name
  FROM missions m
  LEFT JOIN agencies a ON m.agency_id = a.id
  LEFT JOIN destinations d ON m.destination_id = d.id
  ORDER BY m.launch_date DESC
");
$missions = $stmt->fetchAll();

$pageTitle = 'Global Missions Registry — ORBITAL ARCHIVE';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 7rem;">
  <div style="max-width: 1300px; margin: 0 auto; padding: 2rem 2rem 6rem;">
    
    <div style="margin-bottom: 3rem;">
      <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 0.5rem;">
        REGISTRY // HUMAN SPACEFLIGHT & PROBES
      </div>
      <h1 style="font-size: 3.2rem; font-weight: 800; color: #fff; text-transform: uppercase;">
        GLOBAL MISSIONS DIRECTORY
      </h1>
      <p style="color: var(--text-secondary); max-width: 700px; margin-top: 0.5rem;">
        Official flight dossiers for humanity's 30 defining orbital, lunar, interplanetary, and deep-space missions.
      </p>
    </div>

    <!-- Missions Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 1.8rem;">
      <?php foreach ($missions as $m): ?>
        <div class="relic-hud-card">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span class="mono-label" style="color: var(--accent-blue);"><?= htmlspecialchars($m['agency_code']) ?></span>
              <h2 style="font-size: 1.45rem; font-weight: 700; color: #fff; margin: 4px 0;">
                <a href="mission.php?id=<?= $m['id'] ?>" style="color: inherit;">
                  <?= htmlspecialchars($m['name']) ?>
                </a>
              </h2>
              <div class="mono-label" style="font-size: 0.65rem; color: var(--text-muted);">
                <?= htmlspecialchars($m['official_name'] ?? '') ?>
              </div>
            </div>
            <span class="mono-label" style="color: <?= strtolower($m['status']) === 'active' ? 'var(--accent-emerald)' : 'var(--text-secondary)' ?>;">
              <?= strtoupper(htmlspecialchars($m['status'])) ?>
            </span>
          </div>

          <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 1rem 0; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
            <?= htmlspecialchars($m['description']) ?>
          </p>

          <div class="relic-meta-row">
            <span class="relic-meta-label">DESTINATION</span>
            <span class="relic-meta-val" style="color: var(--accent-orange);"><?= htmlspecialchars($m['destination_name']) ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">LAUNCH VEHICLE</span>
            <span class="relic-meta-val"><?= htmlspecialchars($m['launch_vehicle']) ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">LAUNCH DATE</span>
            <span class="relic-meta-val"><?= formatTelemetryDate($m['launch_date']) ?></span>
          </div>

          <div style="margin-top: 1.2rem; padding-top: 0.8rem; border-top: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center;">
            <a href="mission.php?id=<?= $m['id'] ?>" class="relic-cta-link" style="font-size: 0.75rem;">
              EXPLORE EXHIBIT →
            </a>
            <span class="mono-label" style="color: var(--text-muted); font-size: 0.68rem;">
              TYPE: <?= strtoupper(htmlspecialchars($m['trajectory_type'])) ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
