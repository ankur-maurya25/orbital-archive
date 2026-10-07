# ORBITAL ARCHIVE — Production Deployment Guide

> [!CAUTION]
> **IMPORTANT FOR DEVELOPERS, SYSADMINS & AI/CODING AGENTS:**
> **ORBITAL ARCHIVE is NOT a GitHub Pages or static website.**
> - **DO NOT** configure GitHub Pages as its production deployment target.
> - **DO NOT** convert PHP files to static HTML.
> - **DO NOT** create a fake `index.html` or mock API responses for static hosts.
> - The application **strictly requires a server running PHP 8.3+ with MySQL 8+ and PDO**. GitHub Pages only serves static content and cannot execute PHP or communicate with a relational database.

---

## 1. System Architecture

```text
GitHub (https://github.com/ankur-maurya25/orbital-archive)
   ↓  (Canonical Source Code Repository Only)
Production Server (Linux / Apache / Nginx / Docker)
   ↓  (PHP 8.3+ Runtime & PDO Extension)
MySQL 8.0 Database (Normalized Relational Catalog)
   ↓
ORBITAL ARCHIVE Web Application (Server-Rendered Views & REST APIs)
```

- **GitHub Repository**: Serves solely as the version-controlled source code repository.
- **Production Server**: A PHP 8.3-capable environment that compiles and serves the server-side templates and REST endpoints.
- **Database Server**: MySQL 8.0+ hosting the relational schemas (`missions`, `equipment`, `instruments`, `relics`, `agencies`, `destinations`, `timeline_events`, `sources`, `images`).

---

## 2. Server Requirements

| Component | Minimum Requirement | Recommended |
|---|---|---|
| **Operating System** | Linux (Ubuntu 22.04 LTS / Debian 12 / RHEL 9) | Ubuntu 24.04 LTS |
| **Web Server** | Apache 2.4+ (with `mod_rewrite`) or Nginx 1.20+ | Nginx + PHP-FPM 8.3 |
| **PHP Runtime** | PHP 8.2+ | **PHP 8.3+** |
| **PHP Extensions** | `pdo`, `pdo_mysql`, `curl`, `json`, `mbstring`, `xml` | Standard `php8.3-fpm` stack |
| **Database** | MySQL 8.0+ or MariaDB 10.5+ | MySQL 8.0 Community / Cloud SQL |
| **TLS / SSL** | Valid SSL Certificate (Let's Encrypt / Certbot) | HTTPS enforced |

---

## 3. Environment Configuration

The application uses environment variables or a local `.env` file to configure connection parameters. **Never commit real credentials to Git.**

A template is provided in [`.env.example`](../.env.example):

```ini
# Application Base URL (used for canonical URLs, Open Graph, and sitemap generation)
APP_URL=https://your-production-domain.example

# Relational Database Connection (MySQL 8.0 / PDO)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=orbital_archive
DB_USER=your_database_user
DB_PASS=your_strong_database_password
```

---

## 4. Step-by-Step Production Deployment

### Step 1: Clone Repository onto Server
```bash
cd /var/www
git clone https://github.com/ankur-maurya25/orbital-archive.git orbital-archive
cd orbital-archive
```

### Step 2: Configure Environment
Copy `.env.example` to `.env` and set your production values:
```bash
cp .env.example .env
nano .env  # set APP_URL, DB_USER, DB_PASS, etc.
chmod 600 .env
```

### Step 3: Initialize MySQL Database
Log into MySQL and import the database schema and seed data:
```bash
mysql -u root -p -e "CREATE DATABASE orbital_archive CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p orbital_archive < database/schema.sql
mysql -u root -p orbital_archive < database/seed.sql
```

### Step 4: Run Archival Migrations & Pre-Flight Checks
```bash
# Execute safe relic taxonomy migrations
php database/migrate_phase6_relic_safety.php

# Generate production sitemap with APP_URL
APP_URL=https://your-production-domain.example php database/generate_sitemap.php

# Execute automated integrity & QA verification suites
php database/verify_phase6_integrity.php
php database/verify_phase6_qa.php
```

### Step 5: Web Server Configuration

#### Option A: Nginx (Recommended)
```nginx
server {
    listen 80;
    server_name your-production-domain.example;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-production-domain.example;

    root /var/www/orbital-archive;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/your-production-domain.example/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-production-domain.example/privkey.pem;

    # Protect sensitive directories and files
    location ~ /\.(env|git) {
        deny all;
        return 404;
    }

    location ~ ^/(config|database|includes|docs|tools)/ {
        deny all;
        return 404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### Option B: Apache
Ensure `mod_rewrite` and `mod_headers` are enabled:
```bash
sudo a2enmod rewrite headers
sudo systemctl restart apache2
```

---

## 5. Post-Deployment Verification

After deploying, verify the following 12 endpoints:

1. **Homepage**: `https://your-production-domain.example/` (HTTP 200)
2. **Catalog**: `https://your-production-domain.example/explore.php` (HTTP 200)
3. **Missions**: `https://your-production-domain.example/missions.php` (HTTP 200)
4. **Relics Vault**: `https://your-production-domain.example/relics.php` (HTTP 200)
5. **Timeline**: `https://your-production-domain.example/timeline.php` (HTTP 200)
6. **Flagship Exhibit**: `https://your-production-domain.example/equipment.php?id=perseverance` (HTTP 200)
7. **Telemetry API**: `https://your-production-domain.example/api/stats.php` (Valid JSON)
8. **Search API**: `https://your-production-domain.example/api/search.php?q=mars` (Valid JSON)
9. **Ask Archive API**: `https://your-production-domain.example/api/ai-archive.php?q=chandrayaan` (Valid JSON)
10. **Admin Route**: `https://your-production-domain.example/admin/login.php` (HTTP 200)
11. **Clean 404**: `https://your-production-domain.example/nonexistent-route` (Clean 404 notice)
12. **Sitemap & Robots**: `https://your-production-domain.example/sitemap.xml` & `robots.txt`

---

## 6. GitHub Pages Warning

> [!WARNING]
> **DO NOT USE GITHUB PAGES FOR PRODUCTION**
> GitHub Pages is a static hosting service. It does not provide a PHP interpreter or a MySQL database daemon. Attempting to deploy this project via GitHub Pages will result in Jekyll serving raw Markdown files or failing to execute application routes. Always deploy to a standard PHP/MySQL hosting provider.
