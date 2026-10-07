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

  // 4. Setup Visual Theme Switcher (NIGHT / ARCHIVE)
  setupThemeSwitcher();
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
 * Setup Search Modal & Keyboard shortcuts (Ctrl+K, Cmd+K, '/')
 */
function setupSearch() {
  const modal = document.getElementById('search-modal');
  const openBtns = document.querySelectorAll('.open-search-modal');
  const closeBtn = document.getElementById('close-search-modal');
  const input = document.getElementById('search-input');
  const resultsContainer = document.getElementById('search-results');
  let selectedIndex = -1;

  if (!modal || !input) return;

  const openModal = () => {
    modal.classList.add('active');
    selectedIndex = -1;
    setTimeout(() => input.focus(), 100);
  };

  const closeModal = () => {
    modal.classList.remove('active');
    input.value = '';
    selectedIndex = -1;
    if (resultsContainer) resultsContainer.innerHTML = '';
  };

  openBtns.forEach(btn => btn.addEventListener('click', openModal));
  if (closeBtn) closeBtn.addEventListener('click', closeModal);

  // Close on outside click
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  // Keyboard shortcut Ctrl+K / Cmd+K and '/'
  window.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      openModal();
    } else if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
      e.preventDefault();
      openModal();
    }
    if (e.key === 'Escape') {
      if (modal.classList.contains('active')) closeModal();
      const aiModal = document.getElementById('ai-modal');
      if (aiModal && aiModal.classList.contains('active')) aiModal.classList.remove('active');
    }
  });

  // Arrow key navigation through search results
  input.addEventListener('keydown', (e) => {
    if (!resultsContainer) return;
    const cards = resultsContainer.querySelectorAll('.search-item-card');
    if (!cards || cards.length === 0) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      selectedIndex = (selectedIndex + 1) % cards.length;
      updateCardHighlight(cards, selectedIndex);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      selectedIndex = (selectedIndex - 1 + cards.length) % cards.length;
      updateCardHighlight(cards, selectedIndex);
    } else if (e.key === 'Enter' && selectedIndex >= 0 && cards[selectedIndex]) {
      e.preventDefault();
      window.location.href = cards[selectedIndex].href;
    }
  });

  function updateCardHighlight(cards, idx) {
    cards.forEach((c, i) => {
      if (i === idx) {
        c.classList.add('keyboard-selected');
        c.style.borderColor = 'var(--accent-cyan)';
        c.style.background = 'rgba(56, 189, 248, 0.15)';
        c.scrollIntoView({ block: 'nearest' });
      } else {
        c.classList.remove('keyboard-selected');
        c.style.borderColor = '';
        c.style.background = '';
      }
    });
  }

  // Debounced input search with loading state
  let debounceTimeout;
  input.addEventListener('input', () => {
    clearTimeout(debounceTimeout);
    selectedIndex = -1;
    const query = input.value.trim();
    if (query.length < 2) {
      if (resultsContainer) resultsContainer.innerHTML = '<div style="color: var(--text-muted); font-size: 0.85rem; padding: 1rem 0;">Type at least 2 characters to search missions, rovers, instruments, and probes...</div>';
      return;
    }

    if (resultsContainer) {
      resultsContainer.innerHTML = '<div style="color: var(--accent-cyan); padding: 1.2rem 0; font-family: var(--font-mono); font-size: 0.8rem;" class="mono-label">QUERYING ARCHIVE DATABASE...</div>';
    }

    debounceTimeout = setTimeout(async () => {
      try {
        const res = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
        const json = await res.json();
        renderSearchResults(json.results, resultsContainer, query);
      } catch (err) {
        console.error('Search error', err);
        if (resultsContainer) {
          resultsContainer.innerHTML = '<div style="color: var(--accent-rose); padding: 1rem 0;">Archive telemetry query failed. Please retry.</div>';
        }
      }
    }, 250);
  });
}

function renderSearchResults(data, container, query) {
  if (!container) return;
  if (!data || (data.missions.length === 0 && data.equipment.length === 0 && (!data.relics || data.relics.length === 0) && (!data.instruments || data.instruments.length === 0) && data.agencies.length === 0 && (!data.destinations || data.destinations.length === 0))) {
    container.innerHTML = `<div style="color: var(--text-muted); padding: 1.5rem 0; font-family: var(--font-mono); font-size: 0.85rem;">NO MATCHING ARCHIVAL RECORDS FOUND FOR "${escapeHtml(query || '')}"</div>`;
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
            <div style="font-weight: 600; color: var(--text-bright); display: flex; align-items: center; gap: 6px;">
              <span class="mono-label" style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); color: var(--accent-cyan); padding: 2px 6px; border-radius: 2px; font-size: 0.65rem;">[MISSION]</span>
              ${m.name}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">${m.official_name || ''}</div>
          </div>
          <div style="text-align: right;">
            <span class="mono-label">${m.agency_code || ''}</span>
            <span class="mono-value" style="display: block; font-size: 0.72rem; color: var(--accent-orange);">${m.destination_name || ''}</span>
          </div>
        </a>
      `;
    });
  }

  // 2. Relics
  if (data.relics && data.relics.length > 0) {
    html += '<div class="mono-label" style="color: var(--accent-orange); margin: 0.8rem 0 0.4rem;">OFF-WORLD RELICS (ABANDONED BUT NOT FORGOTTEN)</div>';
    data.relics.forEach(e => {
      const linkId = e.slug ? e.slug : e.id;
      html += `
        <a href="equipment.php?id=${linkId}" class="search-item-card" style="border-left: 2px solid var(--accent-orange);">
          <div>
            <div style="font-weight: 600; color: var(--text-bright); display: flex; align-items: center; gap: 6px;">
              <span class="mono-label" style="background: rgba(217, 154, 91, 0.15); border: 1px solid rgba(217, 154, 91, 0.3); color: var(--accent-orange); padding: 2px 6px; border-radius: 2px; font-size: 0.65rem;">[RELIC]</span>
              ${e.name}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">${e.type} // Mission: ${e.mission_name || 'Archived'}</div>
          </div>
          <div style="text-align: right;">
            <span class="mono-label" style="color: var(--accent-orange);">${e.destination_name || ''}</span>
            <span class="mono-value" style="display: block; font-size: 0.7rem; color: var(--text-muted);">${e.current_status || 'HISTORIC RELIC'}</span>
          </div>
        </a>
      `;
    });
  }

  // 3. Operational Equipment & Spacecraft
  if (data.equipment && data.equipment.length > 0) {
    html += '<div class="mono-label" style="color: var(--accent-emerald); margin: 0.8rem 0 0.4rem;">OPERATIONAL EQUIPMENT & SPACECRAFT</div>';
    data.equipment.forEach(e => {
      const linkId = e.slug ? e.slug : e.id;
      html += `
        <a href="equipment.php?id=${linkId}" class="search-item-card">
          <div>
            <div style="font-weight: 600; color: var(--text-bright); display: flex; align-items: center; gap: 6px;">
              <span class="mono-label" style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--accent-emerald); padding: 2px 6px; border-radius: 2px; font-size: 0.65rem;">[EQUIPMENT]</span>
              ${e.name}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">${e.type} // ${e.mission_name || ''}</div>
          </div>
          <div style="text-align: right;">
            <span class="mono-label" style="color: var(--accent-emerald);">${e.current_status || 'OPERATIONAL'}</span>
            <span class="mono-value" style="display: block; font-size: 0.7rem; color: var(--accent-orange);">${e.destination_name || ''}</span>
          </div>
        </a>
      `;
    });
  }

  // 4. Scientific Instruments
  if (data.instruments && data.instruments.length > 0) {
    html += '<div class="mono-label" style="color: var(--accent-blue); margin: 0.8rem 0 0.4rem;">SCIENTIFIC INSTRUMENTS</div>';
    data.instruments.forEach(inst => {
      const parentSlug = inst.equipment_slug || inst.equipment_id || 'perseverance';
      html += `
        <a href="equipment.php?id=${parentSlug}#instruments-section" class="search-item-card">
          <div>
            <div style="font-weight: 600; color: var(--text-bright); display: flex; align-items: center; gap: 6px;">
              <span class="mono-label" style="background: rgba(110, 168, 255, 0.15); border: 1px solid rgba(110, 168, 255, 0.3); color: var(--accent-blue); padding: 2px 6px; border-radius: 2px; font-size: 0.65rem;">[INSTRUMENT]</span>
              ${inst.name}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">${inst.purpose || ''}</div>
          </div>
          <div style="text-align: right;">
            <span class="mono-label" style="color: var(--accent-cyan);">${inst.equipment_name || 'HARDWARE'}</span>
            <span class="mono-value" style="display: block; font-size: 0.7rem; color: var(--text-muted);">${inst.destination_name || ''}</span>
          </div>
        </a>
      `;
    });
  }

  // 5. Agencies
  if (data.agencies && data.agencies.length > 0) {
    html += '<div class="mono-label" style="color: #c084fc; margin: 0.8rem 0 0.4rem;">AGENCIES</div>';
    data.agencies.forEach(a => {
      html += `
        <a href="agencies.php?id=${a.id}" class="search-item-card">
          <div>
            <div style="font-weight: 600; color: var(--text-bright); display: flex; align-items: center; gap: 6px;">
              <span class="mono-label" style="background: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.3); color: #c084fc; padding: 2px 6px; border-radius: 2px; font-size: 0.65rem;">[AGENCY]</span>
              ${a.short_name} - ${a.name}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">${a.country}</div>
          </div>
        </a>
      `;
    });
  }

  // 6. Destinations
  if (data.destinations && data.destinations.length > 0) {
    html += '<div class="mono-label" style="color: var(--accent-rose); margin: 0.8rem 0 0.4rem;">DESTINATIONS</div>';
    data.destinations.forEach(d => {
      html += `
        <a href="destinations.php?id=${d.id}" class="search-item-card">
          <div>
            <div style="font-weight: 600; color: var(--text-bright); display: flex; align-items: center; gap: 6px;">
              <span class="mono-label" style="background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.3); color: var(--accent-rose); padding: 2px 6px; border-radius: 2px; font-size: 0.65rem;">[DESTINATION]</span>
              ${d.name}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px;">${d.distance_from_earth || ''}</div>
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

  const openModal = () => {
    modal.classList.add('active');
    setTimeout(() => input?.focus(), 100);
  };
  const closeModal = () => modal.classList.remove('active');

  openBtns.forEach(btn => btn.addEventListener('click', openModal));
  if (closeBtn) closeBtn.addEventListener('click', closeModal);

  // Close when clicking outside modal window
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

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
    userMsg.className = 'ai-msg-bubble ai-msg-user';
    userMsg.textContent = q;
    chatLog.appendChild(userMsg);
    input.value = '';

    // Disable input while searching
    sendBtn.disabled = true;
    input.disabled = true;
    sendBtn.style.opacity = '0.6';

    // Loading indicator
    const aiMsg = document.createElement('div');
    aiMsg.className = 'ai-msg-bubble ai-msg-assistant';
    aiMsg.innerHTML = '<div class="ai-loading-pulse"><span class="ai-pulse-dot"></span> SEARCHING VERIFIED ARCHIVE RECORDS...</div>';
    chatLog.appendChild(aiMsg);
    chatLog.scrollTop = chatLog.scrollHeight;

    try {
      const res = await fetch(`api/ai-archive.php?q=${encodeURIComponent(q)}`);
      const json = await res.json();
      if (json.success && json.answer) {
        let sourcesHtml = '';
        if (json.source_links && json.source_links.length > 0) {
          const links = json.source_links.map(s => 
            `<a href="${escapeHtml(s.url)}" target="_blank" rel="noopener" class="ai-source-link">${escapeHtml(s.name)} ↗</a>`
          ).join(' <span style="opacity:0.35;">//</span> ');
          sourcesHtml = `<div class="ai-sources-tray"><span class="ai-sources-label">VERIFIED SOURCES:</span> ${links}</div>`;
        } else if (json.sources && json.sources.length > 0) {
          sourcesHtml = `<div class="ai-sources-tray"><span class="ai-sources-label">VERIFIED SOURCES:</span> ${escapeHtml(json.sources.join(' // '))}</div>`;
        }

        aiMsg.innerHTML = `
          <div class="ai-header-tag">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2L15 9L22 12L15 15L12 22L9 15L2 12L9 9Z"/>
            </svg>
            ARCHIVE SYNTHESIS
          </div>
          <div class="ai-answer-body">${escapeHtml(json.answer).replace(/\n/g, '<br>')}</div>
          ${sourcesHtml}
        `;
      } else {
        const fallbackMsg = json.error || 'The archive does not currently record verified details on this topic.';
        aiMsg.innerHTML = `
          <div class="ai-header-tag" style="color: var(--accent-orange);">
            ARCHIVE NOTICE
          </div>
          <div class="ai-answer-body">${escapeHtml(fallbackMsg)}</div>
        `;
      }
    } catch (e) {
      aiMsg.innerHTML = `
        <div class="ai-header-tag" style="color: var(--accent-rose);">
          ARCHIVE NOTICE
        </div>
        <div class="ai-answer-body">Archive knowledge query encountered a temporary system connection error. Please retry.</div>
      `;
    } finally {
      sendBtn.disabled = false;
      input.disabled = false;
      sendBtn.style.opacity = '1';
      chatLog.scrollTop = chatLog.scrollHeight;
      input.focus();
    }
  };

  sendBtn.addEventListener('click', sendQuestion);
  input?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      sendQuestion();
    }
  });
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

/**
 * Setup HUD Theme Switcher (NIGHT / ARCHIVE)
 */
function setupThemeSwitcher() {
  const currentTheme = document.documentElement.getAttribute('data-theme') || 'night';

  const syncButtons = (theme) => {
    document.querySelectorAll('.theme-btn').forEach(btn => {
      const isTarget = btn.getAttribute('data-theme-target') === theme;
      btn.setAttribute('aria-pressed', isTarget ? 'true' : 'false');
      if (isTarget) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });
  };

  syncButtons(currentTheme);

  document.querySelectorAll('.theme-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const targetTheme = btn.getAttribute('data-theme-target');
      if (!targetTheme) return;

      document.documentElement.setAttribute('data-theme', targetTheme);
      try {
        localStorage.setItem('orbital_theme', targetTheme);
      } catch (err) {}

      syncButtons(targetTheme);

      if (window.earthVisual && typeof window.earthVisual.setTheme === 'function') {
        window.earthVisual.setTheme(targetTheme);
      }

      if (window.globalMissionMap && typeof window.globalMissionMap.updateTheme === 'function') {
        window.globalMissionMap.updateTheme(targetTheme);
      }

      window.dispatchEvent(new CustomEvent('themechange', { detail: { theme: targetTheme } }));
    });
  });
}

