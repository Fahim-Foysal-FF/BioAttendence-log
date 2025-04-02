<?php
// No whitespace before this tag!
session_start();

require 'connectDB.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Authentication logic
    
    if ($authenticated) {
        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Management</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icons/atte1.jpg">

    <!-- Combined CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/Users.css">
    
    <!-- JavaScript -->
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(window).on("load resize", function() {
        var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
        $('.tbl-header').css({'padding-right':scrollWidth});
    }).resize();

    // Auto-refresh every 30 seconds
    setTimeout(function(){
        location.reload();
    }, 30000);
    </script>
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <section class="container py-4">
        <h1 class="slideInDown animated">Registered Users</h1>
        
        <!-- User table with enhanced features -->
        <div class="card shadow slideInRight animated">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Users List</h5>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" id="exportBtn">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="refreshBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 500px;">
                    <table class="table table-hover mb-0">
                        <thead class="bg-dark text-white">
                            <tr>
                                <th>ID | Name</th>
                                <th>Serial Number</th>
                                <th>Gender</th>
                                <th>Finger ID</th>
                                <th>Registration Date</th>
                                <th>Department</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require 'connectDB.php';
                            
                            try {
                                $sql = "SELECT * FROM users WHERE add_fingerid = 0 ORDER BY id DESC";
                                $stmt = $conn->query($sql);
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $status = ($row['fingerprint_id'] > 0) ? 
                                        '<span class="badge badge-success">Registered</span>' : 
                                        '<span class="badge badge-warning">Pending</span>';
                                    
                                    echo '<tr>
                                        <td>'.htmlspecialchars($row['id']).' | '.htmlspecialchars($row['username']).'</td>
                                        <td>'.htmlspecialchars($row['serialnumber']).'</td>
                                        <td>'.htmlspecialchars($row['gender']).'</td>
                                        <td>'.htmlspecialchars($row['fingerprint_id']).'</td>
                                        <td>'.htmlspecialchars($row['user_date']).'</td>
                                        <td>'.htmlspecialchars($row['device_dep']).'</td>
                                        <td>'.$status.'</td>
                                    </tr>';
                                }
                                
                                if ($stmt->rowCount() == 0) {
                                    echo '<tr><td colspan="7" class="text-center">No users found</td></tr>';
                                }
                                
                            } catch (PDOException $e) {
                                echo '<tr><td colspan="7" class="text-center text-danger">Error loading users: '.htmlspecialchars($e->getMessage()).'</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card-footer text-muted">
                <small>Last updated: <?php echo date('Y-m-d H:i:s'); ?></small>
            </div>
        </div>
    </section>
</main>

<script>
// Export functionality
$('#exportBtn').click(function() {
    window.location.href = 'export_users.php?type=csv';
});

// Refresh functionality
$('#refreshBtn').click(function() {
    location.reload();
});
</script>

</body>
</html>