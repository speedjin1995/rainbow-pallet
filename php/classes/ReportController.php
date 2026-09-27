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

    public function handleFilterAuditLog() {
        if (!hasModulePermission('Reports', 'Audit Log', ['view', 'create', 'edit'])) {
            $this->failed('Unauthorized');
        }

        try {
            echo json_encode($this->service->filterAuditLog($_POST));
        } catch (mysqli_sql_exception $e) {
            error_log('Audit log filter: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }
    }

    public function handleGetSawnTimberLogDetails() {
        if (!hasModulePermission('Reports', 'Audit Log', ['view', 'create', 'edit'])) {
            $this->failed('Unauthorized');
        }

        $headerLogId = intval($this->getRequiredPost('userID'));

        try {
            $data = $this->service->getSawnTimberLogDetails($headerLogId);
        } catch (mysqli_sql_exception $e) {
            error_log('Sawn timber log details: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }

        if ($data === null) {
            $this->failed('Record not found');
        }
        $this->success($data);
    }

    public function handleGetItemPriceLogDetails() {
        if (!hasModulePermission('Reports', 'Audit Log', ['view', 'create', 'edit'])) {
            $this->failed('Unauthorized');
        }

        $productLogId = intval($this->getRequiredPost('userID'));

        try {
            $data = $this->service->getItemPriceLogDetails($productLogId);
        } catch (mysqli_sql_exception $e) {
            error_log('Item price log details: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }

        if ($data === null) {
            $this->failed('Record not found');
        }
        $this->success($data);
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
