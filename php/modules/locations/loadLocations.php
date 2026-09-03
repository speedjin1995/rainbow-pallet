<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../../db_connect.php';

$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
$columnIndex = $_POST['order'][0]['column'];
$columnName = $_POST['columns'][$columnIndex]['data'];
$columnSortOrder = $_POST['order'][0]['dir'];
$searchValue = mysqli_real_escape_string($db,$_POST['search']['value']);

## SEARCH
$searchQuery = "";
if($searchValue != '') {
  $searchQuery = " AND (
    cust.location_code LIKE '%$searchValue%' OR
    cust.location_name LIKE '%$searchValue%' OR
    pl.name LIKE '%$searchValue%'
  )";
}

## TOTAL without filtering
$sel = mysqli_query($db,"SELECT COUNT(*) as allcount FROM Location WHERE status = '0'");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## TOTAL with filtering
$sel = mysqli_query($db,"
    SELECT COUNT(*) as allcount
    FROM Location cust
    LEFT JOIN Plant pl ON cust.plant_id = pl.id
    WHERE cust.status = '0' $searchQuery
");
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## FETCH DATA
$empQuery = "
  SELECT 
    cust.*,
    pl.name as plant
  FROM Location cust
  LEFT JOIN Plant pl ON cust.plant_id = pl.id
  WHERE cust.status = '0' $searchQuery
";

## Handle ordering
if ($columnName == "plant") {
  $empQuery .= " ORDER BY pl.name $columnSortOrder";
} else {
  $empQuery .= " ORDER BY cust.$columnName $columnSortOrder";
}

$empQuery .= " LIMIT $row,$rowperpage";

$empRecords = mysqli_query($db, $empQuery);

$data = array();
while($row = mysqli_fetch_assoc($empRecords)) {
    $data[] = array(
        "id" => $row['id'],
        "location_code" => $row['location_code'],
        "location_name" => $row['location_name'],
        "weighing_count" => $row['weighing_count'],
        "plant" => $row['plant'] ?? '',
        "plant_id" => $row['plant_id'],
        "status" => $row['status'] == '0' ? 'active' : 'inactive'
    );
}

## RESPONSE
$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);
