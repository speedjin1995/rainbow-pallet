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
        $Code = $rows['Code'];
        $Name = !empty($rows['Name']) ? trim($rows['Name']) : '';
        $RegNo = !empty($rows['RegNo']) ? trim($rows['RegNo']) : '';
        $NewRegNo = !empty($rows['NewRegNo']) ? trim($rows['NewRegNo']) : '';
        $Address1 = !empty($rows['Addr1']) ? $rows['Addr1'] : '';
        $Address2 = !empty($rows['Addr2']) ? $rows['Addr2'] : '';
        $Address3 = !empty($rows['Addr3']) ? $rows['Addr3'] : '';
        $Address4 = !empty($rows['Addr4']) ? $rows['Addr4'] : '';
        $Phone = !empty($rows['Tel']) ? $rows['Tel'] : '';
        $Fax = !empty($rows['Fax']) ? $rows['Fax'] : '';
        $ContactName = !empty($rows['ContactName']) ? $rows['ContactName'] : '';
        $ICNo = !empty($rows['ICNo']) ? $rows['ICNo'] : '';
        $TinNo = !empty($rows['TinNo']) ? $rows['TinNo'] : '';

        if ($Code != null && $Code != '') {
            $status = '0';
            $check = $db->prepare("SELECT id FROM Customer WHERE customer_code = ? AND status = ?");
            $check->bind_param('ss', $Code, $status);
            $check->execute();
            $customerRow = $check->get_result()->fetch_assoc();
            $check->close();

            if (empty($customerRow)) {
                if ($insert_stmt = $db->prepare("INSERT INTO Customer (customer_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                    $insert_stmt->bind_param('ssssssssssssss', $Code, $RegNo, $NewRegNo, $Name, $Address1, $Address2, $Address3, $Phone, $Fax, $ContactName, $ICNo, $TinNo, $uid, $uid);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
            } else {
                $errorSoProductArray[] = "Customer: " . $Name . " already exist in master data.";
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
