<?php
/**
 * ORBITAL ARCHIVE - Admin Security & Clearance Management
 * Allows operators to update callsign and security access keys (passwords).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();
$pdo = getDB();

$userId = $_SESSION['admin_user_id'] ?? 1;
$stmt = $pdo->prepare("SELECT id, username, email, password_hash, role, created_at FROM users WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $userId]);
$currentUser = $stmt->fetch();

if (!$currentUser) {
    header('Location: login.php');
    exit;
}

$isDefaultKey = password_verify('admin123', $currentUser['password_hash']);
$isDefaultCallsign = ($currentUser['username'] === 'admin');

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!verifyCsrfToken($csrf)) {
        $error = 'Security validation failed (CSRF mismatch).';
    } elseif ($action === 'update_profile') {
        $newUsername = trim($_POST['username'] ?? '');
        $newEmail = trim($_POST['email'] ?? '');

        if (empty($newUsername)) {
            $error = 'Operator callsign cannot be empty.';
        } else {
            // Check if username taken by another user
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :u AND id != :id LIMIT 1");
            $checkStmt->execute(['u' => $newUsername, 'id' => $userId]);
            if ($checkStmt->fetch()) {
                $error = "Callsign '$newUsername' is already assigned to another terminal.";
            } else {
                $upStmt = $pdo->prepare("UPDATE users SET username = :u, email = :e WHERE id = :id");
                $upStmt->execute(['u' => $newUsername, 'e' => $newEmail, 'id' => $userId]);
                $_SESSION['admin_username'] = $newUsername;
                $message = "Operator identity successfully updated to '$newUsername'.";
                
                // Refresh
                $stmt->execute(['id' => $userId]);
                $currentUser = $stmt->fetch();
                $isDefaultCallsign = ($currentUser['username'] === 'admin');
            }
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $currentUser['password_hash'])) {
            $error = 'Authentication denied: Current access key is incorrect.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'Access key must be at least 8 characters in length.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New access key confirmation does not match.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $upStmt = $pdo->prepare("UPDATE users SET password_hash = :h WHERE id = :id");
            $upStmt->execute(['h' => $newHash, 'id' => $userId]);
            $message = 'Security clearance access key updated successfully. Encryption verified.';

            // Refresh
            $stmt->execute(['id' => $userId]);
            $currentUser = $stmt->fetch();
            $isDefaultKey = password_verify('admin123', $currentUser['password_hash']);
        }
    }
}

$pageTitle = 'Security Clearance // Mission Control — ORBITAL ARCHIVE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
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
        SECURITY CLEARANCE // OPERATOR: <?= strtoupper(htmlspecialchars($_SESSION['admin_username'])) ?>
      </div>
    </div>
    <div style="display: flex; gap: 1rem; align-items: center;">
      <a href="index.php" class="filter-btn">← DASHBOARD</a>
      <a href="logout.php" class="filter-btn" style="border-color: var(--accent-rose); color: var(--accent-rose);">LOGOUT</a>
    </div>
  </header>

  <main style="max-width: 1000px; margin: 0 auto; padding: 2rem 2rem 6rem; position: relative; z-index: 10;">
    
    <!-- Tab Navigation -->
    <div style="display: flex; gap: 8px; margin-bottom: 2.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; flex-wrap: wrap;">
      <a href="index.php" class="filter-btn">DASHBOARD OVERVIEW</a>
      <a href="missions.php" class="filter-btn">MANAGE MISSIONS</a>
      <a href="equipment.php" class="filter-btn">MANAGE EQUIPMENT</a>
      <a href="instruments.php" class="filter-btn">MANAGE INSTRUMENTS</a>
      <a href="security.php" class="filter-btn active">SECURITY CLEARANCE</a>
    </div>

    <!-- Security Status HUD -->
    <div class="relic-hud-card" style="margin-bottom: 2.5rem; border-color: <?= ($isDefaultKey || $isDefaultCallsign) ? 'var(--accent-orange)' : 'var(--accent-emerald)' ?>;">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
          <div class="mono-label" style="color: <?= ($isDefaultKey || $isDefaultCallsign) ? 'var(--accent-orange)' : 'var(--accent-emerald)' ?>;">
            SYSTEM SECURITY STATUS: <?= ($isDefaultKey || $isDefaultCallsign) ? 'SECURITY ADVISORY ACTIVE' : 'AUTHENTICATED & SECURED' ?>
          </div>
          <div style="font-size: 1.15rem; font-weight: 700; color: #fff; margin-top: 4px;">
            <?= ($isDefaultKey || $isDefaultCallsign) ? 'Default installation credentials detected. Please rotate access keys.' : 'Production encryption standards enforced.' ?>
          </div>
        </div>
        <div style="text-align: right;">
          <span class="status-indicator <?= ($isDefaultKey || $isDefaultCallsign) ? 'status-planned' : 'status-active' ?>" style="font-size: 0.75rem; padding: 6px 14px;">
            <?= ($isDefaultKey || $isDefaultCallsign) ? 'DEFAULT KEY IN USE' : 'PROTECTED' ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($message)): ?>
      <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid var(--accent-emerald); color: #fff; padding: 12px 18px; border-radius: 2px; margin-bottom: 2rem; font-family: var(--font-mono); font-size: 0.85rem;">
        ✓ <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid var(--accent-rose); color: #fff; padding: 12px 18px; border-radius: 2px; margin-bottom: 2rem; font-family: var(--font-mono); font-size: 0.85rem;">
        ✕ <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      
      <!-- Callsign / Profile Update -->
      <div class="relic-hud-card">
        <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 8px;">TERMINAL IDENTIFIER</div>
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem;">UPDATE CALLSIGN</h2>

        <form method="POST" action="security.php">
          <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
          <input type="hidden" name="action" value="update_profile">

          <div style="margin-bottom: 1.5rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">OPERATOR CALLSIGN *</label>
            <input type="text" name="username" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 10px 14px; width: 100%; border-radius: 2px;" value="<?= htmlspecialchars($currentUser['username']) ?>" required>
          </div>

          <div style="margin-bottom: 1.5rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">OFFICIAL EMAIL</label>
            <input type="email" name="email" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 10px 14px; width: 100%; border-radius: 2px;" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" placeholder="operator@orbitalarchive.space">
          </div>

          <div style="margin-bottom: 1.5rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">ROLE / CLEARANCE LEVEL</label>
            <input type="text" class="search-input-field" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); padding: 10px 14px; width: 100%; border-radius: 2px; color: var(--text-muted);" value="<?= strtoupper(htmlspecialchars($currentUser['role'])) ?>" readonly>
          </div>

          <button type="submit" class="cta-button" style="width: 100%; justify-content: center; padding: 12px;">
            SAVE IDENTITY SETTINGS →
          </button>
        </form>
      </div>

      <!-- Password / Access Key Rotation -->
      <div class="relic-hud-card">
        <div class="mono-label" style="color: var(--accent-orange); margin-bottom: 8px;">CRYPTOGRAPHIC CREDENTIALS</div>
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem;">ROTATE ACCESS KEY</h2>

        <form method="POST" action="security.php">
          <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
          <input type="hidden" name="action" value="change_password">

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">CURRENT ACCESS KEY *</label>
            <input type="password" name="current_password" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 10px 14px; width: 100%; border-radius: 2px;" placeholder="Enter current password" required>
          </div>

          <div style="margin-bottom: 1.2rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">NEW ACCESS KEY (MIN 8 CHARS) *</label>
            <input type="password" name="new_password" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 10px 14px; width: 100%; border-radius: 2px;" placeholder="••••••••••••" required>
          </div>

          <div style="margin-bottom: 1.5rem;">
            <label class="mono-label" style="display: block; margin-bottom: 6px;">CONFIRM NEW ACCESS KEY *</label>
            <input type="password" name="confirm_password" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 10px 14px; width: 100%; border-radius: 2px;" placeholder="••••••••••••" required>
          </div>

          <button type="submit" class="cta-button" style="width: 100%; justify-content: center; padding: 12px; border-color: var(--accent-orange); color: var(--accent-orange);">
            ROTATE ACCESS KEY 🔒
          </button>
        </form>

        <div class="mono-label" style="margin-top: 1.5rem; color: var(--text-muted); font-size: 0.65rem; line-height: 1.6;">
          Security standard: Keys are salted and hashed with BCRYPT (Cost 10) algorithm in accordance with PHP 8.3 password security standards.
        </div>
      </div>

    </div>

  </main>
</body>
</html>
