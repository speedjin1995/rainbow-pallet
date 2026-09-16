<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Product Category Controller
 * Handles all CRUD operations for Product Categories
 */
class ProductCategoryController extends BaseController {
    protected $table = 'Product_Categories';
    
    /**
     * Get all product categories (for DataTables)
     */
    public function getAll() {
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $searchValue = isset($_POST['search']['value']) ? mysqli_real_escape_string($this->db, $_POST['search']['value']) : '';
        
        $columnIndex = $_POST['order'][0]['column'] ?? 0;
        $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
        
        // Total records
        $totalQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0";
        $totalResult = $this->db->query($totalQuery);
        $totalRecords = $totalResult->fetch_assoc()['total'];
        
        // Search filter
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = " AND (category_name LIKE '%{$searchValue}%' OR post_to_sql LIKE '%{$searchValue}%')";
        }
        
        // Filtered records
        $filteredQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0 {$searchQuery}";
        $filteredResult = $this->db->query($filteredQuery);
        $totalFiltered = $filteredResult->fetch_assoc()['total'];
        
        // Data
        $dataQuery = "SELECT id, category_name, CASE WHEN post_to_sql = 'Y' THEN 'Yes' ELSE 'No' END AS post_to_sql, status FROM {$this->table} WHERE status = 0 {$searchQuery} ORDER BY {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}";
        $dataResult = $this->db->query($dataQuery);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
        exit();
    }
    
    /**
     * Create new product category
     */
    public function create() {
        $categoryName = $this->getRequiredPost('categoryName');
        $postToSql = $this->getRequiredPost('postToSql');
        
        // Check duplicate
        if ($this->isDuplicate('category_name', $categoryName)) {
            $this->failed('Category name already exists');
        }
        
        try {
            $this->db->begin_transaction();
            
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (category_name, post_to_sql, created_by, modified_by) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            
            $stmt->bind_param('ssss', $categoryName, $postToSql, $this->username, $this->username);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            
            $insertId = $stmt->insert_id;
            $stmt->close();
            $this->db->commit();
            
            $this->success('Added Successfully!!', ['id' => $insertId]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Update existing product category
     */
    public function update() {
        $id = $this->getRequiredPost('id');
        $categoryName = $this->getRequiredPost('categoryName');
        $postToSql = $this->getRequiredPost('postToSql');
        
        // Check duplicate (exclude current record)
        if ($this->isDuplicate('category_name', $categoryName, $id)) {
            $this->failed('Category name already exists');
        }
        
        try {
            $this->db->begin_transaction();
            
            $stmt = $this->db->prepare("UPDATE {$this->table} SET category_name=?, post_to_sql=?, modified_by=? WHERE id=?");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            
            $stmt->bind_param('sssi', $categoryName, $postToSql, $this->username, $id);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            
            $stmt->close();
            $this->db->commit();
            
            $this->success('Updated Successfully!!');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Delete (soft delete) product category
     */
    public function delete() {
        $id = $this->getPost('id');
        $type = $this->getPost('type');
        
        try {
            $this->db->begin_transaction();
            
            if ($type === 'MULTI' && is_array($id)) {
                $placeholders = implode(',', array_fill(0, count($id), '?'));
                $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id IN ($placeholders)");
                if (!$stmt) {
                    throw new Exception($this->db->error);
                }
                $types = 's' . str_repeat('i', count($id));
                $params = array_merge([$this->username], $id);
                $stmt->bind_param($types, ...$params);
            } else {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id=?");
                if (!$stmt) {
                    throw new Exception($this->db->error);
                }
                $stmt->bind_param('si', $this->username, $id);
            }
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            
            $stmt->close();
            $this->db->commit();
            
            $this->success('Deleted Successfully!!');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Reactivate product category
     */
    public function reactivate() {
        $id = $this->getRequiredPost('id');
        
        try {
            $this->db->begin_transaction();
            
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=0, modified_by=? WHERE id=?");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            
            $stmt->bind_param('si', $this->username, $id);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            
            $stmt->close();
            $this->db->commit();
            
            $this->success('Reactivated Successfully!!');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Get single product category by ID
     */
    public function get() {
        $id = $this->getRequiredPost('id');
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        if (!$stmt) {
            $this->failed($this->db->error);
        }
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            $this->success('Record found', ['data' => $row]);
        } else {
            $stmt->close();
            $this->failed('Record not found');
        }
    }
    
    /**
     * Upload product categories from Excel
     */
    public function upload() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data)) {
            $this->failed('No data provided');
        }
        
        $errors = [];
        $successCount = 0;
        
        foreach ($data as $index => $row) {
            $rowNum = $index + 1; // Excel row number (1-based + header)
            
            $categoryName = isset($row['CategoryName']) ? trim($row['CategoryName']) : null;
            $postToSql = isset($row['PostToSQL']) ? trim($row['PostToSQL']) : null;
            
            // Validate required fields
            if (empty($categoryName)) {
                $errors[] = "Row {$rowNum}: Category Name is required";
                continue;
            }
            
            if (empty($postToSql)) {
                $errors[] = "Row {$rowNum}: Post to SQL is required";
                continue;
            }
            
            // Validate and format post to sql value
            if (in_array($postToSql, ['Yes', 'Y'])) {
                $postToSql = 'Y';
            } elseif (in_array($postToSql, ['No', 'N'])) {
                $postToSql = 'N';
            } else {
                $errors[] = "Row {$rowNum}: Post to SQL must be 'Yes' or 'No'";
                continue;
            }
            
            // Check duplicate
            if ($this->isDuplicate('category_name', $categoryName)) {
                $errors[] = "Row {$rowNum}: Category Name '{$categoryName}' already exists";
                continue;
            }
            
            // Insert
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (category_name, post_to_sql, created_by) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $categoryName, $postToSql, $this->username);
            
            if ($stmt->execute()) {
                $successCount++;
            } else {
                $errors[] = "Row {$rowNum}: " . $stmt->error;
            }
            $stmt->close();
        }
        
        if (count($errors) > 0 && $successCount > 0) {
            echo json_encode(['status' => 'error', 'message' => $errors]);
            exit();
        } elseif (count($errors) > 0) {
            $this->failed(implode(', ', $errors));
        } else {
            $this->success("{$successCount} records imported successfully");
        }
    }
}
?>
