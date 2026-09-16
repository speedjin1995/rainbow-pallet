<?php
// Enable error reporting for all types of errors
error_reporting(E_ALL);

// Tell PHP to display the errors on the screen
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Rest of your code goes here

## Database configuration
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';
// require_once '../../requires/permissions.php';

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = mysqli_real_escape_string($db,$_POST['search']['value']); // Search value

## Search 
$searchQuery = "";

if($_POST['fromDate'] != null && $_POST['fromDate'] != ''){
  $dateTime = DateTime::createFromFormat('d-m-Y', $_POST['fromDate']);
  $fromDateTime = $dateTime->format('Y-m-d 00:00:00');
  $searchQuery .= " and h.record_date >= '".$fromDateTime."'";
}

if($_POST['toDate'] != null && $_POST['toDate'] != ''){
  $dateTime = DateTime::createFromFormat('d-m-Y', $_POST['toDate']);
  $toDateTime = $dateTime->format('Y-m-d 23:59:59');
  $searchQuery .= " and h.record_date <= '".$toDateTime."'";
}

if($_POST['company'] != null && $_POST['company'] != '' && $_POST['company'] != '-'){
	$searchQuery .= " and h.company_id = '".$_POST['company']."'";
}

if($_POST['plant'] != null && $_POST['plant'] != '' && $_POST['plant'] != '-'){
	$searchQuery .= " and h.plant_id = '".$_POST['plant']."'";
}

if($_POST['transactionId'] != null && $_POST['transactionId'] != ''){
    $searchQuery .= " AND w.transaction_id LIKE '%".mysqli_real_escape_string($db, $_POST['transactionId'])."%'";
}

if($searchValue != ''){
  $searchQuery = " and (w.transaction_id like '%".$searchValue."%' or w.lorry_plate_no1 like '%".$searchValue."%')";
}

## Total number of records without filtering
$sel = mysqli_query($db, "select count(*) as allcount from Sawn_Timber_Header h WHERE h.status = 0");
$totalRecords = mysqli_num_rows($sel);

## Total number of record with filtering
$sel = mysqli_query($db, "select count(*) as allcount from (SELECT h.id FROM Sawn_Timber_Header h LEFT JOIN Sawn_Timber_Detail d ON h.id=d.header_id LEFT JOIN Weight w ON h.weight_id=w.id WHERE h.status = 0".$searchQuery.") x");
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = mysqli_num_rows($sel);

## Fetch records
$empQuery = "SELECT h.id, h.company_id, h.plant_id, h.weight_id, w.transaction_id, h.record_date, w.supplier_name, h.remarks, h.status, COALESCE(SUM(d.pieces),0) AS total_pieces, COALESCE(SUM(d.tons),0) AS total_tons FROM Sawn_Timber_Header h LEFT JOIN Sawn_Timber_Detail d ON h.id=d.header_id LEFT JOIN Weight w ON h.weight_id=w.id WHERE h.status = 0".$searchQuery." group by h.id order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();

while($row = mysqli_fetch_assoc($empRecords)) {
  $data[] = array( 
    "id" => $row['id'],
    "transaction_id" => $row['transaction_id'],
    "transaction_date" => $row['record_date'],
    "company" => searchCompanyById($row['company_id'], $db)['name'],
    "plant" => searchPlantNameById($row['plant_id'], $db),
    "supplier" => $row['supplier_name'] ?? '',
    "total_pieces" => $row['total_pieces'],
    "total_tons" => number_format((float)$row['total_tons'], 4, '.', ''),
    "remarks" => $row['remarks'],
    "status" => $row['status']
  );
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecordwithFilter,
  "aaData" => $data,
  "sql" => $empQuery
);

echo json_encode($response);

?>