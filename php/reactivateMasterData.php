<?php
session_start();
require_once 'db_connect.php';

$username = $_SESSION["username"];

if(isset($_POST['userID'])){
	$id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);
	$reactivate = "0";
	$action = "2";

	$type = '';

	if(isset($_POST['type']) && $_POST['type']!=null && $_POST['type']!=""){
		$type = $_POST['type'];
	}

	if ($type == 'Customer') {
		if ($stmt2 = $db->prepare("UPDATE Customer SET status=? WHERE id=?")) {
			$stmt2->bind_param('ss', $reactivate, $id);
			
			if($stmt2->execute()){
				if ($insert_stmt = $db->prepare("INSERT INTO Customer_Log (customer_id, action_id, action_by) VALUES (?, ?, ?)")) {
					$insert_stmt->bind_param('sss', $id, $action, $username);
		
					// Execute the prepared query.
					if (! $insert_stmt->execute()) {
						echo json_encode(
							array(
								"status"=> "failed", 
								"message"=> $insert_stmt->error
							)
						);
					}
					else{
						$insert_stmt->close();
						echo json_encode(
							array(
								"status"=> "success", 
								"message"=> "Reactivated"
							)
						);
					}
				}
	
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
	}elseif ($type == 'Destination') {
		if ($stmt2 = $db->prepare("UPDATE Destination SET status=? WHERE id=?")) {
			$stmt2->bind_param('ss', $reactivate, $id);
			
			if($stmt2->execute()){
				if ($insert_stmt = $db->prepare("INSERT INTO Destination_Log (destination_id, action_id, action_by) VALUES (?, ?, ?)")) {
					$insert_stmt->bind_param('sss', $id, $action, $username);
		
					// Execute the prepared query.
					if (! $insert_stmt->execute()) {
						echo json_encode(
							array(
								"status"=> "failed", 
								"message"=> $insert_stmt->error
							)
						);
					}
					else{
						$insert_stmt->close();
						echo json_encode(
							array(
								"status"=> "success", 
								"message"=> "Reactivated"
							)
						);
					}
				}
	
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
	}elseif ($type == 'Product') {
		if ($stmt2 = $db->prepare("UPDATE Product SET status=? WHERE id=?")) {
			$stmt2->bind_param('ss', $reactivate, $id);
			
			if($stmt2->execute()){
				if ($insert_stmt = $db->prepare("INSERT INTO Product_Log (product_id, action_id, action_by) VALUES (?, ?, ?)")) {
					$insert_stmt->bind_param('sss', $id, $action, $username);
		
					// Execute the prepared query.
					if (! $insert_stmt->execute()) {
						echo json_encode(
							array(
								"status"=> "failed", 
								"message"=> $insert_stmt->error
							)
						);
					}
					else{
						$insert_stmt->close();
						echo json_encode(
							array(
								"status"=> "success", 
								"message"=> "Reactivated"
							)
						);
					}
				}
	
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
	}elseif ($type == 'RawMat') {
		if ($stmt2 = $db->prepare("UPDATE Raw_Mat SET status=? WHERE id=?")) {
			$stmt2->bind_param('ss', $reactivate, $id);
			
			if($stmt2->execute()){
				if ($insert_stmt = $db->prepare("INSERT INTO Raw_Mat_Log (raw_mat_id, action_id, action_by) VALUES (?, ?, ?)")) {
					$insert_stmt->bind_param('sss', $id, $action, $username);
		
					// Execute the prepared query.
					if (! $insert_stmt->execute()) {
						echo json_encode(
							array(
								"status"=> "failed", 
								"message"=> $insert_stmt->error
							)
						);
					}
					else{
						$insert_stmt->close();
						echo json_encode(
							array(
								"status"=> "success", 
								"message"=> "Reactivated"
							)
						);
					}
				}
	
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
	}elseif ($type == 'Supplier') {
		if ($stmt2 = $db->prepare("UPDATE Supplier SET status=? WHERE id=?")) {
			$stmt2->bind_param('ss', $reactivate, $id);
			
			if($stmt2->execute()){
				if ($insert_stmt = $db->prepare("INSERT INTO Supplier_Log (supplier_id, action_id, action_by) VALUES (?, ?, ?)")) {
					$insert_stmt->bind_param('sss', $id, $action, $username);
		
					// Execute the prepared query.
					if (! $insert_stmt->execute()) {
						echo json_encode(
							array(
								"status"=> "failed", 
								"message"=> $insert_stmt->error
							)
						);
					}
					else{
						$insert_stmt->close();
						echo json_encode(
							array(
								"status"=> "success", 
								"message"=> "Reactivated"
							)
						);
					}
				}
	
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
	}elseif ($type == 'Vehicle') {
		if ($stmt2 = $db->prepare("UPDATE Vehicle SET status=? WHERE id=?")) {
			$stmt2->bind_param('ss', $reactivate, $id);
			
			if($stmt2->execute()){
				if ($insert_stmt = $db->prepare("INSERT INTO Vehicle_Log (vehicle_id, action_id, action_by) VALUES (?, ?, ?)")) {
					$insert_stmt->bind_param('sss', $id, $action, $username);
		
					// Execute the prepared query.
					if (! $insert_stmt->execute()) {
						echo json_encode(
							array(
								"status"=> "failed", 
								"message"=> $insert_stmt->error
							)
						);
					}
					else{
						$insert_stmt->close();
						echo json_encode(
							array(
								"status"=> "success", 
								"message"=> "Reactivated"
							)
						);
					}
				}
	
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
	}elseif ($type == 'Transporter') {
		if ($stmt2 = $db->prepare("UPDATE Transporter SET status=? WHERE id=?")) {
			$stmt2->bind_param('ss', $reactivate, $id);
			
			if($stmt2->execute()){
				if ($insert_stmt = $db->prepare("INSERT INTO Transporter_Log (transporter_id, action_id, action_by) VALUES (?, ?, ?)")) {
					$insert_stmt->bind_param('sss', $id, $action, $username);
		
					// Execute the prepared query.
					if (! $insert_stmt->execute()) {
						echo json_encode(
							array(
								"status"=> "failed", 
								"message"=> $insert_stmt->error
							)
						);
					}
					else{
						$insert_stmt->close();
						echo json_encode(
							array(
								"status"=> "success", 
								"message"=> "Reactivated"
							)
						);
					}
				}
	
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
	}elseif ($type == 'User') {
		if ($stmt2 = $db->prepare("UPDATE Users SET status=? WHERE id=?")) {
			$stmt2->bind_param('ss', $reactivate, $id);
			
			if($stmt2->execute()){
				if ($insert_stmt = $db->prepare("INSERT INTO Users_Log (user_id, action_id, action_by) VALUES (?, ?, ?)")) {
					$insert_stmt->bind_param('sss', $id, $action, $username);
		
					// Execute the prepared query.
					if (! $insert_stmt->execute()) {
						echo json_encode(
							array(
								"status"=> "failed", 
								"message"=> $insert_stmt->error
							)
						);
					}
					else{
						$insert_stmt->close();
						echo json_encode(
							array(
								"status"=> "success", 
								"message"=> "Reactivated"
							)
						);
					}
				}
	
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
	}elseif ($type == 'Plant') {
		if ($stmt2 = $db->prepare("UPDATE Plant SET status=? WHERE id=?")) {
			$stmt2->bind_param('ss', $reactivate, $id);
			
			if($stmt2->execute()){
				if ($insert_stmt = $db->prepare("INSERT INTO Plant_Log (plant_id, action_id, action_by) VALUES (?, ?, ?)")) {
					$insert_stmt->bind_param('sss', $id, $action, $username);
		
					// Execute the prepared query.
					if (! $insert_stmt->execute()) {
						echo json_encode(
							array(
								"status"=> "failed", 
								"message"=> $insert_stmt->error
							)
						);
					}
					else{
						$insert_stmt->close();
						echo json_encode(
							array(
								"status"=> "success", 
								"message"=> "Reactivated"
							)
						);
					}
				}
	
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
