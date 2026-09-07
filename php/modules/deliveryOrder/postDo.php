<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';
// $config = include(dirname(__DIR__, 2) . '/sql_config.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$uid = $_SESSION['username'];
$companyKey = $_SESSION['company'] ?? null;
$type = '';

if($_POST['type'] != null && $_POST['type'] != ''){
    $type = $_POST['type'];
}

## Search 
$searchQuery = "";
if(!empty($_POST['fromDate']) && $_POST['fromDate'] != null && $_POST['fromDate'] != ''){
  $dateTime = DateTime::createFromFormat('d-m-Y H:i:s', $_POST['fromDate']);
  $fromDateTime = $dateTime->format('Y-m-d H:i:s');
  $searchQuery .= " and transaction_date >= '".$fromDateTime."'";
}

if(!empty($_POST['toDate']) && $_POST['toDate'] != null && $_POST['toDate'] != ''){
  $dateTime = DateTime::createFromFormat('d-m-Y H:i:s', $_POST['toDate']);
  $toDateTime = $dateTime->format('Y-m-d H:i:s');
	$searchQuery .= " and transaction_date <= '".$toDateTime."'";
}

if(!empty($_POST['company']) && $_POST['company'] != null && $_POST['company'] != '' && $_POST['company'] != '-'){
	$searchQuery .= " and company_id = '".$_POST['company']."'";
}

if(!empty($_POST['customer']) && $_POST['customer'] != null && $_POST['customer'] != '' && $_POST['customer'] != '-'){
	$searchQuery .= " and customer_code = '".$_POST['customer']."'";
}

if(!empty($_POST['product']) && $_POST['product'] != null && $_POST['product'] != '' && $_POST['product'] != '-'){
	$searchQuery .= " and product_code = '".$_POST['product']."'";
}

if(!empty($_POST['plant']) && $_POST['plant'] != null && $_POST['plant'] != '' && $_POST['plant'] != '-'){
	$searchQuery .= " and plant_code = '".$_POST['plant']."'";
}

if(!empty($_POST['deliveryNo']) && $_POST['deliveryNo'] != null && $_POST['deliveryNo'] != ''){
  $searchQuery .= " and delivery_no = '".mysqli_real_escape_string($db, $_POST['deliveryNo'])."'";
}

if(!empty($_POST['transactionId']) && $_POST['transactionId'] != null && $_POST['transactionId'] != ''){
  $searchQuery .= " and transaction_id = '".mysqli_real_escape_string($db, $_POST['transactionId'])."'";
}

if (!$companyKey || !isset($config[$companyKey])) {
    echo json_encode([
        "status" => "failed",
        "message" => "Invalid company session"
    ]);
    exit;
}

if ($type == "MULTI"){
    if(is_array($_POST['userID'])){
        $ids = implode(",", $_POST['userID']);
    }else{
        $ids = $_POST['userID'];
    }

    if ($stmt2 = $db->prepare("SELECT * FROM Weight WHERE id IN ($ids) AND synced='N'")) {
        if($stmt2->execute()){
            $result = $stmt2->get_result();

            while ($row = $result->fetch_assoc()) {
                $product = $row['product_code'];
                $customer = $row['customer_code'];
                $plant = $row['plant_code'];
                $company = $row['company_id'];
                $fromDate = DateTime::createFromFormat('d-m-Y H:i:s', $_POST['fromDate']);
                $fromDateTime = $fromDate->format('Y-m-d H:i:s');
                $toDate = DateTime::createFromFormat('d-m-Y H:i:s', $_POST['toDate']);
                $toDateTime = $toDate->format('Y-m-d H:i:s');

                $do_stmt = $db->prepare("
                    SELECT * 
                    FROM Weight 
                    WHERE transaction_status = 'Sales' 
                      AND plant_code = ? 
                      AND product_code = ? 
                      AND customer_code = ? 
                      AND company_id = ? 
                      AND transaction_date >= ? 
                      AND transaction_date <= ? 
                      AND is_complete = 'Y' 
                      AND is_cancel <> 'Y' 
                      AND status = '0'
                ");
                $do_stmt->bind_param('ssssss', $plant, $product, $customer, $company, $fromDateTime, $toDateTime);
                $do_stmt->execute();
                $doRecords = $do_stmt->get_result();

                while($row2 = $doRecords->fetch_assoc()) {
                    $transactionDate = DateTime::createFromFormat('Y-m-d H:i:s', $row2['transaction_date']);
                    $transactionDateTime = $transactionDate->format('Y-m-d');
                    $uom = 'MT';
                    $finalPlantCode = $row2['plant_code'];
                    $qty = (float) $row2['nett_weight1']/1000;
                    $unitPrice = $row2['unit_price'] ?? 0;
                    $amt = (float) $qty * (float) $unitPrice;
        
                    $records[] = [
                        "DOCREF2"     => $row2["transaction_id"],
                        "DOCDATE"     => $transactionDateTime,
                        "DESCRIPTION2"=> $row2["lorry_plate_no1"],
                        "CODE"        => $row2["customer_code"] ?? "", // hardcoded or dynamic if needed
                        "COMPANYNAME" => $row2["customer_name"],
                        "ITEMCODE"    => $row2["product_code"],
                        "DESCRIPTION" => $row2["product_name"],
                        "REMARK2"     => $row2["destination"] ?? "-",
                        "SHIPPER"     => $row2["transporter_code"] ?? "",
                        "DOCREF1"     => "",
                        "DOCNOEX"     => "",
                        "REMARK1"     => $row2["delivery_no"],
                        "QTY"         => round($qty, 2),
                        "UOM"         => $uom,
                        "PROJECT"     => $finalPlantCode,
                        "LOCATION"    => $finalPlantCode,
                        "UNITPRICE"   => round($unitPrice, 2),
                        "AMOUNT"      => round($amt, 2)
                    ];
                }
                if(isset($do_stmt)) {
                    $do_stmt->close();
                }
            }

            $stmt2->close();
            
            $services = 'PostDeliveryOrder';
            $requests = json_encode($records);
            
            // Insert request into Api_Log
            $stmtL = $db->prepare("INSERT INTO Api_Log (services, request) VALUES (?, ?)");
            $stmtL->bind_param('ss', $services, $requests);
            $stmtL->execute();
            $logId = $stmtL->insert_id;
            
            // Send to API
            $url = rtrim($config[$companyKey], '/') . "/delivery_order";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($records));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            
            // Decode API response (JSON string to array)
            $responseData = json_decode($response, true);
            
            // Prepare loggable response JSON
            if ($httpCode === 200 && isset($responseData["status"]) && $responseData["status"] === "success") {
                foreach ($responseData["results"] as $item) {
                    if (isset($item["status"]) && $item["status"] === "success") {
                        $docref2 = $item["docref2"];
                        
                        $oldReportMode = mysqli_report(MYSQLI_REPORT_OFF);
                        $alive = ($db && @$db->ping());
                        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                        
                        if (!$alive) {
                            if ($db) { @$db->close(); }
                            require 'db_connect.php';
                        }
            
                        // Update weight table
                        /*$stmtUpdateWeight = $db->prepare("UPDATE Weight SET synced = 'Y' WHERE transaction_id = ?");
                        $stmtUpdateWeight->bind_param('s', $docref2);
                        $stmtUpdateWeight->execute();
                        $stmtUpdateWeight->close();*/
                    }
                }
                
                $responseToLog = json_encode([
                    "status" => "success", 
                    "message" => "Post Successfully",
                    "posted" => $responseData["results"]
                ]);
            } else {
                $responseToLog = json_encode([
                    "status" => "failed",
                    "message" => $responseData["message"],
                ]);
            }
            
            $oldReportMode = mysqli_report(MYSQLI_REPORT_OFF);
            $alive = ($db && @$db->ping());
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            if (!$alive) {
                if ($db) { @$db->close(); }
                require 'db_connect.php';
            }
            
            // Update the same Api_Log record with the response
            $stmtU = $db->prepare("UPDATE Api_Log SET response = ? WHERE id = ?");
            $stmtU->bind_param('ss', $responseToLog, $logId);
            $stmtU->execute();
            $stmtU->close();
            
            // Output final response to client
            $db->close();
            echo $responseToLog;
        } else{
            echo json_encode(
                array(
                    "status"=> "failed", 
                    "message"=> $stmt2->error
                )
            );
        }
    } 
    else{
        echo json_encode(
            array(
                "status"=> "failed", 
                "message"=> "Something's wrong"
            )
        );
    }
}else if ($type == "MULTIDO"){
    if(is_array($_POST['userID'])){
        $ids = implode(",", $_POST['userID']);
    }else{
        $ids = $_POST['userID'];
    }

    if ($stmt2 = $db->prepare("SELECT * FROM Weight WHERE id IN ($ids) AND synced='N'")) {
        if($stmt2->execute()){
            $result = $stmt2->get_result();

            while ($row = $result->fetch_assoc()) {
                $transactionDate = DateTime::createFromFormat('Y-m-d H:i:s', $row['transaction_date']);
                $transactionDateTime = $transactionDate->format('Y-m-d');
                $uom = 'MT';
                $finalPlantCode = $row['plant_code'];
                $qty = (float) $row['nett_weight1']/1000;
                $unitPrice = $row['unit_price'] ?? 0;
                $amt = (float) $qty * (float) $unitPrice;
    
                $records[] = [
                    "DOCREF2"     => $row["transaction_id"],
                    "DOCDATE"     => $transactionDateTime,
                    "DESCRIPTION2"=> $row["lorry_plate_no1"],
                    "CODE"        => $row["customer_code"] ?? "", // hardcoded or dynamic if needed
                    "COMPANYNAME" => $row["customer_name"],
                    "ITEMCODE"    => $row["product_code"],
                    "DESCRIPTION" => $row["product_name"],
                    "REMARK2"     => $row["destination"] ?? "-",
                    "SHIPPER"     => $row["transporter_code"] ?? "",
                    "DOCREF1"     => "",
                    "DOCNOEX"     => "",
                    "REMARK1"     => $row["delivery_no"],
                    "QTY"         => round($qty, 2),
                    "UOM"         => $uom,
                    "PROJECT"     => $finalPlantCode,
                    "LOCATION"    => $finalPlantCode,
                    "UNITPRICE"   => round($unitPrice, 2),
                    "AMOUNT"      => round($amt, 2)
                ];
            }

            $stmt2->close();
            
            $services = 'PostDeliveryOrder';
            $requests = json_encode($records);
            
            // Insert request into Api_Log
            $stmtL = $db->prepare("INSERT INTO Api_Log (services, request) VALUES (?, ?)");
            $stmtL->bind_param('ss', $services, $requests);
            $stmtL->execute();
            $logId = $stmtL->insert_id;
            
            // Send to API
            $url = rtrim($config[$companyKey], '/') . "/delivery_order";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($records));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            
            // Decode API response (JSON string to array)
            $responseData = json_decode($response, true);
            
            // Prepare loggable response JSON
            if ($httpCode === 200 && isset($responseData["status"]) && $responseData["status"] === "success") {
                foreach ($responseData["results"] as $item) {
                    if (isset($item["status"]) && $item["status"] === "success") {
                        $docref2 = $item["docref2"];
                        
                        $oldReportMode = mysqli_report(MYSQLI_REPORT_OFF);
                        $alive = ($db && @$db->ping());
                        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                        
                        if (!$alive) {
                            if ($db) { @$db->close(); }
                            require 'db_connect.php';
                        }
            
                        // Update weight table
                        /*$stmtUpdateWeight = $db->prepare("UPDATE Weight SET synced = 'Y' WHERE transaction_id = ?");
                        $stmtUpdateWeight->bind_param('s', $docref2);
                        $stmtUpdateWeight->execute();
                        $stmtUpdateWeight->close();*/
                    }
                }
                
                $responseToLog = json_encode([
                    "status" => "success", 
                    "message" => "Post Successfully",
                    "posted" => $responseData["results"]
                ]);
            } else {
                $responseToLog = json_encode([
                    "status" => "failed",
                    "message" => $responseData["message"],
                ]);
            }
            
            $oldReportMode = mysqli_report(MYSQLI_REPORT_OFF);
            $alive = ($db && @$db->ping());
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            if (!$alive) {
                if ($db) { @$db->close(); }
                require 'db_connect.php';
            }
            
            // Update the same Api_Log record with the response
            $stmtU = $db->prepare("UPDATE Api_Log SET response = ? WHERE id = ?");
            $stmtU->bind_param('ss', $responseToLog, $logId);
            $stmtU->execute();
            $stmtU->close();
            
            // Output final response to client
            $db->close();
            echo $responseToLog;
        } else{
            echo json_encode(
                array(
                    "status"=> "failed", 
                    "message"=> $stmt2->error
                )
            );
        }
    } 
    else{
        echo json_encode(
            array(
                "status"=> "failed", 
                "message"=> "Something's wrong"
            )
        );
    }
}else{
    $sql = "select * from Weight WHERE transaction_status = 'Sales' AND is_complete = 'Y' AND  is_cancel <> 'Y' AND synced='N'".$searchQuery;
    if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
        $username = implode("', '", $_SESSION["plant"]);
        $sql = "select * from Weight WHERE transaction_status = 'Sales' AND is_complete = 'Y' AND  is_cancel <> 'Y' AND synced='N' and plant_code IN ('$username')".$searchQuery;
    }

    if ($stmt2 = $db->prepare($sql)){
        if($stmt2->execute()){
            $result = $stmt2->get_result();
            $records = [];

            while ($row = $result->fetch_assoc()) {
                $transactionDate = DateTime::createFromFormat('Y-m-d H:i:s', $row['transaction_date']);
                $transactionDateTime = $transactionDate->format('Y-m-d');
                $uom = 'MT';
                $finalPlantCode = $row['plant_code'];
                $qty = (float) $row['nett_weight1']/1000;
                $unitPrice = $row['unit_price'] ?? 0;
                $amt = (float) $qty * (float) $unitPrice;
    
                $records[] = [
                    "DOCREF2"     => $row["transaction_id"],
                    "DOCDATE"     => $transactionDateTime,
                    "DESCRIPTION2"=> $row["lorry_plate_no1"],
                    "CODE"        => $row["customer_code"] ?? "", // hardcoded or dynamic if needed
                    "COMPANYNAME" => $row["customer_name"],
                    "ITEMCODE"    => $row['product_code'],
                    "DESCRIPTION" => $row["product_name"],
                    "REMARK2"     => $row["destination"] ?? "-",
                    "SHIPPER"     => $row["transporter_code"] ?? "",
                    "DOCREF1"     => "",
                    "DOCNOEX"     => "",
                    "REMARK1"     => $row["delivery_no"],
                    "QTY"         => round($qty, 2),
                    "UOM"         => $uom,
                    "PROJECT"     => $finalPlantCode,
                    "LOCATION"    => $finalPlantCode,
                    "UNITPRICE"   => round($unitPrice, 2),
                    "AMOUNT"      => round($amt, 2)
                ];
            }

            $stmt2->close();
            
            if(isset($records) && count($records)>0){
                $services = 'PostDeliveryOrder';
                $requests = json_encode($records);
                
                // Insert request into Api_Log
                $stmtL = $db->prepare("INSERT INTO Api_Log (services, request) VALUES (?, ?)");
                $stmtL->bind_param('ss', $services, $requests);
                $stmtL->execute();
                $logId = $stmtL->insert_id;
                
                // Send to API
                $url = rtrim($config[$companyKey], '/') . "/delivery_order";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($records));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = curl_error($ch);
                curl_close($ch);
                
                // Decode API response (JSON string to array)
                $responseData = json_decode($response, true);
                
                // Prepare loggable response JSON
                if ($httpCode === 200 && isset($responseData["status"]) && $responseData["status"] === "success") {
                    // Loop through each result item
                    foreach ($responseData["results"] as $item) {
                        if (isset($item["status"]) && $item["status"] === "success") {
                            $docref2 = $item["docref2"];
                            
                            $oldReportMode = mysqli_report(MYSQLI_REPORT_OFF);
                            $alive = ($db && @$db->ping());
                            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                            
                            if (!$alive) {
                                if ($db) { @$db->close(); }
                                require 'db_connect.php';
                            }
                
                            // Update weight table
                            /*$stmtUpdateWeight = $db->prepare("UPDATE Weight SET synced = 'Y' WHERE transaction_id = ?");
                            $stmtUpdateWeight->bind_param('s', $docref2);
                            $stmtUpdateWeight->execute();
                            $stmtUpdateWeight->close();*/
                        }
                    }
                    
                    $responseToLog = json_encode([
                        "status" => "success", 
                        "message" => "Post Successfully",
                        "posted" => $responseData["results"]
                    ]);
                } 
                else {
                    $responseToLog = json_encode([
                        "status" => "failed",
                        "message" => $responseData["message"] ?? 'Failed to insert',
                    ]);
                }
                
                $oldReportMode = mysqli_report(MYSQLI_REPORT_OFF);
                $alive = ($db && @$db->ping());
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                
                if (!$alive) {
                    if ($db) { @$db->close(); }
                    require 'db_connect.php';
                }
                
                // Update the same Api_Log record with the response
                $stmtU = $db->prepare("UPDATE Api_Log SET response = ? WHERE id = ?");
                $stmtU->bind_param('ss', $responseToLog, $logId);
                $stmtU->execute();
                $stmtU->close();
            }
            else{
                $responseToLog = json_encode([
                    "status" => "failed", 
                    "message" => "No record founds"
                ]);
            }
            
            // Output final response to client
            $db->close();
            echo $responseToLog;
        } else{
            echo json_encode(
                array(
                    "status"=> "failed", 
                    "message"=> $stmt2->error
                )
            );
        }
    }
    else{
        echo json_encode(
            array(
                "status"=> "failed", 
                "message"=> "Something's wrong"
            )
        );
    }
}
?>