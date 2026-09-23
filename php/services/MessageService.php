<?php
require_once __DIR__ . '/BaseService.php';

class MessageService extends BaseService {

    protected $table = 'message_resource';

    /**
     * Get all messages (for DataTables)
     */
    public function getAll($post) {
        $draw = $post['draw'] ?? 1;
        $start = $post['start'] ?? 0;
        $length = $post['length'] ?? 10;
        $searchValue = isset($post['search']['value']) ? mysqli_real_escape_string($this->db, $post['search']['value']) : '';

        $columnIndex = $post['order'][0]['column'] ?? 0;
        $columnName = $post['columns'][$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $post['order'][0]['dir'] ?? 'asc';

        // Total records
        $totalResult = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $totalRecords = $totalResult->fetch_assoc()['total'];

        // Search filter
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = " WHERE message_key_code LIKE '%{$searchValue}%'";
        }

        // Filtered records
        $filteredResult = $this->db->query("SELECT COUNT(*) as total FROM {$this->table} {$searchQuery}");
        $totalFiltered = $filteredResult->fetch_assoc()['total'];

        // Data
        $dataResult = $this->db->query("SELECT * FROM {$this->table} {$searchQuery} ORDER BY {$columnName} {$columnSortOrder} LIMIT {$start}, {$length}");

        $data = [];
        $counter = 1;
        while ($row = $dataResult->fetch_assoc()) {
            $data[] = [
                'counter'          => $counter++,
                'id'               => $row['id'],
                'message_key_code' => $row['message_key_code'],
                'en'               => $row['en'],
                'zh'               => $row['zh'],
                'my'               => $row['my'],
                'ne'               => $row['ne']
            ];
        }

        return [
            'draw'                 => intval($draw),
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $totalFiltered,
            'aaData'               => $data
        ];
    }

    /**
     * Create new message
     */
    public function create($post) {
        $keyCode     = trim($post['keyCode']);
        $englishDecs = trim($post['englishDecs']);
        $chineseDecs = trim($post['chineseDecs']);
        $malayDecs   = trim($post['malayDecs']);
        $nepaliDecs  = trim($post['nepaliDecs']);

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (message_key_code, en, zh, my, ne) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('sssss', $keyCode, $englishDecs, $chineseDecs, $malayDecs, $nepaliDecs);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $insertId = $stmt->insert_id;
        $stmt->close();

        return ['id' => $insertId];
    }

    /**
     * Update existing message
     */
    public function update($post) {
        $id          = $post['keyId'];
        $keyCode     = trim($post['keyCode']);
        $englishDecs = trim($post['englishDecs']);
        $chineseDecs = trim($post['chineseDecs']);
        $malayDecs   = trim($post['malayDecs']);
        $nepaliDecs  = trim($post['nepaliDecs']);

        $stmt = $this->db->prepare("UPDATE {$this->table} SET message_key_code=?, en=?, zh=?, my=?, ne=? WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('ssssss', $keyCode, $englishDecs, $chineseDecs, $malayDecs, $nepaliDecs, $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();

        return ['id' => $id];
    }

    /**
     * Hard delete message
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) throw new Exception($stmt->error);
        $stmt->close();
    }

    /**
     * Get single message by ID
     */
    public function get($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id=?");
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row;
    }
}
?>
