<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/DeliveryOrderService.php';

class DeliveryOrderController extends BaseController {
    protected $table = 'Weight';
    private $service;

    public function __construct($db) {
        parent::__construct($db);
        $this->service = new DeliveryOrderService($db, $this->username);
    }

    public function handleFilter() {
        $this->requirePermission('view');
        try {
            echo json_encode($this->service->filter($_POST));
        } catch (mysqli_sql_exception $e) {
            error_log('DeliveryOrder filter: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }
    }

    public function handleExport() {
        $this->requirePermission('export');
        try {
            $export = $this->service->export($_GET);
        } catch (mysqli_sql_exception $e) {
            error_log('DeliveryOrder export: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }

        // Headers for download
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"{$export['fileName']}\"");
        echo $export['content'];
        exit;
    }

    public function handlePost() {
        $this->requirePermission('post_to_sql');
        try {
            $result = $this->service->post($_POST);
            $this->response($result['status'], $result['message'], array_diff_key($result, ['status' => 1, 'message' => 1]));
        } catch (mysqli_sql_exception $e) {
            error_log('DeliveryOrder post: ' . $e->getMessage());
            $this->failed('Something went wrong');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    private function requirePermission($permission) {
        if (!hasModulePermission('Accounting', 'Delivery Order', [$permission])) {
            $this->failed('No permission');
        }
    }
}
?>
