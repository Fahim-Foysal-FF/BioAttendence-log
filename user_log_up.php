<?php
session_start();
require 'connectDB.php';

header('Content-Type: text/html; charset=utf-8');

// Debugging: Log received POST data
error_log(print_r($_POST, true));

// Initialize variables
$searchQuery = "1=1";
$params = [];

// Process filters
if (isset($_POST['filter_logs'])) {
    // Date filter
    if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
        $searchQuery .= " AND DATE(check_date) BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $_POST['start_date'];
        $params[':end_date'] = $_POST['end_date'];
    }

    // Time filter
    if (!empty($_POST['time_type'])) {
        $timeField = ($_POST['time_type'] == "Time_out") ? "time_out" : "time_in";
        
        if (!empty($_POST['start_time'])) {
            $searchQuery .= " AND TIME($timeField) >= :start_time";
            $params[':start_time'] = $_POST['start_time'];
        }
        if (!empty($_POST['end_time'])) {
            $searchQuery .= " AND TIME($timeField) <= :end_time";
            $params[':end_time'] = $_POST['end_time'];
        }
    }

    // Fingerprint filter
    if (!empty($_POST['finger_id']) && $_POST['finger_id'] != 0) {
        $searchQuery .= " AND ul.fingerprint_id = :finger_id";
        $params[':finger_id'] = $_POST['finger_id'];
    }

    // Device filter
    if (!empty($_POST['device_id']) && $_POST['device_id'] != 0) {
        $searchQuery .= " AND ul.device_dep = :device_dep";
        $params[':device_dep'] = $_POST['device_id'];
    }
}

try {
    // Main query
    $sql = "SELECT ul.*, u.username AS username, d.device_dep AS department 
            FROM users_logs ul
            LEFT JOIN users u ON ul.fingerprint_id = u.fingerprint_id
            LEFT JOIN devices d ON ul.device_uid = d.device_uid
            WHERE $searchQuery
            ORDER BY ul.id DESC
            LIMIT 500";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    
    // Output results
    if ($stmt->rowCount() > 0) {
        echo '<table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Fingerprint ID</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                    </tr>
                </thead>
                <tbody>';
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>
                    <td>'.$row['id'].'</td>
                    <td>'.htmlspecialchars($row['username'] ?? 'Unknown').'</td>
                    <td>'.$row['fingerprint_id'].'</td>
                    <td>'.htmlspecialchars($row['department'] ?? 'N/A').'</td>
                    <td>'.$row['check_date'].'</td>
                    <td>'.$row['time_in'].'</td>
                    <td>'.$row['time_out'].'</td>
                  </tr>';
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-info">No records found matching your criteria</div>';
    }
} catch(PDOException $e) {
    echo '<div class="alert alert-danger">Database Error: '.$e->getMessage().'</div>';
    error_log("Database error: " . $e->getMessage());
}
?>