<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../classes/VehicleController.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'save';
$controller = new VehicleController($db);

switch ($action) {
    case 'filter': $controller->handleFilter(); break;
    case 'get':    $controller->handleGet();    break;
    case 'delete': $controller->handleDelete(); break;
    case 'upload': $controller->handleUpload(); break;
    case 'list':   $controller->handleList();   break;
    default:       $controller->handleSave();   break;
}
?>
