<?php
require_once __DIR__ . '/BaseController.php';

class UnitController extends BaseController {
    protected $table = 'Units';
    
    public function getAll() {
        $draw = $_POST['draw'] ?? 1;
        $start = $_POST['start'] ?? 0;
        $length = $_POST['length'] ?? 10;
        $searchValue = isset($_POST['search']['value']) ? mysqli_real_escape_string($this->db, $_POST['search']['value']) : '';
        
        $columnIndex = $_POST['order'][0]['column'] ?? 0;
        $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $_POST['order'][0]['dir'] ?? 'asc';
        
        $totalQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0";
        $totalResult = $this->db->query($totalQuery);
        $totalRecords = $totalResult->fetch_assoc()['total'];
        
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = " AND unit LIKE '%{$searchValue}%'";
        }
        
        $filteredQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0 {$searchQuery}";
        $filteredResult = $this->db->query($filteredQuery);
        $totalFiltered = $filteredResult->fetch_assoc()['total'];
        
        $dataQuery = "SELECT id, unit, status FROM {$this->table} WHERE status = 0 {$searchQuery} ORDER BY {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}";
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
    
    public function create() {
        $unit = $this->getRequiredPost('unit');
        
        if ($this->isDuplicate('unit', $unit)) {
            $this->failed('Unit already exists');
        }
        
        try {
            $this->db->begin_transaction();
            
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (unit, created_by, modified_by) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $unit, $this->username, $this->username);
            
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
    
    public function update() {
        $id = $this->getRequiredPost('id');
        $unit = $this->getRequiredPost('unit');
        
        if ($this->isDuplicate('unit', $unit, $id)) {
            $this->failed('Unit already exists');
        }
        
        try {
            $this->db->begin_transaction();
            
            $stmt = $this->db->prepare("UPDATE {$this->table} SET unit=?, modified_by=? WHERE id=?");
            $stmt->bind_param('ssi', $unit, $this->username, $id);
            
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
    
    public function delete() {
        $id = $this->getPost('id');
        $type = $this->getPost('type');
        
        try {
            $this->db->begin_transaction();
            
            if ($type === 'MULTI' && is_array($id)) {
                $placeholders = implode(',', array_fill(0, count($id), '?'));
                $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id IN ($placeholders)");
                $types = 's' . str_repeat('i', count($id));
                $params = array_merge([$this->username], $id);
                $stmt->bind_param($types, ...$params);
            } else {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id=?");
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
    
    public function reactivate() {
        $id = $this->getRequiredPost('id');
        
        try {
            $this->db->begin_transaction();
            
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=0, modified_by=? WHERE id=?");
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
    
    public function get() {
        $id = $this->getRequiredPost('id');
        
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            $this->success('Record found', $row);
        } else {
            $stmt->close();
            $this->failed('Record not found');
        }
    }
    
    public function upload() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data)) {
            $this->failed('No data provided');
        }
        
        $errors = [];
        $successCount = 0;
        
        foreach ($data as $index => $row) {
            $rowNum = $index + 1;
            
            $unit = isset($row['Unit']) ? trim($row['Unit']) : null;
            
            if (empty($unit)) {
                $errors[] = "Row {$rowNum}: Unit is required";
                continue;
            }
            
            if ($this->isDuplicate('unit', $unit)) {
                $errors[] = "Row {$rowNum}: Unit '{$unit}' already exists";
                continue;
            }
            
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (unit, created_by) VALUES (?, ?)");
            $stmt->bind_param('ss', $unit, $this->username);
            
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
