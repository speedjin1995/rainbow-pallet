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
        $TransporterCode = !empty($rows['TransporterCode']) ? trim($rows['TransporterCode']) : '';
        $CompanyRegNo = !empty($rows['CompanyRegNo']) ? trim($rows['CompanyRegNo']) : '';
        $NewRegNo = !empty($rows['NewRegNo']) ? trim($rows['NewRegNo']) : '';
        $CompanyName = !empty($rows['CompanyName']) ? trim($rows['CompanyName']) : '';
        $AddressLine1 = !empty($rows['AddressLine1']) ? trim($rows['AddressLine1']) : '';
        $AddressLine2 = !empty($rows['AddressLine2']) ? trim($rows['AddressLine2']) : '';
        $AddressLine3 = !empty($rows['AddressLine3']) ? trim($rows['AddressLine3']) : '';
        $PhoneNo = !empty($rows['PhoneNo']) ? trim($rows['PhoneNo']) : '';
        $FaxNo = !empty($rows['FaxNo']) ? trim($rows['FaxNo']) : '';
        $ContactName = !empty($rows['ContactName']) ? trim($rows['ContactName']) : '';
        $ICNo = !empty($rows['ICNo']) ? trim($rows['ICNo']) : '';
        $TinNo = !empty($rows['TinNo']) ? trim($rows['TinNo']) : '';
        $actionId = 1;

        if ($TransporterCode != null && $TransporterCode != '') {
            $check = $db->prepare("SELECT id FROM Transporter WHERE transporter_code = ? AND status = ?");
            $check->bind_param('ss', $TransporterCode, $status);
            $check->execute();
            $transporterRow = $check->get_result()->fetch_assoc();
            $check->close();

            if (empty($transporterRow)) {
                if ($insert_stmt = $db->prepare("INSERT INTO Transporter (transporter_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, status, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                    $insert_stmt->bind_param('sssssssssssssss', $TransporterCode, $CompanyRegNo, $NewRegNo, $CompanyName, $AddressLine1, $AddressLine2, $AddressLine3, $PhoneNo, $FaxNo, $ContactName, $ICNo, $TinNo, $status, $uid, $uid);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
            } else {
                $errorSoProductArray[] = "Transporter: " . $CompanyName . " already exist in master data.";
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
