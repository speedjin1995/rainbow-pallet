<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['userID'])) {
    $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($stmt = $db->prepare("SELECT * FROM Supplier WHERE id=?")) {
        $stmt->bind_param('s', $id);

        if (!$stmt->execute()) {
            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
        } else {
            $result = $stmt->get_result();
            $message = array();

            while ($row = $result->fetch_assoc()) {
                $message['id']             = $row['id'];
                $message['supplier_code']  = $row['supplier_code'];
                $message['company_reg_no'] = $row['company_reg_no'];
                $message['new_reg_no']     = $row['new_reg_no'];
                $message['name']           = $row['name'];
                $message['address_line_1'] = $row['address_line_1'];
                $message['address_line_2'] = $row['address_line_2'];
                $message['address_line_3'] = $row['address_line_3'];
                $message['phone_no']       = $row['phone_no'];
                $message['fax_no']         = $row['fax_no'];
                $message['contact_name']   = $row['contact_name'];
                $message['ic_no']          = $row['ic_no'];
                $message['tin_no']         = $row['tin_no'];
                $message['payment_term']   = $row['payment_term'];
                $message['payment_term_period'] = $row['payment_term_period'];
            }

            echo json_encode(array("status" => "success", "message" => $message));
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Missing Attribute"));
}
?>
