<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['username'])) {
    echo '<script type="text/javascript">window.location.href = "../login.html";</script>';
}

try {
    if (!isset($_POST['userID'])) {
        throw new Exception('Missing required fields');
    }

    $userID = $_POST['userID'];
    $password = "123456";
    $param_password = password_hash($password, PASSWORD_DEFAULT);
    $param_token = bin2hex(random_bytes(50));
    $modifiedBy = $_SESSION['username'];
    $modifiedDate = date('Y-m-d H:i:s');

    $db->begin_transaction();

    // Skip trigger
    $db->query("SET @skip_user_trigger = 1");

    $stmt = $db->prepare("UPDATE Users SET password = ?, token = ?, modified_by = ?, modified_date = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $param_password, $param_token, $modifiedBy, $modifiedDate, $userID);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    $stmt->close();

    // Get user data for log
    $stmt = $db->prepare("SELECT employee_code, username, name, useremail, role, plant_id, languages FROM Users WHERE id = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Manual insert into Users_Log with action_id = 6 (password reset)
    $stmt = $db->prepare("INSERT INTO Users_Log (user_id, employee_code, username, name, useremail, role, plant_id, languages, action_id, action_by, event_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 6, ?, ?)");
    $stmt->bind_param("isssssssss", $userID, $user['employee_code'], $user['username'], $user['name'], $user['useremail'], $user['role'], $user['plant_id'], $user['languages'], $modifiedBy, $modifiedDate);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }
    $stmt->close();

    $db->commit();
    $db->close();

    echo json_encode(['status' => 'success', 'message' => 'Password reset successfully']);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['status' => 'failed', 'message' => $e->getMessage()]);
}
?>
