<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../classes/SawnTimberController.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'save';
$controller = new SawnTimberController($db);

switch ($action) {
    case 'list':        $controller->handleList(); break;
    case 'get':         $controller->handleGet(); break;
    case 'getDetails':  $controller->handleGetDetails(); break;
    case 'getWeighing': $controller->handleGetWeighing(); break;
    case 'delete':      $controller->handleDelete(); break;
    case 'export':      $controller->handleExport(); break;
    default:            $controller->handleSave(); break;
}

$db->close();
?>
