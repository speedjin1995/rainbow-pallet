<?php
session_start();
require_once "db_connect.php";

if(isset($_POST['userID'])){
	$id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($update_stmt = $db->prepare("SELECT * FROM Weight_Container WHERE container_no=? AND status = '0' AND is_complete = 'Y' AND is_cancel = 'N'")) {
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
                $message['transaction_status'] = $row['transaction_status'];
                $message['customer_code'] = $row['customer_code'];
                $message['customer_name'] = $row['customer_name'];
                $message['supplier_code'] = $row['supplier_code'];
                $message['supplier_name'] = $row['supplier_name'];
                $message['invoice_no'] = $row['invoice_no'];
                $message['delivery_no'] = $row['delivery_no'];
                $message['purchase_order'] = $row['purchase_order'];
                $message['container_no'] = $row['container_no'];
                $message['seal_no'] = $row['seal_no'];
                $message['container_no2'] = $row['container_no2'];
                $message['seal_no2'] = $row['seal_no2'];
                $message['product_code'] = $row['product_code'];
                $message['product_name'] = $row['product_name'];
                $message['raw_mat_code'] = $row['raw_mat_code'];
                $message['raw_mat_name'] = $row['raw_mat_name'];
                $message['plant_code'] = $row['plant_code'];
                $message['plant_name'] = $row['plant_name'];
                $message['transporter_code'] = $row['transporter_code'];
                $message['transporter'] = $row['transporter'];
                $message['destination_code'] = $row['destination_code'];
                $message['destination'] = $row['destination'];
                $message['gross_weight1'] = $row['gross_weight1'];
                $message['gross_weight1_date'] = $row['gross_weight1_date'];
                $message['gross_weight_by1'] = $row['gross_weight_by1'];
                $message['tare_weight1'] = $row['tare_weight1'];
                $message['tare_weight1_date'] = $row['tare_weight1_date'];
                $message['tare_weight_by1'] = $row['tare_weight_by1'];
                $message['lorry_plate_no1'] = $row['lorry_plate_no1'];
                $message['nett_weight1'] = $row['nett_weight1'];

                if ($update_stmt2 = $db->prepare("SELECT * FROM Vehicle WHERE veh_number=?")) {
                    $update_stmt2->bind_param('s', $row['lorry_plate_no1']);
                    $update_stmt2->execute();
                    $result2 = $update_stmt2->get_result();
                    
                    if ($row2 = $result2->fetch_assoc()) {
                        $message['vehicleNoTxt'] = null; // Replace "123" with the actual value if needed
                    } 
                    else {
                        $message['vehicleNoTxt'] = $row['lorry_plate_no1']; // Debugging line
                    }
                } 
                else {
                    // Log error if the statement couldn't be prepared
                    $message['vehicleNoTxt'] = $db->error;
                }
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