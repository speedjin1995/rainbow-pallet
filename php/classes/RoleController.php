<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/RoleService.php';

class RoleController extends BaseController {

    private $roleService;

    public function __construct($db) {
        parent::__construct($db);
        $this->roleService = new RoleService($db, $this->username);
    }

    public function getAll() {
        echo json_encode($this->roleService->getAll($_POST));
        exit();
    }

    public function create() {
        try {
            $result = $this->roleService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function update() {
        try {
            $this->roleService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function delete() {
        $id   = $_POST['roleID'] ?? null;
        $type = $_POST['type'] ?? '';
        if (!$id) { $this->failed('Missing ID'); return; }
        try {
            $this->roleService->delete($id, $type);
            $this->success('Deleted');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function get() {
        $id = $this->getRequiredPost('id');
        try {
            $row = $this->roleService->get($id);
            $row ? $this->success($row) : $this->failed('Record not found');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function getRolePermissions() {
        $id = $this->getRequiredPost('id');
        try {
            $rows = $this->roleService->getRolePermissions($id);
            $this->success($rows);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function saveRolePermissions() {
        try {
            $this->roleService->saveRolePermissions($_POST);
            $this->success('Permissions Updated Successfully');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
}
?>
