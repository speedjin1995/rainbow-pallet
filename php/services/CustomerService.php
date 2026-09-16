<?php
require_once __DIR__ . '/BaseService.php';

class CustomerService extends BaseService {

    /**
     * Auto register customer from manual input
     * @param string $customerName
     * @return array ['customer_code' => code, 'name' => name]
     */
    public function autoRegisterCustomer($customerName) {
        $customerName = trim($customerName);
        
        $stmt = $this->db->prepare("SELECT customer_code FROM Customer WHERE name=? AND status='0'");
        $stmt->bind_param('s', $customerName);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($row) {
            return ['customer_code' => $row['customer_code'], 'name' => $customerName];
        }
        
        $customerCode = 'C' . date('ymdHis') . rand(100, 999);
        $isManual = 'Y';
        
        $stmt = $this->db->prepare("INSERT INTO Customer (customer_code, name, is_manual, created_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $customerCode, $customerName, $isManual, $this->username);
        $stmt->execute();
        $stmt->close();
        
        return ['customer_code' => $customerCode, 'name' => $customerName];
    }
}
?>
