<?php
session_start();
require_once 'db_connect.php';

$searchQuery = "";
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
    $username = implode("', '", $_SESSION["plant"]);
    $searchQuery = "and plant_code IN ('$username')";
}

if(isset($_POST['fromDate']) && $_POST['fromDate'] != null && $_POST['fromDate'] != ''){
    $date = DateTime::createFromFormat('d-m-Y', $_POST['fromDate']);
    $formatted_date = $date->format('Y-m-d 00:00:00');
    $fromDate = $date->format('d/m/Y');

    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.transaction_date >= '".$formatted_date."'";
    }
    else{
        $searchQuery .= " and count.transaction_date >= '".$formatted_date."'";
    }
}

if(isset($_POST['toDate']) && $_POST['toDate'] != null && $_POST['toDate'] != ''){
    $date = DateTime::createFromFormat('d-m-Y', $_POST['toDate']);
    $formatted_date = $date->format('Y-m-d 23:59:59');
    $toDate = $date->format('d/m/Y');

    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.transaction_date <= '".$formatted_date."'";
    }
    else{
        $searchQuery .= " and count.transaction_date <= '".$formatted_date."'";
    }
}

if(isset($_POST['transactionStatus']) && $_POST['transactionStatus'] != null && $_POST['transactionStatus'] != '' && $_POST['transactionStatus'] != '-'){
    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.transaction_status = '".$_POST['transactionStatus']."'";

        // if($_POST['status'] == 'Sales'){
        //     $searchQuery .= " and Weight.transaction_status = '".$_POST['status']."'";
        // }
        // else{
        //     $searchQuery .= " and Weight.transaction_status IN ('Purchase', 'Local')";
        // }
    }
    else{
        $searchQuery .= " and count.transaction_status = '".$_POST['transactionStatus']."'";
    }	
}

if(isset($_POST['customer']) && $_POST['customer'] != null && $_POST['customer'] != '' && $_POST['customer'] != '-'){
    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.customer_code = '".$_POST['customer']."'";
    }
    else{
        $searchQuery .= " and count.customer_code = '".$_POST['customer']."'";
    }
}

if(isset($_POST['supplier']) && $_POST['supplier'] != null && $_POST['supplier'] != '' && $_POST['supplier'] != '-'){
    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.supplier_code = '".$_POST['supplier']."'";
    }
    else{
        $searchQuery .= " and count.supplier_code = '".$_POST['supplier']."'";
    }
}

if(isset($_POST['vehicle']) && $_POST['vehicle'] != null && $_POST['vehicle'] != '' && $_POST['vehicle'] != '-'){
    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.lorry_plate_no1 = '".$_POST['vehicle']."'";
    }
    else{
        $searchQuery .= " and count.lorry_plate_no1 = '".$_POST['vehicle']."'";
    }
}

if(isset($_POST['weighingType']) && $_POST['weighingType'] != null && $_POST['weighingType'] != '' && $_POST['weighingType'] != '-'){
    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.weight_type like '%".$_POST['weighingType']."%'";
    }
    else{
        $searchQuery .= " and count.weight_type like '%".$_POST['weighingType']."%'";
    }
}

if(isset($_POST['customerType']) && $_POST['customerType'] != null && $_POST['customerType'] != '' && $_POST['customerType'] != '-'){
    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.customer_type like '%".$_POST['customerType']."%'";
    }
    else{
        $searchQuery .= " and count.customer_type like '%".$_POST['customerType']."%'";
    }
}

if(isset($_POST['product']) && $_POST['product'] != null && $_POST['product'] != '' && $_POST['product'] != '-'){
    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.product_code = '".$_POST['product']."'";
    }
    else{
        $searchQuery .= " and count.product_code = '".$_POST['product']."'";
    }
}

if(isset($_POST['rawMat']) && $_POST['rawMat'] != null && $_POST['rawMat'] != '' && $_POST['rawMat'] != '-'){
    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.raw_mat_code = '".$_POST['rawMat']."'";
    }
    else{
        $searchQuery .= " and count.raw_mat_code = '".$_POST['rawMat']."'";
    }
}

if(isset($_POST['destination']) && $_POST['destination'] != null && $_POST['destination'] != '' && $_POST['destination'] != '-'){
    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.destination = '".$_POST['destination']."'";
    }
    else{
        $searchQuery .= " and count.destination = '".$_POST['destination']."'";
    }
}

if(isset($_POST['plant']) && $_POST['plant'] != null && $_POST['plant'] != '' && $_POST['plant'] != '-'){
    if($_POST["file"] == 'weight'){
        $searchQuery .= " and Weight.plant_code = '".$_POST['plant']."'";
    }
    else{
        $searchQuery .= " and count.plant_code = '".$_POST['plant']."'";
    }
}

if($_POST['status'] != null && $_POST['status'] != '' && $_POST['status'] != '-'){
    if ($_POST['status'] == 'Complete'){
        $searchQuery .= " and is_complete = 'Y'";
    }elseif ($_POST['status'] == 'Cancelled'){
        $searchQuery .= " and is_cancel = 'Y'";
    }elseif ($_POST['status'] == 'Pending'){
        $searchQuery .= " and is_complete='N' AND is_cancel='N'";
    }
    else{
        $searchQuery .= " and is_complete = 'Y'";
    }
}

if($_POST['isMulti'] != null && $_POST['isMulti'] != '' && $_POST['isMulti'] != '-'){
    $isMulti = $_POST['isMulti'];

    if ($isMulti == 'Y'){
        if(is_array($_POST['ids'])){
			$ids = implode(",", $_POST['ids']);
		}else{
			$ids = $_POST['ids'];
		}

        $searchQuery = " and id IN ($ids)";
    }
}

if(isset($_POST["file"])){
    if($_POST["file"] == 'weight'){
        if ($select_stmt = $db->prepare("select * from Weight WHERE status = '0'".$searchQuery.' ORDER BY tare_weight1_date')) {
            // Execute the prepared query.
            if (! $select_stmt->execute()) {
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Something went wrong"
                    )); 
            }
            else{
                $result = $select_stmt->get_result();

                $message = '<html>
                            <head>
                                <style>
                                    @media print {
                                        @page {
                                            margin-left: 0.5in;
                                            margin-right: 0.5in;
                                            margin-top: 0.1in;
                                            margin-bottom: 0.1in;
                                        }
                                        
                                    } 
                                            
                                    table {
                                        width: 100%;
                                        border-collapse: collapse;
                                        
                                    } 
                                    
                                    .table th, .table td {
                                        padding: 0.70rem;
                                        vertical-align: top;
                                        border-top: 1px solid #dee2e6;
                                        
                                    } 
                                    
                                    .table-bordered {
                                        border: 1px solid #000000;
                                        
                                    } 
                                    
                                    .table-bordered th, .table-bordered td {
                                        border: 1px solid #000000;
                                        font-family: sans-serif;
                                        font-size: 12px;
                                        
                                    } 
                                    
                                    .row {
                                        display: flex;
                                        flex-wrap: wrap;
                                        margin-top: 20px;
                                        margin-right: -15px;
                                        margin-left: -15px;
                                        
                                    } 
                                    
                                    .col-md-4{
                                        position: relative;
                                        width: 33.333333%;
                                    }
                                </style>
                            </head>
                            <body>
                                <table style="width:100%;">
                                    <thead>
                                        <tr style="font-size: 9px; text-align: center;">
                                            <th>TRANSACTION <br>ID</th>
                                            <th>TRANSACTION <br>DATE</th>
                                            <th>TRANSACTION <br>STATUS</th>
                                            <th>LORRY <br>NO.</th>';
                                            
                                        if($_POST['status'] == 'Sales' || $_POST['status'] == 'Misc'){
                                            $message .= '<th>CUSTOMER <br>CODE</th>';
                                            $message .= '<th>CUSTOMER</th>';
                                        }
                                        else{
                                            $message .= '<th>SUPPLIER <br>CODE</th>';
                                            $message .= '<th>SUPPLIER</th>';
                                        }
                                            
                                            $message .= '<th>'.(($_POST['status'] == 'Sales' || $_POST['status'] == 'Misc') ? 'PRODUCT <br>CODE' : 'RAW MAT <br>CODE').'</th>
                                            <th>'.(($_POST['status'] == 'Sales' || $_POST['status'] == 'Misc') ? 'PRODUCT' : 'RAW MAT').'</th>
                                            <th>DESTINATION <br>CODE</th>
                                            <th>DESTINATION</th>
                                            <th>PO NO.</th>
                                            <th>DO NO.</th>
                                            <th>CONTAINER <br>NO.</th>
                                            <th>SEAL NO.</th>
                                            <th>CONTAINER <br>NO. 2</th>
                                            <th>SEAL NO. 2</th>
                                            <th>ORDER WEIGHT</th>
                                            <th>SUPPLIER WEIGHT</th>
                                            <th>INCOMING <br>(MT)</th>
                                            <th>OUTGOING <br>(MT)</th>
                                            <th>NET <br>(MT)</th>
                                            <th>IN TIME</th>
                                            <th>OUT TIME</th>
                                            <th>INCOMING 2 <br>(MT)</th>
                                            <th>OUTGOING 2 <br>(MT)</th>
                                            <th>NET 2 <br>(MT)</th>
                                            <th>IN TIME 2</th>
                                            <th>OUT TIME 2</th>
                                            <th>VARIANCE</th>
                                            <th>SUB TOTAL WEIGHT</th>
                                            <th>USER</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

                                    // Initialize the grouped data array
                                    $groupedData = [];
                                    
                                    // Fetch data and group by product_name
                                    while ($row = $result->fetch_assoc()) {
                                        $productName = ($row['transaction_status'] == 'Sales' || $row['transaction_status'] == 'Misc' ? $row['product_name'] : $row['raw_mat_name']);
                                    
                                        if (!isset($groupedData[$productName])) {
                                            $groupedData[$productName] = [];
                                        }

                                        if($row['transaction_status'] == 'Sales'){
                                            $transactionStatus = 'Dispatch';
                                        }
                                        else if($row['transaction_status'] == 'Purchase'){
                                            $transactionStatus = 'Receiving';
                                        }
                                        else if($row['transaction_status'] == 'Misc'){
                                            $transactionStatus = 'Miscellaneous';
                                        }
                                        else{
                                            $transactionStatus = 'Internal Transfer';
                                        }

                                        $row['transactionStatus'] = $transactionStatus;

                                        $groupedData[$productName][] = $row;
                                    } 
                                    
                                    // Initialize total values
                                    $grandTotalGross = 0;
                                    $grandTotalTare = 0;
                                    $grandTotalNet = 0;

                                    // Generate table grouped by product
                                    foreach ($groupedData as $product => $rows) {
                                        $message .= '<tr>
                                            <td colspan="14" style="font-size: 9px;">. </td>
                                        </tr>
                                        <tr>
                                            <td colspan="14" style="font-size: 9px;">. </td>
                                        </tr>';
                                    
                                        $totalGross = 0;
                                        $totalTare = 0;
                                        $totalNet = 0;
                                    
                                        foreach ($rows as $row) {
                                            $grossWeightDate = new DateTime($row['gross_weight1_date']);
                                            $formattedGrossWeightDate = $grossWeightDate->format('H:i');
                                            $tareWeightDate =  new DateTime($row['tare_weight1_date']);
                                            $formattedTareWeightDate = $tareWeightDate->format('H:i');
                                            $grossWeightDate2 = new DateTime($row['gross_weight2_date']);
                                            $formattedGrossWeightDate2 = $grossWeightDate2->format('H:i');
                                            $tareWeightDate2 =  new DateTime($row['tare_weight2_date']);
                                            $formattedTareWeightDate2 = $tareWeightDate2->format('H:i');
                                            $transactionDate =  new DateTime($row['transaction_date']);
                                            $formattedtransactionDate = $transactionDate->format('d/m/Y');
                                            
                                            $message .= '<tr style="font-size: 9px; text-align: center;">
                                                <td>' . $row['transaction_id'] . '</td>
                                                <td>' . $formattedtransactionDate . '</td>
                                                <td>' . $row['transactionStatus'] . '</td>
                                                <td>' . $row['lorry_plate_no1'] . '</td>';
                                                
                                                if($_POST['status'] == 'Sales' || $_POST['status'] == 'Misc'){
                                                    $message .= '<td>' . $row['customer_code'] . '</td>';
                                                    $message .= '<td>' . $row['customer_name'] . '</td>';
                                                }
                                                else{
                                                    $message .= '<td>' . $row['supplier_code'] . '</td>';
                                                    $message .= '<td>' . $row['supplier_name'] . '</td>';
                                                }
                                                
                                                $message .= '<td>' . (($row['transaction_status'] == 'Sales' || $row['transaction_status'] == 'Misc') ? $row['product_code'] : $row['raw_mat_code']) . '</td>
                                                <td>' . (($row['transaction_status'] == 'Sales' || $row['transaction_status'] == 'Misc') ? $row['product_name'] : $row['raw_mat_name']) . '</td>
                                                <td>' . $row['destination_code'] . '</td>
                                                <td>' . $row['destination'] . '</td>
                                                <td>' . $row['purchase_order'] . '</td>
                                                <td>' . $row['delivery_no'] . '</td>
                                                <td>' . $row['container_no'] . '</td>
                                                <td>' . $row['seal_no'] . '</td>
                                                <td>' . (!empty($row['order_weight']) ? number_format($row['order_weight'] / 1000, 2) : '') . '</td>
                                                <td>' . (!empty($row['supplier_weight']) ? number_format($row['supplier_weight'] / 1000, 2) : '') . '</td>
                                                <td>' . number_format($row['gross_weight1']/1000, 2) . '</td>
                                                <td>' . number_format($row['tare_weight1']/1000, 2) . '</td>
                                                <td>' . number_format($row['nett_weight1']/1000, 2) . '</td>
                                                <td>' . $formattedGrossWeightDate . '</td>
                                                <td>' . $formattedTareWeightDate . '</td>
                                                <td>' . (!empty($row['gross_weight2']) ? number_format($row['gross_weight2'] / 1000, 2) : '') . '</td>
                                                <td>' . (!empty($row['tare_weight2']) ? number_format($row['tare_weight2'] / 1000, 2) : '') . '</td>
                                                <td>' . (!empty($row['nett_weight2']) ? number_format($row['nett_weight2'] / 1000, 2) : '') . '</td>
                                                <td>' . $formattedGrossWeightDate2 . '</td>
                                                <td>' . $formattedTareWeightDate2 . '</td>
                                                <td>' . (!empty($row['weight_different']) ? number_format($row['weight_different'] / 1000, 2) : '') . '</td>
                                                <td>' . number_format($row['final_weight']/1000, 2) . '</td>
                                                <td>' . $row['created_by'] . '</td>
                                            </tr>';
                                    
                                            // Calculate subtotals
                                            $totalGross += (float)$row['gross_weight1'];
                                            $totalTare += (float)$row['tare_weight1'];
                                            $totalNet += (float)$row['nett_weight1'];
                                        }
                                    
                                        // Add product-wise subtotal
                                        $message .= '<tr>
                                            <th style="font-size: 10px;" colspan="18">Subtotal (' . $product . ')</th>
                                            <th style="border:1px solid black;font-size: 9px;">' . number_format($totalGross /1000, 2). '</th>
                                            <th style="border:1px solid black;font-size: 9px;">' . number_format($totalTare/1000, 2) . '</th>
                                            <th style="border:1px solid black;font-size: 9px;">' . number_format($totalNet/1000, 2) . '</th>
                                        </tr>';
                                    
                                        // Add to grand total
                                        $grandTotalGross += $totalGross;
                                        $grandTotalTare += $totalTare;
                                        $grandTotalNet += $totalNet;
                                    }
                                    
                                    $message .= '</tbody>
                                        <tfoot>
                                            <tr>
                                                <th style="font-size: 10px;" colspan="18">Grand Total</th>
                                                <th style="border:1px solid black;font-size: 9px;border:1px solid black;">'.number_format($grandTotalGross/1000, 2).'</th>
                                                <th style="border:1px solid black;font-size: 9px;border:1px solid black;">'.number_format($grandTotalTare/1000, 2).'</th>
                                                <th style="border:1px solid black;font-size: 9px;border:1px solid black;">'.number_format($grandTotalNet/1000, 2).'</th>
                                            </tr>
                                        </tfoot>';
                                    $message .= '</tbody>';
                                    
                                $message .= '</table>
                            </body>
                        </html>';

                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => $message
                    )
                );
            }
        }
        else{
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something Goes Wrong"
                ));
        }
    }
}
else{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    ); 
}

?>