<?php
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['id'])){
	echo '<script type="text/javascript">location.href = "../login.html";</script>'; 
}

if(isset($_POST['indicator'], $_POST['serialPort'], $_POST['serialPortBaudRate'], $_POST['serialPortDataBits'], $_POST['serialPortParity'], $_POST['serialPortStopBits'])){
	$indicator = filter_input(INPUT_POST, 'indicator', FILTER_SANITIZE_STRING);
	$serialPort = filter_input(INPUT_POST, 'serialPort', FILTER_SANITIZE_STRING);
	$serialPortBaudRate = filter_input(INPUT_POST, 'serialPortBaudRate', FILTER_SANITIZE_STRING);
	$serialPortDataBits = filter_input(INPUT_POST, 'serialPortDataBits', FILTER_SANITIZE_STRING);
	$serialPortParity = filter_input(INPUT_POST, 'serialPortParity', FILTER_SANITIZE_STRING);
	$serialPortStopBits = filter_input(INPUT_POST, 'serialPortStopBits', FILTER_SANITIZE_STRING);
	$id = $_SESSION['id'];
	
	if ($stmt2 = $db->prepare("UPDATE Port SET indicator=?, com_port=?, bits_per_second=?, data_bits=?, parity=?, stop_bits=? WHERE weighind_id=?")) {
		$stmt2->bind_param('sssssss', $indicator, $serialPort, $serialPortBaudRate, $serialPortDataBits, $serialPortParity, $serialPortStopBits, $id);
		
		if($stmt2->execute()){
			$stmt2->close();
			$db->close();

			echo '<script type="text/javascript">alert("Your port setup is updated successfully!");</script>'; 
			header("location: ../portSetup.php");
		} 
		else{
			echo '<script type="text/javascript">alert("'.$stmt->error.'");</script>'; 
			header("location: ../portSetup.php");
		}
	} 
	else{
		echo '<script type="text/javascript">alert("Something went wrong!");</script>'; 
		header("location: ../portSetup.php");
	}
} 
else{
	echo '<script type="text/javascript">alert("Something went wrong!");</script>'; 
	header("location: ../portSetup.php");
}
?>
