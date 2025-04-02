<?php
$host = "dpg-cvk3h8gdl3ps73foe3lg-a"; // Host (without port)
$dbname = "app1_4v6k";                // Database name
$user = "app1_4v6k_user";             // Username
$password = "GyidmFDJG2hYgyxruZKyQCLFbTHO5c3U"; // Password

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to PostgreSQL successfully!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>