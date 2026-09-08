<?php
session_start();
// Load the database configuration file 
require_once 'db_connect.php';
 
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
} 
 
// Excel file name for download 
$fileName = "Weight-data_" . date('Y-m-d') . ".xls";

## Search 
$searchQuery = "";
$searchContainerQuery = "";
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
    $username = implode("', '", $_SESSION["plant"]);
    $searchQuery = "and plant_code IN ('$username')";
}

if($_GET['fromDate'] != null && $_GET['fromDate'] != ''){
    $date = DateTime::createFromFormat('d-m-Y', $_GET['fromDate']);
    $formatted_date = $date->format('Y-m-d 00:00:00');
    $searchQuery .= " and Weight.transaction_date >= '".$formatted_date."'";
}

if($_GET['toDate'] != null && $_GET['toDate'] != ''){
    $date = DateTime::createFromFormat('d-m-Y', $_GET['toDate']);
    $formatted_date = $date->format('Y-m-d 23:59:59');
    $searchQuery .= " and Weight.transaction_date <= '".$formatted_date."'";
}

if($_GET['transactionStatus'] != null && $_GET['transactionStatus'] != '' && $_GET['transactionStatus'] != '-'){
    $searchQuery .= " and Weight.transaction_status = '".$_GET['transactionStatus']."'";
}

if($_GET['customer'] != null && $_GET['customer'] != '' && $_GET['customer'] != '-'){
    $searchQuery .= " and Weight.customer_code = '".$_GET['customer']."'";
}

if(isset($_GET['supplier']) && $_GET['supplier'] != null && $_GET['supplier'] != '' && $_GET['supplier'] != '-'){
    $searchQuery .= " and Weight.supplier_code = '".$_GET['supplier']."'";
}

if($_GET['vehicle'] != null && $_GET['vehicle'] != '' && $_GET['vehicle'] != '-'){
    $searchQuery .= " and Weight.lorry_plate_no1 = '".$_GET['vehicle']."'";
}

if($_GET['weighingType'] != null && $_GET['weighingType'] != '' && $_GET['weighingType'] != '-'){
    $searchQuery .= " and Weight.weight_type like '%".$_GET['weighingType']."%'";
}

if($_GET['product'] != null && $_GET['product'] != '' && $_GET['product'] != '-'){
    $searchQuery .= " and Weight.product_code = '".$_GET['product']."'";
}

if(isset($_GET['rawMat']) && $_GET['rawMat'] != null && $_GET['rawMat'] != '' && $_GET['rawMat'] != '-'){
    $searchQuery .= " and Weight.raw_mat_code = '".$_GET['rawMat']."'";
}

if(isset($_GET['plant']) && $_GET['plant'] != null && $_GET['plant'] != '' && $_GET['plant'] != '-'){
    $searchQuery .= " and Weight.plant_code = '".$_GET['plant']."'";
}

if(isset($_GET['status']) && $_GET['status'] != null && $_GET['status'] != '' && $_GET['status'] != '-'){
    if ($_GET['status'] == 'Complete'){
        $searchQuery .= " and Weight.is_complete = 'Y'";
    }elseif ($_GET['status'] == 'Cancelled'){
        $searchQuery .= " and Weight.is_cancel = 'Y'";
    }elseif ($_GET['status'] == 'Pending'){
        $searchQuery .= " and is_complete='N' AND is_cancel='N'";
    }else{
        $searchQuery .= " and Weight.is_complete = 'Y'";
    }
}

if(isset($_GET['reportType']) && $_GET['reportType'] != null && $_GET['reportType'] != '' && $_GET['reportType'] != '-'){
    $reportType = $_GET['reportType'];
}

if($_GET['isMulti'] != null && $_GET['isMulti'] != '' && $_GET['isMulti'] != '-'){
    $isMulti = $_GET['isMulti'];

    if ($isMulti == 'Y'){
        if(is_array($_GET['ids'])){
			$ids = implode(",", $_GET['ids']);
		}else{
			$ids = $_GET['ids'];
		}

        $searchQuery = " and id IN ($ids)";
    }
}

// Column names
if ($reportType == 'SUMMARY'){
    if ($_GET['transactionStatus'] == 'Purchase' || $_GET['transactionStatus'] == 'Local'){
        $fields = array('BIL', 'SUPPLIER', 'DATE IN', 'NO DO', 'NO TICKET', 'NO LORRY', 'EDT', 'FIRST', 'SECOND', 'MC', 'NET', 
            'DATE DN', 'COMPANY', 'REMOVAL PASS NO.', 'NO. LESEN', 'MOISTURE CONTENT', 'NAMA PEGAWAI', 'DRIVER RAINBOW', 'TIME IN/TIME OUT');
    }else{
        $fields = array('BIL', 'CUSTOMER', 'DATE', 'NO DO', 'NO TICKET', 'NO LORRY', 'EDT', 'FIRST (RP)', 'SECOND (RP)', 'MC', 'NET(RP)', 
            'NO DO', 'FIRST (MECO)', 'SECOND (MECO)', 'MC', 'NET (MECO)', 'WEIGHT DIFFERENCE');
    }
}

// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n";

// Fetch records from database
$query = $db->query("SELECT * FROM Weight WHERE Weight.status = '0'".$searchQuery." ORDER BY transaction_date ASC");

if($query->num_rows > 0){ 
    // Output each row of the data 
    $no = 1;
    while($row = $query->fetch_assoc()){
        $lineData = [];

        if ($reportType == 'SUMMARY'){
            if ($_GET['transactionStatus'] == 'Purchase'){
                $lineData = array($no, $row['supplier_name'], $row['transaction_date'], $row['delivery_no'], $row['transaction_id'], $row['lorry_plate_no1'], "", $row['gross_weight1'], $row['tare_weight1'], "", $row['nett_weight1'], 
                "", $row['customer_side_company'], $row['customer_side_removal_pass_no'], $row['customer_side_license_no'], $row['customer_side_moisture_content'], $row['customer_side_officer_name'], $row['customer_side_rainbow_driver'], $row['customer_side_time_in'].'/'.$row['customer_side_time_out']);
            }else{
                $lineData = array($no, $row['customer_name'], $row['transaction_date'], $row['delivery_no'], $row['transaction_id'], $row['lorry_plate_no1'], "", $row['gross_weight1'], $row['tare_weight1'], "", $row['nett_weight1'], 
                $row['cust_side_do_no'], $row['cust_side_first_weight'], $row['cust_side_second_weight'], $row['cust_side_mc'], $row['cust_side_nett_weight'], $row['weight_difference']);
            }
        }

        if (!empty($lineData)) {
            array_walk($lineData, 'filterData'); 
            $excelData .= implode("\t", array_values($lineData)) . "\n"; 
        }

        $no++;
    } 
}else{ 
    $excelData .= 'No records found...'. "\n"; 
} 
 
// Headers for download 
header("Content-Type: application/vnd.ms-excel"); 
header("Content-Disposition: attachment; filename=\"$fileName\""); 
 
// Render excel data 
echo $excelData;
 
exit;
?>
