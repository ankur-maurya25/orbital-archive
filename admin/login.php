<?php
/**
 * ORBITAL ARCHIVE - Admin Mission Control Login
 * Credentials seeded: admin / admin123
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';

    if (!verifyCsrfToken($csrf)) {
        $error = 'Security validation failed (CSRF mismatch).';
    } elseif (empty($username) || empty($password)) {
        $error = 'Username and security credentials required.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = :u LIMIT 1");
        $stmt->execute(['u' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Authentication denied: Invalid security clearance credentials.';
        }
    }
}

$pageTitle = 'Mission Control Authentication — ORBITAL ARCHIVE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem;">
  <div class="bg-grid"></div>

  <div class="relic-hud-card" style="width: 100%; max-width: 440px; position: relative; z-index: 10; padding: 2.5rem;">
    <div style="text-align: center; margin-bottom: 2rem;">
      <div class="logo-brand" style="justify-content: center; font-size: 1.3rem;">
        ORBITAL <span class="logo-bracket">[ ARCHIVE ]</span>
      </div>
      <div class="mono-label" style="color: var(--accent-cyan); margin-top: 6px;">
        MISSION CONTROL // SECURE PORTAL
      </div>
    </div>

    <?php if (!empty($error)): ?>
      <div style="background: rgba(244, 63, 94, 0.15); border: 1px solid var(--accent-rose); color: #fff; padding: 10px 14px; border-radius: 2px; font-size: 0.82rem; margin-bottom: 1.5rem;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

      <div style="margin-bottom: 1.2rem;">
        <label class="mono-label" style="display: block; margin-bottom: 6px;">OPERATOR CALLSIGN</label>
        <input type="text" name="username" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 10px 14px; width: 100%; border-radius: 2px;" placeholder="admin" required autofocus>
      </div>

      <div style="margin-bottom: 1.8rem;">
        <label class="mono-label" style="display: block; margin-bottom: 6px;">ACCESS KEY</label>
        <input type="password" name="password" class="search-input-field" style="background: rgba(5,7,11,0.8); border: 1px solid var(--border-color); padding: 10px 14px; width: 100%; border-radius: 2px;" placeholder="••••••••" required>
      </div>

      <button type="submit" class="cta-button" style="width: 100%; justify-content: center; padding: 12px;">
        AUTHENTICATE →
      </button>

      <div class="mono-label" style="text-align: center; margin-top: 1.5rem; color: var(--text-muted); font-size: 0.65rem;">
        DEFAULT CREDENTIALS: admin / admin123
      </div>
    </form>

    <div style="text-align: center; margin-top: 1.5rem;">
      <a href="../index.php" style="color: var(--text-secondary); font-size: 0.8rem;">← Back to Observatory</a>
    </div>
  </div>
</body>
</html>
