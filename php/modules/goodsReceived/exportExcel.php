<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';
require_once '../../requires/permissions.php';

// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
} 

## Search 
$searchQuery = "";
if($_GET['fromDate'] != null && $_GET['fromDate'] != ''){
    $dateTime = DateTime::createFromFormat('d-m-Y H:i:s', $_GET['fromDate']);
    $fromDateTime = $dateTime->format('Y-m-d H:i:s');
    $searchQuery = " and transaction_date >= '".$fromDateTime."'";
}

if($_GET['toDate'] != null && $_GET['toDate'] != ''){
    $dateTime = DateTime::createFromFormat('d-m-Y H:i:s', $_GET['toDate']);
    $toDateTime = $dateTime->format('Y-m-d H:i:s');
    $searchQuery .= " and transaction_date <= '".$toDateTime."'";
}

if($_GET['company'] != null && $_GET['company'] != '' && $_GET['company'] != '-'){
	$searchQuery .= " and company_id = '".$_GET['company']."'";
}

if($_GET['supplier'] != null && $_GET['supplier'] != '' && $_GET['supplier'] != '-'){
	$searchQuery .= " and supplier_code = '".$_GET['supplier']."'";
}

if($_GET['rawMaterial'] != null && $_GET['rawMaterial'] != '' && $_GET['rawMaterial'] != '-'){
	$searchQuery .= " and raw_mat_code = '".$_GET['rawMaterial']."'";
}

if($_GET['plant'] != null && $_GET['plant'] != '' && $_GET['plant'] != '-'){
	$searchQuery .= " and plant_code = '".$_GET['plant']."'";
}

if($_GET['purchaseOrder'] != null && $_GET['purchaseOrder'] != '' && $_GET['purchaseOrder'] != '-'){
    $searchQuery .= " and purchase_order = '".mysqli_real_escape_string($db, $_GET['purchaseOrder'])."'";
}

if($_GET['transactionId'] != null && $_GET['transactionId'] != '' && $_GET['transactionId'] != '-'){
    $searchQuery .= " and transaction_id = '".mysqli_real_escape_string($db, $_GET['transactionId'])."'";
}

$isMulti = 'N';
if($_GET['isMulti'] != null && $_GET['isMulti'] != '' && $_GET['isMulti'] != '-'){
    $isMulti = $_GET['isMulti'];
}

// Column names 
if (hasModulePermission('Accounting', 'Goods Received', ['include_price'])){
    $fields = array('DocNo', 'DOCREF2', 'DOCDATE', 'DESCRIPTION2', 'CODE', 'COMPANYNAME', 'ITEMCODE', 'DESCRIPTION', 'REMARK2', 'SHIPPER', 'DOCREF1', 'DOCNOEX', 'REMARK1', 'NETT', 'QTY', 'VAR', 'UOM', 'PROJECT', 'LOCATION', 'UNITPRICE', 'Amount', 'Remarks'); 
}else{
    $fields = array('DocNo', 'DOCREF2', 'DOCDATE', 'DESCRIPTION2', 'CODE', 'COMPANYNAME', 'ITEMCODE', 'DESCRIPTION', 'REMARK2', 'SHIPPER', 'DOCREF1', 'DOCNOEX', 'REMARK1', 'NETT', 'QTY', 'VAR', 'UOM', 'PROJECT', 'LOCATION', 'Remarks'); 
}

// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n";

if ($isMulti == 'N'){
    // Excel file name for download 
    $fileName = "GR-data_" . date('Y-m-d') . ".xls";

    // Fetch records from database
    $query = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y'".$searchQuery." group by company_id, plant_code, raw_mat_code, supplier_code order by id asc";
    
    if (!hasModulePermission('Accounting', 'Goods Received', ['view_all_plant'])){
        $username = implode("', '", $_SESSION["plant"]);
        $query = "select * from Weight where is_complete = 'Y' AND  is_cancel <> 'Y' and plant_code IN ('$username')".$searchQuery." group by company_id, plant_code, raw_mat_code, supplier_code order by id asc";
    }

    $do_stmt = $db->query($query);
    if($do_stmt->num_rows > 0){  
        // Output each row of the data 
        while($row = $do_stmt->fetch_assoc()){
            $company = $row['company_id'];
            $rawMatCode = $row['raw_mat_code'];
            $plantCode = $row['plant_code'];
            $supplierCode = $row['supplier_code'];
            $fromDate = DateTime::createFromFormat('d-m-Y H:i:s', $_GET['fromDate']);
            $fromDateTime = $fromDate->format('Y-m-d H:i:s');
            $toDate = DateTime::createFromFormat('d-m-Y H:i:s', $_GET['toDate']);
            $toDateTime = $toDate->format('Y-m-d H:i:s');

            $stmt = $db->prepare("SELECT * FROM Weight WHERE company_id=? AND plant_code = ? AND raw_mat_code = ? AND supplier_code = ? AND transaction_date >= ? AND transaction_date <= ? AND is_complete = 'Y' AND is_cancel <> 'Y' AND status = '0'");
            $stmt->bind_param('ssssss', $company, $plantCode, $rawMatCode, $supplierCode, $fromDateTime, $toDateTime);
            $stmt->execute();
            $doRecords = $stmt->get_result();
            $weighingData = array();

            while($row2 = $doRecords->fetch_assoc()) {
                $lineData = []; // Ensure it starts as an empty array each iteration
                $transactionDate = DateTime::createFromFormat('Y-m-d H:i:s', $row2['transaction_date']);
                $transactionDateTime = $transactionDate->format('Y-m-d');
                $uom = 'MT';
                $finalPlantCode = $row2['plant_code'];
                $qty = (float) $row2['nett_weight1']/1000;
                $unitPrice = $row2['unit_price'] ?? 0;
                $var = (float) $row2['weight_different']/1000;
                $amt = (float) $qty * (float) $unitPrice;

                if (hasModulePermission('Accounting', 'Goods Received', ['include_price'])){
                    $lineData = array('', $row2['destination'], $transactionDateTime, $row2['lorry_plate_no1'], $row2['supplier_code'], $row2['supplier_name'], $row2['raw_mat_code'], $row2['raw_mat_name'], $row2['transaction_id'], $row2['transporter_code'], '', $row2['purchase_order'], $row2['delivery_no'], $qty, $qty, $var, $uom, $finalPlantCode, $finalPlantCode, $unitPrice, $amt, $row2['remarks']);
                }else{
                    $lineData = array('', $row2['destination'], $transactionDateTime, $row2['lorry_plate_no1'], $row2['supplier_code'], $row2['supplier_name'], $row2['raw_mat_code'], $row2['raw_mat_name'], $row2['transaction_id'], $row2['transporter_code'], '', $row2['purchase_order'], $row2['delivery_no'], $qty, $qty, $var, $uom, $finalPlantCode, $finalPlantCode, $row2['remarks']);
                }

                # Added checking to fix duplicated issue
                if (!empty($lineData)) {
                    foreach($lineData as $key => $value) {
                        if($key == 3) { // lorry_plate_no1 is at index 3
                            $lineData[$key] = '="' . $value . '"';
                        } else {
                            // Apply normal filtering to other columns
                            filterData($lineData[$key]); 
                        }
                    }
                    $excelData .= implode("\t", array_values($lineData)) . "\n"; 
                }
            }

            $stmt->close();
        }
    }else{ 
        $excelData .= 'No records found...'. "\n"; 
    } 
}else{
    $id = $_GET['id']; 
    
    // Excel file name for download 
    $fileName = "GR-data_" . date('Y-m-d') . ".xls";

    // Fetch records from database
    $query = "select * from Weight where id IN (". $id .") order by id asc";

    $do_stmt = $db->query($query);
    if($do_stmt->num_rows > 0){  
        // Output each row of the data 
        while($row = $do_stmt->fetch_assoc()){
            $company = $row['company_id'];
            $rawMatCode = $row['raw_mat_code'];
            $plantCode = $row['plant_code'];
            $supplierCode = $row['supplier_code'];
            $fromDate = DateTime::createFromFormat('d-m-Y H:i:s', $_GET['fromDate']);
            $fromDateTime = $fromDate->format('Y-m-d H:i:s');
            $toDate = DateTime::createFromFormat('d-m-Y H:i:s', $_GET['toDate']);
            $toDateTime = $toDate->format('Y-m-d H:i:s');

            $stmt = $db->prepare("SELECT * FROM Weight WHERE company_id=? AND plant_code = ? AND raw_mat_code = ? AND supplier_code = ? AND transaction_date >= ? AND transaction_date <= ? AND is_complete = 'Y' AND is_cancel <> 'Y' AND status = '0'");
            $stmt->bind_param('ssssss', $company, $plantCode, $rawMatCode, $supplierCode, $fromDateTime, $toDateTime);
            $stmt->execute();
            $doRecords = $stmt->get_result();
            $weighingData = array();

            while($row2 = $doRecords->fetch_assoc()) {
                $lineData = []; // Ensure it starts as an empty array each iteration
                $transactionDate = DateTime::createFromFormat('Y-m-d H:i:s', $row2['transaction_date']);
                $transactionDateTime = $transactionDate->format('Y-m-d');
                $uom = 'MT';
                $finalPlantCode = $row2['plant_code'];
                $qty = (float) $row2['nett_weight1']/1000;
                $unitPrice = $row2['unit_price'] ?? 0;
                $var = (float) $row2['weight_different']/1000;
                $amt = (float) $qty * (float) $unitPrice;

                if (hasModulePermission('Accounting', 'Goods Received', ['include_price'])){
                    $lineData = array('', $row2['destination'], $transactionDateTime, $row2['lorry_plate_no1'], $row2['supplier_code'], $row2['supplier_name'], $row2['raw_mat_code'], $row2['raw_mat_name'], $row2['transaction_id'], $row2['transporter_code'], '', $row2['purchase_order'], $row2['delivery_no'], $qty, $uom, $finalPlantCode, $finalPlantCode, $unitPrice, $amt);
                }else{
                    $lineData = array('', $row2['destination'], $transactionDateTime, $row2['lorry_plate_no1'], $row2['supplier_code'], $row2['supplier_name'], $row2['raw_mat_code'], $row2['raw_mat_name'], $row2['transaction_id'], $row2['transporter_code'], '', $row2['purchase_order'], $row2['delivery_no'], $qty, $uom, $finalPlantCode, $finalPlantCode);
                }

                # Added checking to fix duplicated issue
                if (!empty($lineData)) {
                    foreach($lineData as $key => $value) {
                        if($key == 3) { // lorry_plate_no1 is at index 3
                            $lineData[$key] = '="' . $value . '"';
                        } else {
                            // Apply normal filtering to other columns
                            filterData($lineData[$key]); 
                        }
                    }
                    $excelData .= implode("\t", array_values($lineData)) . "\n"; 
                }
            }
            $stmt->close();
        }
    }else{ 
        $excelData .= 'No records found...'. "\n"; 
    }
}
 
// Headers for download 
header("Content-Type: application/vnd.ms-excel"); 
header("Content-Disposition: attachment; filename=\"$fileName\""); 
 
// Render excel data 
echo $excelData;
 
exit;
?>