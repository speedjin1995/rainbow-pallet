<?php
session_start();
## Database configuration
require_once '../../db_connect.php';

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
$columnIndex = $_POST['order'][0]['column'];
$columnName = $_POST['columns'][$columnIndex]['data'];
$columnSortOrder = $_POST['order'][0]['dir'];
$searchValue = mysqli_real_escape_string($db, $_POST['search']['value']);

## Search
$searchQuery = " ";
if ($searchValue != '') {
    $searchQuery = " and (name like '%".$searchValue."%' or description like '%".$searchValue."%' or destination_code like '%".$searchValue."%')";
}

## Total number of records without filtering
$sel = mysqli_query($db, "select count(*) as allcount from Destination");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($db, "select count(*) as allcount from Destination WHERE status IN (0)".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select * from Destination WHERE status IN (0)".$searchQuery."order by status ASC, ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {
    $data[] = array(
        "id"               => $row['id'],
        "destination_code" => $row['destination_code'],
        "name"             => $row['name'],
        "description"      => $row['description'],
        "status"           => $row['status']
    );
}

## Response
$response = array(
    "draw"                 => intval($draw),
    "iTotalRecords"        => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData"               => $data
);

echo json_encode($response);
?>
