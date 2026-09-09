<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_POST['id'])) {
    echo json_encode(array("status" => "failed", "message" => "Missing species"));
    exit;
}

$id = trim($_POST['id']);
$stmt = $db->prepare("SELECT * FROM Sawn_Timber_Species WHERE id=?");
$stmt->bind_param('s', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$db->close();

if ($row) {
    echo json_encode(array("status" => "success", "message" => $row));
} else {
    echo json_encode(array("status" => "failed", "message" => "Species not found"));
}
?>
