<?php
/**
 * ORBITAL ARCHIVE - Admin Mission Control Overview
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();
$pdo = getDB();

$missionsCount = $pdo->query("SELECT COUNT(*) FROM missions")->fetchColumn();
$equipmentCount = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
$relicsCount = $pdo->query("SELECT COUNT(*) FROM equipment WHERE is_relic = 1")->fetchColumn();
$agenciesCount = $pdo->query("SELECT COUNT(*) FROM agencies")->fetchColumn();
$instrumentsCount = $pdo->query("SELECT COUNT(*) FROM instruments")->fetchColumn();

// Check security posture
$userHash = $pdo->query("SELECT password_hash FROM users WHERE username = " . $pdo->quote($_SESSION['admin_username']))->fetchColumn();
$isDefaultKey = ($userHash && password_verify('admin123', $userHash));
$isDefaultAdmin = ($_SESSION['admin_username'] === 'admin');

// Recent missions
$recentMissions = $pdo->query("
  SELECT m.id, m.name, m.launch_date, m.status, a.short_name AS agency_code, d.name AS destination_name
  FROM missions m
  LEFT JOIN agencies a ON m.agency_id = a.id
  LEFT JOIN destinations d ON m.destination_id = d.id
  ORDER BY m.id DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mission Control Panel — ORBITAL ARCHIVE</title>
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
        LOGGED IN AS: <?= strtoupper(htmlspecialchars($_SESSION['admin_username'])) ?> // SECURE SESSION
      </div>
    </div>
    <div style="display: flex; gap: 1rem; align-items: center;">
      <a href="../index.php" target="_blank" class="filter-btn">VIEW LIVE OBSERVATORY ↗</a>
      <a href="logout.php" class="filter-btn" style="border-color: var(--accent-rose); color: var(--accent-rose);">LOGOUT</a>
    </div>
  </header>

  <main style="max-width: 1200px; margin: 0 auto; padding: 2rem 2rem 6rem; position: relative; z-index: 10;">
    
    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 8px; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; flex-wrap: wrap;">
      <a href="index.php" class="filter-btn active">DASHBOARD OVERVIEW</a>
      <a href="missions.php" class="filter-btn">MANAGE MISSIONS (CRUD)</a>
      <a href="equipment.php" class="filter-btn">MANAGE EQUIPMENT & RELICS</a>
      <a href="instruments.php" class="filter-btn">SCIENTIFIC INSTRUMENTS (CRUD)</a>
      <a href="security.php" class="filter-btn">SECURITY CLEARANCE</a>
    </div>

    <!-- Security Advisory Alert if Default Credentials in Use -->
    <?php if ($isDefaultKey || $isDefaultAdmin): ?>
      <div class="relic-hud-card" style="margin-bottom: 2rem; border-color: var(--accent-orange); background: rgba(245, 158, 11, 0.05); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
          <div class="mono-label" style="color: var(--accent-orange);">
            ⚠️ SECURITY ADVISORY // DEFAULT ACCESS CREDENTIALS DETECTED
          </div>
          <p style="margin: 4px 0 0; font-size: 0.85rem; color: #fff;">
            This installation is operating with default credentials (callsign 'admin' and/or default access key). Please update your credentials to safeguard the archive.
          </p>
        </div>
        <a href="security.php" class="cta-button" style="border-color: var(--accent-orange); color: var(--accent-orange); font-size: 0.75rem; padding: 8px 16px;">
          CONFIGURE SECURITY CLEARANCE →
        </a>
      </div>
    <?php endif; ?>

    <!-- Telemetry Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
      <div class="relic-hud-card">
        <div class="mono-label" style="color: var(--accent-blue);">TOTAL MISSIONS</div>
        <div style="font-size: 2.2rem; font-weight: 800; color: #fff; margin: 6px 0;"><?= $missionsCount ?></div>
        <div class="mono-label" style="font-size: 0.65rem;">Active flight dossiers</div>
      </div>

      <div class="relic-hud-card">
        <div class="mono-label" style="color: var(--accent-orange);">EQUIPMENT UNITS</div>
        <div style="font-size: 2.2rem; font-weight: 800; color: #fff; margin: 6px 0;"><?= $equipmentCount ?></div>
        <div class="mono-label" style="font-size: 0.65rem;">Rovers, landers, orbiters</div>
      </div>

      <div class="relic-hud-card">
        <div class="mono-label" style="color: var(--accent-cyan);">OFF-WORLD RELICS</div>
        <div style="font-size: 2.2rem; font-weight: 800; color: #fff; margin: 6px 0;"><?= $relicsCount ?></div>
        <div class="mono-label" style="font-size: 0.65rem;">Moon, Mars, deep space</div>
      </div>

      <div class="relic-hud-card">
        <div class="mono-label" style="color: var(--accent-emerald);">PARTNER AGENCIES</div>
        <div style="font-size: 2.2rem; font-weight: 800; color: #fff; margin: 6px 0;"><?= $agenciesCount ?></div>
        <div class="mono-label" style="font-size: 0.65rem;">Global space organizations</div>
      </div>

      <div class="relic-hud-card">
        <div class="mono-label" style="color: var(--accent-purple);">SCIENCE PAYLOADS</div>
        <div style="font-size: 2.2rem; font-weight: 800; color: #fff; margin: 6px 0;"><?= $instrumentsCount ?></div>
        <div class="mono-label" style="font-size: 0.65rem;">Spectrometers, imagers, sensors</div>
      </div>
    </div>

    <!-- Quick Actions & Recent Missions -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
      <div class="relic-hud-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 style="font-size: 1.25rem; font-weight: 700; color: #fff;">RECENT MISSIONS</h2>
          <a href="missions.php?action=create" class="cta-button" style="padding: 6px 14px; font-size: 0.72rem;">+ NEW MISSION</a>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.85rem;">
          <thead>
            <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-family: var(--font-mono); font-size: 0.7rem;">
              <th style="padding: 8px 6px;">ID</th>
              <th style="padding: 8px 6px;">MISSION</th>
              <th style="padding: 8px 6px;">AGENCY</th>
              <th style="padding: 8px 6px;">DESTINATION</th>
              <th style="padding: 8px 6px;">STATUS</th>
              <th style="padding: 8px 6px; text-align: right;">ACTION</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentMissions as $rm): ?>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                <td style="padding: 10px 6px; font-family: var(--font-mono); color: var(--text-muted);"><?= $rm['id'] ?></td>
                <td style="padding: 10px 6px; font-weight: 600; color: #fff;"><?= htmlspecialchars($rm['name']) ?></td>
                <td style="padding: 10px 6px; font-family: var(--font-mono); color: var(--accent-blue);"><?= htmlspecialchars($rm['agency_code']) ?></td>
                <td style="padding: 10px 6px; color: var(--accent-orange);"><?= htmlspecialchars($rm['destination_name']) ?></td>
                <td style="padding: 10px 6px; font-family: var(--font-mono); font-size: 0.75rem; color: <?= strtolower($rm['status']) === 'active' ? 'var(--accent-emerald)' : 'var(--text-secondary)' ?>;"><?= strtoupper(htmlspecialchars($rm['status'])) ?></td>
                <td style="padding: 10px 6px; text-align: right;">
                  <a href="missions.php?action=edit&id=<?= $rm['id'] ?>" class="relic-cta-link" style="font-size: 0.72rem;">EDIT</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="relic-hud-card">
        <h2 style="font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 1rem;">DATABASE PROTOCOL</h2>
        <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 1.5rem;">
          All mission updates propagate immediately to the REST APIs and interactive Three.js/SVG visualizations.
        </p>
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <a href="missions.php?action=create" class="cta-button" style="justify-content: center; font-size: 0.75rem; padding: 10px;">+ RECORD NEW MISSION</a>
          <a href="equipment.php?action=create" class="cta-button" style="justify-content: center; font-size: 0.75rem; padding: 10px; border-color: var(--accent-orange); color: var(--accent-orange);">+ CATALOG RELIC / HARDWARE</a>
          <a href="instruments.php?action=create" class="cta-button" style="justify-content: center; font-size: 0.75rem; padding: 10px; border-color: var(--accent-purple); color: var(--accent-purple);">+ ADD SCIENTIFIC INSTRUMENT</a>
          <a href="security.php" class="filter-btn" style="text-align: center;">SECURITY CLEARANCE 🔒</a>
          <a href="../api/stats.php" target="_blank" class="filter-btn" style="text-align: center;">TEST /api/stats.php ↗</a>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
