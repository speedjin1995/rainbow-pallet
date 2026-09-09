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
        $ItemCode = $rows['ItemCode'];
        $ItemName = !empty($rows['ItemName']) ? trim($rows['ItemName']) : '';
        
        if($ItemCode != null && $ItemCode != ''){
            if ($pv_item_stmt = $db->prepare("SELECT * FROM Pv_Items WHERE item_code = ? AND status = '0'")) {
                $pv_item_stmt->bind_param('s', $Code);
                $pv_item_stmt->execute();
                $pvItemRow = $pv_item_stmt->get_result()->fetch_assoc();
                $pv_item_stmt->close();
            }

            if(empty($pvItemRow)){
                if ($insert_stmt = $db->prepare("INSERT INTO Pv_Items (item_code, item_name, created_by) VALUES (?, ?, ?)")) {
                    $insert_stmt->bind_param('sss', $ItemCode, $ItemName, $uid);
                    $insert_stmt->execute();
                    $insert_stmt->close(); 
                }
            }else{
                $errMsg = "Pv Item: ". $Name ." already exist in master data.";
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
