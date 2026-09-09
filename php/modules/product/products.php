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

if (isset($_POST['productCode'])) {

    $productId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $productCode = empty($_POST["productCode"]) ? null : trim($_POST["productCode"]);
    $productName = empty($_POST["productName"]) ? null : trim($_POST["productName"]);
    $description = empty($_POST["description"]) ? null : trim($_POST["description"]);
    $varianceType = empty($_POST["varianceType"]) ? null : trim($_POST["varianceType"]);
    $high = empty($_POST["high"]) ? null : trim($_POST["high"]);
    $low = empty($_POST["low"]) ? null : trim($_POST["low"]);

    // Check for duplicate product_code (exclude current record when updating)
    $duplicateCheck = $db->prepare("SELECT id FROM Product WHERE product_code = ? AND status = 0" . (!empty($productId) ? " AND id != ?" : ""));
    if (!empty($productId)) {
        $duplicateCheck->bind_param('si', $productCode, $productId);
    } else {
        $duplicateCheck->bind_param('s', $productCode);
    }
    $duplicateCheck->execute();
    $duplicateCheck->store_result();

    if ($duplicateCheck->num_rows > 0) {
        echo json_encode(array("status" => "failed", "message" => "Product code already exists"));
        $duplicateCheck->close();
        $db->close();
        exit;
    }
    $duplicateCheck->close();

    try {
        $db->begin_transaction();
        if (!empty($productId)) {
            // Get current product_code and name before update
            $oldCode = null;
            $oldName = null;
            $stmt = $db->prepare('SELECT product_code, name FROM Product WHERE id = ?');
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('s', $productId);
            $stmt->execute();
            $stmt->bind_result($oldCode, $oldName);
            $stmt->fetch();
            $stmt->close();

            // Update existing record
            $stmt = $db->prepare("UPDATE Product SET product_code=?, name=?, description=?, variance=?, high=?, low=?, created_by=?, modified_by=? WHERE id=?");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('sssssssss', $productCode, $productName, $description, $varianceType, $high, $low, $username, $username, $productId);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            // Update related tables if product code is changed
            if ($oldCode !== null && $oldCode !== $productCode) {
                updateMasterDataCodeValue($db, $oldCode, $productCode, 'Product');
            }

            // Update related tables if product name is changed
            if ($oldName !== null && $oldName !== $productName) {
                updateMasterDataNameValue($db, $oldName, $productName, 'Product');
            }

            $stmt->close();
            $db->commit();
            $db->close();

            echo json_encode(['status' => 'success', 'message' => 'Updated Successfully!!']);
            exit();
        } else {
            $stmt = $db->prepare("INSERT INTO Product (product_code, name, description, variance, high, low, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception($db->error);
            }
            $stmt->bind_param('ssssssss', $productCode, $productName, $description, $varianceType, $high, $low, $username, $username);

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