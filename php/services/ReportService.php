<?php
require_once __DIR__ . '/BaseService.php';

class ReportService extends BaseService {
    // Weight.transaction_status => Reports permission module
    private $moduleMap = [
        'Sales'    => 'Sales',
        'Purchase' => 'Purchase',
        'Port'     => 'Port',
        'Misc'     => 'Miscellaneous',
        'Local'    => 'Local',
    ];

    // DataTables column => DB column (only whitelisted columns can be sorted)
    private $sortColumns = [
        'id', 'transaction_id', 'transaction_status', 'weight_type', 'transaction_date', 'lorry_plate_no1', 'lorry_plate_no2',
        'supplier_weight', 'customer_code', 'customer_name', 'supplier_code', 'supplier_name', 'product_code', 'product_name',
        'container_no', 'seal_no', 'invoice_no', 'purchase_order', 'delivery_no', 'transporter_code', 'transporter',
        'destination_code', 'destination', 'remarks', 'gross_weight1', 'gross_weight1_date', 'tare_weight1', 'tare_weight1_date',
        'nett_weight1', 'gross_weight2', 'gross_weight2_date', 'tare_weight2', 'tare_weight2_date', 'nett_weight2',
        'final_weight', 'weight_different', 'is_complete', 'is_cancel', 'manual_weight', 'created_date', 'created_by',
        'modified_date', 'modified_by',
    ];

    public function filter($post) {
        $draw       = intval($post['draw'] ?? 0);
        $start      = intval($post['start'] ?? 0);
        $length     = intval($post['length'] ?? 10);
        $columnName = $post['columns'][$post['order'][0]['column'] ?? 0]['data'] ?? 'id';
        if ($columnName === 'customer') $columnName = 'customer_name';
        $sortColumn = in_array($columnName, $this->sortColumns, true) ? $columnName : 'id';
        $sortOrder  = strtolower($post['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

        $scope   = $this->buildScope($post['transactionStatus'] ?? '', $post['company'] ?? null);
        $filters = $this->buildTableFilters($post);

        $totalRecords  = $this->count($scope['sql'], $scope['types'], $scope['values']);
        $where  = $scope['sql'] . $filters['sql'];
        $types  = $scope['types'] . $filters['types'];
        $values = array_merge($scope['values'], $filters['values']);
        $totalFiltered = $this->count($where, $types, $values);

        $rows = $this->fetchAll(
            "SELECT * FROM Weight WHERE status = '0'" . $where . " ORDER BY {$sortColumn} {$sortOrder} LIMIT ?, ?",
            $types . 'ii',
            array_merge($values, [$start, $length])
        );

        $language      = $_SESSION['language'] ?? 'en';
        $languageArray = $_SESSION['languageArray'] ?? [];
        $counts = ['Sales' => 0, 'Purchase' => 0, 'Local' => 0, 'Port' => 0, 'Misc' => 0];
        $statusLabels = [
            'Sales'    => 'dispatch_code',
            'Purchase' => 'receiving_code',
            'Misc'     => 'miscellaneous_code',
            'Port'     => 'trx_to_port_code',
        ];
        $weightTypeLabels = [
            'Container'           => 'primer_mover_code',
            'Empty Container'     => 'primer_mover_container_code',
            'Different Container' => 'primer_mover_different_bins_code',
            'Normal'              => 'normal_weighing_code',
        ];

        $data = [];
        foreach ($rows as $row) {
            $status = isset($statusLabels[$row['transaction_status']]) ? $row['transaction_status'] : 'Local';
            $counts[$status]++;
            $labelKey = $statusLabels[$status] ?? 'internal_transfer_code';
            $transactionStatus = $languageArray[$labelKey][$language] ?? $row['transaction_status'];

            $weightType = isset($weightTypeLabels[$row['weight_type']])
                ? ($languageArray[$weightTypeLabels[$row['weight_type']]][$language] ?? $row['weight_type'])
                : $row['weight_type'];

            $isPurchase = $this->isPurchaseType($row['transaction_status']);
            $data[] = [
                'id'                  => $row['id'],
                'transaction_id'      => $row['transaction_id'],
                'transaction_status'  => $transactionStatus,
                'weight_type'         => $weightType,
                'transaction_date'    => $row['transaction_date'],
                'lorry_plate_no1'     => $row['lorry_plate_no1'],
                'lorry_plate_no2'     => $row['lorry_plate_no2'],
                'supplier_weight'     => $row['supplier_weight'],
                'customer_code'       => $row['customer_code'],
                'customer_name'       => $row['customer_name'],
                'supplier_code'       => $row['supplier_code'],
                'supplier_name'       => $row['supplier_name'],
                'customer'            => $isPurchase ? $row['supplier_name'] : $row['customer_name'],
                'product_code'        => $isPurchase ? $row['raw_mat_code'] : $row['product_code'],
                'product_name'        => $isPurchase ? $row['raw_mat_name'] : $row['product_name'],
                'container_no'        => $row['container_no'],
                'seal_no'             => $row['seal_no'],
                'invoice_no'          => $row['invoice_no'],
                'purchase_order'      => $row['purchase_order'],
                'delivery_no'         => $row['delivery_no'],
                'transporter_code'    => $row['transporter_code'],
                'transporter'         => $row['transporter'],
                'destination_code'    => $row['destination_code'],
                'destination'         => $row['destination'],
                'remarks'             => $row['remarks'],
                'gross_weight1'       => $row['gross_weight1'],
                'gross_weight1_date'  => $row['gross_weight1_date'],
                'tare_weight1'        => $row['tare_weight1'],
                'tare_weight1_date'   => $row['tare_weight1_date'],
                'nett_weight1'        => $row['nett_weight1'],
                'gross_weight2'       => $row['gross_weight2'],
                'gross_weight2_date'  => $row['gross_weight2_date'],
                'tare_weight2'        => $row['tare_weight2'],
                'tare_weight2_date'   => $row['tare_weight2_date'],
                'nett_weight2'        => $row['nett_weight2'],
                'final_weight'        => $row['final_weight'],
                'weight_different'    => $row['weight_different'],
                'is_complete'         => $row['is_complete'],
                'is_cancel'           => $row['is_cancel'],
                'manual_weight'       => $row['manual_weight'],
                'indicator_id'        => $row['indicator_id'],
                'weighbridge_id'      => $row['weighbridge_id'],
                'created_date'        => $row['created_date'],
                'created_by'          => $row['created_by'],
                'modified_date'       => $row['modified_date'],
                'modified_by'         => $row['modified_by'],
                'indicator_id_2'      => $row['indicator_id_2'],
                'product_description' => $row['product_description'],
            ];
        }

        return [
            'draw'                 => $draw,
            'iTotalRecords'        => $totalRecords,
            'iTotalDisplayRecords' => $totalFiltered,
            'aaData'               => $data,
            'salesTotal'           => $counts['Sales'],
            'purchaseTotal'        => $counts['Purchase'],
            'localTotal'           => $counts['Local'],
            'miscTotal'            => $counts['Misc'],
        ];
    }

    /**
     * Tab-separated Excel export (SUMMARY layout)
     */
    public function exportExcel($get) {
        $rows = $this->fetchExportRows($get);
        $isPurchase = $this->isPurchaseType($get['transactionStatus'] ?? '');

        if ($isPurchase) {
            $fields = ['BIL', 'SUPPLIER', 'DATE IN', 'NO DO', 'NO TICKET', 'NO LORRY', 'EDT', 'FIRST', 'SECOND', 'MC', 'NET',
                'DATE DN', 'COMPANY', 'REMOVAL PASS NO.', 'NO. LESEN', 'MOISTURE CONTENT', 'NAMA PEGAWAI', 'DRIVER RAINBOW', 'TIME IN/TIME OUT'];
        } else {
            $fields = ['BIL', 'CUSTOMER', 'DATE', 'NO DO', 'NO TICKET', 'NO LORRY', 'EDT', 'FIRST (RP)', 'SECOND (RP)', 'MC', 'NET(RP)',
                'NO DO', 'FIRST (MECO)', 'SECOND (MECO)', 'MC', 'NET (MECO)', 'WEIGHT DIFFERENCE'];
        }

        $content = implode("\t", $fields) . "\n";
        if (empty($rows)) {
            $content .= 'No records found...' . "\n";
        }

        $no = 1;
        foreach ($rows as $row) {
            if ($isPurchase) {
                $line = [$no, $row['supplier_name'], $row['transaction_date'], $row['delivery_no'], $row['transaction_id'], $row['lorry_plate_no1'], '', $row['gross_weight1'], $row['tare_weight1'], '', $row['nett_weight1'],
                    '', $row['customer_side_company'], $row['customer_side_removal_pass_no'], $row['customer_side_license_no'], $row['customer_side_moisture_content'], $row['customer_side_officer_name'], $row['customer_side_rainbow_driver'], $row['customer_side_time_in'] . '/' . $row['customer_side_time_out']];
            } else {
                $line = [$no, $row['customer_name'], $row['transaction_date'], $row['delivery_no'], $row['transaction_id'], $row['lorry_plate_no1'], '', $row['gross_weight1'], $row['tare_weight1'], '', $row['nett_weight1'],
                    $row['cust_side_do_no'], $row['cust_side_first_weight'], $row['cust_side_second_weight'], $row['cust_side_mc'], $row['cust_side_nett_weight'], $row['weight_difference']];
            }
            $content .= implode("\t", array_map([$this, 'filterExcelValue'], $line)) . "\n";
            $no++;
        }

        return [
            'fileName' => 'Weight-data_' . date('Y-m-d') . '.xls',
            'content'  => $content,
        ];
    }

    /**
     * Printable HTML report (SUMMARY or detailed, grouped by product)
     */
    public function exportPdf($post) {
        $rows = $this->fetchExportRows($post);

        if (($post['reportType'] ?? '') === 'SUMMARY') {
            return $this->buildSummaryHtml($rows, $this->isPurchaseType($post['transactionStatus'] ?? ''));
        }
        // Legacy behaviour: the detail layout switches supplier/customer columns on the 'status' field
        return $this->buildDetailHtml($rows, $this->isPurchaseType($post['status'] ?? ''));
    }

    /**
     * Rows for Excel/PDF: selected ids (isMulti = Y) or every row matching the filters, always within the user's scope
     */
    private function fetchExportRows($input) {
        $scope = $this->buildScope($input['transactionStatus'] ?? '', $input['company'] ?? null);

        if (($input['isMulti'] ?? 'N') === 'Y') {
            $rawIds = $input['ids'] ?? '';
            $ids = array_values(array_filter(array_map('intval', is_array($rawIds) ? $rawIds : explode(',', (string) $rawIds))));
            if (empty($ids)) {
                return [];
            }
            return $this->fetchAll(
                "SELECT * FROM Weight WHERE status = '0' AND id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")" . $scope['sql'] . " ORDER BY transaction_date ASC",
                str_repeat('i', count($ids)) . $scope['types'],
                array_merge($ids, $scope['values'])
            );
        }

        $filters = $this->buildExportFilters($input);
        return $this->fetchAll(
            "SELECT * FROM Weight WHERE status = '0'" . $scope['sql'] . $filters['sql'] . " ORDER BY transaction_date ASC",
            $scope['types'] . $filters['types'],
            array_merge($scope['values'], $filters['values'])
        );
    }

    /**
     * Company/plant restriction derived from the report module.
     * Unknown module (e.g. transaction status '-') falls back to the most restrictive scope except for SADMIN.
     */
    private function buildScope($transactionStatus, $requestedCompany) {
        $module = $this->moduleMap[$transactionStatus] ?? '';
        $sql    = '';
        $types  = '';
        $values = [];

        // Determine company on the backend - never trust frontend value for restricted users
        if (hasModulePermission('Reports', $module, ['view_all_companies'])) {
            $companyId = ($requestedCompany !== null && $requestedCompany !== '' && $requestedCompany !== '-') ? intval($requestedCompany) : 0;
        } else {
            $companyId = intval($_SESSION['company_id'] ?? 0);
        }
        if ($companyId > 0) {
            $sql .= " AND company_id = ?"; $types .= 'i'; $values[] = $companyId;
        }

        if (!hasModulePermission('Reports', $module, ['view_all_plants'])) {
            $plants = $this->getAllowedPlantCodes();
            if (empty($plants)) {
                $sql .= " AND 1=0";
            } else {
                $sql   .= " AND plant_code IN (" . implode(',', array_fill(0, count($plants), '?')) . ")";
                $types .= str_repeat('s', count($plants));
                $values = array_merge($values, $plants);
            }
        }

        return ['sql' => $sql, 'types' => $types, 'values' => $values];
    }

    /**
     * Plants a user without view_all_plants may see:
     * the plant selected at login if any, otherwise every plant the user is tied to
     */
    private function getAllowedPlantCodes() {
        $selectedPlantId = intval($_SESSION['selected_plant_id'] ?? 0);
        if ($selectedPlantId > 0) {
            $rows = $this->fetchAll("SELECT plant_code FROM Plant WHERE id = ? AND status = '0'", 'i', [$selectedPlantId]);
            return array_column($rows, 'plant_code');
        }
        return array_values((array) ($_SESSION['plant'] ?? []));
    }

    /**
     * Search filters sent by the report DataTables
     */
    private function buildTableFilters($input) {
        $f = ['sql' => '', 'types' => '', 'values' => []];
        $this->addDateRange($f, $input);

        $this->addEquals($f, $input, [
            'transactionStatus' => 'transaction_status',
            'customer'          => 'customer_code',
            'supplier'          => 'supplier_code',
            'customerType'      => 'customer_type',
            'weightType'        => 'weight_type',
            'product'           => 'product_code',
            'rawMaterial'       => 'raw_mat_code',
            'destination'       => 'destination',
            'plant'             => 'plant_code',
        ]);

        $vehicle = $this->value($input, 'vehicle');
        if ($vehicle !== null) {
            $this->add($f, " AND lorry_plate_no1 LIKE ?", 's', ["%{$vehicle}%"]);
        }

        $status = $this->value($input, 'status');
        if ($status === null) {
            $f['sql'] .= " AND is_complete = 'Y'";
        } elseif ($status === 'Complete') {
            $f['sql'] .= " AND is_complete = 'Y'";
        } elseif ($status === 'Cancelled') {
            $f['sql'] .= " AND is_cancel = 'Y'";
        }

        $invDelPo = $this->value($input, 'invDelPo');
        if ($invDelPo !== null) {
            $like = "%{$invDelPo}%";
            $this->add($f, " AND (purchase_order LIKE ? OR invoice_no LIKE ? OR delivery_no LIKE ?)", 'sss', [$like, $like, $like]);
        }

        $search = trim($input['search']['value'] ?? '');
        if ($search !== '') {
            $like = "%{$search}%";
            $this->add($f, " AND (transaction_id LIKE ? OR lorry_plate_no1 LIKE ?)", 'ss', [$like, $like]);
        }

        return $f;
    }

    /**
     * Search filters sent by the Excel/PDF exports
     */
    private function buildExportFilters($input) {
        $f = ['sql' => '', 'types' => '', 'values' => []];
        $this->addDateRange($f, $input);

        $this->addEquals($f, $input, [
            'transactionStatus' => 'transaction_status',
            'customer'          => 'customer_code',
            'supplier'          => 'supplier_code',
            'vehicle'           => 'lorry_plate_no1',
            'product'           => 'product_code',
            'rawMat'            => 'raw_mat_code',
            'destination'       => 'destination',
            'plant'             => 'plant_code',
        ]);

        foreach (['weighingType' => 'weight_type', 'customerType' => 'customer_type'] as $key => $column) {
            $value = $this->value($input, $key);
            if ($value !== null) {
                $this->add($f, " AND {$column} LIKE ?", 's', ["%{$value}%"]);
            }
        }

        $status = $this->value($input, 'status');
        if ($status === 'Cancelled') {
            $f['sql'] .= " AND is_cancel = 'Y'";
        } elseif ($status === 'Pending') {
            $f['sql'] .= " AND is_complete = 'N' AND is_cancel = 'N'";
        } elseif ($status !== null) {
            $f['sql'] .= " AND is_complete = 'Y'";
        }

        return $f;
    }

    private function addDateRange(&$f, $input) {
        $from = $this->toDbDate($input['fromDate'] ?? '', '00:00:00');
        if ($from !== null) {
            $this->add($f, " AND transaction_date >= ?", 's', [$from]);
        }
        $to = $this->toDbDate($input['toDate'] ?? '', '23:59:59');
        if ($to !== null) {
            $this->add($f, " AND transaction_date <= ?", 's', [$to]);
        }
    }

    private function addEquals(&$f, $input, $map) {
        foreach ($map as $key => $column) {
            $value = $this->value($input, $key);
            if ($value !== null) {
                $this->add($f, " AND {$column} = ?", 's', [$value]);
            }
        }
    }

    private function add(&$f, $sql, $types, $values) {
        $f['sql']   .= $sql;
        $f['types'] .= $types;
        $f['values'] = array_merge($f['values'], $values);
    }

    // Trimmed request value, or null when empty / '-'
    private function value($input, $key) {
        if (!isset($input[$key]) || is_array($input[$key])) {
            return null;
        }
        $value = trim($input[$key]);
        return ($value === '' || $value === '-') ? null : $value;
    }

    private function isPurchaseType($transactionStatus) {
        return $transactionStatus === 'Purchase' || $transactionStatus === 'Local';
    }

    private function count($where, $types, $values) {
        $rows = $this->fetchAll("SELECT COUNT(*) AS c FROM Weight WHERE status = '0'" . $where, $types, $values);
        return intval($rows[0]['c'] ?? 0);
    }

    private function fetchAll($sql, $types = '', $values = []) {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new mysqli_sql_exception('Prepare failed');
        }
        if ($types !== '') {
            $stmt->bind_param($types, ...$values);
        }
        if (!$stmt->execute()) {
            $stmt->close();
            throw new mysqli_sql_exception('Execute failed');
        }
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // d-m-Y => Y-m-d {time}
    private function toDbDate($value, $time) {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $date = DateTime::createFromFormat('d-m-Y', trim($value));
        return $date ? $date->format('Y-m-d') . ' ' . $time : null;
    }

    private function formatTime($value) {
        if (empty($value)) {
            return '';
        }
        return (new DateTime($value))->format('H:i');
    }

    private function formatMt($value) {
        return number_format((float) $value / 1000, 2);
    }

    private function formatOptionalMt($value) {
        return !empty($value) ? $this->formatMt($value) : '';
    }

    // Escape a value for the tab-separated Excel export
    private function filterExcelValue($str) {
        $str = preg_replace("/\t/", "\\t", (string) $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }

    private function buildSummaryHtml($rows, $isPurchase) {
        $html = '
        <html>
            <head>
                <style>
                    @media print {
                        @page {
                            size: landscape;
                            margin-left: 0.3in;
                            margin-right: 0.3in;
                            margin-top: 0.1in;
                            margin-bottom: 0.1in;
                        }
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .table th, .table td {
                        padding: 0.50rem;
                        vertical-align: top;
                        border-top: 1px solid #dee2e6;
                    }
                    .table-bordered {
                        border: 1px solid #000000;
                    }
                    .table-bordered th, .table-bordered td {
                        border: 1px solid #000000;
                        font-family: sans-serif;
                        font-size: 10px;
                    }
                </style>
            </head>
            <body>
                <table class="table-bordered" style="width:100%;">
                    <thead>
                        <tr style="font-size: 9px; text-align: center; background-color: #f0f0f0;">
                            <th>BIL</th>
                            <th>' . ($isPurchase ? 'SUPPLIER' : 'CUSTOMER') . '</th>
                            <th>DATE</th>
                            <th>NO DO</th>
                            <th>NO TICKET</th>
                            <th>NO LORRY</th>
                            <th>EDT</th>
                            <th>FIRST' . ($isPurchase ? '' : ' (RP)') . '</th>
                            <th>SECOND' . ($isPurchase ? '' : ' (RP)') . '</th>
                            <th>MC</th>
                            <th>NET' . ($isPurchase ? '' : ' (RP)') . '</th>';

        if ($isPurchase) {
            $html .= '
                            <th>DATE DN</th>
                            <th>COMPANY</th>
                            <th>REMOVAL PASS NO.</th>
                            <th>NO. LESEN</th>
                            <th>MOISTURE CONTENT</th>
                            <th>NAMA PEGAWAI</th>
                            <th>DRIVER RAINBOW</th>
                            <th>TIME IN/OUT</th>';
        } else {
            $html .= '
                            <th>NO DO</th>
                            <th>FIRST (MECO)</th>
                            <th>SECOND (MECO)</th>
                            <th>MC</th>
                            <th>NET (MECO)</th>
                            <th>WEIGHT DIFF</th>';
        }

        $html .= '
                        </tr>
                    </thead>
                    <tbody>';

        $no = 1;
        foreach ($rows as $row) {
            $html .= '<tr style="font-size: 9px; text-align: center;">
                <td>' . $no . '</td>
                <td>' . ($isPurchase ? $row['supplier_name'] : $row['customer_name']) . '</td>
                <td>' . (new DateTime($row['transaction_date']))->format('d/m/Y') . '</td>
                <td>' . $row['delivery_no'] . '</td>
                <td>' . $row['transaction_id'] . '</td>
                <td>' . $row['lorry_plate_no1'] . '</td>
                <td></td>
                <td>' . $row['gross_weight1'] . '</td>
                <td>' . $row['tare_weight1'] . '</td>
                <td></td>
                <td>' . $row['nett_weight1'] . '</td>';

            if ($isPurchase) {
                $html .= '
                <td></td>
                <td>' . $row['customer_side_company'] . '</td>
                <td>' . $row['customer_side_removal_pass_no'] . '</td>
                <td>' . $row['customer_side_license_no'] . '</td>
                <td>' . $row['customer_side_moisture_content'] . '</td>
                <td>' . $row['customer_side_officer_name'] . '</td>
                <td>' . $row['customer_side_rainbow_driver'] . '</td>
                <td>' . $row['customer_side_time_in'] . '/' . $row['customer_side_time_out'] . '</td>';
            } else {
                $html .= '
                <td>' . $row['cust_side_do_no'] . '</td>
                <td>' . $row['cust_side_first_weight'] . '</td>
                <td>' . $row['cust_side_second_weight'] . '</td>
                <td>' . $row['cust_side_mc'] . '</td>
                <td>' . $row['cust_side_nett_weight'] . '</td>
                <td>' . $row['weight_difference'] . '</td>';
            }

            $html .= '</tr>';
            $no++;
        }

        $html .= '
                    </tbody>
                </table>
            </body>
        </html>';

        return $html;
    }

    private function buildDetailHtml($rows, $isPurchase) {
        $languageArray = $_SESSION['languageArray'] ?? [];
        $statusLabels = [
            'Sales'    => 'dispatch_code',
            'Purchase' => 'receiving_code',
            'Port'     => 'trx_to_port_code',
            'Misc'     => 'miscellaneous_code',
        ];

        $html = '
        <html>
            <head>
                <style>
                    @media print {
                        @page {
                            margin-left: 0.5in;
                            margin-right: 0.5in;
                            margin-top: 0.1in;
                            margin-bottom: 0.1in;
                        }
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .table th, .table td {
                        padding: 0.70rem;
                        vertical-align: top;
                        border-top: 1px solid #dee2e6;
                    }
                    .table-bordered {
                        border: 1px solid #000000;
                    }
                    .table-bordered th, .table-bordered td {
                        border: 1px solid #000000;
                        font-family: sans-serif;
                        font-size: 12px;
                    }
                    .row {
                        display: flex;
                        flex-wrap: wrap;
                        margin-top: 20px;
                        margin-right: -15px;
                        margin-left: -15px;
                    }
                    .col-md-4{
                        position: relative;
                        width: 33.333333%;
                    }
                </style>
            </head>
            <body>
                <table style="width:100%;">
                    <thead>
                        <tr style="font-size: 9px; text-align: center;">
                            <th>TRANSACTION <br>ID</th>
                            <th>TRANSACTION <br>DATE</th>
                            <th>TRANSACTION <br>STATUS</th>
                            <th>LORRY <br>NO.</th>
                            <th>' . ($isPurchase ? 'SUPPLIER <br>CODE' : 'CUSTOMER <br>CODE') . '</th>
                            <th>' . ($isPurchase ? 'SUPPLIER' : 'CUSTOMER') . '</th>
                            <th>' . ($isPurchase ? 'RAW MAT <br>CODE' : 'PRODUCT <br>CODE') . '</th>
                            <th>' . ($isPurchase ? 'RAW MAT' : 'PRODUCT') . '</th>
                            <th>DESTINATION <br>CODE</th>
                            <th>DESTINATION</th>
                            <th>PO NO.</th>
                            <th>DO NO.</th>
                            <th>CONTAINER <br>NO.</th>
                            <th>SEAL NO.</th>
                            <th>CONTAINER <br>NO. 2</th>
                            <th>SEAL NO. 2</th>
                            <th>ORDER WEIGHT</th>
                            <th>SUPPLIER WEIGHT</th>
                            <th>INCOMING <br>(MT)</th>
                            <th>OUTGOING <br>(MT)</th>
                            <th>NET <br>(MT)</th>
                            <th>IN TIME</th>
                            <th>OUT TIME</th>
                            <th>INCOMING 2 <br>(MT)</th>
                            <th>OUTGOING 2 <br>(MT)</th>
                            <th>NET 2 <br>(MT)</th>
                            <th>IN TIME 2</th>
                            <th>OUT TIME 2</th>
                            <th>VARIANCE</th>
                            <th>SUB TOTAL WEIGHT</th>
                            <th>USER</th>
                        </tr>
                    </thead>
                    <tbody>';

        // Group rows by product / raw material name
        $groupedData = [];
        foreach ($rows as $row) {
            $rowIsPurchase = $this->isPurchaseType($row['transaction_status']);
            $productName = $rowIsPurchase ? $row['raw_mat_name'] : $row['product_name'];
            $labelKey = $statusLabels[$row['transaction_status']] ?? 'local_code';
            $row['transactionStatus'] = $languageArray[$labelKey]['en'] ?? $row['transaction_status'];
            $groupedData[$productName][] = $row;
        }

        $grandTotalGross = 0;
        $grandTotalTare = 0;
        $grandTotalNet = 0;

        foreach ($groupedData as $product => $productRows) {
            $html .= '<tr>
                <td colspan="14" style="font-size: 9px;">. </td>
            </tr>
            <tr>
                <td colspan="14" style="font-size: 9px;">. </td>
            </tr>';

            $totalGross = 0;
            $totalTare = 0;
            $totalNet = 0;

            foreach ($productRows as $row) {
                $rowIsPurchase = $this->isPurchaseType($row['transaction_status']);
                $html .= '<tr style="font-size: 9px; text-align: center;">
                    <td>' . $row['transaction_id'] . '</td>
                    <td>' . (new DateTime($row['transaction_date']))->format('d/m/Y') . '</td>
                    <td>' . $row['transactionStatus'] . '</td>
                    <td>' . $row['lorry_plate_no1'] . '</td>
                    <td>' . ($isPurchase ? $row['supplier_code'] : $row['customer_code']) . '</td>
                    <td>' . ($isPurchase ? $row['supplier_name'] : $row['customer_name']) . '</td>
                    <td>' . ($rowIsPurchase ? $row['raw_mat_code'] : $row['product_code']) . '</td>
                    <td>' . ($rowIsPurchase ? $row['raw_mat_name'] : $row['product_name']) . '</td>
                    <td>' . $row['destination_code'] . '</td>
                    <td>' . $row['destination'] . '</td>
                    <td>' . $row['purchase_order'] . '</td>
                    <td>' . $row['delivery_no'] . '</td>
                    <td>' . $row['container_no'] . '</td>
                    <td>' . $row['seal_no'] . '</td>
                    <td>' . $this->formatOptionalMt($row['order_weight']) . '</td>
                    <td>' . $this->formatOptionalMt($row['supplier_weight']) . '</td>
                    <td>' . $this->formatMt($row['gross_weight1']) . '</td>
                    <td>' . $this->formatMt($row['tare_weight1']) . '</td>
                    <td>' . $this->formatMt($row['nett_weight1']) . '</td>
                    <td>' . $this->formatTime($row['gross_weight1_date']) . '</td>
                    <td>' . $this->formatTime($row['tare_weight1_date']) . '</td>
                    <td>' . $this->formatOptionalMt($row['gross_weight2']) . '</td>
                    <td>' . $this->formatOptionalMt($row['tare_weight2']) . '</td>
                    <td>' . $this->formatOptionalMt($row['nett_weight2']) . '</td>
                    <td>' . $this->formatTime($row['gross_weight2_date']) . '</td>
                    <td>' . $this->formatTime($row['tare_weight2_date']) . '</td>
                    <td>' . $this->formatOptionalMt($row['weight_different']) . '</td>
                    <td>' . $this->formatMt($row['final_weight']) . '</td>
                    <td>' . $row['created_by'] . '</td>
                </tr>';

                $totalGross += (float) $row['gross_weight1'];
                $totalTare  += (float) $row['tare_weight1'];
                $totalNet   += (float) $row['nett_weight1'];
            }

            $html .= '<tr>
                <th style="font-size: 10px;" colspan="18">Subtotal (' . $product . ')</th>
                <th style="border:1px solid black;font-size: 9px;">' . $this->formatMt($totalGross) . '</th>
                <th style="border:1px solid black;font-size: 9px;">' . $this->formatMt($totalTare) . '</th>
                <th style="border:1px solid black;font-size: 9px;">' . $this->formatMt($totalNet) . '</th>
            </tr>';

            $grandTotalGross += $totalGross;
            $grandTotalTare  += $totalTare;
            $grandTotalNet   += $totalNet;
        }

        $html .= '</tbody>
                    <tfoot>
                        <tr>
                            <th style="font-size: 10px;" colspan="18">Grand Total</th>
                            <th style="border:1px solid black;font-size: 9px;">' . $this->formatMt($grandTotalGross) . '</th>
                            <th style="border:1px solid black;font-size: 9px;">' . $this->formatMt($grandTotalTare) . '</th>
                            <th style="border:1px solid black;font-size: 9px;">' . $this->formatMt($grandTotalNet) . '</th>
                        </tr>
                    </tfoot>
                </table>
            </body>
        </html>';

        return $html;
    }
}
?>
