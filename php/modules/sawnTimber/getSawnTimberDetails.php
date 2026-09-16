<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['id'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);

    // Get header info
    $headerStmt = $db->prepare("SELECT h.record_date, h.remarks, w.transaction_id, w.delivery_no, w.lorry_plate_no1, w.destination 
        FROM Sawn_Timber_Header h 
        LEFT JOIN Weight w ON h.weight_id = w.id 
        WHERE h.id = ?");
    $headerStmt->bind_param('s', $id);
    $headerStmt->execute();
    $headerResult = $headerStmt->get_result();
    $header = $headerResult->fetch_assoc();
    $headerStmt->close();

    // Get details
    $stmt = $db->prepare("SELECT * FROM Sawn_Timber_Detail WHERE header_id = ? ORDER BY id ASC");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $details = array();
    while ($row = $result->fetch_assoc()) {
        $details[] = $row;
    }
    $stmt->close();

    $response = array(
        "record_date" => $header['record_date'] ?? '',
        "transaction_id" => $header['transaction_id'] ?? '',
        "delivery_no" => $header['delivery_no'] ?? '',
        "lorry_plate_no1" => $header['lorry_plate_no1'] ?? '',
        "destination" => $header['destination'] ?? '',
        "remarks" => $header['remarks'] ?? '',
        "details" => $details
    );

    echo json_encode(array("status" => "success", "message" => $response));
} else {
    echo json_encode(array("status" => "failed", "message" => "Missing ID"));
}

$db->close();
?>
