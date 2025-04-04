<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
    header("location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icons/atte1.jpg">
    <link rel="stylesheet" type="text/css" href="css/manageusers.css">

    <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="js/bootbox.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script src="js/manage_users.js"></script>
    <script>
        $(window).on("load resize", function() {
            var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
            $('.tbl-header').css({'padding-right': scrollWidth});
        }).resize();

        $(document).ready(function() {
            // Initial load
            $.ajax({
                url: "manage_users_up.php"
            }).done(function(data) {
                $('#manage_users').html(data);
            });
            
            // Refresh every 5 seconds
            setInterval(function() {
                $.ajax({
                    url: "manage_users_up.php"
                }).done(function(data) {
                    $('#manage_users').html(data);
                });
            }, 5000);
        });
    </script>
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <h1 class="slideInDown animated">Add a new User or update his information<br>or remove him</h1>
        <div class="form-style-5 slideInDown animated">
            <form enctype="multipart/form-data">
                <fieldset>
                    <label for="Device"><b>User Department:</b></label>
                    <select name="dev_sel" id="dev_sel" style="color: #000;">
                        <option value="0">All Departments</option>
                        <?php
                            require 'connectDB.php';
                            try {
                                $sql = "SELECT * FROM devices ORDER BY device_name ASC";
                                $stmt = $conn->query($sql);
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo $row['device_dep']; ?></option>
                        <?php
                                }
                            } catch (PDOException $e) {
                                error_log("Database error: " . $e->getMessage());
                                echo '<p class="error">Database query failed</p>';
                            }
                        ?>
                    </select>
                </fieldset>
                <fieldset>
                    <legend><span class="number">1</span> User Fingerprint ID:</legend>
                    <label>Enter Fingerprint ID between 1 & 127:</label>
                    <input type="number" name="fingerid" id="fingerid" placeholder="User Fingerprint ID...">
                    <button type="button" name="fingerid_add" class="fingerid_add">Add Fingerprint ID</button>
                </fieldset>
                <div class="alert">
                    <label id="alert"></label>
                </div>
                <fieldset>
                    <legend><span class="number">2</span> User Info</legend>
                    <input type="hidden" name="finger_id" id="finger_id">
                    <input type="hidden" name="dev_id" id="dev_id">
                    <input type="text" name="name" id="name" placeholder="User Name...">
                    <input type="text" name="number" id="number" placeholder="Serial Number...">
                </fieldset>
                <label>
                    <input type="radio" name="gender" class="gender" value="Female"> Female
                    <input type="radio" name="gender" class="gender" value="Male" checked="checked"> Male
                </label>
                <div class="row">
                    <div class="col-lg-4">
                        <button type="button" name="user_add" class="user_add">Add</button>
                    </div>
                    <div class="col-lg-4">
                        <button type="button" name="user_upd" class="user_upd">Update</button>
                    </div>
                    <div class="col-lg-4">
                        <button type="button" name="user_rmo" class="user_rmo">Remove</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- User table -->
        <div class="section">
            <div class="slideInRight animated">
                <div id="manage_users"></div>
            </div>
        </div>
    </main>
</body>
</html>