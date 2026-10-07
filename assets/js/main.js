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
  const applyStatsData = (data) => {
    const misEl = document.getElementById('stat-missions-count');
    const objEl = document.getElementById('stat-objects-archived');
    const relEl = document.getElementById('stat-relics-count');
    const agyEl = document.getElementById('stat-agencies-count');

    if (misEl && data.missions_count) animateValue(misEl, 1, data.missions_count, 1000);
    if (objEl && data.equipment_count) animateValue(objEl, 1, data.equipment_count, 1000);
    if (relEl && data.relics_count) animateValue(relEl, 1, data.relics_count, 1000);
    if (agyEl && data.agencies_count) animateValue(agyEl, 1, data.agencies_count, 1000);
  };

  try {
    const res = await fetch('api/stats.php');
    if (!res.ok) throw new Error('API unavailable');
    const json = await res.json();
    if (json.success && json.data) {
      applyStatsData(json.data);
      return;
    }
  } catch (e) {
    try {
      const fbRes = await fetch('assets/data/stats.json');
      const fbJson = await fbRes.json();
      if (fbJson.success && fbJson.data) {
        applyStatsData(fbJson.data);
        return;
      }
    } catch (fbErr) {
      console.log('Using default telemetry numbers');
    }
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
        if (!res.ok) throw new Error('API unavailable');
        const json = await res.json();
        renderSearchResults(json.results, resultsContainer, query);
      } catch (err) {
        try {
          const results = await searchStaticCatalog(query);
          renderSearchResults(results, resultsContainer, query);
        } catch (staticErr) {
          console.error('Search error', staticErr);
          if (resultsContainer) {
            resultsContainer.innerHTML = '<div style="color: var(--accent-rose); padding: 1rem 0;">Archive telemetry query failed. Please retry.</div>';
          }
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
      let json = null;
      try {
        const res = await fetch(`api/ai-archive.php?q=${encodeURIComponent(q)}`);
        if (res.ok) {
          json = await res.json();
        }
      } catch (fetchErr) {
        // API offline / static hosting
      }

      if (!json || !json.success) {
        json = await synthesizeStaticAIAnswer(q);
      }

      if (json && json.answer) {
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
        const fallbackMsg = (json && json.error) ? json.error : 'The archive does not currently record verified details on this topic.';
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

/**
 * Static JSON Fallbacks for GitHub Pages and Offline Telemetry
 */
let _cachedCatalog = null;
async function getArchiveCatalog() {
  if (_cachedCatalog) return _cachedCatalog;
  try {
    const res = await fetch('assets/data/archive_catalog.json');
    _cachedCatalog = await res.json();
    return _cachedCatalog;
  } catch (err) {
    console.warn('Failed to load archive catalog fallback', err);
    return { missions: [], equipment: [], relics: [], instruments: [], agencies: [], destinations: [] };
  }
}

async function searchStaticCatalog(query) {
  const cat = await getArchiveCatalog();
  const q = query.toLowerCase().trim();
  const filterByQ = (list, fields) => {
    if (!list) return [];
    return list.filter(item => fields.some(f => item[f] && String(item[f]).toLowerCase().includes(q)));
  };

  return {
    missions: filterByQ(cat.missions, ['name', 'official_name', 'objective', 'description']).slice(0, 8),
    relics: filterByQ(cat.relics, ['name', 'type', 'current_location', 'mission_name']).slice(0, 6),
    equipment: filterByQ(cat.equipment, ['name', 'type', 'mission_name', 'current_status']).slice(0, 6),
    instruments: filterByQ(cat.instruments, ['name', 'purpose', 'equipment_name']).slice(0, 4),
    agencies: filterByQ(cat.agencies, ['name', 'short_name', 'country']).slice(0, 4),
    destinations: filterByQ(cat.destinations, ['name', 'type']).slice(0, 4)
  };
}

async function synthesizeStaticAIAnswer(query) {
  const cat = await getArchiveCatalog();
  const q = query.toLowerCase().trim();

  // 1. Opportunity
  if (q.includes('opportunity')) {
    return {
      success: true,
      answer: "Mars Exploration Rover Opportunity (MER-B) landed on Mars in Meridiani Planum in January 2004. Key verified discoveries:\n\n1. Standing Liquid Water: Discovered hematite spherules ('blueberries') and jarosite at Eagle Crater and Endurance Crater, providing definitive proof of an ancient acidic aqueous environment.\n2. Long-Distance Exploration: Completed the first off-world marathon (42.195 km) in March 2015, ultimately driving 45.16 km across the Martian surface.\n3. Clay Minerals: Explored Endeavour Crater, uncovering neutral-pH smectite clay minerals indicative of water benign to potential ancient microbial life.\n4. Historic Longevity: Operated for 14 years and 138 days (5,111 sols), exceeding its planned 90-sol warranty by over 55 times before its final transmission during a planetary dust storm in Perseverance Valley on June 10, 2018.",
      source_links: [
        { name: "NASA JPL Mars Exploration Rovers Dossier", url: "https://science.nasa.gov/mission/mars-exploration-rovers-spirit-and-opportunity/" },
        { name: "NASA Planetary Data System (PDS)", url: "https://pds-geosciences.wustl.edu/missions/mer/" }
      ]
    };
  }

  // 2. Chandrayaan-3
  if (q.includes('chandrayaan')) {
    return {
      success: true,
      answer: "Chandrayaan-3 is an ISRO lunar exploration mission launched on July 14, 2023, which executed a historic soft landing near the Lunar South Pole (69.373° S, 32.319° E) on August 23, 2023. Key verified discoveries:\n\n1. In-Situ Sulfur Detection: Pragyan rover's Laser-Induced Breakdown Spectroscope (LIBS) unambiguously detected sulfur (S) on the lunar surface, along with Al, Ca, Fe, Cr, Ti, Mn, Si, and O.\n2. Surface Thermophysical Profiling: Vikram lander's ChaSTE experiment recorded an 80°C temperature differential between the lunar topsoil (+60°C) and just 8 cm below the surface (-10°C), revealing exceptional lunar thermal insulation.\n3. Lunar Seismology: The ILSA payload recorded an authentic lunar seismic event (moonquake) on August 31, 2023.\n4. Plasma Measurements: The RAMBHA-LP Langmuir probe measured sparse lunar plasma density (5 to 30 million electrons per cubic meter) above the south polar region.",
      source_links: [
        { name: "ISRO Official Chandrayaan-3 Mission Dossier", url: "https://www.isro.gov.in/Chandrayaan3.html" }
      ]
    };
  }

  // 3. Missions that landed on Mars
  if (q.includes('mars') && (q.includes('land') || q.includes('mission') || q.includes('touchdown') || q.includes('which'))) {
    return {
      success: true,
      answer: "Humanity has successfully landed multiple robotic missions on the surface of Mars:\n\n• Viking 1 & Viking 2 (NASA, 1976) — First successful American Mars landers, Chryse Planitia & Utopia Planitia.\n• Mars Pathfinder / Sojourner (NASA, 1997) — First robotic mobile rover, Ares Vallis.\n• Spirit (MER-A) (NASA, 2004) — Gusev Crater explorer, Husband Hill summit.\n• Opportunity (MER-B) (NASA, 2004) — Meridiani Planum, 45.16 km marathon traverse.\n• Phoenix Lander (NASA, 2008) — Arctic plains, confirmed water ice beneath soil.\n• Curiosity (MSL) (NASA, 2012–Present) — Gale Crater, confirmed ancient habitable lake.\n• InSight Lander (NASA, 2018–2022) — Elysium Planitia, recorded over 1,300 marsquakes.\n• Perseverance & Ingenuity (NASA, 2021–Present) — Jezero Crater ancient river delta, first powered atmospheric flight.\n• Tianwen-1 / Zhurong Rover (CNSA, 2021) — Utopia Planitia landing and exploration.",
      source_links: [
        { name: "NASA Mars Exploration Program", url: "https://science.nasa.gov/mars/" }
      ]
    };
  }

  // 4. Moon equipment / left behind on the moon
  if (q.includes('moon') && (q.includes('equipment') || q.includes('left') || q.includes('relic') || q.includes('behind'))) {
    return {
      success: true,
      answer: "Documented historical equipment and relics remaining on the Moon include:\n\n• Apollo Lunar Module Descent Stages (Apollo 11, 12, 14, 15, 16, 17) — Base structures including Eagle at Tranquility Base.\n• Apollo Lunar Roving Vehicles (LRV-001, LRV-002, LRV-003) — Parked at Hadley-Apennine, Descartes Highlands, and Taurus-Littrow.\n• Early Soviet Probes & Rovers — Luna 9, Luna 16, Lunokhod 1 (Mare Imbrium), Lunokhod 2 (Le Monnier crater).\n• Laser Retroreflector Arrays (LRRR) — Passive quartz prisms still targeted by Earth observatories to measure Moon-Earth distance to millimeter precision.\n• Modern Landers & Rovers — Chang'e 3 (Yutu), Chang'e 4 (Yutu-2, lunar far side), Chandrayaan-3 (Vikram & Pragyan at Shiv Shakti Point), SLIM (JAXA).",
      source_links: [
        { name: "NASA Apollo Lunar Surface Journal", url: "https://www.nasa.gov/history/alsj/" }
      ]
    };
  }

  // 5. Deep space / interstellar
  if (q.includes('deep space') || q.includes('interstellar') || q.includes('farthest')) {
    return {
      success: true,
      answer: "Five human-made spacecraft are on trajectories carrying them out of the Solar System into interstellar space:\n\n1. Voyager 1 (NASA, launched 1977) — Farthest human-made object, crossed heliopause August 2012, currently ~163 AU (24.4 billion km) from Earth in interstellar space.\n2. Voyager 2 (NASA, launched 1977) — Crossed heliopause November 2018, exploring interstellar medium ~136 AU away.\n3. Pioneer 10 & Pioneer 11 (NASA, launched 1972/1973) — Inactive, coasting toward outer space beyond the Kuiper Belt.\n4. New Horizons (NASA, launched 2006) — Explored Pluto (2015) and Arrokoth (2019), continuing through the Kuiper Belt (~58 AU away).\n\nAdditionally, deep-space solar observatories operate at Sun-Earth Lagrange points (L1/L2), including SOHO, DSCOVR, JWST, and Aditya-L1.",
      source_links: [
        { name: "NASA Voyager Interstellar Mission", url: "https://science.nasa.gov/mission/voyager/" }
      ]
    };
  }

  // 6. Active missions
  if (q.includes('active') || q.includes('currently operating')) {
    const active = cat.missions ? cat.missions.filter(m => m.status === 'Active') : [];
    const list = active.map(m => `• ${m.name} (${m.agency_code}) — Destination: ${m.destination_name}`).join('\n');
    return {
      success: true,
      answer: `The archive records ${active.length} verified active missions currently conducting space operations:\n\n${list}`,
      source_links: [
        { name: "Orbital Archive Active Registry", url: "./#global-mission-map" }
      ]
    };
  }

  // 7. Left behind / relics
  if (q.includes('left behind') || q.includes('relics') || q.includes('abandoned') || q.includes('machines')) {
    const relics = cat.relics ? cat.relics.slice(0, 8) : [];
    const list = relics.map(r => `• ${r.name} (${r.type}) — Location: ${r.current_location || r.destination_name}`).join('\n');
    return {
      success: true,
      answer: `Humanity has left behind specialized hardware across celestial bodies for scientific exploration. Notable documented relics include:\n\n${list}\n\nThese machines remain silent monuments to human ingenuity across the solar system.`,
      source_links: [
        { name: "NASA Space Apps: Abandoned But Not Forgotten", url: "./#relics" }
      ]
    };
  }

  // Generic keyword search fallback
  const matchedMission = cat.missions?.find(m => q.includes(m.name.toLowerCase()));
  if (matchedMission) {
    return {
      success: true,
      answer: `${matchedMission.name} (${matchedMission.official_name || matchedMission.name}) was launched on ${matchedMission.launch_date} by ${matchedMission.agency_name || matchedMission.agency_code}. Destination: ${matchedMission.destination_name}. Status: ${matchedMission.status}.\n\nObjective: ${matchedMission.objective || matchedMission.description}`,
      source_links: [
        { name: `${matchedMission.agency_code} Science Records`, url: "./#global-mission-map" }
      ]
    };
  }

  return {
    success: true,
    answer: `Verified Archival Record Query: "${query}". You can query the archive about missions like Apollo 11, Opportunity, Voyager 1, Chandrayaan-3, Curiosity, or explore humanity's relics on the Moon and Mars.`,
    source_links: [
      { name: "Orbital Archive Exploration Portal", url: "./#where-have-we-been" }
    ]
  };
}

