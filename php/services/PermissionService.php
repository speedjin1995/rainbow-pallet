<?php
require_once __DIR__ . '/BaseService.php';

class PermissionService extends BaseService {

    protected $table = 'permissions';

    public function getAll($post) {
        $languageArray = $_SESSION['languageArray'] ?? [];
        $draw     = $post['draw'] ?? 1;
        $start    = (int)($post['start'] ?? 0);
        $length   = (int)($post['length'] ?? 10);
        $search   = mysqli_real_escape_string($this->db, $post['search']['value'] ?? '');
        $colIndex = $post['order'][0]['column'] ?? 0;
        $cols     = ['id', 'name', 'modules', 'id'];
        $orderBy  = $cols[$colIndex] ?? 'id';
        $orderDir = ($post['order'][0]['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        // Module lookup
        $modResult = $this->db->query("SELECT id, name, category FROM modules");
        $moduleLookup = [];
        while ($mr = $modResult->fetch_assoc()) {
            if (in_array($mr['category'], ['Weighing', 'Reports', 'Master Data'])) {
                $map = [
                    'Sales'            => $languageArray['dispatch_code']['en'] ?? $mr['name'],
                    'Purchase'         => $languageArray['receiving_code']['en'] ?? $mr['name'],
                    'Local'            => $languageArray['internal_transfer_code']['en'] ?? $mr['name'],
                    'Port'             => $languageArray['trx_to_port_code']['en'] ?? $mr['name'],
                    'Misc'             => $languageArray['miscellaneous_code']['en'] ?? $mr['name'],
                    'Product Category' => $languageArray['product_category_code']['en'] ?? $mr['name'],
                ];
                $name = $map[$mr['name']] ?? $mr['name'];
                $moduleLookup[$mr['id']] = $mr['category'] . ' - ' . $name;
            } else {
                $moduleLookup[$mr['id']] = $mr['category'] . ' - ' . $mr['name'];
            }
        }

        $searchQuery = '';
        if ($search !== '') {
            $searchQuery = " AND (name LIKE '%{$search}%' OR modules LIKE '%{$search}%')";
        }

        $total    = $this->db->query("SELECT COUNT(*) FROM {$this->table}")->fetch_row()[0];
        $filtered = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE 1=1{$searchQuery}")->fetch_row()[0];

        $rows = $this->db->query("SELECT * FROM {$this->table} WHERE 1=1{$searchQuery} ORDER BY {$orderBy} {$orderDir} LIMIT {$start},{$length}")->fetch_all(MYSQLI_ASSOC);

        $data = [];
        foreach ($rows as $row) {
            $modulesArr = json_decode($row['modules'], true) ?: ['All'];
            if (count($modulesArr) === 1 && $modulesArr[0] === 'All') {
                $display = 'All';
            } else {
                $names = array_map(fn($mid) => $moduleLookup[$mid] ?? $mid, $modulesArr);
                $display = implode('<br>', $names);
            }
            $data[] = [
                'id'      => $row['id'],
                'name'    => ucwords(str_replace('_', ' ', $row['name'])),
                'modules' => $display,
            ];
        }

        return [
            'draw'                 => intval($draw),
            'iTotalRecords'        => $total,
            'iTotalDisplayRecords' => $filtered,
            'aaData'               => $data,
        ];
    }

    public function create($post) {
        $name       = strtolower(str_replace(' ', '_', trim($post['permissionName'])));
        $modulesJson = $this->buildModulesJson($post);

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name, modules) VALUES (?, ?)");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('ss', $name, $modulesJson);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $id = $stmt->insert_id;
        $stmt->close();

        return ['id' => $id];
    }

    public function update($post) {
        $id         = $post['permissionId'];
        $name       = strtolower(str_replace(' ', '_', trim($post['permissionName'])));
        $modulesJson = $this->buildModulesJson($post);

        $stmt = $this->db->prepare("UPDATE {$this->table} SET name=?, modules=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sss', $name, $modulesJson, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function delete($id, $type = '') {
        if ($type === 'MULTI') {
            $ids  = implode(',', array_map('intval', (array)$id));
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

        if (!$row) return null;
        $row['modules'] = json_decode($row['modules'], true) ?: ['All'];
        return $row;
    }

    public function insertDefaults() {
        // Build module map: "name|category" => id
        $moduleMap = [];
        $result = $this->db->query("SELECT id, name, category FROM modules");
        while ($row = $result->fetch_assoc()) {
            $moduleMap[$row['name'] . '|' . $row['category']] = $row['id'];
        }

        $defaultPermissions = [
            ['name' => 'view', 'modules' => ['All']],
            ['name' => 'create', 'modules' => [
                // ['Payment Voucher', 'Accounting'],
                ['Companies', 'Master Data'], 
                ['Customer', 'Master Data'], 
                ['Destination', 'Master Data'],
                ['Product Category', 'Master Data'], 
                ['Units', 'Master Data'], 
                ['Items', 'Master Data'], 
                // ['Products', 'Master Data'], 
                // ['Raw Material', 'Master Data'], 
                ['Supplier', 'Master Data'],
                ['Vehicles', 'Master Data'], 
                ['Plant', 'Master Data'], 
                ['Locations', 'Master Data'],
                ['Translation', 'Master Data'],
                // ['PV Items', 'Master Data'],
                // ['Sawn Timber Species', 'Master Data'],
                ['Projects', 'Master Data'],
                ['Modules', 'User Management'], 
                ['Permission', 'User Management'], 
                ['Role', 'User Management'], 
                ['User Setup', 'User Management'],
                ['Sales', 'Weighing'], 
                ['Purchase', 'Weighing'], 
                ['Port', 'Weighing'], 
                ['Miscellaneous', 'Weighing'],
                ['Normal Type', 'Weighing'], 
                ['Container Type', 'Weighing'], 
                ['Empty Container Type', 'Weighing'], 
                ['Different Container Type', 'Weighing'],
                ['Sawn Timber', 'Sawn Timber'],
            ]],
            ['name' => 'edit', 'modules' => [
                // ['Payment Voucher', 'Accounting'],
                ['Companies', 'Master Data'], 
                ['Customer', 'Master Data'], 
                ['Destination', 'Master Data'],
                ['Product Category', 'Master Data'], 
                ['Units', 'Master Data'], 
                ['Items', 'Master Data'], 
                // ['Products', 'Master Data'], 
                // ['Raw Material', 'Master Data'], 
                ['Supplier', 'Master Data'],
                ['Vehicles', 'Master Data'], 
                ['Plant', 'Master Data'], 
                ['Locations', 'Master Data'],
                ['Translation', 'Master Data'],
                // ['PV Items', 'Master Data'],
                // ['Sawn Timber Species', 'Master Data'],
                ['Projects', 'Master Data'],
                ['Modules', 'User Management'], 
                ['Permission', 'User Management'], 
                ['Role', 'User Management'], 
                ['User Setup', 'User Management'],
                ['Sales', 'Weighing'], 
                ['Purchase', 'Weighing'], 
                ['Port', 'Weighing'], 
                ['Miscellaneous', 'Weighing'],
                ['Normal Type', 'Weighing'], 
                ['Container Type', 'Weighing'], 
                ['Empty Container Type', 'Weighing'], 
                ['Different Container Type', 'Weighing'],
                ['Sawn Timber', 'Sawn Timber'],
            ]],
            ['name' => 'cancelled', 'modules' => [
                ['Companies', 'Master Data'], 
                ['Customer', 'Master Data'], 
                ['Destination', 'Master Data'],
                ['Product Category', 'Master Data'], 
                ['Units', 'Master Data'], 
                ['Items', 'Master Data'], 
                // ['Products', 'Master Data'], 
                // ['Raw Material', 'Master Data'], 
                ['Supplier', 'Master Data'],
                ['Vehicles', 'Master Data'], 
                ['Plant', 'Master Data'], 
                ['Locations', 'Master Data'],
                ['Translation', 'Master Data'],
                // ['PV Items', 'Master Data'],
                // ['Sawn Timber Species', 'Master Data'],
                ['Projects', 'Master Data'],
                ['Modules', 'User Management'], 
                ['Permission', 'User Management'], 
                ['Role', 'User Management'], 
                ['User Setup', 'User Management'],
                ['Sales', 'Weighing'], 
                ['Purchase', 'Weighing'], 
                ['Port', 'Weighing'], 
                ['Miscellaneous', 'Weighing'],
                ['Normal Type', 'Weighing'], 
                ['Container Type', 'Weighing'], 
                ['Empty Container Type', 'Weighing'], 
                ['Different Container Type', 'Weighing'],
                ['Sawn Timber', 'Sawn Timber'],
            ]],
            ['name' => 'weight_out', 'modules' => [
                ['Sales', 'Weighing'], 
                ['Purchase', 'Weighing'], 
                ['Port', 'Weighing'], 
                ['Miscellaneous', 'Weighing']
            ]],
            ['name' => 'manual_weighing', 'modules' => [
                ['Sales', 'Weighing'], 
                ['Purchase', 'Weighing'], 
                ['Port', 'Weighing'], 
                ['Miscellaneous', 'Weighing']
            ]],
            ['name' => 'manual_date_change', 'modules' => [
                ['Sales', 'Weighing'], 
                ['Purchase', 'Weighing'], 
                ['Port', 'Weighing'], 
                ['Miscellaneous', 'Weighing']
            ]],
            ['name' => 'print', 'modules' => [
                // ['Payment Voucher', 'Accounting'],
                ['Sales', 'Reports'], 
                ['Purchase', 'Reports'], 
                ['Port', 'Reports'], 
                ['Miscellaneous', 'Reports'],
                ['Sales', 'Weighing'], 
                ['Purchase', 'Weighing'], 
                ['Port', 'Weighing'], 
                ['Miscellaneous', 'Weighing']
            ]],
            ['name' => 'view_all_plants', 'modules' => [
                ['Delivery Order', 'Accounting'],
                ['Goods Received', 'Accounting'],
                ['Sales', 'Reports'], 
                ['Purchase', 'Reports'], 
                ['Port', 'Reports'], 
                ['Miscellaneous', 'Reports'],
                ['Sales', 'Weighing'], 
                ['Purchase', 'Weighing'], 
                ['Port', 'Weighing'], 
                ['Miscellaneous', 'Weighing'],
                ['Sawn Timber', 'Sawn Timber'],
                ['User Setup', 'User Management'],
            ]],
            ['name' => 'view_all_companies', 'modules' => [
                ['Customer', 'Master Data'], 
                ['Destination', 'Master Data'],
                ['Product Category', 'Master Data'], 
                ['Units', 'Master Data'], 
                ['Items', 'Master Data'], 
                // ['Products', 'Master Data'], 
                // ['Raw Material', 'Master Data'], 
                ['Supplier', 'Master Data'],
                ['Vehicles', 'Master Data'], 
                ['Plant', 'Master Data'], 
                ['Locations', 'Master Data'],
                ['Translation', 'Master Data'],
                // ['PV Items', 'Master Data'],
                // ['Sawn Timber Species', 'Master Data'],
                ['Projects', 'Master Data'],
                ['User Setup', 'User Management'],
                ['Delivery Order', 'Accounting'],
                ['Goods Received', 'Accounting'],
                ['Sales', 'Reports'], 
                ['Purchase', 'Reports'], 
                ['Port', 'Reports'], 
                ['Miscellaneous', 'Reports'],
                ['Sales', 'Weighing'], 
                ['Purchase', 'Weighing'], 
                ['Port', 'Weighing'], 
                ['Miscellaneous', 'Weighing'],
                ['Sawn Timber', 'Sawn Timber'],
            ]],
            ['name' => 'download_template', 'modules' => [
                ['Companies', 'Master Data'], 
                ['Customer', 'Master Data'], 
                ['Destination', 'Master Data'],
                ['Product Category', 'Master Data'], 
                ['Units', 'Master Data'], 
                ['Items', 'Master Data'], 
                // ['Products', 'Master Data'], 
                // ['Raw Material', 'Master Data'], 
                ['Supplier', 'Master Data'],
                ['Vehicles', 'Master Data'], 
                ['Plant', 'Master Data'], 
                ['Locations', 'Master Data'],
                // ['PV Items', 'Master Data'],
                // ['Sawn Timber Species', 'Master Data'],
                ['Projects', 'Master Data'],
                ['User Setup', 'User Management'],
                // ['Sawn Timber', 'Sawn Timber'],
            ]],
            ['name' => 'upload_excel', 'modules' => [
                ['Companies', 'Master Data'], 
                ['Customer', 'Master Data'], 
                ['Destination', 'Master Data'],
                ['Product Category', 'Master Data'], 
                ['Units', 'Master Data'], 
                ['Items', 'Master Data'], 
                // ['Products', 'Master Data'], 
                // ['Raw Material', 'Master Data'], 
                ['Supplier', 'Master Data'],
                ['Vehicles', 'Master Data'], 
                ['Plant', 'Master Data'], 
                ['Locations', 'Master Data'],
                // ['PV Items', 'Master Data'],
                // ['Sawn Timber Species', 'Master Data'],
                ['Projects', 'Master Data'],
                ['User Setup', 'User Management'],
                // ['Sawn Timber', 'Sawn Timber'],
            ]],
            ['name' => 'export', 'modules' => [
                ['Delivery Order', 'Accounting'], 
                ['Goods Received', 'Accounting'], 
                ['Audit Log', 'Reports'], 
                ['Api Log', 'Reports'],
                ['Sales', 'Reports'], 
                ['Purchase', 'Reports'], 
                ['Port', 'Reports'], 
                ['Miscellaneous', 'Reports'],
                ['Sawn Timber', 'Sawn Timber'],
            ]],
            // ['name' => 'approval', 'modules' => [
            //     ['Payment Voucher', 'Accounting']
            // ]],
            ['name' => 'post_to_sql', 'modules' => [
                // ['Payment Voucher', 'Accounting'], 
                ['Delivery Order', 'Accounting'], 
                ['Goods Received', 'Accounting'], 
            ]],
            ['name' => 'include_price', 'modules' => [
                ['Audit Log', 'Reports'], 
                ['Delivery Order', 'Accounting'],
                ['Goods Received', 'Accounting'],
                // ['Sales', 'Reports'], 
                // ['Purchase', 'Reports'], 
                // ['Port', 'Reports'], 
                // ['Miscellaneous', 'Reports']
            ]],
            ['name' => 'assign_permissions', 'modules' => [
                ['Role', 'User Management']
            ]],
            ['name' => 'reset_password', 'modules' => [
                ['User Setup', 'User Management']
            ]]
        ];

        $inserted = 0; $updated = 0; $skipped = 0;

        foreach ($defaultPermissions as $perm) {
            $newIds = $this->mapModuleIds($perm['modules'], $moduleMap);

            $checkStmt = $this->db->prepare("SELECT id, modules FROM {$this->table} WHERE name = ?");
            $checkStmt->bind_param('s', $perm['name']);
            $checkStmt->execute();
            $existing = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            if ($existing) {
                $existingMods = json_decode($existing['modules'], true) ?: [];
                if ($existingMods === ['All'] || $newIds === ['All']) {
                    $skipped++;
                } else {
                    $merged = array_values(array_unique(array_merge($existingMods, $newIds)));
                    sort($merged);
                    if (count($merged) > count($existingMods)) {
                        $mergedJson = json_encode($merged);
                        $upd = $this->db->prepare("UPDATE {$this->table} SET modules=? WHERE id=?");
                        $upd->bind_param('si', $mergedJson, $existing['id']);
                        $upd->execute();
                        $upd->close();
                        $updated++;
                    } else {
                        $skipped++;
                    }
                }
            } else {
                $modulesJson = json_encode($newIds);
                $ins = $this->db->prepare("INSERT INTO {$this->table} (name, modules) VALUES (?, ?)");
                $ins->bind_param('ss', $perm['name'], $modulesJson);
                $ins->execute();
                $ins->close();
                $inserted++;
            }
        }

        return ['inserted' => $inserted, 'updated' => $updated, 'skipped' => $skipped];
    }

    private function buildModulesJson($post) {
        if (!empty($post['modulesAll'])) return json_encode(['All']);
        if (!empty($post['modules']))   return json_encode(array_values($post['modules']));
        return json_encode(['All']);
    }

    private function mapModuleIds($modules, $moduleMap) {
        if (count($modules) === 1 && $modules[0] === 'All') return ['All'];
        $ids = [];
        foreach ($modules as $mod) {
            $key = $mod[0] . '|' . $mod[1];
            if (isset($moduleMap[$key])) $ids[] = (string)$moduleMap[$key];
        }
        return $ids;
    }
}
?>
