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
    <title>Manage Devices</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icons/atte1.jpg">
    <link rel="stylesheet" type="text/css" href="css/devices.css">

    <!-- Combined and optimized JavaScript includes -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/bootbox.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/dev_config.js"></script>
    
    <script>
        $(window).on("load resize", function() {
            var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
            $('.tbl-header').css({'padding-right':scrollWidth});
        }).resize();
        
        $(document).ready(function(){
            function loadDevices() {
                $.ajax({
                    url: "dev_up.php",
                    type: 'POST',
                    data: { dev_up: 1 }
                }).done(function(data) {
                    $('#devices').html(data);
                }).fail(function(jqXHR, textStatus) {
                    $('.alert_dev').html('<div class="alert alert-danger">Failed to load devices: ' + textStatus + '</div>');
                });
            }
            
            // Initial load
            loadDevices();
            
            // Refresh every 30 seconds
            setInterval(loadDevices, 30000);
        });
    </script>
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <h1 class="slideInDown animated">Device Management</h1>

    <section class="container py-lg-5">
        <div class="alert_dev"></div>
        
        <div class="row">
            <div class="col-lg-12 mt-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Your Devices</h5>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#new-device">
                            <i class="fas fa-plus"></i> New Device
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="devices" class="table-responsive"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Device Modal -->
        <div class="modal fade" id="new-device" tabindex="-1" role="dialog" aria-labelledby="newDeviceModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Device</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="deviceForm">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="dev_name">Device Name:</label>
                                <input type="text" class="form-control" name="dev_name" id="dev_name" placeholder="Device Name" required>
                            </div>
                            <div class="form-group">
                                <label for="dev_dep">Device Department:</label>
                                <input type="text" class="form-control" name="dev_dep" id="dev_dep" placeholder="Device Department" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create Device</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
</body>
</html>