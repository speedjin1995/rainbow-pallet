<?php
## Database configuration
session_start();
require_once '../../db_connect.php';

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
  $searchQuery .= " and p.voucher_date >= '".$fromDateTime."'";
}

if($_POST['toDate'] != null && $_POST['toDate'] != ''){
  $dateTime = DateTime::createFromFormat('d-m-Y', $_POST['toDate']);
  $toDateTime = $dateTime->format('Y-m-d 23:59:59');
	$searchQuery .= " and p.voucher_date <= '".$toDateTime."'";
}

if($_POST['weighingType'] != null && $_POST['weighingType'] != '' && $_POST['weighingType'] != '-'){
	// $searchQuery .= " and p.weighing_type = '".$_POST['weighingType']."'";
}

if($_POST['supplier'] != null && $_POST['supplier'] != '' && $_POST['supplier'] != '-'){
	$searchQuery .= " and p.supplier_id = '".$_POST['supplier']."'";
}

if($_POST['invoiceNo'] != null && $_POST['invoiceNo'] != '' && $_POST['invoiceNo'] != '-'){
    $searchQuery .= " and p.invoice_no = '".mysqli_real_escape_string($db, $_POST['invoiceNo'])."'";
}

if($searchValue != ''){
    $searchQuery = " and (p.voucher_no like '%".$searchValue."%')";
}

## Total number of records without filtering
$sel = mysqli_query($db, "select count(*) as allcount from Payment_Voucher p");
$totalRecords = mysqli_num_rows($sel);


## Total number of record with filtering
$sel = mysqli_query($db, "select count(*) as allcount from Payment_Voucher p WHERE p.deleted = 0".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = mysqli_num_rows($sel);

## Fetch records
$empQuery = "select p.*, s.name AS supplier_name from Payment_Voucher p LEFT JOIN Supplier s ON p.supplier_id = s.id WHERE p.deleted = 0".$searchQuery."order by status ASC, ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();

while($row = mysqli_fetch_assoc($empRecords)) {
  $data[] = array( 
    "id"=>$row['id'],
    "weighing_type"=>$row['weighing_type'],
    "supplier"=>$row['supplier_name'] ?? '',
    "invoice_no"=>$row['invoice_no'],
    "voucher_date"=>($row['voucher_date'] != null ? date('d-m-Y', strtotime($row['voucher_date'])) : ''),
    "voucher_no"=>$row['voucher_no'] ?? '',
    "outstanding_amount"=>(!empty($row['outstanding_amount']) ? number_format(floatval($row['outstanding_amount']), 2) : ''),
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