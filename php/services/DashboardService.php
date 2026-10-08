<?php
require_once __DIR__ . '/BaseService.php';

class DashboardService extends BaseService {
    // Weight.transaction_status => dashboard movement
    private $statusMap = [
        'Purchase'   => 'stockIn',
        'Port'       => 'transfer',
        'Sales'      => 'sales',
        'Production' => 'production',
    ];

    // Status filter => Weight.transaction_status (DO = Sales + Production)
    private $statusFilter = [
        'Purchase' => ['Purchase'],
        'Port'     => ['Port'],
        'DO'       => ['Sales', 'Production'],
    ];

    // Purchase keeps the item in raw_mat_*, the others in product_* (both hold Product codes)
    private $itemCode = "CASE WHEN transaction_status = 'Purchase' THEN raw_mat_code ELSE product_code END";
    private $itemName = "CASE WHEN transaction_status = 'Purchase' THEN raw_mat_name ELSE product_name END";

    /**
     * Completed weighings in the filter: Stock In (Purchase), Stock Transfer (Port), DO (Sales + Production).
     * Balance = Stock In - Stock Transfer - DO.
     */
    public function summary($post) {
        $scope = $this->buildScope($post);
        $status = trim((string) ($post['transactionStatus'] ?? ''));
        $statuses = $this->statusFilter[$status] ?? array_keys($this->statusMap);
        $statusIn = implode(',', array_fill(0, count($statuses), '?'));

        $where  = " WHERE status = '0' AND is_complete = 'Y' AND is_cancel <> 'Y' AND transaction_status IN ({$statusIn})" . $scope['sql'];
        $types  = str_repeat('s', count($statuses)) . $scope['types'];
        $values = array_merge($statuses, $scope['values']);

        return [
            'totals'   => $this->getTotals($where, $types, $values),
            'products' => $this->getProducts($where, $types, $values),
            'recent'   => $this->getRecent($where, $types, $values),
        ];
    }

    private function getTotals($where, $types, $values) {
        $totals = [];
        foreach ($this->statusMap as $key) {
            $totals[$key] = ['weight' => 0, 'trips' => 0];
        }

        $rows = $this->fetchAll(
            "SELECT transaction_status, COUNT(*) AS trips, IFNULL(SUM(final_weight), 0) AS weight FROM Weight" . $where . " GROUP BY transaction_status",
            $types, $values
        );
        foreach ($rows as $row) {
            $totals[$this->statusMap[$row['transaction_status']]] = ['weight' => (float) $row['weight'], 'trips' => intval($row['trips'])];
        }

        $totals['deliveryOrder'] = [
            'weight' => $totals['sales']['weight'] + $totals['production']['weight'],
            'trips'  => $totals['sales']['trips'] + $totals['production']['trips'],
        ];
        $totals['balance'] = [
            'weight' => $totals['stockIn']['weight'] - $totals['transfer']['weight'] - $totals['deliveryOrder']['weight'],
        ];

        return $totals;
    }

    // Movement and balance per item, with its category from the item master
    private function getProducts($where, $types, $values) {
        $rows = $this->fetchAll(
            "SELECT t.item_code, IFNULL(p.name, t.item_name) AS item_name, t.stock_in, t.transfer, t.delivery_order, IFNULL(c.category_name, '') AS category_name
            FROM (
                SELECT company_id, {$this->itemCode} AS item_code, MAX({$this->itemName}) AS item_name,
                    SUM(CASE WHEN transaction_status = 'Purchase' THEN final_weight ELSE 0 END) AS stock_in,
                    SUM(CASE WHEN transaction_status = 'Port' THEN final_weight ELSE 0 END) AS transfer,
                    SUM(CASE WHEN transaction_status IN ('Sales', 'Production') THEN final_weight ELSE 0 END) AS delivery_order
                FROM Weight" . $where . "
                GROUP BY company_id, item_code
            ) t
            LEFT JOIN Product p ON p.product_code = t.item_code AND p.company = t.company_id AND p.status = '0'
            LEFT JOIN Product_Categories c ON c.id = p.category
            ORDER BY item_name",
            $types, $values
        );

        $products = [];
        foreach ($rows as $row) {
            $stockIn  = (float) $row['stock_in'];
            $transfer = (float) $row['transfer'];
            $do       = (float) $row['delivery_order'];
            $products[] = [
                'item_code'     => $row['item_code'] ?? '',
                'item_name'     => $row['item_name'] ?? '',
                'category_name' => $row['category_name'],
                'stockIn'       => $stockIn,
                'transfer'      => $transfer,
                'deliveryOrder' => $do,
                'balance'       => $stockIn - $transfer - $do,
            ];
        }
        return $products;
    }

    // Latest weighings in the filter
    private function getRecent($where, $types, $values) {
        $rows = $this->fetchAll(
            "SELECT transaction_id, transaction_status, transaction_date, final_weight, {$this->itemName} AS item_name
            FROM Weight" . $where . " ORDER BY transaction_date DESC, id DESC LIMIT 5",
            $types, $values
        );

        $recent = [];
        foreach ($rows as $row) {
            $recent[] = [
                'transaction_id' => $row['transaction_id'],
                'type'           => $this->statusMap[$row['transaction_status']],
                'item_name'      => $row['item_name'] ?? '',
                'weight'         => (float) $row['final_weight'],
                'date'           => date('d/m/Y - h:i:sa', strtotime($row['transaction_date'])),
            ];
        }
        return $recent;
    }

    /**
     * Company, date range, product, customer / supplier and plant restriction
     */
    private function buildScope($post) {
        $sql    = '';
        $types  = '';
        $values = [];

        // Determine company on the backend - never trust frontend value for restricted users
        if (hasModulePermission('Dashboard', 'Dashboard', ['view_all_companies'])) {
            $requestedCompany = $post['company'] ?? '';
            $companyId = ($requestedCompany !== '' && $requestedCompany !== '-') ? intval($requestedCompany) : 0;
        } else {
            $companyId = intval($_SESSION['company_id'] ?? 0);
        }
        if ($companyId > 0) {
            $sql .= " AND company_id = ?"; $types .= 'i'; $values[] = $companyId;
        }

        $from = $this->toDbDate($post['fromDate'] ?? '', '00:00:00');
        if ($from !== null) {
            $sql .= " AND transaction_date >= ?"; $types .= 's'; $values[] = $from;
        }
        $to = $this->toDbDate($post['toDate'] ?? '', '23:59:59');
        if ($to !== null) {
            $sql .= " AND transaction_date <= ?"; $types .= 's'; $values[] = $to;
        }

        $product = trim((string) ($post['product'] ?? ''));
        if ($product !== '' && $product !== '-') {
            $sql .= " AND ({$this->itemCode}) = ?"; $types .= 's'; $values[] = $product;
        }

        // C|{customer_code} or S|{supplier_code}
        $party = explode('|', trim((string) ($post['customerSupplier'] ?? '')), 2);
        if (count($party) === 2 && $party[1] !== '') {
            if ($party[0] === 'C') {
                $sql .= " AND customer_code = ?"; $types .= 's'; $values[] = $party[1];
            } elseif ($party[0] === 'S') {
                $sql .= " AND supplier_code = ?"; $types .= 's'; $values[] = $party[1];
            }
        }

        $plant = trim((string) ($post['plant'] ?? ''));
        if ($plant === '-') {
            $plant = '';
        }

        if (!hasModulePermission('Dashboard', 'Dashboard', ['view_all_plants'])) {
            $allowed = $this->getAllowedPlantCodes();
            // Restricted users may only pick one of their own plants
            if ($plant !== '' && in_array($plant, $allowed, true)) {
                $allowed = [$plant];
            }
            if (empty($allowed)) {
                $sql .= " AND 1=0";
            } else {
                $sql   .= " AND plant_code IN (" . implode(',', array_fill(0, count($allowed), '?')) . ")";
                $types .= str_repeat('s', count($allowed));
                $values = array_merge($values, $allowed);
            }
        } elseif ($plant !== '') {
            $sql .= " AND plant_code = ?"; $types .= 's'; $values[] = $plant;
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

    // d-m-Y => Y-m-d {time}
    private function toDbDate($value, $time) {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $date = DateTime::createFromFormat('d-m-Y', trim($value));
        return $date ? $date->format('Y-m-d') . ' ' . $time : null;
    }
}
?>
