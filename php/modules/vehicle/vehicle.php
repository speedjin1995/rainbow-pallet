<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['id'])) {
    echo '<script type="text/javascript">location.href = "../login.php";</script>';
} else {
    $username = $_SESSION["username"];
}
$id = $_SESSION['id'];

if (isset($_POST['vehicleNo'])) {

    $vehicleId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $vehicleNo = empty($_POST["vehicleNo"]) ? null : trim($_POST["vehicleNo"]);
    $vehicleWeight = empty($_POST["vehicleWeight"]) ? 0 : trim($_POST["vehicleWeight"]);
    $transporter = empty($_POST["transporter"]) ? null : trim($_POST["transporter"]);
    $transporterCode = empty($_POST["transporterCode"]) ? null : trim($_POST["transporterCode"]);
    $customer = empty($_POST["customer"]) ? null : trim($_POST["customer"]);
    $customerCode = empty($_POST["customerCode"]) ? null : trim($_POST["customerCode"]);
    $supplier = empty($_POST["supplier"]) ? null : trim($_POST["supplier"]);
    $supplierCode = empty($_POST["supplierCode"]) ? null : trim($_POST["supplierCode"]);

    if (!empty($vehicleId)) {
        if ($stmt = $db->prepare("UPDATE Vehicle SET veh_number=?, vehicle_weight=?, transporter_code=?, transporter_name=?, customer_code=?, customer_name=?, supplier_code=?, supplier_name=?, created_by=?, modified_by=? WHERE id=?")) {
            $stmt->bind_param('sssssssssss', $vehicleNo, $vehicleWeight, $transporterCode, $transporter, $customerCode, $customer, $supplierCode, $supplier, $username, $username, $vehicleId);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            } else {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Updated Successfully!!"));
            }
        }
    } else {
        if ($stmt = $db->prepare("INSERT INTO Vehicle (veh_number, vehicle_weight, transporter_code, transporter_name, customer_code, customer_name, supplier_code, supplier_name, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param('ssssssssss', $vehicleNo, $vehicleWeight, $transporterCode, $transporter, $customerCode, $customer, $supplierCode, $supplier, $username, $username);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            } else {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Added Successfully!!"));
            }
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
}
?>
