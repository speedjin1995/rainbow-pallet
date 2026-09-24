<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

class ProjectService extends BaseService {
    
    protected $table = 'Project';

    /**
     * Get all projects (for DataTables)
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
            $searchQuery = " AND (p.project_code LIKE '%{$searchValue}%' OR p.project_description LIKE '%{$searchValue}%' OR c.name LIKE '%{$searchValue}%')";
        }
        
        // Filtered records
        $filteredQuery = "SELECT COUNT(*) as total FROM {$this->table} p LEFT JOIN Company c ON p.company = c.id WHERE p.status = 0 {$searchQuery}";
        $filteredResult = $this->db->query($filteredQuery);
        $totalFiltered = $filteredResult->fetch_assoc()['total'];
        
        // Data
        $dataQuery = "SELECT p.id, p.project_code, p.project_description, p.company, IFNULL(c.name, '') as company_name, p.status FROM {$this->table} p LEFT JOIN Company c ON p.company = c.id WHERE p.status = 0 {$searchQuery} ORDER BY {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}";
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
     * Create new project
     */
    public function create($post) {
        $projectCode = trim($post['projectCode']);
        $projectDescription = isset($post['projectDescription']) ? trim($post['projectDescription']) : null;
        $companyId = isset($post['company']) && $post['company'] !== '' ? $post['company'] : null;
        
        // Check duplicate
        if ($this->isDuplicate('project_code', $projectCode)) {
            throw new Exception('Project code already exists');
        }
        
        $this->db->begin_transaction();
        
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (project_code, project_description, company, created_by) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        
        $stmt->bind_param('ssis', $projectCode, $projectDescription, $companyId, $this->username);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $insertId = $stmt->insert_id;
        $stmt->close();
        $this->db->commit();
        
        return ['id' => $insertId];
    }
    
    /**
     * Update existing project
     */
    public function update($post) {
        $id = $post['id'];
        $projectCode = trim($post['projectCode']);
        $projectDescription = isset($post['projectDescription']) ? trim($post['projectDescription']) : null;
        $companyId = isset($post['company']) && $post['company'] !== '' ? $post['company'] : null;
        
        // Check duplicate (exclude current record)
        if ($this->isDuplicate('project_code', $projectCode, $id)) {
            throw new Exception('Project code already exists');
        }
        
        $this->db->begin_transaction();
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET project_code=?, project_description=?, company=?, modified_by=? WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        
        $stmt->bind_param('ssisi', $projectCode, $projectDescription, $companyId, $this->username, $id);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        $this->db->commit();
        
        return ['id' => $id];
    }
    
    /**
     * Delete (soft delete) project
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
     * Reactivate project
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
     * Get single project by ID
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
            return $row;
        } else {
            $stmt->close();
            return null;
        }
    }
    
    /**
     * Upload projects from Excel
     */
    public function upload($data) {
        if (empty($data)) {
            throw new Exception('No data provided');
        }
        
        $errors = [];
        $successCount = 0;
        
        foreach ($data as $index => $row) {
            $rowNum = $index + 1;
            
            $projectCode = isset($row['ProjectCode']) ? trim($row['ProjectCode']) : null;
            $projectDescription = isset($row['ProjectDescription']) ? trim($row['ProjectDescription']) : null;
            $companyName = isset($row['Company']) ? trim($row['Company']) : null;
            $companyId = null;
            
            // Validate required fields
            if (empty($projectCode)) {
                $errors[] = "Row {$rowNum}: Project Code is required";
                continue;
            }
            
            // Lookup company_id by name
            if (!empty($companyName)) {
                $companyId = searchCompanyIdByName($companyName, $this->db);
                if (empty($companyId)) {
                    $errors[] = "Row {$rowNum}: Company '{$companyName}' not found.";
                    continue;
                }
            }
            
            // Check duplicate
            if ($this->isDuplicate('project_code', $projectCode)) {
                $errors[] = "Row {$rowNum}: Project Code '{$projectCode}' already exists";
                continue;
            }
            
            // Insert
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (project_code, project_description, company, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssis', $projectCode, $projectDescription, $companyId, $this->username);
            
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
}
?>
