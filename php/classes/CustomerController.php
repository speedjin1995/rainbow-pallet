<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/CustomerService.php';

class CustomerController extends BaseController {
    protected $table = 'Customer';
    private $service;

    public function __construct($db) {
        parent::__construct($db);
        $this->service = new CustomerService($db, $this->username);
    }

    public function handleFilter() {
        echo json_encode($this->service->filter($_POST));
    }

    public function handleGet() {
        $id = $_POST['userID'] ?? null;
        if (!$id) {
            $this->failed('Missing Attribute');
        }
        $data = $this->service->get($id);
        if ($data === null) {
            $this->failed('Record not found');
        }
        echo json_encode(['status' => 'success', 'message' => $data]);
    }

    public function handleSave() {
        $customerCode = $_POST['customerCode'] ?? null;
        if (empty($customerCode)) {
            $this->failed('Please fill in all the fields');
        }
        $f = [
            'customerId'   => $this->getPost('id'),
            'customerCode' => $this->getPost('customerCode'),
            'companyRegNo' => $this->getPost('companyRegNo'),
            'newRegNo'     => $this->getPost('newRegNo'),
            'companyName'  => $this->getPost('companyName'),
            'addressLine1' => $this->getPost('addressLine1'),
            'addressLine2' => $this->getPost('addressLine2'),
            'addressLine3' => $this->getPost('addressLine3'),
            'phoneNo'      => $this->getPost('phoneNo'),
            'faxNo'        => $this->getPost('faxNo'),
            'contactName'  => $this->getPost('contactName'),
            'icNo'         => $this->getPost('icNo'),
            'tinNo'        => $this->getPost('tinNo'),
        ];
        try {
            $this->db->begin_transaction();
            $this->service->save($f);
            $this->db->commit();
            $message = !empty($f['customerId']) ? 'Updated Successfully!!' : 'Added Successfully!!';
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
        $errors = $this->service->upload($data);
        if (!empty($errors)) {
            echo json_encode(['status' => 'error', 'message' => $errors]);
        } else {
            $this->success('Added Successfully!!');
        }
    }
}
?>
