<?php
require_once __DIR__ . '/BaseService.php';

class ItemService extends BaseService {
    
    protected $table = 'Product';

    /**
     * Get all items (for DataTables)
     */
    public function getAll($post) {
        $draw = $post['draw'] ?? 1;
        $start = $post['start'] ?? 0;
        $length = $post['length'] ?? 10;
        $searchValue = isset($post['search']['value']) ? mysqli_real_escape_string($this->db, $post['search']['value']) : '';
        
        $columnIndex = $post['order'][0]['column'] ?? 0;
        $columnName = $post['columns'][$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $post['order'][0]['dir'] ?? 'asc';
        
        // Total records
        $totalQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0";
        $totalResult = $this->db->query($totalQuery);
        $totalRecords = $totalResult->fetch_assoc()['total'];
        
        // Search filter
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = " AND (p.product_code LIKE '%{$searchValue}%' OR p.name LIKE '%{$searchValue}%' OR p.description LIKE '%{$searchValue}%' OR c.category_name LIKE '%{$searchValue}%')";
        }
        
        // Filtered records
        $filteredQuery = "SELECT COUNT(*) as total FROM {$this->table} p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = 0 {$searchQuery}";
        $filteredResult = $this->db->query($filteredQuery);
        $totalFiltered = $filteredResult->fetch_assoc()['total'];
        
        // Data - order by is_manual DESC first to show manual items at top
        $dataQuery = "SELECT p.id, p.product_code, p.name, p.description, p.status, IFNULL(p.is_manual, 'N') as is_manual, IFNULL(c.category_name, '') as category_name FROM {$this->table} p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = 0 {$searchQuery} ORDER BY p.is_manual DESC, {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}";
        $dataResult = $this->db->query($dataQuery);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = $row;
        }
        
        return [
            'draw' => intval($draw),
            'iTotalRecords' => $totalRecords,
            'iTotalDisplayRecords' => $totalFiltered,
            'aaData' => $data
        ];
    }
    
    /**
     * Create new item
     */
    public function create($post) {
        $productCode = trim($post['productCode']);
        $productName = trim($post['productName']);
        $categoryId = $post['categoryId'];
        $uom = $post['uom'];
        $description = $post['description'] ?? null;
        $varianceType = $post['varianceType'] ?? null;
        $high = $post['high'] ?? 0;
        $low = $post['low'] ?? 0;
        
        // Check duplicate
        if ($this->isDuplicate('product_code', $productCode)) {
            throw new Exception('Product code already exists');
        }
        
        $this->db->begin_transaction();
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (product_code, name, category, uom, description, variance, high, low, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        
        $stmt->bind_param('sssssssss', $productCode, $productName, $categoryId, $uom, $description, $varianceType, $high, $low, $this->username);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $insertId = $stmt->insert_id;
        $stmt->close();
        
        // Handle UOM Conversion
        $this->saveUomConversion($insertId, $post);
        
        $this->db->commit();
        
        return ['id' => $insertId];
    }
    
    /**
     * Update existing item
     */
    public function update($post) {
        $id = $post['id'];
        $productCode = trim($post['productCode']);
        $productName = trim($post['productName']);
        $categoryId = $post['categoryId'];
        $uom = $post['uom'];
        $description = $post['description'] ?? null;
        $varianceType = $post['varianceType'] ?? null;
        $high = $post['high'] ?? 0;
        $low = $post['low'] ?? 0;
        
        // Check duplicate (exclude current record)
        if ($this->isDuplicate('product_code', $productCode, $id)) {
            throw new Exception('Product code already exists');
        }
        
        $this->db->begin_transaction();
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET product_code=?, name=?, category=?, uom=?, description=?, variance=?, high=?, low=?, modified_by=? WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        
        $stmt->bind_param('ssssssssss', $productCode, $productName, $categoryId, $uom, $description, $varianceType, $high, $low, $this->username, $id);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
        // Handle UOM Conversion (smart update)
        $this->syncUomConversion($id, $post);
        
        $this->db->commit();
        
        return ['id' => $id];
    }
    
    /**
     * Save UOM Conversion for new product
     */
    private function saveUomConversion($productId, $post) {
        if (!isset($post['uomNo']) || !is_array($post['uomNo'])) {
            return;
        }
        
        $uomNo = $post['uomNo'];
        $convUom = $post['convUom'];
        $rate = $post['rate'];
        
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
    private function syncUomConversion($productId, $post) {
        if (!isset($post['uomNo']) || !is_array($post['uomNo'])) {
            // No UOM data submitted - soft delete all existing
            $stmt = $this->db->prepare("UPDATE Product_Uom SET status = 1 WHERE product_id = ? AND status = 0");
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $stmt->close();
            return;
        }
        
        $uomNo = $post['uomNo'];
        $uomId = $post['uomId'];
        $convUom = $post['convUom'];
        $rate = $post['rate'];
        
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
    public function delete($id, $type = null) {
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
    }
    
    /**
     * Reactivate item
     */
    public function reactivate($id) {
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
    }
    
    /**
     * Get single item by ID
     */
    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        if (!$stmt) {
            throw new Exception($this->db->error);
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
            
            return $row;
        } else {
            $stmt->close();
            return null;
        }
    }
    
    /**
     * Upload items from Excel
     */
    public function upload($data) {
        if (empty($data)) {
            throw new Exception('No data provided');
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
        
        return ['errors' => $errors, 'successCount' => $successCount];
    }
    
    /**
     * Check for duplicate value
     */
    private function isDuplicate($column, $value, $excludeId = null) {
        $query = "SELECT id FROM {$this->table} WHERE {$column} = ? AND status = 0";
        if ($excludeId) {
            $query .= " AND id != ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('si', $value, $excludeId);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s', $value);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
    
    /**
     * Auto register product from manual input (weighing)
     * @param string $productName - Product name
     * @param string $entityType - 'Customer' for product, 'Supplier' for raw material
     * @return array - ['product_code' => code, 'name' => name]
     */
    public function autoRegisterProduct($productName, $entityType) {
        $productName = trim($productName);
        
        // Check if product already exists
        $stmt = $this->db->prepare("SELECT product_code FROM Product WHERE name=? AND entity_type=? AND status='0'");
        $stmt->bind_param('ss', $productName, $entityType);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($row) {
            return ['product_code' => $row['product_code'], 'name' => $productName];
        }
        
        // Generate product code
        $prefix = ($entityType === 'Customer') ? 'P' : 'R';
        $productCode = $prefix . date('ymdHis') . rand(100, 999);
        $isManual = 'Y';
        $status = '0';
        
        $stmt = $this->db->prepare("INSERT INTO Product (product_code, name, entity_type, is_manual, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssss', $productCode, $productName, $entityType, $isManual, $status, $this->username);
        $stmt->execute();
        $stmt->close();
        
        return ['product_code' => $productCode, 'name' => $productName];
    }
}
?>
