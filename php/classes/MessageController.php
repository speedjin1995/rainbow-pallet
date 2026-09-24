<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/MessageService.php';

class MessageController extends BaseController {

    private $messageService;

    public function __construct($db) {
        parent::__construct($db);
        $this->messageService = new MessageService($db, $this->username);
    }

    /**
     * Get all messages (for DataTables)
     */
    public function getAll() {
        $result = $this->messageService->getAll($_POST);
        echo json_encode($result);
        exit();
    }

    /**
     * Create new message
     */
    public function create() {
        try {
            $result = $this->messageService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    /**
     * Update existing message
     */
    public function update() {
        try {
            $this->messageService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    /**
     * Hard delete message
     */
    public function delete() {
        $id = $this->getRequiredPost('id');

        try {
            $this->messageService->delete($id);
            $this->success('Deleted Successfully');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    /**
     * Get single message by ID
     */
    public function get() {
        $id = $this->getRequiredPost('id');

        try {
            $row = $this->messageService->get($id);
            if ($row) {
                $this->success($row);
            } else {
                $this->failed('Record not found');
            }
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
}
?>
