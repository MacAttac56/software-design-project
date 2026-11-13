<?php
$host = "localhost";
$dbname = "GroupHailMary";
$username = "root";
$password = ""; // Default local XAMPP setup

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit("Database connection failed. Check your settings.");
}
?>
