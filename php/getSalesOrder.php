<?php
session_start();
require_once "db_connect.php";
require_once "requires/lookup.php";

if(isset($_POST['userID'])){
	$id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    $format = '';

    if (isset($_POST['format']) && $_POST['format'] != ''){
        $format = $_POST['format'];
    }

    if ($format == 'EXPANDABLE'){
        if ($update_stmt = $db->prepare("SELECT * FROM Sales_Order WHERE id=?")) {
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
                    $message['id'] = $row['id'];
                    $message['company_code'] = $row['company_code'] ?? '';
                    $message['company_name'] = $row['company_name'] ?? '';
                    $message['customer_code'] = $row['customer_code'] ?? '';
                    $message['customer_name'] = $row['customer_name'] ?? '';
                    $message['site_code'] = $row['site_code'] ?? '';
                    $message['site_name'] = $row['site_name'] ?? '';
                    $message['order_date'] = $row['order_date'];
                    $message['order_no'] = $row['order_no']; //customer po no
                    $message['so_no'] = $row['so_no'];
                    $message['agent_code'] = $row['agent_code'] ?? '';
                    $message['agent_name'] = $row['agent_name'] ?? '';
                    $message['destination_code'] = $row['destination_code'] ?? '';
                    $message['destination_name'] = $row['destination_name'] ?? '';
                    $message['product_code'] = $row['product_code'] ?? '';
                    $message['product_name'] = $row['product_name'] ?? '';
                    $message['plant_code'] = $row['plant_code'] ?? '';
                    $message['plant_name'] = $row['plant_name'] ?? '';
                    $message['transporter_code'] = $row['transporter_code'] ?? '';
                    $message['transporter_name'] = $row['transporter_name'] ?? '';
                    $message['veh_number'] = $row['veh_number'] ?? '';
                    if ($row['exquarry_or_delivered'] == 'E'){
                        $message['exquarry_or_delivered'] = 'EX-QUARRY';
                    }else{
                        $message['exquarry_or_delivered'] = 'DELIVERED';
                    }
                    $message['order_quantity'] = $row['order_quantity'];
                    $message['balance'] = $row['balance'];
                    $message['remarks'] = $row['remarks'];

                    $weightData = array();

                    if($row['order_no'] != null && $row['order_no'] != ''){
                        $customerPONo = $row['order_no'];
                        $weightQuery = "SELECT * FROM Weight WHERE purchase_order = '$customerPONo' AND status = '0' AND transaction_status = 'Sales' ORDER BY id ASC";
                        $weightRecords = mysqli_query($db, $weightQuery);

                        while($weightRow = mysqli_fetch_assoc($weightRecords)) {
                            $weightData[] = array(
                                "id" => $weightRow['id'],
                                "transaction_id" => $weightRow['transaction_id'],
                                "product_code" => $weightRow['product_code'],
                                "product_name" => $weightRow['product_name'],
                                "delivery_no" => $weightRow['delivery_no'] ?? '',
                                "lorry_plate_no1" => $weightRow['lorry_plate_no1'],
                                "nett_weight1" => $weightRow['nett_weight1'],
                                "created_by" => searchNamebyId($weightRow['created_by'], $db)
                            );
                        }   
                    }

                    $message['weights'] = $weightData;
                }
                
                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => $message
                    ));   
            }
        }
    }else{
        if ($update_stmt = $db->prepare("SELECT * FROM Sales_Order WHERE id=?")) {
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
                    $message['id'] = $row['id'];
                    $message['company_code'] = $row['company_code'];
                    $message['customer_code'] = $row['customer_code'];
                    $message['site_code'] = $row['site_code'];
                    $message['order_date'] = $row['order_date'];
                    $message['order_no'] = $row['order_no'];
                    $message['so_no'] = $row['so_no'];
                    $message['agent_code'] = $row['agent_code'];
                    $message['destination_code'] = $row['destination_code'];
                    $message['product_code'] = $row['product_code'];
                    $message['plant_code'] = $row['plant_code'];
                    $message['transporter_code'] = $row['transporter_code'];
                    $message['veh_number'] = $row['veh_number'];
                    $message['exquarry_or_delivered'] = $row['exquarry_or_delivered'];
                    $message['order_quantity'] = $row['order_quantity'];
                    $message['remarks'] = $row['remarks'];
                }
                
                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => $message
                    ));   
            }
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