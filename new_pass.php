<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['Admin-name'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Password Reset - Biometric Attendance</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icons/atte1.jpg">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/login.css">
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Window resize handling
        $(window).on("load resize", function() {
            var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
            $('.tbl-header').css({'padding-right': scrollWidth});
        }).resize();
        
        // Password visibility toggle
        $('.password-toggle').click(function() {
            const input = $(this).siblings('input');
            const icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
    </script>
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <?php 
    if (!empty($_GET['selector']) && !empty($_GET['validator'])) {
        echo '<h1 class="slideInDown animated">Please, Insert your new Password</h1>';
    }
    ?>
    
    <!-- Password Reset Section -->
    <section class="pic_date_sel">
        <div class="slideInDown animated">
            <div class="login-page">
                <?php  
                if (empty($_GET['selector']) || empty($_GET['validator'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show">
                            <strong>Error!</strong> Could not validate your request, please retry.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
                } elseif (!empty($_GET['selector']) && !empty($_GET['validator'])) {
                    $selector = $_GET['selector'];
                    $validator = $_GET['validator'];
                    
                    if (ctype_xdigit($selector) && ctype_xdigit($validator)) {
                        echo '<div class="alert alert-info alert-dismissible fade show">
                                <strong>Note:</strong> Please create a strong new password.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
                ?>
                        <div class="form">
                            <form class="login-form" action="ac_reset_pass.php" method="post">
                                <input type="hidden" name="selector" value="<?php echo htmlspecialchars($selector, ENT_QUOTES); ?>">
                                <input type="hidden" name="validator" value="<?php echo htmlspecialchars($validator, ENT_QUOTES); ?>">
                                
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" name="pwd" placeholder="Enter a new Password..." 
                                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                                           title="Must contain at least 8 characters, including uppercase, lowercase and numbers" required>
                                    <button class="btn btn-outline-secondary password-toggle" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" name="pwd_re" placeholder="Repeat new Password..." required>
                                    <button class="btn btn-outline-secondary password-toggle" type="button">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                
                                <div class="password-requirements">
                                    <p>Password must contain:</p>
                                    <ul>
                                        <li>At least 8 characters</li>
                                        <li>One uppercase letter</li>
                                        <li>One lowercase letter</li>
                                        <li>One number</li>
                                    </ul>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100" name="reset">Reset Password</button>
                            </form>
                        </div>
                <?php
                    } else {
                        echo '<div class="alert alert-danger alert-dismissible fade show">
                                <strong>Error!</strong> Invalid token format.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
                    }
                }
                ?>
            </div>
        </div>
    </section>
</main>
</body>
</html>