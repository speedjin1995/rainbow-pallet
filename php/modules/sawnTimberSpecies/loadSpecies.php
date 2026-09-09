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

$allowedColumns = array('id', 'name', 'created_date');
if (!in_array($columnName, $allowedColumns)) {
    $columnName = 'name';
}

$searchQuery = '';
if ($searchValue != '') {
    $searchQuery = " WHERE name LIKE '%".$searchValue."%'";
}

$sel = mysqli_query($db, "SELECT COUNT(*) AS allcount FROM Sawn_Timber_Species");
$totalRecords = mysqli_fetch_assoc($sel)['allcount'];

$sel = mysqli_query($db, "SELECT COUNT(*) AS allcount FROM Sawn_Timber_Species".$searchQuery);
$totalRecordwithFilter = mysqli_fetch_assoc($sel)['allcount'];

$query = "SELECT * FROM Sawn_Timber_Species".$searchQuery." ORDER BY ".$columnName." ".$columnSortOrder." LIMIT ".$row.",".$rowperpage;
$records = mysqli_query($db, $query);
$data = array();

while ($record = mysqli_fetch_assoc($records)) {
    $data[] = array(
        "id" => $record['id'],
        "name" => $record['name'],
        "created_date" => $record['created_date']
    );
}

echo json_encode(array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
));
?>
