<?php
require_once __DIR__ . '/BaseController.php';

/**
 * Item Controller
 * Handles all CRUD operations for Items (Products)
 */
class ItemController extends BaseController {
    protected $table = 'Product';
    
    /**
     * Get all items (for DataTables)
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
            $searchQuery = " AND (p.product_code LIKE '%{$searchValue}%' OR p.name LIKE '%{$searchValue}%' OR p.description LIKE '%{$searchValue}%' OR c.category_name LIKE '%{$searchValue}%' OR p.entity_type LIKE '%{$searchValue}%')";
        }
        
        // Filtered records
        $filteredQuery = "SELECT COUNT(*) as total FROM {$this->table} p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = 0 {$searchQuery}";
        $filteredResult = $this->db->query($filteredQuery);
        $totalFiltered = $filteredResult->fetch_assoc()['total'];
        
        // Data
        $dataQuery = "SELECT p.id, p.product_code, p.name, p.description, p.status, p.entity_type, IFNULL(c.category_name, '') as category_name FROM {$this->table} p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = 0 {$searchQuery} ORDER BY {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}";
        $dataResult = $this->db->query($dataQuery);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        echo json_encode([
            'draw' => intval($draw),
            'iTotalRecords' => $totalRecords,
            'iTotalDisplayRecords' => $totalFiltered,
            'aaData' => $data
        ]);
        exit();
    }
    
    /**
     * Create new item
     */
    public function create() {
        $productCode = $this->getRequiredPost('productCode');
        $productName = $this->getRequiredPost('productName');
        $categoryId = $this->getRequiredPost('categoryId');
        $uom = $this->getRequiredPost('uom');
        $entityType = $this->getRequiredPost('entityType');
        $description = $this->getPost('description');
        $varianceType = $this->getPost('varianceType');
        $high = $this->getPost('high', 0);
        $low = $this->getPost('low', 0);
        
        // Check duplicate
        if ($this->isDuplicate('product_code', $productCode)) {
            $this->failed('Product code already exists');
        }
        
        try {
            $this->db->begin_transaction();
            
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (product_code, name, category, uom, entity_type, description, variance, high, low, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            
            $stmt->bind_param('ssiisssddss', $productCode, $productName, $categoryId, $uom, $entityType, $description, $varianceType, $high, $low, $this->username, $this->username);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            
            $insertId = $stmt->insert_id;
            $stmt->close();
            
            // Handle UOM Conversion
            $this->saveUomConversion($insertId);
            
            $this->db->commit();
            
            $this->success('Added Successfully!!', ['id' => $insertId]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Update existing item
     */
    public function update() {
        $id = $this->getRequiredPost('id');
        $productCode = $this->getRequiredPost('productCode');
        $productName = $this->getRequiredPost('productName');
        $categoryId = $this->getRequiredPost('categoryId');
        $uom = $this->getRequiredPost('uom');
        $entityType = $this->getRequiredPost('entityType');
        $description = $this->getPost('description');
        $varianceType = $this->getPost('varianceType');
        $high = $this->getPost('high', 0);
        $low = $this->getPost('low', 0);
        
        // Check duplicate (exclude current record)
        if ($this->isDuplicate('product_code', $productCode, $id)) {
            $this->failed('Product code already exists');
        }
        
        try {
            $this->db->begin_transaction();
            
            $stmt = $this->db->prepare("UPDATE {$this->table} SET product_code=?, name=?, category=?, uom=?, entity_type=?, description=?, variance=?, high=?, low=?, modified_by=? WHERE id=?");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            
            $stmt->bind_param('ssiisssddsi', $productCode, $productName, $categoryId, $uom, $entityType, $description, $varianceType, $high, $low, $this->username, $id);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            
            $stmt->close();
            
            // Handle UOM Conversion (smart update)
            $this->syncUomConversion($id);
            
            $this->db->commit();
            
            $this->success('Updated Successfully!!');
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Save UOM Conversion for new product
     */
    private function saveUomConversion($productId) {
        if (!isset($_POST['uomNo']) || !is_array($_POST['uomNo'])) {
            return;
        }
        
        $uomNo = $_POST['uomNo'];
        $convUom = $_POST['convUom'];
        $rate = $_POST['rate'];
        
        $stmt = $this->db->prepare("INSERT INTO Product_Uom (product_id, unit_id, rate) VALUES (?, ?, ?)");
        
        foreach ($uomNo as $key => $no) {
            if (!empty($convUom[$key])) {
                $stmt->bind_param('iis', $productId, $convUom[$key], $rate[$key]);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    
    /**
     * Smart update UOM Conversion
     */
    private function syncUomConversion($productId) {
        if (!isset($_POST['uomNo']) || !is_array($_POST['uomNo'])) {
            // No UOM data submitted - soft delete all existing
            $stmt = $this->db->prepare("UPDATE Product_Uom SET status = 1 WHERE product_id = ? AND status = 0");
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $stmt->close();
            return;
        }
        
        $uomNo = $_POST['uomNo'];
        $uomId = $_POST['uomId'];
        $convUom = $_POST['convUom'];
        $rate = $_POST['rate'];
        
        // Collect submitted IDs (existing records being kept)
        $submittedIds = [];
        foreach ($uomId as $key => $id) {
            if (!empty($id)) {
                $submittedIds[] = (int)$id;
            }
        }
        
        // Soft delete records not in submitted list
        if (!empty($submittedIds)) {
            $placeholders = implode(',', $submittedIds);
            $this->db->query("UPDATE Product_Uom SET status = 1 WHERE product_id = {$productId} AND status = 0 AND id NOT IN ({$placeholders})");
        } else {
            // No existing IDs submitted - delete all
            $this->db->query("UPDATE Product_Uom SET status = 1 WHERE product_id = {$productId} AND status = 0");
        }
        
        // Update existing / Insert new
        $updateStmt = $this->db->prepare("UPDATE Product_Uom SET unit_id = ?, rate = ? WHERE id = ?");
        $insertStmt = $this->db->prepare("INSERT INTO Product_Uom (product_id, unit_id, rate) VALUES (?, ?, ?)");
        
        foreach ($uomNo as $key => $no) {
            if (empty($convUom[$key])) continue;
            
            if (!empty($uomId[$key])) {
                // Update existing
                $updateStmt->bind_param('isi', $convUom[$key], $rate[$key], $uomId[$key]);
                $updateStmt->execute();
            } else {
                // Insert new
                $insertStmt->bind_param('iis', $productId, $convUom[$key], $rate[$key]);
                $insertStmt->execute();
            }
        }
        
        $updateStmt->close();
        $insertStmt->close();
    }
    
    /**
     * Delete (soft delete) item
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
     * Reactivate item
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
     * Get single item by ID
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
            
            // Get UOM conversions
            $uomStmt = $this->db->prepare("SELECT pu.id, pu.unit_id, pu.rate, u.unit FROM Product_Uom pu LEFT JOIN Units u ON pu.unit_id = u.id WHERE pu.product_id = ? AND pu.status = 0");
            $uomStmt->bind_param('i', $id);
            $uomStmt->execute();
            $uomResult = $uomStmt->get_result();
            
            $uomData = [];
            while ($uomRow = $uomResult->fetch_assoc()) {
                $uomData[] = $uomRow;
            }
            $uomStmt->close();
            
            $row['uom_conversions'] = $uomData;
            
            $this->success('Record found', $row);
        } else {
            $stmt->close();
            $this->failed('Record not found');
        }
    }
    
    /**
     * Upload items from Excel
     */
    public function upload() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data)) {
            $this->failed('No data provided');
        }
        
        $errors = [];
        $successCount = 0;
        
        foreach ($data as $index => $row) {
            $rowNum = $index + 1;
            
            $productCode = isset($row['ProductCode']) ? trim($row['ProductCode']) : null;
            $productName = isset($row['ProductName']) ? trim($row['ProductName']) : null;
            $description = isset($row['Description']) ? trim($row['Description']) : null;
            
            // Validate required fields
            if (empty($productCode)) {
                $errors[] = "Row {$rowNum}: Product Code is required";
                continue;
            }
            
            if (empty($productName)) {
                $errors[] = "Row {$rowNum}: Product Name is required";
                continue;
            }
            
            // Check duplicate
            if ($this->isDuplicate('product_code', $productCode)) {
                $errors[] = "Row {$rowNum}: Product Code '{$productCode}' already exists";
                continue;
            }
            
            // Insert
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (product_code, name, description, created_by, modified_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $productCode, $productName, $description, $this->username, $this->username);
            
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
