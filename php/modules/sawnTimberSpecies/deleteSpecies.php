<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_POST['id'])) {
    echo json_encode(array("status" => "failed", "message" => "Missing species"));
    exit;
}

$id = trim($_POST['id']);
$username = $_SESSION['username'];

$userStmt = $db->prepare("SET @sawn_timber_species_action_by=?");
$userStmt->bind_param('s', $username);
$userStmt->execute();
$userStmt->close();

$stmt = $db->prepare("DELETE FROM Sawn_Timber_Species WHERE id=?");
$stmt->bind_param('s', $id);

if ($stmt->execute()) {
    echo json_encode(array("status" => "success", "message" => "Deleted Successfully!!"));
} else {
    echo json_encode(array("status" => "failed", "message" => $stmt->error));
}

$stmt->close();
$db->close();
?>
