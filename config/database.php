<?php
/**
 * ORBITAL ARCHIVE - Database Connection (PDO)
 * Supports both standalone MySQL 8.0 and WAMP/XAMPP environments.
 */

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'orbital_archive');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS_PRIMARY', getenv('DB_PASS') ?: '');
define('DB_PASS_FALLBACK', '');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS_PRIMARY, $options);
        return $pdo;
    } catch (PDOException $ePrimary) {
        // Fallback to empty password (standard for WAMP / XAMPP)
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS_FALLBACK, $options);
            return $pdo;
        } catch (PDOException $eFallback) {
            error_log("Database connection error: " . $eFallback->getMessage());
            // Return error response if accessed via API, otherwise terminate gracefully
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
}
