/**
 * ORBITAL ARCHIVE - Section 02: "Where Have We Been?"
 * Interactive SVG Orbits and Planetary Destination Inspector
 */

class DestinationOrbits {
  constructor() {
    this.container = document.getElementById('orbital-destinations-stage');
    this.nodes = document.querySelectorAll('.destination-node');
    this.orbitPaths = document.querySelectorAll('.orbital-svg-path');
    this.infoPanel = document.getElementById('destination-hover-card');

    this.destData = {};
    if (!this.container) return;
    this.init();
  }

  async init() {
    try {
      const res = await fetch('api/destinations.php');
      const json = await res.json();
      if (json.success && json.data) {
        json.data.forEach(d => {
          this.destData[d.id] = d;
        });
      }
    } catch (e) {
      console.warn('Using embedded destination telemetry.', e);
    }

    this.nodes.forEach(node => {
      node.setAttribute('tabindex', '0');
      node.setAttribute('role', 'button');
      node.setAttribute('aria-label', `Explore destination: ${node.getAttribute('data-dest-name') || 'Destination'}`);

      node.addEventListener('mouseenter', (e) => this.onNodeHover(e.currentTarget));
      node.addEventListener('mouseleave', () => this.onNodeLeave());
      node.addEventListener('focus', (e) => this.onNodeHover(e.currentTarget));
      node.addEventListener('blur', () => this.onNodeLeave());

      node.addEventListener('click', (e) => {
        const destId = e.currentTarget.getAttribute('data-dest-id');
        window.location.href = `destinations.php?id=${destId}`;
      });

      node.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const destId = e.currentTarget.getAttribute('data-dest-id');
          window.location.href = `destinations.php?id=${destId}`;
        }
      });
    });
  }

  onNodeHover(node) {
    const destId = node.getAttribute('data-dest-id');
    const live = this.destData[destId] || {};
    const destName = live.name || node.getAttribute('data-dest-name');
    const missionCount = live.calculated_mission_count !== undefined ? live.calculated_mission_count : node.getAttribute('data-missions');
    const equipCount = live.calculated_equipment_count !== undefined ? live.calculated_equipment_count : 'Multiple';
    const flagships = live.flagship_examples ? live.flagship_examples.split(', ').slice(0, 3).join(', ') : '';
    const distance = live.distance_from_earth || node.getAttribute('data-distance') || 'Varies';

    // Highlight connecting SVG orbit path
    const targetOrbit = node.getAttribute('data-orbit-id');
    if (targetOrbit) {
      const path = document.getElementById(targetOrbit);
      if (path) {
        path.style.stroke = 'var(--accent-cyan)';
        path.style.strokeWidth = '2.5px';
        path.style.filter = 'drop-shadow(0 0 10px rgba(56, 189, 248, 0.8))';
      }
    }

    if (this.infoPanel) {
      this.infoPanel.innerHTML = `
        <div class="mono-label" style="color: var(--accent-blue)">DESTINATION TELEMETRY</div>
        <div style="font-size: 1.3rem; font-weight: 700; color: var(--text-bright); margin: 4px 0;">${destName}</div>
        <div class="mono-value" style="color: var(--accent-orange); margin-bottom: 4px;">${missionCount} MISSIONS // ${equipCount} HARDWARE UNITS</div>
        ${flagships ? `<div style="font-size: 0.78rem; color: var(--accent-cyan); margin-bottom: 4px;">KEY OBJECTS: ${flagships}</div>` : ''}
        <div style="font-size: 0.8rem; color: var(--text-secondary);">DISTANCE: <span style="color: var(--text-bright)">${distance}</span></div>
        <div class="mono-label" style="margin-top: 8px; color: var(--accent-cyan);">CLICK TO EXPLORE ARCHIVE →</div>
      `;
      this.infoPanel.style.opacity = '1';
    }
  }

  onNodeLeave() {
    this.orbitPaths.forEach(path => {
      path.style.stroke = '';
      path.style.strokeWidth = '';
      path.style.filter = '';
    });

    if (this.infoPanel) {
      this.infoPanel.style.opacity = '0.7';
    }
  }
}

window.addEventListener('DOMContentLoaded', () => {
  new DestinationOrbits();
});
