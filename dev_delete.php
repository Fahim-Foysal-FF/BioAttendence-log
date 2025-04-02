<?php
session_start();
require 'connectDB.php';

if (isset($_POST['id'])) {
    try {
        $sql = "DELETE FROM devices WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_POST['id']]);
        
        echo json_encode(['status' => 'success', 'message' => 'Device deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting device: '.$e->getMessage()]);
    }
    exit();
}
?>