<?php
/**
 * ORBITAL ARCHIVE - Global Footer & Telemetry Modals
 */
?>
<footer class="orbital-footer">
  <div class="footer-top-grid">
    <div class="footer-brand-summary">
      <div class="logo-brand" style="font-size: 1.25rem;">
        ORBITAL <span class="logo-bracket">[ ARCHIVE ]</span>
      </div>
      <p class="footer-summary-text">
        An authoritative, normalized digital registry documenting the spacecraft, landers, rovers, and observatories dispatched beyond Earth's atmosphere. Developed with PHP, MySQL, vanilla JavaScript, and Three.js.
      </p>
      <div class="mono-label" style="color: var(--accent-blue);">
        LATENCY: 0.04 MS <span class="hud-bracket">//</span> ENGINE: PHP PDO 8.3 <span class="hud-bracket">//</span> ARCHIVE PROTOCOL V4
      </div>
    </div>

    <div>
      <div class="footer-col-title">ARCHIVE SECTIONS</div>
      <ul class="footer-link-list">
        <li><a href="explore.php" class="footer-link">Full Catalog Explorer</a></li>
        <li><a href="missions.php" class="footer-link">Global Missions Database</a></li>
        <li><a href="relics.php" class="footer-link">Off-World Relics Vault</a></li>
        <li><a href="timeline.php" class="footer-link">Humanity's Flight Timeline</a></li>
        <li><a href="destinations.php" class="footer-link">Planetary Destinations</a></li>
      </ul>
    </div>

    <div>
      <div class="footer-col-title">SPACE AGENCIES</div>
      <ul class="footer-link-list">
        <li><a href="agencies.php?id=1" class="footer-link">NASA (United States)</a></li>
        <li><a href="agencies.php?id=2" class="footer-link">ISRO (India)</a></li>
        <li><a href="agencies.php?id=3" class="footer-link">ESA (Europe)</a></li>
        <li><a href="agencies.php?id=4" class="footer-link">JAXA (Japan)</a></li>
        <li><a href="agencies.php?id=5" class="footer-link">CNSA (China)</a></li>
        <li><a href="agencies.php" class="footer-link">Commercial Space Sector</a></li>
      </ul>
    </div>

    <div>
      <div class="footer-col-title">DEVELOPER & ADMIN</div>
      <ul class="footer-link-list">
        <li><a href="admin/login.php" class="footer-link">Mission Control Login</a></li>
        <li><a href="api/missions.php" target="_blank" class="footer-link">REST API Documentation</a></li>
        <li><button type="button" class="footer-link open-ai-modal" style="text-align: left;">Ask The Archive AI</button></li>
        <li><button type="button" class="footer-link open-search-modal" style="text-align: left;">Search Database (/)</button></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom-bar">
    <div>
      &copy; <?= date('Y') ?> ORBITAL ARCHIVE. DATA DERIVED FROM NASA, ISRO, ESA, JAXA PUBLIC DOMAIN SCIENCE ARCHIVES.
    </div>
    <div style="display: flex; align-items: center; gap: 1.5rem;">
      <span>SYSTEM STATUS: <span style="color: var(--accent-emerald);">ONLINE (NOMINAL)</span></span>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="var(--accent-blue)">
        <path d="M12 2L15 9L22 12L15 15L12 22L9 15L2 12L9 9Z"/>
      </svg>
    </div>
  </div>
</footer>

<!-- 1. SEARCH MODAL -->
<div class="modal-overlay" id="search-modal">
  <div class="modal-window">
    <div class="modal-header">
      <div class="modal-title">ORBITAL ARCHIVE TELEMETRY SEARCH</div>
      <div class="modal-close-btn" id="close-search-modal">&times;</div>
    </div>
    <div class="modal-body">
      <div class="search-input-box">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="search-input" class="search-input-field" placeholder="Search Opportunity, Apollo 15, Chandrayaan, Voyager, Mars..." autocomplete="off">
      </div>
      <div id="search-results" class="search-results-list">
        <div style="color: var(--text-muted); font-size: 0.85rem; padding: 0.5rem 0;">
          Press <span class="search-shortcut">ESC</span> to dismiss. Search query searches across missions, rovers, agencies, and destinations.
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 2. "ASK THE ARCHIVE" AI MODAL -->
<div class="modal-overlay" id="ai-modal">
  <div class="modal-window">
    <div class="modal-header">
      <div class="modal-title" style="display: flex; align-items: center; gap: 8px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="var(--accent-cyan)">
          <path d="M12 2L15 9L22 12L15 15L12 22L9 15L2 12L9 9Z"/>
        </svg>
        ASK THE ARCHIVE — FACT-GROUNDED AI
      </div>
      <div class="modal-close-btn" id="close-ai-modal">&times;</div>
    </div>
    <div class="modal-body">
      <div style="font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 1rem;">
        Query the verified space history database. All responses are derived strictly from archived mission records.
      </div>

      <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 1.2rem;">
        <button type="button" class="ai-suggested-chip filter-btn" style="font-size: 0.68rem;">"What did Opportunity discover?"</button>
        <button type="button" class="ai-suggested-chip filter-btn" style="font-size: 0.68rem;">"Which missions landed on Mars?"</button>
        <button type="button" class="ai-suggested-chip filter-btn" style="font-size: 0.68rem;">"What equipment is still on the Moon?"</button>
        <button type="button" class="ai-suggested-chip filter-btn" style="font-size: 0.68rem;">"Which spacecraft are currently in deep space?"</button>
      </div>

      <div id="ai-chat-log" style="display: flex; flex-direction: column; min-height: 180px; max-height: 320px; overflow-y: auto; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1rem;">
        <div style="padding: 10px 14px; background: rgba(11, 17, 24, 0.9); border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.85rem; color: var(--text-secondary);">
          <span class="mono-label" style="color: var(--accent-blue)">AI ARCHIVE AGENT READY</span><br>
          Ask questions about any space mission, relic, launch date, planetary discoveries, or space agency program.
        </div>
      </div>

      <div class="search-input-box" style="margin-bottom: 0;">
        <input type="text" id="ai-question-input" class="search-input-field" placeholder="Ask a factual question about humanity's space history...">
        <button type="button" id="ai-send-btn" class="cta-button" style="padding: 8px 16px; font-size: 0.72rem;">TRANSMIT</button>
      </div>
    </div>
  </div>
</div>

<!-- Core Modular JavaScript Components -->
<script src="assets/js/earth.js"></script>
<script src="assets/js/orbit.js"></script>
<script src="assets/js/mission-map.js"></script>
<script src="assets/js/agency-network.js"></script>
<script src="assets/js/relics.js"></script>
<script src="assets/js/timeline.js"></script>
<script src="assets/js/navigation.js"></script>
<script src="assets/js/main.js"></script>

</body>
</html>
