<?php
session_start();
require_once '../../db_connect.php';

if(isset($_POST['userID'])){
	$id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($update_stmt = $db->prepare("SELECT * FROM Location WHERE id=?")) {
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
                $message['location_code'] = $row['location_code'];
                $message['location_name'] = $row['location_name'];
                $message['weighing_count'] = $row['weighing_count'];
                $message['plant_id'] = $row['plant_id'];

                // Query Port table if $_POST['port'] == 'Y'
                if (isset($_POST['port']) && !empty($_POST['port']) && $_POST['port'] == 'Y') {
                    if ($port_stmt = $db->prepare("SELECT * FROM Port WHERE id=?")){
                        $port_stmt->bind_param('s', $row['port_id']);
                        $port_stmt->execute();
                        $port_result = $port_stmt->get_result();

                        while ($port_row = $port_result->fetch_assoc()) {
                            $message['port_id'] = $port_row['id'];
                            $message['com_port'] = $port_row['com_port'];
                            $message['bits_per_second'] = $port_row['bits_per_second'];
                            $message['data_bits'] = $port_row['data_bits'];
                            $message['parity'] = $port_row['parity'];
                            $message['stop_bits'] = $port_row['stop_bits'];
                            $message['indicator'] = $port_row['indicator'];
                        }   
                    }
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