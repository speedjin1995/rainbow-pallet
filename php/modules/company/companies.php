<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['id'])) {
    echo '<script type="text/javascript">location.href = "../login.php";</script>';
} else {
    $username = $_SESSION["username"];
}

if (isset($_POST['companyCode'], $_POST['companyName'])) {

    $companyId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $companyCode = empty($_POST["companyCode"]) ? null : trim($_POST["companyCode"]);
    $companyRegNo = empty($_POST["companyRegNo"]) ? null : trim($_POST["companyRegNo"]);
    $companyNewRegNo = empty($_POST["companyNewRegNo"]) ? null : trim($_POST["companyNewRegNo"]);
    $companyName = empty($_POST["companyName"]) ? null : trim($_POST["companyName"]);
    $addressLine1 = empty($_POST["addressLine1"]) ? null : trim($_POST["addressLine1"]);
    $addressLine2 = empty($_POST["addressLine2"]) ? null : trim($_POST["addressLine2"]);
    $addressLine3 = empty($_POST["addressLine3"]) ? null : trim($_POST["addressLine3"]);
    $phoneNo = empty($_POST["phoneNo"]) ? null : trim($_POST["phoneNo"]);
    $faxNo = empty($_POST["faxNo"]) ? null : trim($_POST["faxNo"]);
    $tinNo = empty($_POST["tinNo"]) ? null : trim($_POST["tinNo"]);
    $mobileNo = empty($_POST["mobileNo"]) ? null : trim($_POST["mobileNo"]);

    if (!empty($companyId)) {
        if ($stmt = $db->prepare("UPDATE Company SET company_code=?, company_reg_no=?, new_reg_no=?, name=?, address_line_1=?, address_line_2=?, address_line_3=?, phone_no=?, fax_no=?, tin_no=?, mobile_no=?, modified_by=? WHERE id=?")) {
            $stmt->bind_param('sssssssssssss', $companyCode, $companyRegNo, $companyNewRegNo, $companyName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $tinNo, $mobileNo, $username, $companyId);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            } else {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Updated Successfully!!"));
            }
        }
    } else {
        if ($stmt = $db->prepare("INSERT INTO Company (company_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, tin_no, mobile_no, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param('ssssssssssss', $companyCode, $companyRegNo, $companyNewRegNo, $companyName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $tinNo, $mobileNo, $username);

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
