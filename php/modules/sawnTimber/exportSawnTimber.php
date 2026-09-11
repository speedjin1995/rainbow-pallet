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

$headers = array('Company', 'Plant', 'TransactionID', 'TransactionDate', 'Supplier', 'Lot', 'Bundle', 'Species', 'Thick', 'Width', 'Length', 'Pieces', 'Tons', 'Remarks');

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

$sheet->getStyle('A1:N1')->getFont()->setBold(true);

if (!$template) {
    $where = " WHERE h.status='0'";
    $params = array();
    $types = '';

    if (!empty($_GET['fromDate'])) {
        $dateTime = DateTime::createFromFormat('d-m-Y', $_GET['fromDate']);
        if ($dateTime) {
            $where .= " AND h.transaction_date >= ?";
            $params[] = $dateTime->format('Y-m-d 00:00:00');
            $types .= 's';
        }
    }

    if (!empty($_GET['toDate'])) {
        $dateTime = DateTime::createFromFormat('d-m-Y', $_GET['toDate']);
        if ($dateTime) {
            $where .= " AND h.transaction_date <= ?";
            $params[] = $dateTime->format('Y-m-d 23:59:59');
            $types .= 's';
        }
    }

    if ($_GET['company'] != null && $_GET['company'] != '' && $_GET['company'] != '-') {
        $where .= " AND h.company_id = ?";
        $params[] = $_GET['company'];
        $types .= 'i';
    }

    if ($_GET['plant'] != null && $_GET['plant'] != '' && $_GET['plant'] != '-') {
        $where .= " AND h.plant_id = ?";
        $params[] = $_GET['plant'];
        $types .= 'i';
    }

    if ($_GET['transactionId'] != null && $_GET['transactionId'] != '') {
        $where .= " AND h.transaction_id LIKE ?";
        $params[] = '%'.$_GET['transactionId'].'%';
        $types .= 's';
    }

    if ($_GET['supplier'] != null && $_GET['supplier'] != '' && $_GET['supplier'] != '-') {
        $where .= " AND h.supplier = ?";
        $params[] = $_GET['supplier'];
        $types .= 's';
    }

    if ($_GET['lot'] != null && $_GET['lot'] != '') {
        $where .= " AND h.lot LIKE ?";
        $params[] = '%'.$_GET['lot'].'%';
        $types .= 's';
    }

    if ($_GET['species'] != null && $_GET['species'] != '' && $_GET['species'] != '-') {
        $where .= " AND d.species = ?";
        $params[] = $_GET['species'];
        $types .= 's';
    }

    $sql = "SELECT h.company_id, h.plant_id, h.transaction_id, h.transaction_date, h.supplier, h.lot, h.bundle, d.species, d.thick, d.width, d.length, d.pieces, d.tons, h.remarks
            FROM Sawn_Timber_Header h
            INNER JOIN Sawn_Timber_Detail d ON d.header_id = h.id
            $where
            ORDER BY h.transaction_date DESC, h.transaction_id DESC, d.id ASC";

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
        $sheet->setCellValue([1, $rowNumber], searchCompanyById($row['company_id'], $db)['name'] ?? '');
        $sheet->setCellValue([2, $rowNumber], searchPlantNameById($row['plant_id'], $db) ?? '');
        $sheet->setCellValue([3, $rowNumber], $row['transaction_id']);
        $sheet->setCellValue([4, $rowNumber], $row['transaction_date']);
        $sheet->setCellValue([5, $rowNumber], searchSupplierNameById($row['supplier'], $db) ?? '');
        $sheet->setCellValue([6, $rowNumber], $row['lot']);
        $sheet->setCellValue([7, $rowNumber], $row['bundle']);
        $sheet->setCellValue([8, $rowNumber], searchSawnTimberSpeciesNameById($row['species'], $db) ?? '');
        $sheet->setCellValue([9, $rowNumber], $row['thick']);
        $sheet->setCellValue([10, $rowNumber], $row['width']);
        $sheet->setCellValue([11, $rowNumber], $row['length']);
        $sheet->setCellValue([12, $rowNumber], $row['pieces']);
        $sheet->setCellValue([13, $rowNumber], $row['tons']);
        $sheet->setCellValue([14, $rowNumber], $row['remarks']);
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
