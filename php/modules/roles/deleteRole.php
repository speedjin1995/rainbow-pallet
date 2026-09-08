<?php
session_start();
require_once '../../db_connect.php';

$username = $_SESSION["username"];

if(isset($_POST['roleID'])){
	$id = filter_input(INPUT_POST, 'roleID', FILTER_SANITIZE_STRING);
	$del = "1";

	$type = '';

	if(isset($_POST['type']) && $_POST['type']!=null && $_POST['type']!=""){
		$type = $_POST['type'];
	}

	if ($type == 'MULTI'){
		if(is_array($_POST['roleID'])){
			$ids = implode(",", $_POST['roleID']);
		}else{
			$ids = $_POST['roleID'];
		}

		if ($stmt2 = $db->prepare("UPDATE roles SET deleted=? WHERE id IN ($ids)")) {
			$stmt2->bind_param('s', $del);
			
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
		if ($stmt2 = $db->prepare("UPDATE roles SET deleted=? WHERE id=?")) {
			$stmt2->bind_param('ss', $del , $id);
			
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
