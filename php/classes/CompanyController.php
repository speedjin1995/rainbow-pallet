<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/CompanyService.php';
require_once __DIR__ . '/../services/DocumentNumberService.php';

class CompanyController extends BaseController {

    private $companyService;
    private $documentNumberService;

    public function __construct($db) {
        parent::__construct($db);
        $this->companyService = new CompanyService($db, $this->username);
        $this->documentNumberService = new DocumentNumberService($db, $this->username);
    }

    /**
     * Document number formats of a company (one row per transaction status)
     */
    public function getDocumentNumbers() {
        if (!hasModulePermission('Master Data', 'Companies', ['edit'])) {
            $this->failed('Unauthorized');
        }

        try {
            $rows = $this->documentNumberService->getByCompany(intval($this->getRequiredPost('companyId')), $this->getRequiredPost('documentType'));
        } catch (InvalidArgumentException $e) {
            $this->failed($e->getMessage());
        } catch (Exception $e) {
            error_log('Get document numbers: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }

        if ($rows === null) {
            $this->failed('Record not found');
        }
        $this->success($rows);
    }

    /**
     * Save a company's document number formats
     */
    public function saveDocumentNumbers() {
        if (!hasModulePermission('Master Data', 'Companies', ['edit'])) {
            $this->failed('Unauthorized');
        }

        $companyId = intval($this->getRequiredPost('companyId'));
        $documentType = $this->getRequiredPost('documentType');
        $rows = json_decode($this->getRequiredPost('data'), true);

        try {
            $this->documentNumberService->saveByCompany($companyId, $documentType, $rows);
            $this->success('Updated Successfully!!');
        } catch (InvalidArgumentException $e) {
            $this->failed($e->getMessage());
        } catch (Exception $e) {
            error_log('Save document numbers: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }
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

    /**
     * Switch company for current session
     */
    public function switchCompany() {
        $companyId = $this->getRequiredPost('companyId');

        try {
            $_SESSION['company_id'] = $companyId;
            
            $this->success('Company switched successfully');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
}
?>
