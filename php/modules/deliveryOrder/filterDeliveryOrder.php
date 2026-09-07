<?php
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
  $dateTime = DateTime::createFromFormat('d-m-Y H:i:s', $_POST['fromDate']);
  $fromDateTime = $dateTime->format('Y-m-d H:i:s');
  $searchQuery .= " and transaction_date >= '".$fromDateTime."'";
}

if($_POST['toDate'] != null && $_POST['toDate'] != ''){
  $dateTime = DateTime::createFromFormat('d-m-Y H:i:s', $_POST['toDate']);
  $toDateTime = $dateTime->format('Y-m-d H:i:s');
	$searchQuery .= " and transaction_date <= '".$toDateTime."'";
}

if($_POST['company'] != null && $_POST['company'] != '' && $_POST['company'] != '-'){
	$searchQuery .= " and company_id = '".$_POST['company']."'";
}

if($_POST['customer'] != null && $_POST['customer'] != '' && $_POST['customer'] != '-'){
	$searchQuery .= " and customer_code = '".$_POST['customer']."'";
}

if($_POST['product'] != null && $_POST['product'] != '' && $_POST['product'] != '-'){
	$searchQuery .= " and product_code = '".$_POST['product']."'";
}

if($_POST['plant'] != null && $_POST['plant'] != '' && $_POST['plant'] != '-'){
	$searchQuery .= " and plant_code = '".$_POST['plant']."'";
}

if($_POST['deliveryNo'] != null && $_POST['deliveryNo'] != ''){
  $searchQuery .= " and delivery_no = '".mysqli_real_escape_string($db, $_POST['deliveryNo'])."'";
}

if($_POST['transactionId'] != null && $_POST['transactionId'] != ''){
  $searchQuery .= " and transaction_id = '".mysqli_real_escape_string($db, $_POST['transactionId'])."'";
}

if($searchValue != ''){
  $searchQuery = " and (transaction_id like '%".$searchValue."%' or lorry_plate_no1 like '%".$searchValue."%')";
}

$allQuery = "select * from Weight where is_complete = 'Y' AND  is_cancel <> 'Y' AND transaction_status = 'Sales' group by company_id, plant_code, product_code, customer_code";
// if (($_POST['type'] == 'DO' && !hasModulePermission('Accounting', 'Delivery Order (DO)', ['view_all_plants'])) || ($_POST['type'] == 'GR' && !hasModulePermission('Accounting', 'Goods Received (GR)', ['view_all_plants']))){
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
  $username = implode("', '", $_SESSION["plant"]);
  $allQuery = "select * from Weight where is_complete = 'Y' AND  is_cancel <> 'Y' AND transaction_status = 'Sales' and plant_code IN ('$username') group by company_id, plant_code, product_code, customer_code";
}

$sel = mysqli_query($db, $allQuery); 
$totalRecords = mysqli_num_rows($sel);

## Total number of record with filtering
$filteredQuery = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y' AND transaction_status = 'Sales'".$searchQuery." group by company_id, plant_code, product_code, customer_code";
// if (($_POST['type'] == 'DO' && !hasModulePermission('Accounting', 'Delivery Order (DO)', ['view_all_plants'])) || ($_POST['type'] == 'GR' && !hasModulePermission('Accounting', 'Goods Received (GR)', ['view_all_plants']))){
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
    $username = implode("', '", $_SESSION["plant"]);
    $filteredQuery = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y' AND transaction_status = 'Sales' and plant_code IN ('$username')".$searchQuery." group by company_id, plant_code, product_code, customer_code";
}

$sel = mysqli_query($db, $filteredQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = mysqli_num_rows($sel);

## Fetch records
$empQuery = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y' AND transaction_status = 'Sales'".$searchQuery." group by company_id, plant_code, product_code, customer_code order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
// if (($_POST['type'] == 'DO' && !hasModulePermission('Accounting', 'Delivery Order (DO)', ['view_all_plants'])) || ($_POST['type'] == 'GR' && !hasModulePermission('Accounting', 'Goods Received (GR)', ['view_all_plants']))){
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
  $username = implode("', '", $_SESSION["plant"]);
  $empQuery = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y' AND transaction_status = 'Sales' and plant_code IN ('$username')".$searchQuery." group by company_id, plant_code, product_code, customer_code order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
}

$empRecords = mysqli_query($db, $empQuery); 
$data = array();

while($row = mysqli_fetch_assoc($empRecords)) {
  $data[] = array( 
    "id"=>$row['id'],
    "company"=>searchCompanyById($row['company_id'], $db)['name'],
    "transaction_id"=>$row['transaction_id'],
    "transaction_status"=>$row['transaction_status'],
    "weight_type"=>$row['weight_type'],
    "transaction_date"=>$row['transaction_date'],
    "customer_code"=>$row['customer_code'],
    "customer_name"=>$row['customer_name'],
    "customer"=>($row['transaction_status'] == 'Sales' ? $row['customer_name'] : $row['supplier_name']),
    "product_code"=>($row['transaction_status'] == 'Sales' ? $row['product_code'] : $row['raw_mat_code']), 
    "product_name"=>($row['transaction_status'] == 'Sales' ? $row['product_name'] : $row['raw_mat_name']), 
    "purchase_order"=>$row['purchase_order'],
    "plant_code"=>$row['plant_code'],
    "plant_name"=>$row['plant_name'],
    "delivery_no"=>$row['delivery_no'],
    "order_weight"=>$row['order_weight'],
    "supplier_weight"=>$row['supplier_weight'],
    "tare_weight1_date"=>$row['tare_weight1_date'],
    "created_date"=>$row['created_date'],
    "created_by"=>$row['created_by'],
    "modified_date"=>$row['modified_date'],
    "modified_by"=>$row['modified_by']
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