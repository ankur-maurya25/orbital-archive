<?php
/**
 * ORBITAL ARCHIVE - Admin Missions Management (Full CRUD)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();
$pdo = getDB();

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$message = '';
$error = '';

// Handle POST: Create or Update or Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'CSRF validation failed.';
    } else {
        $postAction = $_POST['action'] ?? '';

        if ($postAction === 'delete') {
            $delId = (int)$_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM missions WHERE id = :id");
            $stmt->execute(['id' => $delId]);
            $message = "Mission #$delId successfully expunged from archive.";
            $action = 'list';
        } elseif ($postAction === 'save') {
            $name = trim($_POST['name'] ?? '');
            $officialName = trim($_POST['official_name'] ?? '');
            $agencyId = (int)$_POST['agency_id'];
            $destId = (int)$_POST['destination_id'];
            $launchDate = !empty($_POST['launch_date']) ? $_POST['launch_date'] : null;
            $launchVehicle = trim($_POST['launch_vehicle'] ?? '');
            $launchLocation = trim($_POST['launch_location'] ?? '');
            $status = trim($_POST['status'] ?? 'Active');
            $trajectory = trim($_POST['trajectory_type'] ?? 'Robotic probe');
            $objective = trim($_POST['objective'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $legacy = trim($_POST['legacy'] ?? '');

            if (empty($name)) {
                $error = 'Mission name is required.';
            } else {
                if ($id) {
                    // Update
                    $stmt = $pdo->prepare("
                        UPDATE missions SET 
                            name = :name, official_name = :official_name, agency_id = :agency_id,
                            destination_id = :destination_id, launch_date = :launch_date,
                            launch_vehicle = :launch_vehicle, launch_location = :launch_location,
                            status = :status, trajectory_type = :trajectory_type,
                            objective = :objective, description = :description, legacy = :legacy
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        'name' => $name, 'official_name' => $officialName, 'agency_id' => $agencyId,
                        'destination_id' => $destId, 'launch_date' => $launchDate,
                        'launch_vehicle' => $launchVehicle, 'launch_location' => $launchLocation,
                        'status' => $status, 'trajectory_type' => $trajectory,
                        'objective' => $objective, 'description' => $description, 'legacy' => $legacy,
                        'id' => $id
                    ]);
                    $message = "Mission #$id updated successfully.";
                } else {
                    // Insert
                    $stmt = $pdo->prepare("
                        INSERT INTO missions (name, official_name, agency_id, destination_id, launch_date, launch_vehicle, launch_location, status, trajectory_type, objective, description, legacy)
                        VALUES (:name, :official_name, :agency_id, :destination_id, :launch_date, :launch_vehicle, :launch_location, :status, :trajectory_type, :objective, :description, :legacy)
                    ");
                    $stmt->execute([
                        'name' => $name, 'official_name' => $officialName, 'agency_id' => $agencyId,
                        'destination_id' => $destId, 'launch_date' => $launchDate,
                        'launch_vehicle' => $launchVehicle, 'launch_location' => $launchLocation,
                        'status' => $status, 'trajectory_type' => $trajectory,
                        'objective' => $objective, 'description' => $description, 'legacy' => $legacy
                    ]);
                    $newId = $pdo->lastInsertId();
                    $message = "New mission #$newId recorded in archive.";
                }
                $action = 'list';
            }
        }
    }
}

// Fetch agencies & destinations for forms
$agencies = $pdo->query("SELECT id, short_name, name FROM agencies ORDER BY short_name ASC")->fetchAll();
$destinations = $pdo->query("SELECT id, name FROM destinations ORDER BY id ASC")->fetchAll();

// If editing, load mission
$missionToEdit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM missions WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $missionToEdit = $stmt->fetch();
}

// If listing, fetch all missions
$allMissions = [];
if ($action === 'list') {
    $allMissions = $pdo->query("
        SELECT m.*, a.short_name AS agency_code, d.name AS destination_name
        FROM missions m
        LEFT JOIN agencies a ON m.agency_id = a.id
        LEFT JOIN destinations d ON m.destination_id = d.id
        ORDER BY m.id DESC
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Missions — Mission Control</title>
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
        MISSION DOSSIERS CRUD // OPERATOR: <?= strtoupper(htmlspecialchars($_SESSION['admin_username'])) ?>
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
        <a href="missions.php" class="filter-btn active">MANAGE MISSIONS (<?= count($allMissions) ?>)</a>
        <a href="equipment.php" class="filter-btn">MANAGE EQUIPMENT</a>
      </div>
      <?php if ($action === 'list'): ?>
        <a href="missions.php?action=create" class="cta-button" style="padding: 8px 16px; font-size: 0.75rem;">+ NEW MISSION</a>
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

    <!-- VIEW: LIST ALL MISSIONS -->
    <?php if ($action === 'list'): ?>
      <div class="relic-hud-card">
        <h2 style="font-size: 1.4rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem;">MISSION RECORDS IN ARCHIVE</h2>

        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
          <thead>
            <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-family: var(--font-mono); font-size: 0.7rem;">
              <th style="padding: 10px 8px;">ID</th>
              <th style="padding: 10px 8px;">MISSION NAME</th>
              <th style="padding: 10px 8px;">AGENCY</th>
              <th style="padding: 10px 8px;">DESTINATION</th>
              <th style="padding: 10px 8px;">LAUNCH DATE</th>
              <th style="padding: 10px 8px;">STATUS</th>
              <th style="padding: 10px 8px; text-align: right;">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allMissions as $m): ?>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                <td style="padding: 12px 8px; font-family: var(--font-mono); color: var(--text-muted);"><?= $m['id'] ?></td>
                <td style="padding: 12px 8px; font-weight: 600; color: #fff;">
                  <?= htmlspecialchars($m['name']) ?>
                </td>
                <td style="padding: 12px 8px; font-family: var(--font-mono); color: var(--accent-blue);"><?= htmlspecialchars($m['agency_code']) ?></td>
                <td style="padding: 12px 8px; color: var(--accent-orange);"><?= htmlspecialchars($m['destination_name']) ?></td>
                <td style="padding: 12px 8px; font-family: var(--font-mono);"><?= formatTelemetryDate($m['launch_date']) ?></td>
                <td style="padding: 12px 8px; font-family: var(--font-mono); font-size: 0.75rem; color: <?= strtolower($m['status']) === 'active' ? 'var(--accent-emerald)' : 'var(--text-secondary)' ?>;"><?= strtoupper(htmlspecialchars($m['status'])) ?></td>
                <td style="padding: 12px 8px; text-align: right;">
                  <a href="missions.php?action=edit&id=<?= $m['id'] ?>" class="relic-cta-link" style="font-size: 0.72rem; margin-right: 12px;">EDIT</a>
                  <form method="POST" action="missions.php" style="display: inline;" onsubmit="return confirm('Are you certain you want to permanently delete this mission record?');">
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                    <button type="submit" style="color: var(--accent-rose); font-family: var(--font-mono); font-size: 0.72rem; cursor: pointer;">DELETE</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <!-- VIEW: CREATE OR EDIT MISSION -->
    <?php elseif ($action === 'create' || $action === 'edit'): ?>
      <div class="relic-hud-card" style="max-width: 800px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
          <h2 style="font-size: 1.5rem; font-weight: 700; color: #fff;">
            <?= $action === 'edit' ? 'EDIT MISSION RECORD #' . $missionToEdit['id'] : 'CREATE NEW MISSION RECORD' ?>
          </h2>
          <a href="missions.php" class="filter-btn">CANCEL</a>
        </div>

        <form method="POST" action="missions.php<?= $action === 'edit' ? '?action=edit&id=' . $id : '' ?>">
          <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
          <input type="hidden" name="action" value="save">

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.2rem;">
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">MISSION COMMON NAME *</label>
              <input type="text" name="name" value="<?= htmlspecialchars($missionToEdit['name'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;" required>
            </div>
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">OFFICIAL MISSION NAME</label>
              <input type="text" name="official_name" value="<?= htmlspecialchars($missionToEdit['official_name'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;">
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.2rem;">
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">OPERATING AGENCY</label>
              <select name="agency_id" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.8rem;">
                <?php foreach ($agencies as $a): ?>
                  <option value="<?= $a['id'] ?>" <?= ($missionToEdit['agency_id'] ?? 1) == $a['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['short_name']) ?> - <?= htmlspecialchars($a['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">DESTINATION TARGET</label>
              <select name="destination_id" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.8rem;">
                <?php foreach ($destinations as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= ($missionToEdit['destination_id'] ?? 2) == $d['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.2rem;">
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">LAUNCH DATE</label>
              <input type="date" name="launch_date" value="<?= htmlspecialchars($missionToEdit['launch_date'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;">
            </div>
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">LAUNCH VEHICLE</label>
              <input type="text" name="launch_vehicle" value="<?= htmlspecialchars($missionToEdit['launch_vehicle'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;">
            </div>
            <div>
              <label class="mono-label" style="display: block; margin-bottom: 6px;">STATUS</label>
              <select name="status" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; color: var(--text-primary); font-family: var(--font-mono); font-size: 0.8rem;">
                <option value="Active" <?= ($missionToEdit['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Completed" <?= ($missionToEdit['status'] ?? '') === 'Completed' ? 'selected' : '' ?>>Completed</option>
                <option value="Lost" <?= ($missionToEdit['status'] ?? '') === 'Lost' ? 'selected' : '' ?>>Lost</option>
                <option value="Historic" <?= ($missionToEdit['status'] ?? '') === 'Historic' ? 'selected' : '' ?>>Historic</option>
              </select>
            </div>
          </div>

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">LAUNCH LOCATION / SPACEPORT</label>
            <input type="text" name="launch_location" value="<?= htmlspecialchars($missionToEdit['launch_location'] ?? '') ?>" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%;">
          </div>

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">MISSION OBJECTIVE</label>
            <textarea name="objective" rows="3" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; border-radius: 2px;"><?= htmlspecialchars($missionToEdit['objective'] ?? '') ?></textarea>
          </div>

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">DETAILED DESCRIPTION</label>
            <textarea name="description" rows="4" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; border-radius: 2px;"><?= htmlspecialchars($missionToEdit['description'] ?? '') ?></textarea>
          </div>

          <div style="margin-bottom: 2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">HISTORICAL LEGACY</label>
            <textarea name="legacy" rows="3" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 9px 12px; width: 100%; border-radius: 2px;"><?= htmlspecialchars($missionToEdit['legacy'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="cta-button" style="width: 100%; justify-content: center; padding: 12px;">
            <?= $action === 'edit' ? 'COMMIT UPDATED TELEMETRY →' : 'LOG NEW MISSION RECORD →' ?>
          </button>
        </form>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
