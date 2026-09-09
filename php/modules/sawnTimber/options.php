<?php
session_start();
require_once '../../db_connect.php';
require_once 'helpers.php';

$type = isset($_POST['type']) ? $_POST['type'] : '';

if ($type == 'add') {
    $optionType = optionalPost('optionType');
    $name = optionalPost('name');
    if (!$optionType || !$name) {
        echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
        exit;
    }

    if ($optionType == 'supplier') {
        $stmt = $db->prepare("INSERT INTO Sawn_Timber_Supplier (name) VALUES (?)");
        $stmt->bind_param('s', $name);
    } elseif ($optionType == 'lot') {
        $stmt = $db->prepare("INSERT INTO Sawn_Timber_Lot (lot) VALUES (?)");
        $stmt->bind_param('s', $name);
    } elseif ($optionType == 'species') {
        $stmt = $db->prepare("INSERT INTO Sawn_Timber_Species (name) VALUES (?)");
        $stmt->bind_param('s', $name);
    } else {
        echo json_encode(array("status" => "failed", "message" => "Invalid option type"));
        exit;
    }

    if (!$stmt->execute()) {
        echo json_encode(array("status" => "failed", "message" => $stmt->error));
    } else {
        echo json_encode(array("status" => "success", "message" => "Added Successfully!!"));
    }

    $stmt->close();
    $db->close();
    exit;
}

echo json_encode(array(
    "status" => "success",
    "message" => array(
        "suppliers" => getSawnTimberOptions($db, 'Sawn_Timber_Supplier', 'name'),
        "lots" => getSawnTimberOptions($db, 'Sawn_Timber_Lot', 'lot'),
        "species" => getSawnTimberOptions($db, 'Sawn_Timber_Species', 'name')
    )
));

$db->close();
?>
