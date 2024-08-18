<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'shortener');
define('DB_USER', 'root');
define('DB_PASS', '');

define("earnings_per_click", 50);

// Define a constant API Key
define('API_KEY', '123');

// Create a new PDO instance
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}
?>
