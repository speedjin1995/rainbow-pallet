<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
require_once "php/requires/lookup.php";
require_once "php/requires/functions.php";

if (!hasPermission('Weighing', ['view', 'create', 'edit'])){
    header('Location: no-permission.php');
    exit;
}

$user = $_SESSION['id'];
$username = $_SESSION["username"];
$plantId = $_SESSION['plant'];
$selectedPlantId = $_SESSION['selected_plant_id'] ?? null;
$role = 'NORMAL';
if ($user != null && $user != ''){
    $stmt3 = $db->prepare("SELECT * from Users WHERE id = ?");
    $stmt3->bind_param('s', $user);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
        
    if(($row3 = $result3->fetch_assoc()) !== null){
        $role = $row3['role'];
    }
}

//$lots = $db->query("SELECT * FROM lots WHERE deleted = '0'");
$companyId = $_SESSION['company_id'];
$selectedCompanyId = intval($companyId);
if (!hasPermission('Weighing', ['view_all_companies'])) {
    $company_ids = implode(',', array_map('intval', $_SESSION['company_ids']));
    $company2 = $db->query("SELECT * FROM Company WHERE status = 0 AND id IN ($selectedCompanyId) ORDER BY name");
    $customer2 = $db->query("SELECT * FROM Customer WHERE status='0' AND company=$selectedCompanyId ORDER BY name ASC");
    $productSql="SELECT p.*,IFNULL(c.is_sales,'Y') as is_sales,IFNULL(c.is_purchase,'Y') as is_purchase,IFNULL(c.is_local,'Y') as is_local,IFNULL(c.is_port,'Y') as is_port,IFNULL(c.is_misc,'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category=c.id WHERE p.status='0' AND p.company=$selectedCompanyId ORDER BY p.name ASC";
    $product2 = $db->query($productSql);
    $rawMaterial2 = $db->query($productSql);
    $supplier2 = $db->query("SELECT * FROM Supplier WHERE status='0' AND company=$selectedCompanyId ORDER BY name ASC");
    $container = $db->query("SELECT * FROM Weight_Container WHERE status = '0' AND is_complete = 'Y' AND is_cancel = 'N' AND company_id = $selectedCompanyId");
} else {
    $company2 = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
    $customer2 = $db->query("SELECT * FROM Customer WHERE status = '0' ORDER BY name ASC");
    $productSql = "SELECT p.*, IFNULL(c.is_sales, 'Y') as is_sales, IFNULL(c.is_purchase, 'Y') as is_purchase, IFNULL(c.is_local, 'Y') as is_local, IFNULL(c.is_port, 'Y') as is_port, IFNULL(c.is_misc, 'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = '0' ORDER BY p.name ASC";
    $product2 = $db->query($productSql);
    $rawMaterial2 = $db->query($productSql);
    $supplier2 = $db->query("SELECT * FROM Supplier WHERE status = '0' ORDER BY name ASC");
    $container = $db->query("SELECT * FROM Weight_Container WHERE status = '0' AND is_complete = 'Y' AND is_cancel = 'N'");
}

$transporter = $db->query("SELECT * FROM Transporter WHERE status = '0' ORDER BY name ASC");
$purchaseOrder = $db->query("SELECT * FROM Purchase_Order WHERE status = 'Open' AND deleted = '0' ORDER BY po_no ASC");
$salesOrder = $db->query("SELECT * FROM Sales_Order WHERE status = 'Open' AND deleted = '0' ORDER BY order_no ASC");

$plantName = '-';
$plantCode = '-';
if (!hasPermission('Weighing', ['view_all_plants'])){
    $plant2 = searchPlantById($selectedPlantId, $db);
    
    $stmt2 = $db->prepare("SELECT * from Plant WHERE id = ?");
    $stmt2->bind_param('s', $selectedPlantId);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
        
    if(($row2 = $result2->fetch_assoc()) !== null){
        $plantName = $row2['name'];
        $plantCode = $row2['plant_code'];
    }
}
else{
    $plant2 = $db->query("SELECT * FROM Plant WHERE status = '0'");
}

// Weighing modal component data ($wm* dropdown lists)
require_once "components/weighingModal/data.php";
?>

<head>

    <title><?=$languageArray['weighing_code'][$language]?> | Synctronix - Weighing System</title>
    <?php include 'layouts/title-meta.php'; ?>

    <!-- jsvectormap css -->
    <link href="assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />
    <!--datatable css-->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

    <!-- Include jQuery library -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Include jQuery Validate plugin -->
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>

    <?php include 'layouts/head-css.php'; ?>
    <style>
        .mb-3 {
            margin-bottom: 0.5rem !important;
        }

        .modal-header {
            padding: var(1rem, 1rem) !important;
        }
    </style>
</head>

<?php include 'layouts/body.php'; ?>

<div class="loading" id="spinnerLoading" style="display:none">
  <div class='mdi mdi-loading' style='transform:scale(0.79);'>
    <div></div>
  </div>
</div>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="h-100">
                            <div class="col-xxl-12 col-lg-12">
                                <div class="card">
                                    <div class="card-header fs-5 text-white" href="#collapseSearch" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseSearch" style="background-color: #405189;">
                                        <i class="mdi mdi-chevron-down pull-right"></i>
                                        <?=$languageArray['search_records_code'][$language]?>
                                    </div>
                                    <div id="collapseSearch" class="collapse" aria-labelledby="collapseSearch">                                    
                                        <div class="card-body">
                                            <form action="javascript:void(0);">
                                                <div class="row">
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="fromDateSearch" class="form-label"><?=$languageArray['from_date_code'][$language]?></label>
                                                            <input type="date" class="form-control" data-provider="flatpickr" id="fromDateSearch">
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="toDateSearch" class="form-label"><?=$languageArray['to_date_code'][$language]?></label>
                                                            <input type="date" class="form-control" data-provider="flatpickr" id="toDateSearch">
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="statusSearch" class="form-label"><?=$languageArray['transaction_status_code'][$language]?></label>
                                                            <select id="statusSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <?php if(hasModulePermission('Weighing', 'Sales', ['view'])) { ?>
                                                                    <option value="Sales"><?=$languageArray['dispatch_code'][$language]?></option>
                                                                <?php } ?>
                                                                <?php if(hasModulePermission('Weighing', 'Purchase', ['view'])) { ?>
                                                                    <option value="Purchase"><?=$languageArray['receiving_code'][$language]?></option>
                                                                <?php } ?>
                                                                <?php if(hasModulePermission('Weighing', 'Local', ['view'])) { ?>
                                                                    <!-- <option value="Local"><?=$languageArray['internal_transfer_code'][$language]?></option> -->
                                                                <?php } ?>
                                                                <?php if(hasModulePermission('Weighing', 'Port', ['view'])) { ?>
                                                                    <option value="Port"><?=$languageArray['trx_to_port_code'][$language]?></option>
                                                                <?php } ?>
                                                                <?php if(hasModulePermission('Weighing', 'Miscellaneous', ['view'])) { ?>
                                                                    <option value="Misc"><?=$languageArray['miscellaneous_code'][$language]?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3" style="<?= !hasPermission('Weighing', ['view_all_companies']) ? 'display:none' : '' ?>">
                                                        <div class="mb-3">
                                                            <label for="companySearch" class="form-label"><?=$languageArray['company_code'][$language]?></label>
                                                            <select id="companySearch" class="form-select select2" >
                                                                <?php while($rowCompany = mysqli_fetch_assoc($company2)){ ?>
                                                                    <option value="<?=$rowCompany['id'] ?>" <?=(hasPermission('Weighing', ['view_all_companies']) ? $rowCompany['id'] == 1 : $rowCompany['id'] == $companyId) ? 'selected' : ''?>><?=$rowCompany['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3" id="customerSearchDisplay">
                                                        <div class="mb-3">
                                                            <label for="customerNoSearch" class="form-label"><?=$languageArray['customer_code'][$language]?></label>
                                                            <select id="customerNoSearch" class="form-select select2" >
                                                                <option selected>-</option>
                                                                <?php while($rowPF = mysqli_fetch_assoc($customer2)){ ?>
                                                                    <option value="<?=$rowPF['customer_code'] ?>"><?=$rowPF['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3" id="supplierSearchDisplay" style="display:none">
                                                        <div class="mb-3">
                                                            <label for="supplierSearch" class="form-label"><?=$languageArray['supplier_code'][$language]?></label>
                                                            <select id="supplierSearch" class="form-select select2" >
                                                                <option selected>-</option>
                                                                <?php while($rowSF = mysqli_fetch_assoc($supplier2)){ ?>
                                                                    <option value="<?=$rowSF['supplier_code'] ?>"><?=$rowSF['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="vehicleNo" class="form-label"><?=$languageArray['vehicle_no_code'][$language]?></label>
                                                            <input type="text" class="form-control" placeholder="Vehicle No" id="vehicleNo">
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="invoiceNoSearch" class="form-label"><?=$languageArray['weighing_type_code'][$language]?></label>
                                                            <select id="invoiceNoSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <?php if(hasModulePermission('Weighing', 'Normal Type', ['view'])) { ?>
                                                                    <option value="Normal"><?=$languageArray['normal_weighing_code'][$language]?></option>
                                                                <?php } ?>
                                                                <?php if(hasModulePermission('Weighing', 'Container Type', ['view'])) { ?>
                                                                    <option value="Container"><?=$languageArray['primer_mover_code'][$language]?></option>
                                                                <?php } ?>
                                                                <?php if(hasModulePermission('Weighing', 'Empty Container Type', ['view'])) { ?>
                                                                    <option value="Empty Container"><?=$languageArray['primer_mover_container_code'][$language]?></option>
                                                                <?php } ?>
                                                                <?php if(hasModulePermission('Weighing', 'Different Container Type', ['view'])) { ?>
                                                                    <option value="Different Container"><?=$languageArray['primer_mover_different_bins_code'][$language]?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="batchNoSearch" class="form-label"><?=$languageArray['status_code'][$language]?></label>
                                                            <select id="batchNoSearch" class="form-select select2">
                                                                <option value="N" selected><?=$languageArray['pending_code'][$language]?></option>
                                                                <option value="Y"><?=$languageArray['complete_code'][$language]?></option>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->                                                
                                                    <div class="col-3" id="productSearchDisplay">
                                                        <div class="mb-3">
                                                            <label for="productSearch" class="form-label"><?=$languageArray['product_code'][$language]?></label>
                                                            <select id="productSearch" class="form-select select2" >
                                                                <option selected>-</option>
                                                                <?php while($rowProductF=mysqli_fetch_assoc($product2)){ ?>
                                                                    <option value="<?=$rowProductF['product_code'] ?>" data-is-sales="<?=$rowProductF['is_sales'] ?>" data-is-purchase="<?=$rowProductF['is_purchase'] ?>" data-is-port="<?=$rowProductF['is_port'] ?>" data-is-misc="<?=$rowProductF['is_misc'] ?>"><?=$rowProductF['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3" id="rawMatSearchDisplay" style="display:none">
                                                        <div class="mb-3">
                                                            <label for="rawMatSearch" class="form-label"><?=$languageArray['raw_material_code'][$language]?></label>
                                                            <select id="rawMatSearch" class="form-select select2" >
                                                                <option selected>-</option>
                                                                <?php while($rowRawMatF=mysqli_fetch_assoc($rawMaterial2)){ ?>
                                                                    <option value="<?=$rowRawMatF['product_code'] ?>" data-is-sales="<?=$rowRawMatF['is_sales'] ?>" data-is-purchase="<?=$rowRawMatF['is_purchase'] ?>" data-is-port="<?=$rowRawMatF['is_port'] ?>" data-is-misc="<?=$rowRawMatF['is_misc'] ?>"><?=$rowRawMatF['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="plantSearch" class="form-label"><?=$languageArray['plant_code'][$language]?></label>
                                                            <select id="plantSearch" class="form-select select2" >
                                                                <option value="">-</option>
                                                                <?php while($rowPlantF=mysqli_fetch_assoc($plant2)){ ?>
                                                                    <option value="<?=$rowPlantF['plant_code'] ?>" <?= ($rowPlantF['plant_code'] == $plantCode) ? 'selected' : '' ?>><?=$rowPlantF['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="transactionIdSearch" class="form-label"><?=$languageArray['transaction_id_code'][$language]?></label>
                                                            <input type="text" class="form-control" id="transactionIdSearch" name="transactionIdSearch" placeholder="<?=$languageArray['transaction_id_code'][$language]?>">                                                                                  
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="containerNoSearch" class="form-label"><?=$languageArray['container_no_code'][$language]?></label>
                                                            <input type="text" class="form-control" id="containerNoSearch" name="containerNoSearch" placeholder="<?=$languageArray['container_no_code'][$language]?>">                                                                                  
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="sealNoSearch" class="form-label"><?=$languageArray['seal_no_code'][$language]?></label>
                                                            <input type="text" class="form-control" id="sealNoSearch" name="sealNoSearch" placeholder="<?=$languageArray['seal_no_code'][$language]?>">                                                                                  
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="invDelPoSearch" class="form-label"><?=$languageArray['inv_del_po_no_code'][$language]?></label>
                                                            <input type="text" class="form-control" id="invDelPoSearch" name="invDelPoSearch" placeholder="<?=$languageArray['inv_del_po_no_code'][$language]?>">                                                                                  
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-lg-12">
                                                        <div class="text-end">
                                                            <button type="button" class="btn btn-danger" id="clearAllSearch"><i class="bx bx-x"></i> <?=$languageArray['clear_all_code'][$language]?></button>
                                                            <button type="submit" class="btn btn-success" id="filterSearch"><i class="bx bx-search-alt"></i> <?=$languageArray['search_code'][$language]?></button>
                                                        </div>
                                                    </div><!--end col-->
                                                </div><!--end row-->
                                            </form>                                                                        
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <!-- <div class="col-xl-3 col-md-6">
                                    <div class="card card-animate" style="background-color: #4CAF50;">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <p class="text-uppercase fw-medium text-white text-truncate mb-0">
                                                        Sales
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-end justify-content-between mt-4">
                                                <div>
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                        <span class="counter-value text-white" id="salesInfo">0</span>
                                                    </h4>
                                                </div>
                                                <div class="avatar-sm flex-shrink-0" style="background-color:white;">
                                                    <span class="avatar-title bg-soft-success rounded fs-3">
                                                        <i class="bx bx-dollar-circle text-success"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <div class="card card-animate" style="background-color: #FFC107;">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <p class="text-uppercase fw-medium text-white text-truncate mb-0">
                                                        Purchase
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-end justify-content-between mt-4">
                                                <div>
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                        <span class="counter-value text-white" id="purchaseInfo">0</span>
                                                    </h4>
                                                </div>
                                                <div class="avatar-sm flex-shrink-0" style="background-color:white;">
                                                    <span class="avatar-title bg-soft-info rounded fs-3">
                                                        <i class="bx bx-shopping-bag text-info"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <div class="card card-animate" style="background-color: #81D4FA;">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <p class="text-uppercase fw-medium text-white text-truncate mb-0">
                                                        Transfer to Port
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-end justify-content-between mt-4">
                                                <div>
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                        <span class="counter-value text-white" id="localInfo">0</span>
                                                    </h4>
                                                </div>
                                                <div class="avatar-sm flex-shrink-0" style="background-color:white;">
                                                    <span class="avatar-title bg-soft-warning rounded fs-3">
                                                        <i class="bx bx-user-circle text-warning"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-3 col-md-6">
                                    <div class="card card-animate" style="background-color: #9C27B0;">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <p class="text-white text-uppercase fw-medium text-truncate mb-0">
                                                        Miscellaneous
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-end justify-content-between mt-4">
                                                <div>
                                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                                        <span class="counter-value text-white" id="miscInfo">0</span>
                                                    </h4>
                                                </div>
                                                <div class="avatar-sm flex-shrink-0" style="background-color:white;">
                                                    <span class="avatar-title bg-soft-warning rounded fs-3">
                                                        <i class="bx bx-user-circle text-warning"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> -->
                                
                                <div class="col-xl-3 col-md-6 add-new-weight">
                                    <!-- <button type="button" class="btn btn-lg btn-soft-success" data-bs-toggle="modal" data-bs-target="#addModal"><i
                                            class="ri-add-circle-line align-middle me-1"></i>
                                        <?=$languageArray['add_new_code'][$language]?></button> -->

                                    <!-- /.modal-dialog -->
                                    <?php include 'components/weighingModal/modal.php'; ?>
                                    
                                    <?php include 'components/customerSideInfoModal/modal.php'; ?>

                                    <div class="modal fade" id="cancelModal">
                                        <div class="modal-dialog modal-xl" style="max-width: 90%;">
                                            <div class="modal-content">
                                                <form role="form" id="cancelForm">
                                                    <div class="modal-header bg-gray-dark color-palette">
                                                        <h4 class="modal-title"><?=$languageArray['cancellation_reason_code'][$language]?></h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="form-group">
                                                                <label><?=$languageArray['cancellation_reason_code'][$language]?> *</label>
                                                                <textarea class="form-control" id="cancelReason" name="cancelReason" rows="3"></textarea>
                                                            </div>
                                                            <input type="hidden" class="form-control" id="id" name="id">                                   
                                                            <input type="hidden" class="form-control" id="containerId" name="containerId">                                   
                                                            <input type="hidden" class="form-control" id="isEmptyContainer" name="isEmptyContainer">                                   
                                                            <input type="hidden" class="form-control" id="isMulti" name="isMulti">                                   
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer justify-content-between bg-gray-dark color-palette">
                                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                        <button type="button" class="btn btn-success" id="submitCancel"><?=$languageArray['submit_code'][$language]?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end row-->

                            <div class="row">
                                <div class="col">
                                    <div class="h-100">
                                        <!--datatable--> 
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card">
                                                    <div class="card-header" style="background-color: #405189;">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <h5 class="card-title mb-0 text-white"><?=$languageArray['previous_records_code'][$language]?> (Lorry)</h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <!-- <button type="button" id="exportPdf" class="btn btn-danger waves-effect waves-light">
                                                                    <i class="ri-file-pdf-line align-middle me-1"></i>
                                                                    <?=$languageArray['export_pdf_code'][$language]?>
                                                                </button>
                                                                <button type="button" id="exportExcel" class="btn btn-info waves-effect waves-light" >
                                                                    <i class="ri-file-excel-line align-middle me-1"></i>
                                                                    <?=$languageArray['export_excel_code'][$language]?>
                                                                </button> -->
                                                                <?php if(hasPermission('Weighing', 'cancelled')): ?>
                                                                <button type="button" id="multiDeleteLorry" class="btn btn-warning waves-effect waves-light" >
                                                                    <i class="ri-delete-bin-fill align-middle me-1"></i>
                                                                    <?=$languageArray['delete_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>
                                                                <?php if(hasPermission('Weighing', 'create')): ?>
                                                                <button type="button" id="addWeight" class="btn btn-success waves-effect waves-light">
                                                                    <i class="ri-add-circle-line align-middle me-1"></i>
                                                                    <?=$languageArray['add_new_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>
                                                            </div> 
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="weightTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                                    <th><?=$languageArray['company_code'][$language]?></th>
                                                                    <th><?=$languageArray['transaction_id_code'][$language]?></th>
                                                                    <th><?=$languageArray['weight_type_code'][$language]?></th>
                                                                    <th><?=$languageArray['weight_status_code'][$language]?></th>
                                                                    <th><?=$languageArray['customer_supplier_code'][$language]?></th>
                                                                    <th><?=$languageArray['container_no_code'][$language]?></th>
                                                                    <th><?=$languageArray['seal_no_code'][$language]?></th>
                                                                    <th><?=$languageArray['vehicle_code'][$language]?></th>
                                                                    <th><?=$languageArray['gross_incoming_code'][$language]?></th>
                                                                    <th><?=$languageArray['incoming_date_code'][$language]?></th>
                                                                    <th><?=$languageArray['tare_outgoing_code'][$language]?></th>
                                                                    <th><?=$languageArray['outgoing_date_code'][$language]?></th>
                                                                    <th><?=$languageArray['nett_weight_code'][$language]?></th>
                                                                    <th><?=$languageArray['vehicle_code'][$language]?> 2</th>
                                                                    <th><?=$languageArray['gross_incoming_code'][$language]?> 2</th>
                                                                    <th><?=$languageArray['incoming_date_code'][$language]?> 2</th>
                                                                    <th><?=$languageArray['tare_outgoing_code'][$language]?> 2</th>
                                                                    <th><?=$languageArray['outgoing_date_code'][$language]?> 2</th>
                                                                    <th><?=$languageArray['nett_weight_code'][$language]?> 2</th>
                                                                    <th><?=$languageArray['action_code'][$language]?></th>
                                                                </tr>
                                                            </thead>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--end row-->
                                    </div> <!-- end .h-100-->
                                </div> <!-- end col -->
                            </div><!-- container-fluid -->

                            <!-- Second Card for Empty Container -->
                            <div class="row">
                                <div class="col">
                                    <div class="h-100">
                                        <!--datatable--> 
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card">
                                                    <div class="card-header" style="background-color: #405189;">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <h5 class="card-title mb-0 text-white"><?=$languageArray['pending_empty_container_records_code'][$language]?></h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <?php if(hasPermission('Weighing', 'cancelled')): ?>
                                                                <button type="button" id="multiDeleteContainer" class="btn btn-warning waves-effect waves-light" >
                                                                    <i class="ri-delete-bin-fill align-middle me-1"></i>
                                                                    <?=$languageArray['delete_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>
                                                            </div> 
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="emptyContainerTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllContainerCheckbox" class="selectAllContainerCheckbox"></th>
                                                                    <th><?=$languageArray['company_code'][$language]?></th>
                                                                    <th><?=$languageArray['container_no_code'][$language]?></th>
                                                                    <th><?=$languageArray['seal_no_code'][$language]?></th>
                                                                    <th><?=$languageArray['weight_status_code'][$language]?></th>
                                                                    <th><?=$languageArray['vehicle_code'][$language]?></th>
                                                                    <th><?=$languageArray['gross_incoming_code'][$language]?></th>
                                                                    <th><?=$languageArray['incoming_date_code'][$language]?></th>
                                                                    <th><?=$languageArray['tare_outgoing_code'][$language]?></th>
                                                                    <th><?=$languageArray['outgoing_date_code'][$language]?></th>
                                                                    <th><?=$languageArray['nett_weight_code'][$language]?></th>
                                                                    <th><?=$languageArray['action_code'][$language]?></th>
                                                                </tr>
                                                            </thead>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!--end row-->
                                    </div> <!-- end .h-100-->
                                </div> <!-- end col -->
                            </div><!-- container-fluid -->
                        </div> <!-- end .h-100-->

                    </div> <!-- end col -->
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            <?php include 'layouts/footer.php'; ?>
        </div>
        <!-- end main content-->

    </div>

    <!-- END layout-wrapper -->

    <?php include 'layouts/customizer.php'; ?>
    <?php include 'layouts/vendor-scripts.php'; ?>
    <!-- apexcharts -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
    <!-- Vector map-->
    <script src="assets/libs/jsvectormap/js/jsvectormap.min.js"></script>
    <script src="assets/libs/jsvectormap/maps/world-merc.js"></script>
    <!--Swiper slider js-->
    <script src="assets/libs/swiper/swiper-bundle.min.js"></script>
    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard-ecommerce.init.js"></script>   
    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <!-- prismjs plugin -->
    <script src="assets/libs/prismjs/prism.js"></script>
    <!-- notifications init -->
    <script src="assets/js/pages/notifications.init.js"></script>
    <script src="plugins/datatables/jquery.dataTables.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="assets/js/pages/datatables.init.js"></script>
    <!-- Additional js -->
    <script src="assets/js/additional.js"></script>
    <!-- Weighing modal component -->
    <?php include 'components/weighingModal/script.php'; ?>
    <!-- Customer side info modal component -->
    <?php include 'components/customerSideInfoModal/script.php'; ?>

    <script type="text/javascript">
    var table = null;
    var emptyContainerTable = null;
    var allProductSearchOptions = null;
    var allRawMatSearchOptions = null;
    let clickTimer = null;

    var fromDateSearchPicker;
    var toDateSearchPicker;
    var permissions = <?= json_encode($_SESSION['permissions']) ?>;
    var isSADMIN = <?= json_encode($_SESSION['roles'] == 'SADMIN') ?>;

    $(function () {
        var userRole = '<?=$role ?>';
        const today = new Date();
        const tomorrow = new Date(today);
        const yesterday = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        yesterday.setDate(yesterday.getDate() - 1);

        // Initialize all Select2 elements in the search bar
        $('#collapseSearch .select2').select2({
            allowClear: true,
            placeholder: "Please Select",
        });

        // Apply custom styling to Select2 elements in search bar
        $('.select2-container .select2-selection--single').css({
            'padding-top': '4px',
            'padding-bottom': '4px',
            'height': 'auto'
        });

        $('.select2-container .select2-selection__arrow').css({
            'padding-top': '33px',
            'height': 'auto'
        });

        //Date picker
        fromDateSearchPicker = $('#fromDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: ''
        });

        toDateSearchPicker = $('#toDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: ''
        });

        $('#companySearch').on('change', function(){
            loadSearchListsByCompany($(this).val());
        });

        // Load search filter and modal dropdowns for the initially-selected company on page load
        var initialCompany = $('#companySearch').val();
        if (initialCompany) {
            loadSearchListsByCompany(initialCompany);
        }

        // Clear All Filter Function
        $('#clearAllSearch').on('click', function(){
            fromDateSearchPicker.clear();
            toDateSearchPicker.clear();
            $('#statusSearch').val('-').trigger('change');
            $('#customerNoSearch').val('-').trigger('change');
            $('#supplierSearch').val('-').trigger('change');
            $('#vehicleNo').val('');
            $('#invoiceNoSearch').val('-').trigger('change');
            $('#batchNoSearch').val('N').trigger('change');
            $('#productSearch').val('-').trigger('change');
            $('#rawMatSearch').val('-').trigger('change');
            $('#plantSearch').val('-').trigger('change');
            $('#transactionIdSearch').val('');
            $('#containerNoSearch').val('');
            $('#sealNoSearch').val('');
            $('#invDelPoSearch').val('');
        });

        $('#statusSearch').on('change', function(){
            var status = $(this).val();
            filterDropdownByTransactionStatus('#productSearch', 'allProductSearchOptions', status);
            filterDropdownByTransactionStatus('#rawMatSearch', 'allRawMatSearchOptions', status);

            if (status == 'Purchase' || status == 'Local'){
                // Hide & reset customer then show supplier
                $('#customerSearchDisplay').hide();
                $('#customerSearchDisplay').find('#customerNoSearch').val('-').trigger('change');
                $('#supplierSearchDisplay').show();
                // Hide & reset product then show raw material
                $('#productSearchDisplay').find('#productSearch').val('-').trigger('change');
                $('#productSearchDisplay').hide();
                $('#rawMatSearchDisplay').show();
            }else{
                // Hide & reset supplier then show customer
                $('#supplierSearchDisplay').find('#supplierSearch').val('-').trigger('change');
                $('#supplierSearchDisplay').hide();
                $('#customerSearchDisplay').show();
                // Hide & reset raw material then show product
                $('#rawMatSearchDisplay').find('#rawMatSearch').val('-').trigger('change');
                $('#rawMatSearchDisplay').hide();
                $('#productSearchDisplay').show();
            }
        });

        $('#selectAllCheckbox').on('change', function() {
            var checkboxes = $('#weightTable tbody input[type="checkbox"]');
            checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
        });

        $('#selectAllContainerCheckbox').on('change', function() {
            var checkboxes = $('#emptyContainerTable tbody input[type="checkbox"]');
            checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
        });

        // Initial render
        renderTable();

        // Filter search click
        $('#filterSearch').on('click', function(){
            renderTable();
        });

        // Add event listener for opening and closing details on row click
        $('#weightTable tbody').on('click', 'tr', function (e) {
            var tr = $(this); // The row that was clicked
            var row = table.row(tr);
            if (!row.data()) return; // <-- Exit early if row data is not available

            // Exclude specific td elements by checking the event target
            if ($(e.target).closest('td').hasClass('select-checkbox') || $(e.target).closest('td').hasClass('action-button') || row.data().weight_type =='Primer Mover + Container') {
                return;
            }

            // Clear any previous timer if have
            if (clickTimer) {
                clearTimeout(clickTimer);
                clickTimer = null;
            }

            // Delay to detect double-click
            clickTimer = setTimeout(function () {
                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    $.post('php/modules/weighing/index.php', { action: 'getWeight', userID: row.data().id, format: 'EXPANDABLE' }, function (data) {
                        var obj = JSON.parse(data);
                        if (obj.status === 'success') {
                            row.child(format(obj.message)).show();
                            tr.addClass("shown");
                        }
                    });
                }

                clickTimer = null; // Reset after execution
            }, 250); // Delay to distinguish from double-click
        });

        // Add event listener for double click
        $('#weightTable tbody').on('dblclick', 'tr', function (e) {
            if (clickTimer) {
                clearTimeout(clickTimer); // Cancel single-click
                clickTimer = null;
            }

            var row = table.row(this);
            var id = row.data().id;
            var weightType = row.data().weight_type;

            // run edit function
            if (weightType == 'Empty Container'){
                editWeight(id, 'Y');
            }else{
                editWeight(id, 'N');
            }
        });

        $('#submitCancel').on('click', function(){
            if($('#cancelForm').valid()){
                $('#spinnerLoading').show();
                var id = $('#cancelModal').find('#id').val();
                $.post('php/modules/weighing/index.php', $('#cancelForm').serialize() + '&action=delete', function(data){
                    var obj = JSON.parse(data);
                    
                    if(obj.status === 'success'){
                        table.ajax.reload();
                        emptyContainerTable.ajax.reload();
                        $('#spinnerLoading').hide();
                        $('#cancelModal').modal('hide');
                        $("#successBtn").attr('data-toast-text', obj.message);
                        $("#successBtn").click();
                    }
                    else if(obj.status === 'failed'){
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                    else{
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                });
            }
        });

        // Weighing modal: after a normal save reload the page, "Save & Print" opens the pre-print modal instead
        initWeighingModal({
            onSaved: function(obj, withPrint){
                if (!withPrint) {
                    table.ajax.reload();
                    window.location = 'index.php';
                }
            }
        });

        // Customer side info modal: keep the current page of the table after saving
        initCustomerSideInfoModal({
            onSaved: function(obj){
                table.ajax.reload(null, false);
            }
        });

        $('#addWeight').on('click', function(){
            addWeight();
        });

        $('#exportPdf').on('click', function(){
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();
            var statusI = $('#statusSearch').val() ? $('#statusSearch').val() : '';
            var customerNoI = $('#customerNoSearch').val() ? $('#customerNoSearch').val() : '';
            var supplierNoI = $('#supplierSearch').val() ? $('#supplierSearch').val() : '';
            var vehicleNoI = $('#vehicleNo').val() ? $('#vehicleNo').val() : '';
            var invoiceNoI = $('#invoiceNoSearch').val() ? $('#invoiceNoSearch').val() : '';
            var batchNoI = $('#batchNoSearch').val() ? $('#batchNoSearch').val() : '';
            var productSearchI = $('#productSearch').val() ? $('#productSearch').val() : '';
            var rawMaterialI = $('#rawMatSearch').val() ? $('#rawMatSearch').val() : '';
            var plantNoI = $('#plantSearch').val() ? $('#plantSearch').val() : '';

            if (batchNoI == 'N'){
                batchNoI = 'Pending';
            }else if (batchNoI == 'Y'){
                batchNoI = 'Complete';
            }

            var selectedIds = []; // An array to store the selected 'id' values

            $("#weightTable tbody input[type='checkbox']").each(function () {
                if (this.checked) {
                    var type = $(this).data('type'); // Get data-type attribute
                    if (type == 'Lorry'){
                        selectedIds.push($(this).val());
                    }
                }
            });

            if (selectedIds.length > 0) {
                $.post('php/modules/report/index.php?action=exportPdf', {
                    fromDate : fromDateI,
                    toDate : fromDateI,
                    transactionStatus : statusI,
                    company : $('#companySearch').val() || '',
                    customer : customerNoI,
                    supplier : supplierNoI,
                    vehicle : vehicleNoI,
                    weighingType : invoiceNoI,
                    status : batchNoI,
                    product : productSearchI,
                    rawMat : rawMaterialI,
                    plant : plantNoI,
                    isMulti : 'Y',
                    ids : selectedIds,
                    file : 'weight'
                }, function(response){
                    var obj = JSON.parse(response);

                    if(obj.status === 'success'){
                        var printWindow = window.open('', '', 'height=' + screen.height + ',width=' + screen.width);
                        printWindow.document.write(obj.message);
                        printWindow.document.close();
                        setTimeout(function(){
                            printWindow.print();
                            printWindow.close();
                        }, 500);
                    }
                    else if(obj.status === 'failed'){
                        toastr["error"](obj.message, "Failed:");
                    }
                    else{
                        toastr["error"]("Something wrong when activate", "Failed:");
                    }
                }).fail(function(error){
                    console.error("Error exporting PDF:", error);
                    alert("An error occurred while generating the PDF.");
                });
            }else{
                $.post('php/modules/report/index.php?action=exportPdf', {
                    fromDate : fromDateI,
                    toDate : fromDateI,
                    transactionStatus : statusI,
                    company : $('#companySearch').val() || '',
                    customer : customerNoI,
                    supplier : supplierNoI,
                    vehicle : vehicleNoI,
                    weighingType : invoiceNoI,
                    status : batchNoI,
                    product : productSearchI,
                    rawMat : rawMaterialI,
                    plant : plantNoI,
                    isMulti : 'N',
                    file : 'weight'
                }, function(response){
                    var obj = JSON.parse(response);

                    if(obj.status === 'success'){
                        var printWindow = window.open('', '', 'height=' + screen.height + ',width=' + screen.width);
                        printWindow.document.write(obj.message);
                        printWindow.document.close();
                        setTimeout(function(){
                            printWindow.print();
                            printWindow.close();
                        }, 500);
                    }
                    else if(obj.status === 'failed'){
                        toastr["error"](obj.message, "Failed:");
                    }
                    else{
                        toastr["error"]("Something wrong when activate", "Failed:");
                    }
                }).fail(function(error){
                    console.error("Error exporting PDF:", error);
                    alert("An error occurred while generating the PDF.");
                });
            }
        });

        $('#exportExcel').on('click', function(){
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();
            var statusI = $('#statusSearch').val() ? $('#statusSearch').val() : '';
            var customerNoI = $('#customerNoSearch').val() ? $('#customerNoSearch').val() : '';
            var supplierNoI = $('#supplierSearch').val() ? $('#supplierSearch').val() : '';
            var vehicleNoI = $('#vehicleNo').val() ? $('#vehicleNo').val() : '';
            var invoiceNoI = $('#invoiceNoSearch').val() ? $('#invoiceNoSearch').val() : '';
            var batchNoI = $('#batchNoSearch').val() ? $('#batchNoSearch').val() : '';
            var productSearchI = $('#productSearch').val() ? $('#productSearch').val() : '';
            var rawMaterialI = $('#rawMatSearch').val() ? $('#rawMatSearch').val() : '';
            var plantNoI = $('#plantSearch').val() ? $('#plantSearch').val() : '';
            
            if (batchNoI == 'N'){
                batchNoI = 'Pending';
            }else if (batchNoI == 'Y'){
                batchNoI = 'Complete';
            }

            var selectedIds = []; // An array to store the selected 'id' values

            $("#weightTable tbody input[type='checkbox']").each(function () {
                if (this.checked) {
                    var type = $(this).data('type'); // Get data-type attribute
                    if (type == 'Lorry'){
                        selectedIds.push($(this).val());
                    }
                }
            });

            if (selectedIds.length > 0) {
                window.open("php/modules/report/index.php?action=exportExcel&file=weight&fromDate="+fromDateI+"&toDate="+toDateI+
                "&transactionStatus="+statusI+"&company="+encodeURIComponent($('#companySearch').val() || '')+"&customer="+customerNoI+"&supplier="+supplierNoI+"&vehicle="+vehicleNoI+
                "&weighingType="+invoiceNoI+"&product="+productSearchI+"&rawMat="+rawMaterialI+"&plant="+plantNoI+"&status="+batchNoI+"&isMulti=Y&ids="+selectedIds);
            }else{
                window.open("php/modules/report/index.php?action=exportExcel&file=weight&fromDate="+fromDateI+"&toDate="+toDateI+
                "&transactionStatus="+statusI+"&company="+encodeURIComponent($('#companySearch').val() || '')+"&customer="+customerNoI+"&supplier="+supplierNoI+"&vehicle="+vehicleNoI+
                "&weighingType="+invoiceNoI+"&product="+productSearchI+"&rawMat="+rawMaterialI+"&plant="+plantNoI+"&status="+batchNoI+"&isMulti=N");
            }
        });

        $('#multiDeleteLorry').on('click', function(){
            var selectedLorryIds = []; // An array to store the selected 'id' values
            var selectedEmptyContainerIds = []; // An array to store the selected 'id' values

            $("#weightTable tbody input[type='checkbox']").each(function () {
                if (this.checked) {
                    var type = $(this).data('type'); // Get data-type attribute
                    if (type == 'Lorry'){
                        selectedLorryIds.push($(this).val());
                    }else{
                        selectedEmptyContainerIds.push($(this).val());
                    }
                }
            });

            if (selectedLorryIds.length > 0 || selectedEmptyContainerIds.length > 0) {
                if (confirm('Are you sure you want to cancel these weighing records?')) {
                    $('#cancelModal').find('#id').val(selectedLorryIds);
                    $('#cancelModal').find('#containerId').val(selectedEmptyContainerIds);
                    $('#cancelModal').find('#isEmptyContainer').val('N');
                    $('#cancelModal').find('#isMulti').val('Y');
                    $('#cancelModal').modal('show');

                    $('#cancelForm').validate({
                        errorElement: 'span',
                        errorPlacement: function (error, element) {
                            error.addClass('invalid-feedback');
                            element.closest('.form-group').append(error);
                        },
                        highlight: function (element, errorClass, validClass) {
                            $(element).addClass('is-invalid');
                        },
                        unhighlight: function (element, errorClass, validClass) {
                            $(element).removeClass('is-invalid');
                        }
                    });
                }
            }else{
                alert("Please select at least one weighing record to delete.");
            }
        });

        $('#multiDeleteContainer').on('click', function(){
            var selectedIds = []; // An array to store the selected 'id' values

            $("#emptyContainerTable tbody input[type='checkbox']").each(function () {
                if (this.checked) {
                    selectedIds.push($(this).val());
                }
            });

            if (selectedIds.length > 0) {
                if (confirm('Are you sure you want to cancel these weighing records?')) {
                    $('#cancelModal').find('#id').val(selectedIds);
                    $('#cancelModal').find('#isEmptyContainer').val('Y');
                    $('#cancelModal').find('#isMulti').val('Y');
                    $('#cancelModal').modal('show');

                    $('#cancelForm').validate({
                        errorElement: 'span',
                        errorPlacement: function (error, element) {
                            error.addClass('invalid-feedback');
                            element.closest('.form-group').append(error);
                        },
                        highlight: function (element, errorClass, validClass) {
                            $(element).addClass('is-invalid');
                        },
                        unhighlight: function (element, errorClass, validClass) {
                            $(element).removeClass('is-invalid');
                        }
                    });
                }
            }else{
                alert("Please select at least one weighing record to delete.");
            }
        });

        //Container No Search
        $('#containerNoSearch').on('keyup', function(){
            var x = $('#containerNoSearch').val();
            x = x.toUpperCase();
            $('#containerNoSearch').val(x);
        });

        //Seal No Search
        $('#sealNoSearch').on('keyup', function(){
            var x = $('#sealNoSearch').val();
            x = x.toUpperCase();
            $('#sealNoSearch').val(x);
        });

        <?php
            if(isset($_GET['weight'])){
                echo 'editWeight('.$_GET['weight'].');';
            }
        ?>
    });

    // Rebuild a dropdown: "-" placeholder followed by one option per item
    function fillOptions(selector, items, buildOption) {
        var $sel = $(selector);
        $sel.empty().append('<option selected>-</option>');
        $.each(items || [], function(i, item) {
            $sel.append(buildOption(item));
        });
        $sel.val('-').trigger('change');
    }

    // Reload the search bar listings for the selected company (one request returns all the lists)
    function loadSearchListsByCompany(companyId) {
        if (!companyId || companyId === '-') {
            return;
        }
        var productFiltered = allProductSearchOptions !== null;
        var rawMatFiltered = allRawMatSearchOptions !== null;

        $.post('php/modules/weighing/index.php', { action: 'companyLists', company: companyId }, function(data) {
            var lists = {};
            try {
                var obj = JSON.parse(data);
                if (obj.status === 'success') {
                    lists = obj.data;
                }
            } catch (e) {
                console.error('Failed to load search lists', e);
            }

            fillOptions('#customerNoSearch', lists.customers, function(item) {
                return $('<option>').val(item.customer_code).text(item.name);
            });
            fillOptions('#supplierSearch', lists.suppliers, function(item) {
                return $('<option>').val(item.supplier_code).text(item.name);
            });
            fillOptions('#productSearch', lists.products, function(item) {
                return productOption(item, item.product_code, item.name);
            });
            fillOptions('#rawMatSearch', lists.products, function(item) {
                return productOption(item, item.product_code, item.name);
            });

            allProductSearchOptions = null;
            allRawMatSearchOptions = null;
            var status = $('#statusSearch').val();
            if (productFiltered) filterDropdownByTransactionStatus('#productSearch', 'allProductSearchOptions', status);
            if (rawMatFiltered) filterDropdownByTransactionStatus('#rawMatSearch', 'allRawMatSearchOptions', status);
        });
    }

    // Filter dropdown options based on transaction status
    function filterDropdownByTransactionStatus(selector, allOptionsVar, status) {
        if (!window[allOptionsVar]) {
            window[allOptionsVar] = $(selector + ' option').clone(true);
        }

        var dataAttr = 'is-sales';
        if (status === 'Sales') dataAttr = 'is-sales';
        else if (status === 'Purchase') dataAttr = 'is-purchase';
        else if (status === 'Port') dataAttr = 'is-port';
        else if (status === 'Misc') dataAttr = 'is-misc';

        $(selector).empty();
        window[allOptionsVar].each(function() {
            var $option = $(this).clone(true);
            if ($option.val() === '-' || $option.val() === '') {
                $(selector).append($option);
            } else if ($option.data(dataAttr) === 'Y') {
                $(selector).append($option);
            }
        });

        $(selector).val('-').trigger('change');
    }

    function productOption(item, value, text) {
        return $('<option>').val(value).text(text)
            .attr('data-code', item.product_code)
            .attr('data-high', item.high)
            .attr('data-low', item.low)
            .attr('data-variance', item.variance)
            .attr('data-description', item.description)
            .attr('data-is-sales', item.is_sales)
            .attr('data-is-purchase', item.is_purchase)
            .attr('data-is-port', item.is_port)
            .attr('data-is-misc', item.is_misc);
    }

    function renderTable() {
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();
        var statusI = $('#statusSearch').val() || '';
        var companyI = $('#companySearch').val() || '';
        var customerNoI = $('#customerNoSearch').val() || '';
        var supplierI = $('#supplierSearch').val() || '';
        var vehicleNoI = $('#vehicleNo').val() || '';
        var invoiceNoI = $('#invoiceNoSearch').val() || '';
        var batchNoI = $('#batchNoSearch').val() || '';
        var productSearchI = $('#productSearch').val() || '';
        var rawMaterialI = $('#rawMatSearch').val() || '';
        var plantNoI = $('#plantSearch').val() || '';
        var transactionIdI = $('#transactionIdSearch').val() || '';
        var containerNoI = $('#containerNoSearch').val() || '';
        var sealNoI = $('#sealNoSearch').val() || '';
        var invDelPoI = $('#invDelPoSearch').val() || '';

        // Destroy old DataTables if exist
        if ($.fn.DataTable.isDataTable('#weightTable')) {
            $("#weightTable").DataTable().clear().destroy();
        }
        if ($.fn.DataTable.isDataTable('#emptyContainerTable')) {
            $("#emptyContainerTable").DataTable().clear().destroy();
        }

        table = $("#weightTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            'processing': true,
            'serverSide': true,
            'searching': true,
            'serverMethod': 'post',
            'ajax': {
                'url':'php/modules/weighing/index.php',
                'data': function(d) {
                    d.action = 'filterWeight';
                    d.fromDate = fromDateI;
                    d.toDate = toDateI;
                    d.status = statusI;
                    d.company = companyI;
                    d.customer = customerNoI;
                    d.supplier = supplierI;
                    d.vehicle = vehicleNoI;
                    d.invoice = invoiceNoI;
                    d.batch = batchNoI;
                    d.product = productSearchI;
                    d.rawMaterial = rawMaterialI;
                    d.plant = plantNoI;
                    d.transactionId = transactionIdI;
                    d.containerNo = containerNoI;
                    d.sealNo = sealNoI;
                    d.invDelPo = invDelPoI;
                    return d;
                }
            },
            'columns': [
                {
                    data: 'id',
                    className: 'select-checkbox',
                    orderable: false,
                    render: function (data, type, row) {
                        if (row.weight_type == 'Primer Mover + Container'){
                            return '<input type="checkbox" class="select-checkbox" id="checkbox_' + data + '" value="'+data+'" data-type="Empty Container"/>';
                        }else{
                            return '<input type="checkbox" class="select-checkbox" id="checkbox_' + data + '" value="'+data+'" data-type="Lorry"/>';
                        }
                    }
                },
                { data: 'company_name' },
                { data: 'transaction_id' },
                { data: 'weight_type' },
                { data: 'transaction_status' },
                { data: 'customer' },
                { data: 'container_no' },
                { data: 'seal_no' },
                { data: 'lorry_plate_no1' },
                { data: 'gross_weight1' },
                { data: 'gross_weight1_date' },
                { data: 'tare_weight1' },
                { data: 'tare_weight1_date' },
                { data: 'nett_weight1' },
                { data: 'lorry_plate_no2' },
                { data: 'gross_weight2' },
                { data: 'gross_weight2_date' },
                { data: 'tare_weight2' },
                { data: 'tare_weight2_date' },
                { data: 'nett_weight2' },
                { 
                    data: 'id',
                    class: 'action-button',
                    render: function (data, type, row) {
                        var transactionKey = row.transaction_status;
                        if (transactionKey == 'Transfer To Port'){
                            transactionKey = 'Port';
                        }

                        var buttons = `<div class="row g-1 d-flex">`;

                        if (isSADMIN || (permissions['Weighing'] && permissions['Weighing'][transactionKey] && permissions['Weighing'][transactionKey].includes('edit'))) {
                            if (row.weight_type == 'Primer Mover + Container'){
                                buttons += `
                                <div class="col-auto">
                                    <button title="Edit" type="button" id="edit${data}" onclick="editWeight(${data}, 'Y')" class="btn btn-warning btn-sm">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                </div>`;
                            }else{
                                buttons += `
                                <div class="col-auto">
                                    <button title="Edit" type="button" id="edit${data}" onclick="editWeight(${data}, 'N')" class="btn btn-warning btn-sm">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                </div>`;
                            }
                        }else {
                            if (row.is_complete != 'Y' ){
                                if (isSADMIN || (permissions['Weighing'] && permissions['Weighing'][transactionKey] && permissions['Weighing'][transactionKey].includes('weight_out'))) {
                                    if (row.weight_type == 'Primer Mover + Container'){
                                        buttons += `
                                        <div class="col-auto">
                                            <button title="Weight Out" type="button" id="edit${data}" onclick="editWeight(${data}, 'Y')" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-weight-hanging"></i>
                                            </button>
                                        </div>`;    
                                    }else{
                                        buttons += `
                                        <div class="col-auto">
                                            <button title="Weight Out" type="button" id="edit${data}" onclick="editWeight(${data}, 'N')" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-weight-hanging"></i>
                                            </button>
                                        </div>`;  
                                    }
                                }
                            }
                        }

                        if (row.weight_type != 'Primer Mover + Container'){
                            if (isSADMIN || (permissions['Weighing'] && permissions['Weighing'][transactionKey] && permissions['Weighing'][transactionKey].includes('edit'))) {
                                if (row.transaction_status != 'Purchase' && row.transaction_status != 'Local'){
                                    buttons += `
                                    <div class="col-auto">
                                        <button title="Fill in Customer Side Info" type="button" id="customerSideInfo${data}" onclick="openCustomerSideInfo(${data})" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-clipboard-list"></i>
                                        </button>
                                    </div>`;
                                }
                            }

                            if (isSADMIN || (permissions['Weighing'] && permissions['Weighing'][transactionKey] && permissions['Weighing'][transactionKey].includes('print'))) {
                                buttons += `
                                <div class="col-auto">
                                    <button title="Print" type="button" id="print${data}" onclick="printWeight('${data}', '${row.transaction_status}')" class="btn btn-info btn-sm">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>`;
                            }
                        }

                        if (isSADMIN || (permissions['Weighing'] && permissions['Weighing'][transactionKey] && permissions['Weighing'][transactionKey].includes('cancelled'))) {
                            if (row.weight_type == 'Primer Mover + Container'){
                                buttons += `
                                <div class="col-auto">
                                    <button title="Delete" type="button" id="delete${data}" onclick="deactivate(${data}, 'Y')" class="btn btn-danger btn-sm">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>`;
                            }else{
                                buttons += `
                                <div class="col-auto">
                                    <button title="Delete" type="button" id="delete${data}" onclick="deactivate(${data}, 'N')" class="btn btn-danger btn-sm">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>`;
                            }
                        }
                            
                        buttons += `</div>`;
                        return buttons;
                    }
                }
            ],
            "drawCallback": function(settings) {
                $('#salesInfo').text(settings.json.salesTotal);
                $('#purchaseInfo').text(settings.json.purchaseTotal);
                $('#localInfo').text(settings.json.localTotal);
                $('#miscInfo').text(settings.json.miscTotal);
            }   
        });

        emptyContainerTable = $("#emptyContainerTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            'processing': true,
            'serverSide': true,
            'searching': true,
            'serverMethod': 'post',
            'ajax': {
                'url':'php/modules/weighing/index.php',
                'data': function(d) {
                    d.action = 'filterEmptyContainer';
                    d.fromDate = fromDateI;
                    d.toDate = toDateI;
                    d.plant = plantNoI;
                    d.company = companyI;
                    return d;
                },
            },
            'columns': [
                {
                    data: 'id',
                    className: 'select-checkbox',
                    orderable: false,
                    render: function (data, type, row) {
                        return '<input type="checkbox" class="select-checkbox" id="checkbox_' + data + '" value="'+data+'"/>';
                    }
                },
                { data: 'company_name' },
                { data: 'container_no' },
                { data: 'seal_no' },
                { data: 'transaction_status' },
                { data: 'lorry_plate_no1' },
                { data: 'gross_weight1' },
                { data: 'gross_weight1_date' },
                { data: 'tare_weight1' },
                { data: 'tare_weight1_date' },
                { data: 'nett_weight1' },
                { 
                    data: 'id',
                    class: 'action-button',
                    render: function (data, type, row) {
                        var transactionKey = row.transaction_status;
                        if (transactionKey == 'Transfer To Port'){
                            transactionKey = 'Port';
                        }
                        var buttons = `<div class="row g-1 d-flex">`;

                        if (isSADMIN || (permissions['Weighing'] && permissions['Weighing'][transactionKey] && permissions['Weighing'][transactionKey].includes('edit'))) {
                            if (row.is_complete != 'Y' ){
                                buttons += `
                                <div class="col-auto">
                                    <button title="Edit" type="button" id="edit${data}" onclick="editWeight(${data}, 'Y')" class="btn btn-warning btn-sm">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                </div>`;
                            }
                        }else {
                            if (row.is_complete != 'Y' ){
                                buttons += `
                                <div class="col-auto">
                                    <button title="Weight Out" type="button" id="edit${data}" onclick="editWeight(${data},'Y')" class="btn btn-warning btn-sm">
                                        <i class="fa-solid fa-weight-hanging"></i>
                                    </button>
                                </div>`;
                            }
                        }

                        if (isSADMIN || (permissions['Weighing'] && permissions['Weighing'][transactionKey] && permissions['Weighing'][transactionKey].includes('print'))) {
                            buttons += `
                            <div class="col-auto">
                                <button title="Print" type="button" id="print${data}" onclick="printWeight('${data}', '${row.transaction_status}', 'Y')" class="btn btn-info btn-sm">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>`;
                        }

                        if (isSADMIN || (permissions['Weighing'] && permissions['Weighing'][transactionKey] && permissions['Weighing'][transactionKey].includes('cancelled'))) {
                            buttons += `
                            <div class="col-auto">
                                <button title="Delete" type="button" id="delete${data}" onclick="deactivate(${data}, 'Y')" class="btn btn-danger btn-sm">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>`;
                        }
                            
                        buttons += `</div>`;
                        return buttons;
                    }
                }
            ]
        });
    }

    function format (row) {
        var transactionStatus = '';
        var weightType = '';
        var hasCustomerSideInfo = row.cust_side_do_no || row.cust_side_first_weight || row.cust_side_second_weight || row.cust_side_mc || row.cust_side_nett_weight || row.weight_difference;

        if (row.transaction_status == 'Sales') {
            transactionStatus = '<?=$languageArray['dispatch_code'][$language]?>';
        } else if (row.transaction_status == 'Purchase') {
            transactionStatus = '<?=$languageArray['receiving_code'][$language]?>';
        } else if (row.transaction_status == 'Local') {
            transactionStatus = '<?=$languageArray['internal_transfer_code'][$language]?>';
        } else if (row.transaction_status == 'Port') {
            transactionStatus = '<?=$languageArray['trx_to_port_code'][$language]?>';
        } else {
            transactionStatus = '<?=$languageArray['miscellaneous_code'][$language]?>';
        }

        if (row.weight_type == 'Container') {
            weightType = '<?=$languageArray['primer_mover_code'][$language]?>';
        } else if (row.weight_type == 'Empty Container') {
            weightType = '<?=$languageArray['primer_mover_container_code'][$language]?>';
        } else if (row.weight_type == 'Normal') {
            weightType = '<?=$languageArray['normal_weighing_code'][$language]?>';
        } else if (row.weight_type == 'Different Container') {
            weightType = '<?=$languageArray['primer_mover_different_bins_code'][$language]?>';
        } else {
            weightType = row.weight_type;
        }

        var returnString = `
        <!-- Customer Section -->
        <div class="row">
            <div class="col-6">
                <p><span><strong style="font-size:120%; text-decoration: underline;">Customer/Supplier</strong></span><br>
                <p><strong>${row.name}</strong></p>
                <p>${row.address_line_1}</p>
                <p>${row.address_line_2}</p>
                <p>${row.address_line_3}</p>
                <p>TEL: ${row.phone_no} FAX: ${row.fax_no}</p>
            </div>
        </div>
        <hr>
        <!-- Delivery Order Section -->
        <div class="row">
            <p><span><strong style="font-size:120%; text-decoration: underline;">Delivery Order Information</strong></span><br>
            <div class="col-6">
                <p><strong>COMPANY:</strong> ${row.company_name}</p>
                <p><strong>TRANSPORTER NAME:</strong> ${row.transporter}</p>
                <p><strong>DESTINATION NAME:</strong> ${row.destination}</p>
                <p><strong>PLANT NAME:</strong> ${row.plant_name}</p>`;
                if (row.transaction_status == 'Purchase' || row.transaction_status == 'Local'){
                    returnString += `<p><strong>PURCHASE PRODUCT:</strong> ${row.product_rawmat_name}</p>`;
                }else{
                    returnString += `<p><strong>SALES PRODUCT:</strong> ${row.product_rawmat_name}</p>`;
                }
        
            returnString += `
                <p><strong>PURCHASE ORDER:</strong> ${row.purchase_order}</p>
                <p><strong>CONTAINER NO:</strong> ${row.container_no}</p>
                <p><strong>CONTAINER NO 2:</strong> ${row.container_no2}</p>
            </div>
            <div class="col-6">
                <p><strong>TRANSACTION ID:</strong> ${row.transaction_id}</p>
                <p><strong>PROJECT:</strong> ${row.project_code}</p>
                <p><strong>WEIGHT STATUS:</strong> ${transactionStatus}</p>
                <p><strong>WEIGHT TYPE:</strong> ${weightType}</p>
                <p><strong>DELIVERY NO:</strong> ${row.delivery_no}</p>
                <p><strong>SEAL NO:</strong> ${row.seal_no}</p>
                <p><strong>SEAL NO 2:</strong> ${row.seal_no2}</p>
            </div>
        </div>
        <hr>

        ${row.transaction_status == 'Purchase' ? `
        <!-- Customer Side Section -->
        <div class="row">
            <p><span><strong style="font-size:120%; text-decoration: underline;">Customer Side</strong></span><br>
            <div class="col-6">
                <p><strong><?=$languageArray['customer_side_company_code'][$language]?>:</strong> ${row.customer_side_company || ''}</p>
                <p><strong><?=$languageArray['customer_side_removal_pass_no_code'][$language]?>:</strong> ${row.customer_side_removal_pass_no || ''}</p>
                <p><strong><?=$languageArray['customer_side_license_no_code'][$language]?>:</strong> ${row.customer_side_license_no || ''}</p>
                <p><strong><?=$languageArray['customer_side_moisture_content_code'][$language]?>:</strong> ${row.customer_side_moisture_content || ''}</p>
            </div>
            <div class="col-6">
                <p><strong><?=$languageArray['customer_side_officer_name_code'][$language]?>:</strong> ${row.customer_side_officer_name || ''}</p>
                <p><strong><?=$languageArray['customer_side_rainbow_driver_code'][$language]?>:</strong> ${row.customer_side_rainbow_driver || ''}</p>
                <p><strong><?=$languageArray['customer_side_time_in_code'][$language]?>:</strong> ${row.customer_side_time_in || ''}</p>
                <p><strong><?=$languageArray['customer_side_time_out_code'][$language]?>:</strong> ${row.customer_side_time_out || ''}</p>
            </div>
        </div>
        <hr>` : ''}

        <!-- Weighing Section -->
        <div class="row">
            <p><span><strong style="font-size:120%; text-decoration: underline;">Weighing Information</strong></span><br>
            <!-- Normal -->
            <div class="col-6">
                <p><strong>VEHICLE PLATE:</strong> ${row.lorry_plate_no1}</p>
                <p><strong>IN WEIGHT:</strong> ${row.gross_weight1}</p>
                <p><strong>IN DATE / TIME:</strong> ${row.gross_weight1_date}</p>
                <p><strong>IN WEIGH BY:</strong> ${row.gross_weight_by1}</p>
                <p><strong>OUT WEIGHT:</strong> ${row.tare_weight1}</p>
                <p><strong>OUT DATE / TIME:</strong> ${row.tare_weight1_date}</p>
                <p><strong>OUT WEIGH BY:</strong> ${row.tare_weight_by1}</p>
                <p><strong>NETT WEIGHT:</strong> ${row.nett_weight1}</p>
                <p><strong>SUB TOTAL WEIGHT:</strong> ${row.final_weight}</p>
            </div>
            <!-- Container -->
            <div class="col-6">
                <p><strong>VEHICLE PLATE 2:</strong> ${row.lorry_plate_no2}</p>
                <p><strong>IN WEIGHT 2:</strong> ${row.gross_weight2}</p>
                <p><strong>IN DATE / TIME 2:</strong> ${row.gross_weight2_date}</p>
                <p><strong>IN WEIGH BY 2:</strong> ${row.gross_weight_by2}</p>
                <p><strong>OUT WEIGHT 2:</strong> ${row.tare_weight2}</p>
                <p><strong>OUT DATE / TIME 2:</strong> ${row.tare_weight2_date}</p>
                <p><strong>OUT WEIGH BY 2:</strong> ${row.tare_weight_by2}</p>
                <p><strong>NETT WEIGHT 2:</strong> ${row.nett_weight2}</p>            
                </div>
        </div>
        <hr>

        ${hasCustomerSideInfo ? `
        <!-- Customer Side Info Section -->
        <div class="row">
            <p><span><strong style="font-size:120%; text-decoration: underline;">Customer Side Info</strong></span><br>
            <div class="col-6">
                <p><strong><?=$languageArray['customer_side_do_no_code'][$language]?>:</strong> ${row.cust_side_do_no || ''}</p>
                <p><strong><?=$languageArray['first_code'][$language]?> (KG):</strong> ${row.cust_side_first_weight || ''}</p>
                <p><strong><?=$languageArray['second_code'][$language]?> (KG):</strong> ${row.cust_side_second_weight || ''}</p>
            </div>
            <div class="col-6">
                <p><strong><?=$languageArray['customer_side_mc_code'][$language]?>:</strong> ${row.cust_side_mc || ''}</p>
                <p><strong>Customer Side <?=$languageArray['nett_weight_code'][$language]?> (KG):</strong> ${row.cust_side_nett_weight || ''}</p>
                <p><strong><?=$languageArray['weight_difference_code'][$language]?> (KG):</strong> ${row.weight_difference || ''}</p>
            </div>
        </div>` : ''}
        `;
        
        return returnString;
    }

    function displayPreview(data) {
        // Parse the Excel data
        var workbook = XLSX.read(data, { type: 'binary' });

        // Get the first sheet
        var sheetName = workbook.SheetNames[0];
        var sheet = workbook.Sheets[sheetName];

        // Convert the sheet to an array of objects
        var jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 });

        // Get the headers
        var headers = jsonData[0];

        // Ensure we handle cases where there may be less than 15 columns
        while (headers.length < 18) {
            headers.push(''); // Adding empty headers to reach 15 columns
        }

        // Create HTML table headers
        var htmlTable = '<table style="width:100%;"><thead><tr>';
        headers.forEach(function(header) {
            htmlTable += '<th>' + header + '</th>';
        });
        htmlTable += '</tr></thead><tbody>';

        // Iterate over the data and create table rows
        for (var i = 1; i < jsonData.length; i++) {
            htmlTable += '<tr>';
            var rowData = jsonData[i];

            // Ensure we handle cases where there may be less than 15 cells in a row
            while (rowData.length < 18) {
                rowData.push(''); // Adding empty cells to reach 15 columns
            }

            for (var j = 0; j < 18; j++) {
                var cellData = rowData[j];
                var formattedData = cellData;

                // Check if cellData is a valid Excel date serial number and format it to DD/MM/YYYY
                if (typeof cellData === 'number' && cellData > 0) {
                    var excelDate = XLSX.SSF.parse_date_code(cellData);
                    if (excelDate) {
                        formattedData = formatDate2(new Date(excelDate.y, excelDate.m - 1, excelDate.d));
                    }
                }

                htmlTable += '<td><input type="text" id="'+headers[j].replace(/[^a-zA-Z0-9]/g, '')+(i-1)+'" name="'+headers[j].replace(/[^a-zA-Z0-9]/g, '')+'['+(i-1)+']" value="' + (formattedData == null ? '' : formattedData) + '" /></td>';
            }
            htmlTable += '</tr>';
        }

        htmlTable += '</tbody></table>';

        var previewTable = document.getElementById('previewTable');
        previewTable.innerHTML = htmlTable;
    }

    function deactivate(id, isEmptyContainer) {
        if (confirm('Are you sure you want to cancel this weighing record?')) {
            $('#cancelModal').find('#id').val(id);
            $('#cancelModal').find('#isEmptyContainer').val(isEmptyContainer);
            $('#cancelModal').modal('show');

            $('#cancelForm').validate({
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        }
    }
    </script>
</body>
</html>
