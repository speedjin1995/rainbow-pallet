<?php
require_once __DIR__ . '/BaseService.php';

class RoleService extends BaseService {

    protected $table = 'roles';

    public function getAll($post) {
        $draw     = $post['draw'] ?? 1;
        $start    = (int)($post['start'] ?? 0);
        $length   = (int)($post['length'] ?? 10);
        $search   = mysqli_real_escape_string($this->db, $post['search']['value'] ?? '');
        $colIndex = $post['order'][0]['column'] ?? 0;
        $cols     = ['id', 'role_code', 'role_name', 'id'];
        $orderBy  = $cols[$colIndex] ?? 'id';
        $orderDir = ($post['order'][0]['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $searchQuery = '';
        if ($search !== '') {
            $searchQuery = " AND (role_code LIKE '%{$search}%' OR role_name LIKE '%{$search}%')";
        }

        $total    = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE deleted IN (0,1) AND role_code <> 'SADMIN'")->fetch_row()[0];
        $filtered = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE deleted IN (0) AND role_code <> 'SADMIN'{$searchQuery}")->fetch_row()[0];

        $rows = $this->db->query("SELECT id, role_code, role_name, deleted FROM {$this->table} WHERE deleted IN (0) AND role_code <> 'SADMIN'{$searchQuery} ORDER BY deleted ASC, {$orderBy} {$orderDir} LIMIT {$start},{$length}")->fetch_all(MYSQLI_ASSOC);

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'id'        => $row['id'],
                'role_code' => $row['role_code'],
                'role_name' => $row['role_name'],
                'deleted'   => $row['deleted'] == '0' ? 'Active' : 'Inactive',
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
        $roleCode = trim($post['roleCode']);
        $roleName = trim($post['roleName']);

        if ($this->isDuplicate($roleCode)) {
            throw new Exception('Role code already exists');
        }

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (role_code, role_name) VALUES (?, ?)");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('ss', $roleCode, $roleName);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $id = $stmt->insert_id;
        $stmt->close();

        return ['id' => $id];
    }

    public function update($post) {
        $id       = $post['roleId'];
        $roleCode = trim($post['roleCode']);
        $roleName = trim($post['roleName']);

        if ($this->isDuplicate($roleCode, $id)) {
            throw new Exception('Role code already exists');
        }

        $stmt = $this->db->prepare("UPDATE {$this->table} SET role_code=?, role_name=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sss', $roleCode, $roleName, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function delete($id, $type = '') {
        $del = '1';
        if ($type === 'MULTI') {
            $ids  = implode(',', array_map('intval', (array)$id));
            $stmt = $this->db->prepare("UPDATE {$this->table} SET deleted=? WHERE id IN ($ids)");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('s', $del);
        } else {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET deleted=? WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('ss', $del, $id);
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

    public function getRolePermissions($roleId) {
        $stmt = $this->db->prepare("SELECT module_id, permission_id FROM role_permissions WHERE role_id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $roleId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function saveRolePermissions($post) {
        $roleId = $post['permRoleId'];

        if (empty($post['permissions'])) {
            throw new Exception('Please select at least 1 permission');
        }

        $del = $this->db->prepare("DELETE FROM role_permissions WHERE role_id=?");
        if (!$del) throw new Exception($this->db->error);
        $del->bind_param('s', $roleId);
        if (!$del->execute()) throw new Exception($del->error);
        $del->close();

        $ins = $this->db->prepare("INSERT INTO role_permissions (role_id, module_id, permission_id) VALUES (?, ?, ?)");
        if (!$ins) throw new Exception($this->db->error);
        foreach ($post['permissions'] as $moduleId => $permIds) {
            foreach ($permIds as $permId) {
                $ins->bind_param('sss', $roleId, $moduleId, $permId);
                if (!$ins->execute()) throw new Exception($ins->error);
            }
        }
        $ins->close();

        // Refresh session permissions for current user
        $roles = $_SESSION['roles'];
        $permSql = "SELECT m.category, m.name AS module_name, p.name AS permission_name
            FROM role_permissions rp
            JOIN roles r ON r.id = rp.role_id
            JOIN modules m ON m.id = rp.module_id
            JOIN permissions p ON p.id = rp.permission_id
            WHERE r.role_code = ?";
        $permStmt = $this->db->prepare($permSql);
        $permStmt->bind_param('s', $roles);
        $permStmt->execute();
        $permResult = $permStmt->get_result();
        $permissions = [];
        while ($pRow = $permResult->fetch_assoc()) {
            $permissions[$pRow['category']][$pRow['module_name']][] = $pRow['permission_name'];
        }
        $permStmt->close();
        $_SESSION['permissions'] = $permissions;
    }

    private function isDuplicate($roleCode, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table} WHERE role_code = ? AND deleted = '0'";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('si', $roleCode, $excludeId);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $roleCode);
        }
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}
?>
