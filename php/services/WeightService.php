<?php
require_once __DIR__ . '/../services/BaseService.php';

class WeightService extends BaseService {

    // ─── Normal / Container ──────────────────────────────────────────────────────
    public function saveNormal($f) {
        $misValue = $this->getPlantCount($f['plantCode'], $f['transactionStatus']);
        $f['transactionId'] = $this->buildTransactionId($f['plantCode'], $f['transactionStatus'], $f['weightType'], $misValue);
        $params = $this->normalParams($f);
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $stmt = $this->db->prepare("INSERT INTO Weight ({$this->normalCols()}) VALUES ({$placeholders})");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        $this->persistCustomerSideFields($id, $f['customerSide']);
        $misValue++;
        $this->incrementPlantCount($f['plantCode'], $f['transactionStatus'], $misValue);
        if ($f['weightType'] === 'Container') {
            $this->flagContainerStatus($f['containerNo'], $f['isComplete']);
        }
        return ['id' => $id];
    }

    public function updateNormal($f) {
        $params = $this->normalParams($f);
        $params[] = $f['weightId'];
        $stmt = $this->db->prepare("UPDATE Weight SET {$this->normalSetCols()} WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
        $this->persistCustomerSideFields($f['weightId'], $f['customerSide']);
        if ($f['weightType'] === 'Container') {
            $this->flagContainerStatus($f['containerNo'], $f['isComplete']);
        }
        return ['id' => $f['weightId']];
    }

    // ─── Empty Container ─────────────────────────────────────────────────────────
    public function saveEmptyContainer($f) {
        if ((int)$f['grossIncoming'] < (int)$f['tareOutgoing']) {
            throw new Exception('Incoming Weight cannot be greater than outgoing weight');
        }
        $misValue = $this->getPlantCount($f['plantCode'], $f['transactionStatus']);
        $f['transactionId'] = $this->buildTransactionId($f['plantCode'], $f['transactionStatus'], $f['weightType'], $misValue);
        $params = $this->normalParams($f);
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $stmt = $this->db->prepare("INSERT INTO Weight_Container ({$this->normalCols()}) VALUES ({$placeholders})");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        $misValue++;
        $this->incrementPlantCount($f['plantCode'], $f['transactionStatus'], $misValue);
        return ['id' => $id];
    }

    public function updateEmptyContainer($f) {
        if ((int)$f['grossIncoming'] < (int)$f['tareOutgoing']) {
            throw new Exception('Incoming Weight cannot be greater than outgoing weight');
        }
        $params = $this->normalParams($f);
        $check = $this->db->prepare("SELECT id FROM Weight_Container WHERE id=?");
        if (!$check) {
            throw new Exception($this->db->error);
        }
        $check->bind_param('s', $f['weightId']);
        $check->execute();
        $check->store_result();
        $exists = $check->num_rows > 0;
        $check->close();
        if ($exists) {
            $p = array_merge($params, [$f['weightId']]);
            $stmt = $this->db->prepare("UPDATE Weight_Container SET {$this->normalSetCols()} WHERE id=?");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            $stmt->bind_param('sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', ...$p);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();
        } else {
            $cols = "id, {$this->normalCols()}";
            $placeholders = implode(',', array_fill(0, count($params) + 1, '?'));
            $stmt = $this->db->prepare("INSERT INTO Weight_Container ({$cols}) VALUES ({$placeholders})");
            if (!$stmt) {
                throw new Exception($this->db->error);
            }
            $p = array_merge([$f['weightId']], $params);
            $stmt->bind_param('sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', ...$p);
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            $stmt->close();
        }
        return ['id' => $f['weightId']];
    }

    // ─── Different Container ─────────────────────────────────────────────────────
    public function saveDifferentContainer($f) {
        if (empty($f['grossIncomingDate'])) {
            $f['grossIncomingDate'] = $f['grossIncomingDate2'];
        }
        $misValue = $this->getPlantCount($f['plantCode'], $f['transactionStatus']);
        $f['transactionId'] = $this->buildTransactionId($f['plantCode'], $f['transactionStatus'], $f['weightType'], $misValue);
        $params = $this->diffContainerParams($f);
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $stmt = $this->db->prepare("INSERT INTO Weight ({$this->diffCols()}) VALUES ({$placeholders})");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        $this->persistCustomerSideFields($id, $f['customerSide']);
        $misValue++;
        $this->incrementPlantCount($f['plantCode'], $f['transactionStatus'], $misValue);
        if ($f['isComplete'] === 'Y') {
            $diffContainerTransId = $this->buildTransactionId($f['plantCode'], $f['transactionStatus'], 'Normal', $misValue);
            $this->insertDiffContainerRecord($f, $diffContainerTransId, $f['replacementContainer']);
            $misValue++;
            $this->incrementPlantCount($f['plantCode'], $f['transactionStatus'], $misValue);
        }
        if (!empty($f['containerNo'])) {
            $this->flagContainerStatus($f['containerNo'], $f['isComplete']);
        }
        return ['id' => $id];
    }

    public function updateDifferentContainer($f) {
        $params = $this->diffContainerParams($f);
        $params[] = $f['weightId'];
        $stmt = $this->db->prepare("UPDATE Weight SET {$this->diffSetCols()} WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
        $this->persistCustomerSideFields($f['weightId'], $f['customerSide']);
        if (!empty($f['containerNo'])) {
            $this->flagContainerStatus($f['containerNo'], $f['isComplete']);
            if ($f['isComplete'] === 'Y') {
                $this->syncReplacementContainer($f);
            }
        }
        return ['id' => $f['weightId']];
    }

    // ─── Plant / Transaction Helpers ─────────────────────────────────────────────
    private function getPlantCountColumn($status) {
        $map = ['Purchase' => 'purchase', 'Local' => 'locals', 'Port' => 'port', 'Misc' => 'misc'];
        return $map[$status] ?? 'sales';
    }

    private function getPlantCount($plantCode, $status) {
        $col = $this->getPlantCountColumn($status);
        $stmt = $this->db->prepare("SELECT {$col} as curcount FROM Plant WHERE plant_code=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('s', $plantCode);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? $row['curcount'] : 0;
    }

    private function incrementPlantCount($plantCode, $status, $value) {
        $col = $this->getPlantCountColumn($status);
        $stmt = $this->db->prepare("UPDATE Plant SET {$col}=? WHERE plant_code=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('ss', $value, $plantCode);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
    }

    private function buildTransactionId($plantCode, $status, $weightType, $misValue) {
        $stmt = $this->db->prepare("SELECT * FROM status WHERE status=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('s', $status);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) {
            throw new Exception("Status not found: {$status}");
        }
        $transId = $plantCode . '/';
        if ($weightType === 'Container') {
            $transId .= 'C/' . $row['prefix'] . '/' . date('ym') . '-';
        } else {
            $transId .= $row['prefix'] . '/' . date('ym') . '-';
        }
        $charSize = strlen($misValue);
        for ($i = 0; $i < (4 - (int)$charSize); $i++) {
            $transId .= '0';
        }
        $transId .= $misValue;
        return $transId;
    }

    // ─── SQL Fragments ───────────────────────────────────────────────────────────

    private function normalCols() {
        return "company_id, transaction_id, transaction_status, weight_type, customer_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, raw_mat_code, raw_mat_name, container_no, seal_no, container_no2, seal_no2, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, gross_weight_by1, tare_weight1, tare_weight1_date, tare_weight_by1, nett_weight1, gross_weight2, gross_weight2_date, gross_weight_by2, tare_weight2, tare_weight2_date, tare_weight_by2, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, manual_weight, indicator_id, weighbridge_id, created_by, modified_by, indicator_id_2, product_description, unit_price, sub_total, sst, total_price, is_approved, plant_code, plant_name";
    }

    private function normalSetCols() {
        return "company_id=?, transaction_id=?, transaction_status=?, weight_type=?, customer_type=?, transaction_date=?, lorry_plate_no1=?, lorry_plate_no2=?, supplier_weight=?, order_weight=?, customer_code=?, customer_name=?, supplier_code=?, supplier_name=?, product_code=?, product_name=?, raw_mat_code=?, raw_mat_name=?, container_no=?, seal_no=?, container_no2=?, seal_no2=?, invoice_no=?, purchase_order=?, delivery_no=?, transporter_code=?, transporter=?, destination_code=?, destination=?, remarks=?, gross_weight1=?, gross_weight1_date=?, gross_weight_by1=?, tare_weight1=?, tare_weight1_date=?, tare_weight_by1=?, nett_weight1=?, gross_weight2=?, gross_weight2_date=?, gross_weight_by2=?, tare_weight2=?, tare_weight2_date=?, tare_weight_by2=?, nett_weight2=?, reduce_weight=?, final_weight=?, weight_different=?, weight_different_perc=?, is_complete=?, is_cancel=?, manual_weight=?, indicator_id=?, weighbridge_id=?, created_by=?, modified_by=?, indicator_id_2=?, product_description=?, unit_price=?, sub_total=?, sst=?, total_price=?, is_approved=?, plant_code=?, plant_name=?";
    }

    private function diffCols() {
        return "company_id, transaction_id, transaction_status, weight_type, customer_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, raw_mat_code, raw_mat_name, container_no, seal_no, container_no2, seal_no2, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, gross_weight_by1, tare_weight1, tare_weight1_date, tare_weight_by1, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, gross_weight_by2, tare_weight2, tare_weight2_date, tare_weight_by2, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, manual_weight, indicator_id, weighbridge_id, created_by, modified_by, indicator_id_2, product_description, unit_price, sub_total, sst, total_price, is_approved, plant_code, plant_name";
    }

    private function diffSetCols() {
        return "company_id=?, transaction_id=?, transaction_status=?, weight_type=?, customer_type=?, transaction_date=?, lorry_plate_no1=?, lorry_plate_no2=?, supplier_weight=?, order_weight=?, customer_code=?, customer_name=?, supplier_code=?, supplier_name=?, product_code=?, product_name=?, raw_mat_code=?, raw_mat_name=?, container_no=?, seal_no=?, container_no2=?, seal_no2=?, invoice_no=?, purchase_order=?, delivery_no=?, transporter_code=?, transporter=?, destination_code=?, destination=?, remarks=?, gross_weight1=?, gross_weight1_date=?, gross_weight_by1=?, tare_weight1=?, tare_weight1_date=?, tare_weight_by1=?, nett_weight1=?, lorry_no2_weight=?, empty_container2_weight=?, replacement_container=?, gross_weight2=?, gross_weight2_date=?, gross_weight_by2=?, tare_weight2=?, tare_weight2_date=?, tare_weight_by2=?, nett_weight2=?, reduce_weight=?, final_weight=?, weight_different=?, weight_different_perc=?, is_complete=?, is_cancel=?, manual_weight=?, indicator_id=?, weighbridge_id=?, created_by=?, modified_by=?, indicator_id_2=?, product_description=?, unit_price=?, sub_total=?, sst=?, total_price=?, is_approved=?, plant_code=?, plant_name=?";
    }

    // ─── Param Builders ──────────────────────────────────────────────────────────

    private function normalParams($f) {
        return [
            $f['companyId'], $f['transactionId'], $f['transactionStatus'], $f['weightType'], $f['customerType'], $f['transactionDate'],
            $f['vehiclePlateNo1'], $f['vehiclePlateNo2'], $f['supplierWeight'], $f['orderWeight'],
            $f['customerCode'], $f['customerName'], $f['supplierCode'], $f['supplierName'],
            $f['productCode'], $f['productName'], $f['rawMaterialCode'], $f['rawMaterialName'],
            $f['containerNo'], $f['sealNo'], $f['containerNo2'], $f['sealNo2'],
            $f['invoiceNo'], $f['purchaseOrder'], $f['deliveryNo'],
            $f['transporterCode'], $f['transporter'], $f['destinationCode'], $f['destination'], $f['otherRemarks'],
            $f['grossIncoming'], $f['grossIncomingDate'], $f['grossWeightBy1'],
            $f['tareOutgoing'], $f['tareOutgoingDate'], $f['tareWeightBy1'], $f['nettWeight'],
            $f['grossIncoming2'], $f['grossIncomingDate2'], $f['grossWeightBy2'],
            $f['tareOutgoing2'], $f['tareOutgoingDate2'], $f['tareWeightBy2'], $f['nettWeight2'],
            $f['reduceWeight'], $f['finalWeight'], $f['weightDifference'], $f['weightDifferencePerc'],
            $f['isComplete'], $f['isCancel'], $f['manualWeight'], $f['indicatorId'], $f['weighbridge'],
            $this->username, $this->username, $f['indicatorId2'],
            $f['productDescription'], $f['unitPrice'], $f['subTotalPrice'], $f['sstPrice'], $f['totalPrice'],
            $f['isApproved'], $f['plantCode'], $f['plant']
        ];
    }

    private function diffContainerParams($f, $useVehicle2ForPlate1 = false) {
        $plate1 = $useVehicle2ForPlate1 ? $f['vehiclePlateNo2'] : $f['vehiclePlateNo1'];
        return [
            $f['companyId'], $f['transactionId'], $f['transactionStatus'], $f['weightType'], $f['customerType'], $f['transactionDate'],
            $plate1, $f['vehiclePlateNo2'], $f['supplierWeight'], $f['orderWeight'],
            $f['customerCode'], $f['customerName'], $f['supplierCode'], $f['supplierName'],
            $f['productCode'], $f['productName'], $f['rawMaterialCode'], $f['rawMaterialName'],
            $f['containerNo'], $f['sealNo'], $f['containerNo2'], $f['sealNo2'],
            $f['invoiceNo'], $f['purchaseOrder'], $f['deliveryNo'],
            $f['transporterCode'], $f['transporter'], $f['destinationCode'], $f['destination'], $f['otherRemarks'],
            $f['grossIncoming'], $f['grossIncomingDate'], $f['grossWeightBy1'],
            $f['tareOutgoing'], $f['tareOutgoingDate'], $f['tareWeightBy1'], $f['nettWeight'],
            $f['vehicleWeight2'], $f['emptyContainerWeight2'], $f['replacementContainer'],
            $f['grossIncoming2'], $f['grossIncomingDate2'], $f['grossWeightBy2'],
            $f['tareOutgoing2'], $f['tareOutgoingDate2'], $f['tareWeightBy2'], $f['nettWeight2'],
            $f['reduceWeight'], $f['finalWeight'], $f['weightDifference'], $f['weightDifferencePerc'],
            $f['isComplete'], $f['isCancel'], $f['manualWeight'], $f['indicatorId'], $f['weighbridge'],
            $this->username, $this->username, $f['indicatorId2'],
            $f['productDescription'], $f['unitPrice'], $f['subTotalPrice'], $f['sstPrice'], $f['totalPrice'],
            $f['isApproved'], $f['plantCode'], $f['plant']
        ];
    }

    // ─── Shared DB Helpers ───────────────────────────────────────────────────────

    private function persistCustomerSideFields($weightId, $d) {
        if (empty($weightId)) return;
        $stmt = $this->db->prepare("UPDATE Weight SET customer_side_company=?, customer_side_removal_pass_no=?, customer_side_license_no=?, customer_side_moisture_content=?, customer_side_officer_name=?, customer_side_rainbow_driver=?, customer_side_time_in=?, customer_side_time_out=? WHERE id=?");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('sssssssss',
            $d['company'], $d['removalPassNo'], $d['licenseNo'],
            $d['moistureContent'], $d['officerName'], $d['rainbowDriver'],
            $d['timeIn'], $d['timeOut'], $weightId
        );
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
    }

    private function flagContainerStatus($containerNo, $isComplete) {
        if (empty($containerNo)) {
            return;
        }
        $stmt = $this->db->prepare("UPDATE Weight_Container SET is_cancel=? WHERE container_no=? AND status='0'");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('ss', $isComplete, $containerNo);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
    }

    private function insertDiffContainerRecord($f, $transactionId, $containerNo) {
        $saved = $f['transactionId'];
        $savedContainer = $f['containerNo'];
        $f['transactionId'] = $transactionId;
        $f['containerNo'] = $containerNo;
        $params = $this->diffContainerParams($f, true);
        $f['transactionId'] = $saved;
        $f['containerNo'] = $savedContainer;
        $placeholders = implode(',', array_fill(0, count($params), '?'));
        $stmt = $this->db->prepare("INSERT INTO Weight_Container ({$this->diffCols()}) VALUES ({$placeholders})");
        if (!$stmt) {
            throw new Exception($this->db->error);
        }
        $stmt->bind_param('sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', ...$params);
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    private function syncReplacementContainer($f) {
        $chk = $this->db->prepare("SELECT id FROM Weight_Container WHERE container_no=? AND is_complete='Y' AND is_cancel='N' AND status='0'");
        if (!$chk) {
            throw new Exception($this->db->error);
        }
        $chk->bind_param('s', $f['replacementContainer']);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();
        if ($row) {
            $existingId = $row['id'];
            $updSetCols = "company_id=?, transaction_status=?, weight_type=?, customer_type=?, transaction_date=?, lorry_plate_no1=?, lorry_plate_no2=?, supplier_weight=?, order_weight=?, customer_code=?, customer_name=?, supplier_code=?, supplier_name=?, product_code=?, product_name=?, raw_mat_code=?, raw_mat_name=?, container_no=?, seal_no=?, container_no2=?, seal_no2=?, invoice_no=?, purchase_order=?, delivery_no=?, transporter_code=?, transporter=?, destination_code=?, destination=?, remarks=?, gross_weight1=?, gross_weight1_date=?, gross_weight_by1=?, tare_weight1=?, tare_weight1_date=?, tare_weight_by1=?, nett_weight1=?, lorry_no2_weight=?, empty_container2_weight=?, replacement_container=?, gross_weight2=?, gross_weight2_date=?, gross_weight_by2=?, tare_weight2=?, tare_weight2_date=?, tare_weight_by2=?, nett_weight2=?, reduce_weight=?, final_weight=?, weight_different=?, weight_different_perc=?, is_complete=?, is_cancel=?, manual_weight=?, indicator_id=?, weighbridge_id=?, created_by=?, modified_by=?, indicator_id_2=?, product_description=?, unit_price=?, sub_total=?, sst=?, total_price=?, is_approved=?, plant_code=?, plant_name=?";
            $updParams = [
                $f['companyId'], $f['transactionStatus'], $f['weightType'], $f['customerType'], $f['transactionDate'],
                $f['vehiclePlateNo2'], $f['vehiclePlateNo2'], $f['supplierWeight'], $f['orderWeight'],
                $f['customerCode'], $f['customerName'], $f['supplierCode'], $f['supplierName'],
                $f['productCode'], $f['productName'], $f['rawMaterialCode'], $f['rawMaterialName'],
                $f['replacementContainer'], $f['sealNo'], $f['containerNo2'], $f['sealNo2'],
                $f['invoiceNo'], $f['purchaseOrder'], $f['deliveryNo'],
                $f['transporterCode'], $f['transporter'], $f['destinationCode'], $f['destination'], $f['otherRemarks'],
                $f['grossIncoming2'], $f['grossIncomingDate2'], $f['grossWeightBy2'],
                $f['vehicleWeight2'], $f['grossIncomingDate2'], $f['grossWeightBy2'], $f['emptyContainerWeight2'],
                $f['replacementContainer'], $f['vehicleWeight2'], $f['emptyContainerWeight2'],
                $f['grossIncoming2'], $f['grossIncomingDate2'], $f['grossWeightBy2'],
                $f['tareOutgoing2'], $f['tareOutgoingDate2'], $f['tareWeightBy2'], $f['nettWeight2'],
                $f['reduceWeight'], $f['finalWeight'], $f['weightDifference'], $f['weightDifferencePerc'],
                $f['isComplete'], $f['isCancel'], $f['manualWeight'], $f['indicatorId'], $f['weighbridge'],
                $this->username, $this->username, $f['indicatorId2'],
                $f['productDescription'], $f['unitPrice'], $f['subTotalPrice'], $f['sstPrice'], $f['totalPrice'],
                $f['isApproved'], $f['plantCode'], $f['plant'], $existingId
            ];
            $updStmt = $this->db->prepare("UPDATE Weight_Container SET {$updSetCols} WHERE id=?");
            if (!$updStmt) {
                throw new Exception($this->db->error);
            }
            $updStmt->bind_param('sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss', ...$updParams);
            if (!$updStmt->execute()) {
                throw new Exception($updStmt->error);
            }
            $updStmt->close();
        } else {
            $misValue = $this->getPlantCount($f['plantCode'], $f['transactionStatus']);
            $diffTransId = $this->buildTransactionId($f['plantCode'], $f['transactionStatus'], 'Normal', $misValue);
            $this->insertDiffContainerRecord($f, $diffTransId, $f['replacementContainer']);
            $misValue++;
            $this->incrementPlantCount($f['plantCode'], $f['transactionStatus'], $misValue);
        }
    }
}
?>