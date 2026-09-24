<?php
require_once __DIR__ . '/BaseService.php';

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

        // Filtered records
        $filteredResult = $this->db->query("SELECT COUNT(*) as total FROM {$this->table} cust LEFT JOIN Plant pl ON cust.plant_id = pl.id WHERE cust.status = '0' {$searchQuery}");
        $totalFiltered = $filteredResult->fetch_assoc()['total'];

        // Order by
        $orderBy = $columnName === 'plant' ? "pl.name {$columnSortOrder}" : "cust.{$columnName} {$columnSortOrder}";

        // Data
        $dataResult = $this->db->query("SELECT cust.*, pl.name as plant FROM {$this->table} cust LEFT JOIN Plant pl ON cust.plant_id = pl.id WHERE cust.status = '0' {$searchQuery} ORDER BY {$orderBy} LIMIT {$start}, {$length}");

        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = [
                'id'             => $row['id'],
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
        $locationCode  = trim($post['locationCode']);
        $locationName  = trim($post['locationName']);
        $plant         = trim($post['plant']);
        $weighingCount = isset($post['weighingCount']) && $post['weighingCount'] !== '' ? trim($post['weighingCount']) : '2';

        if ($this->isDuplicate('location_code', $locationCode)) {
            throw new Exception('Location code already exists');
        }

        $this->db->begin_transaction();

        // Insert Location
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (location_code, location_name, plant_id, weighing_count) VALUES (?, ?, ?, ?)");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('ssss', $locationCode, $locationName, $plant, $weighingCount);
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
        $locationCode  = trim($post['locationCode']);
        $locationName  = trim($post['locationName']);
        $plant         = trim($post['plant']);
        $weighingCount = isset($post['weighingCount']) && $post['weighingCount'] !== '' ? trim($post['weighingCount']) : '2';

        if ($this->isDuplicate('location_code', $locationCode, $id)) {
            throw new Exception('Location code already exists');
        }

        $this->db->begin_transaction();

        $stmt = $this->db->prepare("UPDATE {$this->table} SET location_code=?, location_name=?, plant_id=?, weighing_count=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sssss', $locationCode, $locationName, $plant, $weighingCount, $id);
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
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}
?>
