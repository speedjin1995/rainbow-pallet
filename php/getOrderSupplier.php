<?php
session_start();
require_once "db_connect.php";

if(isset($_POST['code'], $_POST['type'])){
	$code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
	$type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);

    $final_weight = [];
    $customerSupplierName = '';
    $destinationName = '';
    $siteName = '';
    $agentName = '';
    $productName = '';
    $plantName = '';
    $balance = 0;
    $order_supplier_weight = 0;
    // $previousRecordsTag = true;
    $count = 1;

    if ($type == 'Purchase'){
        if ($update_stmt = $db->prepare("SELECT * FROM Purchase_Order WHERE po_no=? AND status='Open' AND deleted='0'")) {
            $update_stmt->bind_param('s', $code);
            
            // Execute the prepared query.
            if (!$update_stmt->execute()) {
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
                    $customerSupplierName = $row['supplier_name'];
                    $destinationName = $row['destination_name'];
                    $siteName = $row['site_name'];
                    $agentName = $row['agent_name'];
                    $productName = $row['raw_mat_name'];
                    $plantName = $row['plant_name'];
                    $transporterName = $row['transporter_name'];
                    $vehNo = $row['veh_number'];
                    $exDel = $row['exquarry_or_delivered'];
                    $order_supplier_weight = $row['order_quantity'];
                    $balance = $row['balance'];
                }

                // $empQuery = "SELECT * FROM Weight WHERE status = '0' AND purchase_order = '$code' AND transaction_status = '$type' ORDER BY id ASC"; 
                // $empRecords = mysqli_query($db, $empQuery);
                // if (mysqli_num_rows($empRecords) == 0) { // Check if records exist
                //     // No records found
                //     $previousRecordsTag = false;

                //     // while ($row = mysqli_fetch_assoc($empRecords)) {
                //     //     $final_weight[] = !empty($row['final_weight']) ? $row['final_weight'] : 0;
                //     // }
                // }

                // prevRecordTag
                // $finalWeight = array_sum($final_weight);
                $message['customer_supplier_name'] = $customerSupplierName;
                $message['destination_name'] = $destinationName;
                $message['site_name'] = $siteName;
                $message['agent_name'] = $agentName;
                $message['product_name'] = $productName;
                $message['plant_name'] = $plantName;
                $message['transporter_name'] = $transporterName;
                $message['veh_number'] = $vehNo;
                $message['order_supplier_weight'] = $order_supplier_weight;
                $message['balance'] = $balance;
                // $message['final_weight'] = $finalWeight;
                // $message['previousRecordsTag'] = $previousRecordsTag;

                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => $message
                    )
                );
            }
        }
    }else{
        if ($update_stmt = $db->prepare("SELECT * FROM Sales_Order WHERE order_no=? AND status='Open' AND deleted='0'")) {
            $update_stmt->bind_param('s', $code);
            
            // Execute the prepared query.
            if (!$update_stmt->execute()) {
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
                    $customerSupplierName = $row['customer_name'];
                    $destinationName = $row['destination_name'];
                    $siteName = $row['site_name'];
                    $agentName = $row['agent_name'];
                    $productName = $row['product_name'];
                    $plantName = $row['plant_name'];
                    $transporterName = $row['transporter_name'];
                    $vehNo = $row['veh_number'];
                    $exDel = $row['exquarry_or_delivered'];
                    $order_supplier_weight = $row['order_quantity'];
                    $balance = $row['balance'];
                }  

                // $empQuery = "SELECT * FROM Weight WHERE status = '0' AND purchase_order = '$code' AND transaction_status = '$type' ORDER BY id ASC"; 
                // $empRecords = mysqli_query($db, $empQuery);
                // if (mysqli_num_rows($empRecords) == 0) { // Check if records exist
                //     // No records found
                //     $previousRecordsTag = false;

                //     // while ($row = mysqli_fetch_assoc($empRecords)) {
                //     //     $final_weight[] = !empty($row['final_weight']) ? $row['final_weight'] : 0;
                //     // }
                // }

                // prevRecordTag
                // $finalWeight = array_sum($final_weight);
                $message['customer_supplier_name'] = $customerSupplierName;
                $message['destination_name'] = $destinationName;
                $message['site_name'] = $siteName;
                $message['agent_name'] = $agentName;
                $message['product_name'] = $productName;
                $message['plant_name'] = $plantName;
                $message['transporter_name'] = $transporterName;
                $message['veh_number'] = $vehNo;
                $message['order_supplier_weight'] = $order_supplier_weight;
                $message['balance'] = $balance;
                // $message['final_weight'] = $finalWeight;
                // $message['previousRecordsTag'] = $previousRecordsTag;

                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => $message
                    )
                );
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