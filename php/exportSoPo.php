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

## Search 
$searchQuery = "";
if($_GET['fromDate'] != null && $_GET['fromDate'] != ''){
    $date = DateTime::createFromFormat('d-m-Y', $_GET['fromDate']);
    $formatted_date = $date->format('Y-m-d 00:00:00');
    $searchQuery .= " and order_date >= '".$formatted_date."'";
}

if($_GET['toDate'] != null && $_GET['toDate'] != ''){
    $date = DateTime::createFromFormat('d-m-Y', $_GET['toDate']);
    $formatted_date = $date->format('Y-m-d 23:59:59');
    $searchQuery .= " and order_date <= '".$formatted_date."'";
}

if($_GET['status'] != null && $_GET['status'] != '' && $_GET['status'] != '-'){
    $searchQuery .= " and status = '".$_GET['status']."'";
}

if($_GET['company'] != null && $_GET['company'] != '' && $_GET['company'] != '-'){
    $searchQuery .= " and company_code = '".$_GET['company']."'";
}

if($_GET['site'] != null && $_GET['site'] != '' && $_GET['site'] != '-'){
    $searchQuery .= " and site_code = '".$_GET['site']."'";
}

if(isset($_GET['plant']) && $_GET['plant'] != null && $_GET['plant'] != '' && $_GET['plant'] != '-'){
    $searchQuery .= " and plant_code = '".$_GET['plant']."'";
}

if($_GET['customer'] != null && $_GET['customer'] != '' && $_GET['customer'] != '-'){
    if($_GET["type"] == 'Sales'){
        $searchQuery .= " and customer_code = '".$_GET['customer']."'";
    }
    else{
        $searchQuery .= " and supplier_code = '".$_GET['customer']."'";
    }
}

if($_GET['product'] != null && $_GET['product'] != '' && $_GET['product'] != '-'){
    if($_GET["type"] == 'Sales'){
        $searchQuery .= " and product_code = '".$_GET['product']."'";
    }
    else{
        $searchQuery .= " and raw_mat_code = '".$_GET['product']."'";
    }
}

// Excel file name for download 
if($_GET["type"] == 'Sales'){
    $fileName = "SO-data_" . date('Y-m-d') . ".xls";

    // Column names 
    $fields = array('COMPANY CODE', 'COMPANY NAME', 'CUSTOMER CODE', 'CUSTOMER NAME', 'PLANT CODE', 'PLANT NAME', 'PRODUCT CODE', 'PRODUCT NAME', 'CUSTOMER P/O NO', 'S/O NO', 'ORDER DATE', 'EX-QUARRY/DELIVERED', 'BALANCE'); 

    // Display column names as first row 
    $excelData = implode("\t", array_values($fields)) . "\n";

    // Fetch records from database
    $query = $db->query("select * from Sales_Order WHERE deleted = '0'".$searchQuery);

    if($query->num_rows > 0){ 
        // Output each row of the data 
        while($row = $query->fetch_assoc()){ 
            $lineData = []; // Ensure it starts as an empty array each iteration
            $lineData = array($row['company_code'], $row['company_name'], $row['customer_code'], $row['customer_name'], $row['plant_code'], $row['plant_name'], $row['product_code'], $row['product_name'], $row['order_no'], $row['so_no'], $row['order_date'], $row['exquarry_or_delivered'], $row['balance']);

            # Added checking to fix duplicated issue
            if (!empty($lineData)) {
                array_walk($lineData, 'filterData'); 
                $excelData .= implode("\t", array_values($lineData)) . "\n"; 
            }
        } 
    }else{ 
        $excelData .= 'No records found...'. "\n"; 
    } 
}else{
    $fileName = "PO-data_" . date('Y-m-d') . ".xls";

    // Column names 
    $fields = array('COMPANY CODE', 'COMPANY NAME', 'SUPPLIER CODE', 'SUPPLIER NAME', 'PLANT CODE', 'PLANT NAME', 'RAW MATERIAL CODE', 'RAW MATERIAL NAME', 'P/O NO', 'ORDER DATE', 'EX-QUARRY/DELIVERED', 'BALANCE'); 

    // Display column names as first row 
    $excelData = implode("\t", array_values($fields)) . "\n";

    // Fetch records from database
    $query = $db->query("select * from Purchase_Order WHERE deleted = '0'".$searchQuery);

    if($query->num_rows > 0){ 
        // Output each row of the data 
        while($row = $query->fetch_assoc()){ 
            $lineData = []; // Ensure it starts as an empty array each iteration
            $lineData = array($row['company_code'], $row['company_name'], $row['supplier_code'], $row['supplier_name'], $row['plant_code'], $row['plant_name'], $row['raw_mat_code'], $row['raw_mat_name'], $row['po_no'], $row['order_date'], $row['exquarry_or_delivered'], $row['balance']);

            # Added checking to fix duplicated issue
            if (!empty($lineData)) {
                array_walk($lineData, 'filterData'); 
                $excelData .= implode("\t", array_values($lineData)) . "\n"; 
            }
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