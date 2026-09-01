<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['messageId'])) {
    $id = filter_input(INPUT_POST, 'messageId', FILTER_SANITIZE_STRING);

    if ($stmt = $db->prepare("DELETE FROM message_resource WHERE id=?")) {
        $stmt->bind_param('s', $id);

        if (!$stmt->execute()) {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        } else {
            $stmt->close();
            $db->close();
            echo json_encode(array("status" => "success", "message" => "Deleted Successfully"));
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Missing Attribute"));
}
?>
