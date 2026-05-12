<?php
// Test if config class and Database class load properly
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/model/Database.php';

echo "Config.php loaded successfully!<br>";
echo "Class 'config' exists: " . (class_exists('config') ? 'YES' : 'NO') . "<br>";
echo "Class 'Database' exists: " . (class_exists('Database') ? 'YES' : 'NO') . "<br><br>";

// Test config::getConnexion()
try {
    $pdo = config::getConnexion();
    echo "config::getConnexion() - SUCCESS<br>";
    echo "PDO object: " . get_class($pdo) . "<br><br>";
} catch (Exception $e) {
    echo "config::getConnexion() - FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br><br>";
}

// Test Database::getConnection()
try {
    $pdo = Database::getConnection();
    echo "Database::getConnection() - SUCCESS<br>";
    echo "PDO object: " . get_class($pdo) . "<br><br>";
} catch (Exception $e) {
    echo "Database::getConnection() - FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br><br>";
}

// Test Database::pdo() - THE NEW METHOD
try {
    $pdo = Database::pdo();
    echo "Database::pdo() - SUCCESS<br>";
    echo "PDO object: " . get_class($pdo) . "<br><br>";
    
    // Test a simple query
    $result = $pdo->query("SELECT 1 as test");
    $row = $result->fetch();
    echo "Test query result: " . $row['test'] . "<br>";
    echo "<br><strong>ALL TESTS PASSED!</strong>";
} catch (Exception $e) {
    echo "Database::pdo() - FAILED<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}
