<?php
session_start();
require_once '../../db_connect.php';

$username = $_SESSION["username"];

if (isset($_POST['userID'])) {
    $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);
    $del = "1";
    $type = isset($_POST['type']) && $_POST['type'] != '' ? $_POST['type'] : '';

    if ($type == 'MULTI') {
        $ids = is_array($_POST['userID']) ? implode(",", $_POST['userID']) : $_POST['userID'];

        if ($stmt = $db->prepare("UPDATE Supplier SET status=? WHERE id IN ($ids)")) {
            $stmt->bind_param('s', $del);

            if ($stmt->execute()) {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Deleted"));
            } else {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            }
        } else {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        }
    } else {
        if ($stmt = $db->prepare("UPDATE Supplier SET status=? WHERE id=?")) {
            $stmt->bind_param('ss', $del, $id);

            if ($stmt->execute()) {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Deleted"));
            } else {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            }
        } else {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
}
?>
