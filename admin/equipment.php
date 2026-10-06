<?php
/**
 * ORBITAL ARCHIVE - Admin Equipment & Relics Management (Full CRUD)
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
        $error = 'CSRF validation failed.';
    } else {
        $postAction = $_POST['action'] ?? '';

        if ($postAction === 'delete') {
            $delId = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = :id");
            $stmt->execute(['id' => $delId]);
            $message = "Equipment item #$delId expunged.";
            $action = 'list';
        } elseif ($postAction === 'save') {
            $missionId = (int)$_POST['mission_id'];
            $name = trim($_POST['name'] ?? '');
            $officialName = trim($_POST['official_name'] ?? '');
            $type = trim($_POST['type'] ?? 'Rover');
            $purpose = trim($_POST['purpose'] ?? '');
            $technology = trim($_POST['technology'] ?? '');
            $currentStatus = trim($_POST['current_status'] ?? 'Operational');
            $currentLocation = trim($_POST['current_location'] ?? '');
            $isRelic = isset($_POST['is_relic']) ? 1 : 0;
            $relicCategory = !empty($_POST['relic_category']) ? trim($_POST['relic_category']) : null;
            $description = trim($_POST['description'] ?? '');
            $discoveries = trim($_POST['discoveries'] ?? '');
            $legacy = trim($_POST['legacy'] ?? '');

            if (empty($name) || empty($missionId)) {
                $error = 'Hardware name and parent mission are required.';
            } else {
                if ($id) {
                    $stmt = $pdo->prepare("
                        UPDATE equipment SET 
                            mission_id = :mid, name = :name, official_name = :official,
                            type = :type, purpose = :purpose, technology = :tech,
                            current_status = :status, current_location = :loc,
                            is_relic = :relic, relic_category = :cat, description = :desc,
                            discoveries = :disc, legacy = :legacy
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'mid' => $missionId, 'name' => $name, 'official' => $officialName,
                        'type' => $type, 'purpose' => $purpose, 'tech' => $technology,
                        'status' => $currentStatus, 'loc' => $currentLocation,
                        'relic' => $isRelic, 'cat' => $relicCategory, 'desc' => $description,
                        'disc' => $discoveries, 'legacy' => $legacy, 'id' => $id
                    ]);
                    $message = "Equipment #$id updated successfully.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO equipment (mission_id, name, official_name, type, purpose, technology, current_status, current_location, is_relic, relic_category, description, discoveries, legacy)
                        VALUES (:mid, :name, :official, :type, :purpose, :tech, :status, :loc, :relic, :cat, :desc, :disc, :legacy)
                    ");
                    $stmt->execute([
                        'mid' => $missionId, 'name' => $name, 'official' => $officialName,
                        'type' => $type, 'purpose' => $purpose, 'tech' => $technology,
                        'status' => $currentStatus, 'loc' => $currentLocation,
                        'relic' => $isRelic, 'cat' => $relicCategory, 'desc' => $description,
                        'disc' => $discoveries, 'legacy' => $legacy
                    ]);
                    $newId = $pdo->lastInsertId();
                    $message = "New equipment unit #$newId cataloged in archive.";
                }
                $action = 'list';
            }
        }
    }
}

// Fetch all missions for selector
$missions = $pdo->query("SELECT id, name FROM missions ORDER BY name ASC")->fetchAll();

$itemToEdit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $itemToEdit = $stmt->fetch();
}

$allItems = [];
if ($action === 'list') {
    $allItems = $pdo->query("
        SELECT e.*, m.name AS mission_name 
        FROM equipment e
        JOIN missions m ON e.mission_id = m.id
        ORDER BY e.is_relic DESC, e.id DESC
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Equipment & Relics — Mission Control</title>
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
      <div class="mono-label" style="color: var(--accent-orange);">
        HARDWARE & RELICS CRUD // OPERATOR: <?= strtoupper(htmlspecialchars($_SESSION['admin_username'])) ?>
      </div>
    </div>
    <div style="display: flex; gap: 1rem; align-items: center;">
      <a href="index.php" class="filter-btn">← DASHBOARD</a>
      <a href="logout.php" class="filter-btn" style="border-color: var(--accent-rose); color: var(--accent-rose);">LOGOUT</a>
    </div>
  </header>

  <main style="max-width: 1200px; margin: 0 auto; padding: 2rem 2rem 6rem; position: relative; z-index: 10;">
    
    <!-- Tab Nav -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <div style="display: flex; gap: 8px;">
        <a href="index.php" class="filter-btn">DASHBOARD OVERVIEW</a>
        <a href="missions.php" class="filter-btn">MANAGE MISSIONS</a>
        <a href="equipment.php" class="filter-btn active">MANAGE EQUIPMENT (<?= count($allItems) ?>)</a>
      </div>
      <?php if ($action === 'list'): ?>
        <a href="equipment.php?action=create" class="cta-button" style="padding: 8px 16px; font-size: 0.75rem;">+ CATALOG HARDWARE</a>
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

    <!-- VIEW: LIST ALL EQUIPMENT -->
    <?php if ($action === 'list'): ?>
      <div class="relic-hud-card">
        <h2 style="font-size: 1.4rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem;">HARDWARE & RELICS REGISTRY</h2>

        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
          <thead>
            <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-family: var(--font-mono); font-size: 0.7rem;">
              <th style="padding: 10px 8px;">ID</th>
              <th style="padding: 10px 8px;">NAME</th>
              <th style="padding: 10px 8px;">MISSION</th>
              <th style="padding: 10px 8px;">TYPE</th>
              <th style="padding: 10px 8px;">STATUS</th>
              <th style="padding: 10px 8px;">IS RELIC?</th>
              <th style="padding: 10px 8px; text-align: right;">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allItems as $e): ?>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                <td style="padding: 12px 8px; font-family: var(--font-mono); color: var(--text-muted);"><?= $e['id'] ?></td>
                <td style="padding: 12px 8px; font-weight: 600; color: #fff;"><?= htmlspecialchars($e['name']) ?></td>
                <td style="padding: 12px 8px; font-family: var(--font-mono); color: var(--accent-blue);"><?= htmlspecialchars($e['mission_name']) ?></td>
                <td style="padding: 12px 8px; color: var(--accent-orange);"><?= htmlspecialchars($e['type']) ?></td>
                <td style="padding: 12px 8px; font-family: var(--font-mono); font-size: 0.75rem;"><?= htmlspecialchars($e['current_status']) ?></td>
                <td style="padding: 12px 8px;">
                  <?php if ($e['is_relic']): ?>
                    <span style="font-family: var(--font-mono); font-size: 0.65rem; color: var(--accent-cyan); border: 1px solid var(--accent-cyan); padding: 1px 6px; border-radius: 2px;">
                      <?= htmlspecialchars($e['relic_category'] ?? 'RELIC') ?>
                    </span>
                  <?php else: ?>
                    <span style="color: var(--text-muted); font-size: 0.75rem;">No</span>
                  <?php endif; ?>
                </td>
                <td style="padding: 12px 8px; text-align: right;">
                  <a href="equipment.php?action=edit&id=<?= $e['id'] ?>" class="relic-cta-link" style="font-size: 0.72rem; margin-right: 12px;">EDIT</a>
                  <form method="POST" action="equipment.php" style="display: inline;" onsubmit="return confirm('Delete hardware record?');">
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                    <button type="submit" style="color: var(--accent-rose); font-family: var(--font-mono); font-size: 0.72rem; cursor: pointer;">DELETE</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <!-- VIEW: CREATE OR EDIT EQUIPMENT -->
    <?php elseif ($action === 'create' || $action === 'edit'): ?>
      <div class="relic-hud-card" style="max-width: 800px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
          <h2 style="font-size: 1.5rem; font-weight: 700; color: #fff;">
            <?= $action === 'edit' ? 'EDIT EQUIPMENT #' . $itemToEdit['id'] : 'CATALOG NEW HARDWARE' ?>
          </h2>
          <a href="equipment.php" class="filter-btn">CANCEL</a>
        </div>

        <form method="POST" action="equipment.php<?= $action === 'edit' ? '?action=edit&id=' . $id : '' ?>">
          <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
          <input type="hidden" name="action" value="save">

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">PARENT MISSION *</label>
            <select name="mission_id" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.8rem;" required>
              <?php foreach ($missions as $m): ?>
                <option value="<?= $m['id'] ?>" <?= ($itemToEdit['mission_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($m['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.2rem;">
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">COMMON NAME *</label>
              <input type="text" name="name" value="<?= htmlspecialchars($itemToEdit['name'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;" required>
            </div>
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">OFFICIAL DESIGNATION</label>
              <input type="text" name="official_name" value="<?= htmlspecialchars($itemToEdit['official_name'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.2rem;">
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">HARDWARE TYPE</label>
              <input type="text" name="type" value="<?= htmlspecialchars($itemToEdit['type'] ?? 'Rover') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;" placeholder="Rover, Lander, Orbiter, Flyby Probe...">
            </div>
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">CURRENT STATUS</label>
              <select name="current_status" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.8rem;">
                <option value="Operational" <?= ($itemToEdit['current_status'] ?? '') === 'Operational' ? 'selected' : '' ?>>Operational / Still Operating</option>
                <option value="Decommissioned" <?= ($itemToEdit['current_status'] ?? '') === 'Decommissioned' ? 'selected' : '' ?>>Decommissioned / Mission Complete</option>
                <option value="Abandoned" <?= ($itemToEdit['current_status'] ?? '') === 'Abandoned' ? 'selected' : '' ?>>Abandoned</option>
                <option value="Lost" <?= ($itemToEdit['current_status'] ?? '') === 'Lost' ? 'selected' : '' ?>>Lost</option>
                <option value="Returned to Earth" <?= ($itemToEdit['current_status'] ?? '') === 'Returned to Earth' ? 'selected' : '' ?>>Returned to Earth</option>
                <option value="Destroyed" <?= ($itemToEdit['current_status'] ?? '') === 'Destroyed' ? 'selected' : '' ?>>Destroyed</option>
              </select>
            </div>
          </div>

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">CURRENT RESTING LOCATION & COORDINATES</label>
            <input type="text" name="current_location" value="<?= htmlspecialchars($itemToEdit['current_location'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;" placeholder="e.g. Mare Tranquillitatis (0.67° N, 23.47° E)">
          </div>

          <div style="background: rgba(16, 26, 38, 0.4); border: 1px solid var(--border-bright); padding: 1.2rem; border-radius: 2px; margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin-bottom: 0.8rem;">
              <input type="checkbox" name="is_relic" value="1" <?= (!empty($itemToEdit['is_relic'])) ? 'checked' : '' ?> style="accent-color: var(--accent-cyan); transform: scale(1.3);">
              <span class="mono-label" style="color: var(--accent-cyan); font-size: 0.8rem;">MARK AS OFF-WORLD RELIC ("THE MACHINES WE LEFT BEHIND")</span>
            </label>

            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">RELIC CATEGORY</label>
              <select name="relic_category" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 8px 12px; width: 100%; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.8rem;">
                <option value="">None</option>
                <option value="Lunar Relic" <?= ($itemToEdit['relic_category'] ?? '') === 'Lunar Relic' ? 'selected' : '' ?>>Lunar Relic</option>
                <option value="Martian Relic" <?= ($itemToEdit['relic_category'] ?? '') === 'Martian Relic' ? 'selected' : '' ?>>Martian Relic</option>
                <option value="Deep Space Relic" <?= ($itemToEdit['relic_category'] ?? '') === 'Deep Space Relic' ? 'selected' : '' ?>>Deep Space Relic</option>
                <option value="Orbital Relic" <?= ($itemToEdit['relic_category'] ?? '') === 'Orbital Relic' ? 'selected' : '' ?>>Orbital Relic</option>
              </select>
            </div>
          </div>

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">WHAT WAS IT? (DESCRIPTION)</label>
            <textarea name="description" rows="3" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; border-radius: 2px;"><?= htmlspecialchars($itemToEdit['description'] ?? '') ?></textarea>
          </div>

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">WHAT DID IT DO? (PURPOSE & TECH)</label>
            <textarea name="purpose" rows="2" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; border-radius: 2px;"><?= htmlspecialchars($itemToEdit['purpose'] ?? '') ?></textarea>
          </div>

          <div style="margin-bottom: 2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">WHAT DID IT DISCOVER? (DISCOVERIES)</label>
            <textarea name="discoveries" rows="3" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; border-radius: 2px;"><?= htmlspecialchars($itemToEdit['discoveries'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="cta-button" style="width: 100%; justify-content: center; padding: 12px;">
            <?= $action === 'edit' ? 'COMMIT HARDWARE UPDATES →' : 'LOG HARDWARE RECORD →' ?>
          </button>
        </form>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
