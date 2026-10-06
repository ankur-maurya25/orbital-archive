<?php
/**
 * ORBITAL ARCHIVE - Full Archive Explorer
 * Database-driven multi-filter catalog across missions and hardware
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = getDB();

$agencyFilter = isset($_GET['agency']) ? (int)$_GET['agency'] : null;
$destFilter = isset($_GET['destination']) ? (int)$_GET['destination'] : null;
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : null;
$search = isset($_GET['q']) ? trim($_GET['q']) : null;

// Fetch options for filters
$agencies = $pdo->query("SELECT id, name, short_name FROM agencies ORDER BY short_name ASC")->fetchAll();
$destinations = $pdo->query("SELECT id, name FROM destinations ORDER BY id ASC")->fetchAll();

// Build query
$sql = "
  SELECT m.id AS mission_id, m.name AS mission_name, m.official_name, m.launch_date, m.status, m.trajectory_type,
         a.short_name AS agency_code, a.country AS agency_country,
         d.name AS destination_name,
         e.id AS equipment_id, e.name AS equipment_name, e.type AS equipment_type, e.is_relic, e.current_status
  FROM missions m
  LEFT JOIN agencies a ON m.agency_id = a.id
  LEFT JOIN destinations d ON m.destination_id = d.id
  LEFT JOIN equipment e ON e.mission_id = m.id
  WHERE 1=1
";
$params = [];

if ($agencyFilter) {
  $sql .= " AND m.agency_id = :agency";
  $params['agency'] = $agencyFilter;
}
if ($destFilter) {
  $sql .= " AND m.destination_id = :dest";
  $params['dest'] = $destFilter;
}
if ($statusFilter) {
  $sql .= " AND LOWER(m.status) = LOWER(:status)";
  $params['status'] = $statusFilter;
}
if ($search) {
  $sql .= " AND (m.name LIKE :q OR m.official_name LIKE :q OR e.name LIKE :q OR d.name LIKE :q)";
  $params['q'] = "%$search%";
}

$sql .= " ORDER BY m.launch_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

$pageTitle = 'Explore the Complete Space Archive — ORBITAL ARCHIVE';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 7rem;">
  <div style="max-width: 1300px; margin: 0 auto; padding: 2rem 2rem 6rem;">
    
    <div style="margin-bottom: 2.5rem;">
      <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 0.5rem;">
        CATALOG // ADVANCED TELEMETRY BROWSER
      </div>
      <h1 style="font-size: 3rem; font-weight: 800; color: #fff; letter-spacing: -0.01em;">
        EXPLORE THE ARCHIVE
      </h1>
      <p style="color: var(--text-secondary); max-width: 700px; margin-top: 0.5rem;">
        Filter through 30 landmark missions, rovers, orbiters, and off-world relics spanning 1957 to the modern era.
      </p>
    </div>

    <!-- Filter & Search Toolbar -->
    <form method="GET" action="explore.php" style="background: rgba(11, 17, 24, 0.85); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 4px; margin-bottom: 3rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
      
      <!-- Text search -->
      <div style="flex: 2; min-width: 220px;">
        <label class="mono-label" style="display: block; margin-bottom: 6px;">SEARCH CATALOG</label>
        <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search Opportunity, Apollo, Mars..." class="search-input-field" style="background: rgba(5, 7, 11, 0.8); border: 1px solid var(--border-color); padding: 9px 14px; border-radius: 2px; width: 100%;">
      </div>

      <!-- Agency Filter -->
      <div style="flex: 1; min-width: 160px;">
        <label class="mono-label" style="display: block; margin-bottom: 6px;">AGENCY</label>
        <select name="agency" style="background: rgba(5, 7, 11, 0.8); border: 1px solid var(--border-color); padding: 9px 12px; border-radius: 2px; width: 100%; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.8rem;">
          <option value="">All Agencies</option>
          <?php foreach ($agencies as $a): ?>
            <option value="<?= $a['id'] ?>" <?= $agencyFilter == $a['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($a['short_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Destination Filter -->
      <div style="flex: 1; min-width: 160px;">
        <label class="mono-label" style="display: block; margin-bottom: 6px;">DESTINATION</label>
        <select name="destination" style="background: rgba(5, 7, 11, 0.8); border: 1px solid var(--border-color); padding: 9px 12px; border-radius: 2px; width: 100%; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.8rem;">
          <option value="">All Destinations</option>
          <?php foreach ($destinations as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $destFilter == $d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Status Filter -->
      <div style="flex: 1; min-width: 140px;">
        <label class="mono-label" style="display: block; margin-bottom: 6px;">STATUS</label>
        <select name="status" style="background: rgba(5, 7, 11, 0.8); border: 1px solid var(--border-color); padding: 9px 12px; border-radius: 2px; width: 100%; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.8rem;">
          <option value="">All Statuses</option>
          <option value="Active" <?= strtolower($statusFilter ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="Completed" <?= strtolower($statusFilter ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
        </select>
      </div>

      <!-- Buttons -->
      <div style="display: flex; gap: 8px;">
        <button type="submit" class="cta-button" style="padding: 10px 18px; font-size: 0.75rem;">FILTER</button>
        <a href="explore.php" class="filter-btn" style="padding: 10px 14px; text-decoration: none;">RESET</a>
      </div>
    </form>

    <!-- Results Counter -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
      <div class="mono-label">
        SHOWING <span style="color: #fff; font-weight: 700;"><?= count($records) ?></span> CATALOGED RECORDS
      </div>
      <div class="mono-label" style="color: var(--accent-cyan);">
        ORDER // CHRONOLOGICAL DESCENDING
      </div>
    </div>

    <!-- Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1.5rem;">
      <?php foreach ($records as $row): ?>
        <div class="relic-hud-card" style="transition: transform 0.2s, border-color 0.2s;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
              <span class="mono-label" style="color: var(--accent-blue);"><?= htmlspecialchars($row['agency_code']) ?></span>
              <h3 style="font-size: 1.35rem; font-weight: 700; color: #fff; margin: 4px 0;">
                <a href="mission.php?id=<?= $row['mission_id'] ?>" style="color: inherit;">
                  <?= htmlspecialchars($row['mission_name']) ?>
                </a>
              </h3>
            </div>
            <span class="mono-label" style="color: <?= strtolower($row['status']) === 'active' ? 'var(--accent-emerald)' : 'var(--text-secondary)' ?>;">
              <?= strtoupper(htmlspecialchars($row['status'])) ?>
            </span>
          </div>

          <?php if (!empty($row['equipment_name'])): ?>
            <div style="padding: 8px 12px; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 2px; margin: 0.5rem 0;">
              <div class="mono-label" style="font-size: 0.65rem; color: var(--accent-orange);">CARRIED HARDWARE</div>
              <div style="font-size: 0.95rem; font-weight: 600; color: #fff;">
                <?= htmlspecialchars($row['equipment_name']) ?>
                <?php if ($row['is_relic']): ?>
                  <span style="font-family: var(--font-mono); font-size: 0.62rem; color: var(--accent-cyan); margin-left: 4px;">[RELIC]</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="relic-meta-row" style="margin-top: 0.5rem;">
            <span class="relic-meta-label">DESTINATION</span>
            <span class="relic-meta-val" style="color: var(--accent-orange);"><?= htmlspecialchars($row['destination_name']) ?></span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">LAUNCH DATE</span>
            <span class="relic-meta-val"><?= formatTelemetryDate($row['launch_date']) ?></span>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 0.8rem; border-top: 1px solid rgba(255, 255, 255, 0.05);">
            <a href="mission.php?id=<?= $row['mission_id'] ?>" class="relic-cta-link" style="font-size: 0.75rem;">
              MISSION EXHIBIT →
            </a>
            <?php if (!empty($row['equipment_id'])): ?>
              <a href="equipment.php?id=<?= $row['equipment_id'] ?>" style="font-family: var(--font-mono); font-size: 0.72rem; color: var(--text-secondary);">
                HARDWARE →
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
