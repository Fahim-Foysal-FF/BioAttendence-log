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
    <title>Users Logs</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icons/atte1.jpg">
    <link rel="stylesheet" type="text/css" href="css/userslog.css">

    <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="js/bootbox.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script src="js/user_log.js"></script>
    <script>
        $(window).on("load resize", function() {
            var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
            $('.tbl-header').css({'padding-right':scrollWidth});
        }).resize();

        $(document).ready(function(){
            // Initial load
            $.ajax({
                url: "user_log_up.php",
                type: 'POST',
                data: {'select_date': 1}
            }).done(function(data) {
                $('#userslog').html(data);
            });

            // Auto refresh every 5 seconds
            setInterval(function(){
                $.ajax({
                    url: "user_log_up.php",
                    type: 'POST',
                    data: {'select_date': 0}
                }).done(function(data) {
                    $('#userslog').html(data);
                });
            }, 5000);
        });
    </script>
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section class="container py-lg-5">
            <h1 class="slideInDown animated">Here are the Users daily logs</h1>
            
            <!-- Filter/Export Button -->
            <div class="form-style-5">
                <button type="button" data-toggle="modal" data-target="#Filter-export">Log Filter/Export to Excel</button>
            </div>

            <!-- Log Filter Modal -->
            <div class="modal fade bd-example-modal-lg" id="Filter-export" tabindex="-1" role="dialog" aria-labelledby="Filter/Export" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg animate" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">Filter Your User Log:</h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="Export_Excel.php" enctype="multipart/form-data">
                            <div class="modal-body">
                                <div class="container-fluid">
                                    <div class="row">
                                        <!-- Date Filter -->
                                        <div class="col-lg-6 col-sm-6">
                                            <div class="panel panel-primary">
                                                <div class="panel-heading">Filter By Date:</div>
                                                <div class="panel-body">
                                                    <label for="Start-Date"><b>Select from this Date:</b></label>
                                                    <input type="date" name="date_sel_start" id="date_sel_start">
                                                    <label for="End-Date"><b>To End of this Date:</b></label>
                                                    <input type="date" name="date_sel_end" id="date_sel_end">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Time Filter -->
                                        <div class="col-lg-6 col-sm-6">
                                            <div class="panel panel-primary">
                                                <div class="panel-heading">
                                                    Filter By:
                                                    <div class="time">
                                                        <input type="radio" id="radio-one" name="time_sel" class="time_sel" value="Time_in" checked>
                                                        <label for="radio-one">Time-in</label>
                                                        <input type="radio" id="radio-two" name="time_sel" class="time_sel" value="Time_out">
                                                        <label for="radio-two">Time-out</label>
                                                    </div>
                                                </div>
                                                <div class="panel-body">
                                                    <label for="Start-Time"><b>Select from this Time:</b></label>
                                                    <input type="time" name="time_sel_start" id="time_sel_start">
                                                    <label for="End-Time"><b>To End of this Time:</b></label>
                                                    <input type="time" name="time_sel_end" id="time_sel_end">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <!-- Fingerprint Filter -->
                                        <div class="col-lg-4 col-sm-12">
                                            <label for="Fingerprint"><b>Filter By Fingerprint ID:</b></label>
                                            <select class="fing_sel" name="fing_sel" id="fing_sel">
                                                <option value="0">All Users</option>
                                                <?php
                                                    require 'connectDB.php';
                                                    $sql = "SELECT fingerprint_id FROM users WHERE add_fingerid=0 ORDER BY fingerprint_id ASC";
                                                    $result = $conn->prepare($sql);
                                                    if (!$result->execute()) {
                                                        echo '<p class="error">SQL Error</p>';
                                                    } else {
                                                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                            <option value="<?php echo $row['fingerprint_id']; ?>">
                                                                <?php echo $row['fingerprint_id']; ?>
                                                            </option>
                                                <?php
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <!-- Department Filter -->
                                        <div class="col-lg-4 col-sm-12">
                                            <label for="Device"><b>Filter By Device department:</b></label>
                                            <select class="dev_sel" name="dev_sel" id="dev_sel">
                                                <option value="0">All Departments</option>
                                                <?php
                                                    require 'connectDB.php';
                                                    $sql = "SELECT * FROM devices ORDER BY device_dep ASC";
                                                    $result = $conn->prepare($sql);
                                                    if (!$result->execute()) {
                                                        echo '<p class="error">SQL Error</p>';
                                                    } else {
                                                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                                ?>
                                                            <option value="<?php echo $row['id']; ?>">
                                                                <?php echo $row['device_dep']; ?>
                                                            </option>
                                                <?php
                                                        }
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <!-- Export Button -->
                                        <div class="col-lg-4 col-sm-12">
                                            <label for="Fingerprint"><b>Export to Excel:</b></label>
                                            <input type="submit" name="To_Excel" value="Export" class="btn btn-primary">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" name="user_log" id="user_log" class="btn btn-success">Filter</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Log Filter Modal -->

            <!-- Users Log Table -->
            <div class="slideInRight animated">
                <div id="userslog"></div>
            </div>
        </section>
    </main>
</body>
</html>