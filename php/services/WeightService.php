<?php
require_once __DIR__ . '/../services/BaseService.php';
require_once __DIR__ . '/../services/VehicleService.php';
require_once __DIR__ . '/../services/ItemService.php';
require_once __DIR__ . '/../services/CustomerService.php';
require_once __DIR__ . '/../services/SupplierService.php';
require_once __DIR__ . '/../services/DestinationService.php';
require_once __DIR__ . '/../services/ProjectService.php';
require_once __DIR__ . '/../requires/lookup.php';

class WeightService extends BaseService {

    private $vehicleService;
    private $itemService;
    private $customerService;
    private $supplierService;
    private $destinationService;
    private $projectService;

    public function __construct($db, $username) {
        parent::__construct($db, $username);
        $this->vehicleService = new VehicleService($db, $username);
        $this->itemService = new ItemService($db, $username);
        $this->customerService = new CustomerService($db, $username);
        $this->supplierService = new SupplierService($db, $username);
        $this->destinationService = new DestinationService($db, $username);
        $this->projectService = new ProjectService($db, $username);
    }

    /**
     * All company-scoped dropdown lists for the weighing page (modal + search bar), in a single call.
     * Products are used for both the product and raw material dropdowns; vehicles for vehicle 1 and 2.
     */
    public function getCompanyLists($companyId) {
        return [
            'customers'    => $this->customerService->getListByCompany($companyId),
            'suppliers'    => $this->supplierService->getListByCompany($companyId),
            'products'     => $this->itemService->getListByCompany($companyId),
            'destinations' => $this->destinationService->getListByCompany($companyId),
            'projects'     => $this->projectService->getListByCompany($companyId),
            'vehicles'     => $this->vehicleService->getListByCompany($companyId),
        ];
    }
    
    public function saveNormal($f) {
        $f = $this->autoRegisterProduct($f);
        $misValue = $this->getPlantCount($f['plantCode'], $f['transactionStatus']);
        $f['transactionId'] = $this->buildTransactionId($f['plantCode'], $f['transactionStatus'], $f['weightType'], $misValue);
        $params = $this->normalParams($f);
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $stmt = $this->db->prepare("INSERT INTO Weight ({$this->normalCols()}) VALUES ({$placeholders})");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        $this->persistCustomerSideFields($id, $f['customerSide']);
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo1'], 0, $f['companyId']);
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo2'], 0, $f['companyId']);
        $misValue++;
        $this->incrementPlantCount($f['plantCode'], $f['transactionStatus'], $misValue);
        if ($f['weightType'] === 'Container') {
            $this->flagContainerStatus($f['containerNo'], $f['isComplete']);
        }
        return ['id' => $id];
    }

    public function updateNormal($f) {
        $f = $this->autoRegisterProduct($f);
        $params = $this->normalParams($f);
        $params[] = $f['weightId'];
        $stmt = $this->db->prepare("UPDATE Weight SET {$this->normalSetCols()} WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
        $this->persistCustomerSideFields($f['weightId'], $f['customerSide']);
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo1'], 0, $f['companyId']);
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo2'], 0, $f['companyId']);
        if ($f['weightType'] === 'Container') {
            $this->flagContainerStatus($f['containerNo'], $f['isComplete']);
        }
        return ['id' => $f['weightId']];
    }

    // ─── Empty Container ─────────────────────────────────────────────────────────
    public function saveEmptyContainer($f) {
        $f = $this->autoRegisterProduct($f);
        if ((int)$f['grossIncoming'] < (int)$f['tareOutgoing']) {
            throw new Exception('Incoming Weight cannot be greater than outgoing weight');
        }
        $misValue = $this->getPlantCount($f['plantCode'], $f['transactionStatus']);
        $f['transactionId'] = $this->buildTransactionId($f['plantCode'], $f['transactionStatus'], $f['weightType'], $misValue);
        $params = $this->normalParams($f);
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $stmt = $this->db->prepare("INSERT INTO Weight_Container ({$this->normalCols()}) VALUES ({$placeholders})");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo1'], 0, $f['companyId']);
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo2'], 0, $f['companyId']);
        $misValue++;
        $this->incrementPlantCount($f['plantCode'], $f['transactionStatus'], $misValue);
        return ['id' => $id];
    }

    public function updateEmptyContainer($f) {
        $f = $this->autoRegisterProduct($f);
        if ((int)$f['grossIncoming'] < (int)$f['tareOutgoing']) {
            throw new Exception('Incoming Weight cannot be greater than outgoing weight');
        }
        $params = $this->normalParams($f);
        $check = $this->db->prepare("SELECT id FROM Weight_Container WHERE id=?");
        if (!$check) {
            throw new Exception($this->db->error);
        }
        $check->bind_param('s', $f['weightId']);
        $check->execute();
        $check->store_result();
        $exists = $check->num_rows > 0;
        $check->close();
        if ($exists) {
            $p = array_merge($params, [$f['weightId']]);
            $stmt = $this->db->prepare("UPDATE Weight_Container SET {$this->normalSetCols()} WHERE id=?");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            $stmt->bind_param(str_repeat('s', count($p)), ...$p);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();
        } else {
            $cols = "id, {$this->normalCols()}";
            $placeholders = implode(',', array_fill(0, count($params) + 1, '?'));
            $stmt = $this->db->prepare("INSERT INTO Weight_Container ({$cols}) VALUES ({$placeholders})");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            $p = array_merge([$f['weightId']], $params);
            $stmt->bind_param(str_repeat('s', count($p)), ...$p);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();
        }
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo1'], 0, $f['companyId']);
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo2'], 0, $f['companyId']);
        return ['id' => $f['weightId']];
    }

    // ─── Different Container ─────────────────────────────────────────────────────
    public function saveDifferentContainer($f) {
        $f = $this->autoRegisterProduct($f);
        if (empty($f['grossIncomingDate'])) {
            $f['grossIncomingDate'] = $f['grossIncomingDate2'];
        }
        $misValue = $this->getPlantCount($f['plantCode'], $f['transactionStatus']);
        $f['transactionId'] = $this->buildTransactionId($f['plantCode'], $f['transactionStatus'], $f['weightType'], $misValue);
        $params = $this->diffContainerParams($f);
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $stmt = $this->db->prepare("INSERT INTO Weight ({$this->diffCols()}) VALUES ({$placeholders})");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        $this->persistCustomerSideFields($id, $f['customerSide']);
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo1'], 0, $f['companyId']);
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo2'], 0, $f['companyId']);
        $misValue++;
        $this->incrementPlantCount($f['plantCode'], $f['transactionStatus'], $misValue);
        if ($f['isComplete'] === 'Y') {
            $diffContainerTransId = $this->buildTransactionId($f['plantCode'], $f['transactionStatus'], 'Normal', $misValue);
            $this->insertDiffContainerRecord($f, $diffContainerTransId, $f['replacementContainer']);
            $misValue++;
            $this->incrementPlantCount($f['plantCode'], $f['transactionStatus'], $misValue);
        }
        if (!empty($f['containerNo'])) {
            $this->flagContainerStatus($f['containerNo'], $f['isComplete']);
        }
        return ['id' => $id];
    }

    public function updateDifferentContainer($f) {
        $f = $this->autoRegisterProduct($f);
        $params = $this->diffContainerParams($f);
        $params[] = $f['weightId'];
        $stmt = $this->db->prepare("UPDATE Weight SET {$this->diffSetCols()} WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
        $this->persistCustomerSideFields($f['weightId'], $f['customerSide']);
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo1'], 0, $f['companyId']);
        $this->vehicleService->autoRegisterVehicle($f['vehiclePlateNo2'], 0, $f['companyId']);
        if (!empty($f['containerNo'])) {
            $this->flagContainerStatus($f['containerNo'], $f['isComplete']);
            if ($f['isComplete'] === 'Y') {
                $this->syncReplacementContainer($f);
            }
        }
        return ['id' => $f['weightId']];
    }

    // ─── Filter / Get / Delete (moved to top section) ──────────────────────────
    public function filterWeight($post, $language, $languageArray) {
        $draw        = $post['draw'];
        $start       = $post['start'];
        $length      = $post['length'];
        $columnName  = $post['columns'][$post['order'][0]['column']]['data'] ?? 'transaction_date';
        $sortOrder   = $post['order'][0]['dir'] ?? 'asc';
        $search      = mysqli_real_escape_string($this->db, $post['search']['value']);

        $q = '';
        if (!empty($post['fromDate'])) {
            $q .= " AND transaction_date >= '" . DateTime::createFromFormat('d-m-Y', $post['fromDate'])->format('Y-m-d 00:00:00') . "'";
        }
        if (!empty($post['toDate'])) {
            $q .= " AND transaction_date <= '" . DateTime::createFromFormat('d-m-Y', $post['toDate'])->format('Y-m-d 23:59:59') . "'";
        }
        // Determine company on the backend - never trust frontend value for restricted users
        if (hasPermission('Weighing', ['view_all_companies'])) {
            $companyFilter = (!empty($post['company']) && $post['company'] !== '-') ? intval($post['company']) : 0;
        } else {
            $companyFilter = intval($_SESSION['company_id'] ?? 0);
        }
        if ($companyFilter > 0) {
            $q .= " AND company_id = {$companyFilter}";
        }
        foreach (['status' => 'transaction_status', 'customer' => 'customer_code', 'supplier' => 'supplier_code', 'invoice' => 'weight_type', 'batch' => 'is_complete', 'product' => 'product_code', 'rawMaterial' => 'raw_mat_code', 'plant' => 'plant_code'] as $param => $col) {
            if (!empty($post[$param]) && $post[$param] !== '-') {
                $q .= " AND {$col} = '" . mysqli_real_escape_string($this->db, $post[$param]) . "'";
            }
        }
        if (!empty($post['vehicle']) && $post['vehicle'] !== '-') {
            $q .= " AND lorry_plate_no1 LIKE '%" . mysqli_real_escape_string($this->db, $post['vehicle']) . "%'";
        }
        if (!empty($post['transactionId']) && $post['transactionId'] !== '-') {
            $q .= " AND transaction_id LIKE '%" . mysqli_real_escape_string($this->db, $post['transactionId']) . "%'";
        }
        if (!empty($post['containerNo']) && $post['containerNo'] !== '-') {
            $v = mysqli_real_escape_string($this->db, $post['containerNo']);
            $q .= " AND (container_no LIKE '%{$v}%' OR container_no2 LIKE '%{$v}%')";
        }
        if (!empty($post['sealNo']) && $post['sealNo'] !== '-') {
            $v = mysqli_real_escape_string($this->db, $post['sealNo']);
            $q .= " AND (seal_no LIKE '%{$v}%' OR seal_no2 LIKE '%{$v}%')";
        }
        if (!empty($post['invDelPo']) && $post['invDelPo'] !== '-') {
            $v = mysqli_real_escape_string($this->db, $post['invDelPo']);
            $q .= " AND (purchase_order LIKE '%{$v}%' OR invoice_no LIKE '%{$v}%' OR delivery_no LIKE '%{$v}%')";
        }
        if ($search !== '') {
            $q .= " AND (transaction_id LIKE '%{$search}%' OR lorry_plate_no1 LIKE '%{$search}%')";
        }

        $cols = "id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, customer_code, customer_name, plant_code, plant_name, supplier_code, supplier_name, raw_mat_code, raw_mat_name, product_code, product_name, container_no, container_no2, seal_no, seal_no2, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, created_date, created_by, modified_date, modified_by, indicator_id_2, product_description";
        $isPending = ($post['batch'] ?? '') === 'N';
        $plantFilter = '';
        if (!hasPermission('Weighing', ['view_all_plants'])) {
            $plants = implode("', '", $_SESSION['plant']);
            $plantFilter = " AND plant_code IN ('{$plants}')";
        }

        if ($isPending) {
            $totalRes = $this->db->query("SELECT COUNT(*) as c FROM (SELECT id FROM Weight WHERE status='0'{$plantFilter} UNION ALL SELECT id FROM Weight_Container WHERE status='0'{$plantFilter}) x");
            $totalRecords = $totalRes->fetch_assoc()['c'];
            $filteredRes = $this->db->query("SELECT COUNT(*) as c FROM (SELECT id FROM Weight WHERE status='0'{$plantFilter}{$q} UNION ALL SELECT id FROM Weight_Container WHERE status='0'{$plantFilter}{$q}) x");
            $totalFiltered = $filteredRes->fetch_assoc()['c'];
            $dataQuery = "(SELECT {$cols} FROM Weight WHERE status='0'{$plantFilter}{$q}) UNION ALL (SELECT {$cols} FROM Weight_Container WHERE status='0'{$plantFilter}{$q}) ORDER BY {$columnName} {$sortOrder} LIMIT {$start},{$length}";
        } else {
            $totalRes = $this->db->query("SELECT COUNT(*) as c FROM Weight WHERE status='0'{$plantFilter}");
            $totalRecords = $totalRes->fetch_assoc()['c'];
            $filteredRes = $this->db->query("SELECT COUNT(*) as c FROM Weight WHERE status='0'{$plantFilter}{$q}");
            $totalFiltered = $filteredRes->fetch_assoc()['c'];
            $dataQuery = "SELECT * FROM Weight WHERE status='0'{$plantFilter}{$q} ORDER BY {$columnName} {$sortOrder} LIMIT {$start},{$length}";
        }

        $result = $this->db->query($dataQuery);
        $data = [];
        $salesCount = $purchaseCount = $localCount = $portCount = $miscCount = 0;
        $statusLabels = [
            'Sales'    => [$languageArray['dispatch_code'][$language],    &$salesCount],
            'Purchase' => [$languageArray['receiving_code'][$language],   &$purchaseCount],
            'Misc'     => [$languageArray['miscellaneous_code'][$language],&$miscCount],
            'Port'     => [$languageArray['trx_to_port_code'][$language],  &$portCount],
        ];
        $weightTypeLabels = [
            'Container'          => $languageArray['primer_mover_code'][$language],
            'Empty Container'    => $languageArray['primer_mover_container_code'][$language],
            'Different Container'=> $languageArray['primer_mover_different_bins_code'][$language],
            'Normal'             => $languageArray['normal_weighing_code'][$language],
        ];

        while ($row = $result->fetch_assoc()) {
            $ts = $row['transaction_status'];
            if (isset($statusLabels[$ts])) {
                $row['transaction_status'] = $statusLabels[$ts][0];
                $statusLabels[$ts][1]++;
            } else {
                $localCount++;
                $row['transaction_status'] = $languageArray['internal_transfer_code'][$language];
            }
            $row['weight_type'] = $weightTypeLabels[$row['weight_type']] ?? $row['weight_type'];
            $row['customer'] = ($ts === 'Purchase' || $ts === 'Local') ? $row['supplier_name'] : $row['customer_name'];
            $row['product_code'] = ($ts === 'Purchase' || $ts === 'Local') ? $row['raw_mat_code'] : $row['product_code'];
            $row['product_name'] = ($ts === 'Purchase' || $ts === 'Local') ? $row['raw_mat_name'] : $row['product_name'];
            $company = searchCompanyById($row['company_id'], $this->db);
            $row['company_name'] = $company ? $company['name'] : '';
            $data[] = $row;
        }

        return [
            'draw'          => intval($draw),
            'recordsTotal'  => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data'          => $data,
            'salesTotal'    => $salesCount,
            'purchaseTotal' => $purchaseCount,
            'localTotal'    => $localCount,
            'miscTotal'     => $miscCount,
        ];
    }

    public function filterEmptyContainer($post, $language, $languageArray) {
        $draw       = $post['draw'];
        $start      = $post['start'];
        $length     = $post['length'];
        $columnName = $post['columns'][$post['order'][0]['column']]['data'] ?? 'transaction_date';
        $sortOrder  = $post['order'][0]['dir'] ?? 'asc';
        $search     = mysqli_real_escape_string($this->db, $post['search']['value']);

        $q = " AND is_complete='Y' AND is_cancel='N'";
        if (!empty($post['fromDate'])) {
            $q .= " AND transaction_date >= '" . DateTime::createFromFormat('d-m-Y', $post['fromDate'])->format('Y-m-d 00:00:00') . "'";
        }
        if (!empty($post['toDate'])) {
            $q .= " AND transaction_date <= '" . DateTime::createFromFormat('d-m-Y', $post['toDate'])->format('Y-m-d 23:59:59') . "'";
        }
        // Company filter — never trust frontend value for restricted users
        if (hasPermission('Weighing', ['view_all_companies'])) {
            $companyFilter = (!empty($post['company']) && $post['company'] !== '-') ? intval($post['company']) : 0;
        } else {
            $companyFilter = intval($_SESSION['company_id'] ?? 0);
        }
        if ($companyFilter > 0) {
            $q .= " AND company_id = {$companyFilter}";
        }
        // Plant filter — restrict to user's tied plants unless they have view_all_plants
        if (!empty($post['plant']) && $post['plant'] !== '-') {
            $q .= " AND plant_code='" . mysqli_real_escape_string($this->db, $post['plant']) . "'";
        } elseif (!hasPermission('Weighing', ['view_all_plants'])) {
            $plants = implode("', '", $_SESSION['plant']);
            $q .= " AND plant_code IN ('{$plants}')";
        }
        if ($search !== '') {
            $q .= " AND (transaction_id LIKE '%{$search}%' OR lorry_plate_no1 LIKE '%{$search}%' OR container_no LIKE '%{$search}%')";
        }

        $totalRes = $this->db->query("SELECT COUNT(*) as c FROM Weight_Container WHERE status='0'");
        $totalRecords = $totalRes->fetch_assoc()['c'];
        $filteredRes = $this->db->query("SELECT COUNT(*) as c FROM Weight_Container WHERE status='0'{$q}");
        $totalFiltered = $filteredRes->fetch_assoc()['c'];
        $result = $this->db->query("SELECT * FROM Weight_Container WHERE status='0'{$q} ORDER BY {$columnName} {$sortOrder} LIMIT {$start},{$length}");

        $data = [];
        $salesCount = $purchaseCount = $localCount = $portCount = $miscCount = 0;
        $statusLabels = [
            'Sales'    => [$languageArray['dispatch_code'][$language],    &$salesCount],
            'Purchase' => [$languageArray['receiving_code'][$language],   &$purchaseCount],
            'Misc'     => [$languageArray['miscellaneous_code'][$language],&$miscCount],
            'Port'     => [$languageArray['trx_to_port_code'][$language],  &$portCount],
        ];

        while ($row = $result->fetch_assoc()) {
            $ts = $row['transaction_status'];
            if (isset($statusLabels[$ts])) {
                $row['transaction_status'] = $statusLabels[$ts][0];
                $statusLabels[$ts][1]++;
            } else {
                $localCount++;
                $row['transaction_status'] = $languageArray['internal_transfer_code'][$language];
            }
            $row['customer'] = ($ts === 'Purchase' || $ts === 'Local') ? $row['supplier_name'] : $row['customer_name'];
            $row['product_code'] = ($ts === 'Purchase' || $ts === 'Local') ? $row['raw_mat_code'] : $row['product_code'];
            $row['product_name'] = ($ts === 'Purchase' || $ts === 'Local') ? $row['raw_mat_name'] : $row['product_name'];
            $company = searchCompanyById($row['company_id'], $this->db);
            $row['company_name'] = $company ? $company['name'] : '';
            $data[] = $row;
        }

        return [
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
            'salesTotal'      => $salesCount,
            'purchaseTotal'   => $purchaseCount,
            'localTotal'      => $localCount,
            'miscTotal'       => $miscCount,
        ];
    }

    public function getWeight($id, $format, $type, $acctType, $fromDate, $toDate) {
        if ($format === 'EXPANDABLE' && $type === 'Log') {
            $stmt = $this->db->prepare("SELECT * FROM Weight_Log WHERE id=?");
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $row ? array_map(fn($v) => $v ?? '', $row) : null;
        }

        $table = ($type === 'Container') ? 'Weight_Container' : 'Weight';
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE id=?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) return null;

        if ($format === 'EXPANDABLE') {
            return $this->buildExpandableMessage($row, $acctType, $fromDate, $toDate);
        }

        $msg = $row;
        $msg['vehicleNoTxt']  = $this->resolveVehicleText($row['lorry_plate_no1']);
        $msg['vehicleNoTxt2'] = $this->resolveVehicleText($row['lorry_plate_no2']);
        return $msg;
    }

    public function getEmptyContainer($containerNo) {
        $stmt = $this->db->prepare("SELECT * FROM Weight_Container WHERE container_no=? AND status='0' AND is_complete='Y' AND is_cancel='N'");
        $stmt->bind_param('s', $containerNo);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) return null;
        $msg = $row;
        $msg['vehicleNoTxt'] = $this->resolveVehicleText($row['lorry_plate_no1']);
        return $msg;
    }

    public function getContainers($transactionStatus) {
        $stmt = $this->db->prepare("SELECT id, container_no FROM Weight_Container WHERE transaction_status=? AND status='0' AND is_complete='Y' AND is_cancel='N'");
        $stmt->bind_param('s', $transactionStatus);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    public function deleteWeight($id, $cancelReason, $isEmptyContainer, $isMulti, $containerId) {
        if ($isMulti === 'Y') {
            if ($isEmptyContainer === 'Y') {
                $stmt = $this->db->prepare("UPDATE Weight_Container SET status=1, cancelled_reason=? WHERE id IN ({$id})");
                $stmt->bind_param('s', $cancelReason);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                $stmt->close();
            } else {
                if (!empty($id)) {
                    $cancel = 'Y';
                    $stmt = $this->db->prepare("UPDATE Weight SET is_complete=?, is_cancel=?, cancelled_reason=? WHERE id IN ({$id})");
                    $stmt->bind_param('sss', $cancel, $cancel, $cancelReason);
                    if (!$stmt->execute()) throw new Exception($stmt->error);
                    $stmt->close();
                }
                if (!empty($containerId)) {
                    $deleteStatus = '1';
                    $stmt = $this->db->prepare("UPDATE Weight_Container SET status=?, cancelled_reason=? WHERE id IN ({$containerId})");
                    $stmt->bind_param('ss', $deleteStatus, $cancelReason);
                    if (!$stmt->execute()) throw new Exception($stmt->error);
                    $stmt->close();
                }
            }
        } else {
            if ($isEmptyContainer === 'Y') {
                $deleteStatus = '1';
                $stmt = $this->db->prepare("UPDATE Weight_Container SET status=?, cancelled_reason=? WHERE id=?");
                $stmt->bind_param('sss', $deleteStatus, $cancelReason, $id);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                $stmt->close();
            } else {
                $cancel = 'Y';
                $stmt = $this->db->prepare("UPDATE Weight SET is_complete=?, is_cancel=?, cancelled_reason=? WHERE id=?");
                $stmt->bind_param('ssss', $cancel, $cancel, $cancelReason, $id);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                $stmt->close();
            }
        }
    }

    // ─── Customer Side Info Processing ─────────────────────────────────────────────
    public function saveCustomerSideInfo($id, $doNo, $mc, $firstWeight, $secondWeight) {
        $nettWeight = null;
        $weightDifference = null;
        if ($firstWeight !== null && $secondWeight !== null) {
            $nettWeight = abs((float)$firstWeight - (float)$secondWeight);
        }
        if ($nettWeight !== null) {
            $stmt = $this->db->prepare("SELECT nett_weight1 FROM Weight WHERE id=?");
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row && $row['nett_weight1'] !== null && $row['nett_weight1'] !== '') {
                $weightDifference = (float)$row['nett_weight1'] - $nettWeight;
            }
        }
        $stmt = $this->db->prepare("UPDATE Weight SET cust_side_do_no=?, cust_side_mc=?, cust_side_first_weight=?, cust_side_second_weight=?, cust_side_nett_weight=?, weight_difference=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sssssss', $doNo, $mc, $firstWeight, $secondWeight, $nettWeight, $weightDifference, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
        return ['nett_weight' => $nettWeight, 'weight_difference' => $weightDifference];
    }

    public function getCustomerSideInfo($id) {
        $stmt = $this->db->prepare("SELECT cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference FROM Weight WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $defaults = ['cust_side_do_no'=>'','cust_side_mc'=>'','cust_side_first_weight'=>'','cust_side_second_weight'=>'','cust_side_nett_weight'=>'','weight_difference'=>''];
        return $row ? array_merge($defaults, array_map(fn($v) => $v ?? '', $row)) : $defaults;
    }

    // ─── Plant / Transaction Helpers ─────────────────────────────────────────────
    private function getPlantCountColumn($status) {
        $map = ['Purchase' => 'purchase', 'Local' => 'locals', 'Port' => 'port', 'Misc' => 'misc'];
        return $map[$status] ?? 'sales';
    }

    private function getPlantCount($plantCode, $status) {
        $col = $this->getPlantCountColumn($status);
        $stmt = $this->db->prepare("SELECT {$col} as curcount FROM Plant WHERE plant_code=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('s', $plantCode);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? $row['curcount'] : 0;
    }

    private function incrementPlantCount($plantCode, $status, $value) {
        $col = $this->getPlantCountColumn($status);
        $stmt = $this->db->prepare("UPDATE Plant SET {$col}=? WHERE plant_code=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('ss', $value, $plantCode);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
    }

    private function buildTransactionId($plantCode, $status, $weightType, $misValue) {
        $stmt = $this->db->prepare("SELECT * FROM status WHERE status=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('s', $status);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) {
            throw new Exception("Status not found: {$status}");
        }
        $transId = $plantCode . '/';
        if ($weightType === 'Container') {
            $transId .= 'C/' . $row['prefix'] . '/' . date('ym') . '-';
        } else {
            $transId .= $row['prefix'] . '/' . date('ym') . '-';
        }
        $charSize = strlen($misValue);
        for ($i = 0; $i < (4 - (int)$charSize); $i++) {
            $transId .= '0';
        }
        $transId .= $misValue;
        return $transId;
    }

    // ─── SQL Fragments ───────────────────────────────────────────────────────────
    private function normalCols() {
        return "company_id, transaction_id, transaction_status, weight_type, customer_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, raw_mat_code, raw_mat_name, container_no, seal_no, container_no2, seal_no2, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, gross_weight_by1, tare_weight1, tare_weight1_date, tare_weight_by1, nett_weight1, gross_weight2, gross_weight2_date, gross_weight_by2, tare_weight2, tare_weight2_date, tare_weight_by2, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, manual_weight, indicator_id, weighbridge_id, created_by, modified_by, indicator_id_2, product_description, unit_price, sub_total, sst, total_price, is_approved, plant_code, plant_name, is_manual_product, is_manual_raw_material, is_manual_customer, is_manual_supplier, project_id";
    }

    private function normalSetCols() {
        return "company_id=?, transaction_id=?, transaction_status=?, weight_type=?, customer_type=?, transaction_date=?, lorry_plate_no1=?, lorry_plate_no2=?, supplier_weight=?, order_weight=?, customer_code=?, customer_name=?, supplier_code=?, supplier_name=?, product_code=?, product_name=?, raw_mat_code=?, raw_mat_name=?, container_no=?, seal_no=?, container_no2=?, seal_no2=?, invoice_no=?, purchase_order=?, delivery_no=?, transporter_code=?, transporter=?, destination_code=?, destination=?, remarks=?, gross_weight1=?, gross_weight1_date=?, gross_weight_by1=?, tare_weight1=?, tare_weight1_date=?, tare_weight_by1=?, nett_weight1=?, gross_weight2=?, gross_weight2_date=?, gross_weight_by2=?, tare_weight2=?, tare_weight2_date=?, tare_weight_by2=?, nett_weight2=?, reduce_weight=?, final_weight=?, weight_different=?, weight_different_perc=?, is_complete=?, is_cancel=?, manual_weight=?, indicator_id=?, weighbridge_id=?, created_by=?, modified_by=?, indicator_id_2=?, product_description=?, unit_price=?, sub_total=?, sst=?, total_price=?, is_approved=?, plant_code=?, plant_name=?, is_manual_product=?, is_manual_raw_material=?, is_manual_customer=?, is_manual_supplier=?, project_id=?";
    }

    private function diffCols() {
        return "company_id, transaction_id, transaction_status, weight_type, customer_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, raw_mat_code, raw_mat_name, container_no, seal_no, container_no2, seal_no2, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, gross_weight_by1, tare_weight1, tare_weight1_date, tare_weight_by1, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, gross_weight_by2, tare_weight2, tare_weight2_date, tare_weight_by2, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, manual_weight, indicator_id, weighbridge_id, created_by, modified_by, indicator_id_2, product_description, unit_price, sub_total, sst, total_price, is_approved, plant_code, plant_name, is_manual_product, is_manual_raw_material, is_manual_customer, is_manual_supplier, project_id";
    }

    private function diffSetCols() {
        return "company_id=?, transaction_id=?, transaction_status=?, weight_type=?, customer_type=?, transaction_date=?, lorry_plate_no1=?, lorry_plate_no2=?, supplier_weight=?, order_weight=?, customer_code=?, customer_name=?, supplier_code=?, supplier_name=?, product_code=?, product_name=?, raw_mat_code=?, raw_mat_name=?, container_no=?, seal_no=?, container_no2=?, seal_no2=?, invoice_no=?, purchase_order=?, delivery_no=?, transporter_code=?, transporter=?, destination_code=?, destination=?, remarks=?, gross_weight1=?, gross_weight1_date=?, gross_weight_by1=?, tare_weight1=?, tare_weight1_date=?, tare_weight_by1=?, nett_weight1=?, lorry_no2_weight=?, empty_container2_weight=?, replacement_container=?, gross_weight2=?, gross_weight2_date=?, gross_weight_by2=?, tare_weight2=?, tare_weight2_date=?, tare_weight_by2=?, nett_weight2=?, reduce_weight=?, final_weight=?, weight_different=?, weight_different_perc=?, is_complete=?, is_cancel=?, manual_weight=?, indicator_id=?, weighbridge_id=?, created_by=?, modified_by=?, indicator_id_2=?, product_description=?, unit_price=?, sub_total=?, sst=?, total_price=?, is_approved=?, plant_code=?, plant_name=?, is_manual_product=?, is_manual_raw_material=?, is_manual_customer=?, is_manual_supplier=?, project_id=?";
    }

    // ─── Param Builders ──────────────────────────────────────────────────────────

    private function normalParams($f) {
        $isManualProduct = (!empty($f['manualProduct']) && $f['manualProduct'] == '1') ? 'Y' : 'N';
        $isManualRawMaterial = (!empty($f['manualRawMaterial']) && $f['manualRawMaterial'] == '1') ? 'Y' : 'N';
        $isManualCustomer = (!empty($f['manualCustomer']) && $f['manualCustomer'] == '1') ? 'Y' : 'N';
        $isManualSupplier = (!empty($f['manualSupplier']) && $f['manualSupplier'] == '1') ? 'Y' : 'N';
        return [
            $f['companyId'], $f['transactionId'], $f['transactionStatus'], $f['weightType'], $f['customerType'], $f['transactionDate'],
            $f['vehiclePlateNo1'], $f['vehiclePlateNo2'], $f['supplierWeight'], $f['orderWeight'],
            $f['customerCode'], $f['customerName'], $f['supplierCode'], $f['supplierName'],
            $f['productCode'], $f['productName'], $f['rawMaterialCode'], $f['rawMaterialName'],
            $f['containerNo'], $f['sealNo'], $f['containerNo2'], $f['sealNo2'],
            $f['invoiceNo'], $f['purchaseOrder'], $f['deliveryNo'],
            $f['transporterCode'], $f['transporter'], $f['destinationCode'], $f['destination'], $f['otherRemarks'],
            $f['grossIncoming'], $f['grossIncomingDate'], $f['grossWeightBy1'],
            $f['tareOutgoing'], $f['tareOutgoingDate'], $f['tareWeightBy1'], $f['nettWeight'],
            $f['grossIncoming2'], $f['grossIncomingDate2'], $f['grossWeightBy2'],
            $f['tareOutgoing2'], $f['tareOutgoingDate2'], $f['tareWeightBy2'], $f['nettWeight2'],
            $f['reduceWeight'], $f['finalWeight'], $f['weightDifference'], $f['weightDifferencePerc'],
            $f['isComplete'], $f['isCancel'], $f['manualWeight'], $f['indicatorId'], $f['weighbridge'],
            $this->username, $this->username, $f['indicatorId2'],
            $f['productDescription'], $f['unitPrice'], $f['subTotalPrice'], $f['sstPrice'], $f['totalPrice'],
            $f['isApproved'], $f['plantCode'], $f['plant'],
            $isManualProduct, $isManualRawMaterial, $isManualCustomer, $isManualSupplier,
            $f['project']
        ];
    }

    private function diffContainerParams($f, $useVehicle2ForPlate1 = false) {
        $plate1 = $useVehicle2ForPlate1 ? $f['vehiclePlateNo2'] : $f['vehiclePlateNo1'];
        $isManualProduct = (!empty($f['manualProduct']) && $f['manualProduct'] == '1') ? 'Y' : 'N';
        $isManualRawMaterial = (!empty($f['manualRawMaterial']) && $f['manualRawMaterial'] == '1') ? 'Y' : 'N';
        $isManualCustomer = (!empty($f['manualCustomer']) && $f['manualCustomer'] == '1') ? 'Y' : 'N';
        $isManualSupplier = (!empty($f['manualSupplier']) && $f['manualSupplier'] == '1') ? 'Y' : 'N';
        return [
            $f['companyId'], $f['transactionId'], $f['transactionStatus'], $f['weightType'], $f['customerType'], $f['transactionDate'],
            $plate1, $f['vehiclePlateNo2'], $f['supplierWeight'], $f['orderWeight'],
            $f['customerCode'], $f['customerName'], $f['supplierCode'], $f['supplierName'],
            $f['productCode'], $f['productName'], $f['rawMaterialCode'], $f['rawMaterialName'],
            $f['containerNo'], $f['sealNo'], $f['containerNo2'], $f['sealNo2'],
            $f['invoiceNo'], $f['purchaseOrder'], $f['deliveryNo'],
            $f['transporterCode'], $f['transporter'], $f['destinationCode'], $f['destination'], $f['otherRemarks'],
            $f['grossIncoming'], $f['grossIncomingDate'], $f['grossWeightBy1'],
            $f['tareOutgoing'], $f['tareOutgoingDate'], $f['tareWeightBy1'], $f['nettWeight'],
            $f['vehicleWeight2'], $f['emptyContainerWeight2'], $f['replacementContainer'],
            $f['grossIncoming2'], $f['grossIncomingDate2'], $f['grossWeightBy2'],
            $f['tareOutgoing2'], $f['tareOutgoingDate2'], $f['tareWeightBy2'], $f['nettWeight2'],
            $f['reduceWeight'], $f['finalWeight'], $f['weightDifference'], $f['weightDifferencePerc'],
            $f['isComplete'], $f['isCancel'], $f['manualWeight'], $f['indicatorId'], $f['weighbridge'],
            $this->username, $this->username, $f['indicatorId2'],
            $f['productDescription'], $f['unitPrice'], $f['subTotalPrice'], $f['sstPrice'], $f['totalPrice'],
            $f['isApproved'], $f['plantCode'], $f['plant'],
            $isManualProduct, $isManualRawMaterial, $isManualCustomer, $isManualSupplier,
            $f['project']
        ];
    }

    // ─── Shared DB Helpers ───────────────────────────────────────────────────────

    private function persistCustomerSideFields($weightId, $d) {
        if (empty($weightId)) return;
        $stmt = $this->db->prepare("UPDATE Weight SET customer_side_company=?, customer_side_removal_pass_no=?, customer_side_license_no=?, customer_side_moisture_content=?, customer_side_officer_name=?, customer_side_rainbow_driver=?, customer_side_time_in=?, customer_side_time_out=? WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('sssssssss',
            $d['company'], $d['removalPassNo'], $d['licenseNo'],
            $d['moistureContent'], $d['officerName'], $d['rainbowDriver'],
            $d['timeIn'], $d['timeOut'], $weightId
        );
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
    }

    private function flagContainerStatus($containerNo, $isComplete) {
        if (empty($containerNo)) {
            return;
        }
        $stmt = $this->db->prepare("UPDATE Weight_Container SET is_cancel=? WHERE container_no=? AND status='0'");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('ss', $isComplete, $containerNo);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
    }

    private function insertDiffContainerRecord($f, $transactionId, $containerNo) {
        $saved = $f['transactionId'];
        $savedContainer = $f['containerNo'];
        $f['transactionId'] = $transactionId;
        $f['containerNo'] = $containerNo;
        $params = $this->diffContainerParams($f, true);
        $f['transactionId'] = $saved;
        $f['containerNo'] = $savedContainer;
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $stmt = $this->db->prepare("INSERT INTO Weight_Container ({$this->diffCols()}) VALUES ({$placeholders})");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }


    // ─── Expandable / Vehicle Helpers ─────────────────────────────────────────────

    private function resolveVehicleText($plateNo) {
        $stmt = $this->db->prepare("SELECT id FROM Vehicle WHERE veh_number=?");
        $stmt->bind_param('s', $plateNo);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $exists ? null : $plateNo;
    }

    private function buildExpandableMessage($row, $acctType, $fromDate, $toDate) {
        $isPurchaseOrLocal = ($row['transaction_status'] === 'Purchase' || $row['transaction_status'] === 'Local');
        $msg = [];
        $msg['id'] = $row['id'];

        if ($isPurchaseOrLocal) {
            $party = $this->getParty('Supplier', $row['supplier_code']);
            $msg['product_rawmat_name'] = $row['raw_mat_name'] ?? '';
        } else {
            $party = $this->getParty('Customer', $row['customer_code']);
            $msg['product_rawmat_name'] = $row['product_name'] ?? '';
        }
        $msg = array_merge($msg, $party);

        $fields = ['transporter','destination','plant_name','lorry_plate_no1','transaction_id','transaction_status','weight_type','invoice_no','delivery_no','container_no','seal_no','container_no2','seal_no2','purchase_order','gross_weight1','tare_weight1','nett_weight1','lorry_plate_no2','gross_weight2','tare_weight2','nett_weight2','reduce_weight','final_weight','customer_side_company','customer_side_removal_pass_no','customer_side_license_no','customer_side_moisture_content','customer_side_officer_name','customer_side_rainbow_driver','cust_side_do_no','cust_side_mc','cust_side_first_weight','cust_side_second_weight','cust_side_nett_weight','weight_difference'];
        foreach ($fields as $f) {
            $msg[$f] = $row[$f] ?? '';
        }
        $company = searchCompanyById($row['company_id'], $this->db);
        $msg['company_name'] = $company ? $company['name'] : '';
        $project = searchProjectById($row['project_id'], $this->db);
        $msg['project_code'] = $project ? $project['project_code'] : '';

        $dateFields = ['gross_weight1_date','tare_weight1_date','gross_weight2_date','tare_weight2_date','created_date'];
        foreach ($dateFields as $f) {
            $msg[$f] = !empty($row[$f]) ? date('d/m/Y - h:i:sa', strtotime($row[$f])) : '';
        }
        $msg['gross_weight_by1'] = $row['gross_weight_by1'] ?? '';
        $msg['tare_weight_by1']  = $row['tare_weight_by1'] ?? '';
        $msg['gross_weight_by2'] = $row['gross_weight_by2'] ?? '';
        $msg['tare_weight_by2']  = $row['tare_weight_by2'] ?? '';
        $msg['customer_side_time_in']  = !empty($row['customer_side_time_in'])  ? date('d/m/Y - h:i:sa', strtotime($row['customer_side_time_in']))  : '';
        $msg['customer_side_time_out'] = !empty($row['customer_side_time_out']) ? date('d/m/Y - h:i:sa', strtotime($row['customer_side_time_out'])) : '';

        if ($acctType === 'DO') {
            $msg = array_merge($msg, $this->getDoWeighingData($row, $fromDate, $toDate));
        } elseif ($acctType === 'GR') {
            $msg = array_merge($msg, $this->getGrWeighingData($row, $fromDate, $toDate));
        }

        return $msg;
    }

    private function getParty($table, $code) {
        $stmt = $this->db->prepare("SELECT name, address_line_1, address_line_2, address_line_3, phone_no, fax_no FROM {$table} WHERE " . strtolower($table) . "_code=? AND status='0'");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? $row : ['name'=>'','address_line_1'=>'','address_line_2'=>'','address_line_3'=>'','phone_no'=>'','fax_no'=>''];
    }

    private function getDoWeighingData($row, $fromDate, $toDate) {
        $from = DateTime::createFromFormat('d-m-Y H:i:s', $fromDate)->format('Y-m-d H:i:s');
        $to   = DateTime::createFromFormat('d-m-Y H:i:s', $toDate)->format('Y-m-d H:i:s');
        $stmt = $this->db->prepare("SELECT id, transaction_id, transaction_status, customer_name, lorry_plate_no1, product_name, delivery_no, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, transporter_code, transporter, destination_code, destination, unit_price FROM Weight WHERE plant_code=? AND product_code=? AND customer_code=? AND company_id=? AND transaction_date>=? AND transaction_date<=? AND is_complete='Y' AND is_cancel<>'Y' AND status='0' AND transaction_status='Sales'");
        $stmt->bind_param('ssssss', $row['plant_code'], $row['product_code'], $row['customer_code'], $row['company_id'], $from, $to);
        $stmt->execute();
        $res = $stmt->get_result();
        $weights = [];
        $total = 0;
        while ($r = $res->fetch_assoc()) {
            $r['unit_price'] = $r['unit_price'] ?? '0.00';
            $weights[] = $r;
            $total += $r['nett_weight1'];
        }
        $stmt->close();
        return ['weights' => $weights, 'totalDeliverAmt' => $total];
    }

    private function getGrWeighingData($row, $fromDate, $toDate) {
        $from = DateTime::createFromFormat('d-m-Y H:i:s', $fromDate)->format('Y-m-d H:i:s');
        $to   = DateTime::createFromFormat('d-m-Y H:i:s', $toDate)->format('Y-m-d H:i:s');
        $stmt = $this->db->prepare("SELECT id, transaction_id, transaction_status, supplier_name, lorry_plate_no1, raw_mat_name, delivery_no, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, transporter_code, transporter, destination_code, destination, unit_price, final_weight FROM Weight WHERE plant_code=? AND raw_mat_code=? AND supplier_code=? AND company_id=? AND transaction_date>=? AND transaction_date<=? AND is_complete='Y' AND is_cancel<>'Y' AND status='0' AND transaction_status='Purchase'");
        $stmt->bind_param('ssssss', $row['plant_code'], $row['raw_mat_code'], $row['supplier_code'], $row['company_id'], $from, $to);
        $stmt->execute();
        $res = $stmt->get_result();
        $weights = [];
        $total = 0;
        while ($r = $res->fetch_assoc()) {
            $r['unit_price'] = $r['unit_price'] ?? '0.00';
            $weights[] = $r;
            $total += floatval($r['final_weight']);
        }
        $stmt->close();
        return ['weights' => $weights, 'total_final_weight' => $total];
    }

    private function syncReplacementContainer($f) {
        $chk = $this->db->prepare("SELECT id FROM Weight_Container WHERE container_no=? AND is_complete='Y' AND is_cancel='N' AND status='0'");
        if (!$chk) {
            throw new Exception($this->db->error);
        }
        $chk->bind_param('s', $f['replacementContainer']);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();
        if ($row) {
            $existingId = $row['id'];
            $updSetCols = "company_id=?, transaction_status=?, weight_type=?, customer_type=?, transaction_date=?, lorry_plate_no1=?, lorry_plate_no2=?, supplier_weight=?, order_weight=?, customer_code=?, customer_name=?, supplier_code=?, supplier_name=?, product_code=?, product_name=?, raw_mat_code=?, raw_mat_name=?, container_no=?, seal_no=?, container_no2=?, seal_no2=?, invoice_no=?, purchase_order=?, delivery_no=?, transporter_code=?, transporter=?, destination_code=?, destination=?, remarks=?, gross_weight1=?, gross_weight1_date=?, gross_weight_by1=?, tare_weight1=?, tare_weight1_date=?, tare_weight_by1=?, nett_weight1=?, lorry_no2_weight=?, empty_container2_weight=?, replacement_container=?, gross_weight2=?, gross_weight2_date=?, gross_weight_by2=?, tare_weight2=?, tare_weight2_date=?, tare_weight_by2=?, nett_weight2=?, reduce_weight=?, final_weight=?, weight_different=?, weight_different_perc=?, is_complete=?, is_cancel=?, manual_weight=?, indicator_id=?, weighbridge_id=?, created_by=?, modified_by=?, indicator_id_2=?, product_description=?, unit_price=?, sub_total=?, sst=?, total_price=?, is_approved=?, plant_code=?, plant_name=?";
            $updParams = [
                $f['companyId'], $f['transactionStatus'], $f['weightType'], $f['customerType'], $f['transactionDate'],
                $f['vehiclePlateNo2'], $f['vehiclePlateNo2'], $f['supplierWeight'], $f['orderWeight'],
                $f['customerCode'], $f['customerName'], $f['supplierCode'], $f['supplierName'],
                $f['productCode'], $f['productName'], $f['rawMaterialCode'], $f['rawMaterialName'],
                $f['replacementContainer'], $f['sealNo'], $f['containerNo2'], $f['sealNo2'],
                $f['invoiceNo'], $f['purchaseOrder'], $f['deliveryNo'],
                $f['transporterCode'], $f['transporter'], $f['destinationCode'], $f['destination'], $f['otherRemarks'],
                $f['grossIncoming2'], $f['grossIncomingDate2'], $f['grossWeightBy2'],
                $f['vehicleWeight2'], $f['grossIncomingDate2'], $f['grossWeightBy2'], $f['emptyContainerWeight2'],
                $f['replacementContainer'], $f['vehicleWeight2'], $f['emptyContainerWeight2'],
                $f['grossIncoming2'], $f['grossIncomingDate2'], $f['grossWeightBy2'],
                $f['tareOutgoing2'], $f['tareOutgoingDate2'], $f['tareWeightBy2'], $f['nettWeight2'],
                $f['reduceWeight'], $f['finalWeight'], $f['weightDifference'], $f['weightDifferencePerc'],
                $f['isComplete'], $f['isCancel'], $f['manualWeight'], $f['indicatorId'], $f['weighbridge'],
                $this->username, $this->username, $f['indicatorId2'],
                $f['productDescription'], $f['unitPrice'], $f['subTotalPrice'], $f['sstPrice'], $f['totalPrice'],
                $f['isApproved'], $f['plantCode'], $f['plant'], $existingId
            ];
            $updStmt = $this->db->prepare("UPDATE Weight_Container SET {$updSetCols} WHERE id=?");
            if (!$updStmt) {
                throw new Exception($this->db->error);
            }
            $updStmt->bind_param('sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', ...$updParams);
            if (!$updStmt->execute()) {
                throw new Exception($updStmt->error);
            }
            $updStmt->close();
        } else {
            $misValue = $this->getPlantCount($f['plantCode'], $f['transactionStatus']);
            $diffTransId = $this->buildTransactionId($f['plantCode'], $f['transactionStatus'], 'Normal', $misValue);
            $this->insertDiffContainerRecord($f, $diffTransId, $f['replacementContainer']);
            $misValue++;
            $this->incrementPlantCount($f['plantCode'], $f['transactionStatus'], $misValue);
        }
    }

    // ─── Auto Register Product ───────────────────────────────────────────────────
    // Manually typed product / raw material / customer / supplier are registered under the weighing's company.
    // The register functions return null for blank values ("", "-", null), in which case the form values are kept.
    private function autoRegisterProduct($f) {
        $companyId = $f['companyId'] ?? null;

        // Handle manual product (for Sales/Port/Misc)
        if (!empty($f['manualProduct']) && $f['manualProduct'] == '1' && !empty($f['productNameTxt'])) {
            $result = $this->itemService->autoRegisterProduct($f['productNameTxt'], 'Customer', $companyId);
            if ($result) {
                $f['productCode'] = $result['product_code'];
                $f['productName'] = $result['name'];
            }
        }

        // Handle manual raw material (for Purchase/Local)
        if (!empty($f['manualRawMaterial']) && $f['manualRawMaterial'] == '1' && !empty($f['rawMaterialNameTxt'])) {
            $result = $this->itemService->autoRegisterProduct($f['rawMaterialNameTxt'], 'Supplier', $companyId);
            if ($result) {
                $f['rawMaterialCode'] = $result['product_code'];
                $f['rawMaterialName'] = $result['name'];
            }
        }

        // Handle manual customer
        if (!empty($f['manualCustomer']) && $f['manualCustomer'] == '1' && !empty($f['customerNameTxt'])) {
            $result = $this->customerService->autoRegisterCustomer($f['customerNameTxt'], $companyId);
            if ($result) {
                $f['customerCode'] = $result['customer_code'];
                $f['customerName'] = $result['name'];
            }
        }

        // Handle manual supplier
        if (!empty($f['manualSupplier']) && $f['manualSupplier'] == '1' && !empty($f['supplierNameTxt'])) {
            $result = $this->supplierService->autoRegisterSupplier($f['supplierNameTxt'], $companyId);
            if ($result) {
                $f['supplierCode'] = $result['supplier_code'];
                $f['supplierName'] = $result['name'];
            }
        }

        return $f;
    }
}
?>
