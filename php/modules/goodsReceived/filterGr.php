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

if($_POST['supplier'] != null && $_POST['supplier'] != '' && $_POST['supplier'] != '-'){
	$searchQuery .= " and supplier_code = '".$_POST['supplier']."'";
}

if($_POST['rawMaterial'] != null && $_POST['rawMaterial'] != '' && $_POST['rawMaterial'] != '-'){
	$searchQuery .= " and raw_mat_code = '".$_POST['rawMaterial']."'";
}

if($_POST['plant'] != null && $_POST['plant'] != '' && $_POST['plant'] != '-'){
	$searchQuery .= " and plant_code = '".$_POST['plant']."'";
}

if($_POST['purchaseOrder'] != null && $_POST['purchaseOrder'] != '' && $_POST['purchaseOrder'] != '-'){
  $searchQuery .= " and purchase_order = '".mysqli_real_escape_string($db, $_POST['purchaseOrder'])."'";
}

if($_POST['transactionId'] != null && $_POST['transactionId'] != ''){
  $searchQuery .= " and transaction_id = '".mysqli_real_escape_string($db, $_POST['transactionId'])."'";
}

if($searchValue != ''){
  $searchQuery = " and (transaction_id like '%".$searchValue."%' or lorry_plate_no1 like '%".$searchValue."%')";
}

$allQuery = "select * from Weight where is_complete = 'Y' AND  is_cancel <> 'Y' AND transaction_status = 'Purchase' group by company_id, plant_code, raw_mat_code, supplier_code";
// if (($_POST['type'] == 'DO' && !hasModulePermission('Accounting', 'Delivery Order (DO)', ['view_all_plants'])) || ($_POST['type'] == 'GR' && !hasModulePermission('Accounting', 'Goods Received (GR)', ['view_all_plants']))){
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
    $username = implode("', '", $_SESSION["plant"]);
    $allQuery = "select * from Weight where is_complete = 'Y' AND  is_cancel <> 'Y' AND transaction_status = 'Purchase' and plant_code IN ('$username') group by company_id, plant_code, raw_mat_code, supplier_code";
}

$sel = mysqli_query($db, $allQuery); 
$totalRecords = mysqli_num_rows($sel);

## Total number of record with filtering
$filteredQuery = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y' AND transaction_status = 'Purchase'".$searchQuery." group by company_id, plant_code, raw_mat_code, supplier_code";
// if (($_POST['type'] == 'DO' && !hasModulePermission('Accounting', 'Delivery Order (DO)', ['view_all_plants'])) || ($_POST['type'] == 'GR' && !hasModulePermission('Accounting', 'Goods Received (GR)', ['view_all_plants']))){
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
    $username = implode("', '", $_SESSION["plant"]);
    $filteredQuery = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y' and plant_code IN ('$username')".$searchQuery." group by company_id, plant_code, raw_mat_code, supplier_code";
}

$sel = mysqli_query($db, $filteredQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = mysqli_num_rows($sel);

## Fetch records
$empQuery = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y' AND transaction_status = 'Purchase'".$searchQuery." group by company_id, plant_code, raw_mat_code, supplier_code order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
// if (($_POST['type'] == 'DO' && !hasModulePermission('Accounting', 'Delivery Order (DO)', ['view_all_plants'])) || ($_POST['type'] == 'GR' && !hasModulePermission('Accounting', 'Goods Received (GR)', ['view_all_plants']))){
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
    $username = implode("', '", $_SESSION["plant"]);
	$empQuery = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y' AND transaction_status = 'Purchase' and plant_code IN ('$username')".$searchQuery." group by company_id, plant_code, raw_mat_code, supplier_code order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;

}

$empRecords = mysqli_query($db, $empQuery); 
$data = array();

while($row = mysqli_fetch_assoc($empRecords)) {
    // Query same group and count the total received amount
    if ($gr_stmt = $db->prepare("SELECT *  FROM Weight WHERE company_id=? AND plant_code = ? AND transaction_date >= ? AND transaction_date <= ? AND is_complete = 'Y' AND is_cancel <> 'Y' AND status = '0' AND transaction_status = 'Purchase' AND raw_mat_code = ? AND supplier_code = ?")){
        $gr_stmt->bind_param('ssssss', $row['company_id'], $row['plant_code'], $fromDateTime, $toDateTime, $row['raw_mat_code'], $row['supplier_code']);
        $gr_stmt->execute();
        $grRecords = $gr_stmt->get_result();

        $totalFinalWeight = 0;
        while ($grRow = $grRecords->fetch_assoc()) {
            $totalFinalWeight += floatval($grRow['final_weight']);
        }
    }

    $data[] = array( 
        "id"=>$row['id'],
        "company"=>searchCompanyById($row['company_id'], $db)['name'],
        "transaction_id"=>$row['transaction_id'],
        "transaction_status"=>$row['transaction_status'],
        "weight_type"=>$row['weight_type'],
        "transaction_date"=>$row['transaction_date'],
        "customer_code"=>$row['customer_code'],
        "customer_name"=>$row['customer_name'],
        "supplier_name"=>$row['supplier_name'],
        "customer"=>($row['transaction_status'] == 'Sales' ? $row['customer_name'] : $row['supplier_name']),
        "product_code"=>($row['transaction_status'] == 'Sales' ? $row['product_code'] : $row['raw_mat_code']), 
        "product_name"=>($row['transaction_status'] == 'Sales' ? $row['product_name'] : $row['raw_mat_name']), 
        "raw_mat_name" => $row['raw_mat_name'],
        "lorry_plate_no1"=>$row['lorry_plate_no1'],
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
        "modified_by"=>$row['modified_by'],
        "total_final_weight" => $totalFinalWeight
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