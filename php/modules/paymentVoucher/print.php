<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';
$_POST = array_merge($_GET, $_POST);
require '../../../vendor/autoload.php';
use Dompdf\Dompdf;

if(isset($_POST['slipType'], $_POST['pvId'])){
    $slipType = $_POST['slipType'];
    $pvId = $_POST['pvId'];
    $paymentVoucherNo = null;
    
    // Get payment voucher details
    $sql = "SELECT * FROM Payment_Voucher WHERE id=?";
    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param('s', $pvId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Get company details
            $companyDetail = searchCompanyById($row['company_id'], $db);
            $compname = $companyDetail['name'] ?? '';
            $compreg = $companyDetail['company_reg_no'] ?? '';
            $compaddress1 = $companyDetail['address_line_1'] ?? '';
            $compaddress2 = $companyDetail['address_line_2'] ?? '';
            $compaddress3 = $companyDetail['address_line_3'] ?? '';
            $compphone = $companyDetail['phone_no'] ?? '';
            $parts = array_filter([
                $companyDetail['address_line_1'],
                $companyDetail['address_line_2'],
                $companyDetail['address_line_3']
            ]);
            $compaddress = implode(', ', $parts);

            // PV Details
            $voucherDate = date('d/m/Y', strtotime($row['voucher_date']));
            $paymentVoucherNo = $row['voucher_no'] ?? '';
            $invoiceNo = $row['invoice_no'] ?? '';
            $outstandingDetails = json_decode($row['outstanding_details'], true);
            $outstandingAmount = number_format($row['outstanding_amount'] ?? 0, 2);
            $deduction_details = json_decode($row['deduction_details'], true);
            $deduction_amount = number_format($row['deduction_amount'] ?? 0, 2);

            $isPdfDownload = (isset($_POST['printType']) && $_POST['printType'] == 'exportDownload');

            if ($slipType == 'pv') {
                $pvLogoPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "pv_logo.png";
                $pvLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($pvLogoPath));

                // Format date to Malay month and year
                $malayMonths = array(
                    1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MAC', 4 => 'APRIL',
                    5 => 'MEI', 6 => 'JUN', 7 => 'JULAI', 8 => 'OGOS',
                    9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DISEMBER'
                );
                $month = date('n', strtotime($row['voucher_date']));
                $year = date('Y', strtotime($row['voucher_date']));
                $formatVoucherDate = $malayMonths[$month] . ' ' . $year;

                ###### Supplier details ######
                $supplier = searchSupplierById($row['supplier_id'], $db);
                $supplierName = $supplier['name'] ?? '';
                $accountNo = $supplier['account_no'] ?? '';
                
                $deductions = json_decode($row['deduction_details'], true);
                $additions = json_decode($row['addition_details'], true);
                $unitPrice = floatval($row['unit_price']);
                $totalNettWeight = floatval($row['total_nett_weight']);
                $totalAmount = floatval(str_replace('RM ', '', $row['total_amount']));
                $totalDeductions = floatval($row['deduction_amount']);
                $totalAdditions = floatval($row['addition_amount']);
                $finalAmount = floatval($row['final_amount']);

                // Use table-based layout for PDF, flex for print view
                if ($isPdfDownload) {
                    $infoRowsHtml = '
                    <table style="width: 100%; border: none; margin-bottom: 10px;">
                        <tr>
                            <td style="border: none; width: 80px; font-weight: bold;">NAMA :</td>
                            <td style="border: none; border-bottom: 1px solid #000;">'.$supplierName.'</td>
                            <td style="border: none; width: 80px; font-weight: bold;">TARIKH :</td>
                            <td style="border: none; border-bottom: 1px solid #000;">'.$voucherDate.'</td>
                        </tr>
                        <tr>
                            <td style="border: none; font-weight: bold;">NO AKAUN :</td>
                            <td style="border: none; border-bottom: 1px solid #000;">'.$invoiceNo.'</td>
                            <td style="border: none;"></td>
                            <td style="border: none;"></td>
                        </tr>
                    </table>';
                    $signatureHtml = '
                    <table style="width: 100%; border: none; margin-top: 30px;">
                        <tr>
                            <td style="border: none; vertical-align: bottom;">
                                <p style="margin: 0;">DITERIMA OLEH :</p>
                                <div style="border-top: 1px solid #000; width: 200px; margin-top: 40px;"></div>
                            </td>
                        </tr>
                    </table>';
                } else {
                    $infoRowsHtml = '
                    <div class="info-row">
                        <div class="info-item">
                            <span class="info-label">NAMA :</span>
                            <span class="info-value">'.$supplierName.'</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">TARIKH :</span>
                            <span class="info-value">'.$voucherDate.'</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-item">
                            <span class="info-label">NO AKAUN :</span>
                            <span class="info-value">'.$invoiceNo.'</span>
                        </div>
                        <div class="info-item" style="visibility: hidden;">
                            <span class="info-label">TARIKH :</span>
                            <span class="info-value">'.$voucherDate.'</span>
                        </div>
                    </div>';
                    $signatureHtml = '
                    <div class="signature">
                        <p>DITERIMA OLEH :</p>
                        <div class="signature-line"></div>
                    </div>';
                }
                
                $message = '
                <html>
                <head>
                    <style>
                        @media print {
                            @page {
                                size: A5 landscape;
                                margin: 0px;
                            }
                        }
                        body {
                            font-family: Arial, sans-serif;
                            font-size: 11px;
                            margin: 20px;
                            padding: 0;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 15px;
                            position: relative;
                        }
                        .header-logo {
                            position: absolute;
                            left: 30px;
                            top: 0px;
                            width: 100px;
                            height: auto;
                        }
                        .header-text {
                            text-align: center;
                        }
                        .header h3 {
                            margin: 0;
                            font-size: 12px;
                            font-weight: bold;
                        }
                        .header p {
                            margin: 2px 0;
                            font-size: 10px;
                        }
                        .title {
                            text-align: center;
                            font-size: 14px;
                            font-weight: bold;
                            margin: 10px 0;
                            text-decoration: underline;
                        }
                        .info-row {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 10px;
                            gap: 20px;
                        }
                        .info-item {
                            display: flex;
                            align-items: baseline;
                            flex: 1;
                        }
                        .info-label {
                            font-weight: bold;
                            width: 100px;
                            display: inline-block;
                        }
                        .info-value {
                            border-bottom: 1px solid #000;
                            flex: 1;
                            display: inline-block;
                        }
                        .data-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 10px 0;
                        }
                        .data-table th, .data-table td {
                            border: 1px solid #000;
                            padding: 5px;
                            text-align: center;
                            font-size: 10px;
                        }
                        .data-table th {
                            font-weight: bold;
                            background-color: #f0f0f0;
                        }
                        .text-right {
                            text-align: right;
                        }
                        .text-left {
                            text-align: left;
                        }
                        .signature {
                            margin-top: 30px;
                        }
                        .signature p {
                            margin: 0;
                        }
                        .signature-line {
                            border-top: 1px solid #000;
                            width: 200px;
                            margin-top: 40px;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <img src="'.$pvLogoBase64.'" alt="Logo" class="header-logo">
                        <div class="header-text">
                            <h3>'.$compname.' (No. Daftar: '.$compreg.')</h3>
                            <p>'.$compaddress1.' '.$compaddress2.'</p>
                            <p>'.$compaddress3.' TEL: '.$compphone.'</p>
                        </div>
                    </div>
                    
                    <div class="title">BAUCER BAYARAN</div>
                    
                    '.$infoRowsHtml.'
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>BIL</th>
                                <th>PERKARA</th>
                                <th>UNIT (M/T)</th>
                                <th>HARGA (RM)</th>
                                <th>JUMLAH (RM)</th>
                            </tr>
                        </thead>
                        <tbody>';

                $bil = 1;
                $message .= '
                    <tr>
                        <td class="text-center">'.$bil.'</td>
                        <td class="text-left">BTS '.$formatVoucherDate.'</td>
                        <td>'.number_format($totalNettWeight, 2).'</td>
                        <td class="text-center">RM'.number_format($unitPrice, 2).'</td>
                        <td class="text-center">RM'.number_format($totalAmount, 2).'</td>
                    </tr>
                ';
                $bil++;
            
                foreach ($additions as $addition) {
                    $message .= '
                            <tr>
                                <td class="text-center">'.$bil.'</td>
                                <td class="text-left">'.$addition['addition_desc'].'</td>
                                <td></td>
                                <td></td>
                                <td class="text-center">RM'.number_format($addition['addition_amount'], 2).'</td>
                            </tr>';
                    $bil++;
                }

                foreach ($deductions as $deduction) {
                    $message .= '
                            <tr>
                                <td class="text-center">'.$bil.'</td>
                                <td class="text-left">'.$deduction['deduction_desc'].'</td>
                                <td></td>
                                <td></td>
                                <td class="text-center">-RM'.number_format($deduction['deduction_amount'], 2).'</td>
                            </tr>';
                    $bil++;
                }
                
                $message .= '
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-left" style="border-right:none"><strong>NO AKAUN BANK : '.$accountNo.'</strong></td>
                                <td class="text-right" style="border-left:none"><strong>JUMLAH (RM)</strong></td>
                                <td class="text-center"><strong>RM'.number_format($finalAmount, 2).'</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    '.$signatureHtml.'
                </body>
                </html>';
            } elseif ($slipType == 'ffbStatement') {
                $voucherDateMonthYear = date('F Y', strtotime($row['voucher_date']));
                $voucherDateFormat = date('d M Y', strtotime($row['voucher_date']));
                $additionsDetails = json_decode($row['addition_details'], true);
                $totalAdditions = floatval($row['addition_amount']);

                $pvLogoPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "pv_logo.png";
                $pvLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($pvLogoPath));

                ###### Supplier details ######
                $supplier = searchSupplierById($row['supplier_id'], $db);
                $supplierName = $supplier['name'] ?? '';
                $supplierPhone = $supplier['phone_no'] ?? '';
                $accountNo = $supplier['account_no'] ?? '';

                ###### Weighing details ######
                $weighingData = array();
                $totalNettWeight = 0;
                $totalAmount = 0;
                if ($weight_stmt = $db->prepare("SELECT * FROM Weight WHERE pv_id = ? AND is_complete = 'Y' AND is_cancel <> 'Y' AND status = '0'")) {
                    $weight_stmt->bind_param('s', $pvId);
                    $weight_stmt->execute();
                    $weight_result = $weight_stmt->get_result();
            
                    while ($weight_row = $weight_result->fetch_assoc()) {
                        $weighingData[] = array(
                            "id" => $weight_row['id'],
                            "transaction_id" => $weight_row['transaction_id'],
                            "transaction_date" => $weight_row['transaction_date'],
                            "lorry_plate_no1" => $weight_row['lorry_plate_no1'],
                            "nett_weight1" => $weight_row['nett_weight1'],
                            "unit_price" => $weight_row['unit_price'],
                            "sub_total" => $weight_row['sub_total'],
                            "sst" => $weight_row['sst'],
                            "total_price" => $weight_row['total_price'],
                            "invoice_no" => $weight_row['invoice_no'],
                        );
                        $totalNettWeight += floatval($weight_row['nett_weight1']);
                        $totalAmount += floatval($weight_row['total_price']);
                    }
                }

                if ($isPdfDownload) {
                    $styleBlock = '
                        @page {
                            size: A4;
                            margin-top: 220px;
                            margin-bottom: 250px;
                            margin-left: 15mm;
                            margin-right: 15mm;
                        }
                        .page-header {
                            position: fixed;
                            top: -50mm;
                            left: 0;
                            right: 0;
                            text-align: center;
                            font-size: 13px;
                            line-height: 1.3;
                        }
                        .page-footer {
                            position: fixed;
                            bottom: -70mm;
                            left: 0;
                            right: 0;
                            width: 100%;
                        }
                        .content-wrapper {
                            margin-top: 0px;
                        }
                        .header-logo {
                            position: absolute;
                            left: 50px;
                            top: 10px;
                            width: 80px;
                            height: auto;
                        }
                    ';
                    $scriptTag = '';
                } else {
                    $styleBlock = '
                        @page {
                            size: A4;
                            margin-top: 220px;
                            margin-bottom: 250px;
                            margin-left: 15mm;
                            margin-right: 15mm;
                            @top-center {
                                content: element(page-header);
                            }
                            @bottom-center {
                                content: element(page-footer);
                            }
                        }
                        .page-header {
                            position: running(page-header);
                            text-align: center;
                            font-size: 13px;
                            line-height: 1.3;
                            padding-top: 10px;
                        }
                        .page-footer {
                            position: running(page-footer);
                            width: 100%;
                        }
                        .content-wrapper {
                            margin-top: 0px;
                        }
                        .header-logo {
                            position: absolute;
                            left: 50px;
                            top: 10px;
                            width: 100px;
                            height: auto;
                        }
                    ';
                    $scriptTag = '<script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"></script>';
                }

                // Info section: use table for dompdf (no flex support), div for Paged.js
                if ($isPdfDownload) {
                    $infoSectionHtml = '
                        <table style="margin: 15px 0; border: none;">
                            <tr>
                                <td style="width: 60%; font-weight: bold; font-size: 16px; border: none;">'.$supplierName.'</td>
                                <td style="width: 20%; border: none;">Invoice No:</td>
                                <td style="width: 20%; border: none;">'.$invoiceNo.'</td>
                            </tr>
                            <tr>
                                <td style="border: none;"></td>
                                <td style="border: none;">Date:</td>
                                <td style="border: none;">'.$voucherDateFormat.'</td>
                            </tr>
                        </table>
                        <div style="margin: 15px 0;">
                            <p>TEL: '.$supplierPhone.'</p>
                        </div>';
                } else {
                    $infoSectionHtml = '
                        <div class="info-section">
                            <div class="info-row">
                                <span class="customer-name">'.$supplierName.'</span>
                                <span class="label">Invoice No:</span>
                                <span class="value">'.$invoiceNo.'</span>
                            </div>
                            <div class="info-row">
                                <span></span>
                                <span class="label">Date:</span>
                                <span class="value">'.$voucherDateFormat.'</span>
                            </div>
                        </div>
                        <div class="info-section">
                            <p>TEL: '.$supplierPhone.'</p>
                        </div>';
                }

                // Footer signatures: use table for dompdf, flex div for Paged.js
                if ($isPdfDownload) {
                    $footerSignaturesHtml = '
                        <table style="border-top: 1px solid #000; width: 100%;">
                            <tr>
                                <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                                    <p style="padding-bottom: 30px;">'.$compname.'</p>
                                    <div style="border-top: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                                    <p style="margin-top: 5px;">Authorised Signature</p>
                                </td>
                                <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                                    <p style="padding-bottom: 30px;">Kindly Acknowledge Receipt</p>
                                    <div style="border-top: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                                    <p style="margin-top: 5px;">Received By</p>
                                </td>
                            </tr>
                        </table>';
                } else {
                    $footerSignaturesHtml = '
                        <div class="footer-signatures border-top">
                            <div>
                                <p style="padding-bottom: 30px;">'.$compname.'</p>
                                <div class="signature-line"></div>
                                <p style="text-align: center; margin-top: 5px;">Authorised Signature</p>
                            </div>
                            <div>
                                <p style="padding-bottom: 30px;">Kindly Acknowledge Receipt</p>
                                <div class="signature-line"></div>
                                <p style="text-align: center; margin-top: 5px;">Received By</p>
                            </div>
                        </div>';
                }

                $message = '
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        '.$scriptTag.'
                        <style>
                            '.$styleBlock.'
                            body {
                                font-family: "Times New Roman", serif;
                                font-size: 14px;
                                margin: 0;
                                padding: 0;
                            }
                            .page-header h2 {
                                margin: 0 0 0 0;
                                font-size: 16px;
                            }
                            .header-text {
                                text-align: center;
                            }
                            .info-section {
                                margin: 15px 0;
                            }
                            .info-row {
                                display: flex;
                                margin-bottom: 5px;
                            }
                            .info-row > span:first-child {
                                width: 60%;
                            }
                            .info-row > span:nth-child(2) {
                                width: 20%;
                            }
                            .info-row > span:nth-child(3) {
                                width: 20%;
                            }
                            .customer-name {
                                font-weight: bold;
                                font-size: 16px;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin: 15px 0;
                            }
                            td {
                                padding: 4px 8px;
                                font-size: 13px;
                            }
                            .table-border {
                                border-top: 1px solid #000;
                                border-bottom: 1px solid #000;
                            }
                            .border-top {
                                border-top: 1px solid #000;
                            }
                            .border-bottom {
                                border-bottom: 1px solid #000;
                            }
                            .text-right {
                                text-align: right;
                            }
                            .text-center {
                                text-align: center;
                            }
                            .summary-section {
                                margin-top: 20px;
                                page-break-inside: avoid;
                                break-inside: avoid;
                            }
                            .page-footer table {
                                margin: 10px 0;
                            }
                            .footer-signatures {
                                display: flex;
                                justify-content: space-between;
                            }
                            .footer-signatures > div {
                                width: 45%;
                                text-align: center;
                            }
                            .signature-line {
                                border-top: 1px solid #000;
                                width: 200px;
                                margin: 50px auto 0 auto;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="page-header">
                            <img src="'.$pvLogoBase64.'" alt="Logo" class="header-logo">
                            <div class="header-text">
                                <h2>'.$compname.'</h2>
                                ('.$compreg.')<br>
                                '.$compaddress1.'<br>
                                '.$compaddress2.'<br>
                                '.$compaddress3.'<br>
                                Tel: '.$compphone.'
                            </div>
                            <div style="margin-top: 15px; font-weight: bold; font-size: 18px;">
                                STATEMENT<br>
                                <span style="font-weight: normal; font-size: 14px;">For The Month Of '.$voucherDateMonthYear.'</span>
                            </div>
                            <div style="border-bottom: 1px solid #000; margin: 10px 0;"></div>
                        </div>

                        <div class="page-footer">
                            <table>
                                <tr>
                                    <td>Cheque/Account No:</td>
                                    <td class="border-bottom" style="width: 200px;">'.$accountNo.'</td>
                                    <td></td>
                                    <td>Add Total Flat Rate Amount:</td>
                                    <td class="text-right border-bottom" style="width: 150px;">0.00</td>
                                </tr>
                                <tr>
                                    <td>Payment Date:</td>
                                    <td class="border-bottom" style="width: 200px;"></td>
                                    <td></td>
                                    <td>Net Amount (RM):</td>
                                    <td class="text-right border-bottom" style="width: 150px;">'.$outstandingAmount.'</td>
                                </tr>
                            </table>
                            '.$footerSignaturesHtml.'
                        </div>

                        <div class="content-wrapper">
                            '.$infoSectionHtml.'

                            <table>
                                <tr class="table-border">
                                    <td class="text-left">Date</td>
                                    <td class="text-center">Ticket No</td>
                                    <td class="text-center">Vehicle No</td>
                                    <td class="text-center">POER %</td>
                                    <td class="text-center">Net Weight<br>(MT)</td>
                                    <td class="text-center">Price (RM)<br>(MT)</td>
                                    <td class="text-center">Total<br>Amount (RM)</td>
                                </tr>';

                                if (!empty($weighingData)) {
                                    foreach ($weighingData as $weight) {
                                        $message .= '
                                            <tr>
                                                <td>'.date('d M Y', strtotime($weight['transaction_date'])).'</td>
                                                <td>'.$weight['transaction_id'].'</td>
                                                <td>'.$weight['lorry_plate_no1'].'</td>
                                                <td class="text-center">0.00</td>
                                                <td class="text-center">'.number_format($weight['nett_weight1']/1000, 2).'</td>
                                                <td class="text-center">'.number_format($weight['unit_price'], 2).'</td>
                                                <td class="text-right">'.number_format($weight['total_price'], 2).'</td>
                                            </tr>
                                        ';
                                    }
                                }
                                  
                                if (!empty($additionsDetails)) {
                                    foreach ($additionsDetails as $weight) {
                                        $message .= '
                                            <tr>
                                                <td>'.date('d M Y', strtotime($row['voucher_date'])).'</td>
                                                <td>'.$weight['addition_desc'].'</td>
                                                <td>-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">'.number_format($weight['addition_amount'], 2).'</td>
                                                <td class="text-right">'.number_format($weight['addition_amount'], 2).'</td>
                                            </tr>
                                        ';
                                    }
                                }

                            $message .= '
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td>Total</td>
                                    <td></td>
                                    <td class="text-center border-top">'.number_format($totalNettWeight/1000, 2).'</td>
                                    <td class="border-top"></td>
                                    <td class="text-right border-top">'.number_format($totalAmount, 2).'</td>
                                </tr>
                            </table>

                            <table class="summary-section">
                                <tbody>
                                    <tr class="border-bottom">
                                        <td class="text-left" width="15%">Date</td>
                                        <td class="text-left" width="25%">Details</td>
                                        <td class="text-left" width="15%">Reference</td>
                                        <td class="text-center" width="15%">Sub Total <br> Excluding GST</td>
                                        <td class="text-center" width="15%">SST Amount <br> (6%)</td>
                                        <td class="text-center" width="15%">Total <br> Amount (RM)</td>
                                    </tr>';

                                    $paymentTotal = 0;
                                    if (!empty($deduction_details)) {
                                        $message .= '
                                        <tr>
                                            <td colspan="6" class="text-left"><b><u>Less '.$deduction_details[0]['deduction_desc'].'</u></b></td>
                                        </tr>';

                                        foreach ($deduction_details as $outstanding) {
                                            $paymentTotal += floatval($outstanding['deduction_amount']);

                                            $desc = $outstanding['deduction_desc'];
                                            $reference = '';
                                            if (in_array(strtolower($desc), ['transport', 'harvesting'])) {
                                                $mt = number_format($totalNettWeight/1000, 2);

                                                $rate = number_format(
                                                    $outstanding['deduction_amount'] / ($totalNettWeight/1000),
                                                    2
                                                );

                                                $desc .= ' Per MT ('.$mt.' MT x '.$rate.')';

                                                $reference = $outstanding['deduction_reference'] ?? '';
                                            }


                                            $message .= '
                                                <tr>
                                                    <td>'.date('d M Y').'</td>
                                                    <td>'.$desc.'</td>
                                                    <td>'.$reference.'</td>
                                                    <td class="text-center">'.number_format($outstanding['deduction_amount'], 2).'</td>
                                                    <td class="text-center">0.00</td>
                                                    <td class="text-center">'.number_format($outstanding['deduction_amount'], 2).'</td>
                                                </tr>
                                            ';
                                        }
                                    }

                        $message .= '
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-center"><b>SubTotal :</b></td>
                                        <td class="text-right"><b>'.number_format($paymentTotal, 2).'</b></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-right">Total(Add/Loss) (RM) :</td>
                                        <td class="text-right">'.number_format(-$paymentTotal, 2).'</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </body>
                    </html>
                ';
            }
            
            if ($isPdfDownload){
                $dompdf = new Dompdf();
                $dompdf->loadHtml($message);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                if ($slipType == 'pv'){
                    $dompdf->stream($paymentVoucherNo.".pdf", array("Attachment" => true));
                } elseif ($slipType == 'ffbStatement'){
                    $dompdf->stream('FFB_Statement_' . $supplierName . '_' . $paymentVoucherNo . ".pdf", array("Attachment" => true));
                }
                exit;
            } else {
                echo json_encode(array(
                    "status" => "success",
                    "message" => $message,
                    "paymentVoucherNo" => $paymentVoucherNo
                ));
            }
        } else {
            echo json_encode(array(
                "status" => "failed",
                "message" => "Payment voucher not found"
            ));
        }
    }
} else {
    echo json_encode(array(
        "status" => "failed",
        "message" => "Missing parameters"
    ));
}
?>