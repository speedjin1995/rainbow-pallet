<?php
session_start();
require_once '../../db_connect.php';

$defaultModules = [
    ['name' => 'Translation', 'category' => 'Master Data'],
    ['name' => 'Companies', 'category' => 'Master Data'],
    ['name' => 'Customer', 'category' => 'Master Data'],
    ['name' => 'Destination', 'category' => 'Master Data'],
    ['name' => 'Products', 'category' => 'Master Data'],
    ['name' => 'Raw Material', 'category' => 'Master Data'],
    ['name' => 'Supplier', 'category' => 'Master Data'],
    ['name' => 'Vehicles', 'category' => 'Master Data'],
    ['name' => 'Transporter', 'category' => 'Master Data'],
    ['name' => 'Plant', 'category' => 'Master Data'],
    ['name' => 'Locations', 'category' => 'Master Data'],
    ['name' => 'Modules', 'category' => 'User Management'],
    ['name' => 'User Setup', 'category' => 'User Management'],
    ['name' => 'Permission', 'category' => 'User Management'],
    ['name' => 'Role', 'category' => 'User Management'],
    ['name' => 'Weighing', 'category' => 'Reports'],
    ['name' => 'Audit Log', 'category' => 'Reports'],
    ['name' => 'Api Log', 'category' => 'Reports'],
];

$stmt = $db->prepare("INSERT INTO modules (name, category) SELECT ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM modules WHERE name = ? AND category = ?)");

if (!$stmt) {
    echo json_encode(['status' => 'failed', 'message' => $db->error]);
    exit;
}

$inserted = 0;
$skipped = 0;

foreach ($defaultModules as $module) {
    $stmt->bind_param('ssss', $module['name'], $module['category'], $module['name'], $module['category']);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $inserted++;
    } else {
        $skipped++;
    }
}

$stmt->close();
$db->close();

echo json_encode([
    'status' => 'success', 
    'message' => "Inserted: $inserted, Skipped: $skipped"
]);
