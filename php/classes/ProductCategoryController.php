<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ProductCategoryService.php';

class ProductCategoryController extends BaseController {
    protected $table = 'Product_Categories';
    private $service;

    public function __construct($db) {
        parent::__construct($db);
        $this->service = new ProductCategoryService($db, $this->username);
    }

    public function handleFilter() {
        echo json_encode($this->service->filter($_POST));
    }

    public function handleGet() {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->failed('Missing Attribute');
        }
        $data = $this->service->get($id);
        if ($data === null) {
            $this->failed('Record not found');
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    public function handleSave() {
        $categoryName = $_POST['categoryName'] ?? null;
        if (empty($categoryName)) {
            $this->failed('Please fill in all the fields');
        }
        $f = [
            'id'                => $this->getPost('id'),
            'company'           => $this->getPost('company'),
            'categoryName'      => $this->getPost('categoryName'),
            'postToSql'         => $this->getPost('postToSql'),
            'transactionStatus' => isset($_POST['transactionStatus']) ? $_POST['transactionStatus'] : [],
        ];
        try {
            $this->db->begin_transaction();
            $this->service->save($f);
            $this->db->commit();
            $message = !empty($f['id']) ? 'Updated Successfully!!' : 'Added Successfully!!';
            $this->success($message);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }

    public function handleDelete() {
        $id = $_POST['id'] ?? null;
        $type = $_POST['type'] ?? null;
        if (!$id) {
            $this->failed('Please fill in all the fields');
        }
        try {
            $this->db->begin_transaction();
            $this->service->delete($id, $type);
            $this->db->commit();
            $this->success('Deleted Successfully!!');
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }

    public function handleReactivate() {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->failed('Missing Attribute');
        }
        try {
            $this->db->begin_transaction();
            $this->service->reactivate($id);
            $this->db->commit();
            $this->success('Reactivated Successfully!!');
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }

    public function handleUpload() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data)) {
            $this->failed('No data provided');
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
