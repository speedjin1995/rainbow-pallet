<?php
// Sawn timber modal component data - dropdown lists for components/sawnTimberModal/modal.php.
// Variables are prefixed with $stm so they don't clash with the including page's own lists.
// Requires layouts/head-main.php (for $db and hasModulePermission) and php/requires/lookup.php (for searchPlantById).

$stmCompanyId = $_SESSION['company_id'];
$stmSelectedCompanyId = intval($stmCompanyId);
$stmSelectedPlantId = $_SESSION['selected_plant_id'] ?? null;
$stmCanViewAllCompanies = hasModulePermission('Sawn Timber', 'Sawn Timber', ['view_all_companies']);
$stmCanViewAllPlants = hasModulePermission('Sawn Timber', 'Sawn Timber', ['view_all_plants']);

if (!$stmCanViewAllCompanies) {
    $stmCompany = $db->query("SELECT * FROM Company WHERE status = 0 AND id IN ($stmSelectedCompanyId) ORDER BY name");
} else {
    $stmCompany = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
}

if (!$stmCanViewAllPlants) {
    $stmPlant = searchPlantById($stmSelectedPlantId, $db);
} else {
    $stmPlant = $db->query("SELECT * FROM Plant WHERE status = '0'");
}
