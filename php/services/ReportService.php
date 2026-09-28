<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../requires/lookup.php';

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

    // ─── Audit Log ────────────────────────────────────────────────────────────────

    /**
     * Audit Log data category => log table setup
     * from:    FROM clause (alias = log table alias when joined)
     * search:  [request key, column, '=' | 'like']
     * company: column linking the log to a company, 'json' = JSON id list, 'code' = company_code, null = not company based
     */
    private $auditLogConfig = [
        'Company' => [
            'from'    => 'Company_Log',
            'alias'   => '',
            'search'  => ['companyCode', 'company_code', '='],
            'company' => 'company_id',
            'columns' => ["Company Code", "Company Reg No", "New Reg No", "Company Name", "Address line 1", "Address line 2", "Address line 3", "Phone No", "Fax No", "Mobile No", "Email", "TIN No", "Action", "Action By", "Event Date"]
        ],
        'Customer' => [
            'from'    => 'Customer_Log',
            'alias'   => '',
            'search'  => ['customerCode', 'customer_code', '='],
            'company' => 'company',
            'columns' => ["Customer Code", "Company Reg No", "New Reg No", "Customer Name", "Address line 1", "Address line 2", "Address line 3", "Address line 4", "Phone No", "Fax No", "Contact Name", "IC No", "TIN No", "Email", "Is Manual", "Company", "Action", "Action By", "Event Date"]
        ],
        'Destination' => [
            'from'    => 'Destination_Log',
            'alias'   => '',
            'search'  => ['destinationCode', 'destination_code', '='],
            'company' => 'company',
            'columns' => ["Destination Code", "Destination Name", "Description", "Action", "Action By", "Event Date"]
        ],
        'Product' => [
            'from'    => 'Product_Log',
            'alias'   => '',
            'search'  => ['productCode', 'product_code', 'like'],
            'company' => 'company',
            'columns' => ["Product Code", "Product Name", "Description", "Category", "UOM", "Variance Type", "High", "Low", "Is Manual", "Company", "Action", "Action By", "Event Date"]
        ],
        'Raw Materials' => [
            'from'    => 'Raw_Mat_Log',
            'alias'   => '',
            'search'  => ['rawMatCode', 'raw_mat_code', 'like'],
            'company' => null,
            'columns' => ["Raw Material Code", "Raw Material Name", "Raw Material Price", "Description", "Variance Type", "High", "Low", "Type", "Action", "Action By", "Event Date"]
        ],
        'Supplier' => [
            'from'    => 'Supplier_Log sl LEFT JOIN Company c ON sl.company = c.id',
            'alias'   => 'sl',
            'select'  => 'sl.*, c.name AS company_name',
            'search'  => ['supplierCode', 'supplier_code', '='],
            'company' => 'company',
            'columns' => ["Supplier Code", "Company Reg No", "New Reg No", "Supplier Name", "Address line 1", "Address line 2", "Address line 3", "Address line 4", "Phone No", "Fax No", "Contact Name", "IC No", "TIN No", "Payment Term", "Payment Term Period", "Account No", "Is Manual", "Company", "Action", "Action By", "Event Date"]
        ],
        'Vehicle' => [
            'from'    => 'Vehicle_Log',
            'alias'   => '',
            'search'  => ['vehicleNo', 'veh_number', '='],
            'company' => 'company',
            'columns' => ["Vehicle No", "Vehicle Weight", "Transporter Code", "Transporter Name", "Customer Code", "Customer Name", "Supplier Code", "Supplier Name", "Is Manual", "Action", "Action By", "Event Date"]
        ],
        'Transporter' => [
            'from'    => 'Transporter_Log',
            'alias'   => '',
            'search'  => ['transporterCode', 'transporter_code', '='],
            'company' => null,
            'columns' => ["Transporter Code", "Company Reg No", "Transporter Name", "Address line 1", "Address line 2", "Address line 3", "Phone No", "Fax No", "Action", "Action By", "Event Date"]
        ],
        'Unit' => [
            'from'    => 'Units_Log',
            'alias'   => '',
            'search'  => ['unit', 'unit', '='],
            'company' => 'company',
            'columns' => ["Unit", "Action", "Action By", "Event Date"]
        ],
        'Product Category' => [
            'from'    => 'Product_Categories_Log pcl LEFT JOIN Company c ON pcl.company = c.id',
            'alias'   => 'pcl',
            'select'  => 'pcl.*, c.name AS company_name',
            'search'  => ['productCategory', 'category_name', 'like'],
            'company' => 'company',
            'columns' => ["Category Name", "Company", "Is Sales", "Is Purchase", "Is Local", "Is Port", "Is Misc", "Action", "Action By", "Event Date"]
        ],
        'Location' => [
            'from'    => 'Location_Log ll LEFT JOIN Plant p ON ll.plant_id = p.id',
            'alias'   => 'll',
            'select'  => 'll.*, p.name AS plant_name',
            'search'  => ['locationCode', 'location_code', '='],
            'company' => 'company',
            'columns' => ["Location Code", "Location Name", "Plant", "Weighing Count", "Action", "Action By", "Event Date"]
        ],
        'Project' => [
            'from'    => 'Project_Log pl LEFT JOIN Company c ON pl.company = c.id',
            'alias'   => 'pl',
            'select'  => 'pl.*, c.name AS company_name',
            'search'  => ['projectCode', 'project_code', 'like'],
            'company' => 'company',
            'columns' => ["Project Code", "Project Description", "Company", "Action", "Action By", "Event Date"]
        ],
        'User' => [
            'from'    => 'Users_Log',
            'alias'   => '',
            'search'  => ['userCode', 'username', 'like'],
            'company' => 'json',
            'columns' => ["Employee Code", "Username", "Name", "Email", "Role", "Plant", "Language", "Action", "Action By", "Event Date"]
        ],
        // Every login attempt (success and failed), written by login.php
        'Login' => [
            'from'    => 'Login_Log',
            'alias'   => '',
            'search'  => ['userCode', 'username', 'like'],
            'company' => 'json',
            'columns' => ["Username", "Employee Code", "Role", "Login Status", "Remarks", "IP Address", "User Agent", "Event Date"]
        ],
        'Plant' => [
            'from'    => 'Plant_Log',
            'alias'   => '',
            'search'  => ['plantCode', 'plant_code', 'like'],
            'company' => null,
            'columns' => ["Plant Code", "Plant Name", "Address line 1", "Address line 2", "Address line 3", "Phone No", "Fax No", "Action", "Action By", "Event Date"]
        ],
        'Weight' => [
            'from'    => 'Weight_Log',
            'alias'   => '',
            'search'  => ['weight', 'transaction_id', 'like'],
            'company' => 'company_id',
            'columns' => ["Transaction Id", "Weight Status", "Customer/Supplier", "Vehicle", "Product/Raw Material", "SO/PO", "DO", "Gross Incoming", "Incoming Date", "Tare Outgoing", "Outgoing Date", "Nett Weight", "Action", "Action By", "Event Date"]
        ],
        'Empty Container' => [
            'from'    => 'Weight_Container_Log',
            'alias'   => '',
            'search'  => ['emptyContainer', 'transaction_id', 'like'],
            'company' => 'company_id',
            'columns' => ["Transaction Id", "Weight Status", "Customer/Supplier", "Vehicle", "Container No", "Product/Raw Material", "Gross Incoming", "Incoming Date", "Tare Outgoing", "Outgoing Date", "Nett Weight", "Action", "Action By", "Event Date"]
        ],
        'SO' => [
            'from'    => 'Sales_Order_Log',
            'alias'   => '',
            'search'  => ['custPoNo', 'order_no', 'like'],
            'company' => 'code',
            'columns' => ["Company Code", "Company Name", "Customer Code", "Customer Name", "Site Code", "Site Name", "Sales Representative Code", "Sales Representative Name", "Destination Code", "Destination Name", "Product Code", "Product Name", "Plant Code", "Plant Name", "Transporter Code", "Transporter Name", "Vehicle No", "EXQ/Del", "Customer P/O No", "S/O No", "Order Date", "Order Quantity", "Balance", "Remarks", "Action", "Action By", "Event Date"]
        ],
        'PO' => [
            'from'    => 'Purchase_Order_Log',
            'alias'   => '',
            'search'  => ['poNo', 'po_no', 'like'],
            'company' => 'code',
            'columns' => ["Company Code", "Company Name", "Supplier Code", "Supplier Name", "Site Code", "Site Name", "Sales Representative Code", "Sales Representative Name", "Destination Code", "Destination Name", "Raw Material Code", "Raw Material Name", "Plant Code", "Plant Name", "Transporter Code", "Transporter Name", "Vehicle No", "EXQ/Del", "P/O No", "Order Date", "Order Quantity", "Balance", "Remarks", "Action", "Action By", "Event Date"]
        ],
        // One row per header save, line totals come from the detail logs written in the same save
        'Sawn Timber' => [
            'from'    => 'Sawn_Timber_Header_Log h
                          LEFT JOIN Company c ON h.company_id = c.id
                          LEFT JOIN Plant p ON h.plant_id = p.id
                          LEFT JOIN (SELECT header_log_id, COUNT(*) AS line_count, SUM(pieces) AS total_pieces, SUM(tons) AS total_tons
                                     FROM Sawn_Timber_Detail_Log WHERE action_id <> 3 GROUP BY header_log_id) d ON d.header_log_id = h.id',
            'alias'   => 'h',
            'select'  => 'h.*, c.name AS company_name, p.name AS plant_name, d.line_count, d.total_pieces, d.total_tons',
            'search'  => ['sawnTimber', 'transaction_id', 'like'],
            'company' => 'company_id',
            'columns' => ["Transaction Id", "Company", "Plant", "Record Date", "Remarks", "Status", "Lines", "Total Pieces", "Total Tons", "Action", "Action By", "Event Date"]
        ],
        // One row per Manage Prices save (Product_Log rows flagged is_price_save), entries come from Product_Price_Log
        'Item Price' => [
            'from'    => 'Product_Log pl
                          LEFT JOIN Company c ON pl.company = c.id
                          LEFT JOIN (SELECT product_log_id, COUNT(*) AS entry_count
                                     FROM Product_Price_Log WHERE action_id <> 3 GROUP BY product_log_id) e ON e.product_log_id = pl.id',
            'alias'   => 'pl',
            'select'  => 'pl.*, c.name AS company_name, e.entry_count',
            'where'   => "pl.is_price_save = 'Y'",
            'search'  => ['itemPriceCode', 'product_code', 'like'],
            'company' => 'company',
            'columns' => ["Item Code", "Item Name", "Company", "Purchase Price", "Selling Price", "Price Entries", "Action", "Action By", "Event Date"]
        ],
    ];


    public function filterAuditLog($post) {
        $type   = $post['selectedValue'] ?? '';
        $config = $this->auditLogConfig[$type] ?? null;
        if ($config === null) {
            return ['columnNames' => [], 'dataTable' => []];
        }

        $prefix = $config['alias'] !== '' ? $config['alias'] . '.' : '';
        $f = ['sql' => '', 'types' => '', 'values' => []];

        $from = $this->toDbDate($post['fromDateSearch'] ?? '', '00:00:00');
        if ($from !== null) {
            $this->add($f, " AND {$prefix}event_date >= ?", 's', [$from]);
        }
        $to = $this->toDbDate($post['toDateSearch'] ?? '', '23:59:59');
        if ($to !== null) {
            $this->add($f, " AND {$prefix}event_date <= ?", 's', [$to]);
        }

        [$searchKey, $searchColumn, $searchOperator] = $config['search'];
        $search = $this->value($post, $searchKey);
        if ($search !== null) {
            if ($searchOperator === 'like') {
                $this->add($f, " AND {$prefix}{$searchColumn} LIKE ?", 's', ["%{$search}%"]);
            } else {
                $this->add($f, " AND {$prefix}{$searchColumn} = ?", 's', [$search]);
            }
        }

        $companyId = $this->getAuditLogCompanyId($post['company'] ?? null);
        if ($companyId > 0 && $config['company'] !== null) {
            if ($config['company'] === 'json') {
                $this->add($f, " AND {$prefix}company_id LIKE ?", 's', ['%"' . $companyId . '"%']);
            } elseif ($config['company'] === 'code') {
                $this->add($f, " AND {$prefix}company_code = (SELECT company_code FROM Company WHERE id = ?)", 'i', [$companyId]);
            } else {
                $this->add($f, " AND {$prefix}{$config['company']} = ?", 'i', [$companyId]);
            }
        }

        $select = $config['select'] ?? '*';
        $where  = isset($config['where']) ? " AND {$config['where']}" : '';
        $rows = $this->fetchAll("SELECT {$select} FROM {$config['from']} WHERE 1=1{$where}" . $f['sql'], $f['types'], $f['values']);

        $data = [];
        foreach ($rows as $row) {
            $data[] = $this->mapAuditLogRow($type, $row);
        }

        return ['columnNames' => $config['columns'], 'dataTable' => $data];
    }

    // Determine company on the backend - never trust frontend value for restricted users
    private function getAuditLogCompanyId($requestedCompany) {
        if (hasModulePermission('Reports', 'Audit Log', ['view_all_companies'])) {
            return ($requestedCompany !== null && $requestedCompany !== '' && $requestedCompany !== '-') ? intval($requestedCompany) : 0;
        }
        return intval($_SESSION['company_id'] ?? 0);
    }

    private function mapAuditLogRow($type, $row) {
        $db = $this->db;
        // Only Purchase / Local use supplier + raw material; Sales, Port and Misc use customer + product
        $isSales = !$this->isPurchaseType($row['transaction_status'] ?? '');
        $audit = [
            "Action"    => searchActionNameById($row['action_id'], $db),
            "Action By" => $row['action_by'] ?? '',
            "Event Date"=> $row['event_date'] ?? '',
        ];

        switch ($type) {
            case 'Company':
                $mapped = [
                    "Company Code"   => $row['company_code'] ?? '',
                    "Company Reg No" => $row['company_reg_no'] ?? '',
                    "New Reg No"     => $row['new_reg_no'] ?? '',
                    "Company Name"   => $row['name'] ?? '',
                    "Address line 1" => $row['address_line_1'] ?? '',
                    "Address line 2" => $row['address_line_2'] ?? '',
                    "Address line 3" => $row['address_line_3'] ?? '',
                    "Phone No"       => $row['phone_no'] ?? '',
                    "Fax No"         => $row['fax_no'] ?? '',
                    "Mobile No"      => $row['mobile_no'] ?? '',
                    "Email"          => $row['email'] ?? '',
                    "TIN No"         => $row['tin_no'] ?? '',
                ];
                break;
            case 'Customer':
                $mapped = [
                    "Customer Code"  => $row['customer_code'] ?? '',
                    "Company Reg No" => $row['company_reg_no'] ?? '',
                    "New Reg No"     => $row['new_reg_no'] ?? '',
                    "Customer Name"  => $row['name'] ?? '',
                    "Address line 1" => $row['address_line_1'] ?? '',
                    "Address line 2" => $row['address_line_2'] ?? '',
                    "Address line 3" => $row['address_line_3'] ?? '',
                    "Address line 4" => $row['address_line_4'] ?? '',
                    "Phone No"       => $row['phone_no'] ?? '',
                    "Fax No"         => $row['fax_no'] ?? '',
                    "Contact Name"   => $row['contact_name'] ?? '',
                    "IC No"          => $row['ic_no'] ?? '',
                    "TIN No"         => $row['tin_no'] ?? '',
                    "Email"          => $row['email'] ?? '',
                    "Is Manual"      => $row['is_manual'] ?? '',
                    "Company"        => $row['company'] ? searchCompanyById($row['company'], $db)['name'] ?? '' : '',
                ];
                break;
            case 'Destination':
                $mapped = [
                    "Destination Code" => $row['destination_code'] ?? '',
                    "Destination Name" => $row['name'] ?? '',
                    "Description"      => $row['description'] ?? '',
                ];
                break;
            case 'Product':
                $mapped = [
                    "Product Code"  => $row['product_code'] ?? '',
                    "Product Name"  => $row['name'] ?? '',
                    "Description"   => $row['description'] ?? '',
                    "Category"      => $row['category'] ? searchItemCategoryById($row['category'], $db)['category_name'] ?? '' : '',
                    "UOM"           => $row['uom'] ? searchUnitById($row['uom'], $db)['unit'] ?? '' : '',
                    "Variance Type" => $row['variance'] ?? '',
                    "High"          => $row['high'] ?? '',
                    "Low"           => $row['low'] ?? '',
                    "Is Manual"     => $row['is_manual'] ?? '',
                    "Company"       => $row['company'] ? searchCompanyById($row['company'], $db)['name'] ?? '' : '',
                ];
                break;
            case 'Raw Materials':
                $mapped = [
                    "Raw Material Code"  => $row['raw_mat_code'] ?? '',
                    "Raw Material Name"  => $row['name'] ?? '',
                    "Raw Material Price" => $row['price'] ?? '',
                    "Description"        => $row['description'] ?? '',
                    "Variance Type"      => $row['variance'] ?? '',
                    "High"               => $row['high'] ?? '',
                    "Low"                => $row['low'] ?? '',
                    "Type"               => $row['type'] ?? '',
                ];
                break;
            case 'Supplier':
                $mapped = [
                    "Supplier Code"       => $row['supplier_code'] ?? '',
                    "Company Reg No"      => $row['company_reg_no'] ?? '',
                    "New Reg No"          => $row['new_reg_no'] ?? '',
                    "Supplier Name"       => $row['name'] ?? '',
                    "Address line 1"      => $row['address_line_1'] ?? '',
                    "Address line 2"      => $row['address_line_2'] ?? '',
                    "Address line 3"      => $row['address_line_3'] ?? '',
                    "Address line 4"      => $row['address_line_4'] ?? '',
                    "Phone No"            => $row['phone_no'] ?? '',
                    "Fax No"              => $row['fax_no'] ?? '',
                    "Contact Name"        => $row['contact_name'] ?? '',
                    "IC No"               => $row['ic_no'] ?? '',
                    "TIN No"              => $row['tin_no'] ?? '',
                    "Payment Term"        => $row['payment_term'] ?? '',
                    "Payment Term Period" => $row['payment_term_period'] ?? '',
                    "Account No"          => $row['account_no'] ?? '',
                    "Is Manual"           => $row['is_manual'] ?? '',
                    "Company"             => $row['company'] ? searchCompanyById($row['company'], $db)['name'] ?? '' : '',
                ];
                break;
            case 'Vehicle':
                $mapped = [
                    "Vehicle No"       => $row['veh_number'] ?? '',
                    "Vehicle Weight"   => $row['vehicle_weight'] ?? '',
                    "Transporter Code" => $row['transporter_code'] ?? '',
                    "Transporter Name" => $row['transporter_name'] ?? '',
                    "Customer Code"    => $row['customer_code'] ?? '',
                    "Customer Name"    => $row['customer_name'] ?? '',
                    "Supplier Code"    => $row['supplier_code'] ?? '',
                    "Supplier Name"    => $row['supplier_name'] ?? '',
                    "Is Manual"        => $row['is_manual'] ?? '',
                ];
                break;
            case 'Transporter':
                $mapped = [
                    "Transporter Code" => $row['transporter_code'] ?? '',
                    "Company Reg No"   => $row['company_reg_no'] ?? '',
                    "Transporter Name" => $row['name'] ?? '',
                    "Address line 1"   => $row['address_line_1'] ?? '',
                    "Address line 2"   => $row['address_line_2'] ?? '',
                    "Address line 3"   => $row['address_line_3'] ?? '',
                    "Phone No"         => $row['phone_no'] ?? '',
                    "Fax No"           => $row['fax_no'] ?? '',
                ];
                break;
            case 'Unit':
                $mapped = [
                    "Unit" => $row['unit'] ?? '',
                ];
                break;
            case 'Product Category':
                $mapped = [
                    "Category Name" => $row['category_name'] ?? '',
                    "Company"       => $row['company_name'] ?? '',
                    "Is Sales"      => $row['is_sales'] ?? '',
                    "Is Purchase"   => $row['is_purchase'] ?? '',
                    "Is Local"      => $row['is_local'] ?? '',
                    "Is Port"       => $row['is_port'] ?? '',
                    "Is Misc"       => $row['is_misc'] ?? '',
                ];
                break;
            case 'Location':
                $mapped = [
                    "Location Code"  => $row['location_code'] ?? '',
                    "Location Name"  => $row['location_name'] ?? '',
                    "Plant"          => $row['plant_name'] ?? '',
                    "Weighing Count" => $row['weighing_count'] ?? '',
                ];
                break;
            case 'Project':
                $mapped = [
                    "Project Code"        => $row['project_code'] ?? '',
                    "Project Description" => $row['project_description'] ?? '',
                    "Company"             => $row['company_name'] ?? '',
                ];
                break;
            case 'User':
                $plantNames = '';
                if (!empty($row['plant_id'])) {
                    $plantIds = json_decode($row['plant_id'], true);
                    if (is_array($plantIds)) {
                        $plantNameArr = [];
                        foreach ($plantIds as $pid) {
                            $plantName = searchPlantNameById($pid, $db);
                            if ($plantName) {
                                $plantNameArr[] = $plantName;
                            }
                        }
                        $plantNames = implode(', ', $plantNameArr);
                    }
                }
                $mapped = [
                    "Employee Code" => $row['employee_code'] ?? '',
                    "Username"      => $row['username'] ?? '',
                    "Name"          => $row['name'] ?? '',
                    "Email"         => $row['useremail'] ?? '',
                    "Role"          => $row['role'] ?? '',
                    "Plant"         => $plantNames,
                    "Language"      => $row['languages'] ?? '',
                ];
                break;
            case 'Login':
                // Failed attempts hold raw input from unauthenticated visitors - escape before DataTables renders it as HTML
                $mapped = array_map(fn($v) => htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'), [
                    "Username"      => $row['username'],
                    "Employee Code" => $row['employee_code'],
                    "Role"          => $row['role'],
                    "Login Status"  => $row['login_status'],
                    "Remarks"       => $row['remarks'],
                    "IP Address"    => $row['ip_address'],
                    "User Agent"    => $row['user_agent'],
                ]);
                break;
            case 'Plant':
                $mapped = [
                    "Plant Code"     => $row['plant_code'] ?? '',
                    "Plant Name"     => $row['name'] ?? '',
                    "Address line 1" => $row['address_line_1'] ?? '',
                    "Address line 2" => $row['address_line_2'] ?? '',
                    "Address line 3" => $row['address_line_3'] ?? '',
                    "Phone No"       => $row['phone_no'] ?? '',
                    "Fax No"         => $row['fax_no'] ?? '',
                ];
                break;
            case 'Weight':
                $mapped = [
                    "Transaction Id"       => $row['transaction_id'] ?? '',
                    "Weight Status"        => $row['weight_type'] ?? '',
                    "Customer/Supplier"    => ($isSales ? $row['customer_name'] : $row['supplier_name']) ?? '',
                    "Vehicle"              => $row['lorry_plate_no1'] ?? '',
                    "Product/Raw Material" => ($isSales ? $row['product_name'] : $row['raw_mat_name']) ?? '',
                    "SO/PO"                => $row['purchase_order'] ?? '',
                    "DO"                   => $row['delivery_no'] ?? '',
                    "Gross Incoming"       => $row['gross_weight1'] ?? '',
                    "Incoming Date"        => $row['gross_weight1_date'] ?? '',
                    "Tare Outgoing"        => $row['tare_weight1'] ?? '',
                    "Outgoing Date"        => $row['tare_weight1_date'] ?? '',
                    "Nett Weight"          => $row['nett_weight1'] ?? '',
                ];
                break;
            case 'Empty Container':
                $mapped = [
                    "Transaction Id"       => $row['transaction_id'] ?? '',
                    "Weight Status"        => $row['weight_type'] ?? '',
                    "Customer/Supplier"    => ($isSales ? $row['customer_name'] : $row['supplier_name']) ?? '',
                    "Vehicle"              => $row['lorry_plate_no1'] ?? '',
                    "Container No"         => $row['container_no'] ?? '',
                    "Product/Raw Material" => ($isSales ? $row['product_name'] : $row['raw_mat_name']) ?? '',
                    "Gross Incoming"       => $row['gross_weight1'] ?? '',
                    "Incoming Date"        => $row['gross_weight1_date'] ?? '',
                    "Tare Outgoing"        => $row['tare_weight1'] ?? '',
                    "Outgoing Date"        => $row['tare_weight1_date'] ?? '',
                    "Nett Weight"          => $row['nett_weight1'] ?? '',
                ];
                break;
            case 'SO':
            case 'PO':
                $isSo = $type === 'SO';
                $mapped = [
                    "Company Code"                => $row['company_code'] ?? '',
                    "Company Name"                => $row['company_name'] ?? '',
                ];
                if ($isSo) {
                    $mapped["Customer Code"] = $row['customer_code'] ?? '';
                    $mapped["Customer Name"] = $row['customer_name'] ?? '';
                } else {
                    $mapped["Supplier Code"] = $row['supplier_code'] ?? '';
                    $mapped["Supplier Name"] = $row['supplier_name'] ?? '';
                }
                $mapped += [
                    "Site Code"                   => $row['site_code'] ?? '',
                    "Site Name"                   => $row['site_name'] ?? '',
                    "Sales Representative Code"   => $row['agent_code'] ?? '',
                    "Sales Representative Name"   => $row['agent_name'] ?? '',
                    "Destination Code"            => $row['destination_code'] ?? '',
                    "Destination Name"            => $row['destination_name'] ?? '',
                ];
                if ($isSo) {
                    $mapped["Product Code"] = $row['product_code'] ?? '';
                    $mapped["Product Name"] = $row['product_name'] ?? '';
                } else {
                    $mapped["Raw Material Code"] = $row['raw_mat_code'] ?? '';
                    $mapped["Raw Material Name"] = $row['raw_mat_name'] ?? '';
                }
                $mapped += [
                    "Plant Code"                  => $row['plant_code'] ?? '',
                    "Plant Name"                  => $row['plant_name'] ?? '',
                    "Transporter Code"            => $row['transporter_code'] ?? '',
                    "Transporter Name"            => $row['transporter_name'] ?? '',
                    "Vehicle No"                  => $row['veh_number'] ?? '',
                    "EXQ/Del"                     => $row['exquarry_or_delivered'] ?? '',
                ];
                if ($isSo) {
                    $mapped["Customer P/O No"] = $row['order_no'] ?? '';
                    $mapped["S/O No"]          = $row['so_no'] ?? '';
                } else {
                    $mapped["P/O No"] = $row['po_no'] ?? '';
                }
                $mapped += [
                    "Order Date"                  => $row['order_date'] ?? '',
                    "Order Quantity"              => $row['order_quantity'] ?? '',
                    "Balance"                     => $row['balance'] ?? '',
                    "Remarks"                     => $row['remarks'] ?? '',
                ];
                break;
            case 'Sawn Timber':
                $mapped = [
                    "Transaction Id" => $row['transaction_id'] ?? '',
                    "Company"        => $row['company_name'] ?? '',
                    "Plant"          => $row['plant_name'] ?? '',
                    "Record Date"    => $row['record_date'] ?? '',
                    "Remarks"        => $row['remarks'] ?? '',
                    "Status"         => ($row['status'] ?? '') == '1' ? 'Deleted' : 'Active',
                    "Lines"          => $row['line_count'] ?? '',
                    "Total Pieces"   => $row['total_pieces'] ?? '',
                    "Total Tons"     => $row['total_tons'] ?? '',
                ];
                break;
            case 'Item Price':
                $mapped = [
                    "Item Code"      => $row['product_code'] ?? '',
                    "Item Name"      => $row['name'] ?? '',
                    "Company"        => $row['company_name'] ?? '',
                    "Purchase Price" => $row['purchase_price'] ?? '',
                    "Selling Price"  => $row['selling_price'] ?? '',
                    "Price Entries"  => $row['entry_count'] ?? 0,
                ];
                break;
            default:
                $mapped = [];
        }

        return array_merge(['id' => $row['id']], $mapped, $audit);
    }

    /**
     * Price entries of one Manage Prices save (Product_Log id).
     * Every save soft deletes and re-inserts all entries, so entries are compared by content (party, dates, type, tiers):
     * a saved entry with no identical previous entry is new/changed, a previous entry with no identical saved entry is removed.
     */
    public function getItemPriceLogDetails($productLogId) {
        $headers = $this->fetchAll("SELECT id, company FROM Product_Log WHERE id = ? AND is_price_save = 'Y'", 'i', [$productLogId]);
        if (empty($headers)) {
            return null;
        }

        $companyId = $this->getAuditLogCompanyId(null);
        if ($companyId > 0 && intval($headers[0]['company']) !== $companyId) {
            return null;
        }

        $priceRows = $this->fetchAll(
            "SELECT price_id, party_type, party_id, date_from, date_to, price_type, action_id
             FROM Product_Price_Log WHERE product_log_id = ? ORDER BY party_type, date_from, id",
            'i', [$productLogId]
        );
        $tierRows = $this->fetchAll(
            "SELECT price_id, qty_from, qty_to, unit_price, discount, discount_type, action_id
             FROM Product_Price_Tier_Log WHERE product_log_id = ? ORDER BY qty_from, id",
            'i', [$productLogId]
        );

        // Tiers per entry, kept apart for the removed (action 3) and saved versions of the entry
        $tiersByPrice = [];
        foreach ($tierRows as $tier) {
            $key = $tier['price_id'] . '_' . (intval($tier['action_id']) === 3 ? 'removed' : 'saved');
            unset($tier['price_id'], $tier['action_id']);
            $tiersByPrice[$key][] = array_map(fn($v) => $v ?? '', $tier);
        }

        $partyNames = $this->getPartyNames($priceRows);

        $entries = [];
        $removed = [];
        foreach ($priceRows as $row) {
            $isRemoved = intval($row['action_id']) === 3;
            $entry = [
                'party_type' => $row['party_type'],
                'party_name' => $partyNames[$row['party_type']][$row['party_id']] ?? '',
                'date_from'  => date('d-m-Y', strtotime($row['date_from'])),
                'date_to'    => date('d-m-Y', strtotime($row['date_to'])),
                'price_type' => $row['price_type'],
                'tiers'      => $tiersByPrice[$row['price_id'] . '_' . ($isRemoved ? 'removed' : 'saved')] ?? [],
            ];
            if ($isRemoved) {
                $removed[] = $entry;
            } else {
                $entries[] = $entry;
            }
        }

        // Only a save with previous entries has something to compare with
        $compare = !empty($removed);
        $unmatched = $removed;
        foreach ($entries as &$entry) {
            $index = $compare ? array_search($entry, $unmatched) : false;
            if ($index !== false) {
                unset($unmatched[$index]);
            }
            $entry['is_changed'] = $compare && $index === false;
        }
        unset($entry);

        return ['entries' => $entries, 'removed' => array_values($unmatched)];
    }

    // Customer/Supplier names of the price log rows: [party_type => [id => "code - name"]]
    private function getPartyNames($priceRows) {
        $names = ['Customer' => [], 'Supplier' => []];
        foreach (['Customer' => 'customer_code', 'Supplier' => 'supplier_code'] as $table => $codeColumn) {
            $ids = array_values(array_unique(array_map('intval', array_column(
                array_filter($priceRows, fn($r) => $r['party_type'] === $table), 'party_id'
            ))));
            if (empty($ids)) {
                continue;
            }
            $rows = $this->fetchAll(
                "SELECT id, {$codeColumn} AS code, name FROM {$table} WHERE id IN (" . implode(',', array_fill(0, count($ids), '?')) . ")",
                str_repeat('i', count($ids)), $ids
            );
            foreach ($rows as $row) {
                $names[$table][$row['id']] = $row['code'] . ' - ' . $row['name'];
            }
        }
        return $names;
    }

    /**
     * Detail lines of one Sawn Timber header log (one save).
     * Every save deletes and re-inserts all lines, so lines are compared by content:
     * an inserted line with no identical deleted line is new/changed, a deleted line with no identical inserted line is removed.
     */
    public function getSawnTimberLogDetails($headerLogId) {
        $headers = $this->fetchAll("SELECT id, company_id FROM Sawn_Timber_Header_Log WHERE id = ?", 'i', [$headerLogId]);
        if (empty($headers)) {
            return null;
        }

        $companyId = $this->getAuditLogCompanyId(null);
        if ($companyId > 0 && intval($headers[0]['company_id']) !== $companyId) {
            return null;
        }

        $rows = $this->fetchAll(
            "SELECT species, lot, bundle, thick, width, length, pieces, tons, kd_charges, bundling_charges, grader_fees, action_id
             FROM Sawn_Timber_Detail_Log WHERE header_log_id = ? ORDER BY id ASC",
            'i', [$headerLogId]
        );

        $lines = [];
        $deleted = [];
        foreach ($rows as $row) {
            $actionId = intval($row['action_id']);
            unset($row['action_id']);
            $row = array_map(fn($v) => $v ?? '', $row);
            if ($actionId === 3) {
                $deleted[] = $row;
            } else {
                $lines[] = $row;
            }
        }

        // Only an update has a previous set of lines to compare with
        $compare = !empty($deleted);
        $unmatched = $deleted;
        foreach ($lines as &$line) {
            $index = $compare ? array_search($line, $unmatched) : false;
            if ($index !== false) {
                unset($unmatched[$index]);
            }
            $line['is_changed'] = $compare && $index === false;
        }
        unset($line);

        return ['lines' => $lines, 'removed' => array_values($unmatched)];
    }
}
?>
