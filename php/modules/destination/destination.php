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

if (isset($_POST['destinationCode'])) {

    $destinationId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $destinationCode = empty($_POST["destinationCode"]) ? null : trim($_POST["destinationCode"]);
    $destinationName = empty($_POST["destinationName"]) ? null : trim($_POST["destinationName"]);
    $description = empty($_POST["description"]) ? null : trim($_POST["description"]);

    // Check for duplicate destination_code (exclude current record when updating)
    $duplicateCheck = $db->prepare("SELECT id FROM Destination WHERE destination_code = ? AND status = 0" . (!empty($destinationId) ? " AND id != ?" : ""));
    if (!empty($destinationId)) {
        $duplicateCheck->bind_param('si', $destinationCode, $destinationId);
    } else {
        $duplicateCheck->bind_param('s', $destinationCode);
    }
    $duplicateCheck->execute();
    $duplicateCheck->store_result();

    if ($duplicateCheck->num_rows > 0) {
        echo json_encode(array("status" => "failed", "message" => "Destination code already exists"));
        $duplicateCheck->close();
        $db->close();
        exit;
    }
    $duplicateCheck->close();

    try {
        $db->begin_transaction();
        if (!empty($destinationId)) {
            // Get current destination_code and name before update
            $oldCode = null;
            $oldName = null;
            $stmt = $db->prepare('SELECT destination_code, name FROM Destination WHERE id = ?');
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('s', $destinationId);
            $stmt->execute();
            $stmt->bind_result($oldCode, $oldName);
            $stmt->fetch();
            $stmt->close();

            // Update existing record
            $stmt = $db->prepare("UPDATE Destination SET destination_code=?, name=?, description=?, created_by=?, modified_by=? WHERE id=?");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('ssssss', $destinationCode, $destinationName, $description, $username, $username, $destinationId);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            // Update related tables if destination code is changed
            if ($oldCode !== null && $oldCode !== $destinationCode) {
                updateMasterDataCodeValue($db, $oldCode, $destinationCode, 'Destination');
            }

            // Update related tables if destination name is changed
            if ($oldName !== null && $oldName !== $destinationName) {
                updateMasterDataNameValue($db, $oldName, $destinationName, 'Destination');
            }

            $stmt->close();
            $db->commit();
            $db->close();

            echo json_encode(['status' => 'success', 'message' => 'Updated Successfully!!']);
            exit();
        } else {
            $stmt = $db->prepare("INSERT INTO Destination (destination_code, name, description, created_by, modified_by) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('sssss', $destinationCode, $destinationName, $description, $username, $username);

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
