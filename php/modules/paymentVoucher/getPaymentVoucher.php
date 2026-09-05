<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['pvId'])) {
    $id = filter_input(INPUT_POST, 'pvId', FILTER_SANITIZE_STRING);

    if ($stmt = $db->prepare("SELECT * FROM Payment_Voucher pv WHERE id=?")) {
        $stmt->bind_param('s', $id);

        if (!$stmt->execute()) {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        } else {
            $result = $stmt->get_result();
            $message = array();

            while ($row = $result->fetch_assoc()) {
                $message['id'] = $row['id'];
                $message['type'] = $row['type'];
                $message['supplier_id'] = $row['supplier_id'];
                $message['voucher_no'] = $row['voucher_no'];
                $message['voucher_date'] = $row['voucher_date'];
                $message['from_date'] = $row['from_date'];
                $message['to_date'] = $row['to_date'];
                $message['invoice_no'] = $row['invoice_no'];
                $message['unit_price'] = $row['unit_price'];
                $message['tax'] = $row['tax'];
                $message['total_nett_weight'] = $row['total_nett_weight'];
                $message['total_amount'] = $row['total_amount'];
                $message['deduction_amount'] = $row['deduction_amount'];
                $message['addition_amount'] = $row['addition_amount'];
                $message['final_amount'] = $row['final_amount'];
                $message['deduction_details'] = $row['deduction_details'];
                $message['addition_details'] = $row['addition_details'];
            }

            echo json_encode(array("status" => "success", "message" => $message));
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Missing Attribute"));
}
?>
