<?php
session_start();
require_once '../../db_connect.php';
require_once '../../classes/DeliveryOrderController.php';

$controller = new DeliveryOrderController($db);
$action = $_POST['action'] ?? $_GET['action'] ?? 'filter';

switch ($action) {
    case 'filter':
        $controller->handleFilter();
        break;
    case 'export':
        $controller->handleExport();
        break;
    case 'post':
        $controller->handlePost();
        break;
    case 'getPriceSuggestions':
        $controller->handleGetPriceSuggestions();
        break;
    case 'updatePrices':
        $controller->handleUpdatePrices();
        break;
    default:
        echo json_encode(['status' => 'failed', 'message' => 'Invalid action']);
}
?>
