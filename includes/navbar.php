<?php
/**
 * ORBITAL ARCHIVE - Navigation Bar Component
 * Recreates the exact HUD header from the reference design
 */
$isHome = basename($_SERVER['PHP_SELF']) === 'index.php';
?>
<header class="orbital-header">
  <div class="logo-area">
    <a href="index.php" class="logo-brand">
      ORBITAL <span class="logo-bracket">[ ARCHIVE ]</span>
    </a>
    <div class="logo-tagline">
      A GLOBAL RECORD OF HUMAN SPACE EXPLORATION <span class="hud-bracket">//</span> NASA SPACE APPS 2026
    </div>
  </div>

  <div class="nav-pill-container">
    <nav class="nav-pill">
      <a href="<?= $isHome ? '#where-have-we-been' : 'explore.php' ?>" class="nav-item">
        EXPLORE <span class="nav-index">[01]</span>
      </a>
      <a href="<?= $isHome ? '#global-mission-map' : 'missions.php' ?>" class="nav-item">
        MISSIONS <span class="nav-index">[02]</span>
      </a>
      <a href="<?= $isHome ? '#agency-networks' : 'agencies.php' ?>" class="nav-item">
        AGENCIES <span class="nav-index">[03]</span>
      </a>
      <a href="<?= $isHome ? '#timeline' : 'timeline.php' ?>" class="nav-item">
        TIMELINE <span class="nav-index">[04]</span>
      </a>
    </nav>

    <!-- Search shortcut trigger -->
    <button type="button" class="search-trigger open-search-modal" title="Search the Archive (Ctrl+K or /)" aria-label="Open Archive Search (Ctrl+K or /)">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
      <span>SEARCH</span>
      <span class="search-shortcut">Ctrl+K</span>
    </button>

    <!-- AI Ask Archive button -->
    <button type="button" class="search-trigger open-ai-modal" title="Ask the Archive AI Assistant" aria-label="Ask the Archive AI Assistant" style="border-color: rgba(110, 168, 255, 0.4);">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2L15 9L22 12L15 15L12 22L9 15L2 12L9 9Z"/>
      </svg>
      <span style="color: var(--accent-cyan)">ASK AI</span>
    </button>
  </div>
</header>
