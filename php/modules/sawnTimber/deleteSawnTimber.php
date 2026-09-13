<?php
session_start();
require_once '../../db_connect.php';

$username = $_SESSION["username"];

if (isset($_POST['userID'])) {
    $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);
    $del = "1";
    $type = isset($_POST['type']) && $_POST['type'] != '' ? $_POST['type'] : '';

    $actionByStmt = $db->prepare("SET @sawn_timber_action_by=?");
    $actionByStmt->bind_param('s', $username);
    $actionByStmt->execute();
    $actionByStmt->close();

    if ($type == 'MULTI') {
        $ids = is_array($_POST['userID']) ? implode(",", $_POST['userID']) : $_POST['userID'];

        if ($stmt = $db->prepare("UPDATE Sawn_Timber_Header SET status=?, modified_by=? WHERE id IN ($ids)")) {
            $stmt->bind_param('ss', $del, $username);

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
        if ($stmt = $db->prepare("UPDATE Sawn_Timber_Header SET status=?, modified_by=? WHERE id=?")) {
            $stmt->bind_param('sss', $del, $username, $id);

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
