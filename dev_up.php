<?php
session_start();
require 'connectDB.php';

// Handle AJAX requests
if (isset($_POST['dev_up'])) {
    try {
        $sql = "SELECT * FROM devices ORDER BY id DESC";
        $result = $conn->query($sql);
        
        if ($result->rowCount() > 0) {
            echo '<table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Device Name</th>
                            <th>Department</th>
                            <th>UID</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>
                        <td>'.htmlspecialchars($row['device_name']).'</td>
                        <td>'.htmlspecialchars($row['device_dep']).'</td>
                        <td>'.htmlspecialchars($row['device_uid']).'</td>
                        <td>'.htmlspecialchars($row['device_date']).'</td>
                        <td>
                            <button class="btn btn-sm btn-info edit-device" data-id="'.$row['id'].'">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-device" data-id="'.$row['id'].'">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-info">No devices found</div>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Error: '.htmlspecialchars($e->getMessage()).'</div>';
    }
    exit();
}

// Handle new device creation
if (isset($_POST['dev_name']) && isset($_POST['dev_dep'])) {
    try {
        $dev_name = $_POST['dev_name'];
        $dev_dep = $_POST['dev_dep'];
        $dev_uid = uniqid('dev_', true);
        
        $sql = "INSERT INTO devices (device_name, device_dep, device_uid) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$dev_name, $dev_dep, $dev_uid]);
        
        echo json_encode(['status' => 'success', 'message' => 'Device added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: '.$e->getMessage()]);
    }
    exit();
}
?>