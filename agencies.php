<?php
/**
 * ORBITAL ARCHIVE - Space Agencies Network Directory
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$selectedId = isset($_GET['id']) ? (int)$_GET['id'] : null;

$stmt = $pdo->query("
  SELECT a.*, 
         COUNT(m.id) AS total_missions,
         SUM(CASE WHEN LOWER(m.status) = 'active' THEN 1 ELSE 0 END) AS active_missions
  FROM agencies a
  LEFT JOIN missions m ON a.id = m.agency_id
  GROUP BY a.id
  ORDER BY total_missions DESC, a.id ASC
");
$agencies = $stmt->fetchAll();

$pageTitle = 'Space Agency Networks — ORBITAL ARCHIVE';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 7rem;">
  <div style="max-width: 1300px; margin: 0 auto; padding: 2rem 2rem 6rem;">
    
    <div style="margin-bottom: 3rem;">
      <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 0.5rem;">
        NETWORK // GLOBAL COLLABORATION & OPERATORS
      </div>
      <h1 style="font-size: 3.2rem; font-weight: 800; color: var(--text-bright); text-transform: uppercase;">
        SPACE AGENCY NETWORKS
      </h1>
      <p style="color: var(--text-secondary); max-width: 700px; margin-top: 0.5rem;">
        Profiles of national and commercial aerospace agencies conducting planetary exploration and scientific research.
      </p>
    </div>

    <!-- Agencies Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 2rem;">
      <?php foreach ($agencies as $a): 
        $isSelected = $selectedId === (int)$a['id'];
      ?>
        <div class="relic-hud-card" style="<?= $isSelected ? 'border-color: var(--accent-cyan); box-shadow: var(--glow-cyan);' : '' ?>">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span class="mono-label" style="color: var(--accent-cyan);"><?= htmlspecialchars($a['type']) ?></span>
              <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-bright); margin: 4px 0;">
                <?= htmlspecialchars($a['short_name']) ?>
              </h2>
              <div style="font-size: 0.85rem; color: var(--text-secondary);">
                <?= htmlspecialchars($a['country']) ?>
              </div>
            </div>
            <div style="text-align: right;">
              <span class="mono-label" style="display: block;">CATALOGED</span>
              <span class="mono-value" style="font-size: 1.2rem; color: var(--text-bright);"><?= $a['total_missions'] ?></span>
            </div>
          </div>

          <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 1.2rem 0; line-height: 1.6;">
            <?= htmlspecialchars($a['description']) ?>
          </p>

          <div class="relic-meta-row">
            <span class="relic-meta-label">FULL NAME</span>
            <span class="relic-meta-val" style="font-size: 0.78rem;"><?= htmlspecialchars($a['name']) ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">ACTIVE MISSIONS</span>
            <span class="relic-meta-val" style="color: var(--accent-emerald);"><?= (int)$a['active_missions'] ?></span>
          </div>

          <div style="margin-top: 1.2rem; padding-top: 0.8rem; border-top: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center;">
            <a href="explore.php?agency=<?= $a['id'] ?>" class="relic-cta-link" style="font-size: 0.75rem;">
              EXPLORE MISSIONS →
            </a>
            <a href="<?= htmlspecialchars($a['website_url']) ?>" target="_blank" rel="noopener" class="mono-label" style="color: var(--text-muted); font-size: 0.68rem;">
              OFFICIAL SITE ↗
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
