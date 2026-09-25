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
            'company' => $this->getPost('company'),
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

        // Determine company on the backend - never trust frontend value for restricted users
        if (hasModulePermission('Master Data', 'Units', ['view_all_companies'])) {
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
            $result = $this->service->upload($data, $companyId);

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
