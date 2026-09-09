<?php
session_start();
require_once '../../db_connect.php';
require_once 'helpers.php';

$username = $_SESSION['username'];
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data)) {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
    exit;
}

function generateSawnTimberTransactionId($db) {
    $prefix = 'ST/'.date('ym').'-';
    $countResult = $db->query("SELECT COUNT(*) AS total FROM Sawn_Timber_Header WHERE DATE_FORMAT(created_date, '%y%m')='".date('ym')."'");
    $count = ((int)$countResult->fetch_assoc()['total']) + 1;
    return $prefix.str_pad((string)$count, 4, '0', STR_PAD_LEFT);
}

function transactionIdExists($db, $transactionId) {
    $stmt = $db->prepare("SELECT id FROM Sawn_Timber_Header WHERE transaction_id=? AND status='0'");
    $stmt->bind_param('s', $transactionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

$groups = array();
foreach ($data as $row) {
    $transactionId = isset($row['TransactionID']) ? trim($row['TransactionID']) : '';
    $transactionDate = isset($row['TransactionDate']) ? trim($row['TransactionDate']) : '';
    $supplier = isset($row['Supplier']) ? trim($row['Supplier']) : '';
    $lot = isset($row['Lot']) ? trim($row['Lot']) : '';
    $bundle = isset($row['Bundle']) ? trim($row['Bundle']) : '';
    $remarks = isset($row['Remarks']) ? trim($row['Remarks']) : '';
    $species = isset($row['Species']) ? trim($row['Species']) : '';

    if (!$transactionDate || !$supplier || !$lot || !$bundle) {
        continue;
    }

    $key = $transactionId ?: md5($transactionDate.'|'.$supplier.'|'.$lot.'|'.$bundle.'|'.$remarks);
    if (!isset($groups[$key])) {
        $groups[$key] = array(
            'transaction_id' => $transactionId,
            'transaction_date' => $transactionDate,
            'supplier' => $supplier,
            'lot' => $lot,
            'bundle' => $bundle,
            'remarks' => $remarks,
            'details' => array()
        );
    }

    $groups[$key]['details'][] = array(
        'species' => $species,
        'thick' => isset($row['Thick']) ? trim($row['Thick']) : '0',
        'width' => isset($row['Width']) ? trim($row['Width']) : '0',
        'length' => isset($row['Length']) ? trim($row['Length']) : '0',
        'pieces' => isset($row['Pieces']) ? trim($row['Pieces']) : '0'
    );
}

if (empty($groups)) {
    echo json_encode(array("status" => "failed", "message" => "No valid rows found"));
    exit;
}

$db->begin_transaction();

try {
    $actionByStmt = $db->prepare("SET @sawn_timber_action_by=?");
    $actionByStmt->bind_param('s', $username);
    $actionByStmt->execute();
    $actionByStmt->close();

    foreach ($groups as $group) {
        $transactionId = $group['transaction_id'] ?: generateSawnTimberTransactionId($db);
        if (transactionIdExists($db, $transactionId)) {
            throw new Exception("Transaction ID already exists: ".$transactionId);
        }

        $stmt = $db->prepare("INSERT INTO Sawn_Timber_Header (transaction_id, transaction_date, supplier, lot, bundle, remarks, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssss', $transactionId, $group['transaction_date'], $group['supplier'], $group['lot'], $group['bundle'], $group['remarks'], $username, $username);
        $stmt->execute();
        $headerId = $stmt->insert_id;
        $stmt->close();

        $detailStmt = $db->prepare("INSERT INTO Sawn_Timber_Detail (header_id, species, thick, width, length, pieces, tons) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($group['details'] as $detail) {
            if (!$detail['species']) {
                continue;
            }

            $tons = calculateSawnTimberTons($detail['thick'], $detail['width'], $detail['length'], $detail['pieces']);
            $detailStmt->bind_param('sssssss', $headerId, $detail['species'], $detail['thick'], $detail['width'], $detail['length'], $detail['pieces'], $tons);
            $detailStmt->execute();
        }
        $detailStmt->close();
    }

    $db->commit();
    echo json_encode(array("status" => "success", "message" => "Uploaded Successfully!!"));
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(array("status" => "failed", "message" => $e->getMessage()));
}

$db->close();
?>
