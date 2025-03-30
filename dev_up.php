<?php 
session_start();
?>
<div class="table-responsive">          
    <table class="table">
        <thead>
            <tr>
                <th>De.Name</th>
                <th>De.Department</th>
                <th>De.UID</th>
                <th>De.Date</th>
                <th>De.Mode</th>
                <th>De.Config</th>
            </tr>
        </thead>
        <tbody>
            <?php  
                require 'connectDB.php';
                
                try {
                    $sql = "SELECT * FROM devices ORDER BY id DESC";
                    $stmt = $conn->query($sql);
                    
                    echo '<form action="" method="POST" enctype="multipart/form-data">';
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $radio1 = ($row["device_mode"] == 0) ? "checked" : "";
                        $radio2 = ($row["device_mode"] == 1) ? "checked" : "";

                        $de_mode = '<div class="mode_select">
                            <input type="radio" id="'.$row["id"].'-one" name="'.$row["id"].'" class="mode_sel" data-id="'.$row["id"].'" value="0" '.$radio1.'/>
                            <label for="'.$row["id"].'-one">Enrollment</label>
                            <input type="radio" id="'.$row["id"].'-two" name="'.$row["id"].'" class="mode_sel" data-id="'.$row["id"].'" value="1" '.$radio2.'/>
                            <label for="'.$row["id"].'-two">Attendance</label>
                        </div>';

                        echo '<tr>
                            <td>'.htmlspecialchars($row["device_name"]).'</td>
                            <td>'.htmlspecialchars($row["device_dep"]).'</td>
                            <td>
                                <button type="button" class="dev_uid_up btn btn-warning" id="del_'.$row["id"].'" data-id="'.$row["id"].'" title="Update this device Token">
                                    <span class="glyphicon glyphicon-refresh"></span>
                                </button>
                                '.htmlspecialchars($row["device_uid"]).'
                            </td>
                            <td>'.htmlspecialchars($row["device_date"]).'</td>
                            <td>'.$de_mode.'</td>
                            <td>
                                <button type="button" class="dev_del btn btn-danger" id="del_'.$row["id"].'" data-id="'.$row["id"].'" title="Delete this device">
                                    <span class="glyphicon glyphicon-trash"></span>
                                </button>
                            </td>
                        </tr>';
                    }
                    
                    echo '</form>';
                    
                } catch (PDOException $e) {
                    echo '<p class="error">Database Error: '.htmlspecialchars($e->getMessage()).'</p>';
                }
            ?>
        </tbody>
    </table>
</div>