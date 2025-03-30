<div class="table-responsive-sm" style="max-height: 870px;"> 
  <table class="table table-striped table-hover">
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
      require 'connectDB.php';
      
      try {
          $sql = "SELECT * FROM users WHERE del_fingerid = 0 ORDER BY id DESC";
          $stmt = $conn->prepare($sql);
          $stmt->execute();
          
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
                  <button type="button" class="btn btn-sm btn-outline-primary select_btn" 
                          data-id="<?php echo htmlspecialchars($row['fingerprint_id']); ?>"
                          data-device="<?php echo htmlspecialchars($row['device_uid']); ?>"
                          title="Select this UID">
                    <?php echo htmlspecialchars($row['fingerprint_id']); ?>
                  </button>
                </td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                <td><?php echo htmlspecialchars($row['serialnumber']); ?></td>
                <td><?php echo htmlspecialchars($row['user_date']); ?></td>
                <td><?php echo $department; ?></td>
                <td><?php echo $fingerStatus; ?></td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-info edit-btn" 
                            data-userid="<?php echo $row['id']; ?>"
                            title="Edit User">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger delete-btn" 
                            data-userid="<?php echo $row['id']; ?>"
                            title="Delete User">
                      <i class="fas fa-trash-alt"></i>
                    </button>
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

<script>
$(document).ready(function() {
    // Handle select button click
    $('.select_btn').click(function() {
        const fingerId = $(this).data('id');
        const deviceUid = $(this).data('device');
        
        // Show confirmation dialog
        if (confirm(`Are you sure you want to select Finger ID ${fingerId}?`)) {
            $.ajax({
                url: 'update_fingerprint.php',
                type: 'POST',
                data: {
                    finger_id: fingerId,
                    device_uid: deviceUid,
                    action: 'select'
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error selecting fingerprint: ' + xhr.responseText);
                }
            });
        }
    });
    
    // Handle edit button click
    $('.edit-btn').click(function() {
        const userId = $(this).data('userid');
        // Load edit modal with user data
        $('#editUserModal').modal('show');
        // You would implement AJAX to load user data here
    });
    
    // Handle delete button click
    $('.delete-btn').click(function() {
        const userId = $(this).data('userid');
        
        if (confirm('Are you sure you want to delete this user?')) {
            $.ajax({
                url: 'delete_user.php',
                type: 'POST',
                data: { user_id: userId },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error deleting user: ' + xhr.responseText);
                }
            });
        }
    });
});
</script>

<!-- Edit User Modal (would be included in your main file) -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Form would be loaded via AJAX -->
        <div id="editUserFormContainer"></div>
      </div>
    </div>
  </div>
</div>