<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/functions.php';
require_once __DIR__ . '/../requires/lookup.php';

class CustomerService extends BaseService {

    public function filter($post) {
        $start      = $post['start'];
        $length     = $post['length'];
        $draw       = $post['draw'];
        $columnName = $post['columns'][$post['order'][0]['column']]['data'] ?? 'customer_code';
        $sortOrder  = $post['order'][0]['dir'] ?? 'asc';
        // company_name is a computed column (not a real DB column), sort by company instead
        if ($columnName === 'company_name') $columnName = 'company';
        $search     = mysqli_real_escape_string($this->db, $post['search']['value']);
        $companyId  = isset($post['companyId']) ? intval($post['companyId']) : 0;
        $customerCode = isset($post['customerCode']) ? mysqli_real_escape_string($this->db, $post['customerCode']) : '';
        $customerName = isset($post['customerName']) ? mysqli_real_escape_string($this->db, $post['customerName']) : '';

        $q = '';
        if ($search !== '') {
            $q = " AND (customer_code LIKE '%{$search}%' OR name LIKE '%{$search}%')";
        }
        if ($companyId > 0) {
            $q .= " AND company={$companyId}";
        }
        if ($customerCode !== '') {
            $q .= " AND customer_code LIKE '%{$customerCode}%'";
        }
        if ($customerName !== '') {
            $q .= " AND name LIKE '%{$customerName}%'";
        }

        $totalRes = $this->db->query("SELECT COUNT(*) as c FROM Customer");
        $totalRecords = $totalRes->fetch_assoc()['c'];

        $filteredRes = $this->db->query("SELECT COUNT(*) as c FROM Customer WHERE status='0'{$q}");
        $totalFiltered = $filteredRes->fetch_assoc()['c'];

        $result = $this->db->query("SELECT * FROM Customer WHERE status='0'{$q} ORDER BY is_manual DESC, {$columnName} {$sortOrder} LIMIT {$start},{$length}");

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $company = searchCompanyById($row['company'], $this->db);
            $data[] = [
                'id'             => $row['id'],
                'company'        => $row['company'],
                'company_name'   => $company ? $company['name'] : '',
                'customer_code'  => $row['customer_code'],
                'name'           => $row['name'],
                'company_reg_no' => $row['company_reg_no'],
                'new_reg_no'     => $row['new_reg_no'],
                'address_line_1' => $row['address_line_1'],
                'address_line_2' => $row['address_line_2'],
                'address_line_3' => $row['address_line_3'],
                'phone_no'       => $row['phone_no'],
                'fax_no'         => $row['fax_no'],
                'contact_name'   => $row['contact_name'],
                'ic_no'          => $row['ic_no'],
                'tin_no'         => $row['tin_no'],
                'status'         => $row['status'],
                'is_manual'      => $row['is_manual'],
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
        $stmt = $this->db->prepare("SELECT * FROM Customer WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    public function save($f) {
        if ($this->isDuplicateCode($f['customerCode'], $f['company'], $f['customerId'])) {
            throw new Exception('Customer code already exists');
        }

        if (!empty($f['customerId'])) {
            $old = $this->get($f['customerId']);
            
            $stmt = $this->db->prepare("UPDATE Customer SET company=?, customer_code=?, company_reg_no=?, new_reg_no=?, name=?, address_line_1=?, address_line_2=?, address_line_3=?, phone_no=?, fax_no=?, contact_name=?, ic_no=?, tin_no=?, is_manual='N', modified_by=? WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('sssssssssssssss', $f['company'], $f['customerCode'], $f['companyRegNo'], $f['newRegNo'], $f['companyName'], $f['addressLine1'], $f['addressLine2'], $f['addressLine3'], $f['phoneNo'], $f['faxNo'], $f['contactName'], $f['icNo'], $f['tinNo'], $this->username, $f['customerId']);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();

            if ($old['customer_code'] !== $f['customerCode']) {
                updateMasterDataCodeValue($this->db, $old['customer_code'], $f['customerCode'], 'Customer');
            }
            if ($old['name'] !== $f['companyName']) {
                updateMasterDataNameValue($this->db, $old['name'], $f['companyName'], 'Customer');
            }
        } else {
            $stmt = $this->db->prepare("INSERT INTO Customer (company, customer_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('sssssssssssssss', $f['company'], $f['customerCode'], $f['companyRegNo'], $f['newRegNo'], $f['companyName'], $f['addressLine1'], $f['addressLine2'], $f['addressLine3'], $f['phoneNo'], $f['faxNo'], $f['contactName'], $f['icNo'], $f['tinNo'], $this->username, $this->username);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();
        }
    }

    public function delete($id, $isMulti) {
        if ($isMulti) {
            $ids = is_array($id) ? implode(',', array_map('intval', $id)) : $id;
            $stmt = $this->db->prepare("UPDATE Customer SET status='1' WHERE id IN ({$ids})");
        } else {
            $stmt = $this->db->prepare("UPDATE Customer SET status='1' WHERE id=?");
            $stmt->bind_param('s', $id);
        }
        if (!$stmt) throw new Exception($this->db->error);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function upload($data, $companyId) {
        $errors = [];
        $status = '0';
        $company = $companyId;

        foreach ($data as $index => $row) {
            $code     = !empty($row['Code']) ? trim($row['Code']) : '';
            $name      = !empty($row['Name']) ? trim($row['Name']) : '';
            $regNo     = !empty($row['RegNo']) ? trim($row['RegNo']) : '';
            $newRegNo  = !empty($row['NewRegNo']) ? trim($row['NewRegNo']) : '';
            $addr1     = !empty($row['Addr1']) ? $row['Addr1'] : '';
            $addr2     = !empty($row['Addr2']) ? $row['Addr2'] : '';
            $addr3     = !empty($row['Addr3']) ? $row['Addr3'] : '';
            $phone     = !empty($row['Tel']) ? $row['Tel'] : '';
            $fax       = !empty($row['Fax']) ? $row['Fax'] : '';
            $contact   = !empty($row['ContactName']) ? $row['ContactName'] : '';
            $icNo      = !empty($row['ICNo']) ? $row['ICNo'] : '';
            $tinNo     = !empty($row['TinNo']) ? $row['TinNo'] : '';

            $rowNum = $index + 2;

            if (empty($code)) {
                continue;
            }

            $chk = $this->db->prepare("SELECT id FROM Customer WHERE customer_code=? AND company=? AND status=?");
            $chk->bind_param('sss', $code, $company, $status);
            $chk->execute();
            $exists = $chk->get_result()->fetch_assoc();
            $chk->close();

            if (!empty($exists)) {
                $errors[] = "Row {$rowNum}: Customer '{$name}' already exist in master data.";
                continue;
            }

            $stmt = $this->db->prepare("INSERT INTO Customer (company, customer_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssssssssss', $company, $code, $regNo, $newRegNo, $name, $addr1, $addr2, $addr3, $phone, $fax, $contact, $icNo, $tinNo, $this->username, $this->username);
            $stmt->execute();
            $stmt->close();
        }

        return $errors;
    }

    /**
     * Auto register customer from manual input
     * @param string $customerName
     * @return array ['customer_code' => code, 'name' => name]
     */
    public function autoRegisterCustomer($customerName) {
        $customerName = trim($customerName);
        
        $stmt = $this->db->prepare("SELECT customer_code FROM Customer WHERE name=? AND status='0'");
        $stmt->bind_param('s', $customerName);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($row) {
            return ['customer_code' => $row['customer_code'], 'name' => $customerName];
        }
        
        $customerCode = 'C' . date('ymdHis') . rand(100, 999);
        $isManual = 'Y';
        
        $stmt = $this->db->prepare("INSERT INTO Customer (customer_code, name, is_manual, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $customerCode, $customerName, $isManual, $this->username);
        $stmt->execute();
        $stmt->close();
        
        return ['customer_code' => $customerCode, 'name' => $customerName];
    }

    private function isDuplicateCode($code, $company, $excludeId = null) {
        // Duplicate check is scoped to the same company (<=> is null-safe)
        $sql = "SELECT id FROM Customer WHERE customer_code=? AND company <=> ? AND status='0'";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ssi', $code, $company, $excludeId);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ss', $code, $company);
        }
        $stmt->execute();
        $stmt->store_result();
        $isDuplicate = $stmt->num_rows > 0;
        $stmt->close();
        return $isDuplicate;
    }
}
?>
