<?php
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['id'])){
	echo '<script type="text/javascript">location.href = "../login.php";</script>'; 
} else{
	$id = $_SESSION['id'];
}

if(isset($_POST['userName'], $_POST['userEmail'], $_POST['language'])){
	$name = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_STRING);
	$username = filter_input(INPUT_POST, 'userEmail', FILTER_SANITIZE_STRING);
	$language = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_STRING);

	$_SESSION['language'] = $language;
	
	if ($stmt2 = $db->prepare("UPDATE Users SET username=?, useremail=?, languages=? WHERE id=?")) {
		$stmt2->bind_param('ssss', $name, $username, $language, $id);
		
		if($stmt2->execute()){
			$stmt2->close();
			$db->close();
			echo '<script type="text/javascript">alert("Your Email / Username is updated successfully!");</script>'; 
			header("location: ../myProfile.php");
		} else{
			echo '<script type="text/javascript">alert("Failed to update profile!");</script>'; 
			header("location: ../myProfile.php");
		}
	} 
	else{
		echo '<script type="text/javascript">alert("Failed to prepare statements!");</script>'; 
		header("location: ../myProfile.php");
	}
} 
else{
	echo '<script type="text/javascript">alert("Please fill in all fields!");</script>'; 
	header("location: ../myProfile.php");
}
?>
