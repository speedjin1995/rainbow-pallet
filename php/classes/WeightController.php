<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../services/WeightService.php';
require_once __DIR__ . '/../services/PrintService.php';

class WeightController extends BaseController {
    protected $table = 'Weight';
    private $service;

    public function __construct($db) {
        parent::__construct($db);
        $this->service = new WeightService($db, $this->username);
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

    // ─── Entry Point ─────────────────────────────────────────────────────────────
    public function handle() {
        try {
            $f = $this->parseFields();
            $isUpdate = !empty($f['weightId']);
            $this->db->begin_transaction();
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
        $f['companyId']             = $this->getPost('companyId');
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
        $f['destinationCode']       = $this->getPost('destinationCode');
        $f['transporterCode']       = $this->getPost('transporterCode');
        $f['finalWeight']           = $this->getPost('finalWeight', '0');
        $f['indicatorId2']          = $this->getPost('indicatorId2');
        $f['productDescription']    = $this->getPost('productDescription');
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
        if (($wt === 'Normal' || $wt === 'Empty Container') && $f['grossIncoming'] != null && $f['tareOutgoing'] != null) {
            $f['isComplete'] = 'Y';
        } elseif (($wt === 'Container' || $wt === 'Different Container') && $f['grossIncoming'] != null && $f['tareOutgoing'] != null && $f['grossIncoming2'] != null && $f['tareOutgoing2'] != null) {
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
