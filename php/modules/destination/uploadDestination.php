<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$uid = $_SESSION['username'];

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) {
    $errorSoProductArray = [];
    foreach ($data as $rows) {
        $DestinationCode = !empty($rows['DestinationCode']) ? trim($rows['DestinationCode']) : '';
        $DestinationName = !empty($rows['DestinationName']) ? trim($rows['DestinationName']) : '';
        $Description = !empty($rows['Description']) ? trim($rows['Description']) : '';

        # Check if destination code exist in DB
        $status = "0";
        $check = $db->prepare("SELECT id FROM Destination WHERE destination_code = ? AND status = ?");
        $check->bind_param('ss', $DestinationCode, $status);
        $check->execute();
        $destinationRow = $check->get_result()->fetch_assoc();
        $check->close();

        if (empty($destinationRow)) {
            if ($insert_stmt = $db->prepare("INSERT INTO Destination (destination_code, name, description, created_by, modified_by) VALUES (?, ?, ?, ?, ?)")) {
                $insert_stmt->bind_param('sssss', $DestinationCode, $DestinationName, $Description, $uid, $uid);
                $insert_stmt->execute();
                $insert_stmt->close();
            }
        } else {
            $errorSoProductArray[] = "Destination: " . $DestinationName . " already exist in master data.";
            continue;
        }
    }

    $db->close();

    if (!empty($errorSoProductArray)) {
        echo json_encode(array("status" => "error", "message" => $errorSoProductArray));
    } else {
        echo json_encode(array("status" => "success", "message" => "Added Successfully!!"));
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
}
?>
