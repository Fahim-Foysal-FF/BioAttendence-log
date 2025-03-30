<?php
session_start();
require 'connectDB.php';

// Set default timezone
date_default_timezone_set('UTC');

// Initialize variables
$searchQuery = "1=1"; // Default to show all records
$params = [];

// Process filter parameters
if (isset($_POST['log_date']) || isset($_POST['select_date'])) {
    // Date filters
    $startDate = $_POST['date_sel_start'] ?? date("Y-m-d");
    $endDate = $_POST['date_sel_end'] ?? $startDate;
    
    if (!empty($startDate) && !empty($endDate)) {
        $searchQuery .= " AND checkindate BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;
    }

    // Time filters
    if (isset($_POST['time_sel'])) {
        $timeField = ($_POST['time_sel'] == "Time_out") ? "timeout" : "timein";
        $startTime = $_POST['time_sel_start'] ?? null;
        $endTime = $_POST['time_sel_end'] ?? null;

        if (!empty($startTime) && empty($endTime)) {
            $searchQuery .= " AND $timeField = :start_time";
            $params[':start_time'] = $startTime;
        } elseif (!empty($startTime) && !empty($endTime)) {
            $searchQuery .= " AND $timeField BETWEEN :start_time AND :end_time";
            $params[':start_time'] = $startTime;
            $params[':end_time'] = $endTime;
        }
    }

    // Fingerprint filter
    if (!empty($_POST['fing_sel']) && $_POST['fing_sel'] != 0) {
        $searchQuery .= " AND fingerprint_id = :finger_id";
        $params[':finger_id'] = $_POST['fing_sel'];
    }

    // Department filter
    if (!empty($_POST['dev_id']) && $_POST['dev_id'] != 0) {
        try {
            // Get device UID from department ID
            $sql = "SELECT device_uid FROM devices WHERE id = :dev_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':dev_id', $_POST['dev_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $searchQuery .= " AND device_uid = :device_uid";
                $params[':device_uid'] = $row['device_uid'];
            }
        } catch (PDOException $e) {
            error_log("Department filter error: " . $e->getMessage());
        }
    }
}

// Store search query in session
$_SESSION['searchQuery'] = $searchQuery;
$_SESSION['searchParams'] = $params;

// Prepare and execute the query
try {
    $sql = "SELECT * FROM users_logs WHERE $searchQuery ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    
    $stmt->execute();
    
    // Display results
    echo '<div class="table-responsive" style="max-height: 500px;">';
    echo '<table class="table table-striped">';
    echo '<thead class="table-primary">';
    echo '<tr>
            <th>ID</th>
            <th>Name</th>
            <th>Serial Number</th>
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
            echo '<td>'.htmlspecialchars($row['username']).'</td>';
            echo '<td>'.htmlspecialchars($row['serialnumber']).'</td>';
            echo '<td>'.htmlspecialchars($row['fingerprint_id']).'</td>';
            echo '<td>'.htmlspecialchars($row['device_dep']).'</td>';
            echo '<td>'.htmlspecialchars($row['checkindate']).'</td>';
            echo '<td>'.htmlspecialchars($row['timein']).'</td>';
            echo '<td>'.htmlspecialchars($row['timeout']).'</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="8" class="text-center">No records found</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Database error: '.htmlspecialchars($e->getMessage()).'</div>';
    error_log("Database error: " . $e->getMessage());
}
?>