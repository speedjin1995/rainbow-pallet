<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['id'])) {
    echo '<script type="text/javascript">location.href = "../../login.php";</script>';
} else {
    $username = $_SESSION["username"];
}
$uid = $_SESSION['id'];

try {
    if (!isset($_POST['locationCode'], $_POST['locationName'], $_POST['plant'])) {
        throw new Exception("Please fill in all the fields");
    }

    $id = empty($_POST['id']) ? null : trim($_POST['id']);
    $locationCode = empty($_POST['locationCode']) ? null : trim($_POST['locationCode']);
    $locationName = empty($_POST['locationName']) ? null : trim($_POST['locationName']);
    $plant = empty($_POST['plant']) ? null : trim($_POST['plant']);
    $weighingCount = empty($_POST['weighingCount']) ? '2' : trim($_POST['weighingCount']);

    // Check for duplicate location_code (exclude current record when updating)
    $duplicateCheck = $db->prepare("SELECT id FROM Location WHERE location_code = ? AND status = 0" . (!empty($id) ? " AND id != ?" : ""));
    if (!empty($id)) {
        $duplicateCheck->bind_param('si', $locationCode, $id);
    } else {
        $duplicateCheck->bind_param('s', $locationCode);
    }
    $duplicateCheck->execute();
    $duplicateCheck->store_result();

    if ($duplicateCheck->num_rows > 0) {
        echo json_encode(array("status" => "failed", "message" => "Location code already exists"));
        $duplicateCheck->close();
        $db->close();
        exit;
    }
    $duplicateCheck->close();

    $db->begin_transaction();

    if (!empty($id)) {
        $stmt = $db->prepare("UPDATE Location SET location_code=?, location_name=?, plant_id=?, weighing_count=? WHERE id=?");
        $stmt->bind_param('sssss', $locationCode, $locationName, $plant, $weighingCount, $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();
        $db->commit();
        $db->close();

        echo json_encode(["status" => "success", "message" => "Updated Successfully!!"]);
    } else {
        // Insert new Location table row
        $stmt = $db->prepare("INSERT INTO Location (location_code, location_name, plant_id, weighing_count) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $locationCode, $locationName, $plant, $weighingCount);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $newId = $stmt->insert_id;
        $stmt->close();

        // Insert new Port table row
        $port_stmt = $db->prepare("INSERT INTO Port (com_port, bits_per_second, data_bits, parity, stop_bits, indicator_id, indicator, weighbridge_id, weighind_id, created_by, modified_by) SELECT com_port, bits_per_second, data_bits, parity, stop_bits, indicator_id, indicator, weighbridge_id, ?, ?, modified_by FROM Port WHERE id = 1");
        $port_stmt->bind_param('ss', $newId, $username);
        if (!$port_stmt->execute()) {
            throw new Exception($port_stmt->error);
        }

        $portId = $port_stmt->insert_id;
        $port_stmt->close();

        // Update Location row to tie port_id
        $link_stmt = $db->prepare("UPDATE Location SET port_id=? WHERE id=?");
        $link_stmt->bind_param('ss', $portId, $newId);
        if (!$link_stmt->execute()) {
            throw new Exception($link_stmt->error);
        }

        $link_stmt->close();
        $db->commit();
        $db->close();

        echo json_encode(["status" => "success", "message" => "Added Successfully!!", "id" => $newId]);
    }
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(["status" => "failed", "message" => $e->getMessage()]);
}
?>
