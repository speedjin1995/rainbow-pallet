<?php
session_start();
require_once 'db_connect.php';

function optionalValue($key) {
    return isset($_POST[$key]) && $_POST[$key] !== '' ? trim($_POST[$key]) : null;
}

if (!isset($_POST['id'])) {
    echo json_encode(array("status" => "failed", "message" => "Missing weight record"));
    exit;
}

$id = trim($_POST['id']);
$action = isset($_POST['action']) ? trim($_POST['action']) : 'get';

if ($action == 'save') {
    $doNo = optionalValue('custSideDoNo');
    $mc = optionalValue('custSideMc');
    $firstWeight = optionalValue('custSideFirstWeight');
    $secondWeight = optionalValue('custSideSecondWeight');
    $nettWeight = null;
    $weightDifference = null;

    if ($firstWeight !== null && $secondWeight !== null) {
        $nettWeight = abs((float)$firstWeight - (float)$secondWeight);
    }

    if ($nettWeight !== null && $selectStmt = $db->prepare("SELECT nett_weight1 FROM Weight WHERE id=?")) {
        $selectStmt->bind_param('s', $id);
        $selectStmt->execute();
        $result = $selectStmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $originalNettWeight = $row['nett_weight1'] !== null && $row['nett_weight1'] !== '' ? (float)$row['nett_weight1'] : null;

            if ($originalNettWeight !== null) {
                $weightDifference = $originalNettWeight - $nettWeight;
            }
        }

        $selectStmt->close();
    }

    if ($stmt = $db->prepare("UPDATE Weight SET cust_side_do_no=?, cust_side_mc=?, cust_side_first_weight=?, cust_side_second_weight=?, cust_side_nett_weight=?, weight_difference=? WHERE id=?")) {
        $stmt->bind_param('sssssss', $doNo, $mc, $firstWeight, $secondWeight, $nettWeight, $weightDifference, $id);

        if (!$stmt->execute()) {
            echo json_encode(array("status" => "failed", "message" => $stmt->error));
        } else {
            echo json_encode(array("status" => "success", "message" => "Updated Successfully!!", "nett_weight" => $nettWeight, "weight_difference" => $weightDifference));
        }

        $stmt->close();
    } else {
        echo json_encode(array("status" => "failed", "message" => $db->error));
    }

    $db->close();
    exit;
}

if ($stmt = $db->prepare("SELECT cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference FROM Weight WHERE id=?")) {
    $stmt->bind_param('s', $id);

    if (!$stmt->execute()) {
        echo json_encode(array("status" => "failed", "message" => $stmt->error));
    } else {
        $result = $stmt->get_result();
        $message = array(
            "cust_side_do_no" => "",
            "cust_side_mc" => "",
            "cust_side_first_weight" => "",
            "cust_side_second_weight" => "",
            "cust_side_nett_weight" => "",
            "weight_difference" => ""
        );

        if ($row = $result->fetch_assoc()) {
            $message['cust_side_do_no'] = $row['cust_side_do_no'] ?? '';
            $message['cust_side_mc'] = $row['cust_side_mc'] ?? '';
            $message['cust_side_first_weight'] = $row['cust_side_first_weight'] ?? '';
            $message['cust_side_second_weight'] = $row['cust_side_second_weight'] ?? '';
            $message['cust_side_nett_weight'] = $row['cust_side_nett_weight'] ?? '';
            $message['weight_difference'] = $row['weight_difference'] ?? '';
        }

        echo json_encode(array("status" => "success", "message" => $message));
    }

    $stmt->close();
} else {
    echo json_encode(array("status" => "failed", "message" => $db->error));
}

$db->close();
?>
