<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

class VehicleService extends BaseService {

    public function filter($post) {
        $start       = $post['start'];
        $length      = $post['length'];
        $draw        = $post['draw'];
        $columnName  = $post['columns'][$post['order'][0]['column']]['data'] ?? 'veh_number';
        $sortOrder   = $post['order'][0]['dir'] ?? 'asc';
        $search      = mysqli_real_escape_string($this->db, $post['search']['value']);
        $companyId   = isset($post['companyId']) ? intval($post['companyId']) : 0;
        $vehicleNo   = isset($post['vehicleNo']) ? mysqli_real_escape_string($this->db, $post['vehicleNo']) : '';
        $customerName = isset($post['customerName']) ? mysqli_real_escape_string($this->db, $post['customerName']) : '';

        $q = '';
        if ($search !== '') {
            $q = " AND (veh_number LIKE '%{$search}%' OR vehicle_weight LIKE '%{$search}%' OR transporter_code LIKE '%{$search}%' OR customer_name LIKE '%{$search}%' OR supplier_name LIKE '%{$search}%')";
        }
        if ($companyId > 0) {
            $q .= " AND company={$companyId}";
        }
        if ($vehicleNo !== '') {
            $q .= " AND veh_number LIKE '%{$vehicleNo}%'";
        }
        if ($customerName !== '') {
            $q .= " AND customer_name LIKE '%{$customerName}%'";
        }

        $totalRes = $this->db->query("SELECT COUNT(*) as c FROM Vehicle");
        $totalRecords = $totalRes->fetch_assoc()['c'];

        $filteredRes = $this->db->query("SELECT COUNT(*) as c FROM Vehicle WHERE status='0'{$q}");
        $totalFiltered = $filteredRes->fetch_assoc()['c'];

        $result = $this->db->query("SELECT * FROM Vehicle WHERE status='0'{$q} ORDER BY is_manual='Y' DESC, {$columnName} {$sortOrder} LIMIT {$start},{$length}");

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $company = searchCompanyById($row['company'], $this->db);
            $data[] = [
                'id'               => $row['id'],
                'company'          => $row['company'],
                'company_name'     => $company ? $company['name'] : '',
                'veh_number'       => $row['veh_number'],
                'vehicle_weight'   => $row['vehicle_weight'],
                'transporter_name' => $row['transporter_name'],
                'customer_name'    => $row['customer_name'],
                'supplier_name'    => $row['supplier_name'],
                'is_manual'        => $row['is_manual'],
                'status'           => $row['status'] == '0' ? 'Active' : 'Inactive',
            ];
        }

        return [
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data'            => $data,
        ];
    }

    public function get($id, $type) {
        if ($type === 'lookup') {
            $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM Weight WHERE lorry_plate_no1=? AND is_complete='N'");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('s', $id);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['cnt'];
            $stmt->close();
            if ($count > 0) {
                throw new Exception('There is a pending record for this vehicle');
            }
            return $this->getByPlate($id);
        }

        if ($type === 'pullCustomer') {
            return $this->getByPlate($id);
        }

        $stmt = $this->db->prepare("SELECT * FROM Vehicle WHERE id=? AND status='0'");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? $this->mapRow($row) : null;
    }

    public function save($f) {
        $stmt = $this->db->prepare("INSERT INTO Vehicle (company, veh_number, vehicle_weight, transporter_code, transporter_name, customer_code, customer_name, supplier_code, supplier_name, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sssssssssss', $f['company'], $f['vehicleNo'], $f['vehicleWeight'], $f['transporterCode'], $f['transporter'], $f['customerCode'], $f['customer'], $f['supplierCode'], $f['supplier'], $this->username, $this->username);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function update($f) {
        $stmt = $this->db->prepare("UPDATE Vehicle SET company=?, veh_number=?, vehicle_weight=?, transporter_code=?, transporter_name=?, customer_code=?, customer_name=?, supplier_code=?, supplier_name=?, is_manual='N', created_by=?, modified_by=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('ssssssssssss', $f['company'], $f['vehicleNo'], $f['vehicleWeight'], $f['transporterCode'], $f['transporter'], $f['customerCode'], $f['customer'], $f['supplierCode'], $f['supplier'], $this->username, $this->username, $f['vehicleId']);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function delete($id, $isMulti) {
        if ($isMulti) {
            $ids = is_array($id) ? implode(',', array_map('intval', $id)) : $id;
            $stmt = $this->db->prepare("UPDATE Vehicle SET status='1' WHERE id IN ({$ids})");
            if (!$stmt) throw new Exception($this->db->error);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();
        } else {
            $stmt = $this->db->prepare("UPDATE Vehicle SET status='1' WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('s', $id);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();
        }
    }

    public function upload($data, $companyId) {
        $errors = [];
        $status = '0';
        $company = $companyId;

        foreach ($data as $row) {
            $vehicleNo    = !empty($row['VehicleNo'])        ? trim($row['VehicleNo'])        : '';
            $vehicleWeight= !empty($row['VehicleWeightKG'])  ? trim($row['VehicleWeightKG'])  : '';
            $customerCode = !empty($row['CustomerCode'])     ? trim($row['CustomerCode'])     : '';
            $customerName = !empty($row['CustomerName'])     ? trim($row['CustomerName'])     : '';
            $supplierCode = !empty($row['SupplierCode'])     ? trim($row['SupplierCode'])     : '';
            $supplierName = !empty($row['SupplierName'])     ? trim($row['SupplierName'])     : '';

            if (!empty($customerCode)) {
                $chk = $this->db->prepare("SELECT id FROM Customer WHERE customer_code=? AND status=?");
                $chk->bind_param('ss', $customerCode, $status);
                $chk->execute();
                $exists = $chk->get_result()->fetch_assoc();
                $chk->close();
                if (empty($exists)) {
                    $errors[] = "Customer: {$customerCode} doesn't exist in master data.";
                    continue;
                }
            }

            if (!empty($supplierCode)) {
                $chk = $this->db->prepare("SELECT id FROM Supplier WHERE supplier_code=? AND status=?");
                $chk->bind_param('ss', $supplierCode, $status);
                $chk->execute();
                $exists = $chk->get_result()->fetch_assoc();
                $chk->close();
                if (empty($exists)) {
                    $errors[] = "Supplier: {$supplierCode} doesn't exist in master data.";
                    continue;
                }
            }

            if (!empty($vehicleNo)) {
                $chk = $this->db->prepare("SELECT id FROM Vehicle WHERE veh_number=? AND company <=> ? AND status=?");
                $chk->bind_param('sss', $vehicleNo, $company, $status);
                $chk->execute();
                $exists = $chk->get_result()->fetch_assoc();
                $chk->close();
                if (!empty($exists)) {
                    $errors[] = "Vehicle: {$vehicleNo} already exists in master data.";
                    continue;
                }
                $stmt = $this->db->prepare("INSERT INTO Vehicle (company, veh_number, vehicle_weight, customer_code, customer_name, supplier_code, supplier_name, status, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssssssssss', $company, $vehicleNo, $vehicleWeight, $customerCode, $customerName, $supplierCode, $supplierName, $status, $this->username, $this->username);
                $stmt->execute();
                $stmt->close();
            }
        }

        return $errors;
    }

    private function getByPlate($plateNo) {
        $stmt = $this->db->prepare("SELECT * FROM Vehicle WHERE veh_number=? AND status='0'");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $plateNo);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? $this->mapRow($row) : null;
    }

    public function autoRegisterVehicle($plateNo, $vehicleWeight = 0) {
        if (empty($plateNo)) {
            return;
        }

        $stmt = $this->db->prepare("SELECT id FROM Vehicle WHERE veh_number=? AND status='0'");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('s', $plateNo);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            return;
        }

        $vehicleWeight = $vehicleWeight ?: 0;
        $stmt = $this->db->prepare("INSERT INTO Vehicle (veh_number, vehicle_weight, is_manual, created_by, modified_by) VALUES (?, ?, 'Y', ?, ?)");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('ssss', $plateNo, $vehicleWeight, $this->username, $this->username);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
    }

    private function mapRow($row) {
        return [
            'id'               => $row['id'],
            'company'          => $row['company'],
            'veh_number'       => $row['veh_number'],
            'vehicle_weight'   => $row['vehicle_weight'],
            'transporter_name' => $row['transporter_name'],
            'transporter_code' => $row['transporter_code'],
            'customer_code'    => $row['customer_code'],
            'customer_name'    => $row['customer_name'],
            'supplier_code'    => $row['supplier_code'],
            'supplier_name'    => $row['supplier_name'],
        ];
    }
}
?>
