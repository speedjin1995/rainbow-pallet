<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/CompanyService.php';

class CompanyController extends BaseController {
    
    private $companyService;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->companyService = new CompanyService($db, $this->username);
    }
    
    /**
     * Get all companies (for DataTables)
     */
    public function getAll() {
        $result = $this->companyService->getAll($_POST);
        echo json_encode($result);
        exit();
    }
    
    /**
     * Create new company
     */
    public function create() {
        try {
            $result = $this->companyService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Update existing company
     */
    public function update() {
        try {
            $result = $this->companyService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Delete (soft delete) company
     */
    public function delete() {
        $id = $this->getPost('id');
        $type = $this->getPost('type');
        
        try {
            $this->companyService->delete($id, $type);
            $this->success('Deleted');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Get single company by ID
     */
    public function get() {
        $id = $this->getRequiredPost('id');
        
        try {
            $row = $this->companyService->get($id);
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
     * Upload companies from Excel
     */
    public function upload() {
        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $result = $this->companyService->upload($data);

            if (!empty($result['errors']) && $result['successCount'] > 0) {
                echo json_encode(['status' => 'error', 'message' => $result['errors']]);
                exit();
            } elseif (!empty($result['errors'])) {
                $this->failed(implode(', ', $result['errors']));
            } else {
                $this->success("{$result['successCount']} records imported successfully");
            }
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
}
?>
