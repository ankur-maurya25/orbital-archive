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

    if (!this.container) return;
    this.init();
  }

  init() {
    this.nodes.forEach(node => {
      node.addEventListener('mouseenter', (e) => this.onNodeHover(e.currentTarget));
      node.addEventListener('mouseleave', () => this.onNodeLeave());
      node.addEventListener('click', (e) => {
        const destId = e.currentTarget.getAttribute('data-dest-id');
        const destName = e.currentTarget.getAttribute('data-dest-name');
        window.location.href = `destinations.php?id=${destId}`;
      });
    });
  }

  onNodeHover(node) {
    const destName = node.getAttribute('data-dest-name');
    const missionCount = node.getAttribute('data-missions');
    const distance = node.getAttribute('data-distance');
    const badge = node.getAttribute('data-badge') || '';

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
        <div style="font-size: 1.3rem; font-weight: 700; color: #fff; margin: 4px 0;">${destName}</div>
        <div class="mono-value" style="color: var(--accent-orange); margin-bottom: 6px;">${missionCount} RECORDED MISSIONS</div>
        <div style="font-size: 0.8rem; color: var(--text-secondary);">DISTANCE: <span style="color: #fff">${distance}</span></div>
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
