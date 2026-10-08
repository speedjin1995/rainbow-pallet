<?php
require_once __DIR__ . '/BaseService.php';

/**
 * Columns shown in the Weighing page and report tables per company, set up from the Companies page.
 * A table without a setup gets the preset columns in DEFAULTS.
 * The checkbox and action columns are always shown and are not part of the setup.
 *
 * Company.column_setup holds JSON of table => visible column keys in display order,
 * e.g. {"weight": ["transaction_id", "customer"], "sales_report": [...]}. NULL = all tables use the preset.
 */
class TableColumnService extends BaseService {

    protected $table = 'Company';

    // Row field => [message key, header suffix]. Every key must be a field of the filterWeight / filterEmptyContainer
    // rows, and of the ReportService::filter rows unless excluded for the reports.
    const COLUMNS = [
        'company_name'       => ['company_code', ''],
        'transaction_id'     => ['transaction_id_code', ''],
        'transaction_date'   => ['transaction_date_code', ''],
        'weight_type'        => ['weight_type_code', ''],
        'transaction_status' => ['weight_status_code', ''],
        'customer'           => ['customer_supplier_code', ''],
        'product_name'       => ['items_code', ''],
        'plant_name'         => ['plant_name_code', ''],
        'container_no'       => ['container_no_code', ''],
        'seal_no'            => ['seal_no_code', ''],
        'container_no2'      => ['container_no_code', ' 2'],
        'seal_no2'           => ['seal_no_code', ' 2'],
        'delivery_no'        => ['do_no_code', ''],
        'purchase_order'     => ['po_no_code', ''],
        'invoice_no'         => ['invoice_no_code', ''],
        'transporter'        => ['transporter_code', ''],
        'destination'        => ['destination_code', ''],
        'lorry_plate_no1'    => ['vehicle_code', ''],
        'gross_weight1'      => ['gross_incoming_code', ''],
        'gross_weight1_date' => ['incoming_date_code', ''],
        'tare_weight1'       => ['tare_outgoing_code', ''],
        'tare_weight1_date'  => ['outgoing_date_code', ''],
        'nett_weight1'       => ['nett_weight_code', ''],
        'lorry_plate_no2'    => ['vehicle_code', ' 2'],
        'gross_weight2'      => ['gross_incoming_code', ' 2'],
        'gross_weight2_date' => ['incoming_date_code', ' 2'],
        'tare_weight2'       => ['tare_outgoing_code', ' 2'],
        'tare_weight2_date'  => ['outgoing_date_code', ' 2'],
        'nett_weight2'       => ['nett_weight_code', ' 2'],
        'final_weight'       => ['final_weight_code', ''],
        'remarks'            => ['remarks_code', ''],
        'created_by'         => ['created_by_code', ''],
    ];

    // Computed row fields sorted by a real column (the Pending UNION can only sort by selected columns)
    const SORT_COLUMNS = [
        'company_name' => 'company_id',
        'customer'     => 'customer_name',
    ];

    // Preset columns, same as the fixed headers the tables had before the setup
    const DEFAULTS = [
        'weight' => [
            'company_name', 'transaction_id', 'weight_type', 'transaction_status', 'customer', 'container_no', 'seal_no',
            'lorry_plate_no1', 'gross_weight1', 'gross_weight1_date', 'tare_weight1', 'tare_weight1_date', 'nett_weight1',
            'lorry_plate_no2', 'gross_weight2', 'gross_weight2_date', 'tare_weight2', 'tare_weight2_date', 'nett_weight2',
        ],
        'empty_container' => [
            'company_name', 'container_no', 'seal_no', 'transaction_status',
            'lorry_plate_no1', 'gross_weight1', 'gross_weight1_date', 'tare_weight1', 'tare_weight1_date', 'nett_weight1',
        ],
        'sales_report'    => self::REPORT_DEFAULT,
        'purchase_report' => self::REPORT_DEFAULT,
        'port_report'     => self::REPORT_DEFAULT,
        'misc_report'     => self::REPORT_DEFAULT,
        'production_report' => self::REPORT_DEFAULT,
    ];

    // Report pages all had the same fixed headers
    const REPORT_DEFAULT = [
        'transaction_id', 'weight_type', 'transaction_status', 'customer', 'container_no', 'seal_no',
        'lorry_plate_no1', 'gross_weight1', 'gross_weight1_date', 'tare_weight1', 'tare_weight1_date', 'nett_weight1',
        'lorry_plate_no2', 'gross_weight2', 'gross_weight2_date', 'tare_weight2', 'tare_weight2_date', 'nett_weight2',
    ];

    // Empty container rows keep the raw weight type, so it is not offered there.
    // Reports show one company at a time and their rows have no company name.
    const EXCLUDED = [
        'weight'          => [],
        'empty_container' => ['weight_type'],
        'sales_report'    => ['company_name'],
        'purchase_report' => ['company_name'],
        'port_report'     => ['company_name'],
        'misc_report'     => ['company_name'],
        'production_report' => ['company_name'],
    ];

    // Report table => Reports permission module (for the company scope, same as ReportService::buildScope)
    const REPORT_MODULES = [
        'sales_report'    => 'Sales',
        'purchase_report' => 'Purchase',
        'port_report'     => 'Port',
        'misc_report'     => 'Miscellaneous',
        'production_report' => 'Production',
    ];

    /**
     * Every available column of a table: the company's visible ones first in display order,
     * then the hidden ones in catalog order: [{key, label, visible}].
     * The Weighing page shows the visible ones and lists the rest in its Columns dropdown.
     */
    public function getColumns($companyId, $tableName) {
        $this->checkTable($tableName);

        $visible = $this->getKeys($companyId, $tableName);
        $hidden = array_diff($this->availableKeys($tableName), $visible);

        $rows = [];
        foreach ($visible as $key) {
            $rows[] = ['key' => $key, 'label' => $this->label($key), 'visible' => true];
        }
        foreach ($hidden as $key) {
            $rows[] = ['key' => $key, 'label' => $this->label($key), 'visible' => false];
        }
        return $rows;
    }

    /**
     * Same columns as getColumns() for the Companies page setup. Null when the user can't access the company.
     */
    public function getSetup($companyId, $tableName) {
        if (!$this->canAccessCompany($companyId)) {
            return null;
        }
        return $this->getColumns($companyId, $tableName);
    }

    /**
     * Save the visible columns of a table in display order. Saving the preset removes the setup,
     * so the company keeps following DEFAULTS.
     */
    public function save($companyId, $tableName, $keys) {
        if (!$this->canAccessCompany($companyId)) {
            throw new InvalidArgumentException('Record not found');
        }
        $this->checkTable($tableName);
        if (!is_array($keys) || empty($keys)) {
            throw new InvalidArgumentException('Please select at least one column');
        }

        $available = $this->availableKeys($tableName);
        $keys = array_values(array_unique(array_map('strval', $keys)));
        foreach ($keys as $key) {
            if (!in_array($key, $available, true)) {
                throw new InvalidArgumentException('Invalid column');
            }
        }

        // Other tables' setups live in the same JSON, so lock the row while it is rewritten
        $this->db->begin_transaction();
        try {
            $setup = $this->findSetup($companyId, true);
            if ($keys === self::DEFAULTS[$tableName]) {
                unset($setup[$tableName]);
            } else {
                $setup[$tableName] = $keys;
            }

            $json = empty($setup) ? null : json_encode($setup);
            $this->execute("UPDATE {$this->table} SET column_setup = ?, modified_by = ? WHERE id = ?", 'ssi', [$json, $this->username, intval($companyId)]);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * SQL column to sort a table by, from the DataTables column data. Anything outside the catalog sorts by transaction date.
     */
    public static function sortColumn($tableName, $key) {
        if ($key === 'id') {
            return 'id';
        }
        $excluded = self::EXCLUDED[$tableName] ?? [];
        if (!isset(self::COLUMNS[$key]) || in_array($key, $excluded, true)) {
            return 'transaction_date';
        }
        return self::SORT_COLUMNS[$key] ?? $key;
    }

    // Saved keys still in the catalog, or the preset when the company has no setup
    private function getKeys($companyId, $tableName) {
        $keys = $this->findSetup($companyId, false)[$tableName] ?? null;
        if (is_array($keys)) {
            $keys = array_values(array_intersect($keys, $this->availableKeys($tableName)));
        }
        return !empty($keys) ? $keys : self::DEFAULTS[$tableName];
    }

    private function availableKeys($tableName) {
        return array_values(array_diff(array_keys(self::COLUMNS), self::EXCLUDED[$tableName]));
    }

    private function label($key) {
        $language = $_SESSION['language'] ?? 'en';
        $languageArray = $_SESSION['languageArray'] ?? [];
        list($code, $suffix) = self::COLUMNS[$key];
        return ($languageArray[$code][$language] ?? $key) . $suffix;
    }

    private function checkTable($tableName) {
        if (!isset(self::DEFAULTS[$tableName])) {
            throw new InvalidArgumentException('Invalid table');
        }
    }

    // Decoded column_setup of the company, [] when not set up
    private function findSetup($companyId, $lock) {
        $stmt = $this->db->prepare("SELECT column_setup FROM {$this->table} WHERE id = ?" . ($lock ? " FOR UPDATE" : ""));
        if (!$stmt) throw new Exception($this->db->error);
        $companyId = intval($companyId);
        $stmt->bind_param('i', $companyId);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception($error);
        }
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $setup = $row ? json_decode($row['column_setup'] ?? '', true) : null;
        return is_array($setup) ? $setup : [];
    }

    // Same rule as DocumentNumberService: without view_all_companies on Companies, only the user's own companies can be set up
    private function canAccessCompany($companyId) {
        $companyId = intval($companyId);
        if ($companyId <= 0) {
            return false;
        }
        if (hasModulePermission('Master Data', 'Companies', ['view_all_companies'])) {
            return true;
        }
        $allowed = array_map('intval', array_merge((array) ($_SESSION['company_ids'] ?? []), [$_SESSION['company_id'] ?? 0]));
        return in_array($companyId, $allowed, true);
    }

    // Runs a write statement
    private function execute($sql, $types, $values) {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new Exception($this->db->error);
        $stmt->bind_param($types, ...$values);
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new Exception($error);
        }
        $stmt->close();
    }
}
?>
