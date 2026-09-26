<?php
// Weighing modal component data - dropdown lists for components/weighingModal/modal.php.
// Variables are prefixed with $wm so they don't clash with the including page's own lists.
// Requires layouts/head-main.php (for $db and hasPermission) and php/requires/lookup.php (for searchPlantById).

$wmUsername = $_SESSION["username"];
$wmCompanyId = $_SESSION['company_id'];
$wmSelectedCompanyId = intval($wmCompanyId);
$wmSelectedPlantId = $_SESSION['selected_plant_id'] ?? null;

if (!hasPermission('Weighing', ['view_all_companies'])) {
    $wmCompany = $db->query("SELECT * FROM Company WHERE status = 0 AND id IN ($wmSelectedCompanyId) ORDER BY name");
    $wmVehicles = $db->query("SELECT * FROM Vehicle WHERE status='0' AND company=$wmSelectedCompanyId ORDER BY veh_number ASC");
    $wmVehicles2 = $db->query("SELECT * FROM Vehicle WHERE status='0' AND company=$wmSelectedCompanyId ORDER BY veh_number ASC");
    $wmCustomer = $db->query("SELECT * FROM Customer WHERE status='0' AND company=$wmSelectedCompanyId ORDER BY name ASC");
    $wmProductSql = "SELECT p.*,IFNULL(c.is_sales,'Y') as is_sales,IFNULL(c.is_purchase,'Y') as is_purchase,IFNULL(c.is_local,'Y') as is_local,IFNULL(c.is_port,'Y') as is_port,IFNULL(c.is_misc,'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category=c.id WHERE p.status='0' AND p.company=$wmSelectedCompanyId ORDER BY p.name ASC";
    $wmProduct = $db->query($wmProductSql);
    $wmRawMaterial = $db->query($wmProductSql);
    $wmDestination = $db->query("SELECT * FROM Destination WHERE status='0' AND company=$wmSelectedCompanyId ORDER BY name ASC");
    $wmSupplier = $db->query("SELECT * FROM Supplier WHERE status='0' AND company=$wmSelectedCompanyId ORDER BY name ASC");
    $wmProjects = $db->query("SELECT * FROM Project WHERE status = '0' AND company=$wmSelectedCompanyId ORDER BY project_code ASC");
} else {
    $wmCompany = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
    $wmVehicles = $db->query("SELECT * FROM Vehicle WHERE status = '0' ORDER BY veh_number ASC");
    $wmVehicles2 = $db->query("SELECT * FROM Vehicle WHERE status = '0' ORDER BY veh_number ASC");
    $wmCustomer = $db->query("SELECT * FROM Customer WHERE status = '0' ORDER BY name ASC");
    $wmProductSql = "SELECT p.*, IFNULL(c.is_sales, 'Y') as is_sales, IFNULL(c.is_purchase, 'Y') as is_purchase, IFNULL(c.is_local, 'Y') as is_local, IFNULL(c.is_port, 'Y') as is_port, IFNULL(c.is_misc, 'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = '0' ORDER BY p.name ASC";
    $wmProduct = $db->query($wmProductSql);
    $wmRawMaterial = $db->query($wmProductSql);
    $wmDestination = $db->query("SELECT * FROM Destination WHERE status = '0' ORDER BY name ASC");
    $wmSupplier = $db->query("SELECT * FROM Supplier WHERE status = '0' ORDER BY name ASC");
    $wmProjects = $db->query("SELECT * FROM Project WHERE status = '0' ORDER BY project_code ASC");
}

$wmPlantName = '-';
$wmPlantCode = '-';
if (!hasPermission('Weighing', ['view_all_plants'])){
    $wmPlant = searchPlantById($wmSelectedPlantId, $db);

    $wmStmt = $db->prepare("SELECT * from Plant WHERE id = ?");
    $wmStmt->bind_param('s', $wmSelectedPlantId);
    $wmStmt->execute();
    $wmResult = $wmStmt->get_result();

    if(($wmRow = $wmResult->fetch_assoc()) !== null){
        $wmPlantName = $wmRow['name'];
        $wmPlantCode = $wmRow['plant_code'];
    }
    $wmStmt->close();
}
else{
    $wmPlant = $db->query("SELECT * FROM Plant WHERE status = '0'");
}
