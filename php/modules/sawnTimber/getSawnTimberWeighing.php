<?php
session_start();
require_once '../../db_connect.php';

// Get weighing records where product category is "Sawn Timber" and not yet posted to sawn timber
$sql = "SELECT w.id, w.transaction_id, w.transaction_status, 
        w.customer_code, w.customer_name, w.supplier_code, w.supplier_name,
        w.destination, w.lorry_plate_no1, w.delivery_no, w.transaction_date
        FROM Weight w
        LEFT JOIN Product p ON w.product_code = p.product_code
        LEFT JOIN Product_Categories pc ON p.category = pc.id
        WHERE pc.category_name = 'Sawn Timber'
        AND w.status = 0
        AND w.is_complete = 'Y'
        AND w.synced = 'N'
        AND NOT EXISTS (
            SELECT 1 FROM Sawn_Timber_Header sth 
            WHERE sth.weight_id = w.id AND sth.status = '0'
        )
        ORDER BY w.transaction_date DESC";

$result = $db->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['id'],
        'transaction_id' => $row['transaction_id'],
        'transaction_status' => $row['transaction_status'],
        'customer_code' => $row['customer_code'],
        'customer_name' => $row['customer_name'],
        'supplier_code' => $row['supplier_code'],
        'supplier_name' => $row['supplier_name'],
        'destination' => $row['destination'],
        'lorry_plate_no1' => $row['lorry_plate_no1'],
        'delivery_no' => $row['delivery_no'],
        'transaction_date' => $row['transaction_date']
    ];
}

echo json_encode(['status' => 'success', 'data' => $data]);
?>
