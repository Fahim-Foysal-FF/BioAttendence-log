<?php 
session_start();
require('connectDB.php');

if (isset($_POST['update'])) {
    try {
        $useremail = $_SESSION['Admin-email'];
        $up_name = $_POST['up_name'];
        $up_email = $_POST['up_email'];
        $up_password = $_POST['up_pwd'];

        // Input validation
        if (empty($up_name) || empty($up_email)) {
            header("location: index.php?error=emptyfields");
            exit();
        }
        elseif (!filter_var($up_email, FILTER_VALIDATE_EMAIL) && !preg_match("/^[a-zA-Z 0-9]*$/", $up_name)) {
            header("location: index.php?error=invalidEN&UN=".$up_name);
            exit();
        }
        elseif (!filter_var($up_email, FILTER_VALIDATE_EMAIL)) {
            header("location: index.php?error=invalidEN&UN=".$up_name);
            exit();
        }
        elseif (!preg_match("/^[a-zA-Z 0-9]*$/", $up_name)) {
            header("location: index.php?error=invalidName&E=".$up_email);
            exit();
        }

        // Verify current user
        $sql = "SELECT * FROM admin WHERE admin_email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $useremail);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Verify password
            $pwdCheck = password_verify($up_password, $row['admin_pwd']);
            if ($pwdCheck == false) {
                header("location: index.php?error=wrongpasswordup");
                exit();
            }

            // Email unchanged - update name only
            if ($useremail == $up_email) {
                $sql = "UPDATE admin SET admin_name = :name WHERE admin_email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':name', $up_name);
                $stmt->bindParam(':email', $useremail);
                $stmt->execute();
                
                $_SESSION['Admin-name'] = $up_name;
                header("location: index.php?success=updated");
                exit();
            }
            // Email changed - check if new email is available
            else {
                $sql = "SELECT admin_email FROM admin WHERE admin_email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':email', $up_email);
                $stmt->execute();

                if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                    $sql = "UPDATE admin SET admin_name = :name, admin_email = :new_email WHERE admin_email = :old_email";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':name', $up_name);
                    $stmt->bindParam(':new_email', $up_email);
                    $stmt->bindParam(':old_email', $useremail);
                    $stmt->execute();
                    
                    $_SESSION['Admin-name'] = $up_name;
                    $_SESSION['Admin-email'] = $up_email;
                    header("location: index.php?success=updated");
                    exit();
                }
                else {
                    header("location: index.php?error=nouser2");
                    exit();
                }
            }
        }
        else {
            header("location: index.php?error=nouser1");
            exit();
        }
    }
    catch (PDOException $e) {
        header("location: index.php?error=sqlerror");
        exit();
    }
}
else {
    header("location: index.php");
    exit();
}
?>