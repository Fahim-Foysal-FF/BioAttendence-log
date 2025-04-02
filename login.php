<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['Admin-name'])) {
    header("Location: index.php");
    exit();
}

// Database connection
require 'connectDB.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['pwd'];

    // Validate inputs
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        try {
            // Prepare SQL statement
            $sql = "SELECT * FROM admin WHERE admin_email = :email LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            // Check if user exists
            if ($stmt->rowCount() == 1) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($password, $admin['admin_pwd'])) {
                    // Set session variables
                    $_SESSION['Admin-name'] = $admin['admin_name'];
                    $_SESSION['Admin-email'] = $admin['admin_email'];
                    
                    // Redirect to dashboard
                    header("Location: index.php?login=success");
                    exit();
                } else {
                    $error = "Invalid email or password";
                }
            } else {
                $error = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Biometric Attendance</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icons/atte1.jpg">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/login.css">
    
    
    <!-- JavaScript -->
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="login-container">
    <div class="login-card shadow-lg">
        <div class="card-header text-center">
            <img src="icons/atte1.jpg" alt="Logo" class="logo mb-3">
            <h3>Admin Login</h3>
        </div>
        
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-warning alert-dismissible fade show">
                    <?php 
                    switch ($_GET['error']) {
                        case 'emptyfields':
                            echo "Please fill in all fields";
                            break;
                        case 'invalidEmail':
                            echo "Invalid email format";
                            break;
                        case 'wrongpassword':
                            echo "Incorrect password";
                            break;
                        case 'nouser':
                            echo "No account found with this email";
                            break;
                        default:
                            echo "Login error occurred";
                    }
                    ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        </div>
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" name="pwd" placeholder="Password" required>
                    </div>
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="text-muted">Forgot password?</a>
                </div>
            </form>
        </div>
        
        <div class="card-footer text-center text-muted">
            <small>&copy; <?php echo date('Y'); ?> Biometric Attendance System</small>
        </div>
    </div>
</div>

<script>
// Focus on email field when page loads
$(document).ready(function() {
    $('input[name="email"]').focus();
    
    // Prevent form resubmission on refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
});
</script>
</body>
</html>