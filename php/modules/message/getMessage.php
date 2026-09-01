<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['messageId'])) {
    $id = filter_input(INPUT_POST, 'messageId', FILTER_SANITIZE_STRING);

    if ($stmt = $db->prepare("SELECT * FROM message_resource WHERE id=?")) {
        $stmt->bind_param('s', $id);

        if (!$stmt->execute()) {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        } else {
            $result = $stmt->get_result();
            $message = array();

            while ($row = $result->fetch_assoc()) {
                $message['id']               = $row['id'];
                $message['message_key_code'] = $row['message_key_code'];
                $message['en']               = $row['en'];
                $message['zh']               = $row['zh'];
                $message['my']               = $row['my'];
                $message['ne']               = $row['ne'];
            }

            echo json_encode(array("status" => "success", "message" => $message));
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Missing Attribute"));
}
?>
