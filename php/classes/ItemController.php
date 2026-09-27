<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/ItemService.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Item Controller
 * Handles all CRUD operations for Items (Products)
 */
class ItemController extends BaseController {
    
    private $itemService;
    
    public function __construct($db, $username) {
        parent::__construct($db, $username);
        $this->itemService = new ItemService($db, $username);
    }
    
    public function handleList() {
        $companyId = intval($this->getPost('company'));
        if ($companyId <= 0) {
            $this->failed('Invalid company');
        }
        $list = $this->itemService->getListByCompany($companyId);
        echo json_encode(['status' => 'success', 'data' => $list]);
        exit();
    }

    /**
     * Get all items (for DataTables)
     */
    public function getAll() {
        $result = $this->itemService->getAll($_POST);
        echo json_encode($result);
        exit();
    }
    
    /**
     * Create new item
     */
    public function create() {
        try {
            $result = $this->itemService->create($_POST);
            $this->success('Added Successfully!!', ['id' => $result['id']]);
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Update existing item
     */
    public function update() {
        try {
            $result = $this->itemService->update($_POST);
            $this->success('Updated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Delete (soft delete) item
     */
    public function delete() {
        $id = $this->getPost('id');
        $type = $this->getPost('type');
        
        try {
            $this->itemService->delete($id, $type);
            $this->success('Deleted Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Reactivate item
     */
    public function reactivate() {
        $id = $this->getRequiredPost('id');
        
        try {
            $this->itemService->reactivate($id);
            $this->success('Reactivated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Get single item by ID
     */
    public function get() {
        $id = $this->getRequiredPost('id');
        
        try {
            $row = $this->itemService->get($id);
            if ($row) {
                $this->success('Record found', ['data' => $row]);
            } else {
                $this->failed('Record not found');
            }
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
    
    /**
     * Download the Excel upload template, with Category/UOM as dropdown lists
     * pulled from the user's own Product_Categories/Units (unless view_all_companies)
     */
    public function downloadTemplate() {
        require_once __DIR__ . '/../../vendor/autoload.php';

        $filename = 'Item_Template.xlsx';
        $headers = ['Item Code', 'Item Name', 'Description', 'Category', 'UOM'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Items');

        $column = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue([$column, 1], $header);
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
            $column++;
        }
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // Determine company scope for the dropdown lists
        if (hasModulePermission('Master Data', 'Items', ['view_all_companies'])) {
            $companyId = isset($_GET['company']) && $_GET['company'] !== '' ? intval($_GET['company']) : null;
        } else {
            // Never trust frontend value for restricted users
            $companyId = $_SESSION['company_id'] ?? null;
        }

        $lists = $this->itemService->getDropdownLists($companyId);

        $listSheet = $spreadsheet->createSheet();
        $listSheet->setTitle('Dropdown Lists');
        $listSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        foreach ($lists['categories'] as $index => $value) {
            $listSheet->setCellValue('A'.($index + 1), $value);
        }
        foreach ($lists['units'] as $index => $value) {
            $listSheet->setCellValue('B'.($index + 1), $value);
        }

        if (!empty($lists['categories'])) {
            $this->addDropdownList($sheet, 'D2:D500', "'Dropdown Lists'!\$A\$1:\$A\$".count($lists['categories']));
        }
        if (!empty($lists['units'])) {
            $this->addDropdownList($sheet, 'E2:E500', "'Dropdown Lists'!\$B\$1:\$B\$".count($lists['units']));
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

    /**
     * Upload items from Excel
     */
    public function upload() {
        $data = json_decode(file_get_contents('php://input'), true);

        // Determine company on the backend - never trust frontend value for restricted users
        if (hasModulePermission('Master Data', 'Items', ['view_all_companies'])) {
            $companyId = isset($_GET['company']) ? intval($_GET['company']) : 0;
        } else {
            $companyId = isset($_SESSION['company_id']) ? intval($_SESSION['company_id']) : 0;
        }

        if ($companyId <= 0) {
            $this->failed('Please select a company');
        }

        $company = searchCompanyById($companyId, $this->db);
        if (empty($company) || $company['status'] != '0') {
            $this->failed('Company not found');
        }

        try {
            $result = $this->itemService->upload($data, $companyId);
            
            if (count($result['errors']) > 0) {
                echo json_encode(['status' => 'error', 'message' => $result['errors']]);
                exit();
            } else {
                $this->success("{$result['successCount']} records imported successfully");
            }
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }
}
?>
