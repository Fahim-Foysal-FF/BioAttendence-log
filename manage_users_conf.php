<?php
session_start();
require 'connectDB.php';

// Redirect if not logged in
if (!isset($_SESSION['Admin-name'])) {
    header("Location: login.php");
    exit();
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        // Add new user logic
        $username = trim($_POST['username']);
        $serialnumber = trim($_POST['serialnumber']);
        $gender = $_POST['gender'];
        $department = $_POST['department'];
        
        try {
            $sql = "INSERT INTO users (username, serialnumber, gender, device_dep, user_date) 
                    VALUES (:username, :serialnumber, :gender, :department, CURRENT_DATE)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':serialnumber', $serialnumber);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':department', $department);
            $stmt->execute();
            
            $_SESSION['success'] = "User added successfully";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding user: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_user'])) {
        // Delete user logic
        $user_id = $_POST['user_id'];
        
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $_SESSION['success'] = "User deleted successfully";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting user: " . $e->getMessage();
        }
    }
    
    header("Location: ManageUsers.php");
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
    <script src="js/manage-users.js"></script>
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
        
        <div class="row">
            <!-- Add User Form -->
            <div class="col-md-4 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Add New User</h5>
                    </div>
                    <div class="card-body">
                        <form id="addUserForm" method="POST">
                            <div class="form-group">
                                <label for="username">Full Name</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="serialnumber">Serial Number</label>
                                <input type="text" class="form-control" id="serialnumber" name="serialnumber" required>
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select class="form-control" id="department" name="department" required>
                                    <?php
                                    try {
                                        $sql = "SELECT DISTINCT device_dep FROM devices";
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
                            <button type="submit" name="add_user" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus"></i> Add User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Users List -->
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Existing Users</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Serial Number</th>
                                        <th>Gender</th>
                                        <th>Department</th>
                                        <th>Date Added</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $sql = "SELECT * FROM users ORDER BY id DESC";
                                        $stmt = $conn->query($sql);
                                        
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo '<tr>
                                                <td>'.htmlspecialchars($row['id']).'</td>
                                                <td>'.htmlspecialchars($row['username']).'</td>
                                                <td>'.htmlspecialchars($row['serialnumber']).'</td>
                                                <td>'.htmlspecialchars($row['gender']).'</td>
                                                <td>'.htmlspecialchars($row['device_dep']).'</td>
                                                <td>'.htmlspecialchars($row['user_date']).'</td>
                                                <td>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="user_id" value="'.htmlspecialchars($row['id']).'">
                                                        <button type="submit" name="delete_user" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </td>
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
                </div>
            </div>
        </div>
    </section>
</main>

</body>
</html>