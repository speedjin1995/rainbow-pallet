<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$uid = $_SESSION['username'];

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) {
    $errorSoProductArray = [];
    $status = '0';

    foreach ($data as $rows) {
        $PlantCode = !empty($rows['PlantCode']) ? trim($rows['PlantCode']) : '';
        $PlantName = !empty($rows['PlantName']) ? trim($rows['PlantName']) : '';
        $AddressLine1 = !empty($rows['AddressLine1']) ? trim($rows['AddressLine1']) : '';
        $AddressLine2 = !empty($rows['AddressLine2']) ? trim($rows['AddressLine2']) : '';
        $AddressLine3 = !empty($rows['AddressLine3']) ? trim($rows['AddressLine3']) : '';
        $PhoneNo = !empty($rows['PhoneNo']) ? trim($rows['PhoneNo']) : '';
        $FaxNo = !empty($rows['FaxNo']) ? trim($rows['FaxNo']) : '';

        if ($PlantCode != null && $PlantCode != '') {
            $check = $db->prepare("SELECT id FROM Plant WHERE plant_code = ? AND status = ?");
            $check->bind_param('ss', $PlantCode, $status);
            $check->execute();
            $plantRow = $check->get_result()->fetch_assoc();
            $check->close();

            if (empty($plantRow)) {
                if ($insert_stmt = $db->prepare("INSERT INTO Plant (plant_code, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                    $insert_stmt->bind_param('sssssssss', $PlantCode, $PlantName, $AddressLine1, $AddressLine2, $AddressLine3, $PhoneNo, $FaxNo, $uid, $uid);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
            } else {
                $errorSoProductArray[] = "Plant: " . $PlantName . " already exist in master data.";
                continue;
            }
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
