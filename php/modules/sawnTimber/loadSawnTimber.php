<?php
session_start();
require_once '../../db_connect.php';

$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
$columnIndex = $_POST['order'][0]['column'];
$columnName = $_POST['columns'][$columnIndex]['data'];
$columnSortOrder = $_POST['order'][0]['dir'];
$searchValue = mysqli_real_escape_string($db, $_POST['search']['value']);
$searchQuery = " WHERE h.status='0'";

if (!empty($_POST['fromDate'])) {
    $searchQuery .= " AND h.transaction_date >= '".mysqli_real_escape_string($db, $_POST['fromDate'])." 00:00:00'";
}
if (!empty($_POST['toDate'])) {
    $searchQuery .= " AND h.transaction_date <= '".mysqli_real_escape_string($db, $_POST['toDate'])." 23:59:59'";
}
if (!empty($_POST['transactionId'])) {
    $searchQuery .= " AND h.transaction_id LIKE '%".mysqli_real_escape_string($db, $_POST['transactionId'])."%'";
}
if (!empty($_POST['supplier']) && $_POST['supplier'] != '-') {
    $searchQuery .= " AND h.supplier = '".mysqli_real_escape_string($db, $_POST['supplier'])."'";
}
if (!empty($_POST['lot']) && $_POST['lot'] != '-') {
    $searchQuery .= " AND h.lot LIKE '%".mysqli_real_escape_string($db, $_POST['lot'])."%'";
}
if (!empty($_POST['species']) && $_POST['species'] != '-') {
    $searchQuery .= " AND EXISTS (SELECT 1 FROM Sawn_Timber_Detail d2 WHERE d2.header_id=h.id AND d2.species='".mysqli_real_escape_string($db, $_POST['species'])."')";
}
if (!empty($_POST['remarks'])) {
    $searchQuery .= " AND h.remarks LIKE '%".mysqli_real_escape_string($db, $_POST['remarks'])."%'";
}
if ($searchValue != '') {
    $searchQuery .= " AND (h.transaction_id LIKE '%$searchValue%' OR h.supplier LIKE '%$searchValue%' OR h.lot LIKE '%$searchValue%' OR h.remarks LIKE '%$searchValue%')";
}

$baseSql = "FROM Sawn_Timber_Header h LEFT JOIN Sawn_Timber_Detail d ON h.id=d.header_id $searchQuery GROUP BY h.id";
$totalResult = mysqli_query($db, "SELECT COUNT(*) AS allcount FROM Sawn_Timber_Header WHERE status='0'");
$totalRecords = mysqli_fetch_assoc($totalResult)['allcount'];
$filteredResult = mysqli_query($db, "SELECT COUNT(*) AS allcount FROM (SELECT h.id $baseSql) x");
$totalRecordwithFilter = mysqli_fetch_assoc($filteredResult)['allcount'];

$allowedColumns = array('transaction_id', 'transaction_date', 'supplier', 'lot', 'total_pieces', 'total_tons', 'remarks');
if (!in_array($columnName, $allowedColumns)) {
    $columnName = 'transaction_date';
}

$sql = "SELECT h.id, h.transaction_id, h.transaction_date, h.supplier, h.lot, h.bundle, h.remarks, COALESCE(SUM(d.pieces),0) AS total_pieces, COALESCE(SUM(d.tons),0) AS total_tons $baseSql ORDER BY $columnName $columnSortOrder LIMIT $row,$rowperpage";
$records = mysqli_query($db, $sql);
$data = array();

while ($record = mysqli_fetch_assoc($records)) {
    $data[] = array(
        "id" => $record['id'],
        "transaction_id" => $record['transaction_id'],
        "transaction_date" => $record['transaction_date'],
        "supplier" => $record['supplier'],
        "lot" => $record['lot'],
        "bundle" => $record['bundle'],
        "total_pieces" => $record['total_pieces'],
        "total_tons" => number_format((float)$record['total_tons'], 4, '.', ''),
        "remarks" => $record['remarks']
    );
}

echo json_encode(array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
));
?>
