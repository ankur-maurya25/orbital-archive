<?php
/**
 * ORBITAL ARCHIVE - Admin Instruments Management (CRUD)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();
$pdo = getDB();

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF verification failed.';
    } else {
        $postAction = $_POST['action'] ?? '';

        if ($postAction === 'delete') {
            $delId = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM instruments WHERE id = :id");
            $stmt->execute(['id' => $delId]);
            $message = "Instrument #$delId removed from archive registry.";
            $action = 'list';
        } elseif ($postAction === 'save') {
            $name = trim($_POST['name'] ?? '');
            $officialName = trim($_POST['official_name'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $purpose = trim($_POST['purpose'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $specs = trim($_POST['specifications'] ?? '');
            $manufacturer = trim($_POST['manufacturer'] ?? '');
            $agencyId = !empty($_POST['agency_id']) ? (int)$_POST['agency_id'] : null;
            $equipmentIds = $_POST['equipment_ids'] ?? [];

            if (empty($name)) {
                $error = 'Instrument name is required.';
            } else {
                if ($id) {
                    $stmt = $pdo->prepare("
                        UPDATE instruments SET 
                            name = :name, official_name = :official, type = :type,
                            purpose = :purpose, description = :desc, specifications = :specs,
                            manufacturer = :manuf, agency_id = :agency_id
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'name' => $name, 'official' => $officialName, 'type' => $type,
                        'purpose' => $purpose, 'desc' => $description, 'specs' => $specs,
                        'manuf' => $manufacturer, 'agency_id' => $agencyId, 'id' => $id
                    ]);

                    // Update relationships
                    $pdo->prepare("DELETE FROM equipment_instruments WHERE instrument_id = :id")->execute(['id' => $id]);
                    if (!empty($equipmentIds)) {
                        $linkStmt = $pdo->prepare("INSERT IGNORE INTO equipment_instruments (equipment_id, instrument_id) VALUES (:eid, :iid)");
                        foreach ($equipmentIds as $eid) {
                            $linkStmt->execute(['eid' => (int)$eid, 'iid' => $id]);
                        }
                    }
                    $message = "Instrument #$id updated successfully.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO instruments (name, official_name, type, purpose, description, specifications, manufacturer, agency_id)
                        VALUES (:name, :official, :type, :purpose, :desc, :specs, :manuf, :agency_id)
                    ");
                    $stmt->execute([
                        'name' => $name, 'official' => $officialName, 'type' => $type,
                        'purpose' => $purpose, 'desc' => $description, 'specs' => $specs,
                        'manuf' => $manufacturer, 'agency_id' => $agencyId
                    ]);
                    $newId = $pdo->lastInsertId();

                    if (!empty($equipmentIds)) {
                        $linkStmt = $pdo->prepare("INSERT IGNORE INTO equipment_instruments (equipment_id, instrument_id) VALUES (:eid, :iid)");
                        foreach ($equipmentIds as $eid) {
                            $linkStmt->execute(['eid' => (int)$eid, 'iid' => $newId]);
                        }
                    }
                    $message = "New instrument #$newId cataloged in archive.";
                }
                $action = 'list';
            }
        }
    }
}

// Fetch agencies & equipment
$agencies = $pdo->query("SELECT id, short_name, name FROM agencies ORDER BY short_name ASC")->fetchAll();
$allEquipment = $pdo->query("SELECT id, name, type FROM equipment ORDER BY name ASC")->fetchAll();

$itemToEdit = null;
$linkedEquipmentIds = [];
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM instruments WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $itemToEdit = $stmt->fetch();

    $lStmt = $pdo->prepare("SELECT equipment_id FROM equipment_instruments WHERE instrument_id = :id");
    $lStmt->execute(['id' => $id]);
    $linkedEquipmentIds = $lStmt->fetchAll(PDO::FETCH_COLUMN);
}

$allInstruments = [];
if ($action === 'list') {
    $allInstruments = $pdo->query("
        SELECT i.*, a.short_name AS agency_code,
               (SELECT COUNT(*) FROM equipment_instruments WHERE instrument_id = i.id) AS linked_count
        FROM instruments i
        LEFT JOIN agencies a ON i.agency_id = a.id
        ORDER BY i.id ASC
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Instruments — Mission Control</title>
  <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body style="padding-top: 5rem;">
  <div class="bg-grid"></div>

  <!-- Admin Top Bar -->
  <header class="orbital-header" style="background: rgba(5,7,11,0.98);">
    <div class="logo-area">
      <div class="logo-brand">
        ORBITAL <span class="logo-bracket">[ MISSION CONTROL ]</span>
      </div>
      <div class="mono-label" style="color: var(--accent-cyan);">
        SCIENTIFIC INSTRUMENTS // OPERATOR: <?= strtoupper(htmlspecialchars($_SESSION['admin_username'])) ?>
      </div>
    </div>
    <div style="display: flex; gap: 1rem; align-items: center;">
      <a href="index.php" class="filter-btn">← DASHBOARD</a>
      <a href="logout.php" class="filter-btn" style="border-color: var(--accent-rose); color: var(--accent-rose);">LOGOUT</a>
    </div>
  </header>

  <main style="max-width: 1200px; margin: 0 auto; padding: 2rem 2rem 6rem; position: relative; z-index: 10;">
    
    <!-- Navigation Tabs -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
      <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="index.php" class="filter-btn">DASHBOARD OVERVIEW</a>
        <a href="missions.php" class="filter-btn">MANAGE MISSIONS</a>
        <a href="equipment.php" class="filter-btn">MANAGE EQUIPMENT</a>
        <a href="instruments.php" class="filter-btn active">MANAGE INSTRUMENTS (<?= count($allInstruments) ?>)</a>
        <a href="security.php" class="filter-btn">SECURITY CLEARANCE</a>
      </div>
      <?php if ($action === 'list'): ?>
        <a href="instruments.php?action=create" class="cta-button" style="padding: 8px 16px; font-size: 0.75rem;">+ NEW INSTRUMENT</a>
      <?php endif; ?>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($message)): ?>
      <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--accent-emerald); color: #fff; padding: 12px 18px; border-radius: 2px; margin-bottom: 2rem; font-family: var(--font-mono); font-size: 0.85rem;">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid var(--accent-rose); color: #fff; padding: 12px 18px; border-radius: 2px; margin-bottom: 2rem; font-family: var(--font-mono); font-size: 0.85rem;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- VIEW: LIST ALL INSTRUMENTS -->
    <?php if ($action === 'list'): ?>
      <div class="relic-hud-card">
        <h2 style="font-size: 1.4rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem;">SCIENTIFIC INSTRUMENTS REGISTRY</h2>

        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
          <thead>
            <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-family: var(--font-mono); font-size: 0.7rem;">
              <th style="padding: 10px 8px;">ID</th>
              <th style="padding: 10px 8px;">NAME</th>
              <th style="padding: 10px 8px;">TYPE</th>
              <th style="padding: 10px 8px;">MANUFACTURER</th>
              <th style="padding: 10px 8px;">AGENCY</th>
              <th style="padding: 10px 8px;">ATTACHED ROVERS</th>
              <th style="padding: 10px 8px; text-align: right;">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allInstruments as $inst): ?>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                <td style="padding: 12px 8px; font-family: var(--font-mono); color: var(--text-muted);"><?= $inst['id'] ?></td>
                <td style="padding: 12px 8px; font-weight: 600; color: #fff;">
                  <?= htmlspecialchars($inst['name']) ?>
                </td>
                <td style="padding: 12px 8px; color: var(--accent-orange);"><?= htmlspecialchars($inst['type']) ?></td>
                <td style="padding: 12px 8px; font-size: 0.78rem; color: var(--text-secondary);"><?= htmlspecialchars($inst['manufacturer'] ?? 'N/A') ?></td>
                <td style="padding: 12px 8px; font-family: var(--font-mono); color: var(--accent-blue);"><?= htmlspecialchars($inst['agency_code'] ?? 'GLOBAL') ?></td>
                <td style="padding: 12px 8px; font-family: var(--font-mono); color: var(--accent-cyan);"><?= $inst['linked_count'] ?> equipment</td>
                <td style="padding: 12px 8px; text-align: right;">
                  <a href="instruments.php?action=edit&id=<?= $inst['id'] ?>" class="relic-cta-link" style="font-size: 0.72rem; margin-right: 12px;">EDIT</a>
                  <form method="POST" action="instruments.php" style="display: inline;" onsubmit="return confirm('Delete instrument record?');">
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $inst['id'] ?>">
                    <button type="submit" style="color: var(--accent-rose); font-family: var(--font-mono); font-size: 0.72rem; cursor: pointer;">DELETE</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <!-- VIEW: CREATE OR EDIT INSTRUMENT -->
    <?php elseif ($action === 'create' || $action === 'edit'): ?>
      <div class="relic-hud-card" style="max-width: 800px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
          <h2 style="font-size: 1.5rem; font-weight: 700; color: #fff;">
            <?= $action === 'edit' ? 'EDIT INSTRUMENT #' . $itemToEdit['id'] : 'CATALOG NEW SCIENTIFIC INSTRUMENT' ?>
          </h2>
          <a href="instruments.php" class="filter-btn">CANCEL</a>
        </div>

        <form method="POST" action="instruments.php<?= $action === 'edit' ? '?action=edit&id=' . $id : '' ?>">
          <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
          <input type="hidden" name="action" value="save">

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.2rem;">
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">COMMON NAME / ACRONYM *</label>
              <input type="text" name="name" value="<?= htmlspecialchars($itemToEdit['name'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;" placeholder="e.g. Mastcam-Z, PIXL, SuperCam" required>
            </div>
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">OFFICIAL FULL NAME</label>
              <input type="text" name="official_name" value="<?= htmlspecialchars($itemToEdit['official_name'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;" placeholder="e.g. Planetary Instrument for X-ray Lithochemistry">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.2rem;">
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">INSTRUMENT TYPE</label>
              <input type="text" name="type" value="<?= htmlspecialchars($itemToEdit['type'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;" placeholder="e.g. Laser Spectrometer, Radar Imager, Camera">
            </div>
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">SPONSORING AGENCY</label>
              <select name="agency_id" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.8rem;">
                <option value="">None / International</option>
                <?php foreach ($agencies as $a): ?>
                  <option value="<?= $a['id'] ?>" <?= ($itemToEdit['agency_id'] ?? 1) == $a['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['short_name']) ?> - <?= htmlspecialchars($a['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">MANUFACTURER / RESEARCH INSTITUTION</label>
            <input type="text" name="manufacturer" value="<?= htmlspecialchars($itemToEdit['manufacturer'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;" placeholder="e.g. NASA JPL, MIT, Malin Space Science Systems, FFI">
          </div>

          <!-- Attach to Equipment Selection -->
          <div style="margin-bottom: 1.5rem; background: rgba(16, 26, 38, 0.4); border: 1px solid var(--border-color); padding: 1.2rem; border-radius: 2px;">
            <label class="mono-label" style="display: block; margin-bottom: 8px; color: var(--accent-cyan);">ATTACH TO EQUIPMENT / SPACECRAFT</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; max-height: 180px; overflow-y: auto;">
              <?php foreach ($allEquipment as $eqItem): 
                $isChecked = in_array((int)$eqItem['id'], $linkedEquipmentIds);
              ?>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: var(--text-secondary); cursor: pointer;">
                  <input type="checkbox" name="equipment_ids[]" value="<?= $eqItem['id'] ?>" <?= $isChecked ? 'checked' : '' ?> style="accent-color: var(--accent-cyan);">
                  <span><?= htmlspecialchars($eqItem['name']) ?> (<?= htmlspecialchars($eqItem['type']) ?>)</span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">SCIENTIFIC PURPOSE *</label>
            <textarea name="purpose" rows="2" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; border-radius: 2px;" required><?= htmlspecialchars($itemToEdit['purpose'] ?? '') ?></textarea>
          </div>

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">DETAILED DESCRIPTION</label>
            <textarea name="description" rows="3" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; border-radius: 2px;"><?= htmlspecialchars($itemToEdit['description'] ?? '') ?></textarea>
          </div>

          <div style="margin-bottom: 2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">TECHNICAL SPECIFICATIONS (MASS, RESOLUTION, WAVELENGTH)</label>
            <textarea name="specifications" rows="2" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; border-radius: 2px;"><?= htmlspecialchars($itemToEdit['specifications'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="cta-button" style="width: 100%; justify-content: center; padding: 12px;">
            <?= $action === 'edit' ? 'COMMIT INSTRUMENT UPDATES →' : 'RECORD INSTRUMENT IN ARCHIVE →' ?>
          </button>
        </form>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
