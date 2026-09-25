<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

class UnitService extends BaseService {

    protected $table = 'Units';

    public function filter($post) {
        $draw = $post['draw'] ?? 1;
        $start = $post['start'] ?? 0;
        $length = $post['length'] ?? 10;
        $searchValue = isset($post['search']['value']) ? mysqli_real_escape_string($this->db, $post['search']['value']) : '';
        $companyId  = isset($post['companyId']) ? intval($post['companyId']) : 0;
        $unitName = isset($post['unitName']) ? mysqli_real_escape_string($this->db, $post['unitName']) : '';

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
        if ($companyId > 0) {
            $searchQuery .= " AND company={$companyId}";
        }
        if ($unitName !== '') {
            $searchQuery .= " AND unit LIKE '%{$unitName}%'";
        }

        $filteredQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0 {$searchQuery}";
        $filteredResult = $this->db->query($filteredQuery);
        $totalFiltered = $filteredResult->fetch_assoc()['total'];

        $dataQuery = "SELECT id, company, unit, status FROM {$this->table} WHERE status = 0 {$searchQuery} ORDER BY {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}";
        $dataResult = $this->db->query($dataQuery);

        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $company = searchCompanyById($row['company'], $this->db);
            $row['company_name'] = $company ? $company['name'] : '';
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
        $company = isset($f['company']) && $f['company'] !== '' ? $f['company'] : null;

        if ($this->isDuplicateUnit($f['unit'], $company, $f['id'] ?? null)) {
            throw new Exception('Unit already exists');
        }

        if (!empty($f['id'])) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET company=?, unit=?, modified_by=? WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('sssi', $company, $f['unit'], $this->username, $f['id']);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();
            return ['id' => $f['id']];
        } else {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (company, unit, created_by, modified_by) VALUES (?, ?, ?, ?)");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('ssss', $company, $f['unit'], $this->username, $this->username);
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

    public function upload($data, $companyId) {
        $errors = [];
        $successCount = 0;
        $company = $companyId;

        foreach ($data as $index => $row) {
            $rowNum = $index + 2;

            $unit = isset($row['Unit']) ? trim($row['Unit']) : null;

            if (empty($unit)) {
                continue;
            }

            if ($this->isDuplicateUnit($unit, $company)) {
                $errors[] = "Row {$rowNum}: Unit '{$unit}' already exists";
                continue;
            }

            $stmt = $this->db->prepare("INSERT INTO {$this->table} (company, unit, created_by) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $company, $unit, $this->username);
            
            if ($stmt->execute()) {
                $successCount++;
            } else {
                $errors[] = "Row {$rowNum}: " . $stmt->error;
            }
            $stmt->close();
        }
        
        return ['errors' => $errors, 'successCount' => $successCount];
    }

    private function isDuplicateUnit($unit, $company, $excludeId = null) {
        // Duplicate check is scoped to the same company (<=> is null-safe)
        $sql = "SELECT id FROM {$this->table} WHERE unit=? AND company <=> ? AND status='0'";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ssi', $unit, $company, $excludeId);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ss', $unit, $company);
        }
        $stmt->execute();
        $stmt->store_result();
        $isDuplicate = $stmt->num_rows > 0;
        $stmt->close();
        return $isDuplicate;
    }
}
?>
