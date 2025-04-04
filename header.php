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
    <title>Biometric Attendance</title>
    
    <!-- CSS Resources -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/header.css">
</head>
<body>
<header>
    <div class="header">
        <div class="logo">
            <a href="index.php">Biometric Attendance</a>
        </div>
    </div>

    <!-- Notification Messages -->
    <div class="up_info1 alert-danger"></div>
    <div class="up_info2 alert-success"></div>

    <!-- Navigation -->
    <div class="topnav" id="myTopnav">
        <a href="index.php">Users</a>
        <a href="ManageUsers.php">Manage Users</a>
        <a href="UsersLog.php">Users Log</a>
        <a href="devices.php">Devices</a>
        <?php if (isset($_SESSION['Admin-name'])): ?>
            <a href="#" data-toggle="modal" data-target="#admin-account"><?php echo htmlspecialchars($_SESSION['Admin-name']); ?></a>
            <a href="logout.php">Log Out</a>
        <?php else: ?>
            <a href="login.php">Log In</a>
        <?php endif; ?>
        <a href="javascript:void(0);" class="icon" onclick="navFunction()">
            <i class="fa fa-bars"></i>
        </a>
    </div>
</header>

<!-- Account Update Modal -->
<div class="modal fade" id="admin-account" tabindex="-1" role="dialog" aria-labelledby="Admin Update" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Update Your Account Info:</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="ac_update.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="up_name"><b>Admin Name:</b></label>
                        <input type="text" class="form-control" name="up_name" placeholder="Enter your Name..." 
                               value="<?php echo isset($_SESSION['Admin-name']) ? htmlspecialchars($_SESSION['Admin-name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="up_email"><b>Admin E-mail:</b></label>
                        <input type="email" class="form-control" name="up_email" placeholder="Enter your E-mail..." 
                               value="<?php echo isset($_SESSION['Admin-email']) ? htmlspecialchars($_SESSION['Admin-email']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="up_pwd"><b>Password</b></label>
                        <input type="password" class="form-control" name="up_pwd" placeholder="Enter your Password..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update" class="btn btn-success">Save changes</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    // Mobile navigation toggle
    function navFunction() {
        var x = document.getElementById("myTopnav");
        x.classList.toggle("responsive");
    }

    // Notification handling
    <?php if (isset($_GET['error']) && $_GET['error'] == "wrongpasswordup"): ?>
        setTimeout(function() {
            document.querySelector('.up_info1').style.display = 'block';
            document.querySelector('.up_info1').textContent = "The password is wrong!!";
            $('#admin-account').modal('show');
        }, 500);
        setTimeout(function() {
            document.querySelector('.up_info1').style.display = 'none';
        }, 3000);
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == "updated"): ?>
        setTimeout(function() {
            document.querySelector('.up_info2').style.display = 'block';
            document.querySelector('.up_info2').textContent = "Your Account has been updated";
        }, 500);
        setTimeout(function() {
            document.querySelector('.up_info2').style.display = 'none';
        }, 3000);
    <?php endif; ?>

    <?php if (isset($_GET['login']) && $_GET['login'] == "success"): ?>
        setTimeout(function() {
            document.querySelector('.up_info2').style.display = 'block';
            document.querySelector('.up_info2').textContent = "You successfully logged in";
        }, 500);
        setTimeout(function() {
            document.querySelector('.up_info2').style.display = 'none';
        }, 4000);
    <?php endif; ?>
</script>

<!-- jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</body>
</html>