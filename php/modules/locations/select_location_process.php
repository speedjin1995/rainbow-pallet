<?php
session_start();

// User must login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../../../login.php");
    exit;
}

// Validate form
if(!isset($_POST['location_id']) || empty($_POST['location_id'])){
    header("location: ../../../selectLocation.php?error=choose_location");
    exit;
}

$location_id = intval($_POST['location_id']);
$_SESSION['location_id'] = $location_id;

// Redirect to app
if($_SESSION['package'] == 'Lite'){
    // Redirect user to welcome page
    header("location: ../../../simple.php");
}
else{
    // Redirect user to welcome page
    header("location: ../../../index.php");
}