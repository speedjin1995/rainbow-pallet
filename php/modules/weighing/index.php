<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../requires/permissions.php';
require_once __DIR__ . '/../../classes/WeightController.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? 'save';
$controller = new WeightController($db);

switch ($action) {
    case 'print':               $controller->handlePrint(); break;
    case 'filterWeight':        $controller->handleFilterWeight(); break;
    case 'filterEmptyContainer':$controller->handleFilterEmptyContainer(); break;
    case 'getWeight':           $controller->handleGetWeight(); break;
    case 'getEmptyContainer':   $controller->handleGetEmptyContainer(); break;
    case 'getContainers':       $controller->handleGetContainers(); break;
    case 'delete':              $controller->handleDelete(); break;
    default:
        if (!isset($_POST['transactionId'], $_POST['transactionStatus'], $_POST['weightType'], $_POST['transactionDate'], $_POST['grossIncoming'], $_POST['grossIncomingDate'], $_POST['manualWeight'], $_POST['plantCode'], $_POST['plant'], $_POST['companyId'])) {
            echo json_encode(['status' => 'failed', 'message' => 'Please fill in all the fields']);
            exit;
        }
        $controller->handle();
}
?>
