<?php
session_start();
?>
<div class="table-responsive" style="max-height: 500px; overflow-y: auto;"> 
  <table class="table table-striped table-hover">
    <thead class="table-primary">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Serial Number</th>
        <th>Fingerprint ID</th>
        <th>Device Dep</th>
        <th>Date</th>
        <th>Time In</th>
        <th>Time Out</th>
      </tr>
    </thead>
    <tbody class="table-secondary">
      <?php
        require 'connectDB.php';
        
        try {
            $searchQuery = "1=1"; // Default condition that's always true
            $params = [];
            
            // Initialize date filters
            $Start_date = date("Y-m-d");
            $End_date = date("Y-m-d");
            
            // Process filters if form was submitted
            if (isset($_POST['log_date'])) {
                // Date range filter
                if (!empty($_POST['date_sel_start'])) {
                    $Start_date = $_POST['date_sel_start'];
                    $searchQuery .= " AND checkindate >= :start_date";
                    $params[':start_date'] = $Start_date;
                }
                
                if (!empty($_POST['date_sel_end'])) {
                    $End_date = $_POST['date_sel_end'];
                    $searchQuery .= " AND checkindate <= :end_date";
                    $params[':end_date'] = $End_date;
                }
                
                // Time filter
                $timeField = ($_POST['time_sel'] == "Time_out") ? "timeout" : "timein";
                
                if (!empty($_POST['time_sel_start'])) {
                    $Start_time = $_POST['time_sel_start'];
                    
                    if (!empty($_POST['time_sel_end'])) {
                        $End_time = $_POST['time_sel_end'];
                        $searchQuery .= " AND $timeField BETWEEN :start_time AND :end_time";
                        $params[':start_time'] = $Start_time;
                        $params[':end_time'] = $End_time;
                    } else {
                        $searchQuery .= " AND $timeField = :start_time";
                        $params[':start_time'] = $Start_time;
                    }
                }
                
                // Fingerprint filter
                if (!empty($_POST['fing_sel'])) {
                    $searchQuery .= " AND fingerprint_id = :finger_id";
                    $params[':finger_id'] = $_POST['fing_sel'];
                }
                
                // Department filter
                if (!empty($_POST['dev_id'])) {
                    // Get device UID from device ID
                    $stmt = $conn->prepare("SELECT device_uid FROM devices WHERE id = :dev_id");
                    $stmt->bindParam(':dev_id', $_POST['dev_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    
                    if ($device = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $searchQuery .= " AND device_uid = :dev_uid";
                        $params[':dev_uid'] = $device['device_uid'];
                    }
                }
                
                $_SESSION['searchQuery'] = $searchQuery;
                $_SESSION['searchParams'] = $params;
            }
            
            // For default view (today's logs)
            if (isset($_POST['select_date']) && $_POST['select_date'] == 1) {
                $searchQuery = "checkindate = CURRENT_DATE";
                $params = [];
            }
            
            // Use session-stored query if available
            if (isset($_SESSION['searchQuery'])) {
                $searchQuery = $_SESSION['searchQuery'];
                $params = $_SESSION['searchParams'] ?? [];
            }
            
            // Prepare and execute the query
            $sql = "SELECT * FROM users_logs WHERE $searchQuery ORDER BY id DESC";
            $stmt = $conn->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => &$val) {
                $paramType = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindParam($key, $val, $paramType);
            }
            
            $stmt->execute();
            
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
        } catch (PDOException $e) {
            echo '<tr><td colspan="8" class="error">Database error: '.htmlspecialchars($e->getMessage()).'</td></tr>';
            error_log("Database error in user_log_up.php: " . $e->getMessage());
        }
      ?>
    </tbody>
  </table>
</div>