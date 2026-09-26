<?php
class PrintService {

    private $language;
    private $languageArray;
    private $companyDetails;

    public function __construct($db) {
        $this->db = $db;
        $this->language = $_POST['prePrint'] ?? $_SESSION['language'];
        $this->languageArray = $_SESSION['languageArray'];
    }

    private function respond($status, $message) {
        echo json_encode(['status' => $status, 'message' => $message]);
        exit;
    }

    public function handle() {
        if (!isset($_POST['userID'], $_POST['file'], $_POST['isEmptyContainer'])) {
            $this->respond('failed', 'Please fill in all the fields');
        }

        $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

        if ($_POST['file'] === 'weight') {
            $this->handleWeightPrint($id);
        } else {
            $this->respond('failed', 'Invalid file type');
        }
    }

    // Company printed on the slip - the company the weighing record belongs to
    private function loadCompanyDetails($companyId) {
        $stmt = $this->db->prepare("SELECT * FROM Company WHERE id=?");
        $stmt->bind_param('s', $companyId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $this->companyDetails = [
                'name'     => $row['name'],
                'reg'      => $row['company_reg_no'],
                'address1' => $row['address_line_1'],
                'address2' => $row['address_line_2'],
                'address3' => $row['address_line_3'],
                'phone'    => $row['phone_no'],
                'fax'      => $row['fax_no'],
                'email'    => $row['email'] ?? '',
            ];
        }
    }

    private function getPrintHeaderDetails($weightRow) {
        if (($weightRow['transaction_status'] ?? '') === 'Purchase' && !empty($weightRow['customer_code'])) {
            $stmt = $this->db->prepare("SELECT name, company_reg_no, address_line_1, address_line_2, address_line_3, phone_no, email FROM Customer WHERE customer_code=?");
            $stmt->bind_param('s', $weightRow['customer_code']);
            $stmt->execute();
            $customer = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($customer) {
                return [
                    'name'     => $customer['name'],
                    'reg'      => $customer['company_reg_no'],
                    'address1' => $customer['address_line_1'],
                    'address2' => $customer['address_line_2'],
                    'address3' => $customer['address_line_3'],
                    'phone'    => $customer['phone_no'],
                    'email'    => $customer['email'] ?? '',
                ];
            }
        }
        return $this->companyDetails;
    }

    private function getAddressLines($details) {
        return array_filter([$details['address1'], $details['address2'], $details['address3']], function($a) {
            return trim((string)$a) !== '';
        });
    }

    private function getContactLine($details) {
        $line = 'Tel: ' . $this->printValue($details['phone']);
        if (trim((string)$details['email']) !== '') {
            $line .= ' E-mail: ' . $this->printValue($details['email']);
        }
        return $line;
    }

    private function printValue($value) {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    private function formatWeight($weight) {
        if ($weight != 0) {
            $formatted = number_format(ltrim($weight, '0'), 2, '.', ',');
            return preg_replace('/\.00$/', '', $formatted);
        }
        return $weight;
    }

    private function messageLabel($key) {
        if (isset($this->languageArray[$key][$this->language])) {
            return $this->printValue($this->languageArray[$key][$this->language]);
        }
        return $this->printValue($key);
    }

    private function underlinedValue($value, $width = '180px') {
        $displayValue = trim((string)($value ?? '')) === '' ? '&nbsp;' : $this->printValue($value);
        return '<span style="display:inline-block; width: ' . $width . '; box-sizing: border-box; border-bottom: 1px solid #000; padding: 0 6px 2px; line-height: 18px; min-height: 20px; vertical-align: bottom;">' . $displayValue . '</span>';
    }

    private function handleWeightPrint($id) {
        $sql = ($_POST['isEmptyContainer'] === 'Y')
            ? "SELECT * FROM Weight_Container WHERE id=?"
            : "SELECT * FROM Weight WHERE id=?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) { $this->respond('failed', 'Something went wrong'); }
        $stmt->bind_param('s', $id);
        if (!$stmt->execute()) { $this->respond('failed', 'Something went wrong'); }
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) { $this->respond('failed', 'Unable to read data'); }

        $this->loadCompanyDetails($row['company_id']);

        $printTemplate = $_POST['printTemplate'] ?? 'with_weight';
        if ($row['transaction_status'] === 'Purchase') {
            $printTemplate = 'with_weight';
        }

        if ($printTemplate === 'without_weight' && $_POST['isEmptyContainer'] !== 'Y') {
            $this->respond('success', $this->renderWithoutWeight($row));
        }

        if ($printTemplate === 'with_weight' && $_POST['isEmptyContainer'] !== 'Y') {
            $this->respond('success', $this->renderWithWeight($row));
        }

        $this->respond('success', $this->renderFullSlip($row));
    }

    private function renderWithoutWeight($row) {
        $headerDetails = $this->getPrintHeaderDetails($row);
        $companyAddress = $this->getAddressLines($headerDetails);
        $contactLine = $this->getContactLine($headerDetails);
        $transactionDate = !empty($row['transaction_date']) ? date("d/m/Y", strtotime($row['transaction_date'])) : '';

        $driverName = '';
        $driverIc = '';
        if (!empty($row['transporter_code'])) {
            $stmt = $this->db->prepare("SELECT contact_name, ic_no FROM Transporter WHERE transporter_code=?");
            $stmt->bind_param('s', $row['transporter_code']);
            $stmt->execute();
            $transporter = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($transporter) {
                $driverName = $transporter['contact_name'];
                $driverIc   = $transporter['ic_no'];
            }
        }

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Delivery Note</title><style>
            @page { size: A5 landscape; margin: 10mm 12mm; }
            body { color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.25; margin: 0; }
            .delivery-note { width: 100%; }
            .title-row { position: relative; text-align: center; margin-bottom: 5px; }
            .title { display: inline-block; font-size: 17px; font-weight: 700; text-decoration: underline; }
            .note-no { position: absolute; right: 0; top: 2px; font-size: 12px; }
            .company-name { text-align: center; font-size: 20px; font-weight: 700; margin-top: 5px; }
            .company-reg { text-align: center; font-size: 11px; margin-top: 2px; }
            .company-line { text-align: center; font-size: 12px; }
            .contact-line { text-align: center; margin-top: 2px; }
            .separator { border-top: 2px solid #000; margin: 10px 0; }
            .details-table { width: 100%; table-layout: fixed; border-collapse: collapse; margin-top: 6px; }
            .details-table td { padding: 7px 0; vertical-align: bottom; }
            .label-cell-left { width: 16%; white-space: nowrap; }
            .value-cell-left { width: 34%; padding-right: 38px !important; }
            .label-cell-right { width: 18%; white-space: nowrap; }
            .value-cell-right { width: 32%; }
            .signatures { display: flex; justify-content: space-between; gap: 70px; margin-top: 82px; }
            .signature-box { width: 42%; text-align: center; }
            .signature-line { border-top: 1px solid #000; height: 12px; margin-bottom: 8px; }
        </style></head><body><div class="delivery-note">
            <div class="title-row">
                <span class="title">DELIVERY NOTE</span>
                <span class="note-no">No. RP ' . $this->printValue($row['transaction_id']) . '</span>
            </div>
            <div class="company-name">' . $this->printValue($headerDetails['name']) . '</div>
            <div class="company-reg">(Co. ' . $this->printValue($headerDetails['reg']) . ')</div>';

        foreach ($companyAddress as $address) {
            $html .= '<div class="company-line">' . $this->printValue($address) . '</div>';
        }

        $html .= '<div class="contact-line">' . $contactLine . '</div>
            <div class="separator"></div>
            <table class="details-table">
                <tr>
                    <td class="label-cell-left">' . $this->messageLabel('date_code') . ':</td>
                    <td class="value-cell-left">' . $this->underlinedValue($transactionDate, '100%') . '</td>
                    <td class="label-cell-right"></td><td class="value-cell-right"></td>
                </tr>
                <tr>
                    <td class="label-cell-left">' . $this->messageLabel('name_driver_code') . ':</td>
                    <td class="value-cell-left">' . $this->underlinedValue($driverName, '100%') . '</td>
                    <td class="label-cell-right">' . $this->messageLabel('type_of_collection_code') . ':</td>
                    <td class="value-cell-right">' . $this->underlinedValue('', '100%') . '</td>
                </tr>
                <tr>
                    <td class="label-cell-left">' . $this->messageLabel('vehicle_no_code') . ':</td>
                    <td class="value-cell-left">' . $this->underlinedValue($row['lorry_plate_no1'], '100%') . '</td>
                    <td class="label-cell-right">' . $this->messageLabel('collection_location_code') . ':</td>
                    <td class="value-cell-right">' . $this->underlinedValue($row['destination'], '100%') . '</td>
                </tr>
                <tr>
                    <td class="label-cell-left">' . $this->messageLabel('ic_no_of_driver_code') . ':</td>
                    <td class="value-cell-left">' . $this->underlinedValue($driverIc, '100%') . '</td>
                    <td class="label-cell-right">' . $this->messageLabel('signature_of_driver_code') . ':</td>
                    <td class="value-cell-right">' . $this->underlinedValue('', '100%') . '</td>
                </tr>
            </table>
            <div class="separator"></div>
            <div class="signatures">
                <div class="signature-box"><div class="signature-line"></div><div>' . $this->messageLabel('issued_by_code') . '</div></div>
                <div class="signature-box"><div class="signature-line"></div><div>' . $this->messageLabel('received_by_code') . '</div></div>
            </div>
        </div></body></html>';

        return $html;
    }

    private function renderWithWeight($row) {
        $headerDetails = $this->getPrintHeaderDetails($row);
        $companyAddress = $this->getAddressLines($headerDetails);
        $contactLine = $this->getContactLine($headerDetails);
        $transactionDate = !empty($row['transaction_date']) ? date("d/m/Y", strtotime($row['transaction_date'])) : '';
        $grossWeightTime = !empty($row['gross_weight1_date']) ? date("d/m/Y - H:i:s", strtotime($row['gross_weight1_date'])) : '';
        $tareWeightTime  = !empty($row['tare_weight1_date'])  ? date("d/m/Y - H:i:s", strtotime($row['tare_weight1_date']))  : '';

        $product = '-';
        $pid = ($row['transaction_status'] === 'Purchase' || $row['transaction_status'] === 'Local')
            ? $row['raw_mat_code'] : $row['product_code'];

        if ($pid) {
            $stmt = $this->db->prepare("SELECT name FROM Product WHERE product_code=?");
            $stmt->bind_param('s', $pid);
            $stmt->execute();
            $p = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($p) { $product = $p['name']; }
        }

        if ($row['transaction_status'] === 'Purchase' || $row['transaction_status'] === 'Local') {
            $customerName = $this->companyDetails['name'];
            $supplierName = $row['supplier_name'];
        } else {
            $customerName = $row['customer_name'];
            $supplierName = $this->companyDetails['name'];
        }

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Weight Ticket</title><style>
            @page { size: A5 landscape; margin: 9mm 11mm; }
            body { color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 1.22; margin: 0; }
            .ticket { width: 100%; }
            .company-name { text-align: center; font-size: 20px; font-weight: 700; margin-bottom: 3px; }
            .company-line, .contact-line { text-align: center; font-size: 11px; }
            .info-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 20px; font-size: 12px; }
            .info-table td { padding: 3px 0; white-space: nowrap; }
            .info-label { font-weight: 700; width: 12%; }
            .info-value { width: 38%; }
            .weight-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 10px; font-size: 12px; }
            .weight-table th, .weight-table td { border: 1px solid #000; padding: 6px 7px; vertical-align: middle; }
            .weight-table th { text-align: center; font-weight: 700; }
            .text-center { text-align: center; } .text-right { text-align: right; }
            .no-border { border: 0 !important; }
            .summary-label { font-weight: 700; text-align: right; }
            .summary-value { text-align: right; font-weight: 700; }
            .signatures { display: flex; justify-content: space-between; gap: 95px; margin-top: 72px; }
            .signature-box { width: 42%; text-align: center; font-weight: 700; }
            .signature-line { border-top: 1px solid #000; height: 20px; margin-bottom: 8px; }
        </style></head><body><div class="ticket">
            <div class="company-name">' . $this->printValue($headerDetails['name']) . '</div>';

        foreach ($companyAddress as $address) {
            $html .= '<div class="company-line">' . $this->printValue($address) . '</div>';
        }

        $html .= '<div class="contact-line">' . $contactLine . '</div>
            <table class="info-table">
                <tr>
                    <td class="info-label">' . $this->messageLabel('customer_code') . ':</td>
                    <td class="info-value">' . $this->printValue($customerName) . '</td>
                    <td class="info-label">' . $this->messageLabel('ticket_no_code') . ':</td>
                    <td class="info-value">' . $this->printValue($row['transaction_id']) . '</td>
                </tr>
                <tr>
                    <td class="info-label">' . $this->messageLabel('supplier_code') . ':</td>
                    <td class="info-value">' . $this->printValue($supplierName) . '</td>
                    <td class="info-label">' . $this->messageLabel('date_code') . ':</td>
                    <td class="info-value">' . $this->printValue($transactionDate) . '</td>
                </tr>
                <tr>
                    <td class="info-label">' . $this->messageLabel('trans_type_code') . ':</td>
                    <td class="info-value">' . $this->messageLabel($row['transaction_status']) . '</td>
                    <td class="info-label">' . $this->messageLabel('do_no_code') . ':</td>
                    <td class="info-value">' . $this->printValue($row['delivery_no']) . '</td>
                </tr>
            </table>
            <table class="weight-table">
                <tr>
                    <th style="width:18%;">' . $this->messageLabel('vehicle_no_code') . '</th>
                    <th style="width:32%;">' . $this->messageLabel('product_description_code') . '</th>
                    <th style="width:25%;">' . $this->messageLabel('datetime_code') . '</th>
                    <th colspan="2" style="width:25%;">' . $this->messageLabel('weight_code') . ' (KG)</th>
                </tr>
                <tr>
                    <td class="text-center">' . $this->printValue($row['lorry_plate_no1']) . '</td>
                    <td>' . $this->printValue($product) . '</td>
                    <td class="text-center">' . $this->printValue($grossWeightTime) . '</td>
                    <td class="text-center">' . $this->messageLabel('first_code') . '</td>
                    <td class="text-right">' . $this->printValue($this->formatWeight($row['gross_weight1'])) . '</td>
                </tr>
                <tr>
                    <td colspan="2" class="no-border">REMARKS: ' . $this->printValue($row['remarks']) . '</td>
                    <td class="text-center">' . $this->printValue($tareWeightTime) . '</td>
                    <td class="text-center">' . $this->messageLabel('second_code') . '</td>
                    <td class="text-right">' . $this->printValue($this->formatWeight($row['tare_weight1'])) . '</td>
                </tr>
                <tr>
                    <td colspan="2" class="no-border"></td>
                    <td colspan="2" class="summary-label">' . $this->messageLabel('nett_weight_code') . ' (KG)</td>
                    <td class="summary-value">' . $this->printValue($this->formatWeight($row['nett_weight1'])) . '</td>
                </tr>
                <tr>
                    <td colspan="2" class="no-border"></td>
                    <td colspan="2" class="summary-label">' . $this->messageLabel('reduce_weight_code') . '</td>
                    <td class="summary-value">' . $this->printValue($this->formatWeight($row['reduce_weight'])) . '</td>
                </tr>
                <tr>
                    <td colspan="2" class="no-border"></td>
                    <td colspan="2" class="summary-label">' . $this->messageLabel('final_weight_code') . ' (KG)</td>
                    <td class="summary-value">' . $this->printValue($this->formatWeight($row['final_weight'])) . '</td>
                </tr>
            </table>
            <div class="signatures">
                <div class="signature-box"><div class="signature-line"></div><div>' . $this->messageLabel('driver_code') . '</div></div>
                <div class="signature-box"><div class="signature-line"></div><div>' . $this->messageLabel('weighing_by_code') . '</div></div>
            </div>
        </div></body></html>';

        return $html;
    }

    private function renderFullSlip($row) {
        $la = $this->languageArray;
        $lang = $this->language;
        $cd = $this->companyDetails;

        $transactionDate  = date("d/m/Y", strtotime($row['transaction_date']));
        $grossWeightTime  = date("d/m/Y - H:i:s", strtotime($row['gross_weight1_date']));
        $tareWeightTime   = date("d/m/Y - H:i:s", strtotime($row['tare_weight1_date']));
        $grossWeightTime2 = $row['gross_weight2_date'] ? date("d/m/Y - H:i:s", strtotime($row['gross_weight2_date'])) : '';
        $tareWeightTime2  = $row['tare_weight2_date']  ? date("d/m/Y - H:i:s", strtotime($row['tare_weight2_date']))  : '';

        $transacationStatus = match($row['transaction_status']) {
            'Sales'    => 'dispatch_code',
            'Purchase' => 'receiving_code',
            'Local'    => 'internal_transfer_code',
            default    => 'miscellaneous_code',
        };

        $customer = $customerA = $customerA2 = $customerA3 = '';
        $orderSuppWeight = 0;

        if ($row['transaction_status'] === 'Purchase' || $row['transaction_status'] === 'Local') {
            $cid = $row['supplier_code'];
            $orderSuppWeight = floatval($row['supplier_weight']);
            $stmt = $this->db->prepare("SELECT * FROM Supplier WHERE supplier_code=?");
            $stmt->bind_param('s', $cid);
            $stmt->execute();
            $row2 = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row2) {
                $customer  = $row2['name'];
                $customerA = $row2['address_line_1'];
                $customerA2 = $row2['address_line_2'];
                $customerA3 = $row2['address_line_3'];
            }
            $pid = $row['raw_mat_code'];
        } else {
            $cid = $row['customer_code'];
            $orderSuppWeight = floatval($row['order_weight']);
            $stmt = $this->db->prepare("SELECT * FROM Customer WHERE customer_code=?");
            $stmt->bind_param('s', $cid);
            $stmt->execute();
            $row2 = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row2) {
                $customer  = $row2['name'];
                $customerA = $row2['address_line_1'];
                $customerA2 = $row2['address_line_2'];
                $customerA3 = $row2['address_line_3'];
            }
            $pid = $row['product_code'];
        }

        $isEmptyContainer = $_POST['isEmptyContainer'];

        $html = '<html><head><style>
            @media print { @page { size: A5 landscape; margin-left:0.5in; margin-right:0.5in; margin-top:0.1in; margin-bottom:0.1in; } }
            table { width: 100%; border-collapse: collapse; }
            .table th, .table td { padding: 0.70rem; vertical-align: top; border-top: 1px solid #dee2e6; }
            .table-bordered { border: 1px solid #000; }
            .table-bordered th, .table-bordered td { border: 1px solid #000; font-family: sans-serif; font-size: 12px; }
            .table-border { border: 1px solid #000; }
            .row { display: flex; flex-wrap: wrap; margin-top: 20px; margin-right: -15px; margin-left: -15px; }
            .col-md-4 { position: relative; width: 33.333333%; }
        </style></head><body>
        <table style="width:100%;">
            <tr>
                <td style="width:70%;">
                    <p style="font-size:14px;">
                        <span style="font-weight:bold;font-size:16px;margin-bottom:10px;display:inline-block;">' . $cd['name'] . '</span><br>
                        <span>Reg No.: ' . $cd['reg'] . '</span><br>
                        <span>' . $cd['address1'] . '</span><br>
                        <span>' . $cd['address2'] . '</span><br>
                        <span>' . $cd['address3'] . '</span><br>
                        <span>Tel/Fax: ' . $cd['phone'] . ' / ' . $cd['fax'] . '</span>
                    </p>
                </td>
                <td style="vertical-align:top;">
                    <p style="vertical-align:top;font-size:14px;">
                        <span style="font-size:24px;font-weight:bold;margin-bottom:10px;display:inline-block;">' . $la[$transacationStatus][$lang] . ' ' . $la['slip_code'][$lang] . '</span><br>
                        <span>' . $la['ticket_no_code'][$lang] . ' &nbsp;:&nbsp; <b>' . $row['transaction_id'] . '</b></span><br>
                        <span>' . $la['date_code'][$lang] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;' . $transactionDate . '</span><br>
                        <span>' . $la['do_no_code'][$lang] . ' &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;' . $row['delivery_no'] . '</span><br>
                        <span>' . $la['po_no_code'][$lang] . ' &nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;' . $row['purchase_order'] . '</span>
                    </p>
                </td>
            </tr>
            <tr style="visibility:hidden;"><td style="font-size:3px;">Placeholder for empty space</td></tr>
            <tr style="border-top:1px solid black;">
                <td style="vertical-align:top;">
                    <p style="margin-top:5px;font-size:14px;">
                        <span>' . $la['customer_code'][$lang] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left:23px">:&nbsp; <b>' . $customer . '</b></span><br>
                        <span>' . $la['transporter_code'][$lang] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left:12.5px">:&nbsp; ' . $row['transporter'] . '</span><br>
                        <span>' . $la['destination_code'][$lang] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="margin-left:12.5px">:&nbsp; ' . $row['destination'] . '</span>
                    </p>
                </td>
                <td style="vertical-align:top;">
                    <p style="margin-top:5px;font-size:14px;">
                        <span>' . $la['order_weight_code'][$lang] . '</span><span style="margin-left:10px">:&nbsp; <b>' . $row['order_weight'] . '</b></span><br>
                        <span>' . $la['variance_code'][$lang] . '</span><span style="margin-left:37px">:&nbsp; ' . $row['weight_different'] . '</span><br>
                        <span>% ' . $la['variance_code'][$lang] . '</span><span style="margin-left:22px">:&nbsp; ' . $row['weight_different_perc'] . '</span>
                    </p>
                </td>
            </tr>
        </table>';

        if ($row['weight_type'] === 'Different Container' && $isEmptyContainer !== 'Y') {
            $html .= '<table style="width:100%;border:0px solid black;margin-top:10px;">
                <tr><th style="border:1px solid black;">New Empty Entrance Bin</th></tr>
                <tr><td style="border:1px solid black;">' . $row['replacement_container'] . '</td></tr>
                <tr><th style="border:1px solid black;">Weight</th></tr>
                <tr><td style="border:1px solid black;">' . $row['empty_container2_weight'] . '</td></tr>
            </table>';
        } else {
            $html .= '<table style="width:100%;border:0px solid black;margin-top:10px;">
                <tr style="font-size:14px;text-align:center;">
                    <th width="25%" style="border:1px solid black;">' . $la['container_no1_code'][$lang] . '</th>
                    <th width="25%" style="border:1px solid black;">' . $la['seal_no1_code'][$lang] . '</th>
                    <th width="25%" style="border:1px solid black;">' . $la['container_no2_code'][$lang] . '</th>
                    <th width="25%" style="border:1px solid black;">' . $la['seal_no2_code'][$lang] . '</th>
                </tr>
                <tr style="font-size:14px;text-align:center;">
                    <td style="border:1px solid black;">' . (!empty($row['container_no']) ? $row['container_no'] : '&nbsp;') . '</td>
                    <td style="border:1px solid black;">' . $row['seal_no'] . '</td>
                    <td style="border:1px solid black;">' . (!empty($row['container_no2']) ? $row['container_no2'] : '&nbsp;') . '</td>
                    <td style="border:1px solid black;">' . $row['seal_no2'] . '</td>
                </tr>
            </table>';
        }

        if ($row['weight_type'] === 'Container' && $isEmptyContainer !== 'Y') {
            $html .= '<table style="width:100%;border:0px solid black;margin-top:10px;">
                <tr style="font-size:14px;text-align:center;">
                    <th style="border:1px solid black;">Incoming Date/Time</th>
                    <th style="border:1px solid black;">Outgoing Date/Time</th>
                    <th colspan="2" style="border:1px solid black;">Prime Mover No. &amp; Weight (kg)</th>
                    <th style="border:1px solid black;">Tare (kg)</th>
                    <th style="border:1px solid black;">Nett (kg)</th>
                </tr>
                <tr style="font-size:14px;text-align:center;">
                    <td style="border:1px solid black;">' . $grossWeightTime . '</td>
                    <td style="border:1px solid black;">' . $tareWeightTime . '</td>
                    <td style="border:1px solid black;">' . $row['lorry_plate_no1'] . '</td>
                    <td style="border:1px solid black;">' . $this->formatWeight($row['gross_weight1']) . '</td>
                    <td style="border:1px solid black;">' . $this->formatWeight($row['tare_weight1']) . ' kg</td>
                    <td style="border:1px solid black;">' . $this->formatWeight($row['nett_weight1']) . ' kg</td>
                </tr>
                <tr style="font-size:14px;text-align:center;">
                    <td style="border:1px solid black;">' . $grossWeightTime2 . '</td>
                    <td style="border:1px solid black;">' . $tareWeightTime2 . '</td>
                    <td style="border:1px solid black;">' . $row['lorry_plate_no2'] . '</td>
                    <td style="border:1px solid black;">' . $this->formatWeight($row['gross_weight2']) . '</td>
                    <td style="border:1px solid black;">' . $this->formatWeight($row['tare_weight2']) . ' kg</td>
                    <td style="border:1px solid black;">' . $this->formatWeight($row['nett_weight2']) . ' kg</td>
                </tr>
                <tr style="font-size:14px;text-align:center;">
                    <td colspan="4" style="text-align:left;"><b>Transporter &nbsp;:&nbsp;</b><span style="margin-left:10px">' . $row['transporter'] . '</span></td>
                    <td style="border:1px solid black;">Final Weight</td>
                    <td style="border:1px solid black;">' . $this->formatWeight(abs((int)$row['nett_weight1'] - (int)$row['nett_weight2'])) . ' kg</td>
                </tr>
                <tr style="font-size:14px;text-align:center;">
                    <td colspan="4" style="text-align:left;"><b>Destination &nbsp;&nbsp;:&nbsp;</b><span style="margin-left:10px">' . $row['destination'] . '</span></td>
                    <td style="border:1px solid black;">Reduce Weight</td>
                    <td style="border:1px solid black;">' . $this->formatWeight($row['reduce_weight']) . ' kg</td>
                </tr>
                <tr style="font-size:14px;text-align:center;font-weight:bold;">
                    <td colspan="4" style="text-align:left;">Remarks &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;<span style="margin-left:10px">' . $row['remarks'] . '</span></td>
                    <td style="border:1px solid black;">Nett Weight</td>
                    <td style="border:1px solid black;">' . $this->formatWeight($row['final_weight']) . ' kg</td>
                </tr>
            </table>';
        } elseif ($row['weight_type'] === 'Different Container' && $isEmptyContainer !== 'Y') {
            $html .= '<table style="width:100%;border:1px solid black;text-align:left;font-size:14px;margin-top:5px;">
                <tr style="text-align:center;border:1px solid black;">
                    <th rowspan="2" class="table-border" width="10%">Bin</th>
                    <th rowspan="2" class="table-border" width="25%">Date/Time</th>
                    <th rowspan="2" class="table-border">Vehicle</th>
                    <th rowspan="2" class="table-border" width="10%">Gross Weight</th>
                    <th class="table-border" colspan="2">Tare Weight</th>
                    <th rowspan="2" class="table-border">Nett Weight</th>
                </tr>
                <tr style="text-align:center;">
                    <th class="table-border">Vehicle</th>
                    <th class="table-border">Bin</th>
                </tr>
                <tr style="text-align:center;">
                    <td class="table-border">' . $row['container_no'] . '</td>
                    <td class="table-border">In: ' . $row['gross_weight1_date'] . '<br>Out: ' . $row['tare_weight2_date'] . '</td>
                    <td class="table-border">In: ' . $row['lorry_plate_no1'] . '<br>Out: ' . $row['lorry_plate_no2'] . '</td>
                    <td class="table-border">' . $row['gross_weight1'] . ' kg<br>' . $row['tare_weight2'] . ' kg</td>
                    <td class="table-border">' . $row['tare_weight1'] . ' kg<br>' . $row['lorry_no2_weight'] . ' kg</td>
                    <td class="table-border">-<br>' . $row['nett_weight1'] . ' kg</td>
                    <td class="table-border">-<br>' . $row['nett_weight2'] . ' kg</td>
                </tr>
                <tr style="text-align:center;">
                    <td class="table-border" colspan="6" style="text-align:right;padding-right:20px;">Final Weight</td>
                    <td class="table-border">' . $row['final_weight'] . ' kg</td>
                </tr>
            </table>
            <div style="margin-top:5px;font-size:14px;margin-bottom:60px;">
                <span>Transporter&nbsp;&nbsp;:&nbsp;&nbsp;' . $row['transporter'] . '</span><br>
                <span>Remarks&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;' . $row['remarks'] . '</span>
            </div>';
        } else {
            $html .= '<br><table style="width:100%;border:0px solid black;margin-top:-10px;">
                <tr>
                    <th style="border:1px solid black;font-size:18px;text-align:center;" width="20%">' . $la['vehicle_no_code'][$lang] . '</th>
                    <th colspan="2" style="border:1px solid black;font-size:18px;text-align:center;" width="30%">' . $la['product_description_code'][$lang] . '</th>
                    <th style="border:1px solid black;font-size:18px;text-align:center;" width="25%">' . $la['datetime_code'][$lang] . '</th>
                    <th colspan="2" style="border:1px solid black;font-size:18px;text-align:center;" width="20%">' . $la['weight_code'][$lang] . ' (' . $la['kg_code'][$lang] . ')</th>
                </tr>
                <tr style="font-size:16px;text-align:center;">
                    <td rowspan="2" style="border:1px solid black;">' . $row['lorry_plate_no1'] . '</td>';

            if ($row['transaction_status'] === 'Purchase' || $row['transaction_status'] === 'Local') {
                $html .= '<td rowspan="2" colspan="2" style="border:1px solid black;">' . $row['raw_mat_name'] . '</td>';
            } else {
                $html .= '<td rowspan="2" colspan="2" style="border:1px solid black;">' . $row['product_name'] . '</td>';
            }

            $html .= '<td style="border:1px solid black;">' . $grossWeightTime . '</td>
                    <td style="border:1px solid black;font-weight:bold;">' . $la['in_code'][$lang] . '</td>
                    <td style="border:1px solid black;">' . $this->formatWeight($row['gross_weight1']) . ' kg</td>
                </tr>
                <tr style="font-size:16px;text-align:center;">
                    <td style="border:1px solid black;">' . $tareWeightTime . '</td>
                    <td style="border:1px solid black;font-weight:bold;">' . $la['out_code'][$lang] . '</td>
                    <td style="border:1px solid black;">' . $this->formatWeight($row['tare_weight1']) . ' kg</td>
                </tr>
                <tr>
                    <td colspan="4">' . $la['remarks_code'][$lang] . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;<span style="margin-left:10px">' . $row['remarks'] . '</span></td>
                    <td style="border:1px solid black;font-size:16px;text-align:center;">' . $la['reduce_code'][$lang] . '</td>
                    <td style="border:1px solid black;font-size:16px;text-align:center;">' . $this->formatWeight($row['reduce_weight']) . ' kg</td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td style="border:1px solid black;font-size:16px;font-weight:bold;text-align:center;">' . $la['nett_code'][$lang] . '</td>
                    <td style="border:1px solid black;font-size:16px;font-weight:bold;text-align:center;">' . $this->formatWeight($row['final_weight']) . ' kg</td>
                </tr>
            </table><br>';
        }

        $html .= '<table style="width:100%;position:fixed;bottom:0;left:0;">
            <tr>
                <td style="vertical-align:top;font-size:14px;width:25%;">
                    <hr width="100%" style="margin-left:0;text-align:left;">
                    <span>' . $la['first_weight_by_code'][$lang] . ': ' . $row['gross_weight_by1'] . '<br>' . $la['second_weight_by_code'][$lang] . ': ' . $row['tare_weight_by1'] . '</span>
                </td>
                <td style="width:2%;"></td>
                <td style="vertical-align:top;font-size:14px;width:25%;">
                    <hr width="100%" style="margin-left:0;text-align:left;">
                    <span>' . $la['acknowledge_by_admin_code'][$lang] . '</span>
                </td>
                <td style="width:2%;"></td>
                <td style="vertical-align:top;font-size:14px;width:25%;">
                    <hr width="100%" style="margin-left:0;text-align:left;">
                    <span>' . $la['received_by_code'][$lang] . '</span><br>
                    <span>' . $la['name_code'][$lang] . ': </span><br>
                    <span>I/C: </span>
                </td>
            </tr>
        </table></body></html>';

        return $html;
    }
}