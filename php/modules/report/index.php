<?php
session_start();
require_once '../../db_connect.php';
require_once '../../classes/ReportController.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'failed', 'message' => 'Unauthorized']);
    exit();
}

$controller = new ReportController($db);
$action = $_POST['action'] ?? $_GET['action'] ?? 'filter';

switch ($action) {
    case 'filter':
        $controller->handleFilter();
        break;
    case 'exportExcel':
        $controller->handleExportExcel();
        break;
    case 'exportPdf':
        $controller->handleExportPdf();
        break;
    default:
        echo json_encode(['status' => 'failed', 'message' => 'Invalid action']);
}
?>
