<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Users</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icons/atte1.jpg">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/Users.css">
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script>
      $(window).on("load resize", function() {
        var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
        $('.tbl-header').css({'padding-right':scrollWidth});
      }).resize();
    </script>
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <section>
        <h1 class="slideInDown animated">Here are all the Users</h1>
        
        <!-- User table -->
        <div class="table-responsive slideInRight animated" style="max-height: 400px; overflow-y: auto;"> 
            <table class="table">
                <thead class="table-primary">
                    <tr>
                        <th>ID | Name</th>
                        <th>Serial Number</th>
                        <th>Gender</th>
                        <th>Finger ID</th>
                        <th>Date</th>
                        <th>Device</th>
                    </tr>
                </thead>
                <tbody class="table-secondary">
                    <?php
                    require 'connectDB.php';
                    
                    try {
                        $sql = "SELECT * FROM users WHERE add_fingerid = 0 ORDER BY id DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        
                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '
                                <tr>
                                    <td>'.htmlspecialchars($row['id']).' | '.htmlspecialchars($row['username']).'</td>
                                    <td>'.htmlspecialchars($row['serialnumber']).'</td>
                                    <td>'.htmlspecialchars($row['gender']).'</td>
                                    <td>'.htmlspecialchars($row['fingerprint_id']).'</td>
                                    <td>'.htmlspecialchars($row['user_date']).'</td>
                                    <td>'.htmlspecialchars($row['device_dep']).'</td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center">No users found</td></tr>';
                        }
                    } catch (PDOException $e) {
                        echo '<tr><td colspan="6" class="error">Error: '.htmlspecialchars($e->getMessage()).'</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>