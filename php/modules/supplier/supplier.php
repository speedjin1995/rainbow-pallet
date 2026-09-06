<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['id'])) {
    echo '<script type="text/javascript">location.href = "../login.php";</script>';
} else {
    $username = $_SESSION["username"];
}
$id = $_SESSION['id'];

if (isset($_POST['supplierCode'])) {
    $supplierId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $supplierCode = empty($_POST["supplierCode"]) ? null : trim($_POST["supplierCode"]);
    $companyRegNo = empty($_POST["companyRegNo"]) ? null : trim($_POST["companyRegNo"]);
    $newRegNo = empty($_POST["newRegNo"]) ? null : trim($_POST["newRegNo"]);
    $companyName = empty($_POST["companyName"]) ? null : trim($_POST["companyName"]);
    $addressLine1 = empty($_POST["addressLine1"]) ? null : trim($_POST["addressLine1"]);
    $addressLine2 = empty($_POST["addressLine2"]) ? null : trim($_POST["addressLine2"]);
    $addressLine3 = empty($_POST["addressLine3"]) ? null : trim($_POST["addressLine3"]);
    $addressLine4 = empty($_POST["addressLine4"]) ? null : trim($_POST["addressLine4"]);
    $phoneNo = empty($_POST["phoneNo"]) ? null : trim($_POST["phoneNo"]);
    $faxNo = empty($_POST["faxNo"]) ? null : trim($_POST["faxNo"]);
    $contactName = empty($_POST["contactName"]) ? null : trim($_POST["contactName"]);
    $icNo = empty($_POST["icNo"]) ? null : trim($_POST["icNo"]);
    $tinNo = empty($_POST["tinNo"]) ? null : trim($_POST["tinNo"]);
    $paymentTerm = empty($_POST["paymentTerm"]) ? null : trim($_POST["paymentTerm"]);
    $paymentTermPeriod = empty($_POST["paymentTermPeriod"]) ? null : trim($_POST["paymentTermPeriod"]);
    $accountNo = empty($_POST["accountNo"]) ? null : trim($_POST["accountNo"]);

    // Check for duplicate supplier_code (exclude current record when updating)
    $duplicateCheck = $db->prepare("SELECT id FROM Supplier WHERE supplier_code = ? AND status = 0" . (!empty($supplierId) ? " AND id != ?" : ""));
    if (!empty($supplierId)) {
        $duplicateCheck->bind_param('si', $supplierCode, $supplierId);
    } else {
        $duplicateCheck->bind_param('s', $supplierCode);
    }
    $duplicateCheck->execute();
    $duplicateCheck->store_result();

    if ($duplicateCheck->num_rows > 0) {
        echo json_encode(array("status" => "failed", "message" => "Supplier code already exists"));
        $duplicateCheck->close();
        $db->close();
        exit;
    }
    $duplicateCheck->close();

    if (!empty($supplierId)) {
        if ($stmt = $db->prepare("UPDATE Supplier SET supplier_code=?, company_reg_no=?, new_reg_no=?, name=?, address_line_1=?, address_line_2=?, address_line_3=?, phone_no=?, fax_no=?, contact_name=?, ic_no=?, tin_no=?, payment_term=?, payment_term_period=?, account_no=?, created_by=?, modified_by=? WHERE id=?")) {
            $stmt->bind_param('ssssssssssssssssss', $supplierCode, $companyRegNo, $newRegNo, $companyName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $contactName, $icNo, $tinNo, $paymentTerm, $paymentTermPeriod, $accountNo, $username, $username, $supplierId);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            } else {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Updated Successfully!!"));
            }
        }
    } else {
        if ($stmt = $db->prepare("INSERT INTO Supplier (supplier_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, payment_term, payment_term_period, account_no, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param('sssssssssssssssss', $supplierCode, $companyRegNo, $newRegNo, $companyName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $contactName, $icNo, $tinNo, $paymentTerm, $paymentTermPeriod, $accountNo, $username, $username);

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
