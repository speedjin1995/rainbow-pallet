<?php
session_start();
require_once "../../db_connect.php";

if(isset($_POST['id'])){
	$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);

    if ($update_stmt = $db->prepare("SELECT * FROM role_permissions WHERE role_id=?")) {
        $update_stmt->bind_param('s', $id);
        
        // Execute the prepared query.
        if (! $update_stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something went wrong"
                )); 
        }
        else{
            $result = $update_stmt->get_result();
            $message = array();
            
            while ($row = $result->fetch_assoc()) {
                $message[] = array(
                    'module_id' => $row['module_id'],
                    'permission_id' => $row['permission_id']
                );
            }
            
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => $message
                ));   
        }
    }
}
else{
    echo json_encode(
        array(
            "status" => "failed",
            "message" => "Missing Attribute"
            )); 
}
?>
