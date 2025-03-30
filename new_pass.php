<?php
session_start();
require 'connectDB.php';

// Redirect if already logged in
if (isset($_SESSION['Admin-name'])) {
    header("Location: index.php");
    exit();
}

// Validate token selector and validator
$validRequest = false;
if (!empty($_GET['selector']) && !empty($_GET['validator'])) {
    $selector = $_GET['selector'];
    $validator = $_GET['validator'];
    
    // Check if tokens are hexadecimal
    if (ctype_xdigit($selector) && ctype_xdigit($validator)) {
        $validRequest = true;
        
        // Verify token exists and is not expired
        try {
            $currentDate = date("U");
            $sql = "SELECT * FROM pwd_reset WHERE pwd_reset_selector = :selector AND pwd_reset_expires >= :currentDate";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':selector', $selector);
            $stmt->bindParam(':currentDate', $currentDate);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $validRequest = false;
                $_SESSION['error'] = "Invalid or expired password reset request";
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $_SESSION['error'] = "Database error occurred";
            header("Location: login.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Password Reset</title>
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
    
    <script>
    $(document).ready(function() {
        // Form validation
        $('form').submit(function(e) {
            const pwd = $('input[name="pwd"]').val();
            const pwd_re = $('input[name="pwd_re"]').val();
            
            if (pwd.length < 8) {
                $('.alert1').html('<div class="alert alert-danger">Password must be at least 8 characters</div>');
                e.preventDefault();
                return false;
            }
            
            if (pwd !== pwd_re) {
                $('.alert1').html('<div class="alert alert-danger">Passwords do not match</div>');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Show/hide password
        $('.toggle-password').click(function() {
            const input = $($(this).attr('toggle'));
            if (input.attr('type') == 'password') {
                input.attr('type', 'text');
                $(this).html('<i class="fas fa-eye-slash"></i>');
            } else {
                input.attr('type', 'password');
                $(this).html('<i class="fas fa-eye"></i>');
            }
        });
    });
    </script>
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <section class="container py-5">
        <?php if ($validRequest): ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow slideInDown animated">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">Reset Your Password</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert1 mb-3"></div>
                            
                            <form action="ac_reset_pass.php" method="post">
                                <input type="hidden" name="selector" value="<?php echo htmlspecialchars($selector); ?>">
                                <input type="hidden" name="validator" value="<?php echo htmlspecialchars($validator); ?>">
                                
                                <div class="form-group">
                                    <label for="pwd">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="pwd" id="pwd" 
                                               placeholder="Enter new password (min 8 characters)" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password" 
                                                    type="button" toggle="#pwd">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="pwd_re">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="pwd_re" id="pwd_re" 
                                               placeholder="Repeat new password" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary toggle-password" 
                                                    type="button" toggle="#pwd_re">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <button type="submit" name="reset" class="btn btn-primary btn-block">
                                    <i class="fas fa-key"></i> Reset Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="alert alert-danger text-center">
                        <h4>Invalid Password Reset Link</h4>
                        <p>The password reset link is invalid or has expired.</p>
                        <a href="forgot_password.php" class="btn btn-primary">
                            Request New Reset Link
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>