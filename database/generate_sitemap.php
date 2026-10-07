<?php
/**
 * ORBITAL ARCHIVE - Sitemap Generator
 * Queries MySQL database and outputs production sitemap.xml
 */

require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$baseUrl = 'http://localhost:8000';
$today = date('Y-m-d');

$xml = new XMLWriter();
$xml->openMemory();
$xml->setIndent(true);
$xml->setIndentString('  ');
$xml->startDocument('1.0', 'UTF-8');
$xml->startElement('urlset');
$xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

function addUrl($xml, $loc, $lastmod, $changefreq, $priority) {
    $xml->startElement('url');
    $xml->writeElement('loc', $loc);
    $xml->writeElement('lastmod', $lastmod);
    $xml->writeElement('changefreq', $changefreq);
    $xml->writeElement('priority', $priority);
    $xml->endElement();
}

// 1. Root Pages
addUrl($xml, "$baseUrl/", $today, 'daily', '1.0');
addUrl($xml, "$baseUrl/destinations.php", $today, 'weekly', '0.9');
addUrl($xml, "$baseUrl/missions.php", $today, 'weekly', '0.9');
addUrl($xml, "$baseUrl/relics.php", $today, 'weekly', '0.9');
addUrl($xml, "$baseUrl/agencies.php", $today, 'weekly', '0.8');
addUrl($xml, "$baseUrl/timeline.php", $today, 'weekly', '0.8');

// 2. Destinations
$dests = $pdo->query("SELECT id FROM destinations ORDER BY id ASC")->fetchAll();
foreach ($dests as $d) {
    addUrl($xml, "$baseUrl/destinations.php?id=" . $d['id'], $today, 'weekly', '0.8');
}

// 3. Agencies
$agencies = $pdo->query("SELECT id FROM agencies ORDER BY id ASC")->fetchAll();
foreach ($agencies as $a) {
    addUrl($xml, "$baseUrl/agencies.php?id=" . $a['id'], $today, 'weekly', '0.7');
}

// 4. Missions
$missions = $pdo->query("SELECT id FROM missions ORDER BY id ASC")->fetchAll();
foreach ($missions as $m) {
    addUrl($xml, "$baseUrl/missions.php?id=" . $m['id'], $today, 'weekly', '0.8');
}

// 5. Equipment & Flagship Exhibitions
$equipment = $pdo->query("SELECT id, slug, last_verified FROM equipment ORDER BY id ASC")->fetchAll();
foreach ($equipment as $eq) {
    $slugOrId = !empty($eq['slug']) ? $eq['slug'] : $eq['id'];
    $lastmod = !empty($eq['last_verified']) ? date('Y-m-d', strtotime($eq['last_verified'])) : $today;
    addUrl($xml, "$baseUrl/equipment.php?id=" . urlencode($slugOrId), $lastmod, 'weekly', '0.9');
}

$xml->endElement(); // </urlset>
$xml->endDocument();

$content = $xml->outputMemory();
$targetPath = __DIR__ . '/../sitemap.xml';
file_put_contents($targetPath, $content);

echo "Sitemap successfully generated at sitemap.xml with " . (6 + count($dests) + count($agencies) + count($missions) + count($equipment)) . " URLs.\n";
