<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['userID'])) {
    $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($stmt = $db->prepare("SELECT * FROM Users WHERE id=?")) {
        $stmt->bind_param('s', $id);

        if (!$stmt->execute()) {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        } else {
            $result = $stmt->get_result();
            $message = array();

            while ($row = $result->fetch_assoc()) {
                $message['id']            = $row['id'];
                $message['employee_code'] = $row['employee_code'];
                $message['username']      = $row['username'];
                $message['name']          = $row['name'];
                $message['useremail']     = $row['useremail'];
                $message['role_code']     = $row['role'];
                $message['plant']         = $row['plant_id'];
            }

            echo json_encode(array("status" => "success", "message" => $message));
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Missing Attribute"));
}
?>
