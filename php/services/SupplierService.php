<?php
require_once __DIR__ . '/BaseService.php';

class SupplierService extends BaseService {

    /**
     * Auto register supplier from manual input
     * @param string $supplierName
     * @return array ['supplier_code' => code, 'name' => name]
     */
    public function autoRegisterSupplier($supplierName) {
        $supplierName = trim($supplierName);
        
        $stmt = $this->db->prepare("SELECT supplier_code FROM Supplier WHERE name=? AND status='0'");
        $stmt->bind_param('s', $supplierName);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($row) {
            return ['supplier_code' => $row['supplier_code'], 'name' => $supplierName];
        }
        
        $supplierCode = 'S' . date('ymdHis') . rand(100, 999);
        $isManual = 'Y';
        
        $stmt = $this->db->prepare("INSERT INTO Supplier (supplier_code, name, is_manual, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $supplierCode, $supplierName, $isManual, $this->username);
        $stmt->execute();
        $stmt->close();
        
        return ['supplier_code' => $supplierCode, 'name' => $supplierName];
    }
}
?>
