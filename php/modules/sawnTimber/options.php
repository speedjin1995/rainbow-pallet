<?php
session_start();
require_once '../../db_connect.php';
require_once 'helpers.php';

echo json_encode(array(
    "status" => "success",
    "message" => array(
        "suppliers" => getSawnTimberOptions($db, 'Supplier', 'name'),
        "species" => getSawnTimberOptions($db, 'Sawn_Timber_Species', 'name')
    )
));

$db->close();
?>
