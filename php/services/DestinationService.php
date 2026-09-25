<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/functions.php';
require_once __DIR__ . '/../requires/lookup.php';

class DestinationService extends BaseService {

    protected $table = 'Destination';

    /**
     * Get all destinations (for DataTables)
     */
    public function getAll($post) {
        $draw = $post['draw'] ?? 1;
        $start = $post['start'] ?? 0;
        $length = $post['length'] ?? 10;
        $searchValue = isset($post['search']['value']) ? mysqli_real_escape_string($this->db, $post['search']['value']) : '';
        $companyId  = isset($post['companyId']) ? intval($post['companyId']) : 0;
        $destinationCode = isset($post['destinationCode']) ? mysqli_real_escape_string($this->db, $post['destinationCode']) : '';
        $destinationName = isset($post['destinationName']) ? mysqli_real_escape_string($this->db, $post['destinationName']) : '';

        $columnIndex = $post['order'][0]['column'] ?? 0;
        $columnName = $post['columns'][$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $post['order'][0]['dir'] ?? 'asc';
        // company_name is a computed column (not a real DB column), sort by company instead
        if ($columnName === 'company_name') $columnName = 'company';

        // Total records
        $totalResult = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $totalRecords = $totalResult->fetch_assoc()['total'];

        // Search filter
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = " AND (name LIKE '%{$searchValue}%' OR description LIKE '%{$searchValue}%' OR destination_code LIKE '%{$searchValue}%')";
        }
        if ($companyId > 0) {
            $searchQuery .= " AND company={$companyId}";
        }
        if ($destinationCode !== '') {
            $searchQuery .= " AND destination_code LIKE '%{$destinationCode}%'";
        }
        if ($destinationName !== '') {
            $searchQuery .= " AND name LIKE '%{$destinationName}%'";
        }

        // Filtered records
        $filteredResult = $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0 {$searchQuery}");
        $totalFiltered = $filteredResult->fetch_assoc()['total'];

        // Data
        $dataResult = $this->db->query("SELECT * FROM {$this->table} WHERE status = 0 {$searchQuery} ORDER BY status ASC, {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}");

        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $company = searchCompanyById($row['company'], $this->db);
            $data[] = [
                'id'               => $row['id'],
                'company'          => $row['company'],
                'company_name'     => $company ? $company['name'] : '',
                'destination_code' => $row['destination_code'],
                'name'             => $row['name'],
                'description'      => $row['description'],
                'status'           => $row['status']
            ];
        }

        return [
            'draw'                 => intval($draw),
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $totalFiltered,
            'aaData'               => $data
        ];
    }

    /**
     * Create new destination
     */
    public function create($post) {
        $company         = isset($post['company']) && $post['company'] !== '' ? $post['company'] : null;
        $destinationCode = trim($post['destinationCode']);
        $destinationName = isset($post['destinationName']) && $post['destinationName'] !== '' ? trim($post['destinationName']) : null;
        $description     = isset($post['description']) && $post['description'] !== '' ? trim($post['description']) : null;

        if ($this->isDuplicate('destination_code', $destinationCode, $company)) {
            throw new Exception('Destination code already exists');
        }

        $this->db->begin_transaction();

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (company, destination_code, name, description, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('ssssss', $company, $destinationCode, $destinationName, $description, $this->username, $this->username);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $insertId = $stmt->insert_id;
        $stmt->close();

        $this->db->commit();

        return ['id' => $insertId];
    }

    /**
     * Update existing destination
     */
    public function update($post) {
        $id              = $post['id'];
        $company         = isset($post['company']) && $post['company'] !== '' ? $post['company'] : null;
        $destinationCode = trim($post['destinationCode']);
        $destinationName = isset($post['destinationName']) && $post['destinationName'] !== '' ? trim($post['destinationName']) : null;
        $description     = isset($post['description']) && $post['description'] !== '' ? trim($post['description']) : null;

        if ($this->isDuplicate('destination_code', $destinationCode, $company, $id)) {
            throw new Exception('Destination code already exists');
        }

        $this->db->begin_transaction();

        // Get old values before update
        $stmt = $this->db->prepare("SELECT destination_code, name FROM {$this->table} WHERE id = ?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $stmt->bind_result($oldCode, $oldName);
        $stmt->fetch();
        $stmt->close();

        $stmt = $this->db->prepare("UPDATE {$this->table} SET company=?, destination_code=?, name=?, description=?, created_by=?, modified_by=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sssssss', $company, $destinationCode, $destinationName, $description, $this->username, $this->username, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

        // Cascade code/name changes to related tables
        if ($oldCode !== null && $oldCode !== $destinationCode) {
            updateMasterDataCodeValue($this->db, $oldCode, $destinationCode, 'Destination');
        }

        if ($oldName !== null && $oldName !== $destinationName) {
            updateMasterDataNameValue($this->db, $oldName, $destinationName, 'Destination');
        }

        $this->db->commit();

        return ['id' => $id];
    }

    /**
     * Delete (soft delete) destination
     */
    public function delete($id, $type = null) {
        if ($type === 'MULTI') {
            $ids = is_array($id) ? implode(',', array_map('intval', $id)) : $id;
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id IN ($ids)");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('s', $this->username);
        } else {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('ss', $this->username, $id);
        }

        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    /**
     * Get single destination by ID
     */
    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row;
    }

    /**
     * Upload destinations from Excel
     */
    public function upload($data, $companyId) {
        if (empty($data)) {
            throw new Exception('No data provided');
        }

        $errors = [];
        $successCount = 0;
        $company = $companyId;

        foreach ($data as $index => $row) {
            $rowNum = $index + 2;
            $destinationCode = !empty($row['DestinationCode']) ? trim($row['DestinationCode']) : '';
            $destinationName = !empty($row['DestinationName']) ? trim($row['DestinationName']) : '';
            $description = !empty($row['Description']) ? trim($row['Description']) : '';

            if (empty($destinationCode)) {
                $errors[] = "Row {$rowNum}: Destination Code is required";
                continue;
            }

            if ($this->isDuplicate('destination_code', $destinationCode, $company)) {
                $errors[] = "Row {$rowNum}: Destination '{$destinationCode}' already exists";
                continue;
            }

            $stmt = $this->db->prepare("INSERT INTO {$this->table} (company, destination_code, name, description, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssss', $company, $destinationCode, $destinationName, $description, $this->username, $this->username);

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
    private function isDuplicate($column, $value, $company, $excludeId = null) {
        // Duplicate check is scoped to the same company (<=> is null-safe)
        $query = "SELECT id FROM {$this->table} WHERE {$column} = ? AND company <=> ? AND status = 0";
        if ($excludeId) {
            $query .= " AND id != ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ssi', $value, $company, $excludeId);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ss', $value, $company);
        }
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}
?>
