<?php
session_start();
require_once '../../db_connect.php';
require_once '../../classes/ProductCategoryController.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new ProductCategoryController($db);

switch ($action) {
    case 'filter':
    case 'getAll':
        $controller->handleFilter();
        break;
    case 'get':
        $controller->handleGet();
        break;
    case 'create':
    case 'update':
    case 'save':
        $controller->handleSave();
        break;
    case 'delete':
        $controller->handleDelete();
        break;
    case 'checkItems':
        $controller->handleCheckItems();
        break;
    case 'reactivate':
        $controller->handleReactivate();
        break;
    case 'upload':
        $controller->handleUpload();
        break;
    case 'list':
        $controller->handleList();
        break;
    default:
        echo json_encode(['status' => 'failed', 'message' => 'Invalid action']);
}
?>
