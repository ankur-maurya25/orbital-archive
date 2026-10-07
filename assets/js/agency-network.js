/**
 * ORBITAL ARCHIVE - Section 04: Space Agency Networks
 * Radar / Constellation Visualization with Collaborative Space Ties
 */

class AgencyNetwork {
  constructor() {
    this.viewport = document.getElementById('agency-radar-viewport');
    this.nodes = document.querySelectorAll('.agency-radar-node');
    this.svgLinesLayer = document.getElementById('agency-network-lines');
    this.agencyStatsDrawer = document.getElementById('agency-hover-details');
    this.agenciesData = [];

    if (!this.viewport) return;
    this.init();
  }

  async init() {
    this.drawConstellationConnections();
    await this.fetchAgenciesData();

    this.nodes.forEach(node => {
      node.setAttribute('tabindex', '0');
      node.setAttribute('role', 'button');
      node.setAttribute('aria-label', `Space agency: ${node.getAttribute('data-name') || 'Agency'}`);

      node.addEventListener('mouseenter', (e) => this.onAgencyHover(e.currentTarget));
      node.addEventListener('mouseleave', () => this.onAgencyLeave());
      node.addEventListener('focus', (e) => this.onAgencyHover(e.currentTarget));
      node.addEventListener('blur', () => this.onAgencyLeave());
      
      node.addEventListener('click', (e) => {
        const agencyId = e.currentTarget.getAttribute('data-agency-id');
        window.location.href = `agencies.php?id=${agencyId}`;
      });

      node.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const agencyId = e.currentTarget.getAttribute('data-agency-id');
          window.location.href = `agencies.php?id=${agencyId}`;
        }
      });
    });
  }

  async fetchAgenciesData() {
    try {
      const res = await fetch('api/agencies.php');
      if (!res.ok) throw new Error('API unavailable');
      const json = await res.json();
      if (json.success && json.data) {
        this.agenciesData = json.data;
      }
    } catch (err) {
      try {
        const fbRes = await fetch('assets/data/archive_catalog.json');
        const fbJson = await fbRes.json();
        if (fbJson && fbJson.agencies) {
          this.agenciesData = fbJson.agencies;
        }
      } catch (fbErr) {
        console.warn('Agency telemetry data fallback to node attributes.', fbErr);
      }
    }

    if (this.agenciesData.length > 0) {
      // Update nodes with authentic DB metrics
      this.nodes.forEach(node => {
        const aId = node.getAttribute('data-agency-id');
        const matched = this.agenciesData.find(a => String(a.id) === String(aId));
        if (matched) {
          node.setAttribute('data-name', matched.name);
          node.setAttribute('data-country', matched.country);
          node.setAttribute('data-total', matched.total_missions || matched.mission_count || 1);
          node.setAttribute('data-equipment', matched.total_equipment || 0);
          node.setAttribute('data-active', matched.active_missions || 0);
          node.setAttribute('data-top', matched.top_missions || '');
        }
      });
    }
  }

  drawConstellationConnections() {
    if (!this.svgLinesLayer) return;

    // Pairs of agencies that share historic and active collaborative missions
    const collaborations = [
      { from: 'agency-nasa', to: 'agency-isro', label: 'NISAR / Deep Space Network' },
      { from: 'agency-nasa', to: 'agency-esa', label: 'ISS / Hubble / Artemis / Cassini' },
      { from: 'agency-nasa', to: 'agency-roscosmos', label: 'Apollo-Soyuz / ISS Partnership' },
      { from: 'agency-roscosmos', to: 'agency-esa', label: 'ExoMars / Scientific Cooperation' },
      { from: 'agency-esa', to: 'agency-jaxa', label: 'BepiColombo' },
      { from: 'agency-nasa', to: 'agency-jaxa', label: 'Lunar Gateway / SLIM / Hayabusa' },
      { from: 'agency-nasa', to: 'agency-private', label: 'Commercial Crew / CLPS' },
      { from: 'agency-isro', to: 'agency-esa', label: 'Tracking Support / Chandrayaan' },
      { from: 'agency-cnsa', to: 'agency-esa', label: 'Double Star / Scientific Exchange' }
    ];

    collaborations.forEach(collab => {
      const elFrom = document.getElementById(collab.from);
      const elTo = document.getElementById(collab.to);
      if (!elFrom || !elTo) return;

      const rectView = this.viewport.getBoundingClientRect();
      const rectFrom = elFrom.getBoundingClientRect();
      const rectTo = elTo.getBoundingClientRect();

      const x1 = rectFrom.left - rectView.left + rectFrom.width / 2;
      const y1 = rectFrom.top - rectView.top + rectFrom.height / 2;
      const x2 = rectTo.left - rectView.left + rectTo.width / 2;
      const y2 = rectTo.top - rectView.top + rectTo.height / 2;

      const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
      line.setAttribute('x1', x1);
      line.setAttribute('y1', y1);
      line.setAttribute('x2', x2);
      line.setAttribute('y2', y2);
      line.setAttribute('stroke', 'rgba(110, 168, 255, 0.2)');
      line.setAttribute('stroke-width', '1');
      line.setAttribute('stroke-dasharray', '3 4');
      line.setAttribute('class', `collab-line ${collab.from} ${collab.to}`);

      this.svgLinesLayer.appendChild(line);
    });
  }

  onAgencyHover(node) {
    this.nodes.forEach(n => n.classList.remove('active'));
    node.classList.add('active');

    const nodeId = node.id;
    // Highlight connected lines
    const lines = document.querySelectorAll('.collab-line');
    lines.forEach(line => {
      if (line.classList.contains(nodeId)) {
        line.setAttribute('stroke', 'var(--accent-cyan)');
        line.setAttribute('stroke-width', '2');
        line.style.opacity = '1';
      } else {
        line.style.opacity = '0.15';
      }
    });

    const name = node.getAttribute('data-name');
    const country = node.getAttribute('data-country');
    const total = node.getAttribute('data-total') || '15';
    const equipment = node.getAttribute('data-equipment') || '0';
    const active = node.getAttribute('data-active') || '8';
    const top = node.getAttribute('data-top');

    if (this.agencyStatsDrawer) {
      this.agencyStatsDrawer.innerHTML = `
        <div class="mono-label" style="color: var(--accent-cyan)">AGENCY NETWORK PROFILE</div>
        <div style="font-size: 1.3rem; font-weight: 700; color: var(--text-bright);">${name}</div>
        <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px;">${country}</div>
        <div style="display: flex; gap: 1.5rem; margin-top: 8px; flex-wrap: wrap;">
          <div><span class="mono-label">MISSIONS:</span> <span class="mono-value" style="color: var(--text-bright)">${total}</span></div>
          <div><span class="mono-label">HARDWARE:</span> <span class="mono-value" style="color: var(--accent-cyan)">${equipment}</span></div>
          <div><span class="mono-label">ACTIVE:</span> <span class="mono-value" style="color: var(--accent-emerald)">${active}</span></div>
        </div>
        ${top ? `<div style="font-size: 0.75rem; color: var(--accent-orange); margin-top: 6px;" class="mono-value">KEY MISSIONS: ${top}</div>` : ''}
      `;
      this.agencyStatsDrawer.style.opacity = '1';
    }
  }

  onAgencyLeave() {
    const lines = document.querySelectorAll('.collab-line');
    lines.forEach(line => {
      line.setAttribute('stroke', 'rgba(110, 168, 255, 0.2)');
      line.setAttribute('stroke-width', '1');
      line.style.opacity = '1';
    });

    if (this.agencyStatsDrawer) {
      this.agencyStatsDrawer.style.opacity = '0.6';
    }
  }
}

window.addEventListener('DOMContentLoaded', () => {
  new AgencyNetwork();
});
