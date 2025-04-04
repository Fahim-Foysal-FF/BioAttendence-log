<?php  
//Connect to database
require 'connectDB.php';
date_default_timezone_set('Asia/Damascus');
$d = date("Y-m-d");
$t = date("H:i:sa");

try {
    if (isset($_GET['FingerID']) && isset($_GET['device_token'])) {
        $fingerID = $_GET['FingerID'];
        $device_uid = $_GET['device_token'];

        // Check device validity
        $sql = "SELECT * FROM devices WHERE device_uid = :device_uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':device_uid', $device_uid);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $device_mode = $row['device_mode'];
            $device_dep = $row['device_dep'];
            
            if ($device_mode == 1) {
                // Attendance mode
                $sql = "SELECT * FROM users WHERE fingerprint_id = :fingerID";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':fingerID', $fingerID);
                $stmt->execute();
                
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if ($row['username'] != "None" && $row['add_fingerid'] == 0) {
                        $Uname = $row['username'];
                        $Number = $row['serialnumber'];
                        
                        // Check for existing login today without logout
                        $sql = "SELECT * FROM users_logs WHERE fingerprint_id = :fingerID AND checkindate = :checkindate AND timeout = ''";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':fingerID', $fingerID);
                        $stmt->bindParam(':checkindate', $d);
                        $stmt->execute();
                        
                        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                            // Login - no record for today
                            $sql = "INSERT INTO users_logs (username, serialnumber, fingerprint_id, device_uid, device_dep, checkindate, timein, timeout) 
                                    VALUES (:Uname, :Number, :fingerID, :device_uid, :device_dep, :checkindate, :timein, '00:00:00')";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':Uname', $Uname);
                            $stmt->bindParam(':Number', $Number);
                            $stmt->bindParam(':fingerID', $fingerID);
                            $stmt->bindParam(':device_uid', $device_uid);
                            $stmt->bindParam(':device_dep', $device_dep);
                            $stmt->bindParam(':checkindate', $d);
                            $stmt->bindParam(':timein', $t);
                            $stmt->execute();
                            
                            echo "login".$Uname;
                            exit();
                        } else {
                            // Logout - update existing record
                            $sql = "UPDATE users_logs SET timeout = :timeout, fingerout = 1 
                                    WHERE fingerprint_id = :fingerID AND checkindate = :checkindate AND fingerout = 0";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':timeout', $t);
                            $stmt->bindParam(':fingerID', $fingerID);
                            $stmt->bindParam(':checkindate', $d);
                            $stmt->execute();
                            
                            echo "logout".$Uname;
                            exit();
                        }
                    } else {
                        echo "Not registered!";
                        exit();
                    }
                } else {
                    echo "Not found!";
                    exit();
                }
            } elseif ($device_mode == 0) {
                // Enrollment mode
                $sql = "SELECT * FROM users WHERE fingerprint_id = :fingerID AND device_uid = :device_uid";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':fingerID', $fingerID);
                $stmt->bindParam(':device_uid', $device_uid);
                $stmt->execute();
                
                if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "available";
                    exit();
                } else {
                    $sql = "INSERT INTO users (device_uid, device_dep, fingerprint_id, user_date, add_fingerid) 
                            VALUES (:device_uid, :device_dep, :fingerID, CURRENT_DATE, 0)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':device_uid', $device_uid);
                    $stmt->bindParam(':device_dep', $device_dep);
                    $stmt->bindParam(':fingerID', $fingerID);
                    $stmt->execute();
                    
                    echo "successful";
                    exit();
                }
            }
        } else {
            echo "Invalid Device!";
            exit();
        }
    }

    if (isset($_GET['Get_Fingerid']) && isset($_GET['device_token'])) {
        $device_uid = $_GET['device_token'];
        
        $sql = "SELECT * FROM devices WHERE device_uid = :device_uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':device_uid', $device_uid);
        $stmt->execute();
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($_GET['Get_Fingerid'] == "get_id") {
                $sql = "SELECT fingerprint_id FROM users WHERE add_fingerid = 1 AND device_uid = :device_uid LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':device_uid', $device_uid);
                $stmt->execute();
                
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "add-id".$row['fingerprint_id'];
                    exit();
                } else {
                    echo "Nothing";
                    exit();
                }
            }
        } else {
            echo "Invalid Device";
            exit();
        }
    }

    if (isset($_GET['Check_mode']) && isset($_GET['device_token'])) {
        $device_uid = $_GET['device_token'];
        
        $sql = "SELECT * FROM devices WHERE device_uid = :device_uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':device_uid', $device_uid);
        $stmt->execute();
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($_GET['Check_mode'] == "get_mode") {
                $sql = "SELECT device_mode FROM devices WHERE device_uid = :device_uid";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':device_uid', $device_uid);
                $stmt->execute();
                
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "mode".$row['device_mode'];
                    exit();
                } else {
                    echo "Nothing";
                    exit();
                }
            }
        } else {
            echo "Invalid Device";
            exit();
        }
    }

    if (!empty($_GET['confirm_id']) && isset($_GET['device_token'])) {
        $fingerid = $_GET['confirm_id'];
        $device_uid = $_GET['device_token'];
        
        $sql = "SELECT * FROM devices WHERE device_uid = :device_uid";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':device_uid', $device_uid);
        $stmt->execute();
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            // Reset all fingerprint_select to 0
            $sql = "UPDATE users SET fingerprint_select = 0 WHERE fingerprint_select = 1 AND device_uid = :device_uid";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':device_uid', $device_uid);
            $stmt->execute();
            
            // Set the new fingerprint
            $sql = "UPDATE users SET add_fingerid = 0, fingerprint_select = 1 WHERE fingerprint_id = :fingerid AND device_uid = :device_uid";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':fingerid', $fingerid);
            $stmt->bindParam(':device_uid', $device_uid);
            $stmt->execute();
            
            echo "Fingerprint has been added!";
            exit();
        } else {
            echo "Invalid Device";
            exit();
        }
    }

    if (isset($_GET['DeleteID']) && isset($_GET['device_token'])) {
        $device_uid = $_GET['device_token'];
        
        if ($_GET['DeleteID'] == "check") {
            $sql = "SELECT * FROM devices WHERE device_uid = :device_uid";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':device_uid', $device_uid);
            $stmt->execute();
            
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $sql = "SELECT fingerprint_id FROM users WHERE del_fingerid = 1 AND device_uid = :device_uid LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':device_uid', $device_uid);
                $stmt->execute();
                
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "del-id".$row['fingerprint_id'];
                    
                    // Delete the fingerprint
                    $sql = "DELETE FROM users WHERE del_fingerid = 1";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    
                    exit();
                } else {
                    echo "nothing";
                    exit();
                }
            } else {
                echo "Invalid Device";
                exit();
            }
        }
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "SQL_Error";
    exit();
}
?>