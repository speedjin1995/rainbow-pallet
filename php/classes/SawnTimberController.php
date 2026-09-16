<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/SawnTimberService.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SawnTimberController extends BaseController {
    protected $table = 'Sawn_Timber_Header';
    private $service;

    public function __construct($db) {
        parent::__construct($db);
        $this->service = new SawnTimberService($db, $this->username);
    }

    // ─── List (DataTable) ────────────────────────────────────────────────────────
    public function handleList() {
        echo json_encode($this->service->getList($_POST));
    }

    // ─── Get Single Record ───────────────────────────────────────────────────────
    public function handleGet() {
        $id = $this->getPost('id');
        if (!$id) $this->failed('Missing ID');

        $result = $this->service->getById($id);
        if ($result) {
            $this->success($result);
        } else {
            $this->failed('Record not found');
        }
    }

    // ─── Get Details For Row Expansion ───────────────────────────────────────────
    public function handleGetDetails() {
        $id = $this->getPost('id');
        if (!$id) $this->failed('Missing ID');

        $result = $this->service->getDetailsForExpansion($id);
        $this->success($result);
    }

    // ─── Get Weighing Transactions ───────────────────────────────────────────────
    public function handleGetWeighing() {
        $data = $this->service->getWeighingTransactions();
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    // ─── Save (Create/Update) ────────────────────────────────────────────────────
    public function handleSave() {
        try {
            if (!isset($_POST['transactionId'])) {
                throw new Exception("Please fill in all the fields");
            }

            $id = $this->service->save($_POST);
            $isUpdate = !empty($_POST['id']);
            
            $this->success(($isUpdate ? "Updated" : "Added") . " Successfully!!", ['id' => $id]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    // ─── Delete (Soft Delete) ────────────────────────────────────────────────────
    public function handleDelete() {
        $id = $this->getPost('userID');
        if (!$id) $this->failed('Please select a record');

        try {
            $isMulti = $this->getPost('type') == 'MULTI';
            $this->service->delete($id, $isMulti);
            $this->success('Deleted');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    // ─── Export Excel ────────────────────────────────────────────────────────────
    public function handleExport() {
        require_once __DIR__ . '/../../vendor/autoload.php';

        $template = isset($_GET['template']) && $_GET['template'] == '1';
        $filename = $template ? 'Sawn_Timber_Template.xlsx' : 'Sawn_Timber_Export.xlsx';

        $headers = ['Record Date', 'Transaction ID', 'Transaction Date', 'Company', 'Plant', 'Customer/Supplier', 'DO No', 'Vehicle No', 'Destination', 'Species', 'Lot', 'Bundle', 'Thick', 'Width', 'Length', 'Pieces', 'Tons', 'KD Charges', 'Bundling Charges', 'Grader Fees', 'Remarks'];

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
            $data = $this->service->getExportData($_GET);
            $rowNumber = 2;
            foreach ($data as $row) {
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
        } else {
            $lists = $this->service->getDropdownLists();

            $listSheet = $spreadsheet->createSheet();
            $listSheet->setTitle('Dropdown Lists');
            $listSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

            foreach ($lists['companies'] as $index => $value) {
                $listSheet->setCellValue('A'.($index + 1), $value);
            }
            foreach ($lists['plants'] as $index => $value) {
                $listSheet->setCellValue('B'.($index + 1), $value);
            }
            foreach ($lists['suppliers'] as $index => $value) {
                $listSheet->setCellValue('C'.($index + 1), $value);
            }
            foreach ($lists['species'] as $index => $value) {
                $listSheet->setCellValue('D'.($index + 1), $value);
            }

            if (!empty($lists['companies'])) {
                $this->addDropdownList($sheet, 'A2:A500', "'Dropdown Lists'!\$A\$1:\$A\$".count($lists['companies']));
            }
            if (!empty($lists['plants'])) {
                $this->addDropdownList($sheet, 'B2:B500', "'Dropdown Lists'!\$B\$1:\$B\$".count($lists['plants']));
            }
            if (!empty($lists['suppliers'])) {
                $this->addDropdownList($sheet, 'E2:E500', "'Dropdown Lists'!\$C\$1:\$C\$".count($lists['suppliers']));
            }
            if (!empty($lists['species'])) {
                $this->addDropdownList($sheet, 'H2:H500', "'Dropdown Lists'!\$D\$1:\$D\$".count($lists['species']));
            }
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function addDropdownList($sheet, $range, $formula) {
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
}
?>
