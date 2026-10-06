<?php
/**
 * ORBITAL ARCHIVE - Planetary Destinations Directory
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$destinations = $pdo->query("
  SELECT d.*, COUNT(m.id) AS actual_missions 
  FROM destinations d
  LEFT JOIN missions m ON d.id = m.destination_id
  GROUP BY d.id
  ORDER BY d.id ASC
")->fetchAll();

$pageTitle = 'Planetary Destinations — ORBITAL ARCHIVE';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 7rem;">
  <div style="max-width: 1300px; margin: 0 auto; padding: 2rem 2rem 6rem;">
    
    <div style="margin-bottom: 3rem;">
      <div class="mono-label" style="color: var(--accent-orange); margin-bottom: 0.5rem;">
        CELESTIAL TARGETS // SOLAR SYSTEM & BEYOND
      </div>
      <h1 style="font-size: 3.2rem; font-weight: 800; color: #fff; text-transform: uppercase;">
        PLANETARY DESTINATIONS
      </h1>
      <p style="color: var(--text-secondary); max-width: 700px; margin-top: 0.5rem;">
        Every world, asteroid, and interstellar frontier reached by human spacecraft since 1957.
      </p>
    </div>

    <!-- Destinations Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 2rem;">
      <?php foreach ($destinations as $d): ?>
        <div class="relic-hud-card">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span class="mono-label" style="color: var(--accent-blue);"><?= htmlspecialchars($d['type']) ?></span>
              <h2 style="font-size: 1.6rem; font-weight: 800; color: #fff; margin: 4px 0;">
                <?= htmlspecialchars($d['name']) ?>
              </h2>
            </div>
            <div style="text-align: right;">
              <span class="mono-label" style="display: block;">RECORDED</span>
              <span class="mono-value" style="font-size: 1.2rem; color: var(--accent-orange);">
                <?= $d['actual_missions'] ?>
              </span>
            </div>
          </div>

          <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 1.2rem 0; line-height: 1.6;">
            <?= htmlspecialchars($d['description']) ?>
          </p>

          <div class="relic-meta-row">
            <span class="relic-meta-label">DISTANCE FROM EARTH</span>
            <span class="relic-meta-val" style="color: #fff;"><?= htmlspecialchars($d['distance_from_earth']) ?></span>
          </div>

          <div style="margin-top: 1.2rem; padding-top: 0.8rem; border-top: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center;">
            <a href="explore.php?destination=<?= $d['id'] ?>" class="relic-cta-link" style="font-size: 0.75rem;">
              EXPLORE MISSIONS TO <?= strtoupper(htmlspecialchars($d['name'])) ?> →
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
