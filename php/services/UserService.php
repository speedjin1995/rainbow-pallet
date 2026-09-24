<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

class UserService extends BaseService {

    protected $table = 'Users';

    public function getAll($post) {
        $draw = $post['draw'] ?? 1;
        $start = (int)($post['start'] ?? 0);
        $length = (int)($post['length'] ?? 10);
        $search = mysqli_real_escape_string($this->db, $post['search']['value'] ?? '');
        $colIndex = $post['order'][0]['column'] ?? 0;
        $cols = ['id', 'employee_code', 'username', 'name', 'useremail', 'role_name', 'plant_id', 'status'];
        $orderBy = $cols[$colIndex] ?? 'id';
        $orderDir = ($post['order'][0]['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $searchQuery = '';
        if ($search !== '') {
            $searchQuery = " AND (Users.username LIKE '%{$search}%' OR Users.useremail LIKE '%{$search}%' OR roles.role_name LIKE '%{$search}%')";
        }

        $plantFilter = '';
        if (!hasModulePermission('User Management', 'User Setup', ['view_all_plants'])){
            $plantIds = $_SESSION['plant_id'] ?? [];
            if (!empty($plantIds) && is_array($plantIds)) {
                $conditions = array_map(fn($p) => "JSON_CONTAINS(Users.plant_id, '\"$p\"')", $plantIds);
                $plantFilter = ' AND (' . implode(' OR ', $conditions) . ')';
            }
        }

        $totalQuery = "SELECT COUNT(*) FROM {$this->table} WHERE status IN (0)";
        if ($plantFilter) $totalQuery .= $plantFilter;
        $total = $this->db->query($totalQuery)->fetch_row()[0];

        $filteredQuery = "SELECT COUNT(*) FROM {$this->table}, roles WHERE {$this->table}.role = roles.role_code AND {$this->table}.status IN (0){$searchQuery}{$plantFilter}";
        $filtered = $this->db->query($filteredQuery)->fetch_row()[0];

        $dataQuery = "SELECT Users.id, Users.employee_code, Users.username, Users.useremail, Users.name, roles.role_name, Users.plant_id, Users.company_id, Users.status
            FROM {$this->table}, roles
            WHERE Users.role = roles.role_code AND Users.status IN (0) AND Users.role <> 'SADMIN'
            {$searchQuery}{$plantFilter}
            ORDER BY status ASC, {$orderBy} {$orderDir} LIMIT {$start},{$length}";
        $rows = $this->db->query($dataQuery)->fetch_all(MYSQLI_ASSOC);

        $data = [];
        foreach ($rows as $row) {
            $plant = [];
            if ($row['plant_id']) {
                foreach (json_decode($row['plant_id'], true) as $pid) {
                    $plant[] = searchPlantNameById($pid, $this->db);
                }
            }
            $company = [];
            if ($row['company_id']) {
                foreach (json_decode($row['company_id'], true) as $cid) {
                    $company[] = searchCompanyNameById($cid, $this->db);
                }
            }
            $data[] = [
                'id' => $row['id'],
                'employee_code' => $row['employee_code'],
                'username' => $row['username'],
                'name' => $row['name'] ?? '',
                'useremail' => $row['useremail'],
                'role' => $row['role_name'],
                'plant' => $plant,
                'company' => $company,
                'status' => $row['status'],
            ];
        }

        return [
            'draw' => intval($draw),
            'iTotalRecords' => $total,
            'iTotalDisplayRecords' => $filtered,
            'aaData' => $data,
        ];
    }

    public function create($post) {
        $paramCode = !empty($post['employeeCode']) ? trim($post['employeeCode']) : null;
        $paramUsername = trim($post['username']);
        $paramName = trim($post['name'] ?? '');
        $paramEmail = trim($post['useremail']);
        $paramRole = trim($post['roles']);
        $paramPlant = json_encode(!empty($post['plantId']) ? $post['plantId'] : []);
        $paramCompany = json_encode(!empty($post['company']) ? $post['company'] : []);
        $paramPassword = password_hash('123456', PASSWORD_DEFAULT);
        $paramToken = bin2hex(random_bytes(50));

        $this->checkDuplicates($paramCode, $paramUsername);

        $this->db->begin_transaction();

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (employee_code, useremail, username, name, password, token, role, plant_id, company_id, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sssssssssss', $paramCode, $paramEmail, $paramUsername, $paramName, $paramPassword, $paramToken, $paramRole, $paramPlant, $paramCompany, $this->username, $this->username);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $id = $stmt->insert_id;
        $stmt->close();

        $this->db->commit();
        return ['id' => $id];
    }

    public function update($post) {
        $id = $post['id'];
        $paramCode = !empty($post['employeeCode']) ? trim($post['employeeCode']) : null;
        $paramUsername = trim($post['username']);
        $paramName = trim($post['name'] ?? '');
        $paramEmail = trim($post['useremail']);
        $paramRole = trim($post['roles']);
        $paramPlant = json_encode(!empty($post['plantId']) ? $post['plantId'] : []);
        $paramCompany = json_encode(!empty($post['company']) ? $post['company'] : []);

        $this->checkDuplicates($paramCode, $paramUsername, $id);

        $this->db->begin_transaction();

        $stmt = $this->db->prepare("UPDATE {$this->table} SET username=?, name=?, useremail=?, role=?, modified_by=?, plant_id=?, company_id=?, employee_code=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sssssssss', $paramUsername, $paramName, $paramEmail, $paramRole, $this->username, $paramPlant, $paramCompany, $paramCode, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

        $this->db->commit();
    }

    public function delete($id, $type = '') {
        $del = '1';
        if ($type === 'MULTI') {
            $ids = implode(',', array_map('intval', (array)$id));
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=? WHERE id IN ($ids)");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('s', $del);
        } else {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET status=? WHERE id=?");
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param('ss', $del, $id);
        }
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function get($id) {
        $stmt = $this->db->prepare("SELECT id, employee_code, username, name, useremail, role, plant_id, company_id FROM {$this->table} WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row;
    }

    public function upload($data) {
        $errors = [];
        $status = '0';

        foreach ($data as $row) {
            $employeeCode = !empty($row['EmployeeCode']) ? trim($row['EmployeeCode']) : '';
            $username = !empty($row['Username']) ? trim($row['Username']) : '';
            $name = !empty($row['UserName']) ? trim($row['UserName']) : '';
            $userEmail = !empty($row['UserEmail']) ? trim($row['UserEmail']) : '';
            $role = !empty($row['Role']) ? trim($row['Role']) : '';
            $paramPassword = password_hash('123456', PASSWORD_DEFAULT);
            $paramToken = bin2hex(random_bytes(50));

            if ($employeeCode === '') continue;

            $check = $this->db->prepare("SELECT id FROM {$this->table} WHERE employee_code = ? AND status = ?");
            $check->bind_param('ss', $employeeCode, $status);
            $check->execute();
            $exists = $check->get_result()->fetch_assoc();
            $check->close();

            if ($exists) {
                $errors[] = "User: {$employeeCode} already exist in master data.";
                continue;
            }

            $ins = $this->db->prepare("INSERT INTO {$this->table} (employee_code, username, name, useremail, role, password, token, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param('sssssssss', $employeeCode, $username, $name, $userEmail, $role, $paramPassword, $paramToken, $this->username, $this->username);
            $ins->execute();
            $ins->close();
        }

        return ['errors' => $errors, 'successCount' => count($data) - count($errors)];
    }

    public function resetPassword($userId) {
        $paramPassword = password_hash('123456', PASSWORD_DEFAULT);
        $paramToken = bin2hex(random_bytes(50));
        $modifiedDate = date('Y-m-d H:i:s');

        $this->db->begin_transaction();

        $this->db->query("SET @skip_user_trigger = 1");

        $stmt = $this->db->prepare("UPDATE {$this->table} SET password=?, token=?, modified_by=?, modified_date=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('ssssi', $paramPassword, $paramToken, $this->username, $modifiedDate, $userId);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

        $stmt = $this->db->prepare("SELECT employee_code, username, name, useremail, role, plant_id, languages FROM {$this->table} WHERE id=?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $this->db->prepare("INSERT INTO Users_Log (user_id, employee_code, username, name, useremail, role, plant_id, languages, action_id, action_by, event_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 6, ?, ?)");
        $stmt->bind_param('isssssssss', $userId, $user['employee_code'], $user['username'], $user['name'], $user['useremail'], $user['role'], $user['plant_id'], $user['languages'], $this->username, $modifiedDate);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

        $this->db->commit();
    }

    public function changePassword($userId, $oldPassword, $newPassword) {
        $stmt = $this->db->prepare("SELECT password FROM {$this->table} WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) throw new Exception('User not found');
        if (!password_verify($oldPassword, $row['password'])) {
            throw new Exception('Old password is incorrect');
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE {$this->table} SET password=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('ss', $hashedPassword, $userId);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    public function updateProfile($userId, $post) {
        $email = trim($post['userEmail']);
        $language = trim($post['language']);

        $stmt = $this->db->prepare("UPDATE {$this->table} SET useremail=?, languages=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sss', $email, $language, $userId);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

        $_SESSION['language'] = $language;
    }

    private function checkDuplicates($code, $username, $excludeId = null) {
        $conditions = [];
        $params = [];
        $types = '';

        if (!empty($code)) {
            $conditions[] = 'employee_code = ?';
            $params[] = $code;
            $types .= 's';
        }
        if (!empty($username)) {
            $conditions[] = 'username = ?';
            $params[] = $username;
            $types .= 's';
        }
        if (empty($conditions)) return;

        $sql = "SELECT id, employee_code, username FROM {$this->table} WHERE status = 0 AND (" . implode(' OR ', $conditions) . ")";
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
            $types .= 's';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $field = (!empty($code) && $row['employee_code'] === $code) ? 'Employee Code' : 'Username';
            throw new Exception($field . ' already exists');
        }
    }
}
?>
