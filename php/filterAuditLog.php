<?php
session_start();
## Database configuration
require_once 'db_connect.php';
require_once 'requires/lookup.php';

## Search 
$searchQuery = " ";

if($_POST['fromDateSearch'] != null && $_POST['fromDateSearch'] != ''){
    $fromDate = DateTime::createFromFormat('d-m-Y', $_POST['fromDateSearch']);
    $fromDateTime = $fromDate->format('Y-m-d 00:00:00');
    $searchQuery = " WHERE event_date >= '".$fromDateTime."'";
}
  
if($_POST['toDateSearch'] != null && $_POST['toDateSearch'] != ''){
    $toDate = DateTime::createFromFormat('d-m-Y', $_POST['toDateSearch']);
    $toDateTime = $toDate->format('Y-m-d 23:59:59');
    $searchQuery .= " and event_date <= '".$toDateTime."'";
}

if($_POST['selectedValue'] == "Company")
{
    if(isset($_POST['companyCode']) && $_POST['companyCode'] != '' && $_POST['companyCode'] != '-'){
        $searchQuery .= " and company_code = '".$_POST['companyCode']."'";
    }
}

if($_POST['selectedValue'] == "Customer")
{
    if(isset($_POST['customerCode']) && $_POST['customerCode'] != '' && $_POST['customerCode'] != '-'){
    $searchQuery .= " and customer_code = '".$_POST['customerCode']."'";
    }
}

if($_POST['selectedValue'] == "Destination")
{
    if(isset($_POST['destinationCode']) && $_POST['destinationCode'] != '' && $_POST['destinationCode'] != '-'){
    $searchQuery .= " and destination_code = '".$_POST['destinationCode']."'";
    }
}

if($_POST['selectedValue'] == "Product")
{
    if(isset($_POST['productCode']) && $_POST['productCode'] != ''){
    $searchQuery .= " and product_code like '%".$_POST['productCode']."%'";
    }
}

if($_POST['selectedValue'] == "Raw Materials")
{
    if(isset($_POST['rawMatCode']) && $_POST['rawMatCode'] != ''){
    $searchQuery .= " and raw_mat_code like '%".$_POST['rawMatCode']."%'";
    }
}

if($_POST['selectedValue'] == "Supplier")
{
    if(isset($_POST['supplierCode']) && $_POST['supplierCode'] != '' && $_POST['supplierCode'] != '-'){
    $searchQuery .= " and supplier_code = '".$_POST['supplierCode']."'";
    }
}

if($_POST['selectedValue'] == "Vehicle")
{
    if(isset($_POST['vehicleNo']) && $_POST['vehicleNo'] != '' && $_POST['vehicleNo'] != '-'){
    $searchQuery .= " and veh_number = '".$_POST['vehicleNo']."'";
    }
}

if($_POST['selectedValue'] == "Transporter")
{
    if(isset($_POST['transporterCode']) && $_POST['transporterCode'] != '' && $_POST['transporterCode'] != '-'){
    $searchQuery .= " and transporter_code = '".$_POST['transporterCode']."'";
    }
}

if($_POST['selectedValue'] == "Unit")
{
    if(isset($_POST['unit']) && $_POST['unit'] != '' && $_POST['unit'] != '-'){
    $searchQuery .= " and unit = '".$_POST['unit']."'";
    }
}

if($_POST['selectedValue'] == "Product Category")
{
    if(isset($_POST['productCategory']) && $_POST['productCategory'] != '' && $_POST['productCategory'] != '-'){
    $searchQuery .= " and category_name like '%".$_POST['productCategory']."%'";
    }
}

if($_POST['selectedValue'] == "Location")
{
    if(isset($_POST['locationCode']) && $_POST['locationCode'] != '' && $_POST['locationCode'] != '-'){
    $searchQuery .= " and location_code = '".$_POST['locationCode']."'";
    }
}

if($_POST['selectedValue'] == "Project")
{
    if(isset($_POST['projectCode']) && $_POST['projectCode'] != '' && $_POST['projectCode'] != '-'){
    $searchQuery .= " and project_code like '%".$_POST['projectCode']."%'";
    }
}

if($_POST['selectedValue'] == "User")
{
    if(isset($_POST['userCode']) && $_POST['userCode'] != ''){
    $searchQuery .= " and username like '%".$_POST['userCode']."%'";
    }
}

if($_POST['selectedValue'] == "Plant")
{
    if(isset($_POST['plantCode']) && $_POST['plantCode'] != ''){
    $searchQuery .= " and plant_code like '%".$_POST['plantCode']."%'";
    }
}

if($_POST['selectedValue'] == "Weight")
{
    if(isset($_POST['weight']) && $_POST['weight'] != ''){
    $searchQuery .= " and transaction_id like '%".$_POST['weight']."%'";
    }
}

if($_POST['selectedValue'] == "SO")
{
    if(isset($_POST['custPoNo']) && $_POST['custPoNo'] != ''){
    $searchQuery .= " and order_no like '%".$_POST['custPoNo']."%'";
    }
}

if($_POST['selectedValue'] == "PO")
{
    if(isset($_POST['poNo']) && $_POST['poNo'] != ''){
    $searchQuery .= " and po_no like '%".$_POST['poNo']."%'";
    }
}

// Querying
if($_POST['selectedValue'] == "Company")
{
    ## Fetch records
    $empQuery = "select * from Company_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Company Code"=>$row['company_code'] ?? '',
        "Company Reg No"=>$row['company_reg_no'] ?? '',
        "New Reg No"=>$row['new_reg_no'] ?? '',
        "Company Name"=>$row['name'] ?? '',
        "Address line 1"=>$row['address_line_1'] ?? '',
        "Address line 2"=>$row['address_line_2'] ?? '',
        "Address line 3"=>$row['address_line_3'] ?? '',
        "Phone No"=>$row['phone_no'] ?? '',
        "Fax No"=>$row['fax_no'] ?? '',
        "Mobile No"=>$row['mobile_no'] ?? '',
        "Email"=>$row['email'] ?? '',
        "TIN No"=>$row['tin_no'] ?? '',
        "Action"=> searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'] ?? '',
        "Event Date"=>$row['event_date'] ?? '',
        );
    }

    $columnNames = ["Company Code", "Company Reg No", "New Reg No", "Company Name", "Address line 1", "Address line 2", "Address line 3", "Phone No", "Fax No", "Mobile No", "Email", "TIN No", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Customer")
{
    ## Fetch records
    $empQuery = "select * from Customer_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Customer Code"=>$row['customer_code'] ?? '',
        "Company Reg No"=>$row['company_reg_no'] ?? '',
        "New Reg No"=>$row['new_reg_no'] ?? '',
        "Customer Name"=>$row['name'] ?? '',
        "Address line 1"=>$row['address_line_1'] ?? '',
        "Address line 2"=>$row['address_line_2'] ?? '',
        "Address line 3"=>$row['address_line_3'] ?? '',
        "Address line 4"=>$row['address_line_4'] ?? '',
        "Phone No"=>$row['phone_no'] ?? '',
        "Fax No"=>$row['fax_no'] ?? '',
        "Contact Name"=>$row['contact_name'] ?? '',
        "IC No"=>$row['ic_no'] ?? '',
        "TIN No"=>$row['tin_no'] ?? '',
        "Email"=>$row['email'] ?? '',
        "Is Manual"=>$row['is_manual'] ?? '',
        "Company"=>$row['company'] ? searchCompanyById($row['company'], $db)['name'] ?? '' : '',
        "Action"=> searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'] ?? '',
        "Event Date"=>$row['event_date'] ?? '',
        );
    }

    $columnNames = ["Customer Code", "Company Reg No", "New Reg No", "Customer Name", "Address line 1", "Address line 2", "Address line 3", "Address line 4", "Phone No", "Fax No", "Contact Name", "IC No", "TIN No", "Email", "Is Manual", "Company", "Action", "Action By", "Event Date"];
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
        "Destination Code"=>$row['destination_code'] ?? '',
        "Destination Name"=>$row['name'] ?? '',
        "Description"=>$row['description'] ?? '',
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'] ?? '',
        "Event Date"=>$row['event_date'] ?? '',
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
        "Product Code"=>$row['product_code'] ?? '',
        "Product Name"=>$row['name'] ?? '',
        "Description"=>$row['description'] ?? '',
        "Category"=>$row['category'] ? searchItemCategoryById($row['category'], $db)['category_name'] ?? '' : '',
        "UOM"=>$row['uom'] ? searchUnitById($row['uom'], $db)['unit'] ?? '' : '',
        "Variance Type"=>$row['variance'] ?? '',
        "High"=>$row['high'] ?? '',
        "Low"=>$row['low'] ?? '',
        "Is Manual"=>$row['is_manual'] ?? '',
        "Company"=>$row['company'] ? searchCompanyById($row['company'], $db)['name'] ?? '' : '',
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'] ?? '',
        "Event Date"=>$row['event_date'] ?? '',
        );
    }

    $columnNames = ["Product Code", "Product Name", "Description", "Category", "UOM", "Variance Type", "High", "Low", "Is Manual", "Company", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Raw Materials")
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
    $empQuery = "select sl.*, c.name as company_name from Supplier_Log sl LEFT JOIN Company c ON sl.company = c.id".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Supplier Code"=>$row['supplier_code'] ?? '',
        "Company Reg No"=>$row['company_reg_no'] ?? '',
        "New Reg No"=>$row['new_reg_no'] ?? '',
        "Supplier Name"=>$row['name'] ?? '',
        "Address line 1"=>$row['address_line_1'] ?? '',
        "Address line 2"=>$row['address_line_2'] ?? '',
        "Address line 3"=>$row['address_line_3'] ?? '',
        "Address line 4"=>$row['address_line_4'] ?? '',
        "Phone No"=>$row['phone_no'] ?? '',
        "Fax No"=>$row['fax_no'] ?? '',
        "Contact Name"=>$row['contact_name'] ?? '',
        "IC No"=>$row['ic_no'] ?? '',
        "TIN No"=>$row['tin_no'] ?? '',
        "Payment Term"=>$row['payment_term'] ?? '',
        "Payment Term Period"=>$row['payment_term_period'] ?? '',
        "Account No"=>$row['account_no'] ?? '',
        "Is Manual"=>$row['is_manual'] ?? '',
        "Company"=>$row['company'] ? searchCompanyById($row['company'], $db)['name'] ?? '' : '',
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'] ?? '',
        "Event Date"=>$row['event_date'] ?? '',
        );
    }

    $columnNames = ["Supplier Code", "Company Reg No", "New Reg No", "Supplier Name", "Address line 1", "Address line 2", "Address line 3", "Address line 4", "Phone No", "Fax No", "Contact Name", "IC No", "TIN No", "Payment Term", "Payment Term Period", "Account No", "Is Manual", "Company", "Action", "Action By", "Event Date"];
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
        "Vehicle No"=>$row['veh_number'] ?? '',
        "Vehicle Weight"=>$row['vehicle_weight'] ?? '',
        "Transporter Code"=>$row['transporter_code'] ?? '',
        "Transporter Name"=>$row['transporter_name'] ?? '',
        "Customer Code"=>$row['customer_code'] ?? '',
        "Customer Name"=>$row['customer_name'] ?? '',
        "Supplier Code"=>$row['supplier_code'] ?? '',
        "Supplier Name"=>$row['supplier_name'] ?? '',
        "Is Manual"=>$row['is_manual'] ?? '',
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'] ?? '',
        "Event Date"=>$row['event_date'] ?? '',
        );
    }

    $columnNames = ["Vehicle No", "Vehicle Weight", "Transporter Code", "Transporter Name", "Customer Code", "Customer Name", "Supplier Code", "Supplier Name", "Is Manual", "Action", "Action By", "Event Date"];
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
    $empQuery = "select * from Units_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Unit"=>$row['unit'] ?? '',
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'] ?? '',
        "Event Date"=>$row['event_date'] ?? '',
        );
    }

    $columnNames = ["Unit", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Product Category")
{
    ## Fetch records
    $empQuery = "select pcl.*, c.name as company_name from Product_Categories_Log pcl LEFT JOIN Company c ON pcl.company = c.id".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Category Name"=>$row['category_name'],
        "Company"=>$row['company_name'],
        "Is Sales"=>$row['is_sales'],
        "Is Purchase"=>$row['is_purchase'],
        "Is Local"=>$row['is_local'],
        "Is Port"=>$row['is_port'],
        "Is Misc"=>$row['is_misc'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Category Name", "Company", "Is Sales", "Is Purchase", "Is Local", "Is Port", "Is Misc", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Location")
{
    ## Fetch records
    $empQuery = "select ll.*, p.name as plant_name from Location_Log ll LEFT JOIN Plant p ON ll.plant_id = p.id".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Location Code"=>$row['location_code'],
        "Location Name"=>$row['location_name'],
        "Plant"=>$row['plant_name'],
        "Weighing Count"=>$row['weighing_count'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Location Code", "Location Name", "Plant", "Weighing Count", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "Project")
{
    ## Fetch records
    $empQuery = "select pl.*, c.name as company_name from Project_Log pl LEFT JOIN Company c ON pl.company = c.id".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $data[] = array( 
        "id"=>$row['id'],
        "Project Code"=>$row['project_code'],
        "Project Description"=>$row['project_description'],
        "Company"=>$row['company_name'],
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'],
        "Event Date"=>$row['event_date'],
        );
    }

    $columnNames = ["Project Code", "Project Description", "Company", "Action", "Action By", "Event Date"];
}

if($_POST['selectedValue'] == "User")
{
    ## Fetch records
    $empQuery = "select * from Users_Log".$searchQuery;
    $empRecords = mysqli_query($db, $empQuery);
    $data = array();

    while($row = mysqli_fetch_assoc($empRecords)) {
        $plantNames = '';
        if (!empty($row['plant_id'])) {
            $plantIds = json_decode($row['plant_id'], true);
            if (is_array($plantIds)) {
                $plantNameArr = [];
                foreach ($plantIds as $pid) {
                    $plantName = searchPlantNameById($pid, $db);
                    if ($plantName) {
                        $plantNameArr[] = $plantName;
                    }
                }
                $plantNames = implode(', ', $plantNameArr);
            }
        }
        $data[] = array( 
        "id"=>$row['id'],
        "Employee Code"=>$row['employee_code'] ?? '',
        "Username"=>$row['username'] ?? '',
        "Name"=>$row['name'] ?? '',
        "Email"=>$row['useremail'] ?? '',
        "Role"=>$row['role'] ?? '',
        "Plant"=>$plantNames,
        "Language"=>$row['languages'] ?? '',
        "Action"=>searchActionNameById($row['action_id'], $db),
        "Action By"=>$row['action_by'] ?? '',
        "Event Date"=>$row['event_date'] ?? '',
        );
    }

    $columnNames = ["Employee Code", "Username", "Name", "Email", "Role", "Plant", "Language", "Action", "Action By", "Event Date"];
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