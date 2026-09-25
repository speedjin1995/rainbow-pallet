<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/VehicleService.php';

class VehicleController extends BaseController {
    protected $table = 'Vehicle';
    private $service;

    public function __construct($db) {
        parent::__construct($db);
        $this->service = new VehicleService($db, $this->username);
    }

    public function handleFilter() {
        echo json_encode($this->service->filter($_POST));
    }

    public function handleGet() {
        $id   = $_POST['userID'] ?? null;
        $type = $_POST['type']   ?? null;
        if (!$id) {
            $this->failed('Missing Attribute');
        }
        try {
            $data = $this->service->get($id, $type);
            if ($data === null) {
                $this->failed('Record not found');
            }
            echo json_encode(['status' => 'success', 'message' => $data]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function handleSave() {
        $vehicleNo = $_POST['vehicleNo'] ?? null;
        if (empty($vehicleNo)) {
            $this->failed('Please fill in all the fields');
        }
        $f = [
            'vehicleId'      => $this->getPost('id'),
            'company'        => $this->getPost('company'),
            'vehicleNo'      => $this->getPost('vehicleNo'),
            'vehicleWeight'  => $this->getPost('vehicleWeight', 0),
            'transporter'    => $this->getPost('transporter'),
            'transporterCode'=> $this->getPost('transporterCode'),
            'customer'       => $this->getPost('customer'),
            'customerCode'   => $this->getPost('customerCode'),
            'supplier'       => $this->getPost('supplier'),
            'supplierCode'   => $this->getPost('supplierCode'),
        ];
        try {
            $this->db->begin_transaction();
            if (!empty($f['vehicleId'])) {
                $this->service->update($f);
                $message = 'Updated Successfully!!';
            } else {
                $this->service->save($f);
                $message = 'Added Successfully!!';
            }
            $this->db->commit();
            $this->success($message);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }

    public function handleDelete() {
        $id = $_POST['userID'] ?? null;
        if (!$id) {
            $this->failed('Please fill in all the fields');
        }
        $isMulti = isset($_POST['type']) && $_POST['type'] === 'MULTI';
        try {
            $this->service->delete($id, $isMulti);
            $this->success('Deleted');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function handleUpload() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data)) {
            $this->failed('Please fill in all the fields');
        }

        // Determine company on the backend - never trust frontend value for restricted users
        if (hasModulePermission('Master Data', 'Vehicles', ['view_all_companies'])) {
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

        $errors = $this->service->upload($data, $companyId);
        if (!empty($errors)) {
            echo json_encode(['status' => 'error', 'message' => $errors]);
        } else {
            $this->success('Added Successfully!!');
        }
    }
}
?>
