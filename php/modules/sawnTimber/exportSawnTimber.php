<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$template = isset($_GET['template']) && $_GET['template'] == '1';
$filename = $template ? 'Sawn_Timber_Template.xlsx' : 'Sawn_Timber_Export.xlsx';

$headers = array('Record Date', 'Transaction ID', 'Transaction Date', 'Company', 'Plant', 'Customer/Supplier', 'DO No', 'Vehicle No', 'Destination', 'Species', 'Lot', 'Bundle', 'Thick', 'Width', 'Length', 'Pieces', 'Tons', 'KD Charges', 'Bundling Charges', 'Grader Fees', 'Remarks');

function getOptionList($db, $sql, $columns) {
    $items = array();
    $result = $db->query($sql);
    while ($row = $result->fetch_assoc()) {
        if (count($columns) === 2 && !empty($row[$columns[1]])) {
            $items[] = $row[$columns[0]].' - '.$row[$columns[1]];
        } else {
            $items[] = $row[$columns[0]];
        }
    }
    return $items;
}

function addDropdownList($sheet, $range, $formula) {
    foreach ($sheet->rangeToArray($range, null, true, true, true) as $rowNumber => $row) {
        foreach ($row as $column => $value) {
            $validation = $sheet->getCell($column.$rowNumber)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1($formula);
        }
    }
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Sawn Timber');

$column = 1;
foreach ($headers as $header) {
    $sheet->setCellValue([$column, 1], $header);
    $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
    $column++;
}

$sheet->getStyle('A1:U1')->getFont()->setBold(true);

if (!$template) {
    $where = " WHERE h.status='0'";
    $params = array();
    $types = '';

    if (!empty($_GET['fromDate'])) {
        $dateTime = DateTime::createFromFormat('d-m-Y', $_GET['fromDate']);
        if ($dateTime) {
            $where .= " AND h.record_date >= ?";
            $params[] = $dateTime->format('Y-m-d 00:00:00');
            $types .= 's';
        }
    }

    if (!empty($_GET['toDate'])) {
        $dateTime = DateTime::createFromFormat('d-m-Y', $_GET['toDate']);
        if ($dateTime) {
            $where .= " AND h.record_date <= ?";
            $params[] = $dateTime->format('Y-m-d 23:59:59');
            $types .= 's';
        }
    }

    if (!empty($_GET['company']) && $_GET['company'] != '-') {
        $where .= " AND h.company_id = ?";
        $params[] = $_GET['company'];
        $types .= 'i';
    }

    if (!empty($_GET['plant']) && $_GET['plant'] != '-') {
        $where .= " AND p.plant_code = ?";
        $params[] = $_GET['plant'];
        $types .= 's';
    }

    if (!empty($_GET['transactionId'])) {
        $where .= " AND w.transaction_id LIKE ?";
        $params[] = '%'.$_GET['transactionId'].'%';
        $types .= 's';
    }

    $sql = "SELECT h.record_date, w.transaction_id, w.transaction_date, c.name AS company_name, 
            CONCAT(p.plant_code, ' - ', p.name) AS plant_name, 
            COALESCE(cust.name, sup.name) AS customer_supplier,
            w.delivery_no, w.lorry_plate_no1, w.destination,
            d.species, d.lot, d.bundle, d.thick, d.width, d.length, d.pieces, d.tons,
            d.kd_charges, d.bundling_charges, d.grader_fees, h.remarks
            FROM Sawn_Timber_Header h
            LEFT JOIN Sawn_Timber_Detail d ON d.header_id = h.id
            LEFT JOIN Weight w ON h.weight_id = w.id
            LEFT JOIN Company c ON h.company_id = c.id
            LEFT JOIN Plant p ON h.plant_id = p.id
            LEFT JOIN Customer cust ON w.customer_code = cust.customer_code
            LEFT JOIN Supplier sup ON w.supplier_code = sup.supplier_code
            $where
            ORDER BY h.record_date DESC, w.transaction_id DESC, d.id ASC";

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        die('Prepare failed: ' . $db->error);
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        die('Execute failed: ' . $stmt->error);
    }
    $result = $stmt->get_result();

    $rowNumber = 2;
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue([1, $rowNumber], $row['record_date']);
        $sheet->setCellValue([2, $rowNumber], $row['transaction_id']);
        $sheet->setCellValue([3, $rowNumber], $row['transaction_date']);
        $sheet->setCellValue([4, $rowNumber], $row['company_name']);
        $sheet->setCellValue([5, $rowNumber], $row['plant_name']);
        $sheet->setCellValue([6, $rowNumber], $row['customer_supplier']);
        $sheet->setCellValue([7, $rowNumber], $row['delivery_no']);
        $sheet->setCellValue([8, $rowNumber], $row['lorry_plate_no1']);
        $sheet->setCellValue([9, $rowNumber], $row['destination']);
        $sheet->setCellValue([10, $rowNumber], $row['species']);
        $sheet->setCellValue([11, $rowNumber], $row['lot']);
        $sheet->setCellValue([12, $rowNumber], $row['bundle']);
        $sheet->setCellValue([13, $rowNumber], $row['thick']);
        $sheet->setCellValue([14, $rowNumber], $row['width']);
        $sheet->setCellValue([15, $rowNumber], $row['length']);
        $sheet->setCellValue([16, $rowNumber], $row['pieces']);
        $sheet->setCellValue([17, $rowNumber], $row['tons']);
        $sheet->setCellValue([18, $rowNumber], $row['kd_charges']);
        $sheet->setCellValue([19, $rowNumber], $row['bundling_charges']);
        $sheet->setCellValue([20, $rowNumber], $row['grader_fees']);
        $sheet->setCellValue([21, $rowNumber], $row['remarks']);
        $rowNumber++;
    }

    $stmt->close();
} else {
    $listSheet = $spreadsheet->createSheet();
    $listSheet->setTitle('Dropdown Lists');
    $listSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

    $companies = getOptionList($db, "SELECT name FROM Company WHERE status='0' ORDER BY name ASC", array('name'));
    $plants = getOptionList($db, "SELECT name FROM Plant WHERE status='0' ORDER BY name ASC", array('name'));
    $suppliers = getOptionList($db, "SELECT name FROM Supplier WHERE status='0' ORDER BY name ASC", array('name'));
    $species = getOptionList($db, "SELECT name FROM Sawn_Timber_Species WHERE status=0 ORDER BY name ASC", array('name'));

    foreach ($companies as $index => $value) {
        $listSheet->setCellValue('A'.($index + 1), $value);
    }
    foreach ($plants as $index => $value) {
        $listSheet->setCellValue('B'.($index + 1), $value);
    }
    foreach ($suppliers as $index => $value) {
        $listSheet->setCellValue('C'.($index + 1), $value);
    }
    foreach ($species as $index => $value) {
        $listSheet->setCellValue('D'.($index + 1), $value);
    }

    if (!empty($companies)) {
        addDropdownList($sheet, 'A2:A500', "'Dropdown Lists'!\$A\$1:\$A\$".count($companies));
    }
    if (!empty($plants)) {
        addDropdownList($sheet, 'B2:B500', "'Dropdown Lists'!\$B\$1:\$B\$".count($plants));
    }
    if (!empty($suppliers)) {
        addDropdownList($sheet, 'E2:E500', "'Dropdown Lists'!\$C\$1:\$C\$".count($suppliers));
    }
    if (!empty($species)) {
        addDropdownList($sheet, 'H2:H500', "'Dropdown Lists'!\$D\$1:\$D\$".count($species));
    }
}

$db->close();

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
