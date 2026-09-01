<?php
session_start();
require_once '../../db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$uid = $_SESSION['username'];

// Read the JSON data from the request body
$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) {
    $errorSoProductArray = [];
    foreach ($data as $rows) {
        $Code = $rows['Code'];
        $Name = !empty($rows['Name']) ? trim($rows['Name']) : '';
        $Description = !empty($rows['Description']) ? trim($rows['Description']) : '';
        $action = "1";
        
        if($Code != null && $Code != ''){
            if ($raw_mat_stmt = $db->prepare("SELECT * FROM Raw_Mat WHERE raw_mat_code = ? AND status = '0'")) {
                $raw_mat_stmt->bind_param('s', $Code);
                $raw_mat_stmt->execute();
                $rawMatRow = $raw_mat_stmt->get_result()->fetch_assoc();
                $raw_mat_stmt->close();
            }

            if(empty($rawMatRow)){
                if ($insert_stmt = $db->prepare("INSERT INTO Raw_Mat (raw_mat_code, name, description, created_by, modified_by) VALUES (?, ?, ?, ?, ?)")) {
                    $insert_stmt->bind_param('sssss', $Code, $Name, $Description, $uid, $uid);
                    $insert_stmt->execute();
                    $insert_stmt->close(); 
                }
            }else{
                $errMsg = "Raw Material: ". $Name ." already exist in master data.";
                $errorSoProductArray[] = $errMsg;
                continue;    
            }
        }
    }

    $db->close();

    if (!empty($errorSoProductArray)){
        echo json_encode(
            array(
                "status"=> "error", 
                "message"=> $errorSoProductArray 
            )
        );
    }else{
        echo json_encode(
            array(
                "status"=> "success", 
                "message"=> "Added Successfully!!" 
            )
        );
    }
} else {
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    );     
}
?>
