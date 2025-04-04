<?php
session_start();
require 'connectDB.php';

// Set default timezone
date_default_timezone_set('UTC');

// Initialize variables
$searchQuery = "1=1";
$params = [];

// Check if it's a refresh request
if (isset($_POST['refresh'])) {
    // Just get the latest logs without filtering
    $searchQuery = "1=1 ORDER BY id DESC LIMIT 100";
} 
// Check if it's initial load
elseif (isset($_POST['load_initial'])) {
    // Get recent logs (last 100 records)
    $searchQuery = "1=1 ORDER BY id DESC LIMIT 100";
}
// Check if it's a filter request
elseif (isset($_POST['filter_logs'])) {
    // Date filters
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    
    if (!empty($startDate) && !empty($endDate)) {
        if (strtotime($startDate) && strtotime($endDate)) {
            $searchQuery .= " AND DATE(check_date) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }
    }

    // Time filters
    if (isset($_POST['time_type'])) {
        $timeField = ($_POST['time_type'] == "Time_out") ? "time_out" : "time_in";
        $startTime = $_POST['start_time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';

        if (!empty($startTime)) {
            if (!empty($endTime)) {
                $searchQuery .= " AND TIME($timeField) BETWEEN :start_time AND :end_time";
                $params[':start_time'] = $startTime;
                $params[':end_time'] = $endTime;
            } else {
                $searchQuery .= " AND TIME($timeField) >= :start_time";
                $params[':start_time'] = $startTime;
            }
        } elseif (!empty($endTime)) {
            $searchQuery .= " AND TIME($timeField) <= :end_time";
            $params[':end_time'] = $endTime;
        }
    }

    // Fingerprint filter
    $fingerId = $_POST['finger_id'] ?? 0;
    if (!empty($fingerId) && $fingerId != 0) {
        $searchQuery .= " AND ul.fingerprint_id = :finger_id";
        $params[':finger_id'] = $fingerId;
    }

    // Department filter
    $deviceDep = $_POST['device_id'] ?? 0;
    if (!empty($deviceDep) && $deviceDep != 0) {
        $searchQuery .= " AND ul.device_dep = :device_dep";
        $params[':device_dep'] = $deviceDep;
    }

    $searchQuery .= " ORDER BY ul.id DESC";
}

try {
    // Main query with JOIN to get user names
    $sql = "SELECT ul.*, u.name AS username 
            FROM users_logs ul
            LEFT JOIN users u ON ul.fingerprint_id = u.fingerprint_id
            WHERE $searchQuery";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters with type checking
    foreach ($params as $key => $value) {
        $paramType = PDO::PARAM_STR;
        if (is_int($value)) $paramType = PDO::PARAM_INT;
        $stmt->bindValue($key, $value, $paramType);
    }
    
    $stmt->execute();
    
    // Output table
    echo '<div class="table-responsive" style="max-height: 500px; overflow-y: auto;">';
    echo '<table class="table table-striped table-hover">';
    echo '<thead class="thead-dark">';
    echo '<tr>
            <th>ID</th>
            <th>Name</th>
            <th>Card UID</th>
            <th>Fingerprint ID</th>
            <th>Department</th>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
          </tr>';
    echo '</thead>';
    echo '<tbody>';

    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            echo '<td>'.htmlspecialchars($row['id']).'</td>';
            echo '<td>'.htmlspecialchars($row['username'] ?? 'N/A').'</td>';
            echo '<td>'.htmlspecialchars($row['card_uid'] ?? 'N/A').'</td>';
            echo '<td>'.htmlspecialchars($row['fingerprint_id']).'</td>';
            echo '<td>'.htmlspecialchars($row['device_dep']).'</td>';
            echo '<td>'.htmlspecialchars($row['check_date']).'</td>';
            echo '<td>'.htmlspecialchars($row['time_in'] ?? '').'</td>';
            echo '<td>'.htmlspecialchars($row['time_out'] ?? '').'</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="8" class="text-center text-muted py-3">No attendance records found</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

} catch (PDOException $e) {
    error_log("Database error in user_log_up.php: " . $e->getMessage());
    echo '<div class="alert alert-danger">Error loading attendance data. Please try again.</div>';
}
?>