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
        
        try {
            $result = $this->projectService->upload($data);
            
            if (count($result['errors']) > 0 && $result['successCount'] > 0) {
                echo json_encode(['status' => 'error', 'message' => $result['errors']]);
                exit();
            } elseif (count($result['errors']) > 0) {
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
