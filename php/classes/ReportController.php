<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ReportService.php';

class ReportController extends BaseController {
    protected $table = 'Weight';
    private $service;

    public function __construct($db) {
        parent::__construct($db);
        $this->service = new ReportService($db, $this->username);
    }

    public function handleFilter() {
        try {
            echo json_encode($this->service->filter($_POST));
        } catch (mysqli_sql_exception $e) {
            error_log('Report filter: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }
    }

    public function handleExportExcel() {
        try {
            $export = $this->service->exportExcel($_GET);
        } catch (mysqli_sql_exception $e) {
            error_log('Report excel export: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }

        // Headers for download
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"{$export['fileName']}\"");
        echo $export['content'];
        exit;
    }

    public function handleExportPdf() {
        try {
            $this->success($this->service->exportPdf($_POST));
        } catch (mysqli_sql_exception $e) {
            error_log('Report pdf export: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }
    }
}
?>
