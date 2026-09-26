<?php
require_once __DIR__ . '/../requires/permissions.php';

/**
 * Base Service Class
 * All module services should extend this class
 */
class BaseService {
    protected $db;
    protected $username;

    public function __construct($db, $username) {
        $this->db = $db;
        $this->username = $username;
    }

}
?>
