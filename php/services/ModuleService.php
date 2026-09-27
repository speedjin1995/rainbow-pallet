<?php
require_once __DIR__ . '/BaseService.php';

class ModuleService extends BaseService {

    protected $table = 'modules';

    public function getAll($post) {
        $languageArray = $_SESSION['languageArray'] ?? [];
        $draw     = $post['draw'] ?? 1;
        $start    = (int)($post['start'] ?? 0);
        $length   = (int)($post['length'] ?? 10);
        $search   = $post['search']['value'] ?? '';
        $colIndex = $post['order'][0]['column'] ?? 0;
        $cols     = ['id', 'name', 'category', 'id'];
        $orderBy  = $cols[$colIndex] ?? 'id';
        $orderDir = ($post['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

        $total = $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetch_row()[0];

        $where = '';
        $params = [];
        $types  = '';
        if ($search !== '') {
            $where = "WHERE name LIKE ? OR category LIKE ?";
            $like  = "%$search%";
            $params = [$like, $like];
            $types  = 'ss';
        }

        $filtered = $total;
        if ($types) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} $where");
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $filtered = $stmt->get_result()->fetch_row()[0];
            $stmt->close();
        }

        $stmt = $this->db->prepare("SELECT id, name, category FROM {$this->table} $where ORDER BY $orderBy $orderDir LIMIT ?, ?");
        $allTypes  = $types . 'ii';
        $allParams = array_merge($params, [$start, $length]);
        $stmt->bind_param($allTypes, ...$allParams);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$row) {
            if (in_array($row['category'], ['Weighing', 'Reports'])) {
                $map = [
                    'Sales'             => $languageArray['dispatch_code']['en'] ?? $row['name'],
                    'Purchase'          => $languageArray['receiving_code']['en'] ?? $row['name'],
                    'Internal Transfer' => $languageArray['internal_transfer_code']['en'] ?? $row['name'],
                    'Miscellaneous'     => $languageArray['miscellaneous_code']['en'] ?? $row['name'],
                    'Port'              => $languageArray['trx_to_port_code']['en'] ?? $row['name'],
                ];
                if (isset($map[$row['name']])) $row['name'] = $map[$row['name']];
            }
        }
        unset($row);

        return [
            'draw'            => intval($draw),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows,
        ];
    }

    public function create($post) {
        $name     = trim($post['moduleName']);
        $category = trim($post['moduleCategory']);

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, category) VALUES (?, ?)");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('ss', $name, $category);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $id = $stmt->insert_id;
        $stmt->close();

        return ['id' => $id];
    }

    public function update($post) {
        $id       = $post['moduleId'];
        $name     = trim($post['moduleName']);
        $category = trim($post['moduleCategory']);

        $stmt = $this->db->prepare("UPDATE {$this->table} SET name=?, category=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sss', $name, $category, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function delete($id, $type = '') {
        if ($type === 'MULTI') {
            $ids = implode(',', array_map('intval', (array)$id));
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id IN ($ids)");
            if (!$stmt) throw new Exception($this->db->error);
        } else {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('s', $id);
        }
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    public function insertDefaults() {
        $defaultModules = [
            ['name' => 'Translation', 'category' => 'Master Data'],
            ['name' => 'Companies', 'category' => 'Master Data'],
            ['name' => 'Customer', 'category' => 'Master Data'],
            ['name' => 'Destination', 'category' => 'Master Data'],
            ['name' => 'Product Category', 'category' => 'Master Data'],
            ['name' => 'Units', 'category' => 'Master Data'],
            ['name' => 'Items', 'category' => 'Master Data'],
            // ['name' => 'Products', 'category' => 'Master Data'],
            // ['name' => 'Raw Material', 'category' => 'Master Data'],
            ['name' => 'Supplier', 'category' => 'Master Data'],
            ['name' => 'Vehicles', 'category' => 'Master Data'],
            ['name' => 'Plant', 'category' => 'Master Data'],
            ['name' => 'Locations', 'category' => 'Master Data'],
            // ['name' => 'PV Items', 'category' => 'Master Data'],
            // ['name' => 'Sawn Timber Species', 'category' => 'Master Data'],
            ['name' => 'Projects', 'category' => 'Master Data'],
            ['name' => 'Modules', 'category' => 'User Management'],
            ['name' => 'User Setup', 'category' => 'User Management'],
            ['name' => 'Permission', 'category' => 'User Management'],
            ['name' => 'Role', 'category' => 'User Management'],
            ['name' => 'Sales', 'category' => 'Reports'],
            ['name' => 'Purchase', 'category' => 'Reports'],
            // ['name' => 'Internal Transfer', 'category' => 'Reports'],
            ['name' => 'Port', 'category' => 'Reports'],
            ['name' => 'Miscellaneous', 'category' => 'Reports'],
            ['name' => 'Audit Log', 'category' => 'Reports'],
            ['name' => 'Api Log', 'category' => 'Reports'],
            ['name' => 'Delivery Order', 'category' => 'Accounting'],
            ['name' => 'Goods Received', 'category' => 'Accounting'],
            // ['name' => 'Payment Voucher', 'category' => 'Accounting'],
            ['name' => 'Sales', 'category' => 'Weighing'],
            ['name' => 'Purchase', 'category' => 'Weighing'],
            // ['name' => 'Internal Transfer', 'category' => 'Weighing'],
            ['name' => 'Port', 'category' => 'Weighing'],
            ['name' => 'Miscellaneous', 'category' => 'Weighing'],
            ['name' => 'Normal Type', 'category' => 'Weighing'],
            ['name' => 'Container Type', 'category' => 'Weighing'],
            ['name' => 'Empty Container Type', 'category' => 'Weighing'],
            ['name' => 'Different Container Type', 'category' => 'Weighing'],
            ['name' => 'Sawn Timber', 'category' => 'Sawn Timber'],
        ];

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, category) SELECT ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM {$this->table} WHERE name = ? AND category = ?)");
        if (!$stmt) throw new Exception($this->db->error);

        $inserted = 0;
        $skipped  = 0;
        foreach ($defaultModules as $m) {
            $stmt->bind_param('ssss', $m['name'], $m['category'], $m['name'], $m['category']);
            $stmt->execute();
            $stmt->affected_rows > 0 ? $inserted++ : $skipped++;
        }
        $stmt->close();

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }
}
?>
