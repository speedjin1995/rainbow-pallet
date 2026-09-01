<?php
session_start();
## Database configuration
require_once 'db_connect.php';
require_once 'requires/lookup.php';

## Read value
// $draw = $_POST['draw'];
// $row = $_POST['start'];
// $rowperpage = $_POST['length']; // Rows display per page
// $columnIndex = $_POST['order'][0]['column']; // Column index
// $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
// $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
// $searchValue = mysqli_real_escape_string($db,$_POST['search']['value']); // Search value

## Search 
$searchQuery = " ";

if($_POST['fromDateSearch'] != null && $_POST['fromDateSearch'] != ''){
    $fromDate = new DateTime($_POST['fromDateSearch']);
    $fromDateTime = date_format($fromDate,"Y-m-d 00:00:00");
     $searchQuery = " WHERE event_date >= '".$fromDateTime."'";
  }
  
  if($_POST['toDateSearch'] != null && $_POST['toDateSearch'] != ''){
    $toDate = new DateTime($_POST['toDateSearch']);
    $toDateTime = date_format($toDate,"Y-m-d 23:59:59");
      $searchQuery .= " and event_date <= '".$toDateTime."'";
  }

if($_POST['selectedValue'] == "Customer")
{
    if($_POST['customerCode'] != null && $_POST['customerCode'] != '' && $_POST['customerCode'] != '-'){
    $searchQuery .= " and customer_code = '".$_POST['customerCode']."'";
    }
}

if($_POST['selectedValue'] == "Destination")
{
    if($_POST['destinationCode'] != null && $_POST['destinationCode'] != '' && $_POST['destinationCode'] != '-'){
    $searchQuery .= " and destination_code = '".$_POST['destinationCode']."'";
    }
}

if($_POST['selectedValue'] == "Product")
{
    if($_POST['productCode'] != null && $_POST['productCode'] != ''){
    $searchQuery .= " and product_code like '%".$_POST['productCode']."%'";
    }
}

if($_POST['selectedValue'] == "Raw Materials")
{
    if($_POST['rawMatCode'] != null && $_POST['rawMatCode'] != ''){
    $searchQuery .= " and raw_mat_code like '%".$_POST['rawMatCode']."%'";
    }
}

if($_POST['selectedValue'] == "Supplier")
{
    if($_POST['supplierCode'] != null && $_POST['supplierCode'] != '' && $_POST['supplierCode'] != '-'){
    $searchQuery .= " and supplier_code = '".$_POST['supplierCode']."'";
    }
}

if($_POST['selectedValue'] == "Vehicle")
{
    if($_POST['vehicleNo'] != null && $_POST['vehicleNo'] != '' && $_POST['vehicleNo'] != '-'){
    $searchQuery .= " and veh_number = '".$_POST['vehicleNo']."'";
    }
}

if($_POST['selectedValue'] == "Transporter")
{
    if($_POST['transporterCode'] != null && $_POST['transporterCode'] != '' && $_POST['transporterCode'] != '-'){
    $searchQuery .= " and transporter_code = '".$_POST['transporterCode']."'";
    }
}

if($_POST['selectedValue'] == "Unit")
{
    if($_POST['unit'] != null && $_POST['unit'] != '' && $_POST['unit'] != '-'){
    $searchQuery .= " and unit = '".$_POST['unit']."'";
    }
}

if($_POST['selectedValue'] == "User")
{
    if($_POST['userCode'] != null && $_POST['userCode'] != ''){
    $searchQuery .= " and username like '%".$_POST['userCode']."%'";
    }
}

if($_POST['selectedValue'] == "Plant")
{
    if($_POST['plantCode'] != null && $_POST['plantCode'] != ''){
    $searchQuery .= " and plant_code like '%".$_POST['plantCode']."%'";
    }
}

if($_POST['selectedValue'] == "Weight")
{
    if($_POST['weight'] != null && $_POST['weight'] != ''){
    $searchQuery .= " and transaction_id like '%".$_POST['weight']."%'";
    }
}

if($_POST['selectedValue'] == "SO")
{
    if($_POST['custPoNo'] != null && $_POST['custPoNo'] != ''){
    $searchQuery .= " and order_no like '%".$_POST['custPoNo']."%'";
    }
}

if($_POST['selectedValue'] == "PO")
{
    if($_POST['poNo'] != null && $_POST['poNo'] != ''){
    $searchQuery .= " and po_no like '%".$_POST['poNo']."%'";
    }
}

## Total number of records without filtering
// $sel = mysqli_query($db,"select count(*) as allcount from Customer_Log");
// $records = mysqli_fetch_assoc($sel);
// $totalRecords = $records['allcount'];

// ## Total number of record with filtering
// $sel = mysqli_query($db,"select count(*) as allcount from Customer_Log".$searchQuery);
// $records = mysqli_fetch_assoc($sel);
// $totalRecordwithFilter = $records['allcount'];

if($_POST['selectedValue'] == "Customer")
{
    ## Fetch records
    $empQuery = "select * from Customer_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Customer Code"=>$row['customer_code'],
        "Company Reg No"=>$row['company_reg_no'],
        "Company Name"=>$row['name'],
        "Address line 1"=>$row['address_line_1'],
        "Address line 2"=>$row['address_line_2'],
        "Address line 3"=>$row['address_line_3'],
        "Phone No"=>$row['phone_no'],
        "Fax No"=>$row['fax_no'],
        "Action"=> searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Customer Code", "Company Reg No", "Company Name", "Address line 1", "Address line 2", "Address line 3", "Phone No", "Fax No", "Action", "Action By", "Event Date", ];
}

if($_POST['selectedValue'] == "Destination")
{
    ## Fetch records
    $empQuery = "select * from Destination_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Destination Code"=>$row['destination_code'],
        "Destination Name"=>$row['name'],
        "Description"=>$row['description'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Destination Code", "Destination Name", "Description", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Product")
{
    ## Fetch records
    $empQuery = "select * from Product_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Product Code"=>$row['product_code'],
        "Product Name"=>$row['name'],
        "Product Price"=>$row['price'],
        "Description"=>$row['description'],
        "Variance Type"=>$row['variance'],
        "High"=>$row['high'],
        "Low"=>$row['low'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Product Code", "Product Name", "Product Price", "Description", "Variance Type", "High", "Low", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Product")
{
    ## Fetch records
    $empQuery = "select * from Raw_Mat_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Raw Material Code"=>$row['raw_mat_code'],
        "Raw Material Name"=>$row['name'],
        "Raw Material Price"=>$row['price'],
        "Description"=>$row['description'],
        "Variance Type"=>$row['variance'],
        "High"=>$row['high'],
        "Low"=>$row['low'],
        "Type"=>$row['type'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Raw Material Code", "Raw Material Name", "Raw Material Price", "Description", "Variance Type", "High", "Low", "Type", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Supplier")
{
    ## Fetch records
    $empQuery = "select * from Supplier_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Supplier Code"=>$row['supplier_code'],
        "Company Reg No"=>$row['company_reg_no'],
        "Supplier Name"=>$row['name'],
        "Address line 1"=>$row['address_line_1'],
        "Address line 2"=>$row['address_line_2'],
        "Address line 3"=>$row['address_line_3'],
        "Phone No"=>$row['phone_no'],
        "Fax No"=>$row['fax_no'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date']
        );
    }

    $columnNames = ["Supplier Code", "Company Reg No", "Supplier Name", "Address line 1", "Address line 2", "Address line 3", "Phone No", "Fax No", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Vehicle")
{
    ## Fetch records
    $empQuery = "select * from Vehicle_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Vehicle No"=>$row['veh_number'],
        "Vehicle Weight"=>$row['vehicle_weight'],
        "Transporter Code"=>$row['transporter_code'],
        "Transporter Name"=>$row['transporter_name'],
        "Customer Code"=>$row['customer_code'],
        "Customer Name"=>$row['customer_name'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Vehicle No", "Vehicle Weight", "Transporter Code", "Transporter Name", "Customer Code", "Customer Name", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Transporter")
{
    ## Fetch records
    $empQuery = "select * from Transporter_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Transporter Code"=>$row['transporter_code'],
        "Company Reg No"=>$row['company_reg_no'],
        "Transporter Name"=>$row['name'],
        "Address line 1"=>$row['address_line_1'],
        "Address line 2"=>$row['address_line_2'],
        "Address line 3"=>$row['address_line_3'],
        "Phone No"=>$row['phone_no'],
        "Fax No"=>$row['fax_no'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Transporter Code", "Company Reg No", "Transporter Name", "Address line 1", "Address line 2", "Address line 3", "Phone No", "Fax No", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Unit")
{
    ## Fetch records
    $empQuery = "select * from Unit_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Unit"=>$row['unit'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Unit", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "User")
{
    ## Fetch records
    $empQuery = "select * from Users_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Employee Code"=>$row['employee_code'],
        "Username"=>$row['username'],
        "Name"=>$row['name'],
        "Email"=>$row['useremail'],
        "Role"=>$row['user_department'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Employee Code", "Username", "Name", "Email", "Role", "Action", "Action By", "Event Date"];
}


if($_POST['selectedValue'] == "Plant")
{
    ## Fetch records
    $empQuery = "select * from Plant_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Plant Code"=>$row['plant_code'],
        "Plant Name"=>$row['name'],
        "Address line 1"=>$row['address_line_1'],
        "Address line 2"=>$row['address_line_2'],
        "Address line 3"=>$row['address_line_3'],
        "Phone No"=>$row['phone_no'],
        "Fax No"=>$row['fax_no'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Plant Code", "Plant Name", "Address line 1", "Address line 2", "Address line 3", "Phone No", "Fax No", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Weight")
{
    ## Fetch records
    $empQuery = "select * from Weight_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
            "id"=>$row['id'],
            "Transaction Id"=>$row['transaction_id'],
            "Weight Status"=>$row['weight_type'],
            "Customer/Supplier"=>($row['transaction_status'] == 'Sales' ? $row['customer_name'] : $row['supplier_name']),
            "Vehicle"=>$row['lorry_plate_no1'],
            "Product/Raw Material"=>($row['transaction_status'] == 'Sales' ? $row['product_name'] : $row['raw_mat_name']),
            "SO/PO"=>$row['purchase_order'],
            "DO"=>$row['delivery_no'],
            "Gross Incoming"=>$row['gross_weight1'],
            "Incoming Date"=>$row['gross_weight1_date'],
            "Tare Outgoing"=>$row['tare_weight1'],
            "Outgoing Date"=>$row['tare_weight1_date'],
            "Nett Weight"=>$row['nett_weight1'],
            "Action"=>searchActionNameById($row['action_id'], $db),
            "Action By"=>$row['action_by'],
            "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Transaction Id", "Weight Status", "Customer/Supplier", "Vehicle", "Product/Raw Material", "SO/PO", "DO", "Gross Incoming", "Incoming Date", "Tare Outgoing", "Outgoing Date", "Nett Weight", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "SO")
{
    ## Fetch records
    $empQuery = "select * from Sales_Order_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Company Code"=>$row['company_code'],
        "Company Name"=>$row['company_name'],
        "Customer Code"=>$row['customer_code'],
        "Customer Name"=>$row['customer_name'],
        "Site Code"=>$row['site_code'],
        "Site Name"=>$row['site_name'],
        "Sales Representative Code"=>$row['agent_code'],
        "Sales Representative Name"=>$row['agent_name'],
        "Destination Code"=>$row['destination_code'],
        "Destination Name"=>$row['destination_name'],
        "Product Code"=>$row['product_code'],
        "Product Name"=>$row['product_name'],
        "Plant Code"=>$row['plant_code'],
        "Plant Name"=>$row['plant_name'],
        "Transporter Code"=>$row['transporter_code'],
        "Transporter Name"=>$row['transporter_name'],
        "Vehicle No"=>$row['veh_number'],
        "EXQ/Del"=>$row['exquarry_or_delivered'],
        "Customer P/O No"=>$row['order_no'],
        "S/O No"=>$row['so_no'],
        "Order Date"=>$row['order_date'],
        "Order Quantity"=>$row['order_quantity'],
        "Balance"=>$row['balance'],
        "Remarks"=>$row['remarks'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Company Code", "Company Name", "Customer Code", "Customer Name", "Site Code", "Site Name", "Sales Representative Code", "Sales Representative Name", "Destination Code", "Destination Name", "Product Code", "Product Name", "Plant Code", "Plant Name", "Transporter Code", "Transporter Name", "Vehicle No", "EXQ/Del", "Customer P/O No", "S/O No", "Order Date", "Order Quantity", "Balance", "Remarks", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "PO")
{
    ## Fetch records
    $empQuery = "select * from Purchase_Order_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Company Code"=>$row['company_code'],
        "Company Name"=>$row['company_name'],
        "Supplier Code"=>$row['supplier_code'],
        "Supplier Name"=>$row['supplier_name'],
        "Site Code"=>$row['site_code'],
        "Site Name"=>$row['site_name'],
        "Sales Representative Code"=>$row['agent_code'],
        "Sales Representative Name"=>$row['agent_name'],
        "Destination Code"=>$row['destination_code'],
        "Destination Name"=>$row['destination_name'],
        "Raw Material Code"=>$row['raw_mat_code'],
        "Raw Material Name"=>$row['raw_mat_name'],
        "Plant Code"=>$row['plant_code'],
        "Plant Name"=>$row['plant_name'],
        "Transporter Code"=>$row['transporter_code'],
        "Transporter Name"=>$row['transporter_name'],
        "Vehicle No"=>$row['veh_number'],
        "EXQ/Del"=>$row['exquarry_or_delivered'],
        "P/O No"=>$row['po_no'],
        "Order Date"=>$row['order_date'],
        "Order Quantity"=>$row['order_quantity'],
        "Balance"=>$row['balance'],
        "Remarks"=>$row['remarks'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Company Code", "Company Name", "Supplier Code", "Supplier Name", "Site Code", "Site Name", "Sales Representative Code", "Sales Representative Name", "Destination Code", "Destination Name", "Raw Material Code", "Raw Material Name", "Plant Code", "Plant Name", "Transporter Code", "Transporter Name", "Vehicle No", "EXQ/Del", "P/O No", "Order Date", "Order Quantity", "Balance", "Remarks", "Action", "Action By", "Event Date"];
}

## Response
$response = [
    "columnNames" => $columnNames,
    "dataTable" => $data
];

header("Content-Type: application/json");
echo json_encode($response);
?>