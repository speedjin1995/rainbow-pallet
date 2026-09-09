<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_POST['id'])) {
    echo json_encode(array("status" => "failed", "message" => "Missing record"));
    exit;
}

$id = $_POST['id'];
$stmt = $db->prepare("UPDATE Sawn_Timber_Header SET status='1', modified_by=? WHERE id=?");
$stmt->bind_param('ss', $_SESSION['username'], $id);

if (!$stmt->execute()) {
    echo json_encode(array("status" => "failed", "message" => $stmt->error));
} else {
    echo json_encode(array("status" => "success", "message" => "Deleted Successfully!!"));
}

$stmt->close();
$db->close();
?>
