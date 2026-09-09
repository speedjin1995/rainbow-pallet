<?php
session_start();
require_once '../../db_connect.php';
require_once 'helpers.php';

$username = $_SESSION['username'];
$id = optionalPost('id');
$transactionId = optionalPost('transactionId');
$transactionDate = optionalPost('transactionDate');
$supplier = optionalPost('supplier');
$lot = optionalPost('lot');
$bundle = optionalPost('bundle');
$remarks = optionalPost('remarks');
$details = isset($_POST['details']) ? json_decode($_POST['details'], true) : array();

if (!$transactionId || !$transactionDate || !$supplier || !$lot || !$bundle || empty($details)) {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
    exit;
}

$duplicateSql = "SELECT id FROM Sawn_Timber_Header WHERE transaction_id=? AND status='0'";
if ($id) {
    $duplicateSql .= " AND id<>?";
}

if ($duplicateStmt = $db->prepare($duplicateSql)) {
    if ($id) {
        $duplicateStmt->bind_param('ss', $transactionId, $id);
    } else {
        $duplicateStmt->bind_param('s', $transactionId);
    }
    $duplicateStmt->execute();
    $duplicateResult = $duplicateStmt->get_result();
    if ($duplicateResult->num_rows > 0) {
        echo json_encode(array("status" => "failed", "message" => "Transaction ID already exists"));
        $duplicateStmt->close();
        $db->close();
        exit;
    }
    $duplicateStmt->close();
}

$db->begin_transaction();

try {
    $actionByStmt = $db->prepare("SET @sawn_timber_action_by=?");
    $actionByStmt->bind_param('s', $username);
    $actionByStmt->execute();
    $actionByStmt->close();

    if ($id) {
        $stmt = $db->prepare("UPDATE Sawn_Timber_Header SET transaction_id=?, transaction_date=?, supplier=?, lot=?, bundle=?, remarks=?, modified_by=? WHERE id=?");
        $stmt->bind_param('ssssssss', $transactionId, $transactionDate, $supplier, $lot, $bundle, $remarks, $username, $id);
        $stmt->execute();
        $stmt->close();

        $deleteStmt = $db->prepare("DELETE FROM Sawn_Timber_Detail WHERE header_id=?");
        $deleteStmt->bind_param('s', $id);
        $deleteStmt->execute();
        $deleteStmt->close();
    } else {
        $stmt = $db->prepare("INSERT INTO Sawn_Timber_Header (transaction_id, transaction_date, supplier, lot, bundle, remarks, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssss', $transactionId, $transactionDate, $supplier, $lot, $bundle, $remarks, $username, $username);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
    }

    $detailStmt = $db->prepare("INSERT INTO Sawn_Timber_Detail (header_id, species, thick, width, length, pieces, tons) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($details as $detail) {
        $species = optionalJson($detail, 'species');
        $thick = optionalJson($detail, 'thick', '0');
        $width = optionalJson($detail, 'width', '0');
        $length = optionalJson($detail, 'length', '0');
        $pieces = optionalJson($detail, 'pieces', '0');

        if (!$species) {
            continue;
        }

        $tons = calculateSawnTimberTons($thick, $width, $length, $pieces);
        $detailStmt->bind_param('sssssss', $id, $species, $thick, $width, $length, $pieces, $tons);
        $detailStmt->execute();
    }

    $detailStmt->close();
    $db->commit();
    echo json_encode(array("status" => "success", "message" => "Saved Successfully!!", "id" => $id));
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(array("status" => "failed", "message" => $e->getMessage()));
}

$db->close();
?>
