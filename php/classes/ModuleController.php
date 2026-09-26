<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ModuleService.php';

class ModuleController extends BaseController {

    private $moduleService;

    public function __construct($db) {
        parent::__construct($db);
        $this->moduleService = new ModuleService($db, $this->username);
    }

    public function getAll() {
        echo json_encode($this->moduleService->getAll($_POST));
        exit();
    }

    public function create() {
        try {
            $result = $this->moduleService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function update() {
        try {
            $this->moduleService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function delete() {
        $id   = $_POST['moduleID'] ?? null;
        $type = $_POST['type'] ?? '';
        if (!$id) { $this->failed('Missing ID'); return; }
        try {
            $this->moduleService->delete($id, $type);
            $this->success('Deleted');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function get() {
        $id = $this->getRequiredPost('id');
        try {
            $row = $this->moduleService->get($id);
            $row ? $this->success($row) : $this->failed('Record not found');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function insertDefaults() {
        try {
            $result = $this->moduleService->insertDefaults();
            $this->success("Inserted: {$result['inserted']}, Skipped: {$result['skipped']}");
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
}
?>
