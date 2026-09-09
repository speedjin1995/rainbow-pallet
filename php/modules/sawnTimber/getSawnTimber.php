<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_POST['id'])) {
    echo json_encode(array("status" => "failed", "message" => "Missing record"));
    exit;
}

$id = trim($_POST['id']);

if ($stmt = $db->prepare("SELECT * FROM Sawn_Timber_Header WHERE id=?")) {
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $header = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$header) {
        echo json_encode(array("status" => "failed", "message" => "Record not found"));
        exit;
    }

    $details = array();
    if ($detailStmt = $db->prepare("SELECT * FROM Sawn_Timber_Detail WHERE header_id=? ORDER BY id ASC")) {
        $detailStmt->bind_param('s', $id);
        $detailStmt->execute();
        $result = $detailStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
        $detailStmt->close();
    }

    echo json_encode(array("status" => "success", "message" => array("header" => $header, "details" => $details)));
} else {
    echo json_encode(array("status" => "failed", "message" => $db->error));
}

$db->close();
?>
