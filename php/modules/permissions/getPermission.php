<?php
session_start();
require_once "../../db_connect.php";

if(isset($_POST['id'])){
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);

    if ($stmt = $db->prepare("SELECT * FROM permissions WHERE id=?")) {
        $stmt->bind_param('s', $id);

        if (!$stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed", 
                    "message" => "Something went wrong"
                ));
        } else {
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if($row){
                $message = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'modules' => json_decode($row['modules'], true) ?: ['All']
                );
                echo json_encode(
                    array(
                        "status" => "success", 
                        "message" => $message
                    ));
            } else {
                echo json_encode(
                    array(
                        "status" => "failed", 
                        "message" => "Permission not found"
                    ));
            }
        }
    }
} else {
    echo json_encode(
        array(
            "status" => "failed", 
            "message" => "Missing Attribute"
        ));
}
?>
