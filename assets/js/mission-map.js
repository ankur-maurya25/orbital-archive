/**
 * ORBITAL ARCHIVE - Section 03: Global Mission Map
 * Real Earth Geodesic Arcs, Spaceport Telemetry & Immediate Theme Adaptation
 */

class GlobalMissionMap {
  constructor() {
    this.mapContainer = document.getElementById('global-mission-map-viewport');
    this.filterButtons = document.querySelectorAll('.filter-btn');
    this.drawer = document.getElementById('map-mission-drawer');
    this.missions = [];
    this.currentFilter = 'all';

    if (!this.mapContainer) return;
    this.init();
  }

  async init() {
    // Set up filter buttons
    this.filterButtons.forEach(btn => {
      btn.addEventListener('click', (e) => {
        this.filterButtons.forEach(b => b.classList.remove('active'));
        e.currentTarget.classList.add('active');
        this.currentFilter = e.currentTarget.getAttribute('data-status') || 'all';
        this.applyFilter();
      });
    });

    // Fetch missions from PHP API for telemetry fallback
    await this.fetchMissions();

    // Render interactive spaceport markers and launch arcs
    this.renderLaunchArcs();
    this.attachSpaceportListeners();

    // Expose instance globally for immediate theme switcher notifications
    window.globalMissionMap = this;
  }

  async fetchMissions() {
    try {
      const res = await fetch('api/missions.php?limit=100');
      if (!res.ok) throw new Error('API unavailable');
      const json = await res.json();
      if (json.success) {
        this.missions = json.data;
        return;
      }
    } catch (err) {
      try {
        const fbRes = await fetch('assets/data/archive_catalog.json');
        const fbJson = await fbRes.json();
        if (fbJson && fbJson.missions) {
          this.missions = fbJson.missions;
          return;
        }
      } catch (fbErr) {
        console.warn('Could not load missions API directly, using embedded spaceport telemetry.', fbErr);
      }
    }
  }

  renderLaunchArcs() {
    const trajectoriesLayer = document.getElementById('map-trajectories-layer');
    const endpointsLayer = document.getElementById('map-endpoints-layer');
    const particlesLayer = document.getElementById('map-particles-layer');
    if (!trajectoriesLayer) return;

    trajectoriesLayer.innerHTML = '';
    if (endpointsLayer) endpointsLayer.innerHTML = '';
    if (particlesLayer) particlesLayer.innerHTML = '';

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Connect true spaceport coordinates to target orbital/transfer insertion points
    // Equirectangular viewBox 0 0 1100 620
    const launchArcs = [
      // 1. Cape Canaveral (304, 212) -> Low Earth Orbit / ISS (520, 160)
      { id: 'arc-cape-iss', from: [304, 212], to: [520, 160], curveY: -65, type: 'Crewed', status: 'active', label: 'ISS Expedition' },
      // 2. Cape Canaveral (304, 212) -> Mars Transfer / Deep Space (710, 105)
      { id: 'arc-cape-mars', from: [304, 212], to: [710, 105], curveY: -95, type: 'Robotic probe', status: 'active', label: 'Mars Insertion' },
      // 3. Vandenberg (182, 190) -> Sun-Synchronous Polar Orbit (215, 65)
      { id: 'arc-van-polar', from: [182, 190], to: [215, 65], curveY: -45, type: 'Satellite', status: 'active', label: 'Polar SSO' },
      // 4. Baikonur (743, 152) -> Soyuz ISS Docking (615, 115)
      { id: 'arc-baik-iss', from: [743, 152], to: [615, 115], curveY: -45, type: 'Crewed', status: 'active', label: 'Soyuz Docking' },
      // 5. Baikonur (743, 152) -> Sputnik Historic Orbit (380, 95)
      { id: 'arc-baik-sputnik', from: [743, 152], to: [380, 95], curveY: -70, type: 'Satellite', status: 'historic', label: 'Sputnik 1 Orbit' },
      // 6. Guiana / Kourou (389, 292) -> Sun-Earth L2 JWST Insertion (645, 410)
      { id: 'arc-kourou-jwst', from: [389, 292], to: [645, 410], curveY: -55, type: 'Robotic probe', status: 'active', label: 'Sun-Earth L2 (JWST)' },
      // 7. Sriharikota (795, 263) -> Lunar South Pole Transfer (950, 315)
      { id: 'arc-sri-ch3', from: [795, 263], to: [950, 315], curveY: -50, type: 'Robotic probe', status: 'completed', label: 'Chandrayaan-3 TLI' },
      // 8. Sriharikota (795, 263) -> Sun-Earth L1 Halo Orbit (675, 365)
      { id: 'arc-sri-aditya', from: [795, 263], to: [675, 365], curveY: -45, type: 'Robotic probe', status: 'active', label: 'Aditya-L1 Halo' },
      // 9. Jiuquan (857, 169) -> Tiangong Space Station (985, 125)
      { id: 'arc-jiu-tiangong', from: [857, 169], to: [985, 125], curveY: -55, type: 'Crewed', status: 'active', label: 'Tiangong Station' },
      // 10. Tanegashima (950, 205) -> Asteroid Ryugu / Moon SLIM (1045, 265)
      { id: 'arc-tane-ryugu', from: [950, 205], to: [1045, 265], curveY: -45, type: 'Robotic probe', status: 'completed', label: 'Hayabusa2 / SLIM' },
      // 11. Rocket Lab Mahia (1090, 445) -> Cislunar NRHO CAPSTONE (975, 520)
      { id: 'arc-mahia-capstone', from: [1090, 445], to: [975, 520], curveY: -50, type: 'Satellite', status: 'completed', label: 'CAPSTONE NRHO' }
    ];

    launchArcs.forEach((arc, i) => {
      const typeSlug = arc.type.toLowerCase().replace(/\s+/g, '-');
      const dx = arc.to[0] - arc.from[0];
      const dy = arc.to[1] - arc.from[1];
      const cx = arc.from[0] + dx * 0.5;
      const cy = arc.from[1] + dy * 0.5 + (arc.curveY || -50);

      const d = `M ${arc.from[0]} ${arc.from[1]} Q ${cx} ${cy} ${arc.to[0]} ${arc.to[1]}`;

      // 1. Trajectory Arc Path
      const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      path.setAttribute('id', arc.id);
      path.setAttribute('d', d);
      path.setAttribute('fill', 'none');
      path.setAttribute('class', `launch-trajectory-arc arc-${typeSlug} status-${arc.status}`);
      path.setAttribute('data-type', arc.type);
      path.setAttribute('data-status', arc.status);
      trajectoriesLayer.appendChild(path);

      // 2. Destination Endpoint Marker
      if (endpointsLayer) {
        const eg = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        eg.setAttribute('class', `trajectory-endpoint arc-${typeSlug} status-${arc.status}`);
        eg.setAttribute('data-status', arc.status);

        const ring = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        ring.setAttribute('cx', arc.to[0]);
        ring.setAttribute('cy', arc.to[1]);
        ring.setAttribute('r', '6');
        ring.setAttribute('class', 'endpoint-ring');

        const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        dot.setAttribute('cx', arc.to[0]);
        dot.setAttribute('cy', arc.to[1]);
        dot.setAttribute('r', '3');
        dot.setAttribute('class', 'endpoint-dot');

        const txt = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        // Align label cleanly
        const labelX = arc.to[0] > 950 ? arc.to[0] - 8 : arc.to[0] + 8;
        const textAnchor = arc.to[0] > 950 ? 'end' : 'start';
        txt.setAttribute('x', labelX);
        txt.setAttribute('y', arc.to[1] - 4);
        txt.setAttribute('text-anchor', textAnchor);
        txt.setAttribute('class', 'endpoint-label');
        txt.textContent = arc.label;

        eg.appendChild(ring);
        eg.appendChild(dot);
        eg.appendChild(txt);
        endpointsLayer.appendChild(eg);
      }

      // 3. Animated Travel Pulse (honoring prefers-reduced-motion)
      if (particlesLayer && !prefersReducedMotion) {
        const pulse = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        pulse.setAttribute('r', '2.5');
        pulse.setAttribute('class', `trajectory-particle arc-${typeSlug} status-${arc.status}`);
        pulse.setAttribute('data-status', arc.status);

        const anim = document.createElementNS('http://www.w3.org/2000/svg', 'animateMotion');
        anim.setAttribute('path', d);
        anim.setAttribute('dur', `${3.5 + (i % 3) * 0.8}s`);
        anim.setAttribute('repeatCount', 'indefinite');
        anim.setAttribute('rotate', 'auto');
        pulse.appendChild(anim);

        particlesLayer.appendChild(pulse);
      }
    });
  }

  attachSpaceportListeners() {
    const spaceports = document.querySelectorAll('.spaceport-node');
    spaceports.forEach(sp => {
      sp.setAttribute('tabindex', '0');
      sp.setAttribute('role', 'button');
      sp.setAttribute('aria-label', `Spaceport: ${sp.getAttribute('data-name') || 'Launch Site'}`);

      const handleTrigger = () => {
        const name = sp.getAttribute('data-name');
        const code = sp.getAttribute('data-code');
        const lat = sp.getAttribute('data-lat');
        const lng = sp.getAttribute('data-lng');
        const count = sp.getAttribute('data-launches') || '150+';
        const topMissions = sp.getAttribute('data-top') || 'Apollo, Artemis, ISS, Hubble';

        if (this.drawer) {
          this.drawer.innerHTML = `
            <div style="flex: 1; min-width: 260px;">
              <div class="mono-label" style="color: var(--accent-cyan); display: flex; align-items: center; gap: 6px;">
                <span class="beacon-pulse" style="display:inline-block; width:6px; height:6px; border-radius:50%; background:var(--accent-cyan);"></span>
                ACTIVE LAUNCH COMPLEX
              </div>
              <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-bright); margin-top: 2px;">${name} <span class="mono-label" style="color: var(--accent-blue);">[${code}]</span></div>
              <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 4px;">
                COORDINATES: <span class="mono-value" style="color: var(--text-bright); font-weight: 600;">${lat}, ${lng}</span> | HISTORIC LAUNCHES: <span class="mono-value" style="color: var(--text-bright); font-weight: 600;">${count}</span>
              </div>
            </div>
            <div style="text-align: right; min-width: 220px;">
              <div class="mono-label">PRIMARY MISSIONS</div>
              <div style="font-size: 0.85rem; color: var(--accent-orange); font-weight: 500; margin-top: 2px;">${topMissions}</div>
              <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; margin-top: 8px;">
                <a href="missions.php" class="cta-button" style="padding: 4px 10px; font-size: 0.72rem; min-height: 28px;">VIEW MISSIONS →</a>
                <button type="button" class="drawer-close-btn" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-secondary); padding: 4px 8px; border-radius: 3px; font-size: 0.72rem; cursor: pointer;" aria-label="Close details">CLOSE ✕</button>
              </div>
            </div>
          `;
          this.drawer.classList.add('active');

          const closeBtn = this.drawer.querySelector('.drawer-close-btn');
          if (closeBtn) {
            closeBtn.addEventListener('click', () => {
              this.drawer.classList.remove('active');
            });
          }
        }
      };

      sp.addEventListener('click', handleTrigger);
      sp.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          handleTrigger();
        }
      });
    });
  }

  applyFilter() {
    const filter = this.currentFilter;
    const elements = document.querySelectorAll(
      '.launch-trajectory-arc, .trajectory-endpoint, .trajectory-particle'
    );

    elements.forEach(el => {
      const status = el.getAttribute('data-status') || '';

      if (filter === 'all') {
        el.style.display = '';
        el.style.opacity = '';
      } else if (filter === 'active') {
        if (status === 'active') {
          el.style.display = '';
          el.style.opacity = '1';
        } else {
          el.style.display = '';
          el.style.opacity = '0.12';
        }
      } else if (filter === 'completed') {
        if (status === 'completed') {
          el.style.display = '';
          el.style.opacity = '1';
        } else {
          el.style.display = '';
          el.style.opacity = '0.12';
        }
      } else if (filter === 'historic') {
        if (status === 'historic' || status === 'completed') {
          el.style.display = '';
          el.style.opacity = '1';
        } else {
          el.style.display = '';
          el.style.opacity = '0.12';
        }
      }
    });
  }

  updateTheme(theme) {
    this.applyFilter();
  }
}

window.addEventListener('DOMContentLoaded', () => {
  new GlobalMissionMap();
});
