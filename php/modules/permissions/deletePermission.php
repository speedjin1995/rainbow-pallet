<?php
session_start();
require_once '../../db_connect.php';

if(isset($_POST['permissionID'])){
    $type = isset($_POST['type']) ? $_POST['type'] : '';

    if ($type == 'MULTI'){
        if(is_array($_POST['permissionID'])){
            $ids = implode(",", $_POST['permissionID']);
        } else {
            $ids = $_POST['permissionID'];
        }

        if ($stmt = $db->prepare("DELETE FROM permissions WHERE id IN ($ids)")) {
            if($stmt->execute()){
                echo json_encode(array("status" => "success", "message" => "Deleted"));
            } else {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            }
            $stmt->close();
        } else {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        }
    } else {
        $id = filter_input(INPUT_POST, 'permissionID', FILTER_SANITIZE_STRING);

        if ($stmt = $db->prepare("DELETE FROM permissions WHERE id=?")) {
            $stmt->bind_param('s', $id);
            if($stmt->execute()){
                echo json_encode(array("status" => "success", "message" => "Deleted"));
            } else {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            }
            $stmt->close();
        } else {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        }
    }

    $db->close();
} else {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
}
?>
