<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(["status" => "failed", "message" => "Unauthorized"]);
    exit;
}

try {
    if (!isset($_POST['id'])) {
        throw new Exception("Missing port ID");
    }

    $portId = trim($_POST['id']);

    if (empty($_POST['indicator'])) {
        $indicator = 'D2008';
    } else {
        $indicator = trim($_POST['indicator']);
    }

    if (empty($_POST['serialPort'])) {
        $serialPort = 'COM3';
    } else {
        $serialPort = trim($_POST['serialPort']);
    }

    if (empty($_POST['serialPortBaudRate'])) {
        $serialPortBaudRate = '2400';
    } else {
        $serialPortBaudRate = trim($_POST['serialPortBaudRate']);
    }

    if (empty($_POST['serialPortDataBits'])) {
        $serialPortDataBits = '7';
    } else {
        $serialPortDataBits = trim($_POST['serialPortDataBits']);
    }

    if (empty($_POST['serialPortParity'])) {
        $serialPortParity = 'E';
    } else {
        $serialPortParity = trim($_POST['serialPortParity']);
    }

    if (empty($_POST['serialPortStopBits'])) {
        $serialPortStopBits = '1';
    } else {
        $serialPortStopBits = trim($_POST['serialPortStopBits']);
    }

    $db->begin_transaction();

    $stmt = $db->prepare("UPDATE Port SET indicator=?, com_port=?, bits_per_second=?, data_bits=?, parity=?, stop_bits=? WHERE id=?");
    $stmt->bind_param('sssssss', $indicator, $serialPort, $serialPortBaudRate, $serialPortDataBits, $serialPortParity, $serialPortStopBits, $portId);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $stmt->close();
    $db->commit();
    $db->close();

    echo json_encode(["status" => "success", "message" => "Port setup saved successfully!"]);

} catch (Exception $e) {
    $db->rollback();
    echo json_encode(["status" => "failed", "message" => $e->getMessage()]);
}
?>
