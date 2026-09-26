<?php
require_once __DIR__ . '/../requires/permissions.php';

/**
 * Base Service Class
 * All module services should extend this class
 */
class BaseService {
    protected $db;
    protected $username;

    // Columns holding each master data record's code / name in Weight and Weight_Container.
    // vehicle = Vehicle also stores it (customers / suppliers linked to a vehicle).
    private static $masterDataColumns = [
        'Customer' => ['code' => 'customer_code', 'name' => 'customer_name', 'vehicle' => true],
        'Supplier' => ['code' => 'supplier_code', 'name' => 'supplier_name', 'vehicle' => true],
        'Destination' => ['code' => 'destination_code', 'name' => 'destination'],
        'Product' => ['code' => 'product_code', 'name' => 'product_name'],
        'Raw Material' => ['code' => 'raw_mat_code', 'name' => 'raw_mat_name'],
        'Plant' => ['code' => 'plant_code', 'name' => 'plant_name'],
    ];

    public function __construct($db, $username) {
        $this->db = $db;
        $this->username = $username;
    }

    /**
     * True for values that must not be saved as master data:
     * null, empty / whitespace only, or "-" (the dropdown placeholder).
     */
    protected function isBlankValue($value) {
        if ($value === null) {
            return true;
        }
        $value = trim((string) $value);
        return $value === '' || $value === '-';
    }

    /**
     * True when the company id is missing or not a valid id.
     */
    protected function isInvalidCompanyId($companyId) {
        return $this->isBlankValue($companyId) || (int) $companyId <= 0;
    }

    /**
     * When a master data code changes, update it in the records that reference it.
     * $companyId limits the update to that company's records; null updates every company
     * (for master data without a company, e.g. Plant).
     */
    public function updateMasterDataCodeValue($oldValue, $newValue, $module, $companyId = null) {
        $this->updateMasterDataReferences('code', $oldValue, $newValue, $module, $companyId);
    }

    /**
     * When a master data name changes, update it in the records that reference it.
     * $companyId works the same as in updateMasterDataCodeValue().
     */
    public function updateMasterDataNameValue($oldValue, $newValue, $module, $companyId = null) {
        $this->updateMasterDataReferences('name', $oldValue, $newValue, $module, $companyId);
    }

    private function updateMasterDataReferences($field, $oldValue, $newValue, $module, $companyId) {
        if (!isset(self::$masterDataColumns[$module])) {
            return;
        }

        $config = self::$masterDataColumns[$module];
        $column = $config[$field];

        // Table => its company column
        $tables = ['Weight' => 'company_id', 'Weight_Container' => 'company_id'];
        if (!empty($config['vehicle'])) {
            $tables['Vehicle'] = 'company';
        }

        foreach ($tables as $table => $companyColumn) {
            $sql = "UPDATE {$table} SET {$column} = ? WHERE {$column} = ?";
            $types = 'ss';
            $params = [$newValue, $oldValue];

            if ($companyId !== null && $companyId !== '') {
                $sql .= " AND {$companyColumn} = ?";
                $types .= 'i';
                $params[] = (int) $companyId;
            }

            $stmt = $this->db->prepare($sql);
            if (!$stmt) throw new Exception($this->db->error);
            $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) throw new Exception($stmt->error);
            $stmt->close();
        }
    }
}
?>
