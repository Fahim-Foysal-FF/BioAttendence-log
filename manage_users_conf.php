<?php
require 'connectDB.php';

// Add user Fingerprint
if (isset($_POST['Add_fingerID'])) {
    try {
        $fingerid = (int)$_POST['fingerid'];
        $dev_uid = (int)$_POST['dev_id'];

        // Validate inputs
        if ($fingerid == 0) {
            throw new Exception("Enter a Fingerprint ID!");
        }
        if ($dev_uid == 0) {
            throw new Exception("Select the User department!");
        }
        if ($fingerid <= 0 || $fingerid >= 128) {
            throw new Exception("The Fingerprint ID must be between 1 & 127");
        }

        // Check device exists
        $stmt = $conn->prepare("SELECT device_dep, device_uid FROM devices WHERE id = :dev_id");
        $stmt->bindParam(':dev_id', $dev_uid, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($device = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dev_name = $device['device_dep'];
            $dev_uid = $device['device_uid'];

            // Check if fingerprint ID already exists
            $stmt = $conn->prepare("SELECT fingerprint_id FROM users WHERE fingerprint_id = :fingerid AND device_uid = :dev_uid");
            $stmt->bindParam(':fingerid', $fingerid, PDO::PARAM_INT);
            $stmt->bindParam(':dev_uid', $dev_uid);
            $stmt->execute();

            if ($stmt->fetch()) {
                throw new Exception("This ID already exists! Delete it from the scanner");
            }

            // Check if there's already a fingerprint being added
            $stmt = $conn->prepare("SELECT add_fingerid FROM users WHERE add_fingerid = 1 AND device_uid = :dev_uid");
            $stmt->bindParam(':dev_uid', $dev_uid);
            $stmt->execute();

            if ($stmt->fetch()) {
                throw new Exception("You can't add more than one ID each time");
            }

            // Reset all selections
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("UPDATE users SET fingerprint_select = 0 WHERE fingerprint_select = 1 AND device_uid = :dev_uid");
            $stmt->bindParam(':dev_uid', $dev_uid);
            $stmt->execute();

            // Add new fingerprint
            $stmt = $conn->prepare("INSERT INTO users (fingerprint_id, fingerprint_select, user_date, device_uid, device_dep, del_fingerid, add_fingerid) 
                                   VALUES (:fingerid, 1, CURRENT_DATE, :dev_uid, :dev_name, 0, 1)");
            $stmt->bindParam(':fingerid', $fingerid, PDO::PARAM_INT);
            $stmt->bindParam(':dev_uid', $dev_uid);
            $stmt->bindParam(':dev_name', $dev_name);
            $stmt->execute();

            $conn->commit();
            echo "1";
        } else {
            throw new Exception("Invalid device selected");
        }
    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo $e->getMessage();
    }
    exit();
}

// Add user details
if (isset($_POST['Add'])) {
    try {
        $Uname = trim($_POST['name']);
        $Number = trim($_POST['number']);
        $dev_uid = $_POST['dev_uid'];
        $finger_id = (int)$_POST['finger_id'];
        $Gender = $_POST['gender'] ?? null;

        // Validate inputs
        if (empty($Uname) || empty($Number)) {
            throw new Exception("Empty Fields");
        }
        if (empty($Gender)) {
            throw new Exception("Gender empty!");
        }

        // Check if fingerprint exists and is not already added
        $stmt = $conn->prepare("SELECT username FROM users WHERE fingerprint_id = :finger_id AND device_uid = :dev_uid");
        $stmt->bindParam(':finger_id', $finger_id, PDO::PARAM_INT);
        $stmt->bindParam(':dev_uid', $dev_uid);
        $stmt->execute();

        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($user['username'] != "None") {
                throw new Exception("This Fingerprint is already added");
            }

            // Check if serial number is unique
            $stmt = $conn->prepare("SELECT serialnumber FROM users WHERE serialnumber = :number AND device_uid = :dev_uid");
            $stmt->bindParam(':number', $Number);
            $stmt->bindParam(':dev_uid', $dev_uid);
            $stmt->execute();

            if ($stmt->fetch()) {
                throw new Exception("The serial number is already taken!");
            }

            // Update user details
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("UPDATE users SET username = :uname, serialnumber = :number, gender = :gender, user_date = CURRENT_DATE 
                                   WHERE fingerprint_select = 1 AND device_uid = :dev_uid");
            $stmt->bindParam(':uname', $Uname);
            $stmt->bindParam(':number', $Number);
            $stmt->bindParam(':gender', $Gender);
            $stmt->bindParam(':dev_uid', $dev_uid);
            $stmt->execute();

            // Reset selection
            $stmt = $conn->prepare("UPDATE users SET fingerprint_select = 0 WHERE device_uid = :dev_uid");
            $stmt->bindParam(':dev_uid', $dev_uid);
            $stmt->execute();

            $conn->commit();
            echo "1";
        } else {
            throw new Exception("There's no selected Fingerprint!");
        }
    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo $e->getMessage();
    }
    exit();
}

// Update user
if (isset($_POST['Update'])) {
    try {
        $Uname = trim($_POST['name']);
        $Number = trim($_POST['number']);
        $dev_uid = $_POST['dev_uid'];
        $finger_id = (int)$_POST['finger_id'];
        $Gender = $_POST['gender'] ?? null;

        // Validate inputs
        if (empty($Gender)) {
            throw new Exception("Gender empty!");
        }
        if (empty($Uname) && empty($Number)) {
            throw new Exception("Empty Fields");
        }

        // Check if user exists and is not pending addition
        $stmt = $conn->prepare("SELECT add_fingerid FROM users WHERE fingerprint_select = 1 AND device_uid = :dev_uid");
        $stmt->bindParam(':dev_uid', $dev_uid);
        $stmt->execute();

        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($user['add_fingerid'] == 1) {
                throw new Exception("First, You need to add the User!");
            }

            // Check if serial number is unique (excluding current user)
            $stmt = $conn->prepare("SELECT serialnumber FROM users WHERE serialnumber = :number AND fingerprint_select = 0");
            $stmt->bindParam(':number', $Number);
            $stmt->execute();

            if ($stmt->fetch()) {
                throw new Exception("The serial number is already taken!");
            }

            // Update user
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("UPDATE users SET username = :uname, serialnumber = :number, gender = :gender 
                                   WHERE fingerprint_select = 1 AND device_uid = :dev_uid");
            $stmt->bindParam(':uname', $Uname);
            $stmt->bindParam(':number', $Number);
            $stmt->bindParam(':gender', $Gender);
            $stmt->bindParam(':dev_uid', $dev_uid);
            $stmt->execute();

            // Reset selection
            $stmt = $conn->prepare("UPDATE users SET fingerprint_select = 0 WHERE device_uid = :dev_uid");
            $stmt->bindParam(':dev_uid', $dev_uid);
            $stmt->execute();

            $conn->commit();
            echo "1";
        } else {
            throw new Exception("There's no selected User to update!");
        }
    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        echo $e->getMessage();
    }
    exit();
}

// Select fingerprint
if (isset($_GET['select'])) {
    try {
        $finger_id = (int)$_GET['finger_id'];
        $dev_uid = $_GET['dev_uid'];

        $conn->beginTransaction();
        
        // Reset all selections
        $stmt = $conn->prepare("UPDATE users SET fingerprint_select = 0 WHERE device_uid = :dev_uid");
        $stmt->bindParam(':dev_uid', $dev_uid);
        $stmt->execute();

        // Select the specified fingerprint
        $stmt = $conn->prepare("UPDATE users SET fingerprint_select = 1 WHERE fingerprint_id = :finger_id AND device_uid = :dev_uid");
        $stmt->bindParam(':finger_id', $finger_id, PDO::PARAM_INT);
        $stmt->bindParam(':dev_uid', $dev_uid);
        $stmt->execute();

        // Get user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE fingerprint_id = :finger_id AND device_uid = :dev_uid");
        $stmt->bindParam(':finger_id', $finger_id, PDO::PARAM_INT);
        $stmt->bindParam(':dev_uid', $dev_uid);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $conn->commit();
        
        header('Content-Type: application/json');
        echo json_encode($data);
    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit();
}

// Delete user
if (isset($_POST['delete'])) {
    try {
        $finger_id = (int)$_POST['finger_id'];
        $dev_uid = $_POST['dev_uid'];

        if ($finger_id == 0) {
            throw new Exception("There no selected user to remove");
        }

        $stmt = $conn->prepare("UPDATE users SET del_fingerid = 1 WHERE fingerprint_id = :finger_id AND device_uid = :dev_uid");
        $stmt->bindParam(':finger_id', $finger_id, PDO::PARAM_INT);
        $stmt->bindParam(':dev_uid', $dev_uid);
        $stmt->execute();

        echo "1";
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    exit();
}