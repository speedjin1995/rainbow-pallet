<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/LocationService.php';

class LocationController extends BaseController {

    private $locationService;

    public function __construct($db) {
        parent::__construct($db);
        $this->locationService = new LocationService($db, $this->username);
    }

    public function handleList() {
        $companyId = intval($this->getPost('company'));
        if ($companyId <= 0) {
            $this->failed('Invalid company');
        }
        $list = $this->locationService->getListByCompany($companyId);
        echo json_encode(['status' => 'success', 'data' => $list]);
        exit();
    }

    /**
     * Get all locations (for DataTables)
     */
    public function getAll() {
        $result = $this->locationService->getAll($_POST);
        echo json_encode($result);
        exit();
    }

    /**
     * Create new location
     */
    public function create() {
        try {
            $result = $this->locationService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    /**
     * Update existing location
     */
    public function update() {
        try {
            $this->locationService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    /**
     * Delete (soft delete) location
     */
    public function delete() {
        $id = $this->getRequiredPost('id');

        try {
            $this->locationService->delete($id);
            $this->success('Deleted');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    /**
     * Get single location by ID
     */
    public function get() {
        $id = $this->getRequiredPost('id');
        $withPort = isset($_POST['port']) && $_POST['port'] === 'Y';

        try {
            $row = $this->locationService->get($id, $withPort);
            if ($row) {
                $this->success($row);
            } else {
                $this->failed('Record not found');
            }
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    /**
     * Save port setup
     */
    public function savePortSetup() {
        try {
            $this->locationService->savePortSetup($_POST);
            $this->success('Port setup saved successfully!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
}
?>
