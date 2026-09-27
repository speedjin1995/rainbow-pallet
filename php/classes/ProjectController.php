<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ProjectService.php';

/**
 * Project Controller
 * Handles all CRUD operations for Projects
 */
class ProjectController extends BaseController {
    
    private $projectService;
    
    public function __construct($db, $username) {
        parent::__construct($db, $username);
        $this->projectService = new ProjectService($db, $username);
    }
    
    public function handleList() {
        $companyId = intval($this->getPost('company'));
        if ($companyId <= 0) {
            $this->failed('Invalid company');
        }
        $list = $this->projectService->getListByCompany($companyId);
        echo json_encode(['status' => 'success', 'data' => $list]);
        exit();
    }

    /**
     * Get all projects (for DataTables)
     */
    public function getAll() {
        $result = $this->projectService->getAll($_POST);
        echo json_encode($result);
        exit();
    }
    
    /**
     * Create new project
     */
    public function create() {
        try {
            $result = $this->projectService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Update existing project
     */
    public function update() {
        try {
            $result = $this->projectService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Delete (soft delete) project
     */
    public function delete() {
        $id = $this->getPost('id');
        $type = $this->getPost('type');
        
        try {
            $this->projectService->delete($id, $type);
            $this->success('Deleted Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Reactivate project
     */
    public function reactivate() {
        $id = $this->getRequiredPost('id');
        
        try {
            $this->projectService->reactivate($id);
            $this->success('Reactivated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Get single project by ID
     */
    public function get() {
        $id = $this->getRequiredPost('id');
        
        try {
            $row = $this->projectService->get($id);
            if ($row) {
                $this->success('Record found', ['data' => $row]);
            } else {
                $this->failed('Record not found');
            }
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Upload projects from Excel
     */
    public function upload() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Determine company on the backend - never trust frontend value for restricted users
        if (hasModulePermission('Master Data', 'Projects', ['view_all_companies'])) {
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
            $result = $this->projectService->upload($data, $companyId);
            
            if (count($result['errors']) > 0) {
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
