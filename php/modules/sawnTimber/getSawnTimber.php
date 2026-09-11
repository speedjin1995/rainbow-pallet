<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['id'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);

    if ($stmt = $db->prepare("SELECT * FROM Sawn_Timber_Header WHERE id=?")) {
        $stmt->bind_param('s', $id);

        if (!$stmt->execute()) {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        } else {
            $result = $stmt->get_result();
            $header = $result->fetch_assoc();
            $stmt->close();

            if (!$header) {
                echo json_encode(array("status" => "failed", "message" => "Record not found"));
                $db->close();
                exit;
            }

            $details = array();
            if ($detailStmt = $db->prepare("SELECT * FROM Sawn_Timber_Detail WHERE header_id=? ORDER BY id ASC")) {
                $detailStmt->bind_param('s', $id);
                $detailStmt->execute();
                $detailResult = $detailStmt->get_result();
                while ($row = $detailResult->fetch_assoc()) {
                    $details[] = $row;
                }
                $detailStmt->close();
            }

            echo json_encode(array("status" => "success", "message" => array("header" => $header, "details" => $details)));
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Missing Attribute"));
}

$db->close();
?>
