<?php
session_start();
require 'connectDB.php';

// Redirect if not logged in
if (!isset($_SESSION['Admin-name'])) {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        try {
            $sql = "UPDATE users SET del_fingerid = 1 WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $_SESSION['success'] = "User marked for deletion successfully";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_fingerprint'])) {
        $finger_id = $_POST['finger_id'];
        $device_uid = $_POST['device_uid'];
        
        try {
            // Reset all selections first
            $sql = "UPDATE users SET fingerprint_select = 0";
            $conn->exec($sql);
            
            // Select the new fingerprint
            $sql = "UPDATE users SET fingerprint_select = 1 WHERE fingerprint_id = :finger_id AND device_uid = :device_uid";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':finger_id', $finger_id);
            $stmt->bindParam(':device_uid', $device_uid);
            $stmt->execute();
            
            $_SESSION['success'] = "Fingerprint selected successfully";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error selecting fingerprint: " . $e->getMessage();
        }
    }
    
    header("Location: manageusers.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="icons/atte1.jpg">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/manage-users.css">
    
    <!-- JavaScript -->
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<main>
    <section class="container py-4">
        <h1 class="slideInDown animated">Manage Users</h1>
        
        <!-- Display messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">User Management</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive-sm" style="max-height: 870px;"> 
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th>Finger ID</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>S.No</th>
                                <th>Date</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $sql = "SELECT * FROM users WHERE del_fingerid = 0 ORDER BY id DESC";
                                $stmt = $conn->query($sql);
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $fingerStatus = ($row['add_fingerid'] == "0") ? 
                                        '<span class="badge badge-success">Added</span>' : 
                                        '<span class="badge badge-warning">Free</span>';
                                    
                                    $department = ($row['device_dep'] == "0") ? "All" : htmlspecialchars($row['device_dep']);
                                    $selectIcon = ($row['fingerprint_select'] == 1) ? 
                                        '<span class="text-success"><i class="fas fa-check-circle" title="Selected UID"></i></span>' : 
                                        '';
                            ?>
                                    <tr>
                                        <td>
                                            <?php echo $selectIcon; ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="finger_id" value="<?php echo htmlspecialchars($row['fingerprint_id']); ?>">
                                                <input type="hidden" name="device_uid" value="<?php echo htmlspecialchars($row['device_uid']); ?>">
                                                <button type="submit" name="update_fingerprint" class="btn btn-sm btn-outline-primary" 
                                                        title="Select this UID" onclick="return confirm('Are you sure you want to select this fingerprint?')">
                                                    <?php echo htmlspecialchars($row['fingerprint_id']); ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($row['serialnumber']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_date']); ?></td>
                                        <td><?php echo $department; ?></td>
                                        <td><?php echo $fingerStatus; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-info" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-danger" 
                                                            title="Delete User" onclick="return confirm('Are you sure you want to delete this user?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                            <?php
                                }
                                
                                if ($stmt->rowCount() == 0) {
                                    echo '<tr><td colspan="8" class="text-center">No users found</td></tr>';
                                }
                            } catch (PDOException $e) {
                                echo '<tr><td colspan="8" class="text-center text-danger">Error loading users: '.htmlspecialchars($e->getMessage()).'</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted">
                <small>Total Users: <?php echo $stmt->rowCount(); ?> | Last updated: <?php echo date('Y-m-d H:i:s'); ?></small>
            </div>
        </div>
    </section>
</main>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="editUserFormContainer">
        <!-- Content loaded via AJAX -->
      </div>
    </div>
  </div>
</div>

<script>
// AJAX loading for edit modal
$(document).ready(function() {
    $('.edit-btn').click(function(e) {
        e.preventDefault();
        const userId = $(this).data('userid');
        const url = $(this).attr('href');
        
        $('#editUserModal').modal('show');
        $('#editUserFormContainer').load(url, function(response, status, xhr) {
            if (status == "error") {
                $(this).html('<div class="alert alert-danger">Error loading form</div>');
            }
        });
    });
});
</script>
</body>
</html>