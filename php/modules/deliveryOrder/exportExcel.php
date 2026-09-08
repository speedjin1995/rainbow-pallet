<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';

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

if($_GET['customer'] != null && $_GET['customer'] != '' && $_GET['customer'] != '-'){
	$searchQuery .= " and customer_code = '".$_GET['customer']."'";
}

if($_GET['product'] != null && $_GET['product'] != '' && $_GET['product'] != '-'){
	$searchQuery .= " and product_code = '".$_GET['product']."'";
}

if($_GET['plant'] != null && $_GET['plant'] != '' && $_GET['plant'] != '-'){
	$searchQuery .= " and plant_code = '".$_GET['plant']."'";
}

if($_GET['deliveryNo'] != null && $_GET['deliveryNo'] != '' && $_GET['deliveryNo'] != '-'){
    $searchQuery .= " and delivery_no = '".mysqli_real_escape_string($db, $_GET['deliveryNo'])."'";
}

if($_GET['transactionId'] != null && $_GET['transactionId'] != '' && $_GET['transactionId'] != '-'){
    $searchQuery .= " and transaction_id = '".mysqli_real_escape_string($db, $_GET['transactionId'])."'";
}

$isMulti = 'N';
if($_GET['isMulti'] != null && $_GET['isMulti'] != '' && $_GET['isMulti'] != '-'){
    $isMulti = $_GET['isMulti'];
}

// Column names 
// if (hasModulePermission('Accounting', 'Delivery Order (DO)', ['include_price'])){
if($_SESSION['roles'] == 'SADMIN' || $_SESSION['roles'] == 'ADMIN'){
    $fields = array('DocNo', 'DOCREF2', 'DOCDATE', 'DESCRIPTION2', 'CODE', 'COMPANYNAME', 'ITEMCODE', 'DESCRIPTION', 'REMARK2', 'SHIPPER', 'DOCREF1', 'DOCNOEX', 'REMARK1', 'QTY', 'UOM', 'PROJECT', 'LOCATION', 'UNITPRICE', 'Amount', 'Remarks'); 
}else{
    $fields = array('DocNo', 'DOCREF2', 'DOCDATE', 'DESCRIPTION2', 'CODE', 'COMPANYNAME', 'ITEMCODE', 'DESCRIPTION', 'REMARK2', 'SHIPPER', 'DOCREF1', 'DOCNOEX', 'REMARK1', 'QTY', 'UOM', 'PROJECT', 'LOCATION', 'Remarks'); 
}

// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n";

if ($isMulti == 'N'){
    // Excel file name for download 
    $fileName = "DO-data_" . date('Y-m-d') . ".xls";

    ## Fetch records
    $query = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y'".$searchQuery." order by plant_code asc, purchase_order asc";

    // if (!hasModulePermission('Accounting', 'Delivery Order (DO)', ['view_all_plant'])){
    if($_SESSION['roles'] == 'SADMIN' || $_SESSION['roles'] == 'ADMIN'){
        $username = implode("', '", $_SESSION["plant"]);
        $query = "select * from Weight where is_complete = 'Y' AND is_cancel <> 'Y' and plant_code IN ('$username')".$searchQuery." order by plant_code asc, purchase_order asc";
    }
    
    $do_stmt = $db->query($query);
    if($do_stmt->num_rows > 0){  
        // Output each row of the data 
        while($row = $do_stmt->fetch_assoc()){
            $lineData = []; // Ensure it starts as an empty array each iteration
            $transactionDate = DateTime::createFromFormat('Y-m-d H:i:s', $row['transaction_date']);
            $transactionDateTime = $transactionDate->format('Y-m-d');
            $uom = 'MT';
            $finalPlantCode = $row['plant_code'];
            $qty = (float) $row['nett_weight1']/1000;
            $unitPrice = $row['unit_price'] ?? 0;
            $amt = (float) $qty * (float) $unitPrice;

            // if (hasModulePermission('Accounting', 'Delivery Order (DO)', ['include_price'])){
            if($_SESSION['roles'] == 'SADMIN' || $_SESSION['roles'] == 'ADMIN'){
                $lineData = array('', $row['transaction_id'], $transactionDateTime, $row['lorry_plate_no1'], $row['customer_code'], $row['customer_name'], $row['product_code'], $row['product_name'], $row['destination'], $row['transporter_code'], '', '', $row['delivery_no'], $qty, $uom, $finalPlantCode, $finalPlantCode, $unitPrice, $amt, $row['remarks']);
            }else{
                $lineData = array($soNo, $row['transaction_id'], $transactionDateTime, $row['lorry_plate_no1'], $row['customer_code'], $row['customer_name'], $row['product_code'], $row['product_name'], $row['destination'], $row['transporter_code'], '', '', $row['delivery_no'], $qty, $uom, $finalPlantCode, $finalPlantCode, $row['remarks']);
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
    }else{ 
        $excelData .= 'No records found...'. "\n"; 
    } 
}else{
    $id = $_GET['id']; 

    // Excel file name for download 
    $fileName = "DO-data_" . date('Y-m-d') . ".xls";

    // Fetch records from database
    $query = "select * from Weight where id IN (". $id .") order by id asc";

    $do_stmt = $db->query($query);
    if($do_stmt->num_rows > 0){  
        // Output each row of the data 
        while($row = $do_stmt->fetch_assoc()){
            $product = $row['product_code'];
            $customer = $row['customer_code'];
            $plant = $row['plant_code'];
            $company = $row['company_id'];
            $fromDate = DateTime::createFromFormat('d-m-Y H:i:s', $_GET['fromDate']);
            $fromDateTime = $fromDate->format('Y-m-d H:i:s');
            $toDate = DateTime::createFromFormat('d-m-Y H:i:s', $_GET['toDate']);
            $toDateTime = $toDate->format('Y-m-d H:i:s');

            $stmt = $db->prepare("SELECT * FROM Weight WHERE plant_code = ? AND product_code = ? AND customer_code = ? AND company_id = ? AND transaction_date >= ? AND transaction_date <= ? AND is_complete = 'Y' AND is_cancel <> 'Y' AND status = '0' AND transaction_status = 'Sales'");
            $stmt->bind_param('ssssss', $plant, $product, $customer, $company, $fromDateTime, $toDateTime);
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
                $amt = (float) $qty * (float) $unitPrice;
                
                // if (hasModulePermission('Accounting', 'Delivery Order (DO)', ['include_price'])){
                if($_SESSION['roles'] == 'SADMIN' || $_SESSION['roles'] == 'ADMIN'){
                    $lineData = array('', $row['transaction_id'], $transactionDateTime, $row['lorry_plate_no1'], $row['customer_code'], $row['customer_name'], $row['product_code'], $row['product_name'], $row['destination'], $row['transporter_code'], '', '', $row['delivery_no'], $qty, $uom, $finalPlantCode, $finalPlantCode, $unitPrice, $amt, $row['remarks']);
                }else{
                    $lineData = array($soNo, $row['transaction_id'], $transactionDateTime, $row['lorry_plate_no1'], $row['customer_code'], $row['customer_name'], $row['product_code'], $row['product_name'], $row['destination'], $row['transporter_code'], '', '', $row['delivery_no'], $qty, $uom, $finalPlantCode, $finalPlantCode, $row['remarks']);
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