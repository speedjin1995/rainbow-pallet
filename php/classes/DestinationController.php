<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/DestinationService.php';

class DestinationController extends BaseController {

    private $destinationService;

    public function __construct($db) {
        parent::__construct($db);
        $this->destinationService = new DestinationService($db, $this->username);
    }

    /**
     * Get all destinations (for DataTables)
     */
    public function getAll() {
        $result = $this->destinationService->getAll($_POST);
        echo json_encode($result);
        exit();
    }

    /**
     * Create new destination
     */
    public function create() {
        try {
            $result = $this->destinationService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    /**
     * Update existing destination
     */
    public function update() {
        try {
            $this->destinationService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    /**
     * Delete (soft delete) destination
     */
    public function delete() {
        $id   = $this->getPost('id');
        $type = $this->getPost('type');

        try {
            $this->destinationService->delete($id, $type);
            $this->success('Deleted');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    /**
     * Get single destination by ID
     */
    public function get() {
        $id = $this->getRequiredPost('id');

        try {
            $row = $this->destinationService->get($id);
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
     * Upload destinations from Excel
     */
    public function upload() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Determine company on the backend - never trust frontend value for restricted users
        if (hasModulePermission('Master Data', 'Destination', ['view_all_companies'])) {
            $companyId = isset($_GET['company']) ? intval($_GET['company']) : 0;
        } else {
            $companyId = isset($_SESSION['company_id']) ? intval($_SESSION['company_id']) : 0;
        }

        if ($companyId <= 0) {
            $this->failed('Please select a company');
        }

        $company = searchCompanyById($companyId, $this->db);
        if (empty($company) || $company['status'] != '0') {
            $this->failed('Company not found');
        }

        try {
            $result = $this->destinationService->upload($data, $companyId);

            if (!empty($result['errors'])) {
                echo json_encode(['status' => 'error', 'message' => $result['errors']]);
                exit();
            } else {
                $this->success("{$result['successCount']} records imported successfully");
            }
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
}
?>
