<?php
/**
 * db.php — Central PDO database connection
 * All queries across the app MUST use prepared statements against $pdo.
 */

// ---- Configuration (edit these for your environment) ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'minecommunity');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---- Server display info (used on the home page) ----
define('SERVER_IP', 'play.minecommunity.net');
define('SERVER_NAME', 'MineCommunity');

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements -> strong SQLi protection
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Never leak credentials or raw DB errors to the client
    http_response_code(500);
    die('Database connection failed. Please try again later.');
}
