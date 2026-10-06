<?php
/**
 * ORBITAL ARCHIVE - Humanity's Flight Timeline
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$events = $pdo->query("
  SELECT t.*, m.name AS mission_name, a.short_name AS agency_code, e.name AS equipment_name, e.type AS equipment_type
  FROM timeline_events t
  LEFT JOIN missions m ON t.mission_id = m.id
  LEFT JOIN agencies a ON m.agency_id = a.id
  LEFT JOIN equipment e ON t.equipment_id = e.id
  ORDER BY t.year ASC
")->fetchAll();

$pageTitle = "Humanity's Flight Timeline — ORBITAL ARCHIVE";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 7rem;">
  <div style="max-width: 1100px; margin: 0 auto; padding: 2rem 2rem 6rem;">
    
    <div style="margin-bottom: 3.5rem; text-align: center;">
      <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 0.5rem;">
        CHRONICLE // 1957–PRESENT
      </div>
      <h1 style="font-size: 3.5rem; font-weight: 800; color: #fff; text-transform: uppercase;">
        HUMANITY'S FLIGHT TIMELINE
      </h1>
      <p style="color: var(--text-secondary); max-width: 650px; margin: 0.8rem auto 0; font-size: 1.05rem;">
        A chronological journey through the decisive moments that transformed humankind into a multi-planetary species.
      </p>
    </div>

    <!-- Vertical Timeline Stream -->
    <div style="position: relative; padding-left: 3rem; border-left: 1px solid var(--border-color); margin-left: 2rem;">
      <?php foreach ($events as $ev): ?>
        <div style="position: relative; margin-bottom: 3.5rem;">
          <!-- Timeline Node Pip -->
          <div style="position: absolute; left: -3.55rem; top: 0.2rem; width: 16px; height: 16px; border-radius: 50%; background: var(--bg-primary); border: 2px solid var(--accent-blue); box-shadow: 0 0 10px rgba(110, 168, 255, 0.4);"></div>

          <div class="relic-hud-card" style="padding: 1.8rem;">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.6rem;">
              <span style="font-family: var(--font-mono); font-size: 1.8rem; font-weight: 800; color: var(--accent-cyan);">
                <?= $ev['year'] ?>
              </span>
              <span class="mono-label" style="color: var(--accent-orange);">
                <?= htmlspecialchars($ev['agency_code'] ?? 'GLOBAL') ?> // <?= htmlspecialchars($ev['importance']) ?>
              </span>
            </div>

            <h2 style="font-size: 1.45rem; font-weight: 700; color: #fff; margin-bottom: 0.8rem;">
              <?= htmlspecialchars($ev['title']) ?>
            </h2>

            <p style="font-size: 1rem; color: var(--text-secondary); line-height: 1.7; margin-bottom: 1.2rem;">
              <?= htmlspecialchars($ev['description']) ?>
            </p>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255, 255, 255, 0.05); padding-top: 0.8rem;">
              <?php if (!empty($ev['mission_id'])): ?>
                <a href="mission.php?id=<?= $ev['mission_id'] ?>" class="relic-cta-link" style="font-size: 0.75rem;">
                  INSPECT MISSION DOSSIER →
                </a>
              <?php else: ?>
                <span class="mono-label">HISTORIC MILESTONE</span>
              <?php endif; ?>

              <?php if (!empty($ev['equipment_id'])): ?>
                <a href="equipment.php?id=<?= $ev['equipment_id'] ?>" style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--text-secondary);">
                  VIEW HARDWARE (<?= htmlspecialchars($ev['equipment_name']) ?>) →
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
