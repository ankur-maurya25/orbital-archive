<?php
/**
 * ORBITAL ARCHIVE - Cinematic Homepage
 * Flow:
 * 01 — EARTH HERO
 * 02 — WHERE HAVE WE BEEN?
 * 03 — GLOBAL MISSIONS MAP
 * 04 — SPACE AGENCY NETWORKS
 * 05 — THE MACHINES WE LEFT BEHIND
 * 06 — HUMANITY'S TIMELINE
 */

$pageTitle = 'ORBITAL ARCHIVE — A Global Record of Human Space Exploration';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$pdo = getDB();

// Fetch initial relic record for Section 05
$initialRelicStmt = $pdo->prepare("
  SELECT e.*, m.name AS mission_name, YEAR(m.launch_date) AS launch_year, d.name AS destination_name,
         img.image_url AS hero_image
  FROM equipment e
  JOIN missions m ON e.mission_id = m.id
  LEFT JOIN destinations d ON m.destination_id = d.id
  LEFT JOIN images img ON (img.equipment_id = e.id AND img.image_type = 'hero')
  WHERE e.name LIKE '%Apollo 15 Lunar Roving Vehicle%'
  LIMIT 1
");
$initialRelicStmt->execute();
$apollo15 = $initialRelicStmt->fetch();

// Fetch timeline milestone for 1969 for Section 06
$t1969Stmt = $pdo->query("
  SELECT t.*, m.name AS mission_name, e.type AS eq_type
  FROM timeline_events t
  LEFT JOIN missions m ON t.mission_id = m.id
  LEFT JOIN equipment e ON t.equipment_id = e.id
  WHERE t.year = 1969
  LIMIT 1
");
$milestone1969 = $t1969Stmt->fetch();
?>

<main class="archive-main">

  <!-- ==============================================================
       SECTION 01: HERO & THREE.JS EARTH
       ============================================================== -->
  <section class="hero-section" id="hero">
    <!-- Top left intro tag -->
    <div class="hero-content-top">
      <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 0.5rem;">
        RECORD NUMBER // AGY-0094-EARTH-SYS
      </div>
    </div>

    <!-- Center Interactive Three.js Earth Sphere -->
    <div class="earth-canvas-container" id="earth-canvas-container">
      <!-- Live Orbit Telemetry Tags -->
      <div class="hero-orbit-tag" style="top: 28%; left: 16%;">ISS [400 KM]</div>
      <div class="hero-orbit-tag" style="top: 66%; right: 14%;">HUBBLE [540 KM]</div>
      <div class="hero-orbit-tag" style="top: 18%; right: 26%;">GEOSTATIONARY</div>
      <div class="hero-orbit-tag" style="bottom: 22%; left: 24%;">TIANGONG [390 KM]</div>
    </div>

    <!-- Bottom Split Content -->
    <div class="hero-content-bottom">
      <div class="hero-headline-group">
        <h1 class="hero-title">
          EVERYTHING<br>
          <span class="hero-title-italic">HUMANITY</span> HAS SENT<br>
          BEYOND EARTH.
        </h1>
        <a href="#where-have-we-been" class="cta-button">
          ENTER THE ARCHIVE <span class="cta-arrow">→</span>
        </a>
      </div>

      <!-- Center Scroll Cue -->
      <a href="#where-have-we-been" class="scroll-indicator">
        <span>( SCROLL TO EXPLORE )</span>
        <span class="scroll-arrow">↓</span>
      </a>

      <!-- Right HUD Telemetry Counters -->
      <div class="hero-telemetry-hud">
        <div class="hud-sparkline">
          <svg class="hud-waveform" viewBox="0 0 140 28" fill="none">
            <path d="M0 14 L20 14 L28 4 L36 24 L44 10 L52 18 L60 14 L80 14 L86 2 L94 26 L102 12 L110 16 L118 14 L140 14" stroke="var(--accent-cyan)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" opacity="0.8"/>
          </svg>
          <span class="mono-label">DEEP SPACE TELEMETRY</span>
        </div>

        <div class="hud-metrics">
          <div class="hud-metric-item">
            <span class="hud-metric-label">MISSIONS CATALOGED</span>
            <span class="hud-metric-num" id="stat-missions-count">30+</span>
          </div>
          <div class="hud-metric-item">
            <span class="hud-metric-label">HARDWARE RECORDS</span>
            <span class="hud-metric-num" id="stat-objects-archived">30+</span>
          </div>
          <div class="hud-metric-item">
            <span class="hud-metric-label">OFF-WORLD RELICS</span>
            <span class="hud-metric-num" id="stat-relics-count">18</span>
          </div>
        </div>

        <!-- Four-Point Cosmic Star Emblem -->
        <svg class="cosmic-emblem" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2L14.8 9.2L22 12L14.8 14.8L12 22L9.2 14.8L2 12L9.2 9.2Z"/>
        </svg>
      </div>
    </div>
  </section>


  <!-- ==============================================================
       SECTION 02: WHERE HAVE WE BEEN?
       ============================================================== -->
  <section class="section-orbital-destinations" id="where-have-we-been">
    <div class="section-top-bar">
      <div class="section-title-badge">
        <h2 class="section-title">WHERE HAVE WE BEEN</h2>
      </div>
      <div class="mono-label">EXPLORATION HORIZONS // 1957–PRESENT</div>
    </div>

    <div class="orbital-destination-stage" id="orbital-destinations-stage">
      <!-- Massive Graphic Typography on Left -->
      <div class="destination-sidebar">
        <div class="destination-big-vertical-text">
          WHERE<br>HAVE<br>WE<br>BEEN?
        </div>
        <p class="destination-sidebar-desc">
          From Low Earth Orbit to the edge of the heliosphere, humanity has dispatched robotic probes, orbiters, and crewed expeditions across the solar system.
        </p>
        <div id="destination-hover-card" class="relic-hud-card" style="margin-top: 1.5rem; opacity: 0.8; transition: all 0.3s;">
          <div class="mono-label" style="color: var(--accent-blue)">SELECT ORBITAL NODE</div>
          <div style="font-size: 1.1rem; color: #fff; margin: 4px 0;">Hover over any destination to inspect trajectory records.</div>
          <div class="mono-label" style="color: var(--text-muted)">TARGETS: MOON, MARS, VENUS, DEEP SPACE</div>
        </div>
      </div>

      <!-- Orbital Orbits Graphic Viewport -->
      <div class="orbital-paths-viewport">
        <svg class="orbits-svg" viewBox="0 0 900 650" fill="none">
          <!-- Background radial rings -->
          <circle cx="450" cy="325" r="90" stroke="rgba(255,255,255,0.06)" stroke-width="1" stroke-dasharray="2 3"/>
          <circle cx="450" cy="325" r="190" stroke="rgba(255,255,255,0.05)" stroke-width="1" stroke-dasharray="2 3"/>
          <circle cx="450" cy="325" r="300" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>

          <!-- Hohmann / Transfer Ellipses to Planetary Bodies -->
          <!-- Moon Orbit Path -->
          <path id="orbit-moon-path" class="orbital-svg-path" d="M 450 325 C 450 200, 240 180, 210 240 C 180 300, 360 400, 450 325" stroke="rgba(110, 168, 255, 0.45)" stroke-width="1.5" stroke-dasharray="4 3"/>
          <!-- Mars Orbit Path (Orange Ellipse) -->
          <ellipse id="orbit-mars-path" class="orbital-svg-path" cx="480" cy="310" rx="330" ry="170" transform="rotate(-18 480 310)" stroke="rgba(217, 154, 91, 0.65)" stroke-width="1.8"/>
          <!-- Venus Orbit Path -->
          <ellipse id="orbit-venus-path" class="orbital-svg-path" cx="440" cy="335" rx="270" ry="140" transform="rotate(32 440 335)" stroke="rgba(56, 189, 248, 0.5)" stroke-width="1.5"/>
          <!-- Deep Space Probe Trajectory (Sweeping Outward Curve) -->
          <path id="orbit-deepspace-path" class="orbital-svg-path" d="M 450 325 C 500 420, 680 480, 710 460 C 760 420, 830 310, 780 290" stroke="rgba(16, 185, 129, 0.55)" stroke-width="1.5" stroke-dasharray="5 4"/>
        </svg>

        <!-- Central Earth Globe Anchor -->
        <div class="orbit-central-body" style="background: radial-gradient(circle at 35% 35%, #1e3a8a, #030712); border: 2px solid var(--accent-cyan);" title="Earth (Origin)">
          <div style="position: absolute; inset: 0; border-radius: 50%; box-shadow: inset 0 0 20px rgba(56, 189, 248, 0.6);"></div>
        </div>

        <!-- 1. Moon Destination Node -->
        <div class="destination-node" style="top: 32%; left: 21%;" data-dest-id="2" data-dest-name="Moon" data-missions="142" data-distance="384,400 KM" data-orbit-id="orbit-moon-path">
          <div class="node-sphere" style="background: radial-gradient(circle at 35% 35%, #cbd5e1, #1e293b);"></div>
          <div class="node-card">
            <div class="node-title">MOON</div>
            <div class="node-badge">// 142 MISSIONS</div>
          </div>
        </div>

        <!-- 2. Mars Destination Node -->
        <div class="destination-node" style="top: 20%; left: 58%;" data-dest-id="3" data-dest-name="Mars" data-missions="56" data-distance="225M KM (AVG)" data-orbit-id="orbit-mars-path">
          <div class="node-sphere" style="background: radial-gradient(circle at 35% 35%, #ea580c, #431407); border-color: var(--accent-orange);"></div>
          <div class="node-card">
            <div class="node-title">MARS</div>
            <div class="node-badge" style="color: var(--accent-orange);">NASA // 56 MISSIONS</div>
          </div>
        </div>

        <!-- 3. Venus Destination Node -->
        <div class="destination-node" style="top: 48%; left: 82%;" data-dest-id="4" data-dest-name="Venus" data-missions="45" data-distance="41M KM (AVG)" data-orbit-id="orbit-venus-path">
          <div class="node-sphere" style="background: radial-gradient(circle at 35% 35%, #facc15, #713f12);"></div>
          <div class="node-card">
            <div class="node-title">VENUS</div>
            <div class="node-badge">// 45 MISSIONS</div>
          </div>
        </div>

        <!-- 4. Deep Space / ISRO Node -->
        <div class="destination-node" style="top: 76%; left: 60%;" data-dest-id="7" data-dest-name="Deep Space / Interstellar" data-missions="21" data-distance="24B+ KM" data-orbit-id="orbit-deepspace-path">
          <div class="node-sphere" style="background: radial-gradient(circle at 35% 35%, #38bdf8, #030712); border-color: var(--accent-cyan);"></div>
          <div class="node-card">
            <div class="node-title">ISRO & DEEP SPACE</div>
            <div class="node-badge" style="color: var(--accent-cyan);">// 21 MISSIONS</div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- ==============================================================
       SECTION 03: GLOBAL MISSION MAP
       ============================================================== -->
  <section class="section-mission-map" id="global-mission-map">
    <div class="map-control-bar">
      <div>
        <h2 class="section-title">GLOBAL MISSION MAP</h2>
        <div class="mono-label" style="margin-top: 4px;">GLOBAL SPACEPORTS & LAUNCH TRAJECTORIES</div>
      </div>

      <!-- Filters: +ACTIVE, +COMPLETED, +LOST, +HISTORIC -->
      <div class="filter-group">
        <button class="filter-btn active" data-status="all">+ ALL TRAJECTORIES</button>
        <button class="filter-btn" data-status="active">+ ACTIVE</button>
        <button class="filter-btn" data-status="completed">+ COMPLETED</button>
        <button class="filter-btn" data-status="historic">+ HISTORIC</button>
      </div>

      <!-- Legend -->
      <div class="legend-group">
        <span class="mono-label">TRAJECTORIES:</span>
        <div class="legend-item">
          <span class="legend-dot crewed"></span>
          <span>Crewed</span>
        </div>
        <div class="legend-item">
          <span class="legend-dot robotic"></span>
          <span>Robotic probe</span>
        </div>
        <div class="legend-item">
          <span class="legend-dot satellite"></span>
          <span>Satellite</span>
        </div>
      </div>
    </div>

    <!-- Map Canvas Viewport with Bathymetric Graticule -->
    <div class="map-canvas-viewport" id="global-mission-map-viewport">
      <svg class="map-svg-layer" viewBox="0 0 1100 620" preserveAspectRatio="none">
        <!-- Technical Latitude / Longitude Graticule Grid -->
        <g stroke="rgba(255,255,255,0.06)" stroke-width="1" stroke-dasharray="4 4">
          <line x1="0" y1="155" x2="1100" y2="155"/>
          <line x1="0" y1="310" x2="1100" y2="310"/>
          <line x1="0" y1="465" x2="1100" y2="465"/>
          <line x1="183" y1="0" x2="183" y2="620"/>
          <line x1="366" y1="0" x2="366" y2="620"/>
          <line x1="550" y1="0" x2="550" y2="620"/>
          <line x1="733" y1="0" x2="733" y2="620"/>
          <line x1="916" y1="0" x2="916" y2="620"/>
        </g>

        <!-- World Continent Polygons (Dark Aerospace Styling) -->
        <g fill="#0e1726" stroke="#1f2d3d" stroke-width="1">
          <!-- North America -->
          <polygon points="120,80 280,85 320,160 260,250 210,310 180,270 140,210 100,140"/>
          <!-- South America -->
          <polygon points="260,330 350,350 370,420 320,530 270,540 240,430 250,360"/>
          <!-- Europe -->
          <polygon points="500,100 620,110 630,190 560,220 490,190 480,140"/>
          <!-- Africa -->
          <polygon points="490,230 630,220 660,310 620,440 540,490 480,380 470,290"/>
          <!-- Asia -->
          <polygon points="630,90 920,80 960,180 890,290 770,330 650,230 630,140"/>
          <!-- Australia -->
          <polygon points="860,430 960,420 980,490 920,540 850,510 840,460"/>
          <!-- Antarctica -->
          <polygon points="40,580 1060,580 1060,615 40,615" fill="#1e293b"/>
        </g>

        <!-- Spaceport Pin Coordinates (SVG Group) -->
        <g id="spaceport-markers-group">
          <!-- 1. Cape Canaveral / KSC (USA) -->
          <g class="spaceport-node spaceport-marker" data-name="Kennedy Space Center / Cape Canaveral" data-code="KSC/CCSFS" data-lat="28.57° N" data-lng="-80.65° W" data-launches="900+" data-top="Apollo 11, Apollo 15, Voyager, Curiosity, Perseverance">
            <circle cx="240" cy="260" r="5" fill="var(--accent-cyan)" class="beacon-pulse"/>
            <text x="248" y="256" fill="#fff" font-family="var(--font-mono)" font-size="10" font-weight="600">CAPE CANAVERAL</text>
          </g>

          <!-- 2. Baikonur Cosmodrome (Kazakhstan) -->
          <g class="spaceport-node spaceport-marker" data-name="Baikonur Cosmodrome" data-code="SITE 1/5" data-lat="45.96° N" data-lng="63.30° E" data-launches="1,500+" data-top="Sputnik 1, Vostok 1, ISS Zarya Module">
            <circle cx="650" cy="200" r="5" fill="var(--accent-cyan)" class="beacon-pulse"/>
            <text x="658" y="196" fill="#fff" font-family="var(--font-mono)" font-size="10" font-weight="600">BAIKONUR</text>
          </g>

          <!-- 3. Guiana Space Centre / Kourou (ESA) -->
          <g class="spaceport-node spaceport-marker" data-name="Guiana Space Centre" data-code="CSG KOUROU" data-lat="5.24° N" data-lng="-52.77° W" data-launches="320+" data-top="James Webb Space Telescope, Rosetta, Gaia">
            <circle cx="330" cy="360" r="5" fill="var(--accent-orange)" class="beacon-pulse"/>
            <text x="338" y="356" fill="#fff" font-family="var(--font-mono)" font-size="10" font-weight="600">KOUROU (ESA)</text>
          </g>

          <!-- 4. Satish Dhawan Space Centre (ISRO, India) -->
          <g class="spaceport-node spaceport-marker" data-name="Satish Dhawan Space Centre (Sriharikota)" data-code="SDSC SHAR" data-lat="13.72° N" data-lng="80.23° E" data-launches="90+" data-top="Chandrayaan-1, Chandrayaan-3, Mangalyaan, Aditya-L1">
            <circle cx="720" cy="330" r="5" fill="var(--accent-cyan)" class="beacon-pulse"/>
            <text x="728" y="326" fill="#fff" font-family="var(--font-mono)" font-size="10" font-weight="600">SRIHARIKOTA (ISRO)</text>
          </g>

          <!-- 5. Jiuquan Satellite Launch Center (CNSA, China) -->
          <g class="spaceport-node spaceport-marker" data-name="Jiuquan / Wenchang Launch Center" data-code="JSLC/WSLC" data-lat="40.96° N" data-lng="100.30° E" data-launches="240+" data-top="Shenzhou, Tiangong Station, Zhurong Mars Rover">
            <circle cx="830" cy="210" r="4.5" fill="var(--accent-orange)" class="beacon-pulse"/>
            <text x="838" y="206" fill="#fff" font-family="var(--font-mono)" font-size="10" font-weight="600">JIUQUAN (CNSA)</text>
          </g>

          <!-- 6. Tanegashima Space Center (JAXA, Japan) -->
          <g class="spaceport-node spaceport-marker" data-name="Tanegashima Space Center" data-code="TNSC" data-lat="30.40° N" data-lng="130.97° E" data-launches="85+" data-top="Hayabusa2, SLIM Lunar Lander, Kounotori">
            <circle cx="890" cy="250" r="4.5" fill="var(--accent-orange)" class="beacon-pulse"/>
            <text x="898" y="246" fill="#fff" font-family="var(--font-mono)" font-size="10" font-weight="600">TANEGASHIMA (JAXA)</text>
          </g>

          <!-- 7. Vandenberg Space Force Base (USA) -->
          <g class="spaceport-node spaceport-marker" data-name="Vandenberg Space Force Base" data-code="VSFB" data-lat="34.74° N" data-lng="-120.57° W" data-launches="700+" data-top="InSight Mars Lander, Polar Satellites">
            <circle cx="170" cy="240" r="4" fill="#94a3b8"/>
            <text x="90" y="236" fill="#94a3b8" font-family="var(--font-mono)" font-size="9">VANDENBERG</text>
          </g>

          <!-- 8. Rocket Lab Launch Complex 1 (Mahia, NZ) -->
          <g class="spaceport-node spaceport-marker" data-name="Rocket Lab Launch Complex 1" data-code="LC-1 MAHIA" data-lat="-39.26° S" data-lng="177.86° E" data-launches="50+" data-top="CAPSTONE Lunar CubeSat, Commercial SmallSats">
            <circle cx="980" cy="520" r="4" fill="#94a3b8"/>
            <text x="910" y="535" fill="#94a3b8" font-family="var(--font-mono)" font-size="9">MAHIA (ROCKET LAB)</text>
          </g>
        </g>

        <!-- Trajectory Arcs Layer -->
        <g id="map-trajectories-layer"></g>
      </svg>

      <!-- Interactive Spaceport Drawer Popup -->
      <div class="map-mission-drawer" id="map-mission-drawer">
        <!-- Injected via JavaScript upon spaceport click -->
      </div>
    </div>
  </section>


  <!-- ==============================================================
       SECTION 04: SPACE AGENCY NETWORKS
       ============================================================== -->
  <section class="section-agency-network" id="agency-networks">
    <div class="section-top-bar">
      <div>
        <h2 class="section-title">SPACE AGENCY NETWORKS</h2>
        <div class="mono-label" style="margin-top: 4px;">GLOBAL CONSTELLATION & INTERNATIONAL COOPERATION</div>
      </div>
      <div class="mono-label" id="agency-hover-details" style="opacity: 0.8; transition: all 0.3s; text-align: right;">
        HOVER OVER ANY NODE TO INSPECT MISSIONS & COOPERATION LINKS
      </div>
    </div>

    <div class="radar-viewport" id="agency-radar-viewport">
      <svg class="radar-svg" viewBox="0 0 1000 650" fill="none">
        <!-- Radar concentric circular range rings -->
        <circle cx="500" cy="325" r="90" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
        <circle cx="500" cy="325" r="180" stroke="rgba(255,255,255,0.06)" stroke-width="1" stroke-dasharray="3 4"/>
        <circle cx="500" cy="325" r="260" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>

        <!-- Radar crosshairs -->
        <line x1="500" y1="50" x2="500" y2="600" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
        <line x1="200" y1="325" x2="800" y2="325" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>

        <!-- Rotating Radar Sweep Line -->
        <line class="radar-sweep-line" x1="500" y1="325" x2="500" y2="70" stroke="url(#radarGradient)" stroke-width="1.5"/>

        <defs>
          <linearGradient id="radarGradient" x1="0" y1="1" x2="0" y2="0">
            <stop offset="0%" stop-color="rgba(110, 168, 255, 0)"/>
            <stop offset="100%" stop-color="rgba(56, 189, 248, 0.7)"/>
          </linearGradient>
        </defs>

        <!-- Dynamic Collaborative Connection Lines Layer -->
        <g id="agency-network-lines"></g>
      </svg>

      <!-- Center Title Core -->
      <div class="radar-center-core">
        <h3 class="radar-center-title">HUMANITY'S SPACE EXPLORATION</h3>
        <span class="mono-label" style="color: var(--accent-blue); display: block; margin-top: 6px;">COOPERATIVE NETWORK</span>
      </div>

      <!-- Agency Nodes Array -->
      <!-- 1. ISRO (North) -->
      <div class="agency-radar-node" id="agency-isro" style="top: 14%; left: 50%;" data-agency-id="2" data-name="Indian Space Research Organisation" data-country="India" data-total="4" data-active="3">
        <div class="agency-node-anchor"></div>
        <div class="agency-node-name">ISRO</div>
      </div>

      <!-- 2. ESA (North-East) -->
      <div class="agency-radar-node" id="agency-esa" style="top: 26%; left: 78%;" data-agency-id="3" data-name="European Space Agency" data-country="Europe (22 Member States)" data-total="4" data-active="2">
        <div class="agency-node-anchor"></div>
        <div class="agency-node-name">ESA</div>
      </div>

      <!-- 3. PRIVATE (South-East) -->
      <div class="agency-radar-node" id="agency-private" style="top: 74%; left: 78%;" data-agency-id="7" data-name="Commercial Aerospace Sector" data-country="SpaceX / Rocket Lab / Intuitive Machines" data-total="3" data-active="3">
        <div class="agency-node-anchor"></div>
        <div class="agency-node-name">PRIVATE</div>
      </div>

      <!-- 4. CNSA (South) -->
      <div class="agency-radar-node" id="agency-cnsa" style="top: 86%; left: 50%;" data-agency-id="5" data-name="China National Space Administration" data-country="China" data-total="2" data-active="1">
        <div class="agency-node-anchor"></div>
        <div class="agency-node-name">CNSA</div>
      </div>

      <!-- 5. JAXA (South-West) -->
      <div class="agency-radar-node" id="agency-jaxa" style="top: 74%; left: 22%;" data-agency-id="4" data-name="Japan Aerospace Exploration Agency" data-country="Japan" data-total="2" data-active="1">
        <div class="agency-node-anchor"></div>
        <div class="agency-node-name">JAXA</div>
      </div>

      <!-- 6. NASA (North-West, Active Highlighted) -->
      <div class="agency-radar-node active" id="agency-nasa" style="top: 26%; left: 22%;" data-agency-id="1" data-name="National Aeronautics and Space Administration" data-country="United States" data-total="14" data-active="6">
        <div class="agency-node-anchor"></div>
        <div class="agency-node-name" style="color: var(--accent-cyan);">NASA</div>
        
        <!-- Target Mission Branches -->
        <div class="agency-child-branches">
          <span class="agency-branch-tag">[ MOON ]</span>
          <span class="agency-branch-tag">[ DEEP SPACE ]</span>
          <span class="agency-branch-tag">[ MARS ]</span>
        </div>
      </div>
    </div>
  </section>


  <!-- ==============================================================
       SECTION 05: THE MACHINES WE LEFT BEHIND
       ============================================================== -->
  <section class="section-relics-showcase" id="relics">
    <div class="relics-stage-layout">
      <!-- Left Editorial Story & Relic Spec Card -->
      <div class="relic-editorial-left">
        <h2 class="relic-main-headline">
          THE MACHINES<br>
          WE LEFT<br>
          BEHIND.
        </h2>

        <p class="relic-poetic-quote" id="relic-quote-text">
          Some completed their mission.<br>
          Some stopped transmitting.<br>
          Some are still where humanity left them.
        </p>

        <!-- Relic HUD Metadata Card -->
        <div class="relic-hud-card">
          <div class="relic-meta-row">
            <span class="relic-meta-label">MISSION</span>
            <span class="relic-meta-val" id="relic-val-mission">
              <?= htmlspecialchars($apollo15['mission_name'] ?? 'APOLLO 15') ?> // LRV
            </span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">DESTINATION</span>
            <span class="relic-meta-val" id="relic-val-destination">
              <?= htmlspecialchars($apollo15['current_location'] ?? 'HADLEY RILLE, LUNAR SURFACE') ?>
            </span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">YEAR DEPLOYED</span>
            <span class="relic-meta-val" id="relic-val-year">
              <?= htmlspecialchars($apollo15['launch_year'] ?? '1971') ?>
            </span>
          </div>

          <div class="relic-meta-row">
            <span class="relic-meta-label">CURRENT STATUS</span>
            <span class="relic-meta-val" id="relic-val-status" style="color: var(--accent-orange);">
              <?= strtoupper(htmlspecialchars($apollo15['current_status'] ?? 'DECOMMISSIONED')) ?>
            </span>
          </div>

          <a href="equipment.php?id=<?= $apollo15['id'] ?? 2 ?>" class="relic-cta-link" id="relic-cta-button">
            EXPLORE THE RELIC <span>→</span>
          </a>
        </div>
      </div>

      <!-- Right Cinematic Visual & Relic Switcher -->
      <div class="relic-visual-right">
        <div class="relic-hero-image-wrap">
          <img id="relic-display-image" class="relic-hero-image" 
               src="<?= htmlspecialchars($apollo15['hero_image'] ?? 'https://images-assets.nasa.gov/image/as15-88-11866/as15-88-11866~orig.jpg') ?>" 
               alt="Lunar Roving Vehicle on Lunar Surface">
          <div class="relic-vignette"></div>
        </div>

        <!-- Relic Switcher Buttons -->
        <div class="relic-switcher-bar">
          <button class="relic-tab-btn active" data-relic-id="2">Apollo 15 LRV</button>
          <button class="relic-tab-btn" data-relic-id="7">Opportunity Rover</button>
          <button class="relic-tab-btn" data-relic-id="6">Spirit Rover</button>
          <button class="relic-tab-btn" data-relic-id="29">Surveyor 3</button>
          <button class="relic-tab-btn" data-relic-id="3">Voyager 1</button>
          <button class="relic-tab-btn" data-relic-id="18">Philae Lander</button>
        </div>
      </div>
    </div>
  </section>


  <!-- ==============================================================
       SECTION 06: HUMANITY'S TIMELINE
       ============================================================== -->
  <section class="section-humanity-timeline" id="timeline">
    <div class="section-top-bar">
      <div>
        <h2 class="section-title">HUMANITY'S TIMELINE</h2>
        <div class="mono-label" style="margin-top: 4px;">PIONEERING ERAS FROM 1957 TO THE FUTURE</div>
      </div>
      <div class="mono-label">SELECT ANY ERA TO EXPAND</div>
    </div>

    <div class="timeline-interactive-stage">
      <!-- Active Milestone Spotlight Card -->
      <div class="timeline-spotlight-box">
        <div class="capsule-portal-circle">
          <img id="timeline-spotlight-image" class="capsule-portal-img" 
               src="https://images-assets.nasa.gov/image/as11-40-5927/as11-40-5927~orig.jpg" 
               alt="Spacecraft Module">
        </div>
        <div class="capsule-info-block">
          <span class="capsule-label" id="timeline-spotlight-label">CAPSULE // NASA</span>
          <h3 class="capsule-headline" id="timeline-spotlight-title">Apollo 11 — First Human Footsteps</h3>
          <p class="capsule-desc" id="timeline-spotlight-desc">
            Neil Armstrong and Buzz Aldrin land Lunar Module Eagle on the Sea of Tranquility, taking humanity's giant leap.
          </p>
          <a href="mission.php?id=1" id="timeline-spotlight-link" class="relic-cta-link" style="margin-top: 6px;">
            READ MISSION LOG →
          </a>
        </div>
      </div>

      <!-- Horizontal Year Scrubber (1957 - NOW) -->
      <div class="timeline-scrubber-track">
        <div class="timeline-year-node" data-year="1957">
          <span class="year-node-pip"></span>
          <span class="timeline-year-text">1957</span>
        </div>
        <div class="timeline-year-node active" data-year="1969">
          <span class="year-node-pip"></span>
          <span class="timeline-year-text">1969</span>
        </div>
        <div class="timeline-year-node" data-year="1977">
          <span class="year-node-pip"></span>
          <span class="timeline-year-text">1977</span>
        </div>
        <div class="timeline-year-node" data-year="1997">
          <span class="year-node-pip"></span>
          <span class="timeline-year-text">1997</span>
        </div>
        <div class="timeline-year-node" data-year="2012">
          <span class="year-node-pip"></span>
          <span class="timeline-year-text">2012</span>
        </div>
        <div class="timeline-year-node" data-year="2021">
          <span class="year-node-pip"></span>
          <span class="timeline-year-text">2021</span>
        </div>
        <div class="timeline-year-node" data-year="2023">
          <span class="year-node-pip"></span>
          <span class="timeline-year-text">NOW</span>
        </div>
      </div>
    </div>

    <!-- Timeline Footer Emblem & Telemetry Notation -->
    <div class="timeline-bottom-emblem-wrap">
      <svg class="timeline-star-emblem" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2L14.8 9.2L22 12L14.8 14.8L12 22L9.2 14.8L2 12L9.2 9.2Z"/>
      </svg>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
