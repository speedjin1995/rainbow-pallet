<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/UnitService.php';

class UnitController extends BaseController {
    
    private $service;
    
    public function __construct($db, $username) {
        parent::__construct($db, $username);
        $this->service = new UnitService($db, $username);
    }
    
    public function handleFilter() {
        $result = $this->service->filter($_POST);
        echo json_encode($result);
        exit();
    }
    
    public function handleGet() {
        $id = $this->getRequiredPost('id');
        try {
            $row = $this->service->get($id);
            if ($row) {
                $this->success('Record found', ['data' => $row]);
            } else {
                $this->failed('Record not found');
            }
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    public function handleSave() {
        $f = [
            'id' => $this->getPost('id'),
            'unit' => $this->getRequiredPost('unit')
        ];
        
        try {
            $result = $this->service->save($f);
            $message = empty($f['id']) ? 'Added Successfully!!' : 'Updated Successfully!!';
            $this->success($message, ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    public function handleDelete() {
        $id = $this->getPost('id');
        $type = $this->getPost('type');
        
        try {
            $this->service->delete($id, $type);
            $this->success('Deleted Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    public function handleReactivate() {
        $id = $this->getRequiredPost('id');
        
        try {
            $this->service->reactivate($id);
            $this->success('Reactivated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    public function handleUpload() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data)) {
            $this->failed('No data provided');
        }
        
        try {
            $result = $this->service->upload($data);
            
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
