<?php
session_start();
require_once '../../db_connect.php';
require_once '../../classes/CustomerController.php';

$controller = new CustomerController($db);
$action = $_POST['action'] ?? $_GET['action'] ?? 'filter';

switch ($action) {
    case 'filter':
        $controller->handleFilter();
        break;
    case 'get':
        $controller->handleGet();
        break;
    case 'save':
        $controller->handleSave();
        break;
    case 'delete':
        $controller->handleDelete();
        break;
    case 'upload':
        $controller->handleUpload();
        break;
    default:
        echo json_encode(['status' => 'failed', 'message' => 'Invalid action']);
}
?>
