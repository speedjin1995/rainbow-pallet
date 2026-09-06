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
        $CompanyCode = !empty($rows['CompanyCode']) ? trim($rows['CompanyCode']) : '';
        $CompanyName = !empty($rows['CompanyName']) ? trim($rows['CompanyName']) : '';
        $CompanyRegNo = !empty($rows['CompanyRegNo']) ? trim($rows['CompanyRegNo']) : '';
        $CompanyNewRegNo = !empty($rows['CompanyNewRegNo']) ? trim($rows['CompanyNewRegNo']) : '';
        $AddressLine1 = !empty($rows['AddressLine1']) ? trim($rows['AddressLine1']) : '';
        $AddressLine2 = !empty($rows['AddressLine2']) ? trim($rows['AddressLine2']) : '';
        $AddressLine3 = !empty($rows['AddressLine3']) ? trim($rows['AddressLine3']) : '';
        $PhoneNo = !empty($rows['PhoneNo']) ? trim($rows['PhoneNo']) : '';
        $FaxNo = !empty($rows['FaxNo']) ? trim($rows['FaxNo']) : '';
        $TinNo = !empty($rows['TinNo']) ? trim($rows['TinNo']) : '';
        $MobileNo = !empty($rows['MobileNo']) ? trim($rows['MobileNo']) : '';

        if ($CompanyCode != null && $CompanyCode != '') {
            $check = $db->prepare("SELECT id FROM Company WHERE company_code = ? AND status = ?");
            $check->bind_param('ss', $CompanyCode, $status);
            $check->execute();
            $companyRow = $check->get_result()->fetch_assoc();
            $check->close();

            if (empty($companyRow)) {
                if ($insert_stmt = $db->prepare("INSERT INTO Company (company_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, tin_no, mobile_no, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                    $insert_stmt->bind_param('ssssssssssss', $CompanyCode, $CompanyRegNo, $CompanyNewRegNo, $CompanyName, $AddressLine1, $AddressLine2, $AddressLine3, $PhoneNo, $FaxNo, $TinNo, $MobileNo, $uid);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
            } else {
                $errorSoProductArray[] = "Company: " . $CompanyName . " already exist in master data.";
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
