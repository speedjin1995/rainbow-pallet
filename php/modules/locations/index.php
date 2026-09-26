<?php
session_start();
require_once '../../db_connect.php';
require_once '../../classes/LocationController.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new LocationController($db);

switch ($action) {
    case 'create':  $controller->create(); break;
    case 'update':  $controller->update(); break;
    case 'delete':  $controller->delete(); break;
    case 'get':     $controller->get(); break;
    case 'getAll':  $controller->getAll(); break;
    case 'savePortSetup': $controller->savePortSetup(); break;
    case 'list':    $controller->handleList(); break;
        echo json_encode(['status' => 'failed', 'message' => 'Invalid action']);
}
?>
