<?php
session_start();
require_once '../../db_connect.php';
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$template = isset($_GET['template']) && $_GET['template'] == '1';
$filename = $template ? 'Sawn_Timber_Template.xlsx' : 'Sawn_Timber_Export.xlsx';

$headers = array('TransactionID', 'TransactionDate', 'Supplier', 'Lot', 'Bundle', 'Species', 'Thick', 'Width', 'Length', 'Pieces', 'Tons', 'Remarks');

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

$sheet->getStyle('A1:M1')->getFont()->setBold(true);

if (!$template) {
    $where = " WHERE h.status='0'";
    $params = array();
    $types = '';

    if (!empty($_GET['fromDate'])) {
        $where .= " AND DATE(h.transaction_date) >= ?";
        $params[] = $_GET['fromDate'];
        $types .= 's';
    }

    if (!empty($_GET['toDate'])) {
        $where .= " AND DATE(h.transaction_date) <= ?";
        $params[] = $_GET['toDate'];
        $types .= 's';
    }

    if (!empty($_GET['transactionId'])) {
        $where .= " AND h.transaction_id LIKE ?";
        $params[] = '%'.$_GET['transactionId'].'%';
        $types .= 's';
    }

    if (!empty($_GET['supplier'])) {
        $where .= " AND h.supplier = ?";
        $params[] = $_GET['supplier'];
        $types .= 's';
    }

    if (!empty($_GET['lot'])) {
        $where .= " AND h.lot LIKE ?";
        $params[] = '%'.$_GET['lot'].'%';
        $types .= 's';
    }

    if (!empty($_GET['species'])) {
        $where .= " AND d.species = ?";
        $params[] = $_GET['species'];
        $types .= 's';
    }

    if (!empty($_GET['remarks'])) {
        $where .= " AND h.remarks LIKE ?";
        $params[] = '%'.$_GET['remarks'].'%';
        $types .= 's';
    }

    $sql = "SELECT h.transaction_id, h.transaction_date, h.supplier, h.lot, h.bundle, d.species, d.thick, d.width, d.length, d.pieces, d.tons, h.remarks
            FROM Sawn_Timber_Header h
            INNER JOIN Sawn_Timber_Detail d ON d.header_id = h.id
            $where
            ORDER BY h.transaction_date DESC, h.transaction_id DESC, d.id ASC";

    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $rowNumber = 2;
    while ($row = $result->fetch_assoc()) {
        $column = 1;
        foreach ($row as $value) {
            $sheet->setCellValue([$column, $rowNumber], $value);
            $column++;
        }
        $rowNumber++;
    }

    $stmt->close();
} else {
    $listSheet = $spreadsheet->createSheet();
    $listSheet->setTitle('Dropdown Lists');
    $listSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

    $suppliers = getOptionList($db, "SELECT name FROM Supplier WHERE status='0' ORDER BY name ASC", array('name'));
    $species = getOptionList($db, "SELECT name FROM Sawn_Timber_Species ORDER BY name ASC", array('name'));

    foreach ($suppliers as $index => $value) {
        $listSheet->setCellValue('A'.($index + 1), $value);
    }
    foreach ($species as $index => $value) {
        $listSheet->setCellValue('B'.($index + 1), $value);
    }

    if (!empty($suppliers)) {
        addDropdownList($sheet, 'C2:C500', "'Dropdown Lists'!\$A\$1:\$A\$".count($suppliers));
    }
    if (!empty($species)) {
        addDropdownList($sheet, 'F2:F500', "'Dropdown Lists'!\$B\$1:\$B\$".count($species));
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
