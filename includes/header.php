<?php
/**
 * ORBITAL ARCHIVE - Global Header Include
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'ORBITAL ARCHIVE — A Global Record of Human Space Exploration';
$metaDescription = $metaDescription ?? 'A cinematic, database-driven digital archive documenting everything humanity has launched beyond Earth.';
$ogTitle = $ogTitle ?? $pageTitle;
$ogDescription = $ogDescription ?? $metaDescription;
$ogImage = $ogImage ?? 'https://images-assets.nasa.gov/image/PIA24426/PIA24426~orig.jpg';
$appBaseUrl = rtrim(getenv('APP_URL') ?: '', '/');
if (empty($appBaseUrl)) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $appBaseUrl = "$scheme://$host";
}
$canonicalUrl = $canonicalUrl ?? ($appBaseUrl . ($_SERVER['REQUEST_URI'] ?? '/'));

if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
  <?php if (!empty($canonicalUrl)): ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
  <?php endif; ?>

  <!-- Open Graph / Social Metadata -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= htmlspecialchars($ogTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($ogDescription) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
  <?php if (!empty($canonicalUrl)): ?>
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
  <?php endif; ?>

  <!-- Twitter Card Metadata -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($ogTitle) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($ogDescription) ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">

  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%236EA8FF'><path d='M12 2L15 9L22 12L15 15L12 22L9 15L2 12L9 9Z'/></svg>">
  
  <!-- Theme Initialization (Zero-FOUC) -->
  <script>
    (function() {
      try {
        var savedTheme = localStorage.getItem('orbital_theme') || 'night';
        document.documentElement.setAttribute('data-theme', savedTheme);
      } catch (e) {}
    })();
  </script>

  <!-- Design System CSS -->
  <link rel="stylesheet" href="assets/css/main.css">
  <link rel="stylesheet" href="assets/css/animations.css">
  <link rel="stylesheet" href="assets/css/responsive.css">

  <!-- Stable Three.js CDN for photorealistic Earth & orbital rings -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
</head>
<body>
  <!-- Ambient Global Tech Grid Overlay -->
  <div class="bg-grid"></div>
