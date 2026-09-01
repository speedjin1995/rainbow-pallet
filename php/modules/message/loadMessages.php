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

$searchQuery = " ";
if ($searchValue != '') {
    $searchQuery = " WHERE message_key_code like '%".$searchValue."%'";
}

$sel = mysqli_query($db, "select count(*) as allcount from message_resource");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

$sel = mysqli_query($db, "select count(*) as allcount from message_resource".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

$empQuery = "select * from message_resource ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();
$counter = 1;

while ($row = mysqli_fetch_assoc($empRecords)) {
    $data[] = array(
        "counter"          => $counter,
        "id"               => $row['id'],
        "message_key_code" => $row['message_key_code'],
        "en"               => $row['en'],
        "zh"               => $row['zh'],
        "my"               => $row['my'],
        "ne"               => $row['ne']
    );
    $counter++;
}

$response = array(
    "draw"                 => intval($draw),
    "iTotalRecords"        => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData"               => $data
);

echo json_encode($response);
?>
