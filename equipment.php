<?php
/**
 * ORBITAL ARCHIVE - Equipment & Relic Detail Exhibit
 * Sections: What Was It? What Did It Do? What Did It Discover? Where Is It Now? Why Does It Matter?
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 2; // Default to Apollo 15 LRV

$stmt = $pdo->prepare("
  SELECT e.*, 
         m.id AS mission_id, m.name AS mission_name, m.launch_date, m.launch_vehicle,
         a.name AS agency_name, a.short_name AS agency_code, a.country AS agency_country,
         d.name AS destination_name, d.distance_from_earth
  FROM equipment e
  JOIN missions m ON e.mission_id = m.id
  LEFT JOIN agencies a ON m.agency_id = a.id
  LEFT JOIN destinations d ON m.destination_id = d.id
  WHERE e.id = :id
");
$stmt->execute(['id' => $id]);
$eq = $stmt->fetch();

if (!$eq) {
  header('Location: relics.php');
  exit;
}

// Fetch images
$imgStmt = $pdo->prepare("SELECT * FROM images WHERE equipment_id = :id OR mission_id = :mid");
$imgStmt->execute(['id' => $id, 'mid' => $eq['mission_id']]);
$images = $imgStmt->fetchAll();

// Fetch sources
$srcStmt = $pdo->prepare("SELECT * FROM sources WHERE equipment_id = :id OR mission_id = :mid");
$srcStmt->execute(['id' => $id, 'mid' => $eq['mission_id']]);
$sources = $srcStmt->fetchAll();

$pageTitle = htmlspecialchars($eq['name']) . " // Equipment Exhibit — ORBITAL ARCHIVE";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 7rem;">
  <div style="max-width: 1200px; margin: 0 auto; padding: 2rem 2rem 6rem;">
    
    <!-- Top breadcrumb -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <a href="relics.php" class="relic-cta-link">
        ← BACK TO RELICS VAULT
      </a>
      <div class="mono-label">
        EQUIPMENT SPEC // <?= htmlspecialchars($eq['relic_category'] ?? 'OFF-WORLD HARDWARE') ?>
      </div>
    </div>

    <!-- Header Exhibition Banner -->
    <div style="border-left: 3px solid var(--accent-orange); padding-left: 1.5rem; margin-bottom: 3rem;">
      <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 0.4rem;">
        <?= htmlspecialchars($eq['agency_code']) ?> // MISSION: <?= htmlspecialchars($eq['mission_name']) ?>
      </div>
      <h1 style="font-size: 3.5rem; font-weight: 800; letter-spacing: -0.01em; color: #fff; text-transform: uppercase;">
        <?= htmlspecialchars($eq['name']) ?>
      </h1>
      <div style="font-size: 1.1rem; color: var(--text-secondary); margin-top: 0.5rem;">
        <?= htmlspecialchars($eq['official_name'] ?? $eq['name']) ?>
      </div>
    </div>

    <!-- Metadata Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; background: rgba(11, 17, 24, 0.85); border: 1px solid var(--border-color); padding: 1.5rem 2rem; border-radius: 4px; margin-bottom: 3.5rem;">
      <div>
        <div class="relic-meta-label">HARDWARE TYPE</div>
        <div class="mono-value" style="font-size: 1.1rem; color: #fff; margin-top: 4px;">
          <?= htmlspecialchars($eq['type']) ?>
        </div>
      </div>
      <div>
        <div class="relic-meta-label">OPERATIONAL LIFESPAN</div>
        <div class="mono-value" style="font-size: 1.1rem; color: #fff; margin-top: 4px;">
          <?= htmlspecialchars($eq['operational_period'] ?? 'Cataloged') ?>
        </div>
      </div>
      <div>
        <div class="relic-meta-label">CURRENT LOCATION</div>
        <div class="mono-value" style="font-size: 1.1rem; color: var(--accent-orange); margin-top: 4px;">
          <?= htmlspecialchars($eq['current_location']) ?>
        </div>
      </div>
      <div>
        <div class="relic-meta-label">CURRENT STATUS</div>
        <div class="mono-value" style="font-size: 1.1rem; color: var(--accent-cyan); margin-top: 4px;">
          <?= strtoupper(htmlspecialchars($eq['current_status'])) ?>
        </div>
      </div>
    </div>

    <!-- 5 Questions Exhibition Grid -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3.5rem;">
      <div>
        <!-- 1. WHAT WAS IT? -->
        <section style="margin-bottom: 3rem;">
          <h2 class="mono-label" style="font-size: 0.9rem; color: var(--accent-orange); margin-bottom: 1rem;">
            [01] WHAT WAS IT?
          </h2>
          <p style="font-size: 1.15rem; line-height: 1.8; color: var(--text-primary);">
            <?= nl2br(htmlspecialchars($eq['description'] ?? '')) ?>
          </p>
        </section>

        <!-- 2. WHAT DID IT DO? -->
        <section style="margin-bottom: 3rem;">
          <h2 class="mono-label" style="font-size: 0.9rem; color: var(--accent-orange); margin-bottom: 1rem;">
            [02] WHAT DID IT DO?
          </h2>
          <p style="font-size: 1.05rem; line-height: 1.7; color: var(--text-secondary); margin-bottom: 1rem;">
            <?= nl2br(htmlspecialchars($eq['purpose'] ?? 'Conducted scientific exploration and off-world operations.')) ?>
          </p>
          <?php if (!empty($eq['technology'])): ?>
            <div style="background: rgba(16, 26, 38, 0.4); border: 1px solid var(--border-color); padding: 1.2rem; border-radius: 4px; margin-top: 1rem;">
              <div class="mono-label" style="color: var(--accent-blue); margin-bottom: 6px;">SUBSYSTEM TECHNOLOGY</div>
              <div style="font-size: 0.9rem; color: var(--text-primary);"><?= htmlspecialchars($eq['technology']) ?></div>
            </div>
          <?php endif; ?>
        </section>

        <!-- 3. WHAT DID IT DISCOVER? -->
        <section style="margin-bottom: 3rem;">
          <h2 class="mono-label" style="font-size: 0.9rem; color: var(--accent-orange); margin-bottom: 1rem;">
            [03] WHAT DID IT DISCOVER?
          </h2>
          <div style="background: rgba(11, 17, 24, 0.7); border-left: 3px solid var(--accent-cyan); padding: 1.5rem; border-radius: 0 4px 4px 0;">
            <p style="font-size: 1.05rem; line-height: 1.7; color: var(--text-primary);">
              <?= nl2br(htmlspecialchars($eq['discoveries'] ?? 'Enabled critical off-world discoveries for humankind.')) ?>
            </p>
          </div>
        </section>

        <!-- 4. WHERE IS IT NOW? -->
        <section style="margin-bottom: 3rem;">
          <h2 class="mono-label" style="font-size: 0.9rem; color: var(--accent-orange); margin-bottom: 1rem;">
            [04] WHERE IS IT NOW?
          </h2>
          <p style="font-size: 1.05rem; line-height: 1.7; color: var(--text-secondary);">
            Coordinates & Site: <strong style="color: #fff;"><?= htmlspecialchars($eq['current_location']) ?></strong>.
            Status recorded as <strong style="color: var(--accent-orange);"><?= htmlspecialchars($eq['current_status']) ?></strong>.
          </p>
        </section>

        <!-- 5. WHY DOES IT MATTER? -->
        <section style="margin-bottom: 3rem;">
          <h2 class="mono-label" style="font-size: 0.9rem; color: var(--accent-orange); margin-bottom: 1rem;">
            [05] WHY DOES IT MATTER?
          </h2>
          <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-primary);">
            <?= nl2br(htmlspecialchars($eq['legacy'] ?? 'A lasting monument to human curiosity and extraterrestrial endeavor.')) ?>
          </p>
        </section>
      </div>

      <!-- Right Column: Relic Sidebar & Links -->
      <div>
        <div class="relic-hud-card" style="position: sticky; top: 7rem;">
          <div class="mono-label" style="color: var(--accent-cyan);">PARENT MISSION PROFILE</div>
          
          <div class="relic-meta-row">
            <span class="relic-meta-label">MISSION</span>
            <span class="relic-meta-val"><?= htmlspecialchars($eq['mission_name']) ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">AGENCY</span>
            <span class="relic-meta-val"><?= htmlspecialchars($eq['agency_name']) ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">DESTINATION</span>
            <span class="relic-meta-val"><?= htmlspecialchars($eq['destination_name']) ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">IS RELIC?</span>
            <span class="relic-meta-val" style="color: var(--accent-cyan);"><?= $eq['is_relic'] ? 'YES [CATALOGED]' : 'NO' ?></span>
          </div>

          <div style="padding-top: 1rem; border-top: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 8px;">
            <a href="mission.php?id=<?= $eq['mission_id'] ?>" class="cta-button" style="justify-content: center; font-size: 0.72rem; padding: 10px;">
              VIEW PARENT MISSION LOG →
            </a>
            <a href="relics.php" class="relic-cta-link" style="justify-content: center; font-size: 0.72rem;">
              EXPLORE ALL 18 RELICS
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
