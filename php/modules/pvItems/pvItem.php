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

if (isset($_POST['itemCode'])) {

    $pvItemId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $itemCode = empty($_POST["itemCode"]) ? null : trim($_POST["itemCode"]);
    $itemName = empty($_POST["itemName"]) ? null : trim($_POST["itemName"]);

    // Check for duplicate item_code (exclude current record when updating)
    $duplicateCheck = $db->prepare("SELECT id FROM Pv_Items WHERE item_code = ? AND status = 0" . (!empty($pvItemId) ? " AND id != ?" : ""));
    if (!empty($pvItemId)) {
        $duplicateCheck->bind_param('si', $itemCode, $pvItemId);
    } else {
        $duplicateCheck->bind_param('s', $itemCode);
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
        if (!empty($pvItemId)) {
            // Update existing record
            $stmt = $db->prepare("UPDATE Pv_Items SET item_code=?, item_name=?, modified_by=? WHERE id=?");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('ssss', $itemCode, $itemName, $username, $pvItemId);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();
            $db->commit();
            $db->close();

            echo json_encode(['status' => 'success', 'message' => 'Updated Successfully!!']);
            exit();
        } else {
            $stmt = $db->prepare("INSERT INTO Pv_Items (item_code, item_name, created_by) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('sss', $itemCode, $itemName, $username);

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
