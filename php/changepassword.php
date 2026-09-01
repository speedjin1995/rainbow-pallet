<?php
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['id'])){
	echo '<script type="text/javascript">location.href = "../login.php";</script>'; 
} else{
	$id = $_SESSION['id'];;
}

if(isset($_POST['oldPassword'], $_POST['newPassword'], $_POST['confirmPassword'])){
	$oldPassword = filter_input(INPUT_POST, 'oldPassword', FILTER_SANITIZE_STRING);
	$newPassword = filter_input(INPUT_POST, 'newPassword', FILTER_SANITIZE_STRING);
	$confirmPassword = filter_input(INPUT_POST, 'confirmPassword', FILTER_SANITIZE_STRING);
	
	$stmt = $db->prepare("SELECT * from Users where id = ?");
	$stmt->bind_param('s', $id);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if(($row = $result->fetch_assoc()) !== null){
		if (password_verify($oldPassword, $row['password'])){
			$param_password = password_hash($newPassword, PASSWORD_DEFAULT); // Creates a password hash
			$stmt2 = $db->prepare("UPDATE Users SET password = ? WHERE id = ?");
			$stmt2->bind_param('ss', $param_password, $id);
			
			if($stmt2->execute()){
    			$stmt2->close();
    			$db->close();

				echo '<script type="text/javascript">alert("Update successfully!");window.location.href = "../ChangePassword.php";</script>'; 
				//header("location: ../ChangePassword.php");
    		} else{
    		    echo '<script type="text/javascript">alert("Failed to update due to "'.$stmt2->error.');window.location.href = "../ChangePassword.php";</script>'; 
				//header("location: ../ChangePassword.php");
    		}
		} else{
		    echo '<script type="text/javascript">alert("Old password is not matched");window.location.href = "../ChangePassword.php";</script>'; 
			//header("location: ../ChangePassword.php");
		}
	} else{
	    echo '<script type="text/javascript">alert("Data retrieve failed");window.location.href = "../ChangePassword.php";</script>'; 
		//header("location: ../ChangePassword.php");
	}
} else{
    echo '<script type="text/javascript">alert("Please fill in all the fields");window.location.href = "../ChangePassword.php";</script>'; 
	//header("location: ../ChangePassword.php");
}
?>
