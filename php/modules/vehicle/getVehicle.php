<?php
session_start();
require_once '../../db_connect.php';

if (isset($_POST['userID'])) {
    $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);
    $type = empty($_POST["type"]) ? null : trim($_POST["type"]);

    if (!empty($type) && $type == 'lookup') {
        if ($veh_chk_stmt = $db->prepare("SELECT COUNT(*) AS COUNT FROM `Weight` WHERE lorry_plate_no1 = ? AND is_complete='N'")) {
            $veh_chk_stmt->bind_param('s', $id);

            if (!$veh_chk_stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
            } else {
                $vehicleExist = false;
                $vehResult = $veh_chk_stmt->get_result();
                while ($vehRow = $vehResult->fetch_assoc()) {
                    $vehicleExist = $vehRow['COUNT'] > 0;
                }

                if ($vehicleExist) {
                    echo json_encode(array("status" => "error", "message" => 'There is a pending record for this vehicle'));
                } else {
                    if ($stmt = $db->prepare("SELECT * FROM Vehicle WHERE veh_number=? AND status='0'")) {
                        $stmt->bind_param('s', $id);

                        if (!$stmt->execute()) {
                            echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
                        } else {
                            $result = $stmt->get_result();
                            $message = array();
                            while ($row = $result->fetch_assoc()) {
                                $message['id'] = $row['id'];
                                $message['veh_number'] = $row['veh_number'];
                                $message['vehicle_weight'] = $row['vehicle_weight'];
                                $message['transporter_name'] = $row['transporter_name'];
                                $message['transporter_code'] = $row['transporter_code'];
                                $message['ex_del'] = $row['ex_del'];
                                $message['customer_code'] = $row['customer_code'];
                                $message['customer_name'] = $row['customer_name'];
                                $message['supplier_code'] = $row['supplier_code'];
                                $message['supplier_name'] = $row['supplier_name'];
                            }
                            echo json_encode(array("status" => "success", "message" => $message));
                        }
                    }
                }
            }
        }
    } else if ($type == 'pullCustomer') {
        if ($stmt = $db->prepare("SELECT * FROM Vehicle WHERE veh_number=? AND status='0'")) {
            $stmt->bind_param('s', $id);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
            } else {
                $result = $stmt->get_result();
                $message = array();
                while ($row = $result->fetch_assoc()) {
                    $message['id'] = $row['id'];
                    $message['veh_number'] = $row['veh_number'];
                    $message['vehicle_weight'] = $row['vehicle_weight'];
                    $message['transporter_name'] = $row['transporter_name'];
                    $message['transporter_code'] = $row['transporter_code'];
                    $message['ex_del'] = $row['ex_del'];
                    $message['customer_code'] = $row['customer_code'];
                    $message['customer_name'] = $row['customer_name'];
                    $message['supplier_code'] = $row['supplier_code'];
                    $message['supplier_name'] = $row['supplier_name'];
                }
                echo json_encode(array("status" => "success", "message" => $message));
            }
        }
    } else {
        if ($stmt = $db->prepare("SELECT * FROM Vehicle WHERE id=? AND status='0'")) {
            $stmt->bind_param('s', $id);

            if (!$stmt->execute()) {
                echo json_encode(array("status" => "failed", "message" => "Something went wrong"));
            } else {
                $result = $stmt->get_result();
                $message = array();
                while ($row = $result->fetch_assoc()) {
                    $message['id'] = $row['id'];
                    $message['veh_number'] = $row['veh_number'];
                    $message['vehicle_weight'] = $row['vehicle_weight'];
                    $message['transporter_name'] = $row['transporter_name'];
                    $message['transporter_code'] = $row['transporter_code'];
                    $message['ex_del'] = $row['ex_del'];
                    $message['customer_code'] = $row['customer_code'];
                    $message['customer_name'] = $row['customer_name'];
                    $message['supplier_code'] = $row['supplier_code'];
                    $message['supplier_name'] = $row['supplier_name'];
                }
                echo json_encode(array("status" => "success", "message" => $message));
            }
        }
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Missing Attribute"));
}
?>
