<?php
// Configuration credentials
$host     = '127.0.0.1';
$db       = 'registration_login_system';
$user     = 'root';
$pass     = '';
$charset  = 'utf8mb4';

// Data Source Name
$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

// PDO options for error handling, fetch mode, and security
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Terminate and display error if connection fails
    exit('Database connection failed: ' . $e->getMessage());
}