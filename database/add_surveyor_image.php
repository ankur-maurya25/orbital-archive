<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

$check = $pdo->query("SELECT id FROM images WHERE equipment_id = 29 AND image_type = 'hero'")->fetch();
if (!$check) {
    $stmt = $pdo->prepare("
        INSERT INTO images (equipment_id, mission_id, image_url, title, credit, license, source, image_type)
        VALUES (29, 29, :url, :title, :credit, :license, :src, 'hero')
    ");
    $stmt->execute([
        'url' => 'https://images-assets.nasa.gov/image/as12-48-7134/as12-48-7134~orig.jpg',
        'title' => 'Apollo 12 Astronaut Pete Conrad with Surveyor 3 Lander',
        'credit' => 'NASA / Alan Bean (Apollo 12)',
        'license' => 'Public Domain',
        'src' => 'NASA Image and Video Library'
    ]);
    echo "Surveyor 3 hero image inserted successfully.\n";
} else {
    $stmt = $pdo->prepare("
        UPDATE images 
        SET image_url = :url, title = :title, credit = :credit, license = :license, source = :src
        WHERE id = :id
    ");
    $stmt->execute([
        'url' => 'https://images-assets.nasa.gov/image/as12-48-7134/as12-48-7134~orig.jpg',
        'title' => 'Apollo 12 Astronaut Pete Conrad with Surveyor 3 Lander',
        'credit' => 'NASA / Alan Bean (Apollo 12)',
        'license' => 'Public Domain',
        'src' => 'NASA Image and Video Library',
        'id' => $check['id']
    ]);
    echo "Surveyor 3 hero image updated successfully.\n";
}
