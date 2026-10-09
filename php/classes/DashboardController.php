<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/DashboardService.php';

class DashboardController extends BaseController {
    protected $table = 'Weight';
    private $service;

    public function __construct($db) {
        parent::__construct($db);
        $this->service = new DashboardService($db, $this->username);
    }

    public function handleSummary() {
        if (!hasModulePermission('Dashboard', 'Dashboard', ['view'])) {
            $this->failed('Unauthorized');
        }

        try {
            $this->success('', $this->service->summary($_POST));
        } catch (mysqli_sql_exception $e) {
            error_log('Dashboard summary: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }
    }
}
?>
