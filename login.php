<?php
session_start();
if (isset($_SESSION['Admin-name'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Log In - Biometric Attendance</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icons/atte1.jpg">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/login.css">
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
      $(document).ready(function() {
        // Window resize handling
        $(window).on("load resize", function() {
            var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
            $('.tbl-header').css({'padding-right':scrollWidth});
        }).resize();
        
        // Form toggle animation
        $(document).on('click', '.message a', function(e) {
            e.preventDefault();
            $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
            $('h1').animate({height: "toggle", opacity: "toggle"}, "slow");
        });
        
        // Initially hide the reset form and reset heading
        $(".reset-form").hide();
        $("#reset").hide();
      });
    </script>
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <h1 class="slideInDown animated">Please, Login with the Admin E-mail and Password</h1>
    <h1 class="slideInDown animated" id="reset">Please, Enter your Email to send the reset password link</h1>
    
    <!-- Login Section -->
    <section>
        <div class="slideInDown animated">
            <div class="login-page">
                <div class="form">
                    <!-- Error/Success Messages -->
                    <?php  
                    if (isset($_GET['error'])) {
                        $messages = [
                            "invalidEmail" => "This E-mail is invalid!",
                            "sqlerror" => "There was a database error!",
                            "wrongpassword" => "Wrong password!",
                            "nouser" => "This E-mail does not exist!"
                        ];
                        
                        if (isset($messages[$_GET['error']])) {
                            echo '<div class="alert alert-danger alert-dismissible fade show">
                                    '.htmlspecialchars($messages[$_GET['error']]).'
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                  </div>';
                        }
                    }
                    
                    if (isset($_GET['reset']) && $_GET['reset'] == "success") {
                        echo '<div class="alert alert-success alert-dismissible fade show">
                                Check your E-mail for the reset link!
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                              </div>';
                    }
                    
                    if (isset($_GET['account']) && $_GET['account'] == "activated") {
                        echo '<div class="alert alert-success alert-dismissible fade show">
                                Your account is activated. Please Login.
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                              </div>';
                    }
                    
                    if (isset($_GET['active']) && $_GET['active'] == "success") {
                        echo '<div class="alert alert-success alert-dismissible fade show">
                                The activation link has been sent!
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                              </div>';
                    }
                    ?>
                    
                    <div class="alert1"></div>
                    
                    <!-- Reset Password Form -->
                    <form class="reset-form" action="reset_pass.php" method="post">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" class="form-control" name="email" placeholder="E-mail..." required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" name="reset_pass">Reset Password</button>
                        <p class="message">Remember your password? <a href="#">Log In</a></p>
                    </form>
                    
                    <!-- Login Form -->
                    <form class="login-form" action="ac_login.php" method="post">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                </div>
                                <input type="email" class="form-control" name="email" id="email" placeholder="E-mail..." required>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                </div>
                                <input type="password" class="form-control" name="pwd" id="pwd" placeholder="Password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" name="login" id="login">Login</button>
                        <p class="message">Forgot your Password? <a href="#">Reset your password</a></p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Bootstrap JS -->
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>