<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ItemService.php';

/**
 * Item Controller
 * Handles all CRUD operations for Items (Products)
 */
class ItemController extends BaseController {
    
    private $itemService;
    
    public function __construct($db, $username) {
        parent::__construct($db, $username);
        $this->itemService = new ItemService($db, $username);
    }
    
    /**
     * Get all items (for DataTables)
     */
    public function getAll() {
        $result = $this->itemService->getAll($_POST);
        echo json_encode($result);
        exit();
    }
    
    /**
     * Create new item
     */
    public function create() {
        try {
            $result = $this->itemService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Update existing item
     */
    public function update() {
        try {
            $result = $this->itemService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Delete (soft delete) item
     */
    public function delete() {
        $id = $this->getPost('id');
        $type = $this->getPost('type');
        
        try {
            $this->itemService->delete($id, $type);
            $this->success('Deleted Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Reactivate item
     */
    public function reactivate() {
        $id = $this->getRequiredPost('id');
        
        try {
            $this->itemService->reactivate($id);
            $this->success('Reactivated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Get single item by ID
     */
    public function get() {
        $id = $this->getRequiredPost('id');
        
        try {
            $row = $this->itemService->get($id);
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
     * Upload items from Excel
     */
    public function upload() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $result = $this->itemService->upload($data);
            
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
