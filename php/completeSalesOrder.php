<?php
session_start();
require_once 'db_connect.php';

$username = $_SESSION["username"];

if(isset($_POST['userID'])){
	$id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);
	$status = "Close";

	if ($stmt2 = $db->prepare("UPDATE Sales_Order SET status=? WHERE id=?")) {
		$stmt2->bind_param('ss', $status, $id);
		
		if($stmt2->execute()){
			$stmt2->close();
			$db->close();
			echo json_encode(
				array(
					"status"=> "success", 
					"message"=> "Closed"
				)
			);
		} else{
		    echo json_encode(
    	        array(
    	            "status"=> "failed", 
    	            "message"=> $stmt2->error
    	        )
    	    );
		}
	} 
	else{
	    echo json_encode(
	        array(
	            "status"=> "failed", 
	            "message"=> "Somethings wrong"
	        )
	    );
	}
} 
else{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    ); 
}
?>
