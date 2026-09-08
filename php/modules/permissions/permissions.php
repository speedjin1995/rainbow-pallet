<?php
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['id'])){
    echo '<script type="text/javascript">location.href = "../login.php";</script>';
    exit;
}

if (isset($_POST['permissionName'])) {
    $permissionId = !empty($_POST['permissionId']) ? trim($_POST['permissionId']) : null;
    $permissionName = !empty($_POST['permissionName']) ? strtolower(str_replace(' ', '_', trim($_POST['permissionName']))) : null;

    if(empty($permissionName)){
        echo json_encode(
            array("status" => "failed", 
            "message" => "Please fill in all the fields"
        ));
        exit;
    }

    // Build modules JSON
    if(!empty($_POST['modulesAll'])){
        $modulesJson = json_encode(['All']);
    } elseif(!empty($_POST['modules'])){
        $modulesJson = json_encode(array_values($_POST['modules']));
    } else {
        $modulesJson = json_encode(['All']);
    }

    if(!empty($permissionId)){
        if ($stmt = $db->prepare("UPDATE permissions SET name=?, modules=? WHERE id=?")) {
            $stmt->bind_param('sss', $permissionName, $modulesJson, $permissionId);
            if ($stmt->execute()) {
                echo json_encode(
                    array(
                        "status" => "success", 
                        "message" => "Updated Successfully!!"
                ));
            } else {
                echo json_encode(
                    array(
                        "status" => "failed", 
                        "message" => $stmt->error
                    ));
            }
            $stmt->close();
        }
    } else {
        if ($stmt = $db->prepare("INSERT INTO permissions (name, modules) VALUES (?, ?)")) {
            $stmt->bind_param('ss', $permissionName, $modulesJson);
            if ($stmt->execute()) {
                echo json_encode(
                    array(
                        "status" => "success", 
                        "message" => "Added Successfully!!"
                    ));
            } else {
                echo json_encode(
                    array(
                        "status" => "failed", 
                        "message" => $stmt->error
                    ));
            }
            $stmt->close();
        }
    }

    $db->close();
} else {
    echo json_encode(
        array(
            "status" => "failed", 
            "message" => "Please fill in all the fields"
        ));
}
?>
