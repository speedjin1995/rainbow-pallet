<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

class LocationService extends BaseService {

    protected $table = 'Location';

    /**
     * Get all locations (for DataTables)
     */
    public function getAll($post) {
        $draw = $post['draw'] ?? 1;
        $start = $post['start'] ?? 0;
        $length = $post['length'] ?? 10;
        $searchValue = isset($post['search']['value']) ? mysqli_real_escape_string($this->db, $post['search']['value']) : '';
        $companyId  = isset($post['companyId']) ? intval($post['companyId']) : 0;
        $locationCode = isset($post['locationCode']) ? mysqli_real_escape_string($this->db, $post['locationCode']) : '';
        $locationName = isset($post['locationName']) ? mysqli_real_escape_string($this->db, $post['locationName']) : '';

        $columnIndex = $post['order'][0]['column'] ?? 0;
        $columnName = $post['columns'][$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $post['order'][0]['dir'] ?? 'asc';

        // Total records
        $totalResult = $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE status = '0'");
        $totalRecords = $totalResult->fetch_assoc()['total'];

        // Search filter
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = " AND (cust.location_code LIKE '%{$searchValue}%' OR cust.location_name LIKE '%{$searchValue}%' OR pl.name LIKE '%{$searchValue}%')";
        }
        if ($companyId > 0) {
            $searchQuery .= " AND cust.company={$companyId}";
        }
        if ($locationCode !== '') {
            $searchQuery .= " AND cust.location_code LIKE '%{$locationCode}%'";
        }
        if ($locationName !== '') {
            $searchQuery .= " AND cust.location_name LIKE '%{$locationName}%'";
        }

        // Filtered records
        $filteredResult = $this->db->query("SELECT COUNT(*) as total FROM {$this->table} cust LEFT JOIN Plant pl ON cust.plant_id = pl.id WHERE cust.status = '0' {$searchQuery}");
        $totalFiltered = $filteredResult->fetch_assoc()['total'];

        // Order by (company_name/plant are computed columns, not real DB columns)
        if ($columnName === 'plant') {
            $orderBy = "pl.name {$columnSortOrder}";
        } elseif ($columnName === 'company_name') {
            $orderBy = "cust.company {$columnSortOrder}";
        } else {
            $orderBy = "cust.{$columnName} {$columnSortOrder}";
        }

        // Data
        $dataResult = $this->db->query("SELECT cust.*, pl.name as plant FROM {$this->table} cust LEFT JOIN Plant pl ON cust.plant_id = pl.id WHERE cust.status = '0' {$searchQuery} ORDER BY {$orderBy} LIMIT {$start}, {$length}");

        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $company = searchCompanyById($row['company'], $this->db);
            $data[] = [
                'id'             => $row['id'],
                'company'        => $row['company'],
                'company_name'   => $company ? $company['name'] : '',
                'location_code'  => $row['location_code'],
                'location_name'  => $row['location_name'],
                'weighing_count' => $row['weighing_count'],
                'plant'          => $row['plant'] ?? '',
                'plant_id'       => $row['plant_id'],
                'status'         => $row['status'] == '0' ? 'active' : 'inactive'
            ];
        }

        return [
            'draw'                  => intval($draw),
            'iTotalRecords'         => $totalRecords,
            'iTotalDisplayRecords'  => $totalFiltered,
            'aaData'                => $data
        ];
    }

    /**
     * Create new location (with Port row)
     */
    public function create($post) {
        $company       = isset($post['company']) && $post['company'] !== '' ? trim($post['company']) : null;
        $locationCode  = trim($post['locationCode']);
        $locationName  = trim($post['locationName']);
        $plant         = trim($post['plant']);
        $weighingCount = isset($post['weighingCount']) && $post['weighingCount'] !== '' ? trim($post['weighingCount']) : '2';

        if ($this->isDuplicate('location_code', $locationCode, $company)) {
            throw new Exception('Location code already exists');
        }

        $this->db->begin_transaction();

        // Insert Location
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (company, location_code, location_name, plant_id, weighing_count) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sssss', $company, $locationCode, $locationName, $plant, $weighingCount);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $newId = $stmt->insert_id;
        $stmt->close();

        // Insert Port (copy from default)
        $portStmt = $this->db->prepare("INSERT INTO Port (com_port, bits_per_second, data_bits, parity, stop_bits, indicator_id, indicator, weighbridge_id, weighind_id, created_by, modified_by) SELECT com_port, bits_per_second, data_bits, parity, stop_bits, indicator_id, indicator, weighbridge_id, ?, ?, modified_by FROM Port WHERE id = 1");
        if (!$portStmt) throw new Exception($this->db->error);
        $portStmt->bind_param('ss', $newId, $this->username);
        if (!$portStmt->execute()) throw new Exception($portStmt->error);
        $portId = $portStmt->insert_id;
        $portStmt->close();

        // Link port_id to Location
        $linkStmt = $this->db->prepare("UPDATE {$this->table} SET port_id=? WHERE id=?");
        if (!$linkStmt) throw new Exception($this->db->error);
        $linkStmt->bind_param('ss', $portId, $newId);
        if (!$linkStmt->execute()) throw new Exception($linkStmt->error);
        $linkStmt->close();

        $this->db->commit();

        return ['id' => $newId];
    }

    /**
     * Update existing location
     */
    public function update($post) {
        $id            = $post['id'];
        $company       = isset($post['company']) && $post['company'] !== '' ? trim($post['company']) : null;
        $locationCode  = trim($post['locationCode']);
        $locationName  = trim($post['locationName']);
        $plant         = trim($post['plant']);
        $weighingCount = isset($post['weighingCount']) && $post['weighingCount'] !== '' ? trim($post['weighingCount']) : '2';

        if ($this->isDuplicate('location_code', $locationCode, $company, $id)) {
            throw new Exception('Location code already exists');
        }

        $this->db->begin_transaction();

        $stmt = $this->db->prepare("UPDATE {$this->table} SET company=?, location_code=?, location_name=?, plant_id=?, weighing_count=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('ssssss', $company, $locationCode, $locationName, $plant, $weighingCount, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

        $this->db->commit();

        return ['id' => $id];
    }

    /**
     * Delete (soft delete) location
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1 WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    /**
     * Get single location by ID (optionally with Port data)
     */
    public function get($id, $withPort = false) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) return null;

        if ($withPort) {
            $portStmt = $this->db->prepare("SELECT * FROM Port WHERE id=?");
            $portStmt->bind_param('s', $row['port_id']);
            $portStmt->execute();
            $port = $portStmt->get_result()->fetch_assoc();
            $portStmt->close();

            if ($port) {
                $row['port_id']         = $port['id'];
                $row['com_port']        = $port['com_port'];
                $row['bits_per_second'] = $port['bits_per_second'];
                $row['data_bits']       = $port['data_bits'];
                $row['parity']          = $port['parity'];
                $row['stop_bits']       = $port['stop_bits'];
                $row['indicator']       = $port['indicator'];
            }
        }

        return $row;
    }

    /**
     * Save port setup
     */
    public function savePortSetup($post) {
        $portId             = trim($post['id']);
        $indicator          = !empty($post['indicator'])          ? trim($post['indicator'])          : 'D2008';
        $serialPort         = !empty($post['serialPort'])         ? trim($post['serialPort'])         : 'COM3';
        $serialPortBaudRate = !empty($post['serialPortBaudRate']) ? trim($post['serialPortBaudRate']) : '2400';
        $serialPortDataBits = !empty($post['serialPortDataBits']) ? trim($post['serialPortDataBits']) : '7';
        $serialPortParity   = !empty($post['serialPortParity'])   ? trim($post['serialPortParity'])   : 'E';
        $serialPortStopBits = !empty($post['serialPortStopBits']) ? trim($post['serialPortStopBits']) : '1';

        $stmt = $this->db->prepare("UPDATE Port SET indicator=?, com_port=?, bits_per_second=?, data_bits=?, parity=?, stop_bits=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sssssss', $indicator, $serialPort, $serialPortBaudRate, $serialPortDataBits, $serialPortParity, $serialPortStopBits, $portId);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function getListByCompany($companyId) {
        $stmt = $this->db->prepare("SELECT id, location_code, location_name, plant_id FROM {$this->table} WHERE company = ? AND status = '0' ORDER BY location_name");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('i', $companyId);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $result = $stmt->get_result();
        $list = [];
        while ($row = $result->fetch_assoc()) {
            $list[] = $row;
        }
        $stmt->close();
        return $list;
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
