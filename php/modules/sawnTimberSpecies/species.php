<?php
session_start();
require_once '../../db_connect.php';

$id = isset($_POST['id']) ? trim($_POST['id']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if ($name === '') {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
    exit;
}

$duplicateSql = "SELECT id FROM Sawn_Timber_Species WHERE name=?";
if ($id !== '') {
    $duplicateSql .= " AND id<>?";
}

$duplicateStmt = $db->prepare($duplicateSql);
if ($id !== '') {
    $duplicateStmt->bind_param('ss', $name, $id);
} else {
    $duplicateStmt->bind_param('s', $name);
}
$duplicateStmt->execute();
if ($duplicateStmt->get_result()->num_rows > 0) {
    echo json_encode(array("status" => "failed", "message" => "Species already exists"));
    $duplicateStmt->close();
    $db->close();
    exit;
}
$duplicateStmt->close();

if ($id !== '') {
    $stmt = $db->prepare("UPDATE Sawn_Timber_Species SET name=? WHERE id=?");
    $stmt->bind_param('ss', $name, $id);
} else {
    $stmt = $db->prepare("INSERT INTO Sawn_Timber_Species (name) VALUES (?)");
    $stmt->bind_param('s', $name);
}

if ($stmt->execute()) {
    echo json_encode(array("status" => "success", "message" => "Saved Successfully!!"));
} else {
    echo json_encode(array("status" => "failed", "message" => $stmt->error));
}

$stmt->close();
$db->close();
?>
