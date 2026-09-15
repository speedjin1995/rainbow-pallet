<?php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once 'classes/WeightController.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_POST['transactionId'], $_POST['transactionStatus'], $_POST['weightType'], $_POST['transactionDate'], $_POST['grossIncoming'], $_POST['grossIncomingDate'], $_POST['manualWeight'], $_POST['plantCode'], $_POST['plant'], $_POST['companyId'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Please fill in all the fields']);
    exit;
}

$controller = new WeightController($db);
$controller->handle();
?>
