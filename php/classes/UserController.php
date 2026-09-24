<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/UserService.php';

class UserController extends BaseController {

    private $userService;

    public function __construct($db) {
        parent::__construct($db);
        $this->userService = new UserService($db, $this->username);
    }

    public function getAll() {
        echo json_encode($this->userService->getAll($_POST));
        exit();
    }

    public function create() {
        try {
            $result = $this->userService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function update() {
        try {
            $this->userService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function delete() {
        $id   = $_POST['userID'] ?? null;
        $type = $_POST['type'] ?? '';
        if (!$id) { $this->failed('Missing ID'); return; }
        try {
            $this->userService->delete($id, $type);
            $this->success('Deleted');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function get() {
        $id = $_POST['userID'] ?? null;
        if (!$id) { $this->failed('Missing ID'); return; }
        try {
            $row = $this->userService->get($id);
            $row ? $this->success($row) : $this->failed('Record not found');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function upload() {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data)) { $this->failed('Please fill in all the fields'); return; }
        try {
            $result = $this->userService->upload($data);
            if (!empty($result['errors'])) {
                echo json_encode(['status' => 'error', 'message' => $result['errors']]);
            } else {
                $this->success('Added Successfully!!');
            }
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
        exit();
    }

    public function resetPassword() {
        $id = $_POST['userID'] ?? null;
        if (!$id) { $this->failed('Missing ID'); return; }
        try {
            $this->userService->resetPassword($id);
            $this->success('Password reset successfully');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    public function changePassword() {
        $oldPassword = $_POST['oldPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        if (!$oldPassword || !$newPassword) { $this->failed('Please fill in all fields'); return; }
        try {
            $this->userService->changePassword($_SESSION['id'], $oldPassword, $newPassword);
            $this->success('Password updated successfully');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function updateProfile() {
        try {
            $this->userService->updateProfile($_SESSION['id'], $_POST);
            $this->success('Profile updated successfully');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
}
?>
