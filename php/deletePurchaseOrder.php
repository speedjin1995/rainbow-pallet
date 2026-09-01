<?php
session_start();
require_once 'db_connect.php';

$username = $_SESSION["username"];

if(isset($_POST['userID'])){
	$id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);
	$del = "1";

	if ($stmt2 = $db->prepare("UPDATE Purchase_Order SET deleted=? WHERE id=?")) {
		$stmt2->bind_param('ss', $del, $id);
		
		if($stmt2->execute()){
			$stmt2->close();
			echo json_encode(
				array(
					"status"=> "success", 
					"message"=> "Deleted"
				)
			);

			// $stmt2->close();
			$db->close();
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
