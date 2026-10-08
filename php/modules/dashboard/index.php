<?php
session_start();
require_once __DIR__ . '/../../db_connect.php';
require_once __DIR__ . '/../../requires/permissions.php';
require_once __DIR__ . '/../../classes/DashboardController.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? 'summary';
$controller = new DashboardController($db);

switch ($action) {
    case 'summary': $controller->handleSummary(); break;
    default:
        echo json_encode(['status' => 'failed', 'message' => 'Invalid action']);
}
?>
