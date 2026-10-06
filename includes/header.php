<?php
/**
 * ORBITAL ARCHIVE - Global Header Include
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'ORBITAL ARCHIVE — A Global Record of Human Space Exploration';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="A cinematic, database-driven digital archive documenting everything humanity has launched beyond Earth.">
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%236EA8FF'><path d='M12 2L15 9L22 12L15 15L12 22L9 15L2 12L9 9Z'/></svg>">
  
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
