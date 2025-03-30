<?php
session_start();
require 'connectDB.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['reset_pass'])) {
    header("Location: index.php");
    exit();
}

// Validate email input
$userEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
    header("Location: login.php?error=invalidemail");
    exit();
}

try {
    // Generate tokens
    $selector = bin2hex(random_bytes(8));
    $token = random_bytes(32);
    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
    $expires = date("U") + 1800; // 30 minutes expiration

    // Check if user exists
    $sql = "SELECT id FROM admin WHERE admin_email = :email LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $userEmail);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        header("Location: login.php?error=nouser");
        exit();
    }

    // Begin transaction
    $conn->beginTransaction();

    // Delete any existing reset tokens for this email
    $sql = "DELETE FROM pwd_reset WHERE pwd_reset_email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $userEmail);
    $stmt->execute();

    // Insert new reset token
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

    // Generate reset URL (use your actual domain)
    $resetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                "://$_SERVER[HTTP_HOST]/new_pass.php?selector=$selector&validator=" . bin2hex($token);

    // Prepare email
    $to = $userEmail;
    $subject = 'Password Reset Request';
    $message = '
    <html>
    <head>
        <title>Password Reset</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { 
                display: inline-block; padding: 10px 20px; 
                background-color: #007bff; color: white; 
                text-decoration: none; border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Password Reset Request</h2>
            <p>We received a request to reset your password. If you didn\'t make this request, please ignore this email.</p>
            <p>To reset your password, click the button below:</p>
            <p><a href="'.$resetUrl.'" class="button">Reset Password</a></p>
            <p>Or copy and paste this link into your browser:<br>
            <small>'.$resetUrl.'</small></p>
            <p>This link will expire in 30 minutes.</p>
            <hr>
            <p><small>If you\'re having trouble clicking the button, copy and paste the URL above into your web browser.</small></p>
        </div>
    </body>
    </html>';

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Biometric System <noreply@".$_SERVER['HTTP_HOST'].">\r\n";
    $headers .= "Reply-To: support@".$_SERVER['HTTP_HOST']."\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    // Send email
    if (mail($to, $subject, $message, $headers)) {
        header("Location: login.php?reset=success");
    } else {
        throw new Exception("Failed to send email");
    }

} catch (PDOException $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Password reset error: " . $e->getMessage());
    header("Location: login.php?error=sqlerror");
    exit();
} catch (Exception $e) {
    error_log("Mail error: " . $e->getMessage());
    header("Location: login.php?reset=failed");
    exit();
}