<?php
require_once __DIR__ . '/BaseService.php';

class CompanyService extends BaseService {
    
    protected $table = 'Company';

    /**
     * Get all companies (for DataTables)
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
        $totalQuery = "SELECT COUNT(*) as total FROM {$this->table}";
        $totalResult = $this->db->query($totalQuery);
        $totalRecords = $totalResult->fetch_assoc()['total'];
        
        // Search filter
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = " AND (name LIKE '%{$searchValue}%' OR company_code LIKE '%{$searchValue}%')";
        }
        
        // Filtered records
        $filteredQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0 {$searchQuery}";
        $filteredResult = $this->db->query($filteredQuery);
        $totalFiltered = $filteredResult->fetch_assoc()['total'];
        
        // Data
        $dataQuery = "SELECT * FROM {$this->table} WHERE status = 0 {$searchQuery} ORDER BY status ASC, {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}";
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
     * Create new company
     */
    public function create($post) {
        $companyCode = trim($post['companyCode']);
        $companyRegNo = isset($post['companyRegNo']) && $post['companyRegNo'] !== '' ? trim($post['companyRegNo']) : null;
        $companyNewRegNo = isset($post['companyNewRegNo']) && $post['companyNewRegNo'] !== '' ? trim($post['companyNewRegNo']) : null;
        $companyName = trim($post['companyName']);
        $addressLine1 = isset($post['addressLine1']) && $post['addressLine1'] !== '' ? trim($post['addressLine1']) : null;
        $addressLine2 = isset($post['addressLine2']) && $post['addressLine2'] !== '' ? trim($post['addressLine2']) : null;
        $addressLine3 = isset($post['addressLine3']) && $post['addressLine3'] !== '' ? trim($post['addressLine3']) : null;
        $phoneNo = isset($post['phoneNo']) && $post['phoneNo'] !== '' ? trim($post['phoneNo']) : null;
        $faxNo = isset($post['faxNo']) && $post['faxNo'] !== '' ? trim($post['faxNo']) : null;
        $tinNo = isset($post['tinNo']) && $post['tinNo'] !== '' ? trim($post['tinNo']) : null;
        $mobileNo = isset($post['mobileNo']) && $post['mobileNo'] !== '' ? trim($post['mobileNo']) : null;
        $hasSawnTimber = isset($post['hasSawnTimber']) && $post['hasSawnTimber'] === 'Y' ? 'Y' : 'N';

        // Check duplicate
        if ($this->isDuplicate('company_code', $companyCode)) {
            throw new Exception('Company code already exists');
        }

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (company_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, tin_no, mobile_no, has_sawn_timber, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }

        $stmt->bind_param('sssssssssssss', $companyCode, $companyRegNo, $companyNewRegNo, $companyName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $tinNo, $mobileNo, $hasSawnTimber, $this->username);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $insertId = $stmt->insert_id;
        $stmt->close();
        
        return ['id' => $insertId];
    }
    
    /**
     * Update existing company
     */
    public function update($post) {
        $id = $post['id'];
        $companyCode = trim($post['companyCode']);
        $companyRegNo = isset($post['companyRegNo']) && $post['companyRegNo'] !== '' ? trim($post['companyRegNo']) : null;
        $companyNewRegNo = isset($post['companyNewRegNo']) && $post['companyNewRegNo'] !== '' ? trim($post['companyNewRegNo']) : null;
        $companyName = trim($post['companyName']);
        $addressLine1 = isset($post['addressLine1']) && $post['addressLine1'] !== '' ? trim($post['addressLine1']) : null;
        $addressLine2 = isset($post['addressLine2']) && $post['addressLine2'] !== '' ? trim($post['addressLine2']) : null;
        $addressLine3 = isset($post['addressLine3']) && $post['addressLine3'] !== '' ? trim($post['addressLine3']) : null;
        $phoneNo = isset($post['phoneNo']) && $post['phoneNo'] !== '' ? trim($post['phoneNo']) : null;
        $faxNo = isset($post['faxNo']) && $post['faxNo'] !== '' ? trim($post['faxNo']) : null;
        $tinNo = isset($post['tinNo']) && $post['tinNo'] !== '' ? trim($post['tinNo']) : null;
        $mobileNo = isset($post['mobileNo']) && $post['mobileNo'] !== '' ? trim($post['mobileNo']) : null;
        $hasSawnTimber = isset($post['hasSawnTimber']) && $post['hasSawnTimber'] === 'Y' ? 'Y' : 'N';

        // Check duplicate (exclude current record)
        if ($this->isDuplicate('company_code', $companyCode, $id)) {
            throw new Exception('Company code already exists');
        }

        $stmt = $this->db->prepare("UPDATE {$this->table} SET company_code=?, company_reg_no=?, new_reg_no=?, name=?, address_line_1=?, address_line_2=?, address_line_3=?, phone_no=?, fax_no=?, tin_no=?, mobile_no=?, has_sawn_timber=?, modified_by=? WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }

        $stmt->bind_param('ssssssssssssss', $companyCode, $companyRegNo, $companyNewRegNo, $companyName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $tinNo, $mobileNo, $hasSawnTimber, $this->username, $id);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
        return ['id' => $id];
    }
    
    /**
     * Delete (soft delete) company
     */
    public function delete($id, $type = null) {
        if ($type === 'MULTI') {
            $ids = is_array($id) ? implode(",", $id) : $id;
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id IN ($ids)");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            $stmt->bind_param('s', $this->username);
        } else {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id=?");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            $stmt->bind_param('ss', $this->username, $id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
    }
    
    /**
     * Get single company by ID
     */
    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row;
    }
    
    /**
     * Upload companies from Excel
     */
    public function upload($data) {
        if (empty($data)) {
            throw new Exception('No data provided');
        }

        $errors = [];
        $successCount = 0;

        foreach ($data as $index => $row) {
            $rowNum = $index + 2;
            $companyCode = !empty($row['CompanyCode']) ? trim($row['CompanyCode']) : '';
            $companyName = !empty($row['CompanyName']) ? trim($row['CompanyName']) : '';
            $companyRegNo = !empty($row['CompanyRegNo']) ? trim($row['CompanyRegNo']) : '';
            $companyNewRegNo = !empty($row['CompanyNewRegNo']) ? trim($row['CompanyNewRegNo']) : '';
            $addressLine1 = !empty($row['AddressLine1']) ? trim($row['AddressLine1']) : '';
            $addressLine2 = !empty($row['AddressLine2']) ? trim($row['AddressLine2']) : '';
            $addressLine3 = !empty($row['AddressLine3']) ? trim($row['AddressLine3']) : '';
            $phoneNo = !empty($row['PhoneNo']) ? trim($row['PhoneNo']) : '';
            $faxNo = !empty($row['FaxNo']) ? trim($row['FaxNo']) : '';
            $tinNo = !empty($row['TinNo']) ? trim($row['TinNo']) : '';
            $mobileNo = !empty($row['MobileNo']) ? trim($row['MobileNo']) : '';

            if (empty($companyCode)) {
                $errors[] = "Row {$rowNum}: Company Code is required";
                continue;
            }

            if ($this->isDuplicate('company_code', $companyCode)) {
                $errors[] = "Row {$rowNum}: Company '{$companyCode}' already exists";
                continue;
            }

            $stmt = $this->db->prepare("INSERT INTO {$this->table} (company_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, tin_no, mobile_no, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssssssssss', $companyCode, $companyRegNo, $companyNewRegNo, $companyName, $addressLine1, $addressLine2, $addressLine3, $phoneNo, $faxNo, $tinNo, $mobileNo, $this->username);

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
