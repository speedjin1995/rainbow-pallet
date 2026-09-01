<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['userID'])) {
    $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($stmt = $db->prepare("SELECT * FROM Destination WHERE id=?")) {
        $stmt->bind_param('s', $id);

        if (!$stmt->execute()) {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        } else {
            $result = $stmt->get_result();
            $message = array();

            while ($row = $result->fetch_assoc()) {
                $message['id']               = $row['id'];
                $message['destination_code'] = $row['destination_code'];
                $message['name']             = $row['name'];
                $message['description']      = $row['description'];
            }

            echo json_encode(array("status" => "success", "message" => $message));
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Missing Attribute"));
}
?>
