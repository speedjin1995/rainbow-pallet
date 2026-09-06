<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';

if (isset($_POST['supplierId']) && isset($_POST['fromDate']) && isset($_POST['toDate'])) {
    $supplierId = filter_input(INPUT_POST, 'supplierId', FILTER_SANITIZE_STRING);
    $supplier = searchSupplierById($supplierId, $db);

    if(!empty($_POST['fromDate'])){
        $fromDate = DateTime::createFromFormat('d-m-Y', $_POST['fromDate']);
        $fromDateFormatted = $fromDate->format('Y-m-d');
    }

    if(!empty($_POST['toDate'])){
        $toDate = DateTime::createFromFormat('d-m-Y', $_POST['toDate']);
        $toDateFormatted = $toDate->format('Y-m-d');
    }

    if (!empty($supplier)) {
        if ($stmt = $db->prepare("SELECT w.id as weight_id, w.*, pv.voucher_no FROM Weight w LEFT JOIN Payment_Voucher pv ON w.pv_id = pv.id WHERE w.is_complete = 'Y' AND w.is_cancel <> 'Y' AND w.weight_type = 'Normal' AND w.transaction_status = 'Purchase' AND w.status = 0 AND DATE(w.transaction_date) BETWEEN ? AND ? AND w.supplier_code=?")) {
            $stmt->bind_param('sss', $fromDateFormatted, $toDateFormatted, $supplier['supplier_code']);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
            } else {
                $result = $stmt->get_result();
                $message = array();

                while ($row = $result->fetch_assoc()) {
                    $message[] = array(
                        "id" => $row['weight_id'],
                        "transaction_id" => $row['transaction_id'],
                        "weight_type"=> $row['weight_type'],
                        "lorry_plate_no1" => $row['lorry_plate_no1'],
                        "gross_weight1" => $row['gross_weight1'],
                        "gross_weight1_date"=> $row['gross_weight1_date'],
                        "tare_weight1" => $row['tare_weight1'],
                        "tare_weight1_date"=> $row['tare_weight1_date'],
                        "nett_weight1" => $row['nett_weight1'],
                        "unit_price" => $row['unit_price'],
                        "sub_total" => $row['sub_total'],
                        "sst" => $row['sst'],
                        "total_price" => $row['total_price'],
                        "invoice_no" => $row['invoice_no'],
                        "pv_id" => $row['pv_id'],
                        "voucher_no" => $row['voucher_no']
                    );
                }

                echo json_encode(array("status" => "success", "message" => $message));
            }
        }
    } else {
        echo json_encode(array("status" => "failed", "message" => "Supplier not found"));
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Missing Attribute"));
}
?>
