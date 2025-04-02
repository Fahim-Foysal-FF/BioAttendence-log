<?php
$host = "your_host";
$dbname = "your_db";
$user = "your_user";
$password = "your_pass";

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // No echo here!
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection error");
}
?>