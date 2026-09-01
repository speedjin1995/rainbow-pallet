<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$uid = $_SESSION['username'];

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) {
    $errorSoProductArray = [];
    $status = '0';

    foreach ($data as $rows) {
        $EmployeeCode = !empty($rows['EmployeeCode']) ? trim($rows['EmployeeCode']) : '';
        $Username = !empty($rows['Username']) ? trim($rows['Username']) : '';
        $Name = !empty($rows['UserName']) ? trim($rows['UserName']) : '';
        $UserEmail = !empty($rows['UserEmail']) ? trim($rows['UserEmail']) : '';
        $Role = !empty($rows['Role']) ? trim($rows['Role']) : '';
        $param_password = password_hash("123456", PASSWORD_DEFAULT);
        $param_token = bin2hex(random_bytes(50));

        if ($EmployeeCode != null && $EmployeeCode != '') {
            $check = $db->prepare("SELECT id FROM Users WHERE employee_code = ? AND status = ?");
            $check->bind_param('ss', $EmployeeCode, $status);
            $check->execute();
            $userRow = $check->get_result()->fetch_assoc();
            $check->close();

            if (empty($userRow)) {
                if ($insert_stmt = $db->prepare("INSERT INTO Users (employee_code, username, name, useremail, role, password, token, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                    $insert_stmt->bind_param('sssssssss', $EmployeeCode, $Username, $Name, $UserEmail, $Role, $param_password, $param_token, $uid, $uid);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
            } else {
                $errorSoProductArray[] = "User: " . $EmployeeCode . " already exist in master data.";
                continue;
            }
        }
    }

    $db->close();

    if (!empty($errorSoProductArray)) {
        echo json_encode(array("status" => "error", "message" => $errorSoProductArray));
    } else {
        echo json_encode(array("status" => "success", "message" => "Added Successfully!!"));
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
}
?>
