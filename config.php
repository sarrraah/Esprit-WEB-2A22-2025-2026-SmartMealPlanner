<?php
/**
 * Minimal configuration file for MVC structure
 * All other files are in _project_files/
 */

// Database connection constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_meal_planner');

// Global PDO connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}

// Singleton config class
class config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_TIMEOUT => 5]
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->exec("SET NAMES utf8mb4");
            } catch (Exception $e) {
                die('Database Error: ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

// Verify class is loaded
if (!class_exists('config')) {
    die('CRITICAL ERROR: config class failed to load!');
}

// Helper functions
function determinerStatut($quantiteStock, $dateExpiration) {
    $dateActuelle = new DateTime();
    $dateExp = DateTime::createFromFormat('Y-m-d', $dateExpiration);
    if ($dateExp && $dateExp < $dateActuelle) {
        return 'Expired';
    } elseif ($quantiteStock == 0) {
        return 'Out of Stock';
    } elseif ($quantiteStock > 0) {
        return 'Available';
    }
    return 'Unknown';
}

// Upload directory constants
define('UPLOAD_DIR', __DIR__ . '/_project_files/uploads/');
define('UPLOAD_URL', '/smart_meal_planner/Esprit-WEB-2A22-2025-2026-SmartMealPlanner/_project_files/uploads/');

// Allowed extensions
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

// Define APP_ROOT constant
define('APP_ROOT', __DIR__);
