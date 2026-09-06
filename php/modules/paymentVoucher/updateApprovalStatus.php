<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['pvId'])) {
    $pvId = mysqli_real_escape_string($db, $_POST['pvId']);
    $remark = isset($_POST['remark']) ? mysqli_real_escape_string($db, $_POST['remark']) : '';
    $approvalStatus = 'Approved';
    $approvedBy = $_SESSION['id'];
    $approvedDate = date('Y-m-d H:i:s');

    $sql = "UPDATE Payment_Voucher SET approval_status = ?, approval_remarks = ?, approved_by = ?, approved_date = ? WHERE id = ?";

    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param('ssssi', $approvalStatus, $remark, $approvedBy, $approvedDate, $pvId);
        
        if ($stmt->execute()) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Payment voucher approved successfully"
            ));
        } else {
            echo json_encode(array(
                "status" => "failed",
                "message" => "Failed to approve payment voucher"
            ));
        }
        $stmt->close();
    } else {
        echo json_encode(array(
            "status" => "failed",
            "message" => "Database error"
        ));
    }
} else {
    echo json_encode(array(
        "status" => "failed",
        "message" => "Missing required parameters"
    ));
}
?>
