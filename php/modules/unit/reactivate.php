<?php
session_start();
require_once '../../db_connect.php';
require_once '../../classes/UnitController.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized']);
    exit();
}

$controller = new UnitController($db);
$controller->reactivate();
?>
