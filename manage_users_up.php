<div class="table-responsive-sm" style="max-height: 870px; overflow-y: auto;"> 
  <table class="table table-striped table-hover">
    <thead class="table-primary">
      <tr>
        <th>Finger ID</th>
        <th>Name</th>
        <th>Gender</th>
        <th>S.No</th>
        <th>Date</th>
        <th>Department</th>
        <th>Dev.Status</th>
      </tr>
    </thead>
    <tbody class="table-secondary">
    <?php
      require 'connectDB.php';
      
      try {
          $sql = "SELECT * FROM users WHERE del_fingerid = 0 ORDER BY id DESC";
          $stmt = $conn->prepare($sql);
          $stmt->execute();
          
          if ($stmt->rowCount() > 0) {
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  $isSelected = $row['fingerprint_select'] == 1;
                  $fingerid = htmlspecialchars($row['fingerprint_id']);
                  $device_uid = htmlspecialchars($row['device_uid']);
                  $username = htmlspecialchars($row['username']);
                  $gender = htmlspecialchars($row['gender']);
                  $serialnumber = htmlspecialchars($row['serialnumber']);
                  $user_date = htmlspecialchars($row['user_date']);
                  $device_dep = ($row['device_dep'] == "0") ? "All" : htmlspecialchars($row['device_dep']);
                  $status = ($row['add_fingerid'] == "0") ? "Added" : "Free";
      ?>
                  <tr>
                      <td>
                          <?php if ($isSelected): ?>
                              <span class="text-success"><i class="glyphicon glyphicon-ok" title="The selected UID"></i></span>
                          <?php endif; ?>
                          <form>
                              <button type="button" class="btn btn-sm select_btn" 
                                      data-id="<?php echo $fingerid; ?>" 
                                      data-device="<?php echo $device_uid; ?>"
                                      title="Select this UID">
                                  <?php echo $fingerid; ?>
                              </button>
                          </form>
                      </td>
                      <td><?php echo $username; ?></td>
                      <td><?php echo $gender; ?></td>
                      <td><?php echo $serialnumber; ?></td>
                      <td><?php echo $user_date; ?></td>
                      <td><?php echo $device_dep; ?></td>
                      <td>
                          <span class="badge <?php echo $status == 'Added' ? 'bg-success' : 'bg-warning'; ?>">
                              <?php echo $status; ?>
                          </span>
                      </td>
                  </tr>
      <?php
              }
          } else {
              echo '<tr><td colspan="7" class="text-center">No users found</td></tr>';
          }
      } catch (PDOException $e) {
          echo '<tr><td colspan="7" class="error">Error: '.htmlspecialchars($e->getMessage()).'</td></tr>';
      }
    ?>
    </tbody>
  </table>
</div>