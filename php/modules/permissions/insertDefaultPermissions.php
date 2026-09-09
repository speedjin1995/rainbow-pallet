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
        ['Payment Voucher', 'Accounting'],
        ['Companies', 'Master Data'], 
        ['Customer', 'Master Data'], 
        ['Destination', 'Master Data'],
        ['Products', 'Master Data'], 
        ['Raw Material', 'Master Data'], 
        ['Supplier', 'Master Data'],
        ['Vehicles', 'Master Data'], 
        ['Plant', 'Master Data'], 
        ['Locations', 'Master Data'],
        ['Translation', 'Master Data'],
        ['PV Items', 'Master Data'],
        ['Modules', 'User Management'], 
        ['Permission', 'User Management'], 
        ['Role', 'User Management'], 
        ['User Setup', 'User Management'],
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing'],
        ['Normal Type', 'Weighing'], 
        ['Container Type', 'Weighing'], 
        ['Empty Container Type', 'Weighing'], 
        ['Different Container Type', 'Weighing'],
        ['Sawn Timber', 'Sawn Timber'],
    ]],
    ['name' => 'edit', 'modules' => [
        ['Payment Voucher', 'Accounting'],
        ['Companies', 'Master Data'], 
        ['Customer', 'Master Data'], 
        ['Destination', 'Master Data'],
        ['Products', 'Master Data'], 
        ['Raw Material', 'Master Data'], 
        ['Supplier', 'Master Data'],
        ['Vehicles', 'Master Data'], 
        ['Plant', 'Master Data'], 
        ['Locations', 'Master Data'],
        ['Translation', 'Master Data'],
        ['PV Items', 'Master Data'],
        ['Modules', 'User Management'], 
        ['Permission', 'User Management'], 
        ['Role', 'User Management'], 
        ['User Setup', 'User Management'],
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing'],
        ['Normal Type', 'Weighing'], 
        ['Container Type', 'Weighing'], 
        ['Empty Container Type', 'Weighing'], 
        ['Different Container Type', 'Weighing'],
        ['Sawn Timber', 'Sawn Timber'],
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
        ['Translation', 'Master Data'],
        ['PV Items', 'Master Data'],
        ['Modules', 'User Management'], 
        ['Permission', 'User Management'], 
        ['Role', 'User Management'], 
        ['User Setup', 'User Management'],
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing'],
        ['Normal Type', 'Weighing'], 
        ['Container Type', 'Weighing'], 
        ['Empty Container Type', 'Weighing'], 
        ['Different Container Type', 'Weighing'],
        ['Sawn Timber', 'Sawn Timber'],
    ]],
    ['name' => 'weight_out', 'modules' => [
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
        ['Payment Voucher', 'Accounting'],
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
        ['Delivery Order', 'Accounting'],
        ['Goods Received', 'Accounting'],
        ['Sales', 'Reports'], 
        ['Purchase', 'Reports'], 
        ['Port', 'Reports'], 
        ['Miscellaneous', 'Reports'],
        ['Sales', 'Weighing'], 
        ['Purchase', 'Weighing'], 
        ['Port', 'Weighing'], 
        ['Miscellaneous', 'Weighing'],
        ['Sawn Timber', 'Sawn Timber'],
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
        ['PV Items', 'Master Data'],
        ['User Setup', 'User Management'],
        ['Sawn Timber', 'Sawn Timber'],
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
        ['PV Items', 'Master Data'],
        ['User Setup', 'User Management'],
        ['Sawn Timber', 'Sawn Timber'],
    ]],
    ['name' => 'export', 'modules' => [
        ['Delivery Order', 'Accounting'], 
        ['Goods Received', 'Accounting'], 
        ['Audit Log', 'Reports'], 
        ['Api Log', 'Reports'],
        ['Sales', 'Reports'], 
        ['Purchase', 'Reports'], 
        ['Port', 'Reports'], 
        ['Miscellaneous', 'Reports'],
        ['Sawn Timber', 'Sawn Timber'],
    ]],
    ['name' => 'approval', 'modules' => [
        ['Payment Voucher', 'Accounting']
    ]],
    ['name' => 'post_to_sql', 'modules' => [
        ['Payment Voucher', 'Accounting'], 
        ['Delivery Order', 'Accounting'], 
        ['Goods Received', 'Accounting'], 
    ]],
    ['name' => 'include_price', 'modules' => [
        ['Audit Log', 'Reports'], 
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
function mapModulesToIds($modules, $moduleMap, $returnArray = false) {
    if (count($modules) === 1 && $modules[0] === 'All') {
        return $returnArray ? ['All'] : json_encode(['All']);
    }
    $ids = [];
    foreach ($modules as $mod) {
        $key = $mod[0] . '|' . $mod[1];
        if (isset($moduleMap[$key])) {
            $ids[] = (string)$moduleMap[$key];
        }
    }
    return $returnArray ? $ids : json_encode($ids);
}

$inserted = 0;
$updated = 0;
$skipped = 0;

foreach ($defaultPermissions as $permission) {
    $newModuleIds = mapModulesToIds($permission['modules'], $moduleMap, true);
    
    // Check if permission exists
    $checkStmt = $db->prepare("SELECT id, modules FROM permissions WHERE name = ?");
    $checkStmt->bind_param('s', $permission['name']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Permission exists - merge missing module IDs
        $existingModules = json_decode($row['modules'], true) ?: [];
        
        // Skip if "All"
        if ($existingModules === ['All'] || $newModuleIds === ['All']) {
            $skipped++;
        } else {
            // Add missing IDs
            $merged = array_unique(array_merge($existingModules, $newModuleIds));
            sort($merged);
            
            if (count($merged) > count($existingModules)) {
                $mergedJson = json_encode(array_values($merged));
                $updateStmt = $db->prepare("UPDATE permissions SET modules = ? WHERE id = ?");
                $updateStmt->bind_param('si', $mergedJson, $row['id']);
                $updateStmt->execute();
                $updateStmt->close();
                $updated++;
            } else {
                $skipped++;
            }
        }
    } else {
        // Permission doesn't exist - insert new
        $modulesJson = json_encode($newModuleIds);
        $insertStmt = $db->prepare("INSERT INTO permissions (name, modules) VALUES (?, ?)");
        $insertStmt->bind_param('ss', $permission['name'], $modulesJson);
        $insertStmt->execute();
        $insertStmt->close();
        $inserted++;
    }
    
    $checkStmt->close();
}
$db->close();

echo json_encode([
    'status' => 'success', 
    'message' => "Inserted: $inserted, Updated: $updated, Skipped: $skipped"
]);
