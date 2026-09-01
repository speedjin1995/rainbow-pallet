<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['id'])) {
    echo '<script type="text/javascript">location.href = "../login.php";</script>';
} else {
    $username = $_SESSION["username"];
}
$id = $_SESSION['id'];

if (isset($_POST['destinationCode'])) {

    $destinationId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $destinationCode = empty($_POST["destinationCode"]) ? null : trim($_POST["destinationCode"]);
    $destinationName = empty($_POST["destinationName"]) ? null : trim($_POST["destinationName"]);
    $description = empty($_POST["description"]) ? null : trim($_POST["description"]);

    if (!empty($destinationId)) {
        if ($stmt = $db->prepare("UPDATE Destination SET destination_code=?, name=?, description=?, created_by=?, modified_by=? WHERE id=?")) {
            $stmt->bind_param('ssssss', $destinationCode, $destinationName, $description, $username, $username, $destinationId);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            } else {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Updated Successfully!!"));
            }
        }
    } else {
        if ($stmt = $db->prepare("INSERT INTO Destination (destination_code, name, description, created_by, modified_by) VALUES (?, ?, ?, ?, ?)")) {
            $stmt->bind_param('sssss', $destinationCode, $destinationName, $description, $username, $username);

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
