<?php
session_start();
require 'connectDB.php';

// Check if admin is logged in
if (!isset($_SESSION['Admin-name'])) {
    header("location: login.php");
    exit();
}

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=devices_export_".date('Y-m-d_H-i-s').".xls");
header("Pragma: no-cache");
header("Expires: 0");

try {
    // Query to get all devices with additional details
    $sql = "SELECT d.*, COUNT(a.id) as attendance_count 
            FROM devices d
            LEFT JOIN attendance a ON d.device_uid = a.device_uid
            GROUP BY d.id
            ORDER BY d.id DESC";
    $stmt = $conn->query($sql);
    
    // Start Excel HTML table
    echo '<table border="1">
            <tr>
                <th colspan="7" style="text-align:center; background-color:#f2f2f2; font-size:18px;">
                    Devices Export - '.date('Y-m-d H:i:s').'
                </th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Device Name</th>
                <th>Department</th>
                <th>UID</th>
                <th>Mode</th>
                <th>Date Added</th>
                <th>Usage Count</th>
            </tr>';
    
    // Add data rows
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mode = ($row['device_mode'] == 0) ? 'Enrollment' : 'Attendance';
        
        echo '<tr>
                <td>'.$row['id'].'</td>
                <td>'.htmlspecialchars($row['device_name']).'</td>
                <td>'.htmlspecialchars($row['device_dep']).'</td>
                <td>'.$row['device_uid'].'</td>
                <td>'.$mode.'</td>
                <td>'.$row['device_date'].'</td>
                <td>'.$row['attendance_count'].'</td>
              </tr>';
    }
    
    echo '</table>';
    
} catch (PDOException $e) {
    // Log error and redirect if export fails
    error_log("Excel export failed: ".$e->getMessage());
    $_SESSION['error'] = "Failed to generate Excel export: ".$e->getMessage();
    header("location: devices.php");
    exit();
}
?>