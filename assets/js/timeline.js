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
    this.dateEl = document.getElementById('timeline-spotlight-date');
    this.titleEl = document.getElementById('timeline-spotlight-title');
    this.descEl = document.getElementById('timeline-spotlight-desc');
    this.equipmentEl = document.getElementById('timeline-spotlight-equipment');
    this.missionEl = document.getElementById('timeline-spotlight-mission');
    this.sourceWrap = document.getElementById('timeline-spotlight-source-wrap');
    this.sourceLink = document.getElementById('timeline-spotlight-source-link');
    this.linkEl = document.getElementById('timeline-spotlight-link');

    if (!this.container) return;
    this.init();
  }

  init() {
    this.yearNodes.forEach((node, index) => {
      const year = node.getAttribute('data-year');
      node.setAttribute('tabindex', '0');
      node.setAttribute('role', 'button');
      node.setAttribute('aria-label', `Timeline milestone for year ${year}`);

      node.addEventListener('click', (e) => {
        this.selectNode(e.currentTarget);
      });

      // Keyboard arrow navigation
      node.addEventListener('keydown', (e) => {
        let newIndex = index;
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
          e.preventDefault();
          newIndex = (index + 1) % this.yearNodes.length;
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
          e.preventDefault();
          newIndex = (index - 1 + this.yearNodes.length) % this.yearNodes.length;
        } else if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this.selectNode(node);
          return;
        }

        if (newIndex !== index) {
          const targetNode = this.yearNodes[newIndex];
          targetNode.focus();
          this.selectNode(targetNode);
        }
      });
    });

    if (this.capsuleImg) {
      this.capsuleImg.addEventListener('error', () => {
        this.capsuleImg.src = 'https://images-assets.nasa.gov/image/as11-40-5927/as11-40-5927~orig.jpg';
      });
    }
  }

  selectNode(node) {
    this.yearNodes.forEach(n => n.classList.remove('active'));
    node.classList.add('active');
    const year = node.getAttribute('data-year');
    this.loadYearMilestone(year);
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
      this.capsuleImg.style.opacity = '0.25';
      setTimeout(() => {
        this.capsuleImg.src = item.image_url;
        this.capsuleImg.style.opacity = '1';
      }, 150);
    }

    if (this.labelEl) {
      const eqType = item.equipment_type || 'SPACECRAFT';
      this.labelEl.textContent = `${eqType.toUpperCase()} // ${item.agency_code || 'ARCHIVE'}`;
    }

    if (this.dateEl) {
      this.dateEl.textContent = item.event_date ? this.formatDate(item.event_date) : `${year} EPOCH`;
    }

    if (this.titleEl) {
      this.titleEl.textContent = item.title || `${year} MILESTONE`;
    }

    if (this.equipmentEl) {
      this.equipmentEl.textContent = item.equipment_name || item.equipment_type || 'Exploration Hardware';
    }

    if (this.missionEl) {
      this.missionEl.textContent = item.mission_name || 'Global Archive Program';
    }

    if (this.descEl) {
      this.descEl.textContent = item.description || 'Milestone recorded in global exploration archive.';
    }

    // Source link
    if (this.sourceWrap && this.sourceLink) {
      if (item.source_url) {
        this.sourceWrap.style.display = 'block';
        this.sourceLink.href = item.source_url;
        this.sourceLink.textContent = `${item.source_name || 'Verified Authoritative Record'} ↗`;
      } else {
        this.sourceWrap.style.display = 'block';
        this.sourceLink.href = 'https://www.nasa.gov/';
        this.sourceLink.textContent = 'Official Historical Archive ↗';
      }
    }

    if (this.linkEl) {
      if (item.equipment_slug || item.equipment_id) {
        this.linkEl.href = `equipment.php?id=${item.equipment_slug || item.equipment_id}`;
        this.linkEl.textContent = 'EXPLORE HARDWARE EXHIBIT →';
      } else if (item.mission_id) {
        this.linkEl.href = `mission.php?id=${item.mission_id}`;
        this.linkEl.textContent = 'READ MISSION LOG →';
      } else {
        this.linkEl.href = 'timeline.php';
        this.linkEl.textContent = 'VIEW COMPLETE TIMELINE →';
      }
    }
  }

  formatDate(dateStr) {
    try {
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return dateStr.toUpperCase();
      return d.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' }).toUpperCase();
    } catch (e) {
      return dateStr.toUpperCase();
    }
  }
}

window.addEventListener('DOMContentLoaded', () => {
  new HumanityTimeline();
});
