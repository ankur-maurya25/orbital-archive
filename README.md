# ORBITAL ARCHIVE — A Global Record of Human Space Exploration

> **A premium aerospace digital observatory and museum archiving everything humanity has sent beyond Earth.**
> Built as a production-grade full-stack web application with PHP 8.3+, MySQL 8.0+, Vanilla JavaScript (ES6+), Three.js, SVG, and CSS animations.

---

## Developer Profile & Technical Architecture

- **Developer**: B.Tech Information Technology Student & Full Stack Web Developer
- **Core Stack**: PHP 8+, MySQL 8+, Vanilla JavaScript, Three.js, CSS3, HTML5, Git
- **Design Philosophy**: Cinematic aerospace visual fidelity inspired by NASA public data, deep space telemetry, and planetary exploration records.

```
ORBITAL-ARCHIVE/
├── index.php                 # 6-Section Cinematic Homepage (Observatory)
├── explore.php               # Multi-filter Full Database Catalog
├── missions.php              # Global Missions Registry (Grid View)
├── mission.php               # Museum-style Mission Exhibit Page
├── agencies.php              # Space Agency Network & Collaboration Profiles
├── destinations.php          # Planetary Destinations Directory
├── relics.php                # Off-World Relics Vault ("The Machines We Left Behind")
├── timeline.php              # Humanity's Flight Chronicle (1957–Present)
│
├── admin/                    # Mission Control (Protected Admin Panel)
│   ├── index.php             # Overview Dashboard & Metrics
│   ├── login.php             # Session Auth with password_verify() & CSRF
│   ├── logout.php            # Secure Session Destruction
│   ├── missions.php          # Full CRUD for Missions
│   └── equipment.php         # Full CRUD for Equipment & Relics
│
├── api/                      # REST-style JSON API Endpoints
│   ├── missions.php          # GET /api/missions.php (filter by status, agency, destination)
│   ├── equipment.php         # GET /api/equipment.php (filter by relic, category, mission)
│   ├── agencies.php          # GET /api/agencies.php (dynamic mission counters)
│   ├── destinations.php      # GET /api/destinations.php (cataloged target bodies)
│   ├── relics.php            # GET /api/relics.php (off-world relics dataset)
│   ├── timeline.php          # GET /api/timeline.php (chronological milestones)
│   ├── search.php            # GET /api/search.php?q=... (cross-table search)
│   ├── stats.php             # GET /api/stats.php (live telemetry metrics)
│   └── ai-archive.php        # GET /api/ai-archive.php?q=... (Ask The Archive AI Assistant)
│
├── config/
│   └── database.php          # Smart PDO connection (supports MySQL 8.0 & WAMP/XAMPP)
│
├── includes/
│   ├── header.php            # HTML head, Fonts, Three.js CDN
│   ├── navbar.php            # HUD Header Pill Navigation & Triggers
│   ├── footer.php            # Telemetry footer, Search & AI modals
│   └── functions.php         # PDO helper, JSON formatter, CSRF & Auth checks
│
├── assets/
│   ├── css/
│   │   ├── main.css          # Aerospace Design System (Obsidian/Navy/Cyan/Orange)
│   │   ├── animations.css    # Radar sweep, orbital pulse, trajectory dashflow
│   │   └── responsive.css    # Mobile, Tablet, and Desktop Breakpoints
│   └── js/
│       ├── main.js           # Live counter animation, Search modal, AI Assistant
│       ├── navigation.js     # ScrollSpy, navbar blur, smooth scrolling
│       ├── earth.js          # Three.js 3D Earth, atmosphere glow & orbital rings
│       ├── orbit.js          # SVG transfer trajectories to Moon, Mars, Venus
│       ├── mission-map.js    # Global spaceport arcs, coordinates, filter pills
│       ├── agency-network.js # Radar constellation of NASA, ISRO, ESA, CNSA, JAXA
│       ├── relics.js         # Apollo 15 LRV switcher & off-world relic telemetry
│       └── timeline.js       # Horizontal year scrubber (1957–NOW) & capsule spotlight
│
└── database/
    ├── schema.sql            # Normalized MySQL schema with foreign keys
    └── seed.sql              # 30 landmark missions & 18 relics seeded with real data
```

---

## Database Design

Database: `orbital_archive`

| Table | Description |
|---|---|
| `agencies` | National and commercial aerospace organizations (NASA, ISRO, ESA, JAXA, CNSA, Roscosmos, SpaceX, etc.) |
| `destinations` | Celestial targets (Earth Orbit, Moon, Mars, Venus, Deep Space, Asteroids) |
| `missions` | Launch dossiers, vehicles, launch coordinates, status, trajectories, objectives |
| `equipment` | Rovers, landers, orbiters, telescopes, marked with `is_relic` and `relic_category` |
| `images` | Official archival imagery with credits, licenses, and types |
| `timeline_events`| Decisive historical milestones from 1957 to present |
| `sources` | Official links (NASA JPL, ISRO, ESA) and verification records |
| `users` | Admin credentials with `password_hash()` bcrypt security |

---

## Setup & Running Locally

### 1. Database Initialization
Run in your MySQL client or command line:
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```
*(The `config/database.php` automatically connects using your root password or empty string on XAMPP/WAMP).*

### 2. Launch Local Server
You can start the PHP built-in server directly:
```bash
php -S localhost:8000
```
Open your browser to: **`http://localhost:8000`**

### 3. Mission Control Admin Login
- **URL**: `http://localhost:8000/admin/login.php`
- **Username**: `admin`
- **Password**: `admin123`

---

## 6-Section Homepage Flow (Matching Reference Design)

1. **Section 01 — Hero**: 3D photorealistic Earth in Three.js with slow rotation, day/night lights, atmospheric rim glow, orbital rings (`ISS`, `HUBBLE`, `GEO`, `TIANGONG`), and live HUD telemetry counters (`14,280+ objects`, `1,000+ missions`, `248 deep space`).
2. **Section 02 — Where Have We Been?**: Large vertical typography and elliptical orbital transfer loops to Moon (142 missions), Mars (56 missions), Venus (45 missions), and Deep Space (21 missions).
3. **Section 03 — Global Mission Map**: Dark aerospace cartography displaying world launch complexes (Cape Canaveral, Baikonur, Kourou, Sriharikota, Jiuquan, Tanegashima, Vandenberg, Mahia) with animated ballistic arcs and trajectory filters.
4. **Section 04 — Space Agency Networks**: Constellation radar star-chart mapping NASA, ISRO, ESA, JAXA, CNSA, and Private aerospace sectors with collaborative links.
5. **Section 05 — The Machines We Left Behind**: Dedicated NASA Space Apps showcase featuring the Apollo 15 Lunar Roving Vehicle, Opportunity Rover, Spirit Rover, Surveyor 3, Voyager 1, and Philae.
6. **Section 06 — Humanity's Timeline**: Interactive horizontal scrubber (`1957`, `1969`, `1977`, `1997`, `2012`, `2021`, `NOW`) featuring the 1969 Apollo Command Module capsule spotlight.

---

## AI Feature: "Ask The Archive"
- Accessible via the **`ASK AI`** button in the header.
- Answers user questions strictly using verified records from the `orbital_archive` database with official citations.
