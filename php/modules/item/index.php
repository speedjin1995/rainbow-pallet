<?php
session_start();
require_once '../../db_connect.php';
require_once '../../classes/ItemController.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$controller = new ItemController($db, $_SESSION['username']);

switch ($action) {
    case 'create':    $controller->create(); break;
    case 'update':    $controller->update(); break;
    case 'delete':    $controller->delete(); break;
    case 'get':       $controller->get(); break;
    case 'getAll':    $controller->getAll(); break;
    case 'reactivate': $controller->reactivate(); break;
    case 'upload':    $controller->upload(); break;
    case 'downloadTemplate': $controller->downloadTemplate(); break;
    case 'list':      $controller->handleList(); break;
    default:
        echo json_encode(['status' => 'failed', 'message' => 'Invalid action']);
}
?>
