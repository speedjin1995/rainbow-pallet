<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

class DeliveryOrderService extends BaseService {
    const MODULE = 'Delivery Order';
    const GROUP_BY = ' GROUP BY company_id, plant_code, product_code, customer_code';

    // DataTables column => DB column (only whitelisted columns can be sorted)
    private $sortColumns = [
        'id'               => 'id',
        'company'          => 'company_id',
        'customer_name'    => 'customer_name',
        'product_name'     => 'product_name',
        'plant_name'       => 'plant_name',
        'transaction_date' => 'transaction_date',
        'order_weight'     => 'order_weight',
    ];

    public function filter($post) {
        $draw       = intval($post['draw'] ?? 0);
        $start      = intval($post['start'] ?? 0);
        $length     = intval($post['length'] ?? 10);
        $columnName = $post['columns'][$post['order'][0]['column'] ?? 0]['data'] ?? 'id';
        $sortColumn = $this->sortColumns[$columnName] ?? 'id';
        $sortOrder  = strtolower($post['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

        $scope   = $this->buildScope($post['company'] ?? null);
        $filters = $this->buildFilters($post);
        $search  = trim($post['search']['value'] ?? '');
        if ($search !== '') {
            $filters['sql']     .= " AND (transaction_id LIKE ? OR lorry_plate_no1 LIKE ?)";
            $filters['types']   .= 'ss';
            $filters['values'][] = "%{$search}%";
            $filters['values'][] = "%{$search}%";
        }

        $totalRecords  = $this->countGroups($scope['sql'], $scope['types'], $scope['values']);
        $totalFiltered = $this->countGroups(
            $scope['sql'] . $filters['sql'],
            $scope['types'] . $filters['types'],
            array_merge($scope['values'], $filters['values'])
        );

        $rows = $this->fetchAll(
            "SELECT * FROM Weight WHERE 1=1" . $scope['sql'] . $filters['sql'] . self::GROUP_BY . " ORDER BY {$sortColumn} {$sortOrder} LIMIT ?, ?",
            $scope['types'] . $filters['types'] . 'ii',
            array_merge($scope['values'], $filters['values'], [$start, $length])
        );

        $data = [];
        foreach ($rows as $row) {
            $company = searchCompanyById($row['company_id'], $this->db);
            $data[] = [
                'id'                 => $row['id'],
                'company'            => $company ? $company['name'] : '',
                'transaction_id'     => $row['transaction_id'],
                'transaction_status' => $row['transaction_status'],
                'weight_type'        => $row['weight_type'],
                'transaction_date'   => $row['transaction_date'],
                'customer_code'      => $row['customer_code'],
                'customer_name'      => $row['customer_name'],
                'customer'           => $row['customer_name'],
                'product_code'       => $row['product_code'],
                'product_name'       => $row['product_name'],
                'purchase_order'     => $row['purchase_order'],
                'plant_code'         => $row['plant_code'],
                'plant_name'         => $row['plant_name'],
                'delivery_no'        => $row['delivery_no'],
                'order_weight'       => $row['order_weight'],
                'supplier_weight'    => $row['supplier_weight'],
                'tare_weight1_date'  => $row['tare_weight1_date'],
                'created_date'       => $row['created_date'],
                'created_by'         => $row['created_by'],
                'modified_date'      => $row['modified_date'],
                'modified_by'        => $row['modified_by'],
            ];
        }

        return [
            'draw'                 => $draw,
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $totalFiltered,
            'aaData'               => $data,
        ];
    }

    /**
     * Build the tab-separated Excel export
     * isMulti = 'Y' exports every weighing in the selected groups, otherwise every weighing matching the filters
     */
    public function export($get) {
        $includePrice = hasModulePermission('Accounting', self::MODULE, ['include_price']);

        $fields = ['DocNo', 'DOCREF2', 'DOCDATE', 'DESCRIPTION2', 'CODE', 'COMPANYNAME', 'ITEMCODE', 'DESCRIPTION', 'REMARK2', 'SHIPPER', 'DOCREF1', 'DOCNOEX', 'REMARK1', 'QTY', 'UOM', 'PROJECT', 'LOCATION'];
        if ($includePrice) {
            array_push($fields, 'UNITPRICE', 'Amount');
        }
        $fields[] = 'Remarks';

        if (($get['isMulti'] ?? 'N') === 'Y') {
            $rows = [];
            foreach ($this->fetchSelected($get['id'] ?? '', $get['company'] ?? null) as $group) {
                $rows = array_merge($rows, $this->fetchGroupWeights($group, $get['fromDate'] ?? '', $get['toDate'] ?? ''));
            }
        } else {
            $scope   = $this->buildScope($get['company'] ?? null);
            $filters = $this->buildFilters($get);
            $rows = $this->fetchAll(
                "SELECT * FROM Weight WHERE 1=1" . $scope['sql'] . $filters['sql'] . " ORDER BY plant_code ASC, purchase_order ASC",
                $scope['types'] . $filters['types'],
                array_merge($scope['values'], $filters['values'])
            );
        }

        $excelData = implode("\t", $fields) . "\n";
        if (empty($rows)) {
            $excelData .= 'No records found...' . "\n";
        }

        foreach ($rows as $row) {
            $qty       = (float) $row['nett_weight1'] / 1000;
            $unitPrice = $row['unit_price'] ?? 0;
            $plantCode = $row['plant_code'];

            $lineData = ['', $row['transaction_id'], $this->formatDate($row['transaction_date']), $row['lorry_plate_no1'], $row['customer_code'], $row['customer_name'], $row['product_code'], $row['product_name'], $row['destination'], $row['transporter_code'], '', '', $row['delivery_no'], $qty, 'MT', $plantCode, $plantCode];
            if ($includePrice) {
                array_push($lineData, $unitPrice, $qty * (float) $unitPrice);
            }
            $lineData[] = $row['remarks'];

            foreach ($lineData as $key => $value) {
                if ($key == 3) { // lorry_plate_no1 is at index 3
                    $lineData[$key] = '="' . $value . '"';
                } else {
                    $lineData[$key] = $this->filterExcelValue($value);
                }
            }
            $excelData .= implode("\t", $lineData) . "\n";
        }

        return [
            'fileName' => 'DO-data_' . date('Y-m-d') . '.xls',
            'content'  => $excelData,
        ];
    }

    /**
     * Post delivery orders to the SQL accounting API
     * MULTI   = selected groups (all weighings in each group within the date range)
     * MULTIDO = selected individual weighings
     * other   = all unsynced weighings matching the filters
     */
    public function post($post) {
        $config     = $this->getSqlConfig();
        $companyKey = $_SESSION['company'] ?? null;
        if (!$companyKey || !isset($config[$companyKey])) {
            throw new Exception('Invalid company session');
        }

        $type = $post['type'] ?? '';
        if ($type === 'MULTI') {
            $rows = [];
            foreach ($this->fetchSelected($post['userID'] ?? '', $post['company'] ?? null, true) as $group) {
                $rows = array_merge($rows, $this->fetchGroupWeights($group, $post['fromDate'] ?? '', $post['toDate'] ?? ''));
            }
        } else if ($type === 'MULTIDO') {
            $rows = $this->fetchSelected($post['userID'] ?? '', $post['company'] ?? null, true);
        } else {
            $scope   = $this->buildScope($post['company'] ?? null);
            $filters = $this->buildFilters($post);
            $rows = $this->fetchAll(
                "SELECT * FROM Weight WHERE synced = 'N'" . $scope['sql'] . $filters['sql'],
                $scope['types'] . $filters['types'],
                array_merge($scope['values'], $filters['values'])
            );
        }

        if (empty($rows)) {
            throw new Exception('No record founds');
        }

        $records = [];
        foreach ($rows as $row) {
            $qty       = (float) $row['nett_weight1'] / 1000;
            $unitPrice = $row['unit_price'] ?? 0;
            $plantCode = $row['plant_code'];

            $records[] = [
                'DOCREF2'      => $row['transaction_id'],
                'DOCDATE'      => $this->formatDate($row['transaction_date']),
                'DESCRIPTION2' => $row['lorry_plate_no1'],
                'CODE'         => $row['customer_code'] ?? '',
                'COMPANYNAME'  => $row['customer_name'],
                'ITEMCODE'     => $row['product_code'],
                'DESCRIPTION'  => $row['product_name'],
                'REMARK2'      => $row['destination'] ?? '-',
                'SHIPPER'      => $row['transporter_code'] ?? '',
                'DOCREF1'      => '',
                'DOCNOEX'      => '',
                'REMARK1'      => $row['delivery_no'],
                'QTY'          => round($qty, 2),
                'UOM'          => 'MT',
                'PROJECT'      => $plantCode,
                'LOCATION'     => $plantCode,
                'UNITPRICE'    => round($unitPrice, 2),
                'AMOUNT'       => round($qty * (float) $unitPrice, 2),
            ];
        }

        return $this->sendToApi(rtrim($config[$companyKey], '/') . '/delivery_order', $records);
    }

    /**
     * Base conditions for every DO query: completed sales weighings within the user's company/plant scope
     */
    private function buildScope($requestedCompany) {
        $sql    = " AND is_complete = 'Y' AND is_cancel <> 'Y' AND status = '0' AND transaction_status = 'Sales'";
        $types  = '';
        $values = [];

        // Determine company on the backend - never trust frontend value for restricted users
        if (hasModulePermission('Accounting', self::MODULE, ['view_all_companies'])) {
            $companyId = ($requestedCompany !== null && $requestedCompany !== '' && $requestedCompany !== '-') ? intval($requestedCompany) : null;
        } else {
            $companyId = intval($_SESSION['company_id'] ?? 0);
        }
        if ($companyId !== null) {
            $sql     .= " AND company_id = ?";
            $types   .= 'i';
            $values[] = $companyId;
        }

        if (!hasModulePermission('Accounting', self::MODULE, ['view_all_plants'])) {
            $plants = $this->getAllowedPlantCodes();
            if (empty($plants)) {
                $sql .= " AND 1=0";
            } else {
                $sql   .= " AND plant_code IN (" . implode(',', array_fill(0, count($plants), '?')) . ")";
                $types .= str_repeat('s', count($plants));
                $values = array_merge($values, $plants);
            }
        }

        return ['sql' => $sql, 'types' => $types, 'values' => $values];
    }

    /**
     * Plants a user without view_all_plants may see:
     * the plant selected at login if any, otherwise every plant the user is tied to
     */
    private function getAllowedPlantCodes() {
        $selectedPlantId = intval($_SESSION['selected_plant_id'] ?? 0);
        if ($selectedPlantId > 0) {
            $rows = $this->fetchAll("SELECT plant_code FROM Plant WHERE id = ? AND status = '0'", 'i', [$selectedPlantId]);
            return array_column($rows, 'plant_code');
        }
        return array_values((array) ($_SESSION['plant'] ?? []));
    }

    /**
     * Optional search filters from the page
     */
    private function buildFilters($input) {
        $sql    = '';
        $types  = '';
        $values = [];

        $fromDate = $this->toDbDateTime($input['fromDate'] ?? '');
        if ($fromDate !== null) {
            $sql .= " AND transaction_date >= ?"; $types .= 's'; $values[] = $fromDate;
        }
        $toDate = $this->toDbDateTime($input['toDate'] ?? '');
        if ($toDate !== null) {
            $sql .= " AND transaction_date <= ?"; $types .= 's'; $values[] = $toDate;
        }

        $map = [
            'customer'      => 'customer_code',
            'product'       => 'product_code',
            'plant'         => 'plant_code',
            'deliveryNo'    => 'delivery_no',
            'transactionId' => 'transaction_id',
        ];
        foreach ($map as $key => $column) {
            $value = isset($input[$key]) ? trim($input[$key]) : '';
            if ($value !== '' && $value !== '-') {
                $sql .= " AND {$column} = ?"; $types .= 's'; $values[] = $value;
            }
        }

        return ['sql' => $sql, 'types' => $types, 'values' => $values];
    }

    /**
     * Fetch selected Weight rows by id, restricted to the user's company/plant scope
     */
    private function fetchSelected($ids, $requestedCompany, $unsyncedOnly = false) {
        $ids = array_filter(array_map('intval', is_array($ids) ? $ids : explode(',', (string) $ids)));
        if (empty($ids)) {
            return [];
        }

        $scope = $this->buildScope($requestedCompany);
        $sql = "SELECT * FROM Weight WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")" . $scope['sql'];
        if ($unsyncedOnly) {
            $sql .= " AND synced = 'N'";
        }

        return $this->fetchAll($sql . " ORDER BY id ASC", str_repeat('i', count($ids)) . $scope['types'], array_merge(array_values($ids), $scope['values']));
    }

    /**
     * All weighings in the same company/plant/product/customer group within the date range
     */
    private function fetchGroupWeights($group, $fromDate, $toDate) {
        $sql    = "SELECT * FROM Weight WHERE transaction_status = 'Sales' AND is_complete = 'Y' AND is_cancel <> 'Y' AND status = '0' AND company_id = ? AND plant_code = ? AND product_code = ? AND customer_code = ?";
        $types  = 'ssss';
        $values = [$group['company_id'], $group['plant_code'], $group['product_code'], $group['customer_code']];

        $from = $this->toDbDateTime($fromDate);
        if ($from !== null) {
            $sql .= " AND transaction_date >= ?"; $types .= 's'; $values[] = $from;
        }
        $to = $this->toDbDateTime($toDate);
        if ($to !== null) {
            $sql .= " AND transaction_date <= ?"; $types .= 's'; $values[] = $to;
        }

        return $this->fetchAll($sql, $types, $values);
    }

    private function countGroups($where, $types, $values) {
        $rows = $this->fetchAll("SELECT COUNT(*) AS c FROM (SELECT 1 FROM Weight WHERE 1=1" . $where . self::GROUP_BY . ") g", $types, $values);
        return intval($rows[0]['c'] ?? 0);
    }

    private function fetchAll($sql, $types = '', $values = []) {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new mysqli_sql_exception('Prepare failed');
        }
        if ($types !== '') {
            $stmt->bind_param($types, ...$values);
        }
        if (!$stmt->execute()) {
            $stmt->close();
            throw new mysqli_sql_exception('Execute failed');
        }
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Log the request to Api_Log, post it to the SQL API and log the response
     */
    private function sendToApi($url, $records) {
        $services = 'PostDeliveryOrder';
        $requests = json_encode($records);

        // Insert request into Api_Log
        $stmtL = $this->db->prepare("INSERT INTO Api_Log (services, request) VALUES (?, ?)");
        $stmtL->bind_param('ss', $services, $requests);
        $stmtL->execute();
        $logId = $stmtL->insert_id;
        $stmtL->close();

        // Send to API
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requests);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Decode API response (JSON string to array)
        $responseData = json_decode($response, true);

        if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
            // Update weight table
            /*$stmtUpdateWeight = $db->prepare("UPDATE Weight SET synced = 'Y' WHERE transaction_id = ?");
            $stmtUpdateWeight->bind_param('s', $docref2);
            $stmtUpdateWeight->execute();
            $stmtUpdateWeight->close();*/

            $result = [
                'status'  => 'success',
                'message' => 'Post Successfully',
                'posted'  => $responseData['results'],
            ];
        } else {
            $result = [
                'status'  => 'failed',
                'message' => $responseData['message'] ?? 'Failed to insert',
            ];
        }

        // The API call can outlive the DB connection - reconnect before logging the response
        $this->ensureConnection();

        // Update the same Api_Log record with the response
        $responseToLog = json_encode($result);
        $stmtU = $this->db->prepare("UPDATE Api_Log SET response = ? WHERE id = ?");
        $stmtU->bind_param('si', $responseToLog, $logId);
        $stmtU->execute();
        $stmtU->close();

        return $result;
    }

    private function ensureConnection() {
        try {
            $alive = $this->db && $this->db->ping();
        } catch (Throwable $e) {
            $alive = false;
        }

        if (!$alive) {
            try { $this->db->close(); } catch (Throwable $e) {}
            require __DIR__ . '/../db_connect.php';
            $this->db = $db;
        }
    }

    private function getSqlConfig() {
        $path = dirname(__DIR__) . '/sql_config.php';
        return file_exists($path) ? include($path) : [];
    }

    private function toDbDateTime($value) {
        if ($value === null || $value === '') {
            return null;
        }
        $dateTime = DateTime::createFromFormat('d-m-Y H:i:s', $value);
        return $dateTime ? $dateTime->format('Y-m-d H:i:s') : null;
    }

    private function formatDate($value) {
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $value);
        return $dateTime ? $dateTime->format('Y-m-d') : '';
    }

    // Escape a value for the tab-separated Excel export
    private function filterExcelValue($str) {
        $str = preg_replace("/\t/", "\\t", (string) $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }
}
?>
