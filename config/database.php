<?php
/**
 * ORBITAL ARCHIVE - Database Connection (PDO)
 * Production-ready environment-driven database configuration.
 */

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'orbital_archive');
define('DB_USER', getenv('DB_USER') ?: 'root');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbPass = getenv('DB_PASS');
    if ($dbPass === false) {
        error_log("Database configuration error: DB_PASS environment variable is not set.");
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Database configuration incomplete: DB_PASS environment variable is required.'
            ]);
            exit;
        }
        die("Database configuration error: DB_PASS environment variable is required. Please check your environment configuration or .env file.");
    }

    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, $dbPass, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed. Code: " . $e->getCode());
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Archive database connection unavailable. System telemetry recorded.'
            ]);
            exit;
        }
        die("Archive Database Unavailable. System telemetry recorded.");
    }
}
