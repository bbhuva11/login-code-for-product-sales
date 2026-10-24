<?php
$host    = '127.0.0.1';
$user    = 'root';
$pass    = ''; // Set your MySQL password if configured
$charset = 'utf8mb4';

try {
    // 1. Connect to MySQL server without selecting a database
    $pdo = new PDO("mysql:host={$host};charset={$charset}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 2. Create the database
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `registration_login_system`
        CHARACTER SET utf8mb4 
        COLLATE utf8mb4_unicode_ci;
    ");

    // 3. Switch to the newly created database
    $pdo->exec("USE `registration_login_system`;");

    // 4. Create the users table
    $createTableSql = "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `full_name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `last_login_at` TIMESTAMP NULL DEFAULT NULL,
            `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($createTableSql);

    echo "Success: Database 'registration_login_system' and table 'users' were created successfully!";
} catch (PDOException $e) {
    exit("Setup failed: " . $e->getMessage());
}