/**
 * ORBITAL ARCHIVE - Section 05: The Machines We Left Behind
 * Cinematic Relic Switcher connected to MySQL Equipment API
 */

class RelicsExhibition {
  constructor() {
    this.container = document.getElementById('relics') || document.getElementById('section-relics');
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
    this.buttons.forEach((btn, index) => {
      btn.setAttribute('role', 'tab');
      btn.setAttribute('aria-selected', btn.classList.contains('active') ? 'true' : 'false');
      btn.setAttribute('tabindex', btn.classList.contains('active') ? '0' : '-1');

      btn.addEventListener('click', (e) => {
        this.selectButton(e.currentTarget);
      });

      // Keyboard arrow navigation
      btn.addEventListener('keydown', (e) => {
        let newIndex = index;
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
          e.preventDefault();
          newIndex = (index + 1) % this.buttons.length;
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
          e.preventDefault();
          newIndex = (index - 1 + this.buttons.length) % this.buttons.length;
        }
        if (newIndex !== index) {
          const targetBtn = this.buttons[newIndex];
          targetBtn.focus();
          this.selectButton(targetBtn);
        }
      });
    });

    // Image fallback handling
    if (this.imageEl) {
      this.imageEl.addEventListener('error', () => {
        this.imageEl.src = 'https://images-assets.nasa.gov/image/as15-88-11866/as15-88-11866~orig.jpg';
      });
    }

    try {
      const res = await fetch('api/relics.php');
      if (!res.ok) throw new Error('API unavailable');
      const json = await res.json();
      if (json.success && json.data.length > 0) {
        this.relicsData = json.data;
      }
    } catch (err) {
      try {
        const fbRes = await fetch('assets/data/relics.json');
        const fbJson = await fbRes.json();
        if (fbJson.success && fbJson.data.length > 0) {
          this.relicsData = fbJson.data;
        }
      } catch (fbErr) {
        console.warn('Using local relic telemetry state.', fbErr);
      }
    }
  }

  selectButton(btn) {
    this.buttons.forEach(b => {
      b.classList.remove('active');
      b.setAttribute('aria-selected', 'false');
      b.setAttribute('tabindex', '-1');
    });
    btn.classList.add('active');
    btn.setAttribute('aria-selected', 'true');
    btn.setAttribute('tabindex', '0');
    const relicId = btn.getAttribute('data-relic-id');
    this.switchRelic(relicId);
  }

  async switchRelic(relicId) {
    let relic = this.relicsData.find(r => String(r.id) === String(relicId));
    if (!relic) {
      try {
        const res = await fetch(`api/relics.php?id=${relicId}`);
        const json = await res.json();
        if (json.success && json.data) {
          relic = json.data;
        }
      } catch (err) {
        console.warn('Failed to load relic fallback:', err);
      }
    }
    if (!relic) return;

    // Fade transition
    if (this.imageEl) {
      this.imageEl.style.opacity = '0.25';
      setTimeout(() => {
        if (relic.hero_image) {
          this.imageEl.src = relic.hero_image;
        }
        this.imageEl.style.opacity = '1';
      }, 150);
    }

    if (this.missionEl) this.missionEl.textContent = `${relic.mission_name} // ${relic.name}`;
    if (this.destEl) this.destEl.textContent = relic.current_location || relic.destination_name;
    if (this.yearEl) this.yearEl.textContent = relic.launch_year || '1971';
    
    if (this.statusEl) {
      const statusText = relic.current_status || 'COMPLETED';
      this.statusEl.textContent = statusText.toUpperCase();
      if (statusText === 'OPERATIONAL' || statusText === 'ACTIVE') {
        this.statusEl.style.color = 'var(--accent-emerald)';
      } else if (statusText === 'LOST' || statusText === 'DESTROYED') {
        this.statusEl.style.color = 'var(--accent-rose)';
      } else {
        this.statusEl.style.color = 'var(--accent-orange)';
      }
    }

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
