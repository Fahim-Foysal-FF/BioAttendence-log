<?php  
// Connect to database with error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'connectDB.php';
date_default_timezone_set('Asia/Damascus');
// Use this for all time values
$d = date("Y-m-d");
$t = date("H:i:s"); // 24-hour format without AM/PM

// For display purposes only (not database storage)
$display_time = date("h:i:sa"); // 12-hour format with AM/PM

try {
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['FingerID']) && isset($_GET['device_token'])) {
        $fingerID = (int)$_GET['FingerID'];
        $device_uid = $_GET['device_token'];

        // Validate device first
        $stmt = $conn->prepare("SELECT * FROM devices WHERE device_uid = :device_uid");
        $stmt->bindParam(':device_uid', $device_uid);
        $stmt->execute();
        
        if ($device = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $device_mode = (int)$device['device_mode'];
            $device_dep = $device['device_dep'];
            
            if ($device_mode == 1) { // Attendance mode
                $stmt = $conn->prepare("SELECT * FROM users WHERE fingerprint_id = :fingerID");
                $stmt->bindParam(':fingerID', $fingerID);
                $stmt->execute();
                
                if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($user['username'] != "None" && $user['add_fingerid'] == 0) {
                        // Check for existing login today without logout
                        $stmt = $conn->prepare("SELECT id FROM users_logs 
                                               WHERE fingerprint_id = :fingerID 
                                               AND checkindate = :checkindate 
                                               AND (timeout IS NULL OR timeout = '00:00:00')");
                        $stmt->bindParam(':fingerID', $fingerID);
                        $stmt->bindParam(':checkindate', $d);
                        $stmt->execute();
                        
                        if ($stmt->rowCount() == 0) {
                            // Login - no record for today
                            $stmt = $conn->prepare("INSERT INTO users_logs 
                                                  (username, serialnumber, fingerprint_id, device_uid, device_dep, checkindate, timein, timeout) 
                                                  VALUES (:Uname, :Number, :fingerID, :device_uid, :device_dep, :checkindate, :timein, '00:00:00')");
                            $stmt->execute([
                                ':Uname' => $user['username'],
                                ':Number' => $user['serialnumber'],
                                ':fingerID' => $fingerID,
                                ':device_uid' => $device_uid,
                                ':device_dep' => $device_dep,
                                ':checkindate' => $d,
                                ':timein' => $t
                            ]);
                            echo "login".$user['username'];
                        } else {
                            // Logout - update existing record
                            $stmt = $conn->prepare("UPDATE users_logs 
                                                  SET timeout = :timeout, fingerout = 1 
                                                  WHERE fingerprint_id = :fingerID 
                                                  AND checkindate = :checkindate 
                                                  AND fingerout = 0");
                            $stmt->execute([
                                ':timeout' => $t,
                                ':fingerID' => $fingerID,
                                ':checkindate' => $d
                            ]);
                            echo "logout".$user['username'];
                        }
                    } else {
                        echo "Not registered!";
                    }
                } else {
                    echo "Not found!";
                }
            } elseif ($device_mode == 0) { // Enrollment mode
                $stmt = $conn->prepare("SELECT id FROM users WHERE fingerprint_id = :fingerID AND device_uid = :device_uid");$stmt->execute([
                    ':fingerID' => $fingerID,
                    ':device_uid' => $device_uid
                ]);
                
                if ($stmt->rowCount() > 0) {
                    echo "available";
                } else {
                    $stmt = $conn->prepare("INSERT INTO users 
                                          (device_uid, device_dep, fingerprint_id, user_date, add_fingerid) 
                                          VALUES (:device_uid, :device_dep, :fingerID, CURRENT_DATE, 0)");
                    $stmt->execute([
                        ':device_uid' => $device_uid,
                        ':device_dep' => $device_dep,
                        ':fingerID' => $fingerID
                    ]);
                    echo "successful";
                }
            }
        } else {
            echo "Invalid Device!";
        }
        exit();
    }

    // Handle other request types
    if (isset($_GET['Get_Fingerid']) && $_GET['Get_Fingerid'] == "get_id" && isset($_GET['device_token'])) {
        $device_uid = $_GET['device_token'];
        
        $stmt = $conn->prepare("SELECT fingerprint_id FROM users 
                               WHERE add_fingerid = 1 AND device_uid = :device_uid 
                               LIMIT 1");
        $stmt->bindParam(':device_uid', $device_uid);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "add-id".$row['fingerprint_id'];
        } else {
            echo "Nothing";
        }
        exit();
    }

    if (isset($_GET['Check_mode']) && $_GET['Check_mode'] == "get_mode" && isset($_GET['device_token'])) {
        $device_uid = $_GET['device_token'];
        
        $stmt = $conn->prepare("SELECT device_mode FROM devices WHERE device_uid = :device_uid");
        $stmt->bindParam(':device_uid', $device_uid);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "mode".$row['device_mode'];
        } else {
            echo "Nothing";
        }
        exit();
    }

    if (!empty($_GET['confirm_id']) && isset($_GET['device_token'])) {
        $fingerid = (int)$_GET['confirm_id'];
        $device_uid = $_GET['device_token'];
        
        $conn->beginTransaction();
        try {
            // Reset all fingerprint_select to 0 for this device
            $stmt = $conn->prepare("UPDATE users SET fingerprint_select = 0 
                                   WHERE fingerprint_select = 1 AND device_uid = :device_uid");
            $stmt->bindParam(':device_uid', $device_uid);
            $stmt->execute();
            
            // Set the new fingerprint
            $stmt = $conn->prepare("UPDATE users 
                                  SET add_fingerid = 0, fingerprint_select = 1 
                                  WHERE fingerprint_id = :fingerid AND device_uid = :device_uid");
            $stmt->execute([
                ':fingerid' => $fingerid,
                ':device_uid' => $device_uid
            ]);
            $conn->commit();
            echo "Fingerprint has been added!";
        } catch (Exception $e) {
            $conn->rollBack();
            echo "Error: ".$e->getMessage();
        }
        exit();
    }if (isset($_GET['DeleteID']) && $_GET['DeleteID'] == "check" && isset($_GET['device_token'])) {
        $device_uid = $_GET['device_token'];
        
        $stmt = $conn->prepare("SELECT fingerprint_id FROM users 
                              WHERE del_fingerid = 1 AND device_uid = :device_uid 
                              LIMIT 1");
        $stmt->bindParam(':device_uid', $device_uid);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "del-id".$row['fingerprint_id'];
            
            // Delete the fingerprint
            $stmt = $conn->prepare("DELETE FROM users WHERE del_fingerid = 1 AND device_uid = :device_uid");
            $stmt->bindParam(':device_uid', $device_uid);
            $stmt->execute();
        } else {
            echo "nothing";
        }
        exit();
    }

    echo "Invalid request";
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    echo "SQL_Error: ".$e->getMessage();
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    header("HTTP/1.1 400 Bad Request");
    echo "Error: ".$e->getMessage();
}
?>