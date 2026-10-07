/**
 * ORBITAL ARCHIVE - Section 03: Global Mission Map
 * Real-time Launch Sites, Trajectories, and Dynamic Status Filtering
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

    // Fetch missions from PHP API
    await this.fetchMissions();

    // Render interactive spaceport markers and launch arcs
    this.renderLaunchArcs();
    this.attachSpaceportListeners();
  }

  async fetchMissions() {
    try {
      const res = await fetch('api/missions.php?limit=100');
      const json = await res.json();
      if (json.success) {
        this.missions = json.data;
      }
    } catch (err) {
      console.warn('Could not load missions API directly, using embedded spaceport telemetry.', err);
    }
  }

  renderLaunchArcs() {
    // Dynamic arcs are rendered in SVG overlay
    const svg = document.getElementById('map-trajectories-layer');
    if (!svg) return;

    // Connect spaceport origins to target orbital insertion coordinates
    const launchArcs = [
      { from: [240, 260], to: [480, 180], type: 'Crewed', color: 'var(--accent-cyan)' }, // Cape to LEO
      { from: [240, 260], to: [750, 150], type: 'Robotic probe', color: 'var(--accent-orange)' }, // Cape to Deep Space
      { from: [650, 200], to: [850, 250], type: 'Crewed', color: 'var(--accent-cyan)' }, // Baikonur
      { from: [650, 200], to: [300, 120], type: 'Satellite', color: '#94a3b8' },
      { from: [330, 360], to: [600, 480], type: 'Robotic probe', color: 'var(--accent-orange)' }, // Kourou / Webb
      { from: [720, 330], to: [890, 420], type: 'Robotic probe', color: 'var(--accent-orange)' }, // Sriharikota / Chandrayaan
      { from: [720, 330], to: [520, 250], type: 'Robotic probe', color: 'var(--accent-orange)' }, // Sriharikota / Aditya
      { from: [830, 210], to: [980, 280], type: 'Robotic probe', color: 'var(--accent-orange)' }, // Jiuquan
      { from: [890, 250], to: [950, 180], type: 'Robotic probe', color: 'var(--accent-orange)' }, // Tanegashima
      { from: [980, 520], to: [850, 600], type: 'Satellite', color: '#94a3b8' } // Rocket Lab Mahia
    ];

    launchArcs.forEach((arc, i) => {
      const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      const dx = arc.to[0] - arc.from[0];
      const dy = arc.to[1] - arc.from[1];
      const cx = arc.from[0] + dx * 0.5;
      const cy = arc.from[1] + dy * 0.5 - 50; // Curve arc upward

      const d = `M ${arc.from[0]} ${arc.from[1]} Q ${cx} ${cy} ${arc.to[0]} ${arc.to[1]}`;
      path.setAttribute('d', d);
      path.setAttribute('fill', 'none');
      path.setAttribute('stroke', arc.color);
      path.setAttribute('stroke-width', '1.5');
      path.setAttribute('class', `launch-trajectory-arc arc-${arc.type.toLowerCase().replace(' ', '-')}`);
      path.setAttribute('opacity', '0.65');

      svg.appendChild(path);
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
            <div>
              <div class="mono-label" style="color: var(--accent-cyan)">ACTIVE LAUNCH COMPLEX</div>
              <div style="font-size: 1.25rem; font-weight: 700; color: #fff;">${name} (${code})</div>
              <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 3px;">
                COORDINATES: <span class="mono-value">${lat}, ${lng}</span> | HISTORIC LAUNCHES: <span class="mono-value">${count}</span>
              </div>
            </div>
            <div style="text-align: right;">
              <div class="mono-label">PRIMARY MISSIONS</div>
              <div style="font-size: 0.85rem; color: var(--accent-orange); font-weight: 500;">${topMissions}</div>
              <a href="missions.php" style="display: inline-block; margin-top: 6px; font-family: var(--font-mono); font-size: 0.72rem; color: var(--accent-blue);">VIEW ALL LAUNCHES →</a>
            </div>
          `;
          this.drawer.classList.add('active');
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
    const arcs = document.querySelectorAll('.launch-trajectory-arc');
    arcs.forEach(arc => {
      if (this.currentFilter === 'all') {
        arc.style.display = 'block';
        arc.style.opacity = '0.65';
        arc.style.strokeWidth = '1.5';
      } else if (this.currentFilter === 'active') {
        // Highlight active orbital profiles
        if (arc.classList.contains('arc-robotic-probe') || arc.classList.contains('arc-crewed')) {
          arc.style.display = 'block';
          arc.style.opacity = '0.9';
          arc.style.strokeWidth = '2';
        } else {
          arc.style.display = 'block';
          arc.style.opacity = '0.2';
          arc.style.strokeWidth = '1';
        }
      } else if (this.currentFilter === 'completed' || this.currentFilter === 'historic') {
        // Highlight historic profiles
        if (arc.classList.contains('arc-satellite') || arc.classList.contains('arc-crewed')) {
          arc.style.display = 'block';
          arc.style.opacity = '0.9';
          arc.style.strokeWidth = '2';
        } else {
          arc.style.display = 'block';
          arc.style.opacity = '0.2';
          arc.style.strokeWidth = '1';
        }
      }
    });
  }
}

window.addEventListener('DOMContentLoaded', () => {
  new GlobalMissionMap();
});
