<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/functions.php';

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

    // Check for duplicate plant_code (exclude current record when updating)
    $duplicateCheck = $db->prepare("SELECT id FROM Plant WHERE plant_code = ? AND status = 0" . (!empty($plantId) ? " AND id != ?" : ""));
    if (!empty($plantId)) {
        $duplicateCheck->bind_param('si', $plantCode, $plantId);
    } else {
        $duplicateCheck->bind_param('s', $plantCode);
    }
    $duplicateCheck->execute();
    $duplicateCheck->store_result();

    if ($duplicateCheck->num_rows > 0) {
        echo json_encode(array("status" => "failed", "message" => "Plant code already exists"));
        $duplicateCheck->close();
        $db->close();
        exit;
    }
    $duplicateCheck->close();

    try {
        $db->begin_transaction();
        if (!empty($plantId)) {
            // Get current plant_code and name before update
            $oldCode = null;
            $oldName = null;
            $stmt = $db->prepare('SELECT plant_code, name FROM Plant WHERE id = ?');
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('s', $plantId);
            $stmt->execute();
            $stmt->bind_result($oldCode, $oldName);
            $stmt->fetch();
            $stmt->close();

            // Update existing record
            $stmt = $db->prepare("UPDATE Plant SET plant_code=?, name=?, address_line_1=?, address_line_2=?, address_line_3=?, phone_no=?, fax_no=?, created_by=?, modified_by=? WHERE id=?");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('ssssssssss', $plantCode, $plantName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $username, $username, $plantId);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            // Update related tables if plant code is changed
            if ($oldCode !== null && $oldCode !== $plantCode) {
                updateMasterDataCodeValue($db, $oldCode, $plantCode, 'Plant');
            }

            // Update related tables if plant name is changed
            if ($oldName !== null && $oldName !== $plantName) {
                updateMasterDataNameValue($db, $oldName, $plantName, 'Plant');
            }

            $stmt->close();
            $db->commit();
            $db->close();

            echo json_encode(['status' => 'success', 'message' => 'Updated Successfully!!']);
            exit();
        } else {
            $stmt = $db->prepare("INSERT INTO Plant (plant_code, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('sssssssss', $plantCode, $plantName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $username, $username);

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
