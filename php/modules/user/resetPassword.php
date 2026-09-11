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

    $db->begin_transaction();

    $stmt = $db->prepare("UPDATE Users SET password = ?, token = ? WHERE id = ?");
    $stmt->bind_param("ssi", $param_password, $param_token, $userID);

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
