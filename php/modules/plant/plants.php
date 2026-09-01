<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['id'])) {
    echo '<script type="text/javascript">location.href = "../login.php";</script>';
} else {
    $username = $_SESSION["username"];
}

if (isset($_POST['plantCode'], $_POST['plantName'])) {

    $plantId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $plantCode = empty($_POST["plantCode"]) ? null : trim($_POST["plantCode"]);
    $plantName = empty($_POST["plantName"]) ? null : trim($_POST["plantName"]);
    $addressLine1 = empty($_POST["addressLine1"]) ? null : trim($_POST["addressLine1"]);
    $addressLine2 = empty($_POST["addressLine2"]) ? null : trim($_POST["addressLine2"]);
    $addressLine3 = empty($_POST["addressLine3"]) ? null : trim($_POST["addressLine3"]);
    $phoneNo = empty($_POST["phoneNo"]) ? null : trim($_POST["phoneNo"]);
    $faxNo = empty($_POST["faxNo"]) ? null : trim($_POST["faxNo"]);

    if (!empty($plantId)) {
        if ($stmt = $db->prepare("UPDATE Plant SET plant_code=?, name=?, address_line_1=?, address_line_2=?, address_line_3=?, phone_no=?, fax_no=?, created_by=?, modified_by=? WHERE id=?")) {
            $stmt->bind_param('ssssssssss', $plantCode, $plantName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $username, $username, $plantId);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            } else {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Updated Successfully!!"));
            }
        }
    } else {
        if ($stmt = $db->prepare("INSERT INTO Plant (plant_code, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param('sssssssss', $plantCode, $plantName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $username, $username);

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
