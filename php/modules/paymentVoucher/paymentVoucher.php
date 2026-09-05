<?php
session_start();
require_once '../../db_connect.php';

if(!isset($_SESSION['id'])){
	echo '<script type="text/javascript">location.href = "../login.php";</script>'; 
} else{
	$username = $_SESSION["username"];
}

// Check if the user is already logged in, if yes then redirect him to index page
$id = $_SESSION['id'];
// Processing form data when form is submitted
if (isset($_POST['voucherDate'], $_POST['pvType']) && !empty($_POST['voucherDate']) && !empty($_POST['pvType'])) {
    if (empty($_POST["voucherDate"])) {
        $voucherDate = null;
    } else {
        $voucherDate = DateTime::createFromFormat('d-m-Y', $_POST["voucherDate"])->format('Y-m-d H:i:s');
        $voucherDateOnly = DateTime::createFromFormat('d-m-Y', $_POST["voucherDate"])->format('Y-m-d');
    }

    if (empty($_POST["voucherNo"])) {
        $name = 'payment_voucher';
		if($update_stmt2 = $db->prepare("SELECT * FROM Running_No_Setup WHERE document=?")){
			$update_stmt2->bind_param('s', $name);

			if (! $update_stmt2->execute()) {
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Something went wrong when querying running numbers"
                    )
                ); 
            }
            else{
                $result2 = $update_stmt2->get_result();
				if ($row2 = $result2->fetch_assoc()) {
                    $voucherNo = $row2['document_name'];

                    $charSize = strlen($row2['value']);
                    $value = $row2['value'];
                    for($i=0; $i<(4-(int)$charSize); $i++){
                        $voucherNo.='0';  // PV0000
                    }
                    $voucherNo .= $value;
				} 
            }
		}
    } else {
        $voucherNo = trim($_POST["voucherNo"]);
    }
    
    if (empty($_POST["invoiceNo"])) {
        $name = 'invoice_running_no';
		if($update_stmt2 = $db->prepare("SELECT * FROM Running_No_Setup WHERE document=?")){
			$update_stmt2->bind_param('s', $name);

			if (! $update_stmt2->execute()) {
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Something went wrong when querying running numbers"
                    )
                ); 
            }
            else{
                $result2 = $update_stmt2->get_result();
				if ($row2 = $result2->fetch_assoc()) {
                    $invoiceNo = $row2['document_name'];

                    $charSize = strlen($row2['value']);
                    $value = $row2['value'];
                    for($i=0; $i<(4-(int)$charSize); $i++){
                        $invoiceNo.='0';  // INV0000
                    }
                    $invoiceNo .= $value;
				} 
            }
		}
    } else {
        $invoiceNo = trim($_POST["invoiceNo"]);
    }

    if (empty($_POST["pvType"])) {
        $pvType = null;
    } else {
        $pvType = trim($_POST["pvType"]);
    }

    if (empty($_POST["pvId"])) {
        $pvId = null;
    } else {
        $pvId = trim($_POST["pvId"]);
    }

    if (empty($_POST["supplierId"])) {
        $supplierId = null;
    } else {
        $supplierId = trim($_POST["supplierId"]);
    }

    if (empty($_POST["transactionFromDate"])) {
        $transactionFromDate = null;
    } else {
        $transactionFromDate = DateTime::createFromFormat('d-m-Y', $_POST["transactionFromDate"])->format('Y-m-d H:i:s');
    }

    if (empty($_POST["transactionToDate"])) {
        $transactionToDate = null;
    } else {
        $transactionToDate = DateTime::createFromFormat('d-m-Y', $_POST["transactionToDate"])->format('Y-m-d H:i:s');
    }

    if (empty($_POST["unitPrice"])) {
        $unitPrice = 0;
    } else {
        $unitPrice = trim($_POST["unitPrice"]);
    }

    if (empty($_POST["tax"])) {
        $tax = 0;
    } else {
        $tax = trim($_POST["tax"]);
    }

    if (empty($_POST["totalDeductions"])) {
        $totalDeductions = 0;
    } else {
        $totalDeductions = trim($_POST["totalDeductions"]);
    }

    if (empty($_POST["totalAdditions"])) {
        $totalAdditions = 0;
    } else {
        $totalAdditions = trim($_POST["totalAdditions"]);
    }

    if (empty($_POST["deductions"])) {
        $deductions = 0;
    } else {
        $deductions = trim($_POST["deductions"]);
    }

    if (empty($_POST["additions"])) {
        $additions = 0;
    } else {
        $additions = trim($_POST["additions"]);
    }

    if (empty($_POST["subtotal"])) {
        $subtotal = 0;
    } else {
        $subtotal = trim($_POST["subtotal"]);
    }

    if (empty($_POST["finalAmount"])) {
        $finalAmount = 0;
    } else {
        $finalAmount = trim($_POST["finalAmount"]);
    }

    // Deduction and Addition Calculation
    $deductionDesc = isset($_POST['deduction_desc']) ? $_POST['deduction_desc'] : [];
    $deductionAmount = isset($_POST['deduction_amount']) ? $_POST['deduction_amount'] : [];
    $additionDesc = isset($_POST['addition_desc']) ? $_POST['addition_desc'] : [];
    $additionAmount = isset($_POST['addition_amount']) ? $_POST['addition_amount'] : [];
    $success = true;
    
    if ($pvType == 'Term'){
        // Weight Details
        $selectedIds = isset($_POST['selected']) ? $_POST['selected'] : [];
        $tiedIds = isset($_POST['tied']) ? $_POST['tied'] : [];
        $allIds = $_POST['id'];
        $unitPrices = $_POST['unit_price'];
        $subTotals = $_POST['sub_total'];
        $ssts = $_POST['sst'];
        $totalPrices = $_POST['total_price'];
        $nettWeights = $_POST['nett_weight'];

        // Merge selected and tied IDs to get all rows that need updating
        $rowsToUpdate = array_unique(array_merge($selectedIds, $tiedIds));

        // Calculate totals from selected and tied rows
        $totalNettWeight = 0;
        for ($i = 0; $i < count($allIds); $i++) {
            $weightId = $allIds[$i];
            
            // Only process rows that are selected or tied
            if (in_array($weightId, $rowsToUpdate)) {
                $rowUnitPrice = isset($unitPrices[$i]) ? floatval($unitPrices[$i]) : 0;
                $rowSubTotal = isset($subTotals[$i]) ? floatval($subTotals[$i]) : 0;
                $rowSst = isset($ssts[$i]) ? floatval($ssts[$i]) : 0;
                $rowTotalPrice = isset($totalPrices[$i]) ? floatval($totalPrices[$i]) : 0;
                $rowNettWeight = isset($nettWeights[$i]) ? floatval($nettWeights[$i]) : 0;

                $totalNettWeight += $rowNettWeight;
                
                if ($update_stmt = $db->prepare("UPDATE Weight SET unit_price=?, sub_total=?, sst=?, total_price=?, modified_by=? WHERE id=?")) {
                    $update_stmt->bind_param('ddddsi', $rowUnitPrice, $rowSubTotal, $rowSst, $rowTotalPrice, $username, $weightId);
                    
                    if (!$update_stmt->execute()) {
                        $success = false;
                        break;
                    }
                    $update_stmt->close();
                } else {
                    $success = false;
                    break;
                }
            }
        }
    }
    
    if ($success) {
        // Build JSON objects for each deduction record
        $deductionRecords = [];
        if (!empty($deductionDesc)){
            foreach ($deductionDesc as $key => $desc) {
                if (!empty($desc) && isset($deductionAmount[$key])) {
                    $deductionRecords[] = [
                        "deduction_desc" => $desc,
                        'deduction_reference' => '',
                        "deduction_amount" => $deductionAmount[$key]
                    ];
                }
            }
        }

        $additionRecords = [];
        if (!empty($additionDesc)){
            foreach ($additionDesc as $key => $desc) {
                if (!empty($desc) && isset($additionAmount[$key])) {
                    $additionRecords[] = [
                        "addition_desc" => $desc,
                        "addition_amount" => $additionAmount[$key]
                    ];
                }
            }
        }

        // Checking to see if there are existing payment voucher record
        if (!empty($pvId) && $pvId != null && $pvId != 'null' && $pvId != '') {
            // Update existing record
            if ($update_payment_stmt = $db->prepare("UPDATE Payment_Voucher SET type=?, supplier_id=?, voucher_date=?, from_date=?, to_date=?, unit_price=?, tax=?, total_nett_weight=?, total_amount=?, deduction_amount=?, addition_amount=?, final_amount=?, outstanding_amount=?, deduction_details=?, addition_details=?, modified_by=? WHERE id=?")) {
                $deductionsJson = json_encode($deductionRecords);
                $additionJson = json_encode($additionRecords);
                
                $update_payment_stmt->bind_param('sssssssssssssssss', $pvType, $supplierId, $voucherDate, $transactionFromDate, $transactionToDate, $unitPrice, $tax, $totalNettWeight, $subtotal, $totalDeductions, $totalAdditions, $finalAmount, $finalAmount, $deductionsJson, $additionJson, $username, $pvId);
                
                if (!$update_payment_stmt->execute()) {
                    echo json_encode(array(
                        "status" => "failed",
                        "message" => $update_payment_stmt->error
                    ));
                } else {
                    // Update weight record with payment voucher id for newly selected rows
                    if ($pvType == 'Term' && !empty($selectedIds)) {
                        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
                        $sql = "UPDATE Weight SET pv_id = ? WHERE id IN ($placeholders)";

                        if ($update_weight_stmt = $db->prepare($sql)) {
                            $types = 's' . str_repeat('i', count($selectedIds));
                            $params = array_merge([$pvId], $selectedIds);
                            $update_weight_stmt->bind_param($types, ...$params);
                            $update_weight_stmt->execute();
                            $update_weight_stmt->close();
                        }
                    }
                    
                    echo json_encode(array(
                        "status" => "success",
                        "message" => "Updated Successfully!!"
                    ));
                }
                $update_payment_stmt->close();
            }
        }else{
            if ($insert_payment_stmt = $db->prepare("INSERT INTO Payment_Voucher (type, supplier_id, voucher_no, invoice_no, voucher_date, from_date, to_date, unit_price, tax, total_nett_weight, total_amount, deduction_amount, addition_amount, final_amount, outstanding_amount, deduction_details, addition_details, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                $deductionsJson = json_encode($deductionRecords);
                $additionJson = json_encode($additionRecords);
                
                $insert_payment_stmt->bind_param('sssssssssssssssssss', $pvType, $supplierId, $voucherNo, $invoiceNo, $voucherDate, $transactionFromDate, $transactionToDate, $unitPrice, $tax, $totalNettWeight, $subtotal, $totalDeductions, $totalAdditions, $finalAmount, $finalAmount, $deductionsJson, $additionJson, $username, $username);

                if (! $insert_payment_stmt->execute()) {
                    echo json_encode(
                        array(
                            "status"=> "failed", 
                            "message"=> $insert_payment_stmt->error
                        )
                    );
                }
                else{
                    $pvId = $insert_payment_stmt->insert_id;
                    $insert_payment_stmt->close();

                    // Update Running_No_Setup for voucher no
                    $name = 'payment_voucher';
                    if($update_stmt2 = $db->prepare("UPDATE Running_No_Setup SET value=value+1 WHERE document=?")){
                        $update_stmt2->bind_param('s', $name);
                        $update_stmt2->execute();
                        $update_stmt2->close();
                    }

                    // Update Running_No_Setup for invoice no
                    $name = 'invoice_running_no';
                    if($update_stmt2 = $db->prepare("UPDATE Running_No_Setup SET value=value+1 WHERE document=?")){
                        $update_stmt2->bind_param('s', $name);
                        $update_stmt2->execute();
                        $update_stmt2->close();
                    }

                    // Update weight record with payment voucher id
                    if ($pvType == 'Term') {
                        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
                        if (!empty($selectedIds)) {
                            $sql = "UPDATE Weight SET pv_id = ? WHERE id IN ($placeholders)";

                            if ($update_weight_stmt = $db->prepare($sql)) {
                                $types = 's' . str_repeat('i', count($selectedIds)); // pv_id = string, ids = integer

                                $params = array_merge([$pvId], $selectedIds);

                                $update_weight_stmt->bind_param($types, ...$params);
                                $update_weight_stmt->execute();
                                $update_weight_stmt->close();
                            }
                        }
                    }
                    
                    // Return success response with the inserted payment voucher
                    echo json_encode(
                        array(
                            "status"=> "success", 
                            "message"=> "Added Successfully!!" 
                        )
                    );
                }
            }
        }
    } else {
        echo json_encode(array(
            "status" => "failed",
            "message" => "Failed to update pricing"
        ));
    }
    
    $db->close();
}else{
    echo json_encode(array(
        "status" => "failed",
        "message" => "Please fill in all required fields"
    ));
}
?>