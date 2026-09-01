<?php
session_start();
require_once 'db_connect.php';
require_once 'requires/lookup.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$uid = $_SESSION['username'];

// Read the JSON data from the request body
$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) { 
    $CompanyCode = '';
    $CompanyName = '';

    $companyQuery = "SELECT * FROM Company";
    $companyDetail = mysqli_query($db, $companyQuery);
    $companyRow = mysqli_fetch_assoc($companyDetail);

    if (!empty($companyRow)) {
        $CompanyCode = $companyRow['company_code'];
        $CompanyName = $companyRow['name'];
    }

    foreach ($data as $rows) {
        $OrderDate = (isset($rows['DOCDATE']) && !empty($rows['DOCDATE']) && $rows['DOCDATE'] !== '' && $rows['DOCDATE'] !== null) ? DateTime::createFromFormat('Y-m-d', excelSerialToDate($rows['DOCDATE']))->format('Y-m-d H:i:s') : '';
        $PONumber = (isset($rows['DOCNO']) && !empty($rows['DOCNO']) && $rows['DOCNO'] !== '' && $rows['DOCNO'] !== null) ? trim($rows['DOCNO']) : '';
        $SupplierCode = (isset($rows['CODE']) && !empty($rows['CODE']) && $rows['CODE'] !== '' && $rows['CODE'] !== null) ? trim($rows['CODE']) : '';
        $SupplierName = (isset($rows['COMPANYNAME']) && !empty($rows['COMPANYNAME']) && $rows['COMPANYNAME'] !== '' && $rows['COMPANYNAME'] !== null) ? trim($rows['COMPANYNAME']) : '';
        $TransporterCode = (isset($rows['SHIPPER']) && !empty($rows['SHIPPER']) && $rows['SHIPPER'] !== '' && $rows['SHIPPER'] !== null) ? trim($rows['SHIPPER']) : '';
        $TransporterName = '';
        if (!empty($TransporterCode)) {
            $TransporterName = searchTransporterNameByCode($TransporterCode, $db);
        }
        $AgentCode = (isset($rows['AGENT']) && !empty($rows['AGENT']) && $rows['AGENT'] !== '' && $rows['AGENT'] !== null) ? trim($rows['AGENT']) : '';
        $AgentName = '';
        if (!empty($AgentCode)) {
            $AgentName = searchAgentNameByCode($AgentCode, $db);
        }
        $RawMaterialCode = (isset($rows['ITEMCODE']) && !empty($rows['ITEMCODE']) && $rows['ITEMCODE'] !== '' && $rows['ITEMCODE'] !== null) ? trim($rows['ITEMCODE']) : '';
        $RawMaterialName = (isset($rows['DESCRIPTION']) && !empty($rows['DESCRIPTION']) && $rows['DESCRIPTION'] !== '' && $rows['DESCRIPTION'] !== null) ? trim($rows['DESCRIPTION']) : '';
        $VehNumber = (isset($rows['DESCRIPTION2']) && !empty($rows['DESCRIPTION2']) && $rows['DESCRIPTION2'] !== '' && $rows['DESCRIPTION2'] !== null) ? trim($rows['DESCRIPTION2']) : '';
        $Remarks = !empty($rows['REMARK1']) ? trim($rows['REMARK1']) : '';
        $DestinationName =  (isset($rows['REMARK2']) && !empty($rows['REMARK2']) && $rows['REMARK2'] !== '' && $rows['REMARK2'] !== null) ? trim($rows['REMARK2']) : '';
        $DestinationCode = '';
        if(!empty($DestinationName)){
            $DestinationCode = searchDestinationCodeByName($DestinationName, $db);
        }
        $ExOrDel = (isset($rows['DOCREF1']) && !empty($rows['DOCREF1']) && $rows['DOCREF1'] !== '' && $rows['DOCREF1'] !== null) ? trim($rows['DOCREF1']) : '';
        $SupplierQuantity = (isset($rows['QTY']) && !empty($rows['QTY']) && $rows['QTY'] !== '' && $rows['QTY'] !== null) ? trim($rows['QTY']) : '';
        $unit = (isset($rows['UOM']) && !empty($rows['UOM']) && $rows['UOM'] !== '' && $rows['UOM'] !== null) ? trim($rows['UOM']) : '';
        if ($unit == 'MT'){
            $SupplierQuantity = $SupplierQuantity * 1000;
        }
        $PlantCode = (isset($rows['PROJECT']) && !empty($rows['PROJECT']) && $rows['PROJECT'] !== '' && $rows['PROJECT'] !== null) ? trim($rows['PROJECT']) : '';
        $PlantName = '';
        if (!empty($PlantCode)) {
            $PlantName = searchPlantNameByCode($PlantCode, $db);
        }
        $status = 'Open';
        $actionId = 1;

        # Company Checking & Processing
        // if($CompanyCode != null && $CompanyCode != ''){
        //     $companyQuery = "SELECT * FROM Company WHERE company_code = '$CompanyCode'";
        //     $companyDetail = mysqli_query($db, $companyQuery);
        //     $companyRow = mysqli_fetch_assoc($companyDetail);
            
        //     if(empty($companyRow)){
        //         if($insert_company = $db->prepare("INSERT INTO Company (company_code, name, created_by, modified_by) VALUES (?, ?, ?, ?)")) {
        //             $insert_company->bind_param('ssss', $CompanyCode, $CompanyName, $uid, $uid);
        //             $insert_company->execute();
        //             $companyId = $insert_company->insert_id; // Get the inserted company ID
        //             $insert_company->close();
                    
        //             if ($insert_company_log = $db->prepare("INSERT INTO Company_Log (company_id, company_code, name, action_id, action_by) VALUES (?, ?, ?, ?, ?)")) {
        //                 $insert_company_log->bind_param('sssss', $companyId, $CompanyCode, $CompanyName, $actionId, $uid);
        //                 $insert_company_log->execute();
        //                 $insert_company_log->close();
        //             }    
        //         }
        //     }
        // }

        # Supplier Checking & Processing
        if($SupplierCode != null && $SupplierCode != ''){
            $supplierQuery = "SELECT * FROM Supplier WHERE supplier_code = '$SupplierCode'";
            $supplierDetail = mysqli_query($db, $supplierQuery);
            $supplierRow = mysqli_fetch_assoc($supplierDetail);
            
            if(empty($supplierRow)){
                if($insert_supplier = $db->prepare("INSERT INTO Supplier (supplier_code, name, created_by, modified_by) VALUES (?, ?, ?, ?)")) {
                    $insert_supplier->bind_param('ssss', $SupplierCode, $SupplierName, $uid, $uid);
                    $insert_supplier->execute();
                    $supplierId = $insert_supplier->insert_id; // Get the inserted supplier ID
                    $insert_supplier->close();
                    
                    if ($insert_supplier_log = $db->prepare("INSERT INTO Supplier_Log (supplier_id, supplier_code, name, action_id, action_by) VALUES (?, ?, ?, ?, ?)")) {
                        $insert_supplier_log->bind_param('sssss', $supplierId, $SupplierCode, $SupplierName, $actionId, $uid);
                        $insert_supplier_log->execute();
                        $insert_supplier_log->close();
                    }    
                }
            }
        }

        # Transporter Checking & Processing
        if($TransporterCode != null && $TransporterCode != ''){
            $transporterQuery = "SELECT * FROM Transporter WHERE transporter_code = '$TransporterCode'";
            $transporterDetail = mysqli_query($db, $transporterQuery);
            $transporterSite = mysqli_fetch_assoc($transporterDetail);
            
            if(empty($transporterSite)){
                if($insert_transporter = $db->prepare("INSERT INTO Transporter (transporter_code, name, created_by, modified_by) VALUES (?, ?, ?, ?)")) {
                    $insert_transporter->bind_param('ssss', $TransporterCode, $TransporterName, $uid, $uid);
                    $insert_transporter->execute();
                    $transporterId = $insert_transporter->insert_id; // Get the inserted transporter ID
                    $insert_transporter->close();
                    
                    if ($insert_transporter_log = $db->prepare("INSERT INTO Transporter_Log (transporter_id, transporter_code, name, action_id, action_by) VALUES (?, ?, ?, ?, ?)")) {
                        $insert_transporter_log->bind_param('sssss', $transporterId, $TransporterCode, $TransporterName, $actionId, $uid);
                        $insert_transporter_log->execute();
                        $insert_transporter_log->close();
                    }    
                }
            }
        }

        # Agent Checking & Processing
        if($AgentCode != null && $AgentCode != ''){
            $agentQuery = "SELECT * FROM Agents WHERE agent_code = '$AgentCode'";
            $agentDetail = mysqli_query($db, $agentQuery);
            $agentRow = mysqli_fetch_assoc($agentDetail);
            
            if(empty($agentRow)){
                if($insert_agent = $db->prepare("INSERT INTO Agents (agent_code, name, created_by, modified_by) VALUES (?, ?, ?, ?)")) {
                    $insert_agent->bind_param('ssss', $AgentCode, $AgentName, $uid, $uid);
                    $insert_agent->execute();
                    $agentId = $insert_agent->insert_id; // Get the inserted agent ID
                    $insert_agent->close();
                    
                    if ($insert_agent_log = $db->prepare("INSERT INTO Agents_Log (agent_id, agent_code, name, action_id, action_by) VALUES (?, ?, ?, ?, ?)")) {
                        $insert_agent_log->bind_param('sssss', $agentId, $AgentCode, $AgentName, $actionId, $uid);
                        $insert_agent_log->execute();
                        $insert_agent_log->close();
                    }    
                }
            }
        }
        
        # Vehicle Checking & Processing
        if($VehNumber != null && $VehNumber != ''){
            $vehQuery = "SELECT * FROM Vehicle WHERE veh_number = '$VehNumber'";
            $vehDetail = mysqli_query($db, $vehQuery);
            $vehRow = mysqli_fetch_assoc($vehDetail);
            
            if(empty($vehRow)){
                if($insert_veh = $db->prepare("INSERT INTO Vehicle (veh_number, created_by, modified_by) VALUES (?, ?, ?)")) {
                    $insert_veh->bind_param('sss', $VehNumber, $uid, $uid);
                    $insert_veh->execute();
                    $vehId = $insert_veh->insert_id; // Get the inserted vehicle ID
                    $insert_veh->close();
                    
                    if ($insert_veh_log = $db->prepare("INSERT INTO Vehicle_Log (vehicle_id, veh_number, action_id, action_by) VALUES (?, ?, ?, ?)")) {
                        $insert_veh_log->bind_param('sssss', $vehId, $VehNumber, $actionId, $uid);
                        $insert_veh_log->execute();
                        $insert_veh_log->close();
                    }    
                }
            }
        }
        
        # Destination Checking & Processing
        if($DestinationCode != null && $DestinationCode != ''){
            $destinationQuery = "SELECT * FROM Destination WHERE destination_code = '$DestinationCode'";
            $destinationDetail = mysqli_query($db, $destinationQuery);
            $destinationRow = mysqli_fetch_assoc($destinationDetail);
            
            if(empty($destinationRow)){
                if($insert_destination = $db->prepare("INSERT INTO Destination (destination_code, name, created_by, modified_by) VALUES (?, ?, ?, ?)")) {
                    $insert_destination->bind_param('ssss', $DestinationCode, $DestinationName, $uid, $uid);
                    $insert_destination->execute();
                    $destinationId = $insert_destination->insert_id; // Get the inserted destination ID
                    $insert_destination->close();
                    
                    if ($insert_destination_log = $db->prepare("INSERT INTO Destination_Log (destination_id, destination_code, name, action_id, action_by) VALUES (?, ?, ?, ?, ?)")) {
                        $insert_destination_log->bind_param('sssss', $destinationId, $DestinationCode, $DestinationName, $actionId, $uid);
                        $insert_destination_log->execute();
                        $insert_destination_log->close();
                    }    
                }
            }
        }

        # Plant Checking & Processing
        if($PlantCode != null && $PlantCode != ''){
            $plantQuery = "SELECT * FROM Plant WHERE plant_code = '$PlantCode'";
            $plantDetail = mysqli_query($db, $plantQuery);
            $plantRow = mysqli_fetch_assoc($plantDetail);
            
            if(empty($plantRow)){
                if($insert_plant = $db->prepare("INSERT INTO Plant (plant_code, created_by, modified_by) VALUES (?, ?, ?)")) {
                    $insert_plant->bind_param('sss', $PlantCode, $uid, $uid);
                    $insert_plant->execute();
                    $plantId = $insert_plant->insert_id; // Get the inserted plant ID
                    $insert_plant->close();
                    
                    if ($insert_plant_log = $db->prepare("INSERT INTO Plant_Log (plant_id, plant_code, action_id, action_by) VALUES (?, ?, ?, ?)")) {
                        $insert_plant_log->bind_param('ssss', $plantId, $PlantCode, $actionId, $uid);
                        $insert_plant_log->execute();
                        $insert_plant_log->close();
                    }    
                }
            }
        }

        # Raw Material Checking & Processing
        if($RawMaterialCode != null && $RawMaterialCode != ''){
            $rawMatQuery = "SELECT * FROM Raw_Mat WHERE raw_mat_code = '$RawMaterialCode'";
            $rawMatDetail = mysqli_query($db, $rawMatQuery);
            $rawMatRow = mysqli_fetch_assoc($rawMatDetail);
            
            if(empty($rawMatRow)){
                if($insert_raw_mat = $db->prepare("INSERT INTO Raw_Mat (raw_mat_code, name, created_by, modified_by) VALUES (?, ?, ?, ?)")) {
                    $insert_raw_mat->bind_param('ssss', $RawMaterialCode, $RawMaterialName, $uid, $uid);
                    $insert_raw_mat->execute();
                    $rawMatId = $insert_raw_mat->insert_id; // Get the inserted destination ID
                    $insert_raw_mat->close();
                    
                    if ($insert_raw_mat_log = $db->prepare("INSERT INTO Raw_Mat_Log (raw_mat_id, raw_mat_code, name, action_id, action_by) VALUES (?, ?, ?, ?, ?)")) {
                        $insert_raw_mat_log->bind_param('sssss', $rawMatId, $RawMaterialCode, $RawMaterialName, $actionId, $uid);
                        $insert_raw_mat_log->execute();
                        $insert_raw_mat_log->close();
                    }    
                }
            }
        }

        # Checking for existing PO No.
        if($PONumber != null && $PONumber != ''){
            $poQuery = "SELECT * FROM Purchase_Order WHERE po_no = '$PONumber' AND deleted = '0'";
            $poDetail = mysqli_query($db, $poQuery);
            $poRow = mysqli_fetch_assoc($poDetail);

            if(empty($poRow)){
                // if ($insert_stmt = $db->prepare("INSERT INTO Purchase_Order (company_code, company_name, supplier_code, supplier_name, site_code, site_name, order_date, order_no, po_no, delivery_date, agent_code, agent_name, destination_code, destination_name, deliver_to_name, raw_mat_code, raw_mat_name, order_load, order_quantity, remarks, status, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                //     $insert_stmt->bind_param('sssssssssssssssssssssss', $CompanyCode, $CompanyName, $SupplierCode, $SupplierName, $SiteCode, $SiteName, $OrderDate, $OrderNumber, $PONumber, $DeliveryDate, $SalesrepCode, $SalesrepName, $DestinationCode, $DestinationName, $DeliverToName, $RawMaterialCode, $RawMaterialName, $SupplierLoad, $SupplierQuantity,$Remarks, $status, $uid, $uid);
                //     $insert_stmt->execute();
                //     $insert_stmt->close(); 
                // }

                if ($insert_stmt = $db->prepare("INSERT INTO Purchase_Order (company_code, company_name, supplier_code, supplier_name, order_date, po_no, agent_code, agent_name, destination_code, destination_name, raw_mat_code, raw_mat_name, plant_code, plant_name, transporter_code, transporter_name, veh_number, exquarry_or_delivered, order_quantity, balance, remarks, status, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                    $insert_stmt->bind_param('ssssssssssssssssssssssss', $CompanyCode, $CompanyName, $SupplierCode, $SupplierName, $OrderDate, $PONumber, $AgentCode, $AgentName, $DestinationCode, $DestinationName, $RawMaterialCode, $RawMaterialName, $PlantCode, $PlantName, $TransporterCode, $TransporterName, $VehNumber, $ExOrDel, $SupplierQuantity, $SupplierQuantity, $Remarks, $status, $uid, $uid);
                    $insert_stmt->execute();
                    $insert_stmt->close(); 
                }
            }
        }
    }

    $db->close();

    echo json_encode(
        array(
            "status"=> "success", 
            "message"=> "Added Successfully!!" 
        )
    );
} else {
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    );     
}
?>
