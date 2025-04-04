<?php
if (isset($_POST['reset_pass'])) {
    try {
        // Generate secure tokens
        $selector = bin2hex(random_bytes(8));
        $token = random_bytes(32);
        $url = "https://domain-name.com/new_pass.php?selector=".$selector."&validator=".bin2hex($token);
        $expires = date("U") + 1800; // 30 minutes expiration

        require 'connectDB.php'; // Ensure this uses PDO for PostgreSQL

        $userEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
            header("Location: login.php?error=invalidemail");
            exit();
        }

        // Begin transaction
        $conn->beginTransaction();

        // Delete any existing reset requests
        $sql = "DELETE FROM pwd_reset WHERE pwd_reset_email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $userEmail);
        $stmt->execute();

        // Verify admin exists
        $sql = "SELECT admin_email FROM admin WHERE admin_email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $userEmail);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Insert new reset request
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            $sql = "INSERT INTO pwd_reset (pwd_reset_email, pwd_reset_selector, pwd_reset_token, pwd_reset_expires) 
                    VALUES (:email, :selector, :token, :expires)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $userEmail);
            $stmt->bindParam(':selector', $selector);
            $stmt->bindParam(':token', $hashedToken);
            $stmt->bindParam(':expires', $expires);
            $stmt->execute();

            // Commit transaction
            $conn->commit();

            // Prepare email
            $to = $userEmail;
            $subject = 'Password Reset Request - Biometric Attendance System';
            
            $message = '<html>
                        <body style="font-family: Arial, sans-serif;">
                            <h2>Password Reset Request</h2>
                            <p>We received a password reset request for your account.</p>
                            <p>If you did not request this, please ignore this email.</p>
                            <p>To reset your password, click the link below (valid for 30 minutes):</p>
                            <p><a href="'.$url.'" style="background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;">Reset Password</a></p>
                            <p>Or copy this link to your browser:<br>'.$url.'</p>
                            <hr>
                            <p><small>This is an automated message. Please do not reply.</small></p>
                        </body>
                        </html>';

            $headers = "From: Biometric System <support@domain-name.com>\r\n";
            $headers .= "Reply-To: support@domain-name.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "X-Mailer: PHP/".phpversion();

            // Send email using PHPMailer or similar is recommended for production
            if (mail($to, $subject, $message, $headers)) {
                header("Location: login.php?reset=success");
            } else {
                throw new Exception("Failed to send email");
            }
        } else {
            header("Location: login.php?error=nouser");
        }
    } catch (PDOException $e) {
        // Rollback transaction on error
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Password reset error: " . $e->getMessage());
        header("Location: login.php?error=sqlerror");
    } catch (Exception $e) {
        error_log("Password reset error: " . $e->getMessage());
        header("Location: login.php?reset=failed");
    } finally {
        if (isset($conn)) {
            $conn = null; // Close connection
        }
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>