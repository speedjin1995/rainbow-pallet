<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

class ProductCategoryService extends BaseService {

    protected $table = 'Product_Categories';

    public function filter($post) {
        $draw = $post['draw'] ?? 1;
        $start = $post['start'] ?? 0;
        $length = $post['length'] ?? 10;
        $searchValue = isset($post['search']['value']) ? mysqli_real_escape_string($this->db, $post['search']['value']) : '';
        $companyId  = isset($post['companyId']) ? intval($post['companyId']) : 0;
        $categoryName = isset($post['categoryName']) ? mysqli_real_escape_string($this->db, $post['categoryName']) : '';

        $columnIndex = $post['order'][0]['column'] ?? 0;
        $columnName = $post['columns'][$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $post['order'][0]['dir'] ?? 'asc';
        // company_name is a computed column (not a real DB column), sort by company instead
        if ($columnName === 'company_name') $columnName = 'company';

        // Total records
        $totalQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0";
        $totalResult = $this->db->query($totalQuery);
        $totalRecords = $totalResult->fetch_assoc()['total'];

        // Search filter
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = " AND (category_name LIKE '%{$searchValue}%' OR post_to_sql LIKE '%{$searchValue}%')";
        }
        if ($companyId > 0) {
            $searchQuery .= " AND company={$companyId}";
        }
        if ($categoryName !== '') {
            $searchQuery .= " AND category_name LIKE '%{$categoryName}%'";
        }

        // Filtered records
        $filteredQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0 {$searchQuery}";
        $filteredResult = $this->db->query($filteredQuery);
        $totalFiltered = $filteredResult->fetch_assoc()['total'];
        
        // Data
        $dataQuery = "SELECT id, company, category_name,
            CASE WHEN post_to_sql = 'Y' THEN 'Yes' ELSE 'No' END AS post_to_sql,
            is_sales, is_purchase, is_local, is_port, is_misc, is_sawn_timber, status
            FROM {$this->table} WHERE status = 0 {$searchQuery} ORDER BY {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}";
        $dataResult = $this->db->query($dataQuery);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $company = searchCompanyById($row['company'], $this->db);
            $row['company_name'] = $company ? $company['name'] : '';
            $data[] = $row;
        }
        
        return [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ];
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    public function getListByCompany($companyId) {
        $stmt = $this->db->prepare("SELECT id, category_name FROM {$this->table} WHERE company = ? AND status = '0' ORDER BY category_name");
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

    public function save($f) {
        if ($this->isDuplicateName($f['categoryName'], $f['company'], $f['id'])) {
            throw new Exception('Category name already exists');
        }

        $isSales = in_array('Sales', $f['transactionStatus']) ? 'Y' : 'N';
        $isPurchase = in_array('Purchase', $f['transactionStatus']) ? 'Y' : 'N';
        $isLocal = in_array('Local', $f['transactionStatus']) ? 'Y' : 'N';
        $isPort = in_array('Port', $f['transactionStatus']) ? 'Y' : 'N';
        $isMisc = in_array('Misc', $f['transactionStatus']) ? 'Y' : 'N';
        $isSawnTimber = ($f['isSawnTimber'] ?? 'N') === 'Y' ? 'Y' : 'N';

        if (!empty($f['id'])) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET company=?, category_name=?, post_to_sql=?, is_sales=?, is_purchase=?, is_local=?, is_port=?, is_misc=?, is_sawn_timber=?, modified_by=? WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('ssssssssssi', $f['company'], $f['categoryName'], $f['postToSql'], $isSales, $isPurchase, $isLocal, $isPort, $isMisc, $isSawnTimber, $this->username, $f['id']);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();
            return ['id' => $f['id']];
        } else {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (company, category_name, post_to_sql, is_sales, is_purchase, is_local, is_port, is_misc, is_sawn_timber, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('ssssssssss', $f['company'], $f['categoryName'], $f['postToSql'], $isSales, $isPurchase, $isLocal, $isPort, $isMisc, $isSawnTimber, $this->username);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $id = $stmt->insert_id;
            $stmt->close();
            return ['id' => $id];
        }
    }

    /**
     * Get active items still tied to the given categories, grouped by category,
     * together with the replacement categories (same company, excluding the ones being deleted)
     */
    public function getTiedItems($ids) {
        $ids = array_values(array_filter(array_map('intval', (array)$ids)));
        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT c.id, c.company, c.category_name, p.product_code, p.name
            FROM {$this->table} c
            JOIN Product p ON p.category = c.id AND p.status = '0'
            WHERE c.id IN ($placeholders)
            ORDER BY c.category_name, p.product_code");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $result = $stmt->get_result();

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            if (!isset($categories[$row['id']])) {
                $categories[$row['id']] = [
                    'id'            => $row['id'],
                    'company'       => $row['company'],
                    'category_name' => $row['category_name'],
                    'items'         => [],
                ];
            }
            $categories[$row['id']]['items'][] = ['product_code' => $row['product_code'], 'name' => $row['name']];
        }
        $stmt->close();

        // Replacement options per company, excluding categories being deleted
        $optionsByCompany = [];
        foreach ($categories as $catId => $category) {
            $companyId = intval($category['company']);
            if (!isset($optionsByCompany[$companyId])) {
                $optionsByCompany[$companyId] = array_values(array_filter($this->getListByCompany($companyId), function ($option) use ($ids) {
                    return !in_array(intval($option['id']), $ids);
                }));
            }
            $categories[$catId]['options'] = $optionsByCompany[$companyId];
        }

        return array_values($categories);
    }

    /**
     * Move active items from the deleted categories to their selected replacement category
     */
    private function reassignItems($ids, $reassign) {
        $ids = array_values(array_filter(array_map('intval', (array)$ids)));
        $reassign = is_array($reassign) ? $reassign : [];

        foreach ($this->getTiedItems($ids) as $category) {
            $newId = intval($reassign[$category['id']] ?? 0);
            if ($newId <= 0) {
                throw new Exception("Please select a new category for items under '{$category['category_name']}'");
            }

            // Replacement must be active, in the same company and not one of the categories being deleted
            $newCategory = $this->get($newId);
            if (empty($newCategory) || $newCategory['status'] != '0' || in_array($newId, $ids)
                || $newCategory['company'] != $category['company']) {
                throw new Exception("Invalid new category selected for '{$category['category_name']}'");
            }

            $stmt = $this->db->prepare("UPDATE Product SET category=?, modified_by=? WHERE category=? AND status='0'");
            if (!$stmt) throw new Exception($this->db->error);
            $categoryId = intval($category['id']);
            $stmt->bind_param('isi', $newId, $this->username, $categoryId);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();
        }
    }

    public function delete($id, $type = null, $reassign = []) {
        // Items tied to the categories must be moved to another category before deleting
        $this->reassignItems($id, $reassign);

        if ($type === 'MULTI' && is_array($id)) {
            $placeholders = implode(',', array_fill(0, count($id), '?'));
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id IN ($placeholders)");
            if (!$stmt) throw new Exception($this->db->error);
            $types = 's' . str_repeat('i', count($id));
            $params = array_merge([$this->username], $id);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('si', $this->username, $id);
        }
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function reactivate($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status=0, modified_by=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('si', $this->username, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function upload($data, $companyId) {
        $errors = [];
        $company = $companyId;

        foreach ($data as $index => $row) {
            $rowNum = $index + 2;

            $categoryName = isset($row['CategoryName']) ? trim($row['CategoryName']) : null;
            $postToSql = isset($row['PostToSQL']) ? trim($row['PostToSQL']) : null;

            if (empty($categoryName)) {
                continue;
            }

            // Validate and format post to sql value
            if (in_array($postToSql, ['Yes', 'Y'])) {
                $postToSql = 'Y';
            } elseif (in_array($postToSql, ['No', 'N'])) {
                $postToSql = 'N';
            } else {
                $errors[] = "Row {$rowNum}: Post to SQL must be 'Yes' or 'No'";
                continue;
            }
            
            // Check duplicate (scoped to the same company)
            if ($this->isDuplicateName($categoryName, $company)) {
                $errors[] = "Row {$rowNum}: Category Name '{$categoryName}' already exists";
                continue;
            }
            
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (company, category_name, post_to_sql, created_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $company, $categoryName, $postToSql, $this->username);
            $stmt->execute();
            $stmt->close();
        }
        
        return $errors;
    }

    private function isDuplicateName($name, $company, $excludeId = null) {
        // Duplicate check is scoped to the same company (<=> is null-safe)
        $sql = "SELECT id FROM {$this->table} WHERE category_name=? AND company <=> ? AND status='0'";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ssi', $name, $company, $excludeId);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ss', $name, $company);
        }
        $stmt->execute();
        $stmt->store_result();
        $isDuplicate = $stmt->num_rows > 0;
        $stmt->close();
        return $isDuplicate;
    }
}
?>
