<?php
session_start();
require_once '../../db_connect.php';

$username = $_SESSION["username"];

if(isset($_POST['moduleID'])){
	$id = filter_input(INPUT_POST, 'moduleID', FILTER_SANITIZE_STRING);
	$del = "1";
	$action = "3";

	$type = '';

	if(isset($_POST['type']) && $_POST['type']!=null && $_POST['type']!=""){
		$type = $_POST['type'];
	}
	
	if ($type == 'MULTI'){
		if(is_array($_POST['moduleID'])){
			$ids = implode(",", $_POST['moduleID']);
		}else{
			$ids = $_POST['moduleID'];
		}

		if ($stmt2 = $db->prepare("DELETE FROM modules WHERE id IN ($ids)")) {			
			if($stmt2->execute()){
				$stmt2->close();
				$db->close();
				
				echo json_encode(
					array(
						"status"=> "success", 
						"message"=> "Deleted"
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
					"message"=> "Somthings wrong"
				)
			);
		}
	}else{
		if ($stmt2 = $db->prepare("DELETE FROM modules WHERE id=?")) {
			$stmt2->bind_param('s', $id);
			
			if($stmt2->execute()){
				echo json_encode(
                    array(
                        "status"=> "success", 
                        "message"=> "Deleted"
                    )
                );

				$stmt2->close();
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
