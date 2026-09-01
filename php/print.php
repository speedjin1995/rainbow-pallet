<?php
session_start();
require_once 'db_connect.php';
include 'phpqrcode/qrlib.php';

$language = isset($_POST['prePrint']) ? $_POST['prePrint'] : $_SESSION['language'];
$languageArray = $_SESSION['languageArray'];
$compids = '1';
$compname = 'SYNCTRONIX TECHNOLOGY (M) SDN BHD';
$compreg = '123456789-X';
$compaddress = 'No.34, Jalan Bagan 1,';
$compaddress2 = 'Taman Bagan,';
$compaddress3 = '13400 Butterworth. Penang. Malaysia.';
$compphone = '6043325822';
$compiemail = 'admin@synctronix.com.my';
 
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
}

// Format Weight String
function formatWeight($weight){
    if ($weight != 0){
        $formatted = number_format(ltrim($weight, '0'), 2, '.', ',');
        $formatted = preg_replace('/\.00$/', '', $formatted);    
    }else{
        $formatted = $weight;
    }

    return $formatted;
}

if(isset($_POST['userID'], $_POST["file"], $_POST['isEmptyContainer'])){
    $stmt = $db->prepare("SELECT * FROM Company WHERE id=?");
    $stmt->bind_param('s', $compids);
    $stmt->execute();
    $result1 = $stmt->get_result();
    $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);
            
    if ($row = $result1->fetch_assoc()) {
        $compname = $row['name'];
        $compreg = $row['company_reg_no'];
        $compaddress = $row['address_line_1'];
        $compaddress2 = $row['address_line_2'];
        $compaddress3 = $row['address_line_3'];
        $compphone = $row['phone_no'];
        $compiemail = $row['fax_no'];
    }

    if($_POST["file"] == 'weight'){
        //i remove this because both(billboard and weight) also call this print page.
        //AND weight.pStatus = 'Pending'

        if ($_POST['isEmptyContainer'] == 'Y'){
            $sql = "SELECT * FROM Weight_Container WHERE id=?";
        }else{
            $sql = "SELECT * FROM Weight WHERE id=?";
        }

        if ($select_stmt = $db->prepare($sql)) {
            $select_stmt->bind_param('s', $id);

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
                    
                if ($row = $result->fetch_assoc()) {
                    $customer = '';
                    $customerR = '';
                    $customerP = '';
                    $customerA = '';
                    $customerA2 = '';
                    $customerA3 = '';
                    $customerE = '';

                    $product = '';
                    $price = '';
                    $variance = '';
                    $high = '';
                    $low = '';

                    $transactionDate = date("d/m/Y", strtotime($row['transaction_date']));
                    $grossWeightTime = date("d/m/Y - H:i:s", strtotime($row['gross_weight1_date']));
                    $tareWeightTime = date("d/m/Y - H:i:s", strtotime($row['tare_weight1_date']));

                    $orderSuppWeight = 0;
                    $weightDifference = $row['weight_different'];
                    $finalWeight = $row['final_weight'];

                    $grossWeightTime2 = $row['gross_weight2_date'] != null ? date("d/m/Y - H:i:s", strtotime($row['gross_weight2_date'])) : "";
                    $tareWeightTime2 = $row['tare_weight2_date'] != null ? date("d/m/Y - H:i:s", strtotime($row['tare_weight2_date'])) : "";

                    if ($row['transaction_status'] == 'Sales'){
                        $transacationStatus = 'dispatch_code';
                    }elseif ($row['transaction_status'] == 'Purchase'){
                        $transacationStatus = 'receiving_code';
                    }elseif ($row['transaction_status'] == 'Local'){
                        $transacationStatus = 'internal_transfer_code';
                    }else {
                        $transacationStatus = 'miscellaneous_code';
                    }

                    if($row['transaction_status'] == 'Purchase' || $row['transaction_status'] == 'Local'){
                        $cid = $row['supplier_code'];
                        $orderSuppWeight = floatval($row['supplier_weight']);

                        if ($update_stmt = $db->prepare("SELECT * FROM Supplier WHERE supplier_code=?")) {
                            $update_stmt->bind_param('s', $cid);
                            
                            // Execute the prepared query.
                            if ($update_stmt->execute()) {
                                $result2 = $update_stmt->get_result();
                                
                                if ($row2 = $result2->fetch_assoc()) {
                                    $customer = $row2['name'];
                                    $customerR = $row2['company_reg_no'] ?? '';
                                    $customerP = $row2['phone_no'] ?? '-';
                                    $customerA = $row2['address_line_1'];
                                    $customerA2 = $row2['address_line_2'];
                                    $customerA3 = $row2['address_line_3'];
                                    $customerE = $row2['fax_no'] ?? '-';
                                }
                            }
                        }

                        $pid = $row['raw_mat_code'];
                    
                        if ($update_stmt2 = $db->prepare("SELECT * FROM Raw_Mat WHERE raw_mat_code=?")) {
                            $update_stmt2->bind_param('s', $pid);
                            
                            // Execute the prepared query.
                            if ($update_stmt2->execute()) {
                                $result3 = $update_stmt2->get_result();
                                
                                if ($row3 = $result3->fetch_assoc()) {
                                    $product = $row3['name'];
                                    $variance = $row3['variance'] ?? '';
                                    $high = $row3['high'] ?? '0';
                                    $low = $row3['low'] ?? '0';
                                    $price = $row3['price'] ??  '0.00';
                                }
                            }
                        }
                    }
                    else{
                        $cid = $row['customer_code'];
                        $orderSuppWeight = floatval($row['order_weight']);
                    
                        if ($update_stmt = $db->prepare("SELECT * FROM Customer WHERE customer_code=?")) {
                            $update_stmt->bind_param('s', $cid);
                            
                            // Execute the prepared query.
                            if ($update_stmt->execute()) {
                                $result2 = $update_stmt->get_result();
                                
                                if ($row2 = $result2->fetch_assoc()) {
                                    $customer = $row2['name'];
                                    $customerR = $row2['company_reg_no'] ?? '';
                                    $customerP = $row2['phone_no'] ?? '-';
                                    $customerA = $row2['address_line_1'];
                                    $customerA2 = $row2['address_line_2'];
                                    $customerA3 = $row2['address_line_3'];
                                    $customerE = $row2['fax_no'] ?? '-';
                                }
                            }
                        }

                        $pid = $row['product_code'];
                    
                        if ($update_stmt2 = $db->prepare("SELECT * FROM Product WHERE product_code=?")) {
                            $update_stmt2->bind_param('s', $pid);
                            
                            // Execute the prepared query.
                            if ($update_stmt2->execute()) {
                                $result3 = $update_stmt2->get_result();
                                
                                if ($row3 = $result3->fetch_assoc()) {
                                    $product = $row3['name'];
                                    $variance = $row3['variance'] ?? '';
                                    $high = $row3['high'] ?? '0';
                                    $low = $row3['low'] ?? '0';
                                    $price = $row3['price'] ??  '0.00';
                                }
                            }
                        }
                    }

                    $message = 
                    '<html>
                        <head>
                            <style>
                                @media print {
                                    @page {
                                        size: A5 landscape;
                                        margin-left: 0.5in;
                                        margin-right: 0.5in;
                                        margin-top: 0.1in;
                                        margin-bottom: 0.1in;
                                        padding-left: 0.2in;
                                        padding-right: 0.2in;
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

                                .table-border {
                                    border: 1px solid #000000;
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
                                <tr>
                                    <td style="width: 70%;">
                                        <p style="font-size: 14px;">
                                            <span style="font-weight: bold;font-size: 16px; margin-bottom: 10px; display: inline-block;">'.$compname.'</span><br>
                                            <span> Reg No.: '.$compreg.'</span><br>
                                            <span>'.$compaddress.'</span><br>
                                            <span>'.$compaddress2.'</span><br>
                                            <span>'.$compaddress3.'</span><br>
                                            <span>Tel/Fax: '.$compphone.' / '.$compiemail.'</span>
                                        </p>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <p style="vertical-align: top; font-size: 14px;">
                                            <span style="font-size: 24px; font-weight: bold; margin-bottom: 10px; display: inline-block;">'. $languageArray[$transacationStatus][$language] . '' . $languageArray['slip_code'][$language].'</span><br>
                                            <span>'. $languageArray['ticket_no_code'][$language].' &nbsp;:&nbsp; <b>'.$row['transaction_id'].'</b></span><br>
                                            <span>'. $languageArray['date_code'][$language].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left: 1.5px;">:&nbsp;&nbsp;'.$transactionDate.'</span><br>
                                            <span>'. $languageArray['do_no_code'][$language].' &nbsp;&nbsp;&nbsp;&nbsp;</span><span>:&nbsp;&nbsp;'.$row['delivery_no'].'</span><br>
                                            <span>'. $languageArray['po_no_code'][$language].' &nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left:2.5px">:&nbsp;&nbsp;'.$row['purchase_order'].'</span><br>
                                        </p>
                                    </td>
                                </tr>
                                <tr style="visibility:hidden;">
                                    <td style="font-size: 3px;">Placeholder for empty space</td>
                                </tr>
                                <tr style="border-top: 1px solid black;">
                                    <td style="vertical-align: top;">
                                        <p style="margin-top: 5px; font-size: 14px;">
                                            <span">'.$languageArray['customer_code'][$language].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left: 23px">:&nbsp; <b>'.$customer.'</b></span><br>
                                            <span">'.$languageArray['transporter_code'][$language].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left: 12.5px">:&nbsp; '.$row['transporter'].'</span><br>
                                            <span">'.$languageArray['destination_code'][$language].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left: 12.5px">:&nbsp; '.$row['destination'].'</span>
                                        </p>';

                                        // $message .= '
                                        //     <br>
                                        //     <!-- <span><b>Net Weight &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left: 17.5px">:&nbsp; '.($finalWeight ? formatWeight($finalWeight).' kg' : '-').'</b></span><br>-->
                                        //     <span">Variance &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left: 12.5px">:&nbsp; '.($weightDifference ? formatWeight($weightDifference).' kg' : '-').'</span><br>
                                        //     <span>Product &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left: 21px">:&nbsp; '.($row['transaction_status'] == 'Sales' || $row['transaction_status'] == 'Misc' ? $row['product_code'] . ' - ' . $row['product_name'] : $row['raw_mat_code'] . ' - ' . $row['raw_mat_name']) .'</span><br>';

                                        //     if ($row['weight_type'] == 'Different Container' && $_POST['isEmptyContainer'] == 'N'){
                                        //         $message .= '<span>Destination &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="margin-left: 3px">:&nbsp;</span>'.$row['destination_code']. ' - '.$row['destination'].'</span>';
                                        //     }

                                        // $message .= '</p>';

                                        $message .= '
                                    </td>
                                    <td style="vertical-align: top;">
                                        <p style="margin-top: 5px; font-size: 14px;">
                                            <span">'.$languageArray['order_weight_code'][$language].'</span><span style="margin-left: 10px">:&nbsp; <b>'.$row['order_weight'].'</b></span><br>
                                            <span">'.$languageArray['variance_code'][$language].'</span><span style="margin-left: 37px">:&nbsp; '.$row['weight_different'].'</span><br>
                                            <span">% '.$languageArray['variance_code'][$language].'</span><span style="margin-left: 22px">:&nbsp; '.$row['weight_different_perc'].'</span>
                                        </p>
                                    </td>
                                </tr>';
                            $message .= '
                            </table>';

                            if ($row['weight_type'] == 'Different Container' && $_POST['isEmptyContainer'] == 'N'){
                                $message .= '
                                    <table style="width:100%; border:0px solid black; margin-top: 10px;">
                                        <tr>
                                            <th style="border:1px solid black;">New Empty Entrance Bin</th>
                                        </tr>
                                        <tr>
                                            <td style="border:1px solid black;">'.$row['replacement_container'].'</td>
                                        </tr>
                                        <tr>
                                            <th style="border:1px solid black;">Weight</th>
                                        </tr>
                                        <tr>
                                            <td style="border:1px solid black;">'.$row['empty_container2_weight'].'</td>
                                        </tr>
                                    </table>
                                ';
                            }else{
                                $message .= '
                                    <table style="width:100%; border:0px solid black; margin-top: 10px;">
                                        <tr style="font-size: 14px;text-align: center;">
                                            <th width="25%" style="border:1px solid black;">'.$languageArray['container_no1_code'][$language].'</th>
                                            <th width="25%" style="border:1px solid black;">'.$languageArray['seal_no1_code'][$language].'</th>
                                            <th width="25%" style="border:1px solid black;">'.$languageArray['container_no2_code'][$language].'</th>
                                            <th width="25%" style="border:1px solid black;">'.$languageArray['seal_no2_code'][$language].'</th>
                                        </tr>
                                        <tr style="font-size: 14px;text-align: center;">
                                            <td style="border:1px solid black;">'.(!empty($row["container_no"]) ? $row["container_no"] : '&nbsp;').'</td>
                                            <td style="border:1px solid black;">'.$row["seal_no"].'</td>
                                            <td style="border:1px solid black;">'.(!empty($row["container_no2"]) ? $row["container_no2"] : '&nbsp;').'</td>
                                            <td style="border:1px solid black;">'.$row["seal_no2"].'</td>    
                                        </tr>
                                    </table>
                                ';
                            }
                            
                            if($row['weight_type'] == 'Container' && $_POST['isEmptyContainer'] == 'N'){
                                $message .= '
                                <table style="width:100%; border:0px solid black; margin-top: 10px;">
                                    <tr style="font-size: 14px;text-align: center;">
                                        <th style="border:1px solid black;">Incoming Date/Time</th>
                                        <th style="border:1px solid black;">Outgoing Date/Time</th>
                                        <th colspan="2" style="border:1px solid black;">Prime Mover No. & Weight (kg)</th>
                                        <th style="border:1px solid black;">Tare (kg)</th>
                                        <th style="border:1px solid black;">Nett (kg)</th>
                                    </tr>
                                    <tr style="font-size: 14px;text-align: center;">
                                        <td style="border:1px solid black;">'.$grossWeightTime.'</td>
                                        <td style="border:1px solid black;">'.$tareWeightTime.'</td>
                                        <td style="border:1px solid black;">'.$row['lorry_plate_no1'].'</td>
                                        <td style="border:1px solid black;">'.formatWeight($row['gross_weight1']).'</td>
                                        <td style="border:1px solid black;">'.formatWeight($row['tare_weight1']).' kg</td>
                                        <td style="border:1px solid black;">'.formatWeight($row['nett_weight1']).' kg</td>
                                    </tr>
                                    <tr style="font-size: 14px;text-align: center;">
                                        <td style="border:1px solid black;">'.$grossWeightTime2.'</td>
                                        <td style="border:1px solid black;">'.$tareWeightTime2.'</td>
                                        <td style="border:1px solid black;">'.$row['lorry_plate_no2'].'</td>
                                        <td style="border:1px solid black;">'.formatWeight($row['gross_weight2']).'</td>
                                        <td style="border:1px solid black;">'.formatWeight($row['tare_weight2']).' kg</td>
                                        <td style="border:1px solid black;">'.formatWeight($row['nett_weight2']).' kg</td>
                                    </tr>
                                    <tr style="font-size: 14px;text-align: center;">
                                        <td colspan="4" style="text-align: left;"><b>Transporter &nbsp;:&nbsp;</b> <span style="margin-left: 10px">'.$row['transporter'].'</span></td>
                                        <td style="border:1px solid black;">Final Weight</td>
                                        <td style="border:1px solid black;">'.formatWeight(abs((int)$row['nett_weight1'] - (int)$row['nett_weight2'])).' kg</td>
                                    </tr>
                                    <tr style="font-size: 14px;text-align: center;">
                                        <td colspan="4" style="text-align: left;"><b>Destination &nbsp&nbsp;:&nbsp;</b> <span style="margin-left: 10px">'.$row['destination'].'</span></td>
                                        <td style="border:1px solid black;">Reduce Weight</td>
                                        <td style="border:1px solid black;">'.formatWeight($row['reduce_weight']).' kg</td>
                                    </tr>
                                    <tr style="font-size: 14px;text-align: center;font-weight:bold;">
                                        <td colspan="4" style="text-align: left;">Remarks &nbsp&nbsp&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp; <span style="margin-left: 10px">'.$row['remarks'].'</span></td>
                                        <td style="border:1px solid black;">Nett Weight</td>
                                        <td style="border:1px solid black;">'.formatWeight($row['final_weight']).' kg</td>
                                    </tr>
                                </table>';
                            }
                            else if ($row['weight_type'] == 'Different Container' && $_POST['isEmptyContainer'] == 'N'){
                                $message .= '
                                    <table style="width:100%; border: 1px solid black; text-align: left; font-size: 14px; margin-top: 5px;">
                                        <tr style="text-align: center; border: 1px solid black;">
                                            <th rowspan="2" class="table-border" width="10%">Bin</th>
                                            <th rowspan="2" class="table-border" width="25%">Date/Time</th>
                                            <th rowspan="2" class="table-border">Vehicle</th>
                                            <th rowspan="2" class="table-border" width="10%">Gross Weight</th>
                                            <th class="table-border" colspan="2">Tare Weight</th>
                                            <th rowspan="2" class="table-border">Nett Weight</th>
                                        </tr>
                                        <tr style="text-align: center;">
                                            <th class="table-border">Vehicle</th>
                                            <th class="table-border">Bin</th>
                                        </tr>
                                        <tr style="text-align: center;">
                                            <td class="table-border">'.$row['container_no'].'</td>
                                            <td class="table-border">
                                                In: '.$row['gross_weight1_date'].'<br>
                                                Out: '.$row['tare_weight2_date'].'
                                            </td>
                                            <td class="table-border">
                                                In: '.$row['lorry_plate_no1'].'<br>
                                                Out: '.$row['lorry_plate_no2'].'
                                            </td>
                                            <td class="table-border">
                                                '.$row['gross_weight1'].' kg<br>
                                                '.$row['tare_weight2'].' kg
                                            </td>
                                            <td class="table-border">
                                                '.$row['tare_weight1'].' kg<br>
                                                '.$row['lorry_no2_weight'].' kg
                                            </td>
                                            <td class="table-border">
                                                -<br>
                                                '.$row['nett_weight1'].' kg
                                            </td>
                                            <td class="table-border">
                                                -<br>
                                                '.$row['nett_weight2'].' kg
                                            </td>
                                        </tr>
                                        <!--<tr style="text-align: center;">
                                            <td class="table-border">'.$row['replacement_container'].'</td>
                                            <td class="table-border">
                                                Date In: '.$row['gross_weight2_date'].'
                                            </td>
                                            <td class="table-border">
                                                In: '.$row['lorry_plate_no2'].'
                                            </td>
                                            <td class="table-border">
                                                '.$row['gross_weight2'].' kg
                                            </td>
                                            <td class="table-border">
                                                '.$row['lorry_no2_weight'].' kg
                                            </td>
                                            <td class="table-border">
                                                '.$row['empty_container2_weight'].' kg
                                            </td>
                                            <td class="table-border">
                                                -
                                            </td>
                                        </tr>-->
                                        <tr style="text-align: center;">
                                            <td class="table-border" colspan="6" style="text-align: right; padding-right: 20px;">
                                                Final Weight
                                            </td>
                                            <td class="table-border">
                                                '.$row['final_weight'].' kg
                                            </td>
                                        </tr>
                                    </table>
                                    <div style="margin-top: 5px; font-size: 14px; margin-bottom: 60px;">
                                        <span>Transporter&nbsp;&nbsp;:&nbsp;&nbsp; '.$row['transporter'].'</span><br>
                                        <span>Remarks&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;'.$row['remarks'].'</span>
                                    </div>
                                ';
                            }
                            else{
                                $message .= '<br>
                                <table style="width:100%; border:0px solid black; margin-top: -10px;">
                                    <tr>
                                        <th style="border:1px solid black;font-size: 18px;text-align: center;" width="20%">'.$languageArray['vehicle_no_code'][$language].'</th>
                                        <th colspan="2" style="border:1px solid black;font-size: 18px;text-align: center;" width="30%">'.$languageArray['product_description_code'][$language].'</th>
                                        <th style="border:1px solid black;font-size: 18px;text-align: center;" width="25%">'.$languageArray['datetime_code'][$language].'</th>
                                        <th colspan="2" style="border:1px solid black;font-size: 18px;text-align: center;" width="20%">'.$languageArray['weight_code'][$language].' ('.$languageArray['kg_code'][$language].')</th>
                                    </tr>
                                    <tr style="font-size: 16px;text-align: center;">
                                        <td rowspan="2" style="border:1px solid black;">'.$row['lorry_plate_no1'].'</td>';

                                        if ($row['transaction_status'] == 'Purchase' || $row['transaction_status'] == 'Local'){
                                            $message .= '<td rowspan="2" colspan="2" style="border:1px solid black;">'.$row['raw_mat_name'].'</td>';
                                        }else{
                                            $message .= '<td rowspan="2" colspan="2" style="border:1px solid black;">'.$row['product_name'].'</td>';
                                        }

                                    $message .= '    
                                        <td style="border:1px solid black;">'.$grossWeightTime.'</td>
                                        <td style="border:1px solid black; font-weight: bold;">'.$languageArray['in_code'][$language].'</td>
                                        <td style="border:1px solid black;">'.formatWeight($row['gross_weight1']).' kg</td>
                                    </tr>
                                    <tr style="font-size: 16px;text-align: center;">
                                        <td style="border:1px solid black;">'.$tareWeightTime.'</td>
                                        <td style="border:1px solid black; font-weight: bold;">'.$languageArray['out_code'][$language].'</td>
                                        <td style="border:1px solid black;">'.formatWeight($row['tare_weight1']).' kg</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">'.$languageArray['remarks_code'][$language].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp; <span style="margin-left: 10px">'.$row['remarks'].'</span></td>
                                        <td style="border:1px solid black;font-size: 16px;text-align: center;">'.$languageArray['reduce_code'][$language].'</td>
                                        <td style="border:1px solid black;font-size: 16px;text-align: center;">'.formatWeight($row['reduce_weight']).' kg</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td style="border:1px solid black;font-size: 16px;font-weight:bold;text-align: center;">'.$languageArray['nett_code'][$language].'</td>
                                        <td style="border:1px solid black;font-size: 16px;font-weight:bold;text-align: center;">'.formatWeight($row['final_weight']).' kg</td>
                                    </tr>
                                </table><br>';                           
                            }
                            
                            $message .= '
                            <table style="width: 100%; position: fixed; bottom: 0; left: 0;">
                                <tr>
                                    <td style="vertical-align: top; font-size: 14px; width: 25%;">
                                        <hr width="100%" style="margin-left: 0; text-align: left;">
                                        <span>'.$languageArray['first_weight_by_code'][$language].': '.$row['gross_weight_by1'].'<br> '.$languageArray['second_weight_by_code'][$language].': '.$row['tare_weight_by1'].'</span>
                                    </td>
                                    <td style="width: 2%;"></td>
                                    <td style="vertical-align: top; font-size: 14px; width: 25%;">
                                        <hr width="100%" style="margin-left: 0; text-align: left;">
                                        <span>'.$languageArray['acknowledge_by_admin_code'][$language].'</span>
                                    </td>
                                    <td style="width: 2%;"></td>
                                    <td style="vertical-align: top; font-size: 14px; width: 25%;">
                                        <hr width="100%" style="margin-left: 0; text-align: left;">
                                        <span>'.$languageArray['received_by_code'][$language].'</span><br>
                                        <span>'.$languageArray['name_code'][$language].': </span><br>
                                        <span>I/C: </span>
                                    </td>
                                </tr>
                            </table>
                        </body>
                    </html>';

                    echo json_encode(
                        array(
                            "status" => "success",
                            "message" => $message
                        )
                    );
                }
                else{
                    echo json_encode(
                        array(
                            "status" => "failed",
                            "message" => 'Unable to read data'
                        )
                    );
                }
                
                
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
    else{
        $empQuery = "select count.id, count.serialNo, vehicles.veh_number, lots.lots_no, count.batchNo, count.invoiceNo, count.deliveryNo, 
        count.purchaseNo, customers.customer_name, products.product_name, packages.packages, count.unitWeight, count.tare, count.totalWeight, 
        count.actualWeight, count.currentWeight, units.units, count.moq, count.dateTime, count.unitPrice, count.totalPrice,count.totalPCS, 
        count.remark, status.status from count, vehicles, packages, lots, customers, products, units, status WHERE 
        count.vehicleNo = vehicles.id AND count.package = packages.id AND count.lotNo = lots.id AND count.customer = customers.id AND 
        count.productName = products.id AND status.id=count.status AND units.id=count.unit AND count.deleted = '0' AND count.id=?";

        if ($select_stmt = $db->prepare($empQuery)) {
            $select_stmt->bind_param('s', $id);

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
                

                if ($row = $result->fetch_assoc()) {
                    $message = '<html>
                    <head>
                        <title>Html to PDF</title>
                    </head>
                    <body>
                        <h3>'.$compname.'</h3>
                        <p>No.34, Jalan Bagan 1, <br>Taman Bagan, 13400 Butterworth.<br> Penang. Malaysia.</p>
                        <p>TEL: 6043325822 | EMAIL: admin@synctronix.com.my</p><hr>
                        <table style="width:100%">
                        <tr>
                            <td>
                                <h4>CUSTOMER NAME: '.$row['customer_name'].'</h4>
                            </td>
                            <td>
                                <h4>SERIAL NO: '.$row['serialNo'].'</h4>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>No.34, Jalan Bagan 1, <br>Taman Bagan, <br>13400 Butterworth. Penang. Malaysia.</p>
                            </td>
                            <td>
                                <h4>Status: '.$row['status'].'</h4>
                                <p>Date: 23/03/2022<br>Delivery No: '.$row['deliveryNo'].'</p>
                            </td>
                        </tr>
                        </table>
                        <table style="width:100%; border:1px solid black;">
                        <tr>
                            <th style="border:1px solid black;">Vehicle No.</th>
                            <th style="border:1px solid black;">Product Name</th>
                            <th style="border:1px solid black;">Date & Time</th>
                            <th style="border:1px solid black;">Weight</th>
                        </tr>
                        <tr>
                            <td style="border:1px solid black;">'.$row['veh_number'].'</td>
                            <td style="border:1px solid black;">'.$row['product_name'].'</td>
                            <td style="border:1px solid black;">'.$row['dateTime'].'</td>
                            <td style="border:1px solid black;">'.$row['unitWeight'].' '.$row['units'].'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td style="border:1px solid black;">Tare Weight</td>
                            <td style="border:1px solid black;">'.$row['tare'].' '.$row['units'].'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td style="border:1px solid black;">Net Weight</td>
                            <td style="border:1px solid black;">'.$row['actualWeight'].' '.$row['units'].'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td style="border:1px solid black;">M.O.Q</td>
                            <td style="border:1px solid black;">'.$row['moq'].'</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td style="border:1px solid black;">Total Weight</td>
                            <td style="border:1px solid black;">'.$row['totalWeight'].' '.$row['units'].'</td>
                        </tr>
                        </table>
                        <p>Remark: '.$row['remark'].'</p>
                    </body>
                </html>';
                }
                
                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => $message
                    ));
            }
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