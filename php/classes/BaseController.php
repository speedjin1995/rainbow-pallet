<?php
/**
 * Base Controller Class
 * All module controllers should extend this class
 */
class BaseController {
    protected $db;
    protected $username;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct($db) {
        $this->db = $db;
        $this->username = $_SESSION['username'] ?? 'system';
    }
    
    /**
     * Send JSON response
     */
    protected function response($status, $message, $data = null) {
        $response = ['status' => $status, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        echo json_encode($response);
        exit();
    }
    
    /**
     * Success response
     */
    protected function success($message, $data = null) {
        $this->response('success', $message, $data);
    }
    
    /**
     * Failed response
     */
    protected function failed($message) {
        $this->response('failed', $message);
    }
    
    /**
     * Check for duplicate value
     */
    protected function isDuplicate($column, $value, $excludeId = null) {
        $sql = "SELECT {$this->primaryKey} FROM {$this->table} WHERE {$column} = ? AND status = 0";
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $value, $excludeId);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $value);
        }
        $stmt->execute();
        $stmt->store_result();
        $isDuplicate = $stmt->num_rows > 0;
        $stmt->close();
        return $isDuplicate;
    }
    
    /**
     * Get optional POST value (handles arrays)
     */
    protected function getPost($key, $default = null) {
        if (!isset($_POST[$key]) || $_POST[$key] === '') {
            return $default;
        }
        return is_array($_POST[$key]) ? $_POST[$key] : trim($_POST[$key]);
    }
    
    /**
     * Get required POST value
     */
    protected function getRequiredPost($key) {
        if (!isset($_POST[$key]) || $_POST[$key] === '') {
            $this->failed("Field '{$key}' is required");
        }
        return trim($_POST[$key]);
    }
}
?>
