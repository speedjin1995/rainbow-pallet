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
        $ProductCode = $rows['ProductCode'];
        $ProductName = !empty($rows['ProductName']) ? trim($rows['ProductName']) : '';
        $ProductDescription = !empty($rows['ProductDescription']) ? trim($rows['ProductDescription']) : '';
        
        # Checking for existing Product.
        if($ProductCode != null && $ProductCode != ''){
            if ($product_stmt = $db->prepare("SELECT * FROM Product WHERE product_code = ? AND status = '0'")) {
                $product_stmt->bind_param('s', $ProductCode);
                $product_stmt->execute();
                $productRow = $product_stmt->get_result()->fetch_assoc();
                $product_stmt->close();
            }

            if(empty($productRow)){
                if ($insert_stmt = $db->prepare("INSERT INTO Product (product_code, name, description, created_by, modified_by) VALUES (?, ?, ?, ?, ?)")) {
                    $insert_stmt->bind_param('sssss', $ProductCode, $ProductName, $ProductDescription, $uid, $uid);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
            }else{
                $errMsg = "Product: ". $Name ." already exist in master data.";
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
