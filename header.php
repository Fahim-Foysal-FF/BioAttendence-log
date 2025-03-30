<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biometric Attendance System</title>
    
    <!-- Combined CSS resources -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/header.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="icons/atte1.jpg">
</head>
<body>
<header>
    <div class="header">
        <div class="logo">
            <a href="index.php">Biometric Attendance</a>
        </div>
    </div>

    <!-- Notification Messages -->
    <?php  
    if (isset($_GET['error'])) {
        $error = htmlspecialchars($_GET['error']);
        if ($error == "wrongpasswordup") {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(function() {
                        const upInfo1 = document.querySelector(".up_info1");
                        upInfo1.style.display = "block";
                        upInfo1.textContent = "The password is wrong!";
                        $("#admin-account").modal("show");
                    }, 500);
                    setTimeout(function() {
                        document.querySelector(".up_info1").style.display = "none";
                    }, 3000);
                });
            </script>';
        }
    } 
    
    if (isset($_GET['success'])) {
        $success = htmlspecialchars($_GET['success']);
        if ($success == "updated") {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(function() {
                        const upInfo2 = document.querySelector(".up_info2");
                        upInfo2.style.display = "block";
                        upInfo2.textContent = "Your Account has been updated";
                    }, 500);
                    setTimeout(function() {
                        document.querySelector(".up_info2").style.display = "none";
                    }, 3000);
                });
            </script>';
        }
    }
    
    if (isset($_GET['login'])) {
        $login = htmlspecialchars($_GET['login']);
        if ($login == "success") {
            echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(function() {
                        const upInfo2 = document.querySelector(".up_info2");
                        upInfo2.style.display = "block";
                        upInfo2.textContent = "You successfully logged in";
                    }, 500);
                    setTimeout(function() {
                        document.querySelector(".up_info2").style.display = "none";
                    }, 4000);
                });
            </script>';
        }
    }
    ?>
    
    <!-- Navigation Bar -->
    <div class="topnav" id="myTopnav">
        <a href="index.php"><i class="fa fa-home"></i> Dashboard</a>
        <a href="ManageUsers.php"><i class="fa fa-users"></i> Manage Users</a>
        <a href="UsersLog.php"><i class="fa fa-history"></i> Users Log</a>
        <a href="devices.php"><i class="fa fa-desktop"></i> Devices</a>
        <?php  
        if (isset($_SESSION['Admin-name'])) {
            $adminName = htmlspecialchars($_SESSION['Admin-name']);
            echo '<a href="#" data-toggle="modal" data-target="#admin-account">
                    <i class="fa fa-user-circle"></i> '.$adminName.'
                  </a>';
            echo '<a href="logout.php"><i class="fa fa-sign-out"></i> Log Out</a>';
        } else {
            echo '<a href="login.php"><i class="fa fa-sign-in"></i> Log In</a>';
        }
        ?>
        <a href="javascript:void(0);" class="icon" onclick="navFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>
    
    <!-- Notification Containers -->
    <div class="up_info1 alert-danger"></div>
    <div class="up_info2 alert-success"></div>
</header>

<!-- Responsive Navigation Script -->
<script>
    function navFunction() {
        const x = document.getElementById("myTopnav");
        x.classList.toggle("responsive");
    }
    
    // Close notifications when clicking on them
    document.addEventListener('DOMContentLoaded', function() {
        const notifications = document.querySelectorAll('.up_info1, .up_info2');
        notifications.forEach(notification => {
            notification.addEventListener('click', function() {
                this.style.display = 'none';
            });
        });
    });
</script>

<!-- Account Update Modal -->
<div class="modal fade" id="admin-account" tabindex="-1" role="dialog" aria-labelledby="adminAccountModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Update Your Account Info</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="ac_update.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="up_name"><b>Admin Name:</b></label>
                        <input type="text" class="form-control" name="up_name" placeholder="Enter your Name" 
                               value="<?php echo isset($_SESSION['Admin-name']) ? htmlspecialchars($_SESSION['Admin-name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="up_email"><b>Admin E-mail:</b></label>
                        <input type="email" class="form-control" name="up_email" placeholder="Enter your E-mail" 
                               value="<?php echo isset($_SESSION['Admin-email']) ? htmlspecialchars($_SESSION['Admin-email']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="up_pwd"><b>Password</b></label>
                        <input type="password" class="form-control" name="up_pwd" placeholder="Enter your Password" required>
                        <small class="form-text text-muted">Enter your current password to confirm changes</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="update" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>