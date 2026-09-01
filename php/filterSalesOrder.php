<?php
session_start();
## Database configuration
require_once 'db_connect.php';

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
  $searchQuery = " and order_date >= '".$fromDateTime."'";
}

if($_POST['toDate'] != null && $_POST['toDate'] != ''){
  $dateTime = DateTime::createFromFormat('d-m-Y', $_POST['toDate']);
  $toDateTime = $dateTime->format('Y-m-d 23:59:59');
	$searchQuery .= " and order_date <= '".$toDateTime."'";
}

if($_POST['status'] != null && $_POST['status'] != '' && $_POST['status'] != '-'){
	$searchQuery .= " and status = '".$_POST['status']."'";
}

if($_POST['company'] != null && $_POST['company'] != '' && $_POST['company'] != '-'){
	$searchQuery .= " and company_code = '".$_POST['company']."'";
}

if($_POST['site'] != null && $_POST['site'] != '' && $_POST['site'] != '-'){
	$searchQuery .= " and site_code = '".$_POST['site']."'";
}

if($_POST['plant'] != null && $_POST['plant'] != '' && $_POST['plant'] != '-'){
	$searchQuery .= " and plant_code = '".$_POST['plant']."'";
}

if($_POST['customer'] != null && $_POST['customer'] != '' && $_POST['customer'] != '-'){
	$searchQuery .= " and customer_code = '".$_POST['customer']."'";
}

if($_POST['product'] != null && $_POST['product'] != '' && $_POST['product'] != '-'){
	$searchQuery .= " and product_code = '".$_POST['product']."'";
}

if($searchValue != ''){
  $searchQuery = " and (
    company_code like '%".$searchValue."%' or 
    company_name like '%".$searchValue."%' or 
    customer_code like '%".$searchValue."%' or 
    customer_name like '%".$searchValue."%' or 
    plant_code like '%".$searchValue."%' or 
    plant_name like '%".$searchValue."%' or 
    product_code like '%".$searchValue."%' or 
    product_name like '%".$searchValue."%' or 
    order_no like '%".$searchValue."%' or 
    so_no like '%".$searchValue."%' or
    order_date like '%".$searchValue."%' or 
    exquarry_or_delivered like '%".$searchValue."%' or 
    modified_date like '%".$searchValue."%'
  )";
}

$allQuery = "select count(*) as allcount from Sales_Order where deleted = '0'";

$sel = mysqli_query($db, $allQuery); 
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$filteredQuery = "select count(*) as allcount from Sales_Order where deleted = '0'".$searchQuery;
$sel = mysqli_query($db, $filteredQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select * from Sales_Order where deleted = '0'".$searchQuery."order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery); 
$data = array();

while($row = mysqli_fetch_assoc($empRecords)) {
  $data[] = array(
    "id"=>$row['id'],
    "company_code"=>$row['company_code'],
    "company_name"=>$row['company_name'],
    "customer_code"=>$row['customer_code'],
    "customer_name"=>$row['customer_name'],
    "plant_code"=>$row['plant_code'],
    "plant_name"=>$row['plant_name'],
    "product_code"=>$row['product_code'],
    "product_name"=>$row['product_name'],
    "order_no"=>$row['order_no'],
    "so_no"=>$row['so_no'],
    "order_date"=>DateTime::createFromFormat('Y-m-d H:i:s', $row["order_date"])->format('d-m-Y'),
    "exquarry_or_delivered"=>$row['exquarry_or_delivered'],
    "balance"=>$row['balance'],
    "status"=>$row['status'],
    "modified_date"=>DateTime::createFromFormat('Y-m-d H:i:s', $row["modified_date"])->format('d-m-Y'),
  );
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecordwithFilter,
  "aaData" => $data,
);

echo json_encode($response);

?>