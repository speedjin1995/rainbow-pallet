<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

class SawnTimberService extends BaseService {

    public function __construct($db, $username) {
        parent::__construct($db, $username);
    }

    // ─── List (DataTable) ────────────────────────────────────────────────────────
    public function getList($params) {
        $draw = $params['draw'];
        $start = $params['start'];
        $length = $params['length'];
        $columnIndex = $params['order'][0]['column'];
        $columnName = $params['columns'][$columnIndex]['data'];
        $columnSortOrder = $params['order'][0]['dir'];
        $searchValue = mysqli_real_escape_string($this->db, $params['search']['value']);

        $searchQuery = "";

        if (!empty($params['fromDate'])) {
            $dateTime = DateTime::createFromFormat('d-m-Y', $params['fromDate']);
            $searchQuery .= " AND h.record_date >= '" . $dateTime->format('Y-m-d 00:00:00') . "'";
        }
        if (!empty($params['toDate'])) {
            $dateTime = DateTime::createFromFormat('d-m-Y', $params['toDate']);
            $searchQuery .= " AND h.record_date <= '" . $dateTime->format('Y-m-d 23:59:59') . "'";
        }
        if (!empty($params['company']) && $params['company'] != '-') {
            $searchQuery .= " AND h.company_id = '" . mysqli_real_escape_string($this->db, $params['company']) . "'";
        }
        if (!empty($params['plant']) && $params['plant'] != '-') {
            $searchQuery .= " AND h.plant_id = '" . mysqli_real_escape_string($this->db, $params['plant']) . "'";
        }
        if (!empty($params['transactionId'])) {
            $searchQuery .= " AND COALESCE(h.transaction_id, w.transaction_id) LIKE '%" . mysqli_real_escape_string($this->db, $params['transactionId']) . "%'";
        }
        if (!empty($params['customerSupplier'])) {
            [$partyType, $partyCode] = array_pad(explode(':', $params['customerSupplier'], 2), 2, '');
            $partyCode = mysqli_real_escape_string($this->db, $partyCode);
            if ($partyType === 'customer' && $partyCode !== '') {
                $searchQuery .= " AND w.customer_code = '" . $partyCode . "'";
            } elseif ($partyType === 'supplier' && $partyCode !== '') {
                $searchQuery .= " AND (h.supplier_code = '" . $partyCode . "' OR w.supplier_code = '" . $partyCode . "')";
            }
        }
        if ($searchValue != '') {
            $searchQuery = " AND (COALESCE(h.transaction_id, w.transaction_id) LIKE '%" . $searchValue . "%' OR w.lorry_plate_no1 LIKE '%" . $searchValue . "%')";
        }

        // Total records
        $sel = mysqli_query($this->db, "SELECT COUNT(*) as allcount FROM Sawn_Timber_Header h WHERE h.status = 0");
        $totalRecords = mysqli_fetch_assoc($sel)['allcount'];

        // Total records with filter
        $sel = mysqli_query($this->db, "SELECT COUNT(*) as allcount FROM Sawn_Timber_Header h LEFT JOIN Weight w ON h.weight_id=w.id WHERE h.status = 0" . $searchQuery);
        $totalRecordwithFilter = mysqli_fetch_assoc($sel)['allcount'];

        // Fetch records
        $sql = "SELECT h.id, h.company_id, h.plant_id, h.weight_id, COALESCE(h.transaction_id, w.transaction_id) AS transaction_id, h.record_date, 
            c.name AS customer_name, COALESCE(sh.name, s.name) AS supplier_name, h.remarks, h.status, 
            COALESCE(SUM(d.pieces),0) AS total_pieces, COALESCE(SUM(d.tons),0) AS total_tons 
            FROM Sawn_Timber_Header h 
            LEFT JOIN Sawn_Timber_Detail d ON h.id=d.header_id 
            LEFT JOIN Weight w ON h.weight_id=w.id 
            LEFT JOIN Customer c ON w.customer_code=c.customer_code 
            LEFT JOIN Supplier s ON w.supplier_code=s.supplier_code 
            LEFT JOIN Supplier sh ON h.supplier_code=sh.supplier_code 
            WHERE h.status = 0" . $searchQuery . " 
            GROUP BY h.id 
            ORDER BY " . $columnName . " " . $columnSortOrder . " 
            LIMIT " . $start . "," . $length;

        $result = mysqli_query($this->db, $sql);
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $customerSupplier = $row['customer_name'] ?? $row['supplier_name'] ?? '';
            $data[] = [
                "id" => $row['id'],
                "record_date" => $row['record_date'] ? date('d-m-Y', strtotime($row['record_date'])) : '',
                "transaction_id" => $row['transaction_id'],
                "company" => searchCompanyById($row['company_id'], $this->db)['name'],
                "plant" => searchPlantNameById($row['plant_id'], $this->db),
                "customer_supplier" => $customerSupplier,
                "total_pieces" => $row['total_pieces'],
                "total_tons" => number_format((float)$row['total_tons'], 4, '.', ''),
                "remarks" => $row['remarks'],
                "status" => $row['status']
            ];
        }

        return [
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data
        ];
    }

    // ─── Get Single Record ───────────────────────────────────────────────────────
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT h.*, c.name AS company_name, CONCAT(pl.plant_code, ' - ', pl.name) AS plant_display, 
            w.transaction_status, w.transaction_date, w.customer_name, 
            COALESCE(h.transaction_id, w.transaction_id) AS transaction_id, COALESCE(sh.name, w.supplier_name) AS supplier_name,
            w.destination, w.lorry_plate_no1, w.delivery_no 
            FROM Sawn_Timber_Header h 
            LEFT JOIN Company c ON h.company_id = c.id 
            LEFT JOIN Plant pl ON h.plant_id = pl.id 
            LEFT JOIN Weight w ON h.weight_id = w.id 
            LEFT JOIN Supplier sh ON h.supplier_code = sh.supplier_code 
            WHERE h.id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $header = $result->fetch_assoc();
        $stmt->close();

        if (!$header) {
            return null;
        }

        $details = $this->getDetails($id);

        return [
            "header" => $header,
            "details" => $details
        ];
    }

    // ─── Get Details For Row Expansion ───────────────────────────────────────────
    public function getDetailsForExpansion($id) {
        $stmt = $this->db->prepare("SELECT h.record_date, h.remarks, COALESCE(h.transaction_id, w.transaction_id) AS transaction_id, w.delivery_no, w.lorry_plate_no1, w.destination 
            FROM Sawn_Timber_Header h 
            LEFT JOIN Weight w ON h.weight_id = w.id 
            WHERE h.id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $header = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $details = $this->getDetails($id);

        return [
            "record_date" => $header['record_date'] ?? '',
            "transaction_id" => $header['transaction_id'] ?? '',
            "delivery_no" => $header['delivery_no'] ?? '',
            "lorry_plate_no1" => $header['lorry_plate_no1'] ?? '',
            "destination" => $header['destination'] ?? '',
            "remarks" => $header['remarks'] ?? '',
            "details" => $details
        ];
    }

    // ─── Get Details ─────────────────────────────────────────────────────────────
    public function getDetails($headerId) {
        $stmt = $this->db->prepare("SELECT * FROM Sawn_Timber_Detail WHERE header_id = ? ORDER BY id ASC");
        $stmt->bind_param('i', $headerId);
        $stmt->execute();
        $result = $stmt->get_result();

        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
        $stmt->close();

        return $details;
    }

    // ─── Get Weighing Transactions ───────────────────────────────────────────────
    public function getWeighingTransactions() {
        $sql = "SELECT w.id, w.transaction_id, w.transaction_status, 
            w.customer_code, w.customer_name, w.supplier_code, w.supplier_name,
            w.destination, w.lorry_plate_no1, w.delivery_no, w.transaction_date,
            w.company_id, c.name AS company_name, w.plant_code, w.plant_name, pl.id AS plant_id
            FROM Weight w
            LEFT JOIN Product p ON w.product_code = p.product_code
            LEFT JOIN Product_Categories pc ON p.category = pc.id
            LEFT JOIN Company c ON w.company_id = c.id
            LEFT JOIN Plant pl ON w.plant_code = pl.plant_code
            WHERE pc.category_name = 'Sawn Timber'
            AND w.status = 0
            AND w.is_complete = 'Y'
            AND w.synced = 'N'
            AND NOT EXISTS (
                SELECT 1 FROM Sawn_Timber_Header sth 
                WHERE sth.weight_id = w.id AND sth.status = '0'
            )
            ORDER BY w.transaction_date DESC";

        $result = $this->db->query($sql);
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => $row['id'],
                'transaction_id' => $row['transaction_id'],
                'transaction_status' => $row['transaction_status'],
                'customer_code' => $row['customer_code'],
                'customer_name' => $row['customer_name'],
                'supplier_code' => $row['supplier_code'],
                'supplier_name' => $row['supplier_name'],
                'destination' => $row['destination'],
                'lorry_plate_no1' => $row['lorry_plate_no1'],
                'delivery_no' => $row['delivery_no'],
                'transaction_date' => $row['transaction_date'],
                'company_id' => $row['company_id'],
                'company_name' => $row['company_name'],
                'plant_id' => $row['plant_id'],
                'plant_display' => $row['plant_code'] . ' - ' . $row['plant_name']
            ];
        }

        return $data;
    }

    // ─── Save (Create/Update) ────────────────────────────────────────────────────
    public function save($data) {
        $id = $data['id'] ?? null;
        $companyId = $data['companyId'] ?? null;
        $plantId = $data['plantId'] ?? null;
        $weightId = !empty($data['weightId']) ? $data['weightId'] : null;
        $transactionId = $data['transactionId'] ?? null;
        $supplierCode = $data['supplierCode'] ?? null;
        $headerLot = $data['lot'] ?? null;
        $sawnTimberDate = !empty($data['sawnTimberDate']) ? DateTime::createFromFormat('d-m-Y', $data['sawnTimberDate'])->format('Y-m-d H:i:s') : null;
        $remarks = $data['remarks'] ?? null;

        if (empty($plantId)) {
            $plantRes = $this->db->query("SELECT id FROM Plant WHERE status='0' ORDER BY id ASC LIMIT 1");
            $plantRow = $plantRes ? $plantRes->fetch_assoc() : null;
            $plantId = $plantRow['id'] ?? null;
        }

        $speciesArr = $data['species'] ?? [];
        $lotArr = $data['lot'] ?? [];
        $bundleArr = $data['bundle'] ?? [];
        $thickArr = $data['thick'] ?? [];
        $widthArr = $data['width'] ?? [];
        $lengthArr = $data['length'] ?? [];
        $piecesArr = $data['pieces'] ?? [];
        $tonsArr = $data['tons'] ?? [];
        $kdChargesArr = $data['kdCharges'] ?? [];
        $bundlingChargesArr = $data['bundlingCharges'] ?? [];
        $graderFeesArr = $data['graderFees'] ?? [];

        if (!$companyId || !$plantId || !$transactionId || !$supplierCode || !$headerLot || !$sawnTimberDate || empty($speciesArr)) {
            throw new Exception("Please fill in all the fields");
        }

        $this->db->begin_transaction();

        try {
            // Check duplicate
            $duplicateCheck = $this->db->prepare("SELECT id FROM Sawn_Timber_Header WHERE transaction_id = ? AND status = 0" . (!empty($id) ? " AND id != ?" : ""));
            if (!empty($id)) {
                $duplicateCheck->bind_param('si', $transactionId, $id);
            } else {
                $duplicateCheck->bind_param('s', $transactionId);
            }
            $duplicateCheck->execute();
            $duplicateCheck->store_result();

            if ($duplicateCheck->num_rows > 0) {
                $duplicateCheck->close();
                throw new Exception("Transaction ID already exists");
            }
            $duplicateCheck->close();

            // Set action by for triggers
            $actionByStmt = $this->db->prepare("SET @sawn_timber_action_by=?");
            $actionByStmt->bind_param('s', $this->username);
            $actionByStmt->execute();
            $actionByStmt->close();

            if (!empty($id)) {
                // Update
                $stmt = $this->db->prepare("UPDATE Sawn_Timber_Header SET company_id=?, plant_id=?, weight_id=?, transaction_id=?, supplier_code=?, lot=?, record_date=?, remarks=?, modified_by=? WHERE id=?");
                $stmt->bind_param('iiissssssi', $companyId, $plantId, $weightId, $transactionId, $supplierCode, $headerLot, $sawnTimberDate, $remarks, $this->username, $id);
                $stmt->execute();
                $stmt->close();

                // Delete existing details
                $deleteStmt = $this->db->prepare("DELETE FROM Sawn_Timber_Detail WHERE header_id=?");
                $deleteStmt->bind_param('i', $id);
                $deleteStmt->execute();
                $deleteStmt->close();
            } else {
                // Insert
                $stmt = $this->db->prepare("INSERT INTO Sawn_Timber_Header (company_id, plant_id, weight_id, transaction_id, supplier_code, lot, record_date, remarks, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('iiisssssss', $companyId, $plantId, $weightId, $transactionId, $supplierCode, $headerLot, $sawnTimberDate, $remarks, $this->username, $this->username);
                $stmt->execute();
                $id = $stmt->insert_id;
                $stmt->close();
            }

            // Insert details
            $detailStmt = $this->db->prepare("INSERT INTO Sawn_Timber_Detail (header_id, species, lot, bundle, thick, width, length, pieces, tons, kd_charges, bundling_charges, grader_fees) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($speciesArr as $index => $species) {
                $lot = $headerLot;
                $bundle = $bundleArr[$index] ?? null;
                $thick = $this->parseDimension($thickArr[$index] ?? 0);
                $width = $this->parseDimension($widthArr[$index] ?? 0);
                $length = $this->parseDimension($lengthArr[$index] ?? 0);
                $pieces = $piecesArr[$index] ?? 0;
                $tons = $tonsArr[$index] ?? 0;
                $kdCharges = !empty($kdChargesArr[$index]) ? $kdChargesArr[$index] : null;
                $bundlingCharges = !empty($bundlingChargesArr[$index]) ? $bundlingChargesArr[$index] : null;
                $graderFees = !empty($graderFeesArr[$index]) ? $graderFeesArr[$index] : null;

                $detailStmt->bind_param('isssdddddddd', $id, $species, $lot, $bundle, $thick, $width, $length, $pieces, $tons, $kdCharges, $bundlingCharges, $graderFees);
                $detailStmt->execute();
            }
            $detailStmt->close();

            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // ─── Delete (Soft Delete) ────────────────────────────────────────────────────
    public function delete($id, $isMulti = false) {
        $actionByStmt = $this->db->prepare("SET @sawn_timber_action_by=?");
        $actionByStmt->bind_param('s', $this->username);
        $actionByStmt->execute();
        $actionByStmt->close();

        $del = "1";

        if ($isMulti && is_array($id)) {
            $ids = implode(",", array_map('intval', $id));
            $stmt = $this->db->prepare("UPDATE Sawn_Timber_Header SET status=?, modified_by=? WHERE id IN ($ids)");
            $stmt->bind_param('ss', $del, $this->username);
        } else {
            $stmt = $this->db->prepare("UPDATE Sawn_Timber_Header SET status=?, modified_by=? WHERE id=?");
            $stmt->bind_param('ssi', $del, $this->username, $id);
        }

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();

        return true;
    }

    // ─── Export Data ─────────────────────────────────────────────────────────────
    public function getExportData($params) {
        $where = " WHERE h.status='0'";
        $bindParams = [];
        $types = '';

        if (!empty($params['fromDate'])) {
            $dateTime = DateTime::createFromFormat('d-m-Y', $params['fromDate']);
            if ($dateTime) {
                $where .= " AND h.record_date >= ?";
                $bindParams[] = $dateTime->format('Y-m-d 00:00:00');
                $types .= 's';
            }
        }

        if (!empty($params['toDate'])) {
            $dateTime = DateTime::createFromFormat('d-m-Y', $params['toDate']);
            if ($dateTime) {
                $where .= " AND h.record_date <= ?";
                $bindParams[] = $dateTime->format('Y-m-d 23:59:59');
                $types .= 's';
            }
        }

        if (!empty($params['company']) && $params['company'] != '-') {
            $where .= " AND h.company_id = ?";
            $bindParams[] = $params['company'];
            $types .= 'i';
        }

        if (!empty($params['plant']) && $params['plant'] != '-') {
            $where .= " AND h.plant_id = ?";
            $bindParams[] = $params['plant'];
            $types .= 'i';
        }

        if (!empty($params['transactionId'])) {
            $where .= " AND COALESCE(h.transaction_id, w.transaction_id) LIKE ?";
            $bindParams[] = '%' . $params['transactionId'] . '%';
            $types .= 's';
        }

        if (!empty($params['customerSupplier'])) {
            [$partyType, $partyCode] = array_pad(explode(':', $params['customerSupplier'], 2), 2, '');
            if ($partyType === 'customer' && $partyCode !== '') {
                $where .= " AND w.customer_code = ?";
                $bindParams[] = $partyCode;
                $types .= 's';
            } elseif ($partyType === 'supplier' && $partyCode !== '') {
                $where .= " AND (h.supplier_code = ? OR w.supplier_code = ?)";
                $bindParams[] = $partyCode;
                $bindParams[] = $partyCode;
                $types .= 'ss';
            }
        }

        $sql = "SELECT h.record_date, COALESCE(h.transaction_id, w.transaction_id) AS transaction_id, h.record_date AS transaction_date, c.name AS company_name, 
                CONCAT(p.plant_code, ' - ', p.name) AS plant_name, 
                COALESCE(cust.name, sh.name, sup.name) AS customer_supplier,
                w.delivery_no, w.lorry_plate_no1, w.destination,
                d.species, d.lot, d.bundle, d.thick, d.width, d.length, d.pieces, d.tons,
                d.kd_charges, d.bundling_charges, d.grader_fees, h.remarks
                FROM Sawn_Timber_Header h
                LEFT JOIN Sawn_Timber_Detail d ON d.header_id = h.id
                LEFT JOIN Weight w ON h.weight_id = w.id
                LEFT JOIN Company c ON h.company_id = c.id
                LEFT JOIN Plant p ON h.plant_id = p.id
                LEFT JOIN Customer cust ON w.customer_code = cust.customer_code
                LEFT JOIN Supplier sup ON w.supplier_code = sup.supplier_code
                LEFT JOIN Supplier sh ON h.supplier_code = sh.supplier_code
                $where
                ORDER BY h.record_date DESC, transaction_id DESC, d.id ASC";

        $stmt = $this->db->prepare($sql);
        if (!empty($bindParams)) {
            $stmt->bind_param($types, ...$bindParams);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();

        return $data;
    }

    // ─── Get Dropdown Lists for Template ─────────────────────────────────────────
    public function getDropdownLists() {
        $lists = [];

        $result = $this->db->query("SELECT name FROM Company WHERE status='0' ORDER BY name ASC");
        $lists['companies'] = [];
        while ($row = $result->fetch_assoc()) {
            $lists['companies'][] = $row['name'];
        }

        $result = $this->db->query("SELECT name FROM Plant WHERE status='0' ORDER BY name ASC");
        $lists['plants'] = [];
        while ($row = $result->fetch_assoc()) {
            $lists['plants'][] = $row['name'];
        }

        $result = $this->db->query("SELECT name FROM Supplier WHERE status='0' ORDER BY name ASC");
        $lists['suppliers'] = [];
        while ($row = $result->fetch_assoc()) {
            $lists['suppliers'][] = $row['name'];
        }

        $result = $this->db->query("SELECT name FROM Sawn_Timber_Species WHERE status=0 ORDER BY name ASC");
        $lists['species'] = [];
        while ($row = $result->fetch_assoc()) {
            $lists['species'][] = $row['name'];
        }

        return $lists;
    }

    private function parseDimension($value) {
        $value = trim((string)$value);

        if ($value === '') {
            return 0;
        }

        $parts = preg_split('/\s+/', $value);
        $total = 0;

        foreach ($parts as $part) {
            if (strpos($part, '/') !== false) {
                $fraction = explode('/', $part);
                $numerator = isset($fraction[0]) ? (float)$fraction[0] : 0;
                $denominator = isset($fraction[1]) ? (float)$fraction[1] : 0;
                $total += $denominator > 0 ? $numerator / $denominator : 0;
            } else {
                $total += (float)$part;
            }
        }

        return $total;
    }
}
?>
