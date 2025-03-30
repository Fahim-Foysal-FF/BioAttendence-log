<?php
session_start();
require 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['Admin-name'])) {
    header("location: login.php");
    exit();
}

// Set JSON headers
header('Content-Type: application/json');

try {
    // Get parameters for filtering
    $department = isset($_GET['department']) ? $_GET['department'] : null;
    $mode = isset($_GET['mode']) ? $_GET['mode'] : null;
    
    // Base query
    $sql = "SELECT d.*, COUNT(a.id) as usage_count 
            FROM devices d
            LEFT JOIN attendance a ON d.device_uid = a.device_uid";
    
    // Add conditions based on filters
    $conditions = [];
    $params = [];
    
    if ($department) {
        $conditions[] = "d.device_dep = :department";
        $params[':department'] = $department;
    }
    
    if ($mode !== null) {
        $conditions[] = "d.device_mode = :mode";
        $params[':mode'] = $mode;
    }
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    
    $sql .= " GROUP BY d.id ORDER BY d.id DESC";
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    $devices = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $devices[] = [
            'id' => $row['id'],
            'name' => $row['device_name'],
            'department' => $row['device_dep'],
            'uid' => $row['device_uid'],
            'mode' => $row['device_mode'] == 0 ? 'Enrollment' : 'Attendance',
            'date_added' => $row['device_date'],
            'usage_count' => $row['usage_count']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $devices,
        'count' => count($devices),
        'generated_at' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>