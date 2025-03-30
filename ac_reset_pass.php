<?php
if (isset($_POST['reset'])) {
    $selector = $_POST['selector'];
    $validator = $_POST['validator'];
    $pwd = $_POST['pwd'];
    $pwd_re = $_POST['pwd_re'];

    if (empty($pwd) || empty($pwd_re)) {
        header("location: new_pass.php?error=emptypass");
        exit();
    } elseif ($pwd !== $pwd_re) {
        header("location: new_pass.php?error=pwdnotsame");
        exit();
    }

    $currentDate = date("U");

    require 'connectDB.php'; // Make sure this uses PDO for PostgreSQL

    try {
        // Check if reset token is valid
        $sql = "SELECT * FROM pwd_reset WHERE pwd_reset_selector = :selector AND pwd_reset_expires >= :currentDate";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':selector', $selector);
        $stmt->bindParam(':currentDate', $currentDate);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tokenBin = hex2bin($validator);
            $tokenCheck = password_verify($tokenBin, $row['pwd_reset_token']);
            
            if ($tokenCheck == false) {
                header("location: new_pass.php?error=resubmit");
                exit();
            } elseif ($tokenCheck == true) {
                $tokenEmail = $row['pwd_reset_email'];

                // Verify admin exists
                $sql = "SELECT * FROM admin WHERE admin_email = :email";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':email', $tokenEmail);
                $stmt->execute();

                if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Update admin password
                    $newPwdHash = password_hash($pwd, PASSWORD_DEFAULT);
                    $sql = "UPDATE admin SET admin_pwd = :pwd WHERE admin_email = :email";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':pwd', $newPwdHash);
                    $stmt->bindParam(':email', $tokenEmail);
                    $stmt->execute();

                    // Delete the used reset token
                    $sql = "DELETE FROM pwd_reset WHERE pwd_reset_email = :email";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':email', $tokenEmail);
                    $stmt->execute();

                    header("location: login.php?pwd=pwdUpd");
                    exit();
                } else {
                    header("location: new_pass.php?error=nouser");
                    exit();
                }
            }
        } else {
            header("location: new_pass.php?error=resubmit");
            exit();
        }
    } catch (PDOException $e) {
        header("location: new_pass.php?error=sqlerror");
        exit();
    }
} else {
    header("location: index.php");
    exit();
}
?>