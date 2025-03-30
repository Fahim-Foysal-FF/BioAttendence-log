<?php
session_start();
require('connectDB.php'); // Make sure this uses PDO for PostgreSQL

try {
    if (isset($_POST['dev_add'])) {
        // Add new device
        $dev_name = $_POST['dev_name'];
        $dev_dep = $_POST['dev_dep'];

        if (empty($dev_name)) {
            echo '<p class="alert alert-danger">Please, Set the device name!!</p>';
        } elseif (empty($dev_dep)) {
            echo '<p class="alert alert-danger">Please, Set the device department!!</p>';
        } else {
            $token = random_bytes(4);
            $dev_token = bin2hex($token);

            $sql = "INSERT INTO devices (device_name, device_dep, device_uid, device_date) 
                    VALUES(:name, :dep, :uid, CURRENT_DATE)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':name', $dev_name);
            $stmt->bindParam(':dep', $dev_dep);
            $stmt->bindParam(':uid', $dev_token);
            $stmt->execute();
            echo 1;
        }

    } elseif (isset($_POST['dev_del'])) {
        // Delete device
        $dev_del = $_POST['dev_sel'];

        $sql = "DELETE FROM devices WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $dev_del, PDO::PARAM_INT);
        $stmt->execute();
        echo 1;

    } elseif (isset($_POST['dev_uid_up'])) {
        // Update device UID
        $dev_id = $_POST['dev_id_up'];
        $token = random_bytes(8);
        $dev_token = bin2hex($token);

        $sql = "UPDATE devices SET device_uid = :uid WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':uid', $dev_token);
        $stmt->bindParam(':id', $dev_id, PDO::PARAM_INT);
        $stmt->execute();
        echo 1;

    } elseif (isset($_POST['update'])) {
        // Update user account
        $useremail = $_SESSION['user-Email'];
        $up_name = $_POST['up_name'];
        $up_email = $_POST['up_email'];
        $up_password = $_POST['up_pwd'];

        // Input validation
        if (empty($up_name) || empty($up_email)) {
            header("location: account.php?error=emptyfields");
            exit();
        } elseif (!filter_var($up_email, FILTER_VALIDATE_EMAIL) && !preg_match("/^[a-zA-Z 0-9]*$/", $up_name)) {
            header("location: account.php?error=invalidEN&UN=".$up_name);
            exit();
        } elseif (!filter_var($up_email, FILTER_VALIDATE_EMAIL)) {
            header("location: account.php?error=invalidEN&UN=".$up_name);
            exit();
        } elseif (!preg_match("/^[a-zA-Z 0-9]*$/", $up_name)) {
            header("location: account.php?error=invalidName&E=".$up_email);
            exit();
        }

        // Verify current password
        $sql = "SELECT * FROM users WHERE user_email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $useremail);
        $stmt->execute();
        
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!password_verify($up_password, $row['user_pwd'])) {
                header("location: account.php?error=wrongpassword");
                exit();
            }

            if ($useremail == $up_email) {
                // Only update name
                $sql = "UPDATE users SET user_name = :name WHERE user_email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $up_name);
                $stmt->bindParam(':email', $useremail);
                $stmt->execute();
                
                $_SESSION['user-Name'] = $up_name;
                header("location: account.php?success=updated");
                exit();
            } else {
                // Check if new email exists
                $sql = "SELECT user_email FROM users WHERE user_email = :new_email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':new_email', $up_email);
                $stmt->execute();
                
                if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Update both name and email
                    $sql = "UPDATE users SET user_name = :name, user_email = :new_email WHERE user_email = :old_email";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':name', $up_name);
                    $stmt->bindParam(':new_email', $up_email);
                    $stmt->bindParam(':old_email', $useremail);
                    $stmt->execute();
                    
                    $_SESSION['user-Name'] = $up_name;
                    $_SESSION['user-Email'] = $up_email;
                    header("location: account.php?success=updated");
                    exit();
                } else {
                    header("location: account.php?error=emailtaken");
                    exit();
                }
            }
        } else {
            header("location: account.php?error=nouser1");
            exit();
        }

    } elseif (isset($_POST['dev_mode_set'])) {
        // Set device mode
        $dev_mode = $_POST['dev_mode'];
        $dev_id = $_POST['dev_id'];
        
        $sql = "UPDATE devices SET device_mode = :mode WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':mode', $dev_mode, PDO::PARAM_INT);
        $stmt->bindParam(':id', $dev_id, PDO::PARAM_INT);
        $stmt->execute();
        echo 1;

    } else {
        header("location: index.php");
        exit();
    }
} catch (PDOException $e) {
    if (isset($_POST['update'])) {
        header("location: account.php?error=sqlerror");
    } else {
        echo '<p class="alert alert-danger">Database Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    exit();
}
?>