# ORBITAL ARCHIVE
### *Everything humanity has sent beyond Earth.*

[![NASA Space Apps Challenge 2026](https://img.shields.io/badge/NASA%20Space%20Apps-2026%20Challenge%20Submission-0ea5e9?style=for-the-badge)](https://www.spaceappschallenge.org/)
[![Challenge: Abandoned But Not Forgotten](https://img.shields.io/badge/Challenge-Abandoned%20But%20Not%20Forgotten-d99a5b?style=for-the-badge)](https://www.spaceappschallenge.org/)
[![PHP 8.3](https://img.shields.io/badge/PHP-8.3%20%2F%20PDO-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL 8.0](https://img.shields.io/badge/MySQL-8.0%20%2F%20InnoDB-00758f?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Three.js](https://img.shields.io/badge/Three.js-WebGL%20Observatory-black?style=for-the-badge&logo=three.js&logoColor=white)](https://threejs.org/)

---

## 1. Project Overview

**ORBITAL ARCHIVE** is a cinematic, database-driven digital aerospace observatory and museum documenting every spacecraft, lander, rover, probe, and orbital observatory dispatched beyond Earth's atmosphere since 1957.

Built as an authentic research archive, it pairs high-fidelity visual storytelling with a normalized, research-backed relational database grounded strictly in official space agency primary documentation.

---

## 2. Problem Statement

Across seven decades of space exploration, humanity has deployed hundreds of scientific machines to the Moon, Mars, Venus, comets, asteroids, and the outer solar system. However, the records of these historic achievements are severely fragmented:
- Historical landing coordinates and surface disposition details are buried across disparate agency mission archives, academic papers, and broken legacy URLs.
- Modern digital tools often treat space hardware as generic dashboard entries, neglecting the emotional and historical weight of the machines humanity left behind.
- Public web resources frequently conflate active operational platforms with historical surface relics or publish fabricated "real-time" positions.

---

## 3. Solution & Mission

**ORBITAL ARCHIVE** solves this fragmentation by providing:
1. **A Single Unified Observatory**: A cinematic, uninterrupted flow connecting Earth's orbital sphere to distant planetary destinations.
2. **Normalized Relational Architecture**: Connecting missions, hardware, scientific instruments, timeline milestones, archival frames, and authoritative citations.
3. **Strict Scientific Integrity**: Rigorous distinction between active operational hardware, resting surface relics, and interplanetary probes, with transparent source provenance.

---

## 4. NASA Space Apps Challenge 2026 Connection

> **Challenge Category**: *Abandoned But Not Forgotten*

This platform directly answers the challenge prompt by bringing dignity, scientific rigor, and emotional storytelling to humanity's off-world relics:
- **18 Cataloged Off-World Relics**: Highlighting the Lunar Roving Vehicles parked at Hadley Rille, Opportunity's final resting place in Perseverance Valley, Spirit at Home Plate, Surveyor 3 at Ocean of Storms, Vikram and Pragyan at Shiv Shakti Point, Philae on Comet 67P, and the Voyagers traversing the interstellar boundary.
- **Clear Taxonomic Distinction**: Every record semantically separates **Mission Status** (e.g. `COMPLETED`), **Physical Fate** (e.g. in situ resting conditions under lunar/Martian vacuum), and **Location/Region** (verified coordinates and geographic landmarks).
- **Enduring Legacies**: Rather than describing these machines as "space junk" or forgotten debris, the archive honors them as permanent monuments to human curiosity.

---

## 5. Key Architectural Features

- **01 Cinematic Hero & Three.js Earth**: Photorealistic Earth sphere rendered with Three.js, procedural night lights, atmospheric rim glow, orbital shells (`ISS`, `HUBBLE`, `GEO`, `TIANGONG`), and graceful SVG fallback for low-power or WebGL-disabled devices.
- **02 "Where Have We Been?" Planetary Loops**: Interactive orbital transfer loops visualizing humanity's expansion to the Moon, Mars, Venus, Asteroids, and the Outer Solar System.
- **03 Global Mission Map**: Dark-mode cartographic visualization of the world's primary spaceports (Cape Canaveral, Baikonur, Kourou, Sriharikota, Tanegashima, Jiuquan, Vandenberg, Mahia) with trajectory filtering.
- **04 Space Agency Radar Constellation**: Interactive radar diagram demonstrating international cooperation across NASA, ISRO, ESA, JAXA, CNSA, and the commercial aerospace sector.
- **05 "The Machines We Left Behind" Relics Vault**: Emotional editorial showcase spotlighting surface relics with interactive switching and direct exhibition links.
- **06 Humanity's Flight Timeline**: Interactive year scrubber spanning 1957 to the present, spotlighting critical milestones with authentic primary sources.
- **Universal Equipment Exhibition Engine (`equipment.php`)**: Modular, database-driven deep-dive exhibition system powering all 30 cataloged spacecraft, landers, and rovers with dynamic 5-stage journeys, expandable payload matrices, documentary galleries, and provenance registries.
- **Global Archive Search (`Ctrl+K` / `Cmd+K` / `/`)**: Fast cross-table search with visual category badges (`[MISSION]`, `[EQUIPMENT]`, `[RELIC]`, `[INSTRUMENT]`, `[AGENCY]`, `[DESTINATION]`) and keyboard navigation.
- **"Ask the Archive" AI Assistant**: Grounded knowledge assistant synthesizing verified MySQL records directly without external API costs or hallucination risks.

---

## 6. Tech Stack & Engineering Rationale

| Layer | Technologies | Rationale |
|---|---|---|
| **Backend** | PHP 8.3, PDO | High-performance, lightweight server-side rendering with zero framework overhead; robust relational database interaction. |
| **Database** | MySQL 8.0, InnoDB | Strict relational schema, foreign key constraints, `utf8mb4` character set, indexed full-text and lookup queries. |
| **Frontend** | Vanilla JavaScript (ES6+) | Native browser performance without bundle bloat; modular component architecture (`earth.js`, `orbit.js`, `mission-map.js`, `agency-network.js`, `relics.js`, `timeline.js`, `main.js`). |
| **3D Graphics** | Three.js (r128) | Hardware-accelerated WebGL sphere with procedural shader lighting and orbital rings, backed by an SVG fallback canvas. |
| **Styling** | Modern CSS3, SVG | Obsidian/Navy/Cyan/Orange aerospace design system, responsive grid/flexbox layouts, WCAG 2.1 AA focus rings, and reduced-motion media queries. |
| **Architecture** | REST-style JSON APIs | 10 modular API endpoints powering decoupled frontend interactions and public programmatic access. |

---

## 7. Relational Data Architecture

```
                    ┌─────────────┐
                    │  agencies   │
                    └──────┬──────┘
                           │ 1:N
                    ┌──────▼──────┐          ┌────────────────┐
                    │  missions   ├──────────►  destinations  │
                    └──────┬──────┘ 1:N      └────────────────┘
                           │
             ┌─────────────┼─────────────┐
             │ 1:N         │ 1:N         │ 1:N
      ┌──────▼──────┐┌─────▼───────┐┌────▼────────┐
      │  equipment  ││   images    ││   sources   │
      └──────┬──────┘└─────────────┘└─────────────┘
             │ 1:N
      ┌──────▼────────────────┐
      │ equipment_instruments │ (Junction)
      └──────▲────────────────┘
             │ 1:N
      ┌──────┴──────┐
      │ instruments │
      └─────────────┘
```

### Table Schema Summary
- **`missions`**: Launch dates, vehicles, launch sites, coordinates, trajectory types, objectives, descriptions, agency and destination foreign keys.
- **`equipment`**: Slug, identity, specifications (mass, power, dimensions, mobility, arm, sample caching), operational status, physical fate, coordinates, last verified date, and relic classification.
- **`instruments`**: Scientific payloads, acronyms, sensor types, research purposes, specifications, and lead manufacturing institutions.
- **`equipment_instruments`**: Junction table mapping scientific payloads to hardware platforms.
- **`images`**: Authenticated imagery with URLs, captions, types (`HERO`, `SURFACE`, `TOUCHDOWN`, `FLIGHT`), credits, organizations, and licenses.
- **`timeline_events`**: Chronological mission milestones with importance ratings, dates, and equipment/mission associations.
- **`sources`**: Authoritative archival URLs, organizations, source types, and verification timestamps.

---

## 8. Research Methodology & Primary Archives

Every record in ORBITAL ARCHIVE is curated directly from primary aerospace repositories:
1. **NASA Jet Propulsion Laboratory (JPL)**: Mars 2020 Perseverance Mission Dashboard, Mars Exploration Rover (MER) Science Reports, InSight Mission Status, Voyager Interstellar Mission.
2. **NASA Science Mission Directorate**: New Horizons KEM2 Portal, Phoenix Mars Lander Archive, Apollo Lunar Surface Journal (ALSJ).
3. **European Space Agency (ESA)**: Rosetta & Philae Science Archive (PSA), Comet 67P Nucleus Morphology Reports.
4. **Indian Space Research Organisation (ISRO)**: Chandrayaan-3 Mission Dossier, Vikram Lander & Pragyan Rover In-Situ Data Releases.
5. **Japan Aerospace Exploration Agency (JAXA / ISAS)**: Hayabusa2 Asteroid Explorer Science Registry, SLIM Lunar Pinpoint Landing Telemetry.
6. **China National Space Administration (CNSA)**: Tianwen-1 / Zhurong Rover Science Bulletin.

---

## 9. Scientific Data Policy & Language Guardrails

To preserve academic and museum integrity, the archive strictly adheres to conservative scientific communication standards:
- **Cheyava Falls Astrobiology Investigation**:
  - Exact Terminology: *"Potential biosignature candidate"*
  - Mandatory Clarification: *"Biological origin remains unconfirmed. Further laboratory analysis of returned samples would be required to determine whether the observed features have a biological origin."*
  - Prohibited Sensationalism: Strictly bans terms like *"life discovered"*, *"evidence of life"*, or *"confirmed biosignature"*.
- **Telemetry Authenticity**:
  - Inactive Surface Relics: Uses *"HISTORICAL TOUCHDOWN / RESTING SITE"* and *"HISTORICAL MISSION RECORD"*.
  - Active Platforms: Uses *"CURRENT LOCATION / REGION"* and explicitly tags *"LAST VERIFIED: [date]"*. Strictly bans deceptive claims of "real-time telemetry".

---

## 10. Accessibility (WCAG 2.1 AA)

- **Universal Focus Rings**: High-contrast `:focus-visible` styling (`2px solid var(--accent-cyan)` with `3px` offset) on all interactive buttons, links, inputs, and tabs.
- **Reduced Motion Support**: Listens dynamically to `@media (prefers-reduced-motion: reduce)` in CSS and JavaScript:
  - Freezes Three.js Earth rotation and satellite orbital pulses.
  - Halts ambient radar sweeps and trajectory dash-flow animations.
  - Clamps CSS transition durations to `0.001ms`.
- **Keyboard Navigation**:
  - Full arrow-key navigation in global search (`↑` / `↓` with Enter to navigate).
  - Left / Right Arrow navigation across relic tabs and timeline year nodes.
  - Enter / Space toggle on all scientific instrument specification drawers.
  - Dialog semantics (`role="dialog"`, `aria-modal="true"`, `aria-labelledby`, Esc dismiss).
- **Non-blocking Canvas**: Three.js canvas explicitly uses `pointer-events: none` to prevent scroll interception or gesture hijacking.

---

## 11. Responsive Design System

The platform has been audited across 6 dedicated device breakpoints:
- **1440px+ (Ultra-wide)**: 1400px centered max-width constraints for balanced margins on 4K displays.
- **1200px (Desktop)**: Proportional multi-column layouts.
- **992px (Medium / Tablet Landscape)**: Stacks context and findings grids; smooth touch-scrolling on timeline scrubber.
- **768px (Portrait Tablet)**: Mobile navigation drawer toggle; hero image canvas height clamped to 360px.
- **480px (Smartphones)**: Fluid typography (`clamp()`), 44px minimum touch targets, column-stacked telemetry HUD.
- **360px (Compact Mobile - iPhone SE)**: Tightened container padding (`0.75rem`), zero horizontal overflow (`max-width: 100vw; overflow-x: hidden`).

---

## 12. AI Archive Strategy

The **"Ask the Archive"** AI Assistant does not rely on third-party black-box LLM APIs that introduce hallucination risks or recurring API costs. Instead:
- It uses a fact-grounded synthesis engine querying MySQL database records directly.
- All answers state: *"Answers are grounded in verified archive records."*
- Covers astrobiology queries (Cheyava Falls candidate findings), rover instruments, resting coordinates, and historical mission milestones with 100% factual provenance.

---

## 13. Image & Source Provenance Policy

- **Image Transparency**: Every documentary image card explicitly details: Title, Source Organization, Credit, and License. Where licensing documentation is unstated in official records, the system displays: *"Usage information not explicitly stated in archive record."* (never defaulting silently to public domain).
- **Source Transparency**: Near the base of every exhibition, an **ARCHIVE SOURCES** module renders authoritative agency links with organization, publication type, and access dates.

---

## 14. Security & Sanitization

- **PDO Prepared Statements**: 100% of SQL queries across all PHP scripts and REST APIs use parameterized prepared statements.
- **Error Message Sanitization**: All `PDOException` catch blocks log errors to the internal server error log and return user-safe, sanitized JSON messages without leaking internal database or SQL details.
- **Authentication**: Admin panel uses bcrypt `password_hash()` and `password_verify()` with session protection.
- **Dedicated 404 Experience**: Invalid equipment or mission identifiers return an authentic HTTP 404 status code and render `404.php` styled with the observatory aesthetic.

---

## 15. How to Run Locally

### Prerequisites
- PHP 8.1+ (PHP 8.3 recommended) with `pdo_mysql` and `curl` extensions enabled.
- MySQL 8.0+ (or MariaDB 10.4+).

### Step 1: Clone the Repository
```bash
git clone https://github.com/your-username/orbital-archive.git
cd orbital-archive
```

### Step 2: Initialize Database
Create database `orbital_archive` and import the schema and seed scripts:
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS orbital_archive CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p orbital_archive < database/schema.sql
mysql -u root -p orbital_archive < database/seed.sql
```

### Step 3: Run Database Migrations & QA Suite
```bash
php database/migrate_phase6_relic_safety.php
php database/generate_sitemap.php
php database/verify_phase6_integrity.php
php database/verify_phase6_qa.php
```

### Step 4: Start PHP Built-in Server
```bash
php -S localhost:8000
```
Open [http://localhost:8000](http://localhost:8000) in your web browser.

---

## 16. Deployment Architecture

> [!IMPORTANT]
> **ORBITAL ARCHIVE is a server-rendered PHP 8.3 + MySQL 8 application, NOT a static website.**
> - **Canonical Source**: The GitHub repository is the canonical source-code repository only.
> - **GitHub Pages Notice**: GitHub Pages is **NOT** supported for running the application because GitHub Pages only serves static files and cannot execute PHP or connect to MySQL. Do not use GitHub Pages as a production deployment target.
> - **Production Hosting**: Deploy the application to a PHP 8.3 + MySQL 8 compatible server environment (Apache/Nginx/Docker).
> - **Documentation**: See [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) for full server provisioning instructions and [`docs/PRODUCTION_CHECKLIST.md`](docs/PRODUCTION_CHECKLIST.md) for the pre-flight production checklist.

> [!WARNING]
> **IMPORTANT FOR AI & CODING AGENTS:**
> ORBITAL ARCHIVE is NOT a GitHub Pages/static website.
> Do not configure GitHub Pages as its production deployment target.
> Do not convert PHP files to static HTML.
> The application requires PHP 8.3 + MySQL 8 + PDO.

---

## 17. Project Structure

```
ORBITAL-ARCHIVE/
├── index.php                      # 6-Section Cinematic Homepage (Observatory)
├── equipment.php                  # Universal Exhibition Experience Engine
├── explore.php                    # Multi-filter Full Database Catalog
├── missions.php                   # Global Missions Registry (Grid View)
├── mission.php                    # Individual Mission Dossier Page
├── relics.php                     # Off-World Relics Vault ("Abandoned But Not Forgotten")
├── timeline.php                   # Humanity's Flight Chronicle (1957–Present)
├── destinations.php               # Planetary Destinations Directory
├── agencies.php                   # Space Agency Profiles & Collaborative Networks
├── 404.php                        # Dedicated Observatory 404 Error State
├── robots.txt                     # Crawler Guidelines & Sitemap Reference
├── sitemap.xml                    # Canonical XML Sitemap (82 URLs)
│
├── admin/                         # Mission Control (Protected Admin Panel)
│   ├── index.php                  # Admin Dashboard & Metrics
│   ├── login.php                  # Session Auth with password_verify()
│   ├── logout.php                 # Secure Session Destruction
│   ├── missions.php               # CRUD for Missions
│   ├── equipment.php              # CRUD for Equipment & Relics
│   ├── instruments.php            # CRUD for Scientific Payloads
│   └── security.php               # Credential & Key Rotation
│
├── api/                           # REST-style JSON API Endpoints
│   ├── stats.php                  # GET /api/stats.php (Telemetry counters)
│   ├── missions.php               # GET /api/missions.php (Missions filter)
│   ├── equipment.php              # GET /api/equipment.php (Hardware & Relics)
│   ├── instruments.php            # GET /api/instruments.php (Scientific Payloads)
│   ├── agencies.php               # GET /api/agencies.php (Agency Metrics)
│   ├── destinations.php           # GET /api/destinations.php (Planetary Bodies)
│   ├── relics.php                 # GET /api/relics.php (Off-World Relics)
│   ├── timeline.php               # GET /api/timeline.php (Chronological Milestones)
│   ├── search.php                 # GET /api/search.php?q=... (Cross-table search)
│   └── ai-archive.php             # GET /api/ai-archive.php?q=... (Grounded Knowledge Assistant)
│
├── config/
│   └── database.php               # PDO Connection with fallback and error logging
│
├── includes/
│   ├── header.php                 # Global Head, OpenGraph, Three.js CDN
│   ├── navbar.php                 # HUD Navigation Bar with Ctrl+K trigger
│   ├── footer.php                 # Footer, Search Modal, AI Modal
│   └── functions.php              # Utilities, telemetry formatting, CSRF
│
├── assets/
│   ├── css/
│   │   ├── main.css               # Aerospace Design System & :focus-visible rings
│   │   ├── animations.css         # Keyframes, Radar Sweep, Twinkle
│   │   └── responsive.css         # 6 Breakpoints, Touch Scrubber, Reduced Motion
│   └── js/
│       ├── earth.js               # Three.js 3D Earth & SVG Fallback Stage
│       ├── orbit.js               # Interactive Planetary Transfer Trajectories
│       ├── mission-map.js         # Spaceport Cartography & Trajectory Filters
│       ├── agency-network.js      # Agency Radar Constellation & Collaboration Arcs
│       ├── relics.js              # Relic Switcher & Keyboard Tab Controls
│       ├── timeline.js            # Year Scrubber, Arrow Navigation & Spotlights
│       ├── navigation.js          # ScrollSpy, Navbar Blur, Smooth Scroll
│       └── main.js                # Search Modal, Badge Rendering, AI Assistant
│
└── database/
    ├── schema.sql                 # Normalized MySQL Relational Schema
    ├── seed.sql                   # Initial Catalog Seed Data
    ├── generate_sitemap.php       # Dynamic Sitemap Generator
    ├── migrate_phase6_relic_safety.php # Safe Relic Taxonomy & Encoding Seeder
    ├── verify_phase6_integrity.php# Relational Integrity Audit Suite (18 Rules)
    └── verify_phase6_qa.php       # Master Automated QA Test Runner
```

---

## 18. Future Vision & Roadmap

- **Interactive 3D Hardware CAD Explorers**: Integrating lightweight GLTF/GLB models for rovers and probes to inspect instruments in real-time 3D.
- **Deep Space Network (DSN) Live Stream**: Connecting to NASA's public DSN XML feed when real-time antenna dishes at Goldstone, Madrid, and Canberra are tracking Voyager or Perseverance.
- **Community Field Notes**: Allowing verified aerospace researchers to submit peer-reviewed primary source amendments for historical missions.

---

*Orbital Archive — Built with pride for NASA Space Apps Challenge 2026.*
