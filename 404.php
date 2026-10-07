<?php
/**
 * ORBITAL ARCHIVE - 404 Telemetry Not Found
 * Maintains the visual design system of the digital observatory
 */

http_response_code(404);

$pageTitle = "404 — Telemetry Not Found // ORBITAL ARCHIVE";
$metaDescription = "The requested mission, spacecraft exhibit, or astronomical coordinate epoch was not located in the Orbital Archive.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="archive-main" style="padding-top: 8rem; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
  <div style="max-width: 760px; margin: 0 auto; padding: 2rem; text-align: center;">
    
    <div class="mono-label" style="color: var(--accent-rose); letter-spacing: 0.25em; font-size: 0.85rem; margin-bottom: 1rem;">
      [ERROR 404] // SIGNAL NOT DETECTED IN ARCHIVE
    </div>

    <h1 style="font-family: var(--font-serif); font-size: clamp(2.4rem, 6vw, 3.8rem); font-weight: 700; color: #fff; line-height: 1.15; margin-bottom: 1.5rem; text-transform: uppercase;">
      Coordinates Uncataloged
    </h1>

    <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-secondary); margin-bottom: 2.5rem; max-width: 620px; margin-left: auto; margin-right: auto;">
      The requested spacecraft record, mission trajectory, or exhibition artifact does not exist within humanity's verified space exploration registry.
    </p>

    <!-- Telemetry Diagnostic Box -->
    <div class="relic-hud-card" style="padding: 1.5rem 2rem; margin-bottom: 3rem; text-align: left; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; border-color: rgba(244, 63, 94, 0.3);">
      <div>
        <div class="mono-label" style="color: var(--text-muted);">DIAGNOSTIC STATUS</div>
        <div class="mono-value" style="color: var(--accent-rose); font-weight: 700;">TRANSPONDER SILENT</div>
      </div>
      <div>
        <div class="mono-label" style="color: var(--text-muted);">CATALOG SEARCH</div>
        <div class="mono-value" style="color: var(--accent-cyan);">TRY SHORTCUT (Ctrl+K)</div>
      </div>
      <div>
        <div class="mono-label" style="color: var(--text-muted);">REGISTRY INTEGRITY</div>
        <div class="mono-value" style="color: var(--accent-emerald);">VERIFIED NOMINAL</div>
      </div>
    </div>

    <!-- Quick Navigation Recovery Actions -->
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
      <a href="index.php" class="cta-button" style="text-decoration: none; padding: 12px 24px;">
        RETURN TO ARCHIVE HOME →
      </a>
      <a href="relics.php" class="relic-cta-link" style="padding: 12px 20px; border: 1px solid var(--border-color); border-radius: 2px;">
        OFF-WORLD RELICS
      </a>
      <a href="missions.php" class="relic-cta-link" style="padding: 12px 20px; border: 1px solid var(--border-color); border-radius: 2px;">
        GLOBAL MISSIONS
      </a>
      <button type="button" class="relic-cta-link open-search-modal" style="padding: 12px 20px; border: 1px solid var(--border-color); border-radius: 2px; cursor: pointer;">
        SEARCH ARCHIVE (Ctrl+K)
      </button>
    </div>

  </div>
</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
