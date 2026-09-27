<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/PermissionService.php';

class PermissionController extends BaseController {

    private $permissionService;

    public function __construct($db) {
        parent::__construct($db);
        $this->permissionService = new PermissionService($db, $this->username);
    }

    public function getAll() {
        echo json_encode($this->permissionService->getAll($_POST));
        exit();
    }

    public function create() {
        try {
            $result = $this->permissionService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function update() {
        try {
            $this->permissionService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function delete() {
        $id   = $_POST['permissionID'] ?? null;
        $type = $_POST['type'] ?? '';
        if (!$id) { $this->failed('Missing ID'); return; }
        try {
            $this->permissionService->delete($id, $type);
            $this->success('Deleted');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function get() {
        $id = $this->getRequiredPost('id');
        try {
            $row = $this->permissionService->get($id);
            $row ? $this->success($row) : $this->failed('Record not found');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function insertDefaults() {
        try {
            $result = $this->permissionService->insertDefaults();
            $this->success("Inserted: {$result['inserted']}, Updated: {$result['updated']}, Skipped: {$result['skipped']}");
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
}
?>
