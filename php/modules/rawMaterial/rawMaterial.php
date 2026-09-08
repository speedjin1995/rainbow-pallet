<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/functions.php';

if (!isset($_SESSION['id'])) {
    echo '<script type="text/javascript">location.href = "../login.php";</script>';
} else {
    $username = $_SESSION["username"];
}
$id = $_SESSION['id'];

if (isset($_POST['rawMatCode'])) {

    $rawMatId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $rawMatCode = empty($_POST["rawMatCode"]) ? null : trim($_POST["rawMatCode"]);
    $rawMatName = empty($_POST["rawMatName"]) ? null : trim($_POST["rawMatName"]);
    $rawMatType = empty($_POST["rawMatType"]) ? null : trim($_POST["rawMatType"]);
    $description = empty($_POST["description"]) ? null : trim($_POST["description"]);
    $varianceType = empty($_POST["varianceType"]) ? null : trim($_POST["varianceType"]);
    $high = empty($_POST["high"]) ? null : trim($_POST["high"]);
    $low = empty($_POST["low"]) ? null : trim($_POST["low"]);

    // Check for duplicate raw_mat_code (exclude current record when updating)
    $duplicateCheck = $db->prepare("SELECT id FROM Raw_Mat WHERE raw_mat_code = ? AND status = 0" . (!empty($rawMatId) ? " AND id != ?" : ""));
    if (!empty($rawMatId)) {
        $duplicateCheck->bind_param('si', $rawMatCode, $rawMatId);
    } else {
        $duplicateCheck->bind_param('s', $rawMatCode);
    }
    $duplicateCheck->execute();
    $duplicateCheck->store_result();

    if ($duplicateCheck->num_rows > 0) {
        echo json_encode(array("status" => "failed", "message" => "Raw material code already exists"));
        $duplicateCheck->close();
        $db->close();
        exit;
    }
    $duplicateCheck->close();

    try {
        $db->begin_transaction();
        if (!empty($rawMatId)) {
            // Get current raw_mat_code and name before update
            $oldCode = null;
            $oldName = null;
            $stmt = $db->prepare('SELECT raw_mat_code, name FROM Raw_Mat WHERE id = ?');
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('s', $rawMatId);
            $stmt->execute();
            $stmt->bind_result($oldCode, $oldName);
            $stmt->fetch();
            $stmt->close();

            // Update existing record
            $stmt = $db->prepare("UPDATE Raw_Mat SET raw_mat_code=?, name=?, raw_mat_type=?, description=?, variance=?, high=?, low=?, created_by=?, modified_by=? WHERE id=?");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('ssssssssss', $rawMatCode, $rawMatName, $rawMatType, $description, $varianceType, $high, $low, $username, $username, $rawMatId);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            // Update related tables if raw material code is changed
            if ($oldCode !== null && $oldCode !== $rawMatCode) {
                updateMasterDataCodeValue($db, $oldCode, $rawMatCode, 'Raw Material');
            }

            // Update related tables if raw material name is changed
            if ($oldName !== null && $oldName !== $rawMatName) {
                updateMasterDataNameValue($db, $oldName, $rawMatName, 'Raw Material');
            }

            $stmt->close();
            $db->commit();
            $db->close();

            echo json_encode(['status' => 'success', 'message' => 'Updated Successfully!!']);
            exit();
        } else {
            $stmt = $db->prepare("INSERT INTO Raw_Mat (raw_mat_code, name, raw_mat_type, description, variance, high, low, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('sssssssss', $rawMatCode, $rawMatName, $rawMatType, $description, $varianceType, $high, $low, $username, $username);

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
