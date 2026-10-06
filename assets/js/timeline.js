/**
 * ORBITAL ARCHIVE - Section 06: Humanity's Timeline
 * Interactive Horizontal Year Scrubber with Dynamic Milestone Spotlight
 */

class HumanityTimeline {
  constructor() {
    this.container = document.getElementById('timeline') || document.getElementById('section-timeline');
    this.yearNodes = document.querySelectorAll('.timeline-year-node');
    this.capsuleImg = document.getElementById('timeline-spotlight-image');
    this.labelEl = document.getElementById('timeline-spotlight-label');
    this.titleEl = document.getElementById('timeline-spotlight-title');
    this.descEl = document.getElementById('timeline-spotlight-desc');
    this.linkEl = document.getElementById('timeline-spotlight-link');

    if (!this.container) return;
    this.init();
  }

  init() {
    this.yearNodes.forEach(node => {
      node.addEventListener('click', (e) => {
        this.yearNodes.forEach(n => n.classList.remove('active'));
        const target = e.currentTarget;
        target.classList.add('active');
        const year = target.getAttribute('data-year');
        this.loadYearMilestone(year);
      });
    });
  }

  async loadYearMilestone(year) {
    try {
      const res = await fetch(`api/timeline.php?year=${year}`);
      const json = await res.json();
      if (json.success && json.data) {
        this.renderMilestone(json.data, year);
      }
    } catch (err) {
      console.warn('Timeline API fallback triggered.', err);
    }
  }

  renderMilestone(item, year) {
    if (this.capsuleImg && item.image_url) {
      this.capsuleImg.style.opacity = '0.3';
      setTimeout(() => {
        this.capsuleImg.src = item.image_url;
        this.capsuleImg.style.opacity = '1';
      }, 150);
    }

    if (this.labelEl) {
      const eqType = item.equipment_type || 'SPACECRAFT';
      this.labelEl.textContent = `${eqType.toUpperCase()} // ${item.agency_code || 'ARCHIVE'}`;
    }

    if (this.titleEl) {
      this.titleEl.textContent = item.title || `${year} MILESTONE`;
    }

    if (this.descEl) {
      this.descEl.textContent = item.description || 'Milestone recorded in global exploration archive.';
    }

    if (this.linkEl) {
      if (item.equipment_slug || item.equipment_id) {
        this.linkEl.href = `equipment.php?id=${item.equipment_slug || item.equipment_id}`;
        this.linkEl.textContent = 'EXPLORE HARDWARE EXHIBIT →';
      } else if (item.mission_id) {
        this.linkEl.href = `mission.php?id=${item.mission_id}`;
        this.linkEl.textContent = 'READ MISSION LOG →';
      }
    }
  }
}

window.addEventListener('DOMContentLoaded', () => {
  new HumanityTimeline();
});
