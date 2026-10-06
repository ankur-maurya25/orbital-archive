<?php
/**
 * ORBITAL ARCHIVE - Off-World Relics Vault
 * The Machines We Left Behind
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

$stmt = $pdo->query("
  SELECT e.*, m.name AS mission_name, YEAR(m.launch_date) AS launch_year,
         a.short_name AS agency_code, d.name AS destination_name,
         COALESCE((SELECT image_url FROM images WHERE equipment_id = e.id LIMIT 1),
                  (SELECT image_url FROM images WHERE mission_id = m.id LIMIT 1),
                  'https://images-assets.nasa.gov/image/as15-88-11866/as15-88-11866~orig.jpg') AS image_url
  FROM equipment e
  JOIN missions m ON e.mission_id = m.id
  LEFT JOIN agencies a ON m.agency_id = a.id
  LEFT JOIN destinations d ON m.destination_id = d.id
  WHERE e.is_relic = 1
  ORDER BY FIELD(e.relic_category, 'Lunar Relic', 'Martian Relic', 'Deep Space Relic') ASC, e.id ASC
");
$relics = $stmt->fetchAll();

$pageTitle = 'The Machines We Left Behind — Off-World Relics Vault — ORBITAL ARCHIVE';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 7rem;">
  <div style="max-width: 1300px; margin: 0 auto; padding: 2rem 2rem 6rem;">
    
    <div style="margin-bottom: 3rem;">
      <div class="mono-label" style="color: var(--accent-orange); margin-bottom: 0.5rem;">
        NASA SPACE APPS // THE MACHINES WE LEFT BEHIND
      </div>
      <h1 style="font-size: 3.5rem; font-weight: 800; color: #fff; text-transform: uppercase;">
        OFF-WORLD RELICS VAULT
      </h1>
      <p style="color: var(--text-secondary); max-width: 750px; margin-top: 0.8rem; font-size: 1.1rem; line-height: 1.7;">
        Some completed their mission. Some stopped transmitting. Some are still where humanity left them.<br>
        A permanent registry of human machinery resting on the Moon, Mars, comets, and interstellar space.
      </p>
    </div>

    <!-- Relics Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 2rem;">
      <?php foreach ($relics as $r): ?>
        <div class="relic-hud-card">
          <!-- Thumbnail Image -->
          <div style="height: 220px; border-radius: 2px; overflow: hidden; margin-bottom: 1.2rem; position: relative;">
            <img src="<?= htmlspecialchars($r['image_url']) ?>" alt="<?= htmlspecialchars($r['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <div style="position: absolute; top: 10px; right: 10px; background: rgba(5,7,11,0.85); border: 1px solid var(--accent-cyan); padding: 2px 8px; border-radius: 2px;">
              <span class="mono-label" style="color: var(--accent-cyan); font-size: 0.65rem;"><?= htmlspecialchars($r['relic_category']) ?></span>
            </div>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span class="mono-label" style="color: var(--accent-blue);"><?= htmlspecialchars($r['agency_code']) ?> // MISSION: <?= htmlspecialchars($r['mission_name']) ?></span>
              <h2 style="font-size: 1.4rem; font-weight: 700; color: #fff; margin: 4px 0;">
                <a href="equipment.php?id=<?= $r['id'] ?>" style="color: inherit;">
                  <?= htmlspecialchars($r['name']) ?>
                </a>
              </h2>
            </div>
          </div>

          <p style="font-size: 0.88rem; color: var(--text-secondary); margin: 0.8rem 0; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
            <?= htmlspecialchars($r['description']) ?>
          </p>

          <div class="relic-meta-row">
            <span class="relic-meta-label">RESTING LOCATION</span>
            <span class="relic-meta-val" style="color: var(--accent-orange); font-size: 0.78rem;"><?= htmlspecialchars($r['current_location']) ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">STATUS</span>
            <span class="relic-meta-val"><?= strtoupper(htmlspecialchars($r['current_status'])) ?></span>
          </div>

          <div style="margin-top: 1.2rem; padding-top: 0.8rem; border-top: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center;">
            <a href="equipment.php?id=<?= $r['id'] ?>" class="relic-cta-link" style="font-size: 0.75rem;">
              INSPECT RELIC DOSSIER →
            </a>
            <span class="mono-label" style="color: var(--text-muted); font-size: 0.68rem;">
              YEAR: <?= htmlspecialchars($r['launch_year']) ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
