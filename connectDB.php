<?php
// Parse the connection string
$db_connection_string = "postgresql://app1_4v6k_user:GyidmFDJG2hYgyxruZKyQCLFbTHO5c3U@dpg-cvk3h8gdl3ps73foe3lg-a.singapore-postgres.render.com/app1_4v6k";

// Extract components from the connection string
$db_parts = parse_url($db_connection_string);

$host = $db_parts['host'];  // dpg-cvk3h8gdl3ps73foe3lg-a.singapore-postgres.render.com
$dbname = ltrim($db_parts['path'], '/');  // app1_4v6k
$user = $db_parts['user'];  // app1_4v6k_user
$password = $db_parts['pass'];  // GyidmFDJG2hYgyxruZKyQCLFbTHO5c3U
$port = $db_parts['port'] ?? '5432';  // Default PostgreSQL port

try {
    // Create PDO connection with SSL for Render
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false
        ]
    );
    
    // Test connection
    $conn->query("SELECT 1");
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    // Don't show sensitive error details in production
    die("Database connection error. Please try again later.");
}
?>