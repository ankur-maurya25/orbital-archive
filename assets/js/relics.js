/**
 * ORBITAL ARCHIVE - Section 05: The Machines We Left Behind
 * Cinematic Relic Switcher connected to MySQL Equipment API
 */

class RelicsExhibition {
  constructor() {
    this.container = document.getElementById('section-relics');
    this.buttons = document.querySelectorAll('.relic-tab-btn');
    this.imageEl = document.getElementById('relic-display-image');
    this.missionEl = document.getElementById('relic-val-mission');
    this.destEl = document.getElementById('relic-val-destination');
    this.yearEl = document.getElementById('relic-val-year');
    this.statusEl = document.getElementById('relic-val-status');
    this.quoteEl = document.getElementById('relic-quote-text');
    this.ctaLink = document.getElementById('relic-cta-button');

    this.relicsData = [];

    if (!this.container) return;
    this.init();
  }

  async init() {
    this.buttons.forEach(btn => {
      btn.addEventListener('click', (e) => {
        this.buttons.forEach(b => b.classList.remove('active'));
        e.currentTarget.classList.add('active');
        const relicId = e.currentTarget.getAttribute('data-relic-id');
        this.switchRelic(relicId);
      });
    });

    try {
      const res = await fetch('api/relics.php');
      const json = await res.json();
      if (json.success && json.data.length > 0) {
        this.relicsData = json.data;
      }
    } catch (err) {
      console.warn('Using local relic telemetry state.', err);
    }
  }

  switchRelic(relicId) {
    const relic = this.relicsData.find(r => String(r.id) === String(relicId));
    if (!relic) return;

    // Fade transition
    if (this.imageEl) {
      this.imageEl.style.opacity = '0.3';
      setTimeout(() => {
        this.imageEl.src = relic.hero_image;
        this.imageEl.style.opacity = '1';
      }, 200);
    }

    if (this.missionEl) this.missionEl.textContent = `${relic.mission_name} // ${relic.name}`;
    if (this.destEl) this.destEl.textContent = relic.current_location || relic.destination_name;
    if (this.yearEl) this.yearEl.textContent = relic.launch_year || '1971';
    if (this.statusEl) this.statusEl.textContent = relic.current_status || 'DECOMMISSIONED';

    if (this.quoteEl && relic.description) {
      this.quoteEl.textContent = relic.description;
    }

    if (this.ctaLink) {
      const linkId = relic.slug || relic.id;
      this.ctaLink.href = `equipment.php?id=${linkId}`;
    }
  }
}

window.addEventListener('DOMContentLoaded', () => {
  new RelicsExhibition();
});
