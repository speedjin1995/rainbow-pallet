<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['username'])) {
    echo '<script type="text/javascript">window.location.href = "../login.html";</script>';
}

if (isset($_POST['employeeCode'], $_POST['username'], $_POST['useremail'], $_POST['roles'])) {
    $name = $_SESSION["username"];

    $param_code = null;
    $password = "123456";
    $param_name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $param_useremail = filter_input(INPUT_POST, 'useremail', FILTER_SANITIZE_STRING);
    $param_username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $param_password = password_hash($password, PASSWORD_DEFAULT);
    $param_token = bin2hex(random_bytes(50));
    $param_role = filter_input(INPUT_POST, 'roles', FILTER_SANITIZE_STRING);

    if (isset($_POST['employeeCode']) && $_POST['employeeCode'] != null) {
        $param_code = filter_input(INPUT_POST, 'employeeCode', FILTER_SANITIZE_STRING);
    }

    $param_plant = array();
    if (isset($_POST['plantId']) && $_POST['plantId'] != null) {
        $param_plant = $_POST['plantId'];
    }

    $param_plant = json_encode($param_plant);
    $param_created_by = $name;
    $param_modified_by = $name;

    if ($_POST['id'] != null && $_POST['id'] != '') {
        if ($stmt = $db->prepare("UPDATE Users SET username=?, name=?, useremail=?, role=?, modified_by=?, plant_id=?, employee_code=? WHERE id=?")) {
            $stmt->bind_param("ssssssss", $param_username, $param_name, $param_useremail, $param_role, $param_modified_by, $param_plant, $param_code, $_POST['id']);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            } else {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Updated Successfully!!"));
            }
        }
    } else {
        if ($stmt = $db->prepare("INSERT INTO Users (employee_code, useremail, username, name, password, token, role, plant_id, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param("ssssssssss", $param_code, $param_useremail, $param_username, $param_name, $param_password, $param_token, $param_role, $param_plant, $param_created_by, $param_modified_by);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => $stmt->error));
            } else {
                $stmt->close();
                $db->close();
                echo json_encode(array("status" => "success", "message" => "Added Successfully!!", "plants" => $param_plant));
            }
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
}
?>
