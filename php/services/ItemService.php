<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

class ItemService extends BaseService {
    
    protected $table = 'Product';

    /**
     * Get all items (for DataTables)
     */
    public function getAll($post) {
        $draw = $post['draw'] ?? 1;
        $start = $post['start'] ?? 0;
        $length = $post['length'] ?? 10;
        $searchValue = isset($post['search']['value']) ? mysqli_real_escape_string($this->db, $post['search']['value']) : '';
        $companyId  = isset($post['companyId']) ? intval($post['companyId']) : 0;
        $itemCode = isset($post['itemCode']) ? mysqli_real_escape_string($this->db, $post['itemCode']) : '';
        $itemName = isset($post['itemName']) ? mysqli_real_escape_string($this->db, $post['itemName']) : '';

        $columnIndex = $post['order'][0]['column'] ?? 0;
        $columnName = $post['columns'][$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $post['order'][0]['dir'] ?? 'asc';
        // company_name is a computed column (not a real DB column), sort by company instead
        if ($columnName === 'company_name') $columnName = 'p.company';

        // Total records
        $totalQuery = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 0";
        $totalResult = $this->db->query($totalQuery);
        $totalRecords = $totalResult->fetch_assoc()['total'];

        // Search filter
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = " AND (p.product_code LIKE '%{$searchValue}%' OR p.name LIKE '%{$searchValue}%' OR p.description LIKE '%{$searchValue}%' OR c.category_name LIKE '%{$searchValue}%')";
        }
        if ($companyId > 0) {
            $searchQuery .= " AND p.company={$companyId}";
        }
        if ($itemCode !== '') {
            $searchQuery .= " AND p.product_code LIKE '%{$itemCode}%'";
        }
        if ($itemName !== '') {
            $searchQuery .= " AND p.name LIKE '%{$itemName}%'";
        }

        // Filtered records
        $filteredQuery = "SELECT COUNT(*) as total FROM {$this->table} p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = 0 {$searchQuery}";
        $filteredResult = $this->db->query($filteredQuery);
        $totalFiltered = $filteredResult->fetch_assoc()['total'];
        
        // Data - order by is_manual DESC first to show manual items at top
        $dataQuery = "SELECT p.id, p.company, p.product_code, p.name, p.description, p.status, IFNULL(p.is_manual, 'N') as is_manual, IFNULL(c.category_name, '') as category_name FROM {$this->table} p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = 0 {$searchQuery} ORDER BY p.is_manual DESC, {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}";
        $dataResult = $this->db->query($dataQuery);
        
        $data = [];
        while ($row = $dataResult->fetch_assoc()) {
            $company = searchCompanyById($row['company'], $this->db);
            $row['company_name'] = $company ? $company['name'] : '';
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
     * Create new item
     */
    public function create($post) {
        $company = isset($post['company']) && $post['company'] !== '' ? $post['company'] : null;
        $productCode = trim($post['productCode']);
        $productName = trim($post['productName']);
        $categoryId = $post['categoryId'];
        $uom = $post['uom'];
        $description = $post['description'] ?? null;
        $varianceType = $post['varianceType'] ?? null;
        $high = $post['high'] ?? 0;
        $low = $post['low'] ?? 0;
        
        // Check duplicate (scoped to the same company)
        if ($this->isDuplicate('product_code', $productCode, $company)) {
            throw new Exception('Product code already exists');
        }

        $this->db->begin_transaction();

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (company, product_code, name, category, uom, description, variance, high, low, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        
        $stmt->bind_param('ssssssssss', $company, $productCode, $productName, $categoryId, $uom, $description, $varianceType, $high, $low, $this->username);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $insertId = $stmt->insert_id;
        $stmt->close();
        
        // Handle UOM Conversion
        $this->saveUomConversion($insertId, $post);
        
        $this->db->commit();
        
        return ['id' => $insertId];
    }
    
    /**
     * Update existing item
     */
    public function update($post) {
        $id = $post['id'];
        $company = isset($post['company']) && $post['company'] !== '' ? $post['company'] : null;
        $productCode = trim($post['productCode']);
        $productName = trim($post['productName']);
        $categoryId = $post['categoryId'];
        $uom = $post['uom'];
        $description = $post['description'] ?? null;
        $varianceType = $post['varianceType'] ?? null;
        $high = $post['high'] ?? 0;
        $low = $post['low'] ?? 0;
        
        // Check duplicate (scoped to the same company, exclude current record)
        if ($this->isDuplicate('product_code', $productCode, $company, $id)) {
            throw new Exception('Product code already exists');
        }

        $this->db->begin_transaction();

        // Get old values before update
        $stmt = $this->db->prepare("SELECT product_code, name, company FROM {$this->table} WHERE id = ?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->bind_result($oldCode, $oldName, $oldCompany);
        $stmt->fetch();
        $stmt->close();

        $stmt = $this->db->prepare("UPDATE {$this->table} SET company=?, product_code=?, name=?, category=?, uom=?, description=?, variance=?, high=?, low=?, modified_by=? WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        
        $stmt->bind_param('sssssssssss', $company, $productCode, $productName, $categoryId, $uom, $description, $varianceType, $high, $low, $this->username, $id);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
        // Handle UOM Conversion (smart update)
        $this->syncUomConversion($id, $post);

        // Cascade code/name changes to the weighing records of the item's company.
        // Items are used as both sales products and purchase raw materials, so update both.
        foreach (['Product', 'Raw Material'] as $module) {
            if ($oldCode !== null && $oldCode !== $productCode) {
                $this->updateMasterDataCodeValue($oldCode, $productCode, $module, $oldCompany);
            }
            if ($oldName !== null && $oldName !== $productName) {
                $this->updateMasterDataNameValue($oldName, $productName, $module, $oldCompany);
            }
        }

        $this->db->commit();

        return ['id' => $id];
    }
    
    /**
     * Save UOM Conversion for new product
     */
    private function saveUomConversion($productId, $post) {
        if (!isset($post['uomNo']) || !is_array($post['uomNo'])) {
            return;
        }
        
        $uomNo = $post['uomNo'];
        $convUom = $post['convUom'];
        $rate = $post['rate'];
        
        $stmt = $this->db->prepare("INSERT INTO Product_Uom (product_id, unit_id, rate) VALUES (?, ?, ?)");
        
        foreach ($uomNo as $key => $no) {
            if (!empty($convUom[$key])) {
                $stmt->bind_param('iis', $productId, $convUom[$key], $rate[$key]);
                $stmt->execute();
            }
        }
        $stmt->close();
    }
    
    /**
     * Smart update UOM Conversion
     */
    private function syncUomConversion($productId, $post) {
        if (!isset($post['uomNo']) || !is_array($post['uomNo'])) {
            // No UOM data submitted - soft delete all existing
            $stmt = $this->db->prepare("UPDATE Product_Uom SET status = 1 WHERE product_id = ? AND status = 0");
            $stmt->bind_param('i', $productId);
            $stmt->execute();
            $stmt->close();
            return;
        }
        
        $uomNo = $post['uomNo'];
        $uomId = $post['uomId'];
        $convUom = $post['convUom'];
        $rate = $post['rate'];
        
        // Collect submitted IDs (existing records being kept)
        $submittedIds = [];
        foreach ($uomId as $key => $id) {
            if (!empty($id)) {
                $submittedIds[] = (int)$id;
            }
        }
        
        // Soft delete records not in submitted list
        if (!empty($submittedIds)) {
            $placeholders = implode(',', $submittedIds);
            $this->db->query("UPDATE Product_Uom SET status = 1 WHERE product_id = {$productId} AND status = 0 AND id NOT IN ({$placeholders})");
        } else {
            // No existing IDs submitted - delete all
            $this->db->query("UPDATE Product_Uom SET status = 1 WHERE product_id = {$productId} AND status = 0");
        }
        
        // Update existing / Insert new
        $updateStmt = $this->db->prepare("UPDATE Product_Uom SET unit_id = ?, rate = ? WHERE id = ?");
        $insertStmt = $this->db->prepare("INSERT INTO Product_Uom (product_id, unit_id, rate) VALUES (?, ?, ?)");
        
        foreach ($uomNo as $key => $no) {
            if (empty($convUom[$key])) continue;
            
            if (!empty($uomId[$key])) {
                // Update existing
                $updateStmt->bind_param('isi', $convUom[$key], $rate[$key], $uomId[$key]);
                $updateStmt->execute();
            } else {
                // Insert new
                $insertStmt->bind_param('iis', $productId, $convUom[$key], $rate[$key]);
                $insertStmt->execute();
            }
        }
        
        $updateStmt->close();
        $insertStmt->close();
    }
    
    /**
     * Delete (soft delete) item
     */
    public function delete($id, $type = null) {
        $this->db->begin_transaction();
        
        if ($type === 'MULTI' && is_array($id)) {
            $placeholders = implode(',', array_fill(0, count($id), '?'));
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id IN ($placeholders)");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            $types = 's' . str_repeat('i', count($id));
            $params = array_merge([$this->username], $id);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=1, modified_by=? WHERE id=?");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            $stmt->bind_param('si', $this->username, $id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        $this->db->commit();
    }
    
    /**
     * Reactivate item
     */
    public function reactivate($id) {
        $this->db->begin_transaction();
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status=0, modified_by=? WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        
        $stmt->bind_param('si', $this->username, $id);
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        $this->db->commit();
    }
    
    /**
     * Get single item by ID
     */
    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            
            // Get UOM conversions
            $uomStmt = $this->db->prepare("SELECT pu.id, pu.unit_id, pu.rate, u.unit FROM Product_Uom pu LEFT JOIN Units u ON pu.unit_id = u.id WHERE pu.product_id = ? AND pu.status = 0");
            $uomStmt->bind_param('i', $id);
            $uomStmt->execute();
            $uomResult = $uomStmt->get_result();
            
            $uomData = [];
            while ($uomRow = $uomResult->fetch_assoc()) {
                $uomData[] = $uomRow;
            }
            $uomStmt->close();
            
            $row['uom_conversions'] = $uomData;
            
            return $row;
        } else {
            $stmt->close();
            return null;
        }
    }
    
    /**
     * Item prices: product pricing, customer/supplier price entries with their qty tiers,
     * and the customers/suppliers of the item's company for the party dropdown
     */
    public function getPrices($id) {
        $product = $this->getPriceProduct($id);
        if (!$product) {
            return null;
        }

        $entries = [];
        $stmt = $this->db->prepare("SELECT id, party_type, party_id, date_from, date_to, price_type FROM Product_Price WHERE product_id = ? AND status = 0 ORDER BY party_type, date_from, id");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row['date_from'] = date('d-m-Y', strtotime($row['date_from']));
            $row['date_to'] = date('d-m-Y', strtotime($row['date_to']));
            $row['tiers'] = [];
            $entries[$row['id']] = $row;
        }
        $stmt->close();

        if (!empty($entries)) {
            $ids = array_keys($entries);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("SELECT price_id, qty_from, qty_to, unit_price, discount, discount_type FROM Product_Price_Tier WHERE status = 0 AND price_id IN ({$placeholders}) ORDER BY qty_from, id");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $entries[$row['price_id']]['tiers'][] = $row;
            }
            $stmt->close();
        }

        return [
            'product'   => $product,
            'entries'   => array_values($entries),
            'customers' => $this->getPriceParties('Customer', 'customer_code', $product['company']),
            'suppliers' => $this->getPriceParties('Supplier', 'supplier_code', $product['company']),
        ];
    }

    /**
     * Save item prices. Price entries are replaced as a whole:
     * existing entries/tiers are soft deleted and the submitted ones inserted.
     */
    public function savePrices($id, $data) {
        $product = $this->getPriceProduct($id);
        if (!$product) {
            throw new InvalidArgumentException('Record not found');
        }

        $purchasePrice = $this->toPrice($data['purchasePrice'] ?? '', 'Purchase Price');
        $sellingPrice = $this->toPrice($data['sellingPrice'] ?? '', 'Selling Price');
        $entries = $this->validatePriceEntries($data['entries'] ?? [], $product['company']);

        $this->db->begin_transaction();
        try {
            // For the log triggers: flag this Product_Log row as a price save, the Product triggers
            // then set @product_log_id which the price/tier log rows link to
            $stmt = $this->db->prepare("SET @product_log_id = NULL, @product_price_save = 'Y', @product_price_action_by = ?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('s', $this->username);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();

            $stmt = $this->db->prepare("UPDATE {$this->table} SET purchase_price = ?, selling_price = ?, modified_by = ? WHERE id = ?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('ddsi', $purchasePrice, $sellingPrice, $this->username, $id);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();

            // Only the Product_Log row above is a price save, not later edits on this connection
            $this->db->query("SET @product_price_save = NULL");

            $stmt = $this->db->prepare("UPDATE Product_Price_Tier SET status = 1 WHERE status = 0 AND price_id IN (SELECT id FROM Product_Price WHERE product_id = ? AND status = 0)");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();

            $stmt = $this->db->prepare("UPDATE Product_Price SET status = 1, modified_by = ? WHERE product_id = ? AND status = 0");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('si', $this->username, $id);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();

            $entryStmt = $this->db->prepare("INSERT INTO Product_Price (product_id, party_type, party_id, date_from, date_to, price_type, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $tierStmt = $this->db->prepare("INSERT INTO Product_Price_Tier (price_id, qty_from, qty_to, unit_price, discount, discount_type) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$entryStmt || !$tierStmt) throw new Exception($this->db->error);

            foreach ($entries as $entry) {
                $entryStmt->bind_param('isisssss', $id, $entry['partyType'], $entry['partyId'], $entry['dateFrom'], $entry['dateTo'], $entry['priceType'], $this->username, $this->username);
                if (!$entryStmt->execute()) throw new Exception($entryStmt->error);
                $priceId = $entryStmt->insert_id;

                foreach ($entry['tiers'] as $tier) {
                    $tierStmt->bind_param('idddds', $priceId, $tier['qtyFrom'], $tier['qtyTo'], $tier['unitPrice'], $tier['discount'], $tier['discountType']);
                    if (!$tierStmt->execute()) throw new Exception($tierStmt->error);
                }
            }
            $entryStmt->close();
            $tierStmt->close();

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            $this->db->query("SET @product_price_save = NULL");
            throw $e;
        }
    }

    /**
     * Product for price management, restricted to the user's companies without view_all_companies
     */
    private function getPriceProduct($id) {
        $stmt = $this->db->prepare("SELECT id, company, product_code, name, purchase_price, selling_price FROM {$this->table} WHERE id = ? AND status = 0");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            return null;
        }

        if (!hasModulePermission('Master Data', 'Items', ['view_all_companies'])) {
            $allowed = array_map('intval', array_merge((array) ($_SESSION['company_ids'] ?? []), [$_SESSION['company_id'] ?? 0]));
            if (!in_array(intval($product['company']), $allowed, true)) {
                return null;
            }
        }

        return $product;
    }

    private function getPriceParties($table, $codeColumn, $companyId) {
        $stmt = $this->db->prepare("SELECT id, {$codeColumn} AS code, name FROM {$table} WHERE company = ? AND status = '0' ORDER BY name");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('i', $companyId);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Suggested unit price for a weighing's price update: the party's price entry effective on
     * the given date, else the item's default price for that side, else 0 when nothing is set up.
     * $type picks which side of the item/party: 'DO' (Delivery Order) = Customer / Selling Price,
     * 'GR' (Goods Received) = Supplier / Purchase Price.
     */
    public function getSuggestedPrice($companyId, $productCode, $partyCode, $date, $type = 'DO') {
        $isGr = ($type === 'GR');
        $partyType = $isGr ? 'Supplier' : 'Customer';
        $codeColumn = $isGr ? 'supplier_code' : 'customer_code';
        $priceColumn = $isGr ? 'purchase_price' : 'selling_price';

        $stmt = $this->db->prepare("SELECT id, {$priceColumn} AS default_price FROM {$this->table} WHERE product_code = ? AND company = ? AND status = '0'");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('si', $productCode, $companyId);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$product) {
            return ['unitPrice' => 0, 'source' => null];
        }

        if (!$this->isBlankValue($partyCode)) {
            $stmt = $this->db->prepare("SELECT id FROM {$partyType} WHERE {$codeColumn} = ? AND company = ? AND status = '0'");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('si', $partyCode, $companyId);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $party = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($party) {
                $stmt = $this->db->prepare("SELECT ppt.unit_price FROM Product_Price pp INNER JOIN Product_Price_Tier ppt ON ppt.price_id = pp.id AND ppt.status = 0 WHERE pp.product_id = ? AND pp.party_type = ? AND pp.party_id = ? AND pp.status = 0 AND ? BETWEEN pp.date_from AND pp.date_to ORDER BY pp.date_from DESC, pp.id DESC LIMIT 1");
                if (!$stmt) throw new Exception($this->db->error);
                $stmt->bind_param('isis', $product['id'], $partyType, $party['id'], $date);
                if (!$stmt->execute()) throw new Exception($stmt->error);
                $tier = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($tier && $tier['unit_price'] !== null) {
                    return ['unitPrice' => $tier['unit_price'], 'source' => $isGr ? 'supplier' : 'customer'];
                }
            }
        }

        if ($product['default_price'] !== null) {
            return ['unitPrice' => $product['default_price'], 'source' => 'item'];
        }
        return ['unitPrice' => 0, 'source' => null];
    }

    /**
     * Validate and normalise submitted price entries.
     * Single = one tier with qty 1, Range = qty tiers that must not overlap.
     * Entries of the same customer/supplier must not have overlapping dates.
     */
    private function validatePriceEntries($entries, $companyId) {
        if (!is_array($entries)) {
            throw new InvalidArgumentException('Invalid price entries');
        }

        $validParties = [
            'Customer' => array_column($this->getPriceParties('Customer', 'customer_code', $companyId), 'name', 'id'),
            'Supplier' => array_column($this->getPriceParties('Supplier', 'supplier_code', $companyId), 'name', 'id'),
        ];

        $clean = [];
        $entryCount = ['Customer' => 0, 'Supplier' => 0];
        foreach (array_values($entries) as $entry) {
            $partyType = $entry['partyType'] ?? '';
            // Numbered per Customer / Supplier tab, e.g. "Supplier entry 2"
            $entryNo = isset($entryCount[$partyType]) ? ++$entryCount[$partyType] : 0;
            $label = "{$partyType} entry {$entryNo}";
            $partyId = intval($entry['partyId'] ?? 0);
            $priceType = $entry['priceType'] ?? '';

            if (!isset($validParties[$partyType]) || !isset($validParties[$partyType][$partyId])) {
                throw new InvalidArgumentException("{$label}: please select a valid customer/supplier");
            }
            if ($priceType !== 'Single' && $priceType !== 'Range') {
                throw new InvalidArgumentException("{$label}: please select a type");
            }

            $dateFrom = DateTime::createFromFormat('!d-m-Y', trim($entry['dateFrom'] ?? ''));
            $dateTo = DateTime::createFromFormat('!d-m-Y', trim($entry['dateTo'] ?? ''));
            if (!$dateFrom || !$dateTo) {
                throw new InvalidArgumentException("{$label}: please fill in the from and to date");
            }
            if ($dateFrom > $dateTo) {
                throw new InvalidArgumentException("{$label}: from date cannot be after to date");
            }

            $tiers = is_array($entry['tiers'] ?? null) ? array_values($entry['tiers']) : [];
            if (empty($tiers) || ($priceType === 'Single' && count($tiers) !== 1)) {
                throw new InvalidArgumentException("{$label}: please fill in the prices");
            }

            $cleanTiers = [];
            foreach ($tiers as $tier) {
                if ($priceType === 'Single') {
                    $qtyFrom = 1.0;
                    $qtyTo = 1.0;
                } else {
                    $qtyFrom = $this->toQty($tier['qtyFrom'] ?? '', $label);
                    $qtyTo = $this->toQty($tier['qtyTo'] ?? '', $label);
                    if ($qtyFrom > $qtyTo) {
                        throw new InvalidArgumentException("{$label}: qty from cannot be more than qty to");
                    }
                }

                // Supplier = purchase price, customer = selling price
                $unitPrice = $this->toPrice($tier['unitPrice'] ?? '', "{$label} unit price");
                if ($unitPrice === null) {
                    throw new InvalidArgumentException("{$label}: please fill in the unit price");
                }

                $discountType = ($tier['discountType'] ?? 'Amount') === 'Percent' ? 'Percent' : 'Amount';
                $discount = $this->toPrice($tier['discount'] ?? '', "{$label} discount") ?? 0.0;
                if ($discountType === 'Percent' && $discount > 100) {
                    throw new InvalidArgumentException("{$label}: discount cannot be more than 100%");
                }

                $cleanTiers[] = [
                    'qtyFrom' => $qtyFrom, 'qtyTo' => $qtyTo,
                    'unitPrice' => $unitPrice,
                    'discount' => $discount, 'discountType' => $discountType,
                ];
            }

            usort($cleanTiers, fn($a, $b) => $a['qtyFrom'] <=> $b['qtyFrom']);
            for ($t = 1; $t < count($cleanTiers); $t++) {
                if ($cleanTiers[$t]['qtyFrom'] <= $cleanTiers[$t - 1]['qtyTo']) {
                    throw new InvalidArgumentException("{$label}: qty ranges cannot overlap");
                }
            }

            $clean[] = [
                'partyType' => $partyType, 'partyId' => $partyId, 'partyName' => $validParties[$partyType][$partyId], 'label' => $label,
                'priceType' => $priceType,
                'dateFrom' => $dateFrom->format('Y-m-d'), 'dateTo' => $dateTo->format('Y-m-d'),
                'tiers' => $cleanTiers,
            ];
        }

        // Same customer/supplier cannot have two prices active on the same date
        for ($a = 0; $a < count($clean); $a++) {
            for ($b = $a + 1; $b < count($clean); $b++) {
                if ($clean[$a]['partyType'] === $clean[$b]['partyType'] && $clean[$a]['partyId'] === $clean[$b]['partyId']
                    && $clean[$a]['dateFrom'] <= $clean[$b]['dateTo'] && $clean[$b]['dateFrom'] <= $clean[$a]['dateTo']) {
                    throw new InvalidArgumentException("{$clean[$a]['label']} and {$clean[$b]['label']} for {$clean[$a]['partyName']} have overlapping dates");
                }
            }
        }

        return $clean;
    }

    // Blank = null, otherwise a non-negative number
    private function toPrice($value, $label) {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (!is_numeric($value) || $value < 0) {
            throw new InvalidArgumentException("{$label} must be a positive number");
        }
        return round((float) $value, 2);
    }

    private function toQty($value, $label) {
        $value = trim((string) $value);
        if ($value === '' || !is_numeric($value) || $value <= 0) {
            throw new InvalidArgumentException("{$label}: qty must be more than 0");
        }
        return (float) $value;
    }

    /**
     * Upload items from Excel
     */
    public function upload($data, $companyId) {
        if (empty($data)) {
            throw new Exception('No data provided');
        }

        $errors = [];
        $successCount = 0;
        $company = $companyId;

        foreach ($data as $index => $row) {
            $rowNum = $index + 2;

            $productCode = isset($row['ItemCode']) ? trim($row['ItemCode']) : null;
            $productName = isset($row['ItemName']) ? trim($row['ItemName']) : null;
            $description = isset($row['Description']) ? trim($row['Description']) : null;
            $categoryName = isset($row['Category']) ? trim($row['Category']) : '';
            $uomName = isset($row['UOM']) ? trim($row['UOM']) : '';
            
            // Validate required fields
            if (empty($productCode)) {
                $errors[] = "Row {$rowNum}: Item Code is required";
                continue;
            }
            
            if (empty($productName)) {
                $errors[] = "Row {$rowNum}: Item Name is required";
                continue;
            }
            
            // Lookup category by name
            $category = null;
            if (!empty($categoryName)) {
                $category = searchItemCategoryIdByName($categoryName, $this->db);
                if (empty($category)) {
                    $errors[] = "Row {$rowNum}: Category '{$categoryName}' not found.";
                    continue;
                }
            }
            
            // Lookup UOM by name
            $uom = null;
            if (!empty($uomName)) {
                $uom = searchUnitIdByName($uomName, $this->db);
                if (empty($uom)) {
                    $errors[] = "Row {$rowNum}: UOM '{$uomName}' not found.";
                    continue;
                }
            }
            
            // Check duplicate (scoped to the same company)
            if ($this->isDuplicate('product_code', $productCode, $company)) {
                $errors[] = "Row {$rowNum}: Item Code '{$productCode}' already exists";
                continue;
            }
            
            // Insert
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (company, product_code, name, description, category, uom, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssssss', $company, $productCode, $productName, $description, $category, $uom, $this->username, $this->username);
            
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
     * Get Category/UOM dropdown lists for the Excel upload template
     * @param string|int|null $companyId - when set, restricts the lists to that company; null means all companies
     * @return array ['categories' => [...names], 'units' => [...names]]
     */
    public function getDropdownLists($companyId = null) {
        $companyFilter = '';
        if (!empty($companyId)) {
            $companyId = mysqli_real_escape_string($this->db, $companyId);
            $companyFilter = " AND company IN ({$companyId})";
        }

        $lists = [];

        $result = $this->db->query("SELECT category_name FROM Product_Categories WHERE status = '0'{$companyFilter} ORDER BY category_name ASC");
        $lists['categories'] = [];
        while ($row = $result->fetch_assoc()) {
            $lists['categories'][] = $row['category_name'];
        }

        $result = $this->db->query("SELECT unit FROM Units WHERE status = '0'{$companyFilter} ORDER BY unit ASC");
        $lists['units'] = [];
        while ($row = $result->fetch_assoc()) {
            $lists['units'][] = $row['unit'];
        }

        return $lists;
    }

    public function getListByCompany($companyId) {
        $stmt = $this->db->prepare("SELECT p.id, p.product_code, p.name, p.high, p.low, p.variance, p.description,
            IFNULL(c.is_sales, 'Y') as is_sales,
            IFNULL(c.is_purchase, 'Y') as is_purchase,
            IFNULL(c.is_local, 'Y') as is_local,
            IFNULL(c.is_port, 'Y') as is_port,
            IFNULL(c.is_misc, 'Y') as is_misc
            FROM {$this->table} p LEFT JOIN Product_Categories c ON p.category = c.id
            WHERE p.company = ? AND p.status = '0' ORDER BY p.name");
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
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
    
    /**
     * Auto register product from manual input (weighing)
     * @param string $productName - Product name
     * @param string $entityType - 'Customer' for product, 'Supplier' for raw material
     * @return array - ['product_code' => code, 'name' => name]
     */
    public function autoRegisterProduct($productName, $entityType, $companyId) {
        if ($this->isBlankValue($productName) || $this->isInvalidCompanyId($companyId)) {
            return null;
        }
        $productName = trim($productName);
        $companyId = (int) $companyId;

        // Reuse the company's existing item with this name (items serve as both products and raw materials)
        $stmt = $this->db->prepare("SELECT product_code FROM Product WHERE name=? AND company=? AND status='0'");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('si', $productName, $companyId);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            return ['product_code' => $row['product_code'], 'name' => $productName];
        }

        // Generate product code - P for products, R for raw materials
        $prefix = ($entityType === 'Customer') ? 'P' : 'R';
        $productCode = $prefix . date('ymdHis') . rand(100, 999);
        $isManual = 'Y';
        $status = '0';

        $stmt = $this->db->prepare("INSERT INTO Product (company, product_code, name, is_manual, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('isssss', $companyId, $productCode, $productName, $isManual, $status, $this->username);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

        return ['product_code' => $productCode, 'name' => $productName];
    }
}
?>
