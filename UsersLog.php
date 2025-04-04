<?php
session_start();
require 'connectDB.php';

// Redirect if not logged in
if (!isset($_SESSION['Admin-name'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Users Logs</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icons/atte1.jpg">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/userslog.css">
    
    <!-- JavaScript (jQuery FIRST) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    
    <!-- Your custom JS -->
    <script src="js/user_log.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <section class="container py-5">
        <h1 class="slideInDown animated">User Attendance Logs</h1>
        
        <!-- Filter/Export Button -->
        <div class="text-right mb-4">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#Filter-export">
                <i class="fas fa-filter"></i> Filter/Export Logs
            </button>
        </div>

        <!-- Log Filter Modal -->
        <div class="modal fade" id="Filter-export" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Filter and Export Logs</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="Export_Excel.php" enctype="multipart/form-data" id="export-form">
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-secondary text-white">
                                                Filter By Date Range
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="date_sel_start">Start Date</label>
                                                    <input type="date" class="form-control" name="date_sel_start" id="date_sel_start" value="<?php echo date('Y-m-d'); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="date_sel_end">End Date</label>
                                                    <input type="date" class="form-control" name="date_sel_end" id="date_sel_end" value="<?php echo date('Y-m-d'); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header bg-secondary text-white">
                                                Filter By Time Range
                                                <div class="float-right">
                                                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                        <label class="btn btn-sm btn-light active">
                                                            <input type="radio" name="time_sel" value="Time_in" checked> Time-in
                                                        </label>
                                                        <label class="btn btn-sm btn-light">
                                                            <input type="radio" name="time_sel" value="Time_out"> Time-out
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="time_sel_start">Start Time</label>
                                                    <input type="time" class="form-control" name="time_sel_start" id="time_sel_start">
                                                </div>
                                                <div class="form-group">
                                                    <label for="time_sel_end">End Time</label>
                                                    <input type="time" class="form-control" name="time_sel_end" id="time_sel_end">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fing_sel">Filter By Fingerprint ID</label>
                                            <select class="form-control" name="fing_sel" id="fing_sel">
                                                <option value="0">All Users</option>
                                                <?php
                                                try {
                                                    $sql = "SELECT fingerprint_id FROM users WHERE add_fingerid = 0 ORDER BY fingerprint_id ASC";
                                                    $stmt = $conn->query($sql);
                                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        echo '<option value="'.htmlspecialchars($row['fingerprint_id']).'">'.htmlspecialchars($row['fingerprint_id']).'</option>';
                                                    }
                                                } catch (PDOException $e) {
                                                    echo '<option value="">Error loading fingerprints</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="dev_sel">Filter By Department</label>
                                            <select class="form-control" name="dev_sel" id="dev_sel">
                                                <option value="0">All Departments</option>
                                                <?php
                                                try {
                                                    $sql = "SELECT DISTINCT device_dep FROM devices ORDER BY device_dep ASC";
                                                    $stmt = $conn->query($sql);
                                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                        echo '<option value="'.htmlspecialchars($row['device_dep']).'">'.htmlspecialchars($row['device_dep']).'</option>';
                                                    }
                                                } catch (PDOException $e) {
                                                    echo '<option value="">Error loading departments</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" id="export_excel" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                            <button type="button" id="apply_filter" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Logs Table Container -->
        <div class="card shadow slideInRight animated">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Attendance Records</h5>
                <small class="float-right">Auto-refreshing every 10 seconds</small>
            </div>
            <div class="card-body p-0">
                <div id="userslog" class="table-responsive"></div>
            </div>
            <div class="card-footer text-muted text-center">
                <small>Last updated: <?php echo date('Y-m-d H:i:s'); ?></small>
            </div>
        </div>
    </section>
</main>
</body>
</html>