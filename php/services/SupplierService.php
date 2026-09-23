<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/functions.php';
require_once __DIR__ . '/../requires/lookup.php';

class SupplierService extends BaseService {

    public function filter($post) {
        $start      = $post['start'];
        $length     = $post['length'];
        $draw       = $post['draw'];
        $columnName = $post['columns'][$post['order'][0]['column']]['data'] ?? 'supplier_code';
        $sortOrder  = $post['order'][0]['dir'] ?? 'asc';
        $search     = mysqli_real_escape_string($this->db, $post['search']['value']);

        $q = '';
        if ($search !== '') {
            $q = " AND (name LIKE '%{$search}%' OR company_reg_no LIKE '%{$search}%' OR supplier_code LIKE '%{$search}%')";
        }

        $totalRes = $this->db->query("SELECT COUNT(*) as c FROM Supplier");
        $totalRecords = $totalRes->fetch_assoc()['c'];

        $filteredRes = $this->db->query("SELECT COUNT(*) as c FROM Supplier WHERE status='0'{$q}");
        $totalFiltered = $filteredRes->fetch_assoc()['c'];

        $result = $this->db->query("SELECT * FROM Supplier WHERE status='0'{$q} ORDER BY is_manual DESC, {$columnName} {$sortOrder} LIMIT {$start},{$length}");

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $company = searchCompanyById($row['company'], $this->db);
            $data[] = [
                'id'                  => $row['id'],
                'company'             => $row['company'],
                'company_name'        => $company ? $company['name'] : '',
                'supplier_code'       => $row['supplier_code'],
                'name'                => $row['name'],
                'company_reg_no'      => $row['company_reg_no'],
                'new_reg_no'          => $row['new_reg_no'],
                'address_line_1'      => $row['address_line_1'],
                'address_line_2'      => $row['address_line_2'],
                'address_line_3'      => $row['address_line_3'],
                'phone_no'            => $row['phone_no'],
                'fax_no'              => $row['fax_no'],
                'contact_name'        => $row['contact_name'],
                'ic_no'               => $row['ic_no'],
                'tin_no'              => $row['tin_no'],
                'account_no'          => $row['account_no'],
                'payment_term'        => $row['payment_term'],
                'payment_term_period' => $row['payment_term_period'],
                'status'              => $row['status'],
                'is_manual'           => $row['is_manual'],
            ];
        }

        return [
            'draw'                 => intval($draw),
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $totalFiltered,
            'aaData'               => $data,
        ];
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM Supplier WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    public function save($f) {
        if ($this->isDuplicateCode($f['supplierCode'], $f['supplierId'])) {
            throw new Exception('Supplier code already exists');
        }

        if (!empty($f['supplierId'])) {
            $old = $this->get($f['supplierId']);
            
            $stmt = $this->db->prepare("UPDATE Supplier SET company=?, supplier_code=?, company_reg_no=?, new_reg_no=?, name=?, address_line_1=?, address_line_2=?, address_line_3=?, phone_no=?, fax_no=?, contact_name=?, ic_no=?, tin_no=?, payment_term=?, payment_term_period=?, account_no=?, is_manual='N', modified_by=? WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('ssssssssssssssssss', $f['company'], $f['supplierCode'], $f['companyRegNo'], $f['newRegNo'], $f['companyName'], $f['addressLine1'], $f['addressLine2'], $f['addressLine3'], $f['phoneNo'], $f['faxNo'], $f['contactName'], $f['icNo'], $f['tinNo'], $f['paymentTerm'], $f['paymentTermPeriod'], $f['accountNo'], $this->username, $f['supplierId']);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();

            if ($old['supplier_code'] !== $f['supplierCode']) {
                updateMasterDataCodeValue($this->db, $old['supplier_code'], $f['supplierCode'], 'Supplier');
            }
            if ($old['name'] !== $f['companyName']) {
                updateMasterDataNameValue($this->db, $old['name'], $f['companyName'], 'Supplier');
            }
        } else {
            $stmt = $this->db->prepare("INSERT INTO Supplier (company, supplier_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, payment_term, payment_term_period, account_no, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('ssssssssssssssssss', $f['company'], $f['supplierCode'], $f['companyRegNo'], $f['newRegNo'], $f['companyName'], $f['addressLine1'], $f['addressLine2'], $f['addressLine3'], $f['phoneNo'], $f['faxNo'], $f['contactName'], $f['icNo'], $f['tinNo'], $f['paymentTerm'], $f['paymentTermPeriod'], $f['accountNo'], $this->username, $this->username);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();
        }
    }

    public function delete($id, $isMulti) {
        if ($isMulti) {
            $ids = is_array($id) ? implode(',', array_map('intval', $id)) : $id;
            $stmt = $this->db->prepare("UPDATE Supplier SET status='1' WHERE id IN ({$ids})");
        } else {
            $stmt = $this->db->prepare("UPDATE Supplier SET status='1' WHERE id=?");
            $stmt->bind_param('s', $id);
        }
        if (!$stmt) throw new Exception($this->db->error);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function upload($data) {
        $errors = [];
        $status = '0';

        foreach ($data as $index => $row) {
            $companyName = !empty($row['Company']) ? trim($row['Company']) : '';
            $code      = !empty($row['Code']) ? trim($row['Code']) : '';
            $name      = !empty($row['Name']) ? trim($row['Name']) : '';
            $regNo     = !empty($row['RegNo']) ? trim($row['RegNo']) : '';
            $newRegNo  = !empty($row['NewRegNo']) ? trim($row['NewRegNo']) : '';
            $addr1     = !empty($row['Addr1']) ? $row['Addr1'] : '';
            $addr2     = !empty($row['Addr2']) ? $row['Addr2'] : '';
            $addr3     = !empty($row['Addr3']) ? $row['Addr3'] : '';
            $addr4     = !empty($row['Addr4']) ? $row['Addr4'] : '';
            $phone     = !empty($row['Tel']) ? $row['Tel'] : '';
            $fax       = !empty($row['Fax']) ? $row['Fax'] : '';
            $contact   = !empty($row['ContactName']) ? $row['ContactName'] : '';
            $icNo      = !empty($row['ICNo']) ? $row['ICNo'] : '';
            $tinNo     = !empty($row['TinNo']) ? $row['TinNo'] : '';
            $accountNo = !empty($row['AccountNo']) ? $row['AccountNo'] : '';
            $paymentTerm = !empty($row['PaymentTerm']) ? $row['PaymentTerm'] : '';
            $paymentTermPeriod = !empty($row['PaymentTermPeriod']) ? $row['PaymentTermPeriod'] : '';

            $rowNum = $index + 2;

            if (empty($code)) {
                continue;
            }

            // Lookup company by name
            $company = null;
            if (!empty($companyName)) {
                $company = searchCompanyIdByName($companyName, $this->db);
                if (empty($company)) {
                    $errors[] = "Row {$rowNum}: Company '{$companyName}' not found.";
                    continue;
                }
            }

            $chk = $this->db->prepare("SELECT id FROM Supplier WHERE supplier_code=? AND status=?");
            $chk->bind_param('ss', $code, $status);
            $chk->execute();
            $exists = $chk->get_result()->fetch_assoc();
            $chk->close();

            if (!empty($exists)) {
                $errors[] = "Row {$rowNum}: Supplier '{$name}' already exist in master data.";
                continue;
            }

            $stmt = $this->db->prepare("INSERT INTO Supplier (company, supplier_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, address_line_4, phone_no, fax_no, contact_name, ic_no, tin_no, account_no, payment_term, payment_term_period, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssssssssssssss', $company, $code, $regNo, $newRegNo, $name, $addr1, $addr2, $addr3, $addr4, $phone, $fax, $contact, $icNo, $tinNo, $accountNo, $paymentTerm, $paymentTermPeriod, $this->username, $this->username);
            $stmt->execute();
            $stmt->close();
        }

        return $errors;
    }

    public function autoRegisterSupplier($supplierName) {
        $supplierName = trim($supplierName);
        
        $stmt = $this->db->prepare("SELECT supplier_code FROM Supplier WHERE name=? AND status='0'");
        $stmt->bind_param('s', $supplierName);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($row) {
            return ['supplier_code' => $row['supplier_code'], 'name' => $supplierName];
        }
        
        $supplierCode = 'S' . date('ymdHis') . rand(100, 999);
        $isManual = 'Y';
        
        $stmt = $this->db->prepare("INSERT INTO Supplier (supplier_code, name, is_manual, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $supplierCode, $supplierName, $isManual, $this->username);
        $stmt->execute();
        $stmt->close();
        
        return ['supplier_code' => $supplierCode, 'name' => $supplierName];
    }

    private function isDuplicateCode($code, $excludeId = null) {
        $sql = "SELECT id FROM Supplier WHERE supplier_code=? AND status='0'";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $code, $excludeId);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $code);
        }
        $stmt->execute();
        $stmt->store_result();
        $isDuplicate = $stmt->num_rows > 0;
        $stmt->close();
        return $isDuplicate;
    }
}
?>
