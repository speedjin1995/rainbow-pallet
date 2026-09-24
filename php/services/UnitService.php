<?php
require_once __DIR__ . '/BaseService.php';

class UnitService extends BaseService {

    protected $table = 'Units';

    public function filter($post) {
        $draw = $post['draw'] ?? 1;
        $start = $post['start'] ?? 0;
        $length = $post['length'] ?? 10;
        $searchValue = isset($post['search']['value']) ? mysqli_real_escape_string($this->db, $post['search']['value']) : '';
        
        $columnIndex = $post['order'][0]['column'] ?? 0;
        $columnName = $post['columns'][$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $post['order'][0]['dir'] ?? 'asc';
        
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
        
        return [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ];
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    public function save($f) {
        if ($this->isDuplicateUnit($f['unit'], $f['id'] ?? null)) {
            throw new Exception('Unit already exists');
        }

        if (!empty($f['id'])) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET unit=?, modified_by=? WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('ssi', $f['unit'], $this->username, $f['id']);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();
            return ['id' => $f['id']];
        } else {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (unit, created_by, modified_by) VALUES (?, ?, ?)");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('sss', $f['unit'], $this->username, $this->username);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $id = $stmt->insert_id;
            $stmt->close();
            return ['id' => $id];
        }
    }

    public function delete($id, $type = null) {
        if ($type === 'MULTI' && is_array($id)) {
            $placeholders = implode(',', array_fill(0, count($id), '?'));
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id IN ($placeholders)");
            if (!$stmt) throw new Exception($this->db->error);
            $types = 's' . str_repeat('i', count($id));
            $params = array_merge([$this->username], $id);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('si', $this->username, $id);
        }
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function reactivate($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status=0, modified_by=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('si', $this->username, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function upload($data) {
        $errors = [];
        $successCount = 0;

        foreach ($data as $index => $row) {
            $rowNum = $index + 2;
            
            $unit = isset($row['Unit']) ? trim($row['Unit']) : null;
            
            if (empty($unit)) {
                continue;
            }
            
            if ($this->isDuplicateUnit($unit)) {
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
        
        return ['errors' => $errors, 'successCount' => $successCount];
    }

    private function isDuplicateUnit($unit, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table} WHERE unit=? AND status='0'";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $unit, $excludeId);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $unit);
        }
        $stmt->execute();
        $stmt->store_result();
        $isDuplicate = $stmt->num_rows > 0;
        $stmt->close();
        return $isDuplicate;
    }
}
?>
