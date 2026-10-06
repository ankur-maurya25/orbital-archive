/**
 * ORBITAL ARCHIVE - Main Controller
 * Handles global telemetry counts, search modal, shortcuts, and AI archive query
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Fetch dynamic telemetry stats
  fetchStats();

  // 2. Setup Search Modal
  setupSearch();

  // 3. Setup AI Archive Assistant
  setupAIArchive();
});

/**
 * Animate numbers counting up (honoring prefers-reduced-motion)
 */
function animateValue(obj, start, end, duration) {
  if (!obj) return;
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReduced) {
    obj.innerHTML = end.toLocaleString() + '+';
    return;
  }

  let startTimestamp = null;
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp;
    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
    obj.innerHTML = Math.floor(progress * (end - start) + start).toLocaleString() + (end >= 30 ? '+' : '');
    if (progress < 1) {
      window.requestAnimationFrame(step);
    }
  };
  window.requestAnimationFrame(step);
}

/**
 * Fetch and animate HUD stats from real MySQL database counts
 */
async function fetchStats() {
  try {
    const res = await fetch('api/stats.php');
    const json = await res.json();
    if (json.success && json.data) {
      const misEl = document.getElementById('stat-missions-count');
      const objEl = document.getElementById('stat-objects-archived');
      const relEl = document.getElementById('stat-relics-count');
      const agyEl = document.getElementById('stat-agencies-count');

      if (misEl) animateValue(misEl, 1, json.data.missions_count, 1000);
      if (objEl) animateValue(objEl, 1, json.data.equipment_count, 1000);
      if (relEl) animateValue(relEl, 1, json.data.relics_count, 1000);
      if (agyEl) animateValue(agyEl, 1, json.data.agencies_count, 1000);
    }
  } catch (e) {
    console.log('Using default telemetry numbers');
  }
}

/**
 * Setup Search Modal & Keyboard shortcut '/'
 */
function setupSearch() {
  const modal = document.getElementById('search-modal');
  const openBtns = document.querySelectorAll('.open-search-modal');
  const closeBtn = document.getElementById('close-search-modal');
  const input = document.getElementById('search-input');
  const resultsContainer = document.getElementById('search-results');

  if (!modal || !input) return;

  const openModal = () => {
    modal.classList.add('active');
    setTimeout(() => input.focus(), 100);
  };

  const closeModal = () => {
    modal.classList.remove('active');
    input.value = '';
    if (resultsContainer) resultsContainer.innerHTML = '';
  };

  openBtns.forEach(btn => btn.addEventListener('click', openModal));
  if (closeBtn) closeBtn.addEventListener('click', closeModal);

  // Close on outside click
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  // Keyboard shortcut '/'
  window.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
      e.preventDefault();
      openModal();
    }
    if (e.key === 'Escape' && modal.classList.contains('active')) {
      closeModal();
    }
  });

  // Debounced input search
  let debounceTimeout;
  input.addEventListener('input', () => {
    clearTimeout(debounceTimeout);
    const query = input.value.trim();
    if (query.length < 2) {
      if (resultsContainer) resultsContainer.innerHTML = '<div style="color: var(--text-muted); font-size: 0.85rem; padding: 1rem 0;">Type at least 2 characters to search missions, rovers, instruments, and probes...</div>';
      return;
    }

    debounceTimeout = setTimeout(async () => {
      try {
        const res = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
        const json = await res.json();
        renderSearchResults(json.results, resultsContainer);
      } catch (err) {
        console.error('Search error', err);
      }
    }, 250);
  });
}

function renderSearchResults(data, container) {
  if (!container) return;
  if (!data || (data.missions.length === 0 && data.equipment.length === 0 && (!data.instruments || data.instruments.length === 0) && data.agencies.length === 0)) {
    container.innerHTML = '<div style="color: var(--text-muted); padding: 1rem 0;">No matching telemetry records found.</div>';
    return;
  }

  let html = '';

  // 1. Missions
  if (data.missions && data.missions.length > 0) {
    html += '<div class="mono-label" style="color: var(--accent-cyan); margin: 0.8rem 0 0.4rem;">MISSIONS</div>';
    data.missions.forEach(m => {
      html += `
        <a href="mission.php?id=${m.id}" class="search-item-card">
          <div>
            <div style="font-weight: 600; color: #fff;">${m.name}</div>
            <div style="font-size: 0.75rem; color: var(--text-secondary);">${m.official_name || ''}</div>
          </div>
          <div style="text-align: right;">
            <span class="mono-label">${m.agency_code || ''}</span>
            <span class="mono-value" style="display: block; font-size: 0.72rem; color: var(--accent-orange);">${m.destination_name || ''}</span>
          </div>
        </a>
      `;
    });
  }

  // 2. Equipment & Spacecraft
  if (data.equipment && data.equipment.length > 0) {
    html += '<div class="mono-label" style="color: var(--accent-orange); margin: 0.8rem 0 0.4rem;">EQUIPMENT & SPACECRAFT</div>';
    data.equipment.forEach(e => {
      const linkId = e.slug ? e.slug : e.id;
      html += `
        <a href="equipment.php?id=${linkId}" class="search-item-card">
          <div>
            <div style="font-weight: 600; color: #fff;">${e.name} ${e.is_relic ? '<span style="color: var(--accent-cyan); font-size: 0.7rem;">[RELIC]</span>' : ''}</div>
            <div style="font-size: 0.75rem; color: var(--text-secondary);">${e.type} // ${e.mission_name}</div>
          </div>
          <div style="text-align: right;">
            <span class="mono-label">${e.current_status || ''}</span>
          </div>
        </a>
      `;
    });
  }

  // 3. Scientific Instruments
  if (data.instruments && data.instruments.length > 0) {
    html += '<div class="mono-label" style="color: var(--accent-cyan); margin: 0.8rem 0 0.4rem;">SCIENTIFIC INSTRUMENTS</div>';
    data.instruments.forEach(inst => {
      const parentSlug = inst.equipment_slug || 'perseverance';
      html += `
        <a href="equipment.php?id=${parentSlug}#instruments-section" class="search-item-card">
          <div>
            <div style="font-weight: 600; color: #fff;">${inst.name} <span class="mono-label" style="color: var(--accent-blue); font-size: 0.65rem;">[${inst.type}]</span></div>
            <div style="font-size: 0.75rem; color: var(--text-secondary);">${inst.purpose || ''}</div>
          </div>
          <div style="text-align: right;">
            <span class="mono-label" style="color: var(--accent-orange);">${inst.equipment_name || 'HARDWARE'}</span>
          </div>
        </a>
      `;
    });
  }

  // 4. Agencies
  if (data.agencies && data.agencies.length > 0) {
    html += '<div class="mono-label" style="color: var(--accent-blue); margin: 0.8rem 0 0.4rem;">AGENCIES</div>';
    data.agencies.forEach(a => {
      html += `
        <a href="agencies.php?id=${a.id}" class="search-item-card">
          <div>
            <div style="font-weight: 600; color: #fff;">${a.short_name} - ${a.name}</div>
            <div style="font-size: 0.75rem; color: var(--text-secondary);">${a.country}</div>
          </div>
        </a>
      `;
    });
  }

  container.innerHTML = html;
}

/**
 * Setup AI "Ask the Archive" Assistant
 */
function setupAIArchive() {
  const modal = document.getElementById('ai-modal');
  const openBtns = document.querySelectorAll('.open-ai-modal');
  const closeBtn = document.getElementById('close-ai-modal');
  const sendBtn = document.getElementById('ai-send-btn');
  const input = document.getElementById('ai-question-input');
  const chatLog = document.getElementById('ai-chat-log');

  if (!modal || !sendBtn) return;

  const openModal = () => modal.classList.add('active');
  const closeModal = () => modal.classList.remove('active');

  openBtns.forEach(btn => btn.addEventListener('click', openModal));
  if (closeBtn) closeBtn.addEventListener('click', closeModal);

  // Suggested questions
  document.querySelectorAll('.ai-suggested-chip').forEach(chip => {
    chip.addEventListener('click', () => {
      if (input) {
        input.value = chip.textContent.trim().replace(/^"|"$/g, '');
        sendQuestion();
      }
    });
  });

  const sendQuestion = async () => {
    const q = input.value.trim();
    if (!q) return;

    // Append user message
    const userMsg = document.createElement('div');
    userMsg.style.cssText = 'padding: 8px 12px; background: rgba(110, 168, 255, 0.15); border: 1px solid rgba(110, 168, 255, 0.3); border-radius: 4px; align-self: flex-end; margin-bottom: 8px; font-size: 0.85rem; max-width: 80%;';
    userMsg.textContent = q;
    chatLog.appendChild(userMsg);
    input.value = '';

    // Typing indicator
    const aiMsg = document.createElement('div');
    aiMsg.style.cssText = 'padding: 8px 12px; background: rgba(11, 17, 24, 0.9); border: 1px solid var(--border-color); border-radius: 4px; align-self: flex-start; margin-bottom: 8px; font-size: 0.85rem; max-width: 85%; color: var(--text-primary);';
    aiMsg.innerHTML = '<span class="mono-label" style="color: var(--accent-cyan)">CONSULTING ARCHIVE DATABASE...</span>';
    chatLog.appendChild(aiMsg);
    chatLog.scrollTop = chatLog.scrollHeight;

    try {
      const res = await fetch(`api/ai-archive.php?q=${encodeURIComponent(q)}`);
      const json = await res.json();
      if (json.success) {
        aiMsg.innerHTML = `
          <div class="mono-label" style="color: var(--accent-cyan); margin-bottom: 4px;">ARCHIVE SYNTHESIS</div>
          <div>${json.answer}</div>
          ${json.sources && json.sources.length ? `<div class="mono-label" style="margin-top: 8px; color: var(--text-muted);">VERIFIED SOURCES: ${json.sources.join(', ')}</div>` : ''}
        `;
      } else {
        aiMsg.textContent = 'Archive records on this topic are currently restricted or unavailable.';
      }
    } catch (e) {
      aiMsg.textContent = 'Archive system link error. Please try again.';
    }
    chatLog.scrollTop = chatLog.scrollHeight;
  };

  sendBtn.addEventListener('click', sendQuestion);
  input?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') sendQuestion();
  });
}
