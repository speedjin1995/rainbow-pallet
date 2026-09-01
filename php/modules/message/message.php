<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['keyCode'], $_POST['englishDecs'], $_POST['chineseDecs'], $_POST['malayDecs'], $_POST['nepaliDecs'])) {
    $keyCode = filter_input(INPUT_POST, 'keyCode', FILTER_SANITIZE_STRING);
    $englishDecs = filter_input(INPUT_POST, 'englishDecs', FILTER_SANITIZE_STRING);
    $chineseDecs = filter_input(INPUT_POST, 'chineseDecs', FILTER_SANITIZE_STRING);
    $malayDecs = filter_input(INPUT_POST, 'malayDecs', FILTER_SANITIZE_STRING);
    $nepaliDecs = filter_input(INPUT_POST, 'nepaliDecs', FILTER_SANITIZE_STRING);

    if ($_POST['keyId'] != null && $_POST['keyId'] != '') {
        if ($stmt = $db->prepare("UPDATE message_resource SET message_key_code=?, en=?, zh=?, my=?, ne=? WHERE id=?")) {
            $stmt->bind_param('ssssss', $keyCode, $englishDecs, $chineseDecs, $malayDecs, $nepaliDecs, $_POST['keyId']);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            } else {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Updated Successfully!!"));
            }
        }
    } else {
        if ($stmt = $db->prepare("INSERT INTO message_resource (message_key_code, en, zh, my, ne) VALUES (?, ?, ?, ?, ?)")) {
            $stmt->bind_param('sssss', $keyCode, $englishDecs, $chineseDecs, $malayDecs, $nepaliDecs);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            } else {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Added Successfully!!"));
            }
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
}
?>
