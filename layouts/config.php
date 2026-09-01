<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
//define('DB_SERVER', 'srv597.hstgr.io');
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u664110560_'.$_SESSION["company"]);
define('DB_PASSWORD', '@Sync5500');
define('DB_NAME', 'u664110560_'.$_SESSION["company"]);

/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$gmailid = ''; // YOUR gmail email
$gmailpassword = ''; // YOUR gmail password
$gmailusername = ''; // YOUR gmail User name

?>
