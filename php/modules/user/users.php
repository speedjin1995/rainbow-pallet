<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['username'])) {
    echo '<script type="text/javascript">window.location.href = "../login.html";</script>';
}

try {
    if (!isset($_POST['employeeCode'], $_POST['username'], $_POST['useremail'], $_POST['roles'])) {
        throw new Exception("Please fill in all the fields");
    }

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

    $db->begin_transaction();

    // Check for duplicates
    $id = ($_POST['id'] != null && $_POST['id'] != '') ? $_POST['id'] : null;
    $conditions = [];
    $params = [];
    $types = '';
    if ($param_code !== null && $param_code !== '') {
        $conditions[] = "employee_code = ?";
        $params[] = $param_code;
        $types .= 's';
    }
    if ($param_username !== null && $param_username !== '') {
        $conditions[] = "username = ?";
        $params[] = $param_username;
        $types .= 's';
    }
    if (!empty($conditions)) {
        $sql = "SELECT id, employee_code, username FROM Users WHERE status = 0 AND (" . implode(" OR ", $conditions) . ")";
        if ($id !== null) {
            $sql .= " AND id != ?";
            $params[] = $id;
            $types .= 's';
        }
        $duplicateCheck = $db->prepare($sql);
        $duplicateCheck->bind_param($types, ...$params);
        $duplicateCheck->execute();
        $result = $duplicateCheck->get_result();
        if ($row = $result->fetch_assoc()) {
            $field = ($param_code !== null && $row['employee_code'] === $param_code) ? 'Employee Code' : 'Username';
            throw new Exception($field . " already exists");
        }
        $duplicateCheck->close();
    }

    if ($id) {
        $stmt = $db->prepare("UPDATE Users SET username=?, name=?, useremail=?, role=?, modified_by=?, plant_id=?, employee_code=? WHERE id=?");
        $stmt->bind_param("ssssssss", $param_username, $param_name, $param_useremail, $param_role, $param_modified_by, $param_plant, $param_code, $_POST['id']);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();
        $db->commit();
        $db->close();

        echo json_encode(["status" => "success", "message" => "Updated Successfully!!"]);
    } else {
        $stmt = $db->prepare("INSERT INTO Users (employee_code, useremail, username, name, password, token, role, plant_id, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $param_code, $param_useremail, $param_username, $param_name, $param_password, $param_token, $param_role, $param_plant, $param_created_by, $param_modified_by);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();
        $db->commit();
        $db->close();

        echo json_encode(["status" => "success", "message" => "Added Successfully!!", "plants" => $param_plant]);
    }
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(["status" => "failed", "message" => $e->getMessage()]);
}
?>
