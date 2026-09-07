<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/functions.php';

if(!isset($_SESSION['id'])){
	echo '<script type="text/javascript">location.href = "../login.php";</script>'; 
} else{
	$username = $_SESSION["username"];
}
$id = $_SESSION['id'];

if (isset($_POST['customerCode'])) {

    $customerId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $customerCode = empty($_POST["customerCode"]) ? null : trim($_POST["customerCode"]);
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

    // Check for duplicate customer_code (exclude current record when updating)
    $duplicateCheck = $db->prepare("SELECT id FROM Customer WHERE customer_code = ? AND status = 0" . (!empty($customerId) ? " AND id != ?" : ""));
    if (!empty($customerId)) {
        $duplicateCheck->bind_param('si', $customerCode, $customerId);
    } else {
        $duplicateCheck->bind_param('s', $customerCode);
    }
    $duplicateCheck->execute();
    $duplicateCheck->store_result();

    if ($duplicateCheck->num_rows > 0) {
        echo json_encode(array("status" => "failed", "message" => "Customer code already exists"));
        $duplicateCheck->close();
        $db->close();
        exit;
    }
    $duplicateCheck->close();

    try {
        $db->begin_transaction();
        if (!empty($customerId)) {
            // Get current customer_code before update
            $oldCode = null;
            $stmt = $db->prepare('SELECT customer_code FROM Customer WHERE id = ?');
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('s', $customerId);
            $stmt->execute();
            $stmt->bind_result($oldCode);
            $stmt->fetch();
            $stmt->close();

            // Update existing record
            $update_stmt = $db->prepare("UPDATE Customer SET customer_code=?, company_reg_no=?, new_reg_no=?, name=?, address_line_1=?, address_line_2=?, address_line_3=?, phone_no=?, fax_no=?, contact_name=?, ic_no=?, tin_no=?, created_by=?, modified_by=? WHERE id=?");
            if (!$update_stmt) {
                throw new Exception($db->error);
            }
            $update_stmt->bind_param('sssssssssssssss', $customerCode, $companyRegNo, $newRegNo, $companyName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $contactName, $icNo, $tinNo, $username, $username, $customerId);

            if (!$update_stmt->execute()) {
                throw new Exception($update_stmt->error);
            }

            // Update related tables if customer code is changed
            if ($oldCode !== null && $oldCode !== $customerCode) {
                updateMasterDataCodeValue($db, $oldCode, $customerCode, 'Customer');
            }

            $update_stmt->close();
            $db->commit();
            $db->close();

            echo json_encode(['status' => 'success', 'message' => 'Updated Successfully!!']);
            exit();
        } else {
            $stmt = $db->prepare("INSERT INTO Customer (customer_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('ssssssssssssss', $customerCode, $companyRegNo, $newRegNo, $companyName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $contactName, $icNo, $tinNo, $username, $username);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }            
            
            $stmt->close();
            $db->commit();
            $db->close();

            echo json_encode(['status' => 'success', 'message' => 'Added Successfully!!']);
            exit();
        }
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status' => 'failed', 'message' => $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
}
?>
