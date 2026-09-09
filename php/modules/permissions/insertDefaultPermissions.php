<?php
session_start();
require_once '../../db_connect.php';

// Build module lookup: "name|category" => id
$moduleMap = [];
$result = $db->query("SELECT id, name, category FROM modules");
while ($row = $result->fetch_assoc()) {
    $key = $row['name'] . '|' . $row['category'];
    $moduleMap[$key] = $row['id'];
}

// Define permissions with module names: [name, category] or "All"
$defaultPermissions = [
    ['name' => 'view', 'modules' => ['All']],
    ['name' => 'create', 'modules' => [
        ['Delivery Order', 'Accounting'],
        ['Companies', 'Master Data'], 
        ['Customer', 'Master Data'], 
        ['Destination', 'Master Data'],
        ['Products', 'Master Data'], 
        ['Raw Material', 'Master Data'], 
        ['Supplier', 'Master Data'],
        ['Vehicles', 'Master Data'], 
        ['Plant', 'Master Data'], 
        ['Locations', 'Master Data'],
        ['Modules', 'User Management'], 
        ['Permission', 'User Management'], 
        ['Role', 'User Management'], 
        ['User Setup', 'User Management'],
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing']
    ]],
    ['name' => 'edit', 'modules' => [
        ['Delivery Order', 'Accounting'],
        ['Companies', 'Master Data'], 
        ['Customer', 'Master Data'], 
        ['Destination', 'Master Data'],
        ['Products', 'Master Data'], 
        ['Raw Material', 'Master Data'], 
        ['Supplier', 'Master Data'],
        ['Vehicles', 'Master Data'], 
        ['Plant', 'Master Data'], 
        ['Locations', 'Master Data'],
        ['Modules', 'User Management'], 
        ['Permission', 'User Management'], 
        ['Role', 'User Management'], 
        ['User Setup', 'User Management'],
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing']
    ]],
    ['name' => 'cancelled', 'modules' => [
        ['Companies', 'Master Data'], 
        ['Customer', 'Master Data'], 
        ['Destination', 'Master Data'],
        ['Products', 'Master Data'], 
        ['Raw Material', 'Master Data'], 
        ['Supplier', 'Master Data'],
        ['Vehicles', 'Master Data'], 
        ['Plant', 'Master Data'], 
        ['Locations', 'Master Data'],
        ['Modules', 'User Management'], 
        ['Permission', 'User Management'], 
        ['Role', 'User Management'], 
        ['User Setup', 'User Management'],
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing']
    ]],
    ['name' => 'manual_weighing', 'modules' => [
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing']
    ]],
    ['name' => 'manual_date_change', 'modules' => [
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing']
    ]],
    ['name' => 'print', 'modules' => [
        ['Delivery Order', 'Accounting'],
        ['Sales', 'Reports'], 
        ['Purchase', 'Reports'], 
        ['Port', 'Reports'], 
        ['Miscellaneous', 'Reports'],
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing']
    ]],
    ['name' => 'view_all_plants', 'modules' => [
        ['Audit Log', 'Reports'], 
        ['Api Log', 'Reports'], 
        ['Delivery Order', 'Accounting'],
        ['Sales', 'Reports'], 
        ['Purchase', 'Reports'], 
        ['Port', 'Reports'], 
        ['Miscellaneous', 'Reports'],
        ['User Setup', 'User Management'],
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing']
    ]],
    ['name' => 'download_template', 'modules' => [
        ['Companies', 'Master Data'], 
        ['Customer', 'Master Data'], 
        ['Destination', 'Master Data'],
        ['Products', 'Master Data'], 
        ['Raw Material', 'Master Data'], 
        ['Supplier', 'Master Data'],
        ['Vehicles', 'Master Data'], 
        ['Plant', 'Master Data'], 
        ['Locations', 'Master Data'],
        ['User Setup', 'User Management']
    ]],
    ['name' => 'upload_excel', 'modules' => [
        ['Companies', 'Master Data'], 
        ['Customer', 'Master Data'], 
        ['Destination', 'Master Data'],
        ['Products', 'Master Data'], 
        ['Raw Material', 'Master Data'], 
        ['Supplier', 'Master Data'],
        ['Vehicles', 'Master Data'], 
        ['Plant', 'Master Data'], 
        ['Locations', 'Master Data'],
        ['User Setup', 'User Management']
    ]],
    ['name' => 'export', 'modules' => [
        ['Audit Log', 'Reports'], 
        ['Api Log', 'Reports'],
        ['Sales', 'Reports'], 
        ['Purchase', 'Reports'], 
        ['Port', 'Reports'], 
        ['Miscellaneous', 'Reports']
    ]],
    ['name' => 'approval', 'modules' => [
        ['Delivery Order', 'Accounting']
    ]],
    ['name' => 'post_to_sql', 'modules' => [
        ['Audit Log', 'Reports'], 
        ['Api Log', 'Reports']
    ]],
    ['name' => 'include_price', 'modules' => [
        ['Audit Log', 'Reports'], 
        ['Api Log', 'Reports'], 
        ['Goods Received', 'Accounting'],
        ['Sales', 'Reports'], 
        ['Purchase', 'Reports'], 
        ['Port', 'Reports'], 
        ['Miscellaneous', 'Reports']
    ]],
    ['name' => 'assign_permissions', 'modules' => [
        ['Role', 'User Management']
    ]],
    ['name' => 'reset_password', 'modules' => [
        ['User Setup', 'User Management']
    ]]
];

// Helper function to convert module names to IDs
function mapModulesToIds($modules, $moduleMap) {
    if (count($modules) === 1 && $modules[0] === 'All') {
        return json_encode(['All']);
    }
    $ids = [];
    foreach ($modules as $mod) {
        $key = $mod[0] . '|' . $mod[1];
        if (isset($moduleMap[$key])) {
            $ids[] = (string)$moduleMap[$key];
        }
    }
    return json_encode($ids);
}

$stmt = $db->prepare("INSERT INTO permissions (name, modules) SELECT ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE name = ?)");

if (!$stmt) {
    echo json_encode(['status' => 'failed', 'message' => $db->error]);
    exit;
}

$inserted = 0;
$skipped = 0;

foreach ($defaultPermissions as $permission) {
    $modulesJson = mapModulesToIds($permission['modules'], $moduleMap);
    $stmt->bind_param('sss', $permission['name'], $modulesJson, $permission['name']);
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
