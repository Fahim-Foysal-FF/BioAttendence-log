<?php
// Parse the connection string
$db_connection_string = "postgresql://app1_4v6k_user:GyidmFDJG2hYgyxruZKyQCLFbTHO5c3U@dpg-cvk3h8gdl3ps73foe3lg-a.singapore-postgres.render.com/app1_4v6k";

// Extract components from the connection string
$db_parts = parse_url($db_connection_string);

$host = $db_parts['host'];  // dpg-cvk3h8gdl3ps73foe3lg-a.singapore-postgres.render.com
$dbname = ltrim($db_parts['path'], '/');  // app1_4 6  k
$user = $db_parts['user'];  // app1_4v6k_user
$password = $db_parts['pass'];  // GyidmFDJG2hYgyxruZKyQCLFbTHO5  c3  U
$port = $db_parts['port'] ?? '5432';  // Default PostgreSQL port

// Session and error reporting setup
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable in production
ini_set('log_errors', 1);

try {
    // Create PDO connection with SSL for Render
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_EMULATE_PREPARES => false, // Important for PostgreSQL
            PDO::ATTR_D  EFA  U  L  T_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Test connection
    $conn->query("SELECT 1");
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    // Secure  error  handling  for  production
    if (str  pos  ($  e  ->  get  Message  (),  'password  authentication  failed')  !==  false) {
        die("  Authentication  failed.  Please  check  your  credentials.  ");
    }  else  {
        die("  Database  connection  error.  Please  try  again  later.  ");
    }
}

  //  Set  timezone  if  needed
  $  conn  ->  exec  ("  SET  TIME  ZONE  '  UTC  '  ");
  ?>