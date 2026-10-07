# ORBITAL ARCHIVE — Production Deployment Checklist

Use this checklist before releasing or deploying ORBITAL ARCHIVE to a live production server.

---

## 1. Hosting & Infrastructure
- [ ] PHP 8.3+ verified (`php -v`)
- [ ] MySQL 8.0+ verified (`mysql --version`)
- [ ] Required PHP extensions active: `pdo`, `pdo_mysql`, `curl`, `json`, `mbstring`, `xml`
- [ ] HTTPS enabled with valid TLS/SSL certificate
- [ ] Web server (Apache/Nginx) configured with PHP-FPM
- [ ] Sensitive directory access blocked (`.git`, `.env`, `config/`, `database/`, `tools/`)
- [ ] Display errors disabled in production (`display_errors = Off` in `php.ini`)

---

## 2. Environment & Security
- [ ] `APP_URL` configured with the live production domain (e.g. `https://orbitalarchive.space`)
- [ ] Database credentials configured exclusively through environment variables or secured `.env`
- [ ] `.env` file verified **NOT** committed or exposed publicly (permissions `600`)
- [ ] Default administrator credentials rotated via `/admin/security.php`
- [ ] CSRF token verification operational across admin forms

---

## 3. Database & Archival Data
- [ ] Database schema imported cleanly (`database/schema.sql`)
- [ ] Archival seed data loaded (`database/seed.sql`)
- [ ] Relic safety migration executed (`php database/migrate_phase6_relic_safety.php`)
- [ ] Database integrity check passes 100% (`php database/verify_phase6_integrity.php`)
- [ ] Master QA test suite passes 100% (`php database/verify_phase6_qa.php`)

---

## 4. Application Verification
- [ ] Homepage renders Three.js Earth observatory and all 6 core sections
- [ ] All 10 REST API endpoints tested and returning valid JSON (`/api/*.php`)
- [ ] Global search tested (`/api/search.php?q=perseverance`)
- [ ] Ask The Archive assistant tested (`/api/ai-archive.php?q=chandrayaan`)
- [ ] Flagship equipment exhibitions verified (Perseverance, Voyager 1, Vikram, Pragyan, etc.)
- [ ] Archival documentary images and primary source links loading correctly
- [ ] Dedicated observatory 404 handler operational for invalid IDs
- [ ] Mobile responsive layout tested across 360px–1440px+ viewports
- [ ] Theme persistence verified across page reloads (NIGHT and ARCHIVE themes)

---

## 5. Repository & Architecture Boundaries
- [ ] GitHub repository remains the canonical source code repository
- [ ] **GitHub Pages is NOT used for production deployment**
- [ ] Production sitemap generated with live `APP_URL` (`php database/generate_sitemap.php`)
- [ ] `robots.txt` verified pointing to production sitemap
