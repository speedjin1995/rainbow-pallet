<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/WeightService.php';
require_once __DIR__ . '/../services/PrintService.php';
require_once __DIR__ . '/../services/DocumentNumberService.php';
require_once __DIR__ . '/../services/TableColumnService.php';

class WeightController extends BaseController {
    protected $table = 'Weight';
    private $service;
    private $documentNumberService;
    private $tableColumnService;

    public function __construct($db) {
        parent::__construct($db);
        $this->service = new WeightService($db, $this->username);
        $this->documentNumberService = new DocumentNumberService($db, $this->username);
        $this->tableColumnService = new TableColumnService($db, $this->username);
    }

    // ─── Table Columns ────────────────────────────────────────────────────────────
    // Columns of both tables for the company filter's company (own company without view_all_companies)
    public function handleTableColumns() {
        $companyId = hasPermission('Weighing', ['view_all_companies']) ? $this->getPost('companyId') : null;
        $companyId = intval($companyId ?: ($_SESSION['company_id'] ?? 0));

        try {
            $this->success([
                'weight'          => $this->tableColumnService->getColumns($companyId, 'weight'),
                'empty_container' => $this->tableColumnService->getColumns($companyId, 'empty_container'),
            ]);
        } catch (Exception $e) {
            error_log('Table columns: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }
    }

    // ─── DO No ────────────────────────────────────────────────────────────────────
    // Next DO No for the Generate button, not consumed until the weighing is saved
    public function handlePreviewDoNo() {
        $companyId = hasPermission('Weighing', ['view_all_companies']) ? $this->getPost('companyId') : ($_SESSION['company_id'] ?? null);
        $transactionStatus = $this->getPost('transactionStatus');
        $transactionDate = $this->getPost('transactionDate');
        $date = $transactionDate ? DateTime::createFromFormat('d-m-Y', $transactionDate) : null;

        if (empty($companyId) || empty($transactionStatus)) {
            $this->failed('Please select the company and transaction status');
        }

        try {
            $doNo = $this->documentNumberService->preview($companyId, 'DO', $transactionStatus, $date ?: null);
        } catch (Exception $e) {
            error_log('Preview DO No: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }

        if ($doNo === null) {
            $this->failed('No document number format set up for this company and transaction status');
        }
        $this->success($doNo);
    }

    /**
     * DO No to save: a generated (preview) or blank DO No takes the next number from the company/status format,
     * a typed DO No is kept as-is. Runs inside the save transaction so a failed save gives the number back.
     */
    private function resolveDeliveryNo($f) {
        $isGenerated = ($f['deliveryNoAuto'] ?? '') === '1';
        if (!$isGenerated && !empty($f['deliveryNo'])) {
            return $f['deliveryNo'];
        }
        $doNo = $this->documentNumberService->allocate($f['companyId'], 'DO', $f['transactionStatus'], $f['transactionDate']);
        return $doNo ?? $f['deliveryNo'];
    }

    // ─── Print ────────────────────────────────────────────────────────────────────
    public function handlePrint() {
        $service = new PrintService($this->db);
        $service->handle();
    }

    // ─── Filter / Get / Delete ────────────────────────────────────────────────────
    public function handleFilterWeight() {
        echo json_encode($this->service->filterWeight($_POST, $_SESSION['language'], $_SESSION['languageArray']));
    }

    public function handleFilterEmptyContainer() {
        echo json_encode($this->service->filterEmptyContainer($_POST, $_SESSION['language'], $_SESSION['languageArray']));
    }

    public function handleGetWeight() {
        $id       = $_POST['userID'] ?? null;
        $format   = $_POST['format'] ?? 'MODAL';
        $type     = $_POST['type'] ?? 'Weight';
        $acctType = $_POST['acctType'] ?? null;
        $fromDate = $_POST['fromDate'] ?? null;
        $toDate   = $_POST['toDate'] ?? null;
        if (!$id) $this->failed('Missing Attribute');
        $data = $this->service->getWeight($id, $format, $type, $acctType, $fromDate, $toDate);
        if ($data === null) $this->failed('Something went wrong');
        echo json_encode(['status' => 'success', 'message' => $data]);
    }

    public function handleGetEmptyContainer() {
        $id = $_POST['userID'] ?? null;
        if (!$id) $this->failed('Missing Attribute');
        $data = $this->service->getEmptyContainer($id);
        if ($data === null) $this->failed('Record not found');
        echo json_encode(['status' => 'success', 'message' => $data]);
    }

    public function handleGetContainers() {
        $id = $_POST['userID'] ?? null;
        if (!$id) $this->failed('Missing Attribute');
        echo json_encode(['status' => 'success', 'message' => $this->service->getContainers($id)]);
    }

    // ─── Company dropdown lists (weighing modal + search bar) ─────────────────────
    public function handleCompanyLists() {
        $companyId = intval($this->getPost('company'));
        if ($companyId <= 0) $this->failed('Invalid company');

        try {
            $data = $this->service->getCompanyLists($companyId);
        } catch (Exception $e) {
            error_log('Weighing company lists: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    public function handleCustomerSideInfo() {
        $id = $_POST['id'] ?? null;
        if (!$id) $this->failed('Missing weight record');
        $action = $_POST['custAction'] ?? 'get';
        if ($action === 'save') {
            $result = $this->service->saveCustomerSideInfo(
                $id,
                $_POST['custSideDoNo'] ?? null,
                $_POST['custSideMc'] ?? null,
                $_POST['custSideFirstWeight'] ?? null,
                $_POST['custSideSecondWeight'] ?? null
            );
            $this->success('Updated Successfully!!', $result);
        } else {
            $data = $this->service->getCustomerSideInfo($id);
            echo json_encode(['status' => 'success', 'message' => $data]);
        }
    }

    public function handleTrxPort() {
        $id = $this->getPost('id');
        if (!$id) $this->failed('Missing weight record');
        $isContainer = $this->getPost('isContainer') === 'Y' ? 'Y' : 'N';

        try {
            $record = $this->service->getTrxPort($id, $isContainer);
        } catch (Exception $e) {
            error_log('Transfer to port get: ' . $e->getMessage());
            $this->failed('Something went wrong');
        }
        if (!$record) $this->failed('Record not found');

        // Restricted users can only touch their own company's weighings
        if (!hasPermission('Weighing', ['view_all_companies']) && intval($record['company_id']) !== intval($_SESSION['company_id'] ?? 0)) {
            $this->failed('Unauthorized');
        }

        if (($this->getPost('trxPortAction') ?? 'get') === 'save') {
            if (!hasModulePermission('Weighing', $record['transaction_status'], 'edit')) $this->failed('Unauthorized');

            $portSpQty = $this->getPost('portSpQty');
            if ($portSpQty !== null && (!is_numeric($portSpQty) || strlen($portSpQty) > 10)) $this->failed('Invalid SP Quantity / Nett Weight');

            $portLocation = $this->getPost('portLocation');
            if ($portLocation !== null && mb_strlen($portLocation) > 255) $this->failed('Invalid location');

            try {
                $this->service->saveTrxPort($id, $isContainer, $portSpQty, $this->getPost('portRefNo'), $portLocation);
            } catch (Exception $e) {
                error_log('Transfer to port save: ' . $e->getMessage());
                $this->failed('Something went wrong');
            }
            $this->success('Updated Successfully!!');
        }

        echo json_encode(['status' => 'success', 'message' => [
            'port_sp_qty'   => $record['port_sp_qty'] ?? '',
            'port_ref_no'   => $record['port_ref_no'] ?? '',
            'port_location' => $record['port_location'] ?? '',
        ]]);
    }

    public function handleDelete() {
        $cancelReason     = $_POST['cancelReason'] ?? null;
        $isEmptyContainer = $_POST['isEmptyContainer'] ?? 'N';
        $isMulti          = $_POST['isMulti'] ?? '';
        $id               = $_POST['id'] ?? null;
        $containerId      = $_POST['containerId'] ?? null;
        if ($cancelReason === null) $this->failed('Please fill in all the fields');
        try {
            $this->service->deleteWeight($id, $cancelReason, $isEmptyContainer, $isMulti, $containerId);
            $this->success('Deleted');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    public function handleReactivate() {
        $id = $_POST['id'] ?? null;
        if (!$id) $this->failed('Missing weight record');

        $weight = $this->service->getWeight($id, 'MODAL', 'Weight', null, null, null);
        if (!$weight || $weight['is_cancel'] !== 'Y') $this->failed('Cancelled weight record not found');
        if (!hasModulePermission('Weighing', $weight['transaction_status'], 'cancelled')) $this->failed('Unauthorized');

        try {
            $this->service->reactivateWeight($id);
            $this->success('Reactivated Successfully!!');
        } catch (Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    // ─── Entry Point ─────────────────────────────────────────────────────────────
    public function handle() {
        try {
            $f = $this->parseFields();
            $isUpdate = !empty($f['weightId']);
            $this->db->begin_transaction();
            $f['deliveryNo'] = $this->resolveDeliveryNo($f);
            switch ($f['weightType']) {
                case 'Empty Container':
                    $result = $isUpdate ? $this->service->updateEmptyContainer($f) : $this->service->saveEmptyContainer($f);
                    break;
                case 'Different Container':
                    $result = $isUpdate ? $this->service->updateDifferentContainer($f) : $this->service->saveDifferentContainer($f);
                    break;
                default:
                    $result = $isUpdate ? $this->service->updateNormal($f) : $this->service->saveNormal($f);
                    break;
            }
            $this->db->commit();
            $result['delivery_no'] = $f['deliveryNo'];
            $message = $isUpdate ? 'Updated Successfully!!' : 'Added Successfully!!';
            $this->success($message, $result);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->failed($e->getMessage());
        }
    }

    // ─── Input Parsing ───────────────────────────────────────────────────────────
    private function parseFields() {
        $f = [];
        $f['weightId']              = $this->getPost('id');
        // Restricted users can only save weighings under their own company
        $f['companyId']             = hasPermission('Weighing', ['view_all_companies']) ? $this->getPost('companyId') : ($_SESSION['company_id'] ?? null);
        $f['plantCode']             = $this->getPost('plantCode');
        $f['plant']                 = $this->getPost('plant');
        $f['weightType']            = $this->getPost('weightType', 'Normal');
        $f['transactionId']         = $this->getPost('transactionId');
        $f['transactionStatus']     = $this->getPost('transactionStatus');
        $f['customerType']          = $this->getPost('customerType', 'Normal');
        $f['unitPrice']             = $this->getPost('unitPrice');
        $f['subTotalPrice']         = $this->getPost('subTotalPrice', '0.00');
        $f['sstPrice']              = $this->getPost('sstPrice', '0.00');
        $f['totalPrice']            = $this->getPost('totalPrice', '0.00');
        $f['transactionDate']       = empty($_POST['transactionDate']) ? null : DateTime::createFromFormat('d-m-Y', $_POST['transactionDate'])->format('Y-m-d H:i:s');
        $f['poSupplyWeight']        = $this->getPost('poSupplyWeight');
        $f['supplierWeight']        = $this->getPost('supplierWeight');
        $f['orderWeight']           = $this->getPost('orderWeight');
        $f['grossIncoming']         = $this->getPost('grossIncoming', 0);
        $f['grossIncomingDate']     = $this->getPost('grossIncomingDate');
        $f['grossWeightBy1']        = $this->getPost('grossWeightBy1', 0);
        $f['tareOutgoing']          = $this->getPost('tareOutgoing', 0);
        $f['tareOutgoingDate']      = $this->getPost('tareOutgoingDate');
        $f['tareWeightBy1']         = $this->getPost('tareWeightBy1', 0);
        $f['nettWeight']            = $this->getPost('nettWeight', 0);
        $f['manualWeight']          = $this->getPost('manualWeight');
        $f['weighbridge']           = $this->getPost('weighbridge', 'Weigh1');
        $f['indicatorId']           = $this->getPost('indicatorId');
        $f['invoiceNo']             = $this->getPost('invoiceNo');
        $f['deliveryNo']            = $this->getPost('deliveryNo');
        $f['deliveryNoAuto']        = $this->getPost('deliveryNoAuto');
        $f['purchaseOrder']         = $this->getPost('purchaseOrder');
        $f['containerNo']           = $this->getPost('containerNo');
        $f['sealNo']                = $this->getPost('sealNo');
        $f['containerNo2']          = $this->getPost('containerNo2');
        $f['sealNo2']               = $this->getPost('sealNo2');
        $f['customerName']          = $this->getPost('customerName');
        $f['productName']           = $this->getPost('productName');
        $f['rawMaterialName']       = $this->getPost('rawMaterialName');
        $f['transporter']           = $this->getPost('transporter');
        $f['weightDifference']      = $this->getPost('weightDifference');
        $f['weightDifferencePerc']  = $this->getPost('weightDifferencePerc');
        $f['destination']           = $this->getPost('destination');
        $f['reduceWeight']          = $this->getPost('reduceWeight', '0');
        $f['otherRemarks']          = $this->getPost('otherRemarks');
        $f['customerCode']          = $this->getPost('customerCode');
        $f['supplierCode']          = $this->getPost('supplierCode');
        $f['supplierName']          = $this->getPost('supplierName');
        $f['productCode']           = $this->getPost('productCode');
        $f['rawMaterialCode']       = $this->getPost('rawMaterialCode');
        $f['manualProduct']         = $this->getPost('manualProduct', '0');
        $f['manualRawMaterial']     = $this->getPost('manualRawMaterial', '0');
        $f['productNameTxt']        = $this->getPost('productNameTxt');
        $f['rawMaterialNameTxt']    = $this->getPost('rawMaterialNameTxt');
        $f['manualCustomer']        = $this->getPost('manualCustomer', '0');
        $f['manualSupplier']        = $this->getPost('manualSupplier', '0');
        $f['customerNameTxt']       = $this->getPost('customerNameTxt');
        $f['supplierNameTxt']       = $this->getPost('supplierNameTxt');
        $f['destinationCode']       = $this->getPost('destinationCode');
        $f['transporterCode']       = $this->getPost('transporterCode');
        $f['finalWeight']           = $this->getPost('finalWeight', '0');
        $f['indicatorId2']          = $this->getPost('indicatorId2');
        $f['productDescription']    = $this->getPost('productDescription');
        $project                    = $this->getPost('project');
        $f['project']               = ($project === null || $project === '' || $project === '-') ? null : $project;
        $f['vehiclePlateNo1']       = filter_has_var(INPUT_POST, 'manualVehicle') ? trim($_POST['vehicleNoTxt']) : $this->getPost('vehiclePlateNo1');
        $f['vehiclePlateNo2']       = filter_has_var(INPUT_POST, 'manualVehicle2') ? trim($_POST['vehicleNoTxt2']) : $this->getPost('vehiclePlateNo2');
        $f['vehicleWeight2']        = $this->getPost('vehicleWeight2');
        $f['grossIncoming2']        = $this->getPost('grossIncoming2');
        $f['grossIncomingDate2']    = $this->getPost('grossIncomingDate2');
        $f['emptyContainerWeight2'] = $this->getPost('emptyContainerWeight2', 0);
        $f['replacementContainer']  = $this->getPost('replacementContainer', 0);
        $f['grossWeightBy2']        = $this->getPost('grossWeightBy2', 0);
        $f['tareOutgoing2']         = $this->getPost('tareOutgoing2');
        $f['tareOutgoingDate2']     = $this->getPost('tareOutgoingDate2');
        $f['tareWeightBy2']         = $this->getPost('tareWeightBy2', 0);
        $f['nettWeight2']           = $this->getPost('nettWeight2');
        $f['customerSide'] = [
            'company'         => $this->getPost('customerSideCompany'),
            'removalPassNo'   => $this->getPost('customerSideRemovalPassNo'),
            'licenseNo'       => $this->getPost('customerSideLicenseNo'),
            'moistureContent' => $this->getPost('customerSideMoistureContent'),
            'officerName'     => $this->getPost('customerSideOfficerName'),
            'rainbowDriver'   => $this->getPost('customerSideRainbowDriver'),
            'timeIn'          => $this->getPost('customerSideTimeIn'),
            'timeOut'         => $this->getPost('customerSideTimeOut'),
        ];
        $wt = $f['weightType'];
        if (($wt === 'Normal' || $wt === 'Empty Container') && !empty($f['grossIncoming']) && !empty($f['tareOutgoing'])) {
            $f['isComplete'] = 'Y';
        } elseif (($wt === 'Container' || $wt === 'Different Container') && !empty($f['grossIncoming']) && !empty($f['tareOutgoing']) && !empty($f['grossIncoming2']) && !empty($f['tareOutgoing2'])) {
            $f['isComplete'] = 'Y';
        } else {
            $f['isComplete'] = 'N';
        }
        if (isset($_POST['status']) && $_POST['status'] === 'pending') {
            $f['isComplete'] = 'N';
            $f['isApproved'] = 'N';
        } else {
            $f['isApproved'] = 'Y';
        }
        $f['isCancel'] = 'N';
        return $f;
    }
}
?>
