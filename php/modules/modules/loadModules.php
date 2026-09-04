<?php
session_start();
require_once '../../db_connect.php';

$search  = $_POST['search']['value'] ?? '';
$start   = (int)($_POST['start'] ?? 0);
$length  = (int)($_POST['length'] ?? 10);
$orderCol = (int)($_POST['order'][0]['column'] ?? 0);
$orderDir = $_POST['order'][0]['dir'] === 'desc' ? 'DESC' : 'ASC';

$cols = ['id', 'name', 'category', 'id'];
$orderBy = $cols[$orderCol] ?? 'id';

$where = '';
$params = [];
$types  = '';

if ($search !== '') {
    $where = "WHERE name LIKE ? OR category LIKE ?";
    $like = "%$search%";
    $params = [$like, $like];
    $types  = 'ss';
}

$totalResult   = $db->query("SELECT COUNT(*) FROM modules")->fetch_row()[0];
$filteredResult = $db->query("SELECT COUNT(*) FROM modules $where" . ($types ? '' : ''), ...($types ? [] : []))->fetch_row()[0];

// filtered count with params
if ($types) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM modules $where");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $filteredResult = $stmt->get_result()->fetch_row()[0];
    $stmt->close();
}

$sql = "SELECT id, name, category FROM modules $where ORDER BY $orderBy $orderDir LIMIT ?, ?";
$allTypes = $types . 'ii';
$allParams = array_merge($params, [$start, $length]);

$stmt = $db->prepare($sql);
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    'draw'            => (int)($_POST['draw'] ?? 1),
    'recordsTotal'    => $totalResult,
    'recordsFiltered' => $filteredResult,
    'data'            => $rows,
]);
