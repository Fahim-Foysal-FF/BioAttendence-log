<?php
if (isset($_POST['login'])) {
    require 'connectDB.php'; // Make sure this file connects to PostgreSQL

    $Usermail = $_POST['email'];
    $Userpass = $_POST['pwd'];

    if (empty($Usermail) || empty($Userpass)) {
        header("location: login.php?error=emptyfields");
        exit();
    } else if (!filter_var($Usermail, FILTER_VALIDATE_EMAIL)) {
        header("location: login.php?error=invalidEmail");
        exit();
    } else {
        $sql = "SELECT * FROM admin WHERE admin_email = :email";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            header("location: login.php?error=sqlerror");
            exit();
        } else {
            $stmt->bindParam(':email', $Usermail);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $pwdCheck = password_verify($Userpass, $row['admin_pwd']);
                if ($pwdCheck == false) {
                    header("location: login.php?error=wrongpassword");
                    exit();
                } else if ($pwdCheck == true) {
                    session_start();
                    $_SESSION['Admin-name'] = $row['admin_name'];
                    $_SESSION['Admin-email'] = $row['admin_email'];
                    header("location: index.php?login=success");
                    exit();
                }
            } else {
                header("location: login.php?error=nouser");
                exit();
            }
        }
    }
} else {
    header("location: login.php");
    exit();
}
?>