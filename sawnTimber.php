<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
require_once "php/requires/lookup.php";
if (!hasModulePermission('Sawn Timber', 'Sawn Timber', ['view'])){
    header('Location: no-permission.php');
    exit;
}

$plantId = $_SESSION['plant'];
$selectedPlantId = $_SESSION['selected_plant_id'] ?? null;

$company = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
$company2 = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
$supplier = $db->query("SELECT * FROM Supplier WHERE status = '0' ORDER BY name ASC");
$supplier2 = $db->query("SELECT * FROM Supplier WHERE status = '0' ORDER BY name ASC");
$species = $db->query("SELECT * FROM Sawn_Timber_Species WHERE status = '0' ORDER BY name ASC");
$species2 = $db->query("SELECT * FROM Sawn_Timber_Species WHERE status = '0' ORDER BY name ASC");
$plant2 = $db->query("SELECT * FROM Plant WHERE status = '0' ORDER BY name ASC");

$plantName = '-';
$plantCode = '-';
if (!hasModulePermission('Accounting', 'Sawn Timber', ['view_all_plants'])){
    $plant = searchPlantById($selectedPlantId, $db);

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
    $plant = $db->query("SELECT * FROM Plant WHERE status = '0'");
}
?>

<head>

    <title><?=$languageArray['sawn_timber_code'][$language]?> | Synctronix - Weighing System</title>
    <?php include 'layouts/title-meta.php'; ?>

    <!-- jsvectormap css -->
    <link href="assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />
    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <!-- Include jQuery library -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include jQuery Validate plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

    <?php include 'layouts/head-css.php'; ?>
    <style>
        .mb-3 {
            margin-bottom: 0.5rem !important;
        }
        #addModal .btn-close {
            filter: brightness(0) invert(1);
        }
        #addModal .select2-container--open {
            z-index: 9999 !important;
        }
        #addModal .modal-content {
            max-height: 90vh;
        }
        #addModal .modal-body {
            overflow-y: auto;
        }
        #addModal .modal-footer {
            position: relative;
            z-index: 1;
        }
        #addModal #detailTable thead th {
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        #addModal #detailTable .tons {
            background-color: #e9ecef;
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
                                                            <label for="companySearch" class="form-label"><?=$languageArray['company_code'][$language]?></label>
                                                            <select class="form-select select2" id="companySearch" name="companySearch" required>
                                                                <?php while($rowCompany=mysqli_fetch_assoc($company)){ ?>
                                                                    <option value="<?=$rowCompany['id'] ?>" <?=$rowCompany['id'] == 1 ? 'selected' : ''?>><?=$rowCompany['name'] ?></option>
                                                                <?php } ?>
                                                            </select>           
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3" id="supplierSearchDisplay">
                                                        <div class="mb-3">
                                                            <label for="supplierSearch" class="form-label"><?=$languageArray['supplier_code'][$language]?></label>
                                                            <select id="supplierSearch" class="form-select select2" >
                                                                <option selected>-</option>
                                                                <?php while($rowSF=mysqli_fetch_assoc($supplier)){ ?>
                                                                    <option value="<?=$rowSF['supplier_code'] ?>"><?=$rowSF['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="plantSearch" class="form-label"><?=$languageArray['plant_code'][$language]?></label>
                                                            <select id="plantSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <?php while($rowPlantF=mysqli_fetch_assoc($plant)){ ?>
                                                                    <option value="<?=$rowPlantF['plant_code'] ?>" <?= ($rowPlantF['plant_code'] == $plantCode) ? 'selected' : '' ?>><?=$rowPlantF['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="transactionIdSearch" class="form-label"><?=$languageArray['transaction_id_code'][$language]?></label>
                                                            <input id="transactionIdSearch" name="transactionIdSearch" class="form-control">
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="lotSearch" class="form-label"><?=$languageArray['lot_code'][$language]?></label>
                                                            <input id="lotSearch" name="lotSearch" class="form-control">
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="speciesSearch" class="form-label"><?=$languageArray['species_code'][$language]?></label>
                                                            <select id="speciesSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <?php while($rowSpecies=mysqli_fetch_assoc($species)){ ?>
                                                                    <option value="<?=$rowSpecies['id'] ?>"><?=$rowSpecies['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-lg-12">
                                                        <div class="text-end">
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
                                <div class="col-xl-3 col-md-6">
                                    <!-- /.modal-dialog -->
                                    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header" style="background-color: #405189;">
                                                    <h5 class="modal-title text-white" id="exampleModalScrollableTitle"><?=$languageArray['sawn_timber_code'][$language]?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    </button>
                                                </div>
                                                <div class="modal-body bg-light p-4">
                                                    <form role="form" id="sawnTimberForm" class="needs-validation" novalidate autocomplete="off">
                                                        <div class="bg-white p-3 rounded shadow-sm mb-3">
                                                            <div class="row g-3">
                                                                <div class="col-md-4">
                                                                    <label class="form-label"><?=$languageArray['company_code'][$language]?> <span class="text-danger">*</span></label>
                                                                    <select class="form-control select2" id="companyId" name="companyId" required>
                                                                        <option value="">-</option>
                                                                        <?php while($rowC=mysqli_fetch_assoc($company2)){ ?>
                                                                            <option value="<?=$rowC['id'] ?>"><?=$rowC['name'] ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label"><?=$languageArray['plant_code'][$language]?> <span class="text-danger">*</span></label>
                                                                    <select class="form-control select2" id="plantId" name="plantId" required>
                                                                        <?php while($rowP=mysqli_fetch_assoc($plant2)){ ?>
                                                                            <option value="<?=$rowP['id'] ?>"><?=$rowP['name'] ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label"><?=$languageArray['transaction_id_code'][$language]?> <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" id="transactionId" name="transactionId" placeholder="Enter transaction ID" required>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label"><?=$languageArray['transaction_date_code'][$language]?> <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" data-provider="flatpickr" id="transactionDate" name="transactionDate" required>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label"><?=$languageArray['supplier_code'][$language]?> <span class="text-danger">*</span></label>
                                                                    <select class="form-control select2" id="supplier" name="supplier" required>
                                                                        <option value="">-</option>
                                                                        <?php while($rowSF=mysqli_fetch_assoc($supplier2)){ ?>
                                                                            <option value="<?=$rowSF['id'] ?>"><?=$rowSF['name'] ?></option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label"><?=$languageArray['lot_code'][$language]?> <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" id="lot" name="lot" placeholder="Enter lot number" required>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label"><?=$languageArray['bundle_code'][$language]?> <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" id="bundle" name="bundle" placeholder="Enter bundle number" required>
                                                                </div>
                                                                <input type="hidden" class="form-control" id="id" name="id">
                                                            </div>
                                                            <div class="row g-3 mt-2">
                                                                <div class="col-md-12">
                                                                    <label class="form-label"><?=$languageArray['remarks_code'][$language]?></label>
                                                                    <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter remarks (optional)"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card border-0 mt-3 mb-5">
                                                            <div class="card-body" style="padding-bottom: 4%;">
                                                                <div class="d-flex justify-content-between align-items-center pb-2 border-bottom">
                                                                    <label class="form-label mb-0 fw-semibold"><?=$languageArray['details_code'][$language]?></label>
                                                                    <button type="button" class="btn btn-success btn-sm" id="addDetail">
                                                                        <i class="ri-add-circle-line align-middle me-1"></i><?=$languageArray['add_new_code'][$language]?>
                                                                    </button>
                                                                </div>
                                                            <table id="detailTable" class="table table-bordered table-striped table-hover align-middle sawn-table mt-2" style="width:100%;">
                                                                <thead>
                                                                    <tr>
                                                                        <th><?=$languageArray['species_code'][$language]?></th>
                                                                        <th><?=$languageArray['thick_code'][$language]?></th>
                                                                        <th><?=$languageArray['width_code'][$language]?></th>
                                                                        <th><?=$languageArray['length_code'][$language]?></th>
                                                                        <th><?=$languageArray['pieces_code'][$language]?></th>
                                                                        <th><?=$languageArray['tons_code'][$language]?></th>
                                                                        <th><?=$languageArray['action_code'][$language]?></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-end gap-2 mt-3">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                                <i class="ri-close-line align-middle me-1"></i><?=$languageArray['close_code'][$language]?>
                                                            </button>
                                                            <button type="button" class="btn btn-primary" id="saveSawnTimber">
                                                                <i class="ri-save-line align-middle me-1"></i><?=$languageArray['submit_code'][$language]?>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->

                                    <div class="modal fade" id="uploadModal">
                                        <div class="modal-dialog modal-xl" style="max-width: 90%;">
                                            <div class="modal-content">
                                                <form role="form" id="uploadForm">
                                                    <div class="modal-header bg-gray-dark color-palette">
                                                        <h4 class="modal-title"><?=$languageArray['upload_excel_code'][$language]?></h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="file" id="fileInput">
                                                        <button type="button" id="previewButton"><?=$languageArray['preview_data_code'][$language]?></button>
                                                        <div id="previewTable" style="overflow: auto;"></div>
                                                    </div>
                                                    <div class="modal-footer justify-content-between bg-gray-dark color-palette">
                                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                        <button type="button" class="btn btn-success" id="submitUpload"><?=$languageArray['submit_code'][$language]?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="errorModal" style="display:none">
                                        <div class="modal-dialog modal-xl" style="max-width: 50%;">
                                            <div class="modal-content">
                                                <div class="modal-header bg-gray-dark color-palette">
                                                    <h4 class="modal-title"><?=$languageArray['error_log_code'][$language]?></h4>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="form-group">
                                                            <ol id="errorList" class="text-danger mt-2" style="padding-left: 20px;"></ol>
                                                        </div>
                                                    </div>
                                                </div>
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
                                                                <h5 class="card-title mb-0 text-white"><?=$languageArray['sawn_timber_code'][$language]?></h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <?php if(hasModulePermission('Sawn Timber', 'Sawn Timber', ['export'])): ?>
                                                                <button type="button" id="exportExcel" class="btn btn-success waves-effect waves-light">
                                                                    <i class="ri-file-excel-line align-middle me-1"></i>
                                                                    <?=$languageArray['export_excel_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('Sawn Timber', 'Sawn Timber', ['download_template'])): ?>
                                                                <a href="php/modules/sawnTimber/exportSawnTimber.php?template=1" download>
                                                                    <button type="button" class="btn btn-info waves-effect waves-light">
                                                                        <i class="mdi mdi-file-import-outline align-middle me-1"></i>
                                                                        <?=$languageArray['download_template_code'][$language]?>
                                                                    </button>
                                                                </a>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('Sawn Timber', 'Sawn Timber', ['upload_excel'])): ?>
                                                                <button type="button" id="uploadExcel" class="btn btn-warning waves-effect waves-light">
                                                                    <i class="ri-file-pdf-line align-middle me-1"></i>
                                                                    <?=$languageArray['upload_excel_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('Sawn Timber', 'Sawn Timber', ['cancelled'])): ?>
                                                                <button type="button" id="multiDeactivate" class="btn btn-danger waves-effect waves-light">
                                                                    <i class="ri-delete-bin-fill align-middle me-1"></i>
                                                                    <?=$languageArray['delete_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('Sawn Timber', 'Sawn Timber', ['create'])): ?>
                                                                <button type="button" id="addSawTimber" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                                                    <i class="ri-add-circle-line align-middle me-1"></i>
                                                                    <?=$languageArray['add_new_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>
                                                            </div> 
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="sawnTimberTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                                    <th><?=$languageArray['transaction_id_code'][$language]?></th>
                                                                    <th><?=$languageArray['transaction_date_code'][$language]?></th>
                                                                    <th><?=$languageArray['company_code'][$language]?></th>
                                                                    <th><?=$languageArray['plant_code'][$language]?></th>
                                                                    <th><?=$languageArray['supplier_code'][$language]?></th>
                                                                    <th><?=$languageArray['lot_code'][$language]?></th>
                                                                    <th><?=$languageArray['total_pcs_code'][$language]?></th>
                                                                    <th><?=$languageArray['total_tons_code'][$language]?></th>
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
            </div>

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
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="assets/js/pages/datatables.init.js"></script>
    <!-- Additional js -->
    <script src="assets/js/additional.js"></script>

    <script type="text/html" id="detailRowTemplate">
        <tr class="detail-row">
            <td style="width: 25%;">
                <select class="form-control species-select" id="species" name="species" required>
                    <?php foreach($species2 as $species) { ?>
                        <option value="<?=$species['id']?>"><?=$species['name']?></option>
                    <?php } ?>
                </select>
            </td>
            <td><input type="number" step="0.0001" class="form-control thick" id="thick" name="thick"></td>
            <td><input type="number" step="0.0001" class="form-control width" id="width" name="width"></td>
            <td><input type="number" step="0.0001" class="form-control length" id="length" name="length"></td>
            <td><input type="number" step="1" class="form-control pieces" id="pieces" name="pieces"></td>
            <td><input type="number" step="0.0001" class="form-control tons" id="tons" name="tons" readonly value="0.0000"></td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-detail">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        </tr>
    </script>

    <script type="text/javascript">
    var userRole = '<?=$_SESSION["roles"] ?>';
    var table = null;
    var permissions = <?= json_encode($_SESSION['permissions']) ?>;
    var isSADMIN = <?= json_encode($_SESSION['roles'] == 'SADMIN') ?>;
    var detailRowCount = 0;

    $(function () {
        const today = new Date();
        const tomorrow = new Date(today);
        const yesterday = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        yesterday.setDate(yesterday.getDate() - 1);

        //Date picker
        $('#fromDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            enableTime: true,
            time_24hr: true,
            defaultDate: yesterday
        });

        $('#toDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            dateFormat: "d-m-Y",
            enableTime: true,
            time_24hr: true,
            defaultDate: today
        });

        $('#transactionDate').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: today
        });

        $('.select2').each(function() {
            $(this).select2({
                allowClear: true,
                placeholder: "Please Select",
                // Conditionally set dropdownParent based on the element’s location
                dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal-body') : undefined
            });
        });

        // Apply custom styling to Select2 elements in addModal
        $('.select2-container .select2-selection--single').css({
            'padding-top': '4px',
            'padding-bottom': '4px',
            'height': 'auto'
        });

        $('.select2-container .select2-selection__arrow').css({
            'padding-top': '33px',
            'height': 'auto'
        });

        $('#selectAllCheckbox').on('change', function() {
            var checkboxes = $('#sawnTimberTable tbody input[type="checkbox"]');
            checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
        });

        $('#multiDeactivate').on('click', function() {
            var selectedIds = [];
            $('#sawnTimberTable tbody input[type="checkbox"]:checked').each(function() {
                selectedIds.push($(this).val());
            });
            if (selectedIds.length === 0) {
                toastr.warning('Please select at least one record');
                return;
            }
            if (confirm('Are you sure you want to deactivate ' + selectedIds.length + ' record(s)?')) {
                $.post('php/modules/sawnTimber/deleteSawnTimber.php', {
                    userID: selectedIds,
                    type: 'MULTI'
                }, function(data) {
                    var obj = JSON.parse(data);
                    if (obj.status === 'success') {
                        toastr.success(obj.message);
                        table.ajax.reload();
                        $('#selectAllCheckbox').prop('checked', false);
                    } else {
                        toastr.error(obj.message);
                    }
                });
            }
        });

        // Initial load
        renderTable();

        // Filter search
        $('#filterSearch').on('click', function () {
            renderTable();
        });

        // Detail table events
        $('#addDetail').on('click', function() { 
            addDetailRow(); 
        });
        $('#detailTable').on('click', '.remove-detail', function() { 
            $(this).closest('tr').remove(); 
        });
        $('#detailTable').on('input', '.thick,.width,.length,.pieces', function() { 
            calculateTons($(this).closest('tr')); 
        });

        // Reset form and detailTable when Add button is clicked
        $('#addSawTimber').on('click', function() {
            $('#sawnTimberForm')[0].reset();
            $('#companyId').val(1).trigger('change');
            $('#plantId').val('').trigger('change');
            $('#supplier').val('').trigger('change');
            $('#detailTable tbody').html('');
            $('#id').val('');
            detailRowCount = 0;
        });

        // Add event listener for opening and closing details on row click
        $('#sawnTimberTable tbody').on('click', 'tr', function (e) {
            var tr = $(this); // The row that was clicked
            var row = table.row(tr);
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();

            // Exclude specific td elements by checking the event target
            if ($(e.target).closest('td').hasClass('select-checkbox') || $(e.target).closest('td').hasClass('action-button')) {
                return;
            }

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                $.post('php/getWeight.php', { userID: row.data().id, fromDate: fromDateI, toDate: toDateI, format: 'EXPANDABLE', acctType: 'GR' }, function (data) {
                    var obj = JSON.parse(data);
                    if (obj.status === 'success') {
                        row.child(format(obj.message)).show();
                        tr.addClass("shown");
                    }
                });
            }
        });

        // Export Excel
        $('#exportExcel').on('click', function () {
            var params = $.param({
                fromDate: $('#fromDateSearch').val(),
                toDate: $('#toDateSearch').val(),
                company: $('#companySearch').val(),
                plant: $('#plantSearch').val(),
                supplier: $('#supplierSearch').val(),
                transactionId: $('#transactionIdSearch').val(),
                lot: $('#lotSearch').val(),
                species: $('#speciesSearch').val()
            });
            window.location = 'php/modules/sawnTimber/exportSawnTimber.php?' + params;
        });

        $('#saveSawnTimber').on('click', function() {
            $('#detailTable tbody tr').each(function() { calculateTons($(this)); });
            $.post('php/modules/sawnTimber/sawnTimber.php', $('#sawnTimberForm').serialize(), function(data) {
                var obj = JSON.parse(data);
                if (obj.status === 'success') {
                    $('#addModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(obj.message);
                } else {
                    toastr.error(obj.message);
                }
            });
        });

        $('#uploadExcel').on('click', function(){
            $('#previewTable').html('');
            $('#fileInput').val('');
            $('#uploadModal').modal('show');

            $('#uploadForm').validate({
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
        });

        $('#uploadModal').find('#previewButton').on('click', function(){
            var fileInput = document.getElementById('fileInput');
            var file = fileInput.files[0];
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var data = e.target.result;
                displayPreview(data);
            };

            reader.readAsBinaryString(file);
        });

        $('#submitUpload').on('click', function(){
            $('#spinnerLoading').show();
            var formData = $('#uploadForm').serializeArray();
            var data = [];
            var rowIndex = -1;
            formData.forEach(function(field) {
                var match = field.name.match(/([a-zA-Z0-9]+)\[(\d+)\]/);
                if (match) {
                    var fieldName = match[1];
                    var index = parseInt(match[2], 10);
                    if (index !== rowIndex) {
                        rowIndex = index;
                        data.push({});
                    }
                    data[index][fieldName] = field.value;
                }
            });

            $.ajax({
                url: 'php/modules/sawnTimber/uploadSawnTimber.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    var obj = JSON.parse(response);
                    if (obj.status === 'success') {
                        $('#spinnerLoading').hide();
                        $('#uploadModal').modal('hide');
                        toastr["success"](obj.message, "Success:");
                        table.ajax.reload(null, false);
                    } 
                    else if (obj.status === 'failed') {
                        $('#spinnerLoading').hide();
                        toastr["error"](obj.message, "Failed:");
                    } 
                    else if (obj.status === 'error') {
                        $('#spinnerLoading').hide();
                        $('#uploadModal').modal('hide');
                        table.ajax.reload(null, false);
                        $('#errorModal').find('#errorList').empty();
                        var errorMessage = obj.message;
                        for (var i = 0; i < errorMessage.length; i++) {
                            $('#errorModal').find('#errorList').append(`<li>${errorMessage[i]}</li>`);                            
                        }
                        $('#errorModal').modal('show');
                    } 
                    else {
                        $('#spinnerLoading').hide();
                        toastr["error"]("Failed to save", "Failed:");
                    }
                }
            });
        });
    });

    function renderTable() {
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();
        var companyI = $('#companySearch').val() || '';
        var supplierNoI = $('#supplierSearch').val() || '';
        var plantI = $('#plantSearch').val() || '';
        var transactionIdI = $('#transactionIdSearch').val() || '';
        var lotI = $('#lotSearch').val() || '';
        var speciesI = $('#speciesSearch').val() || '';

        // Destroy the old Datatable if exists
        if ($.fn.DataTable.isDataTable('#sawnTimberTable')) {
            $("#sawnTimberTable").DataTable().clear().destroy();
        }

        // Create new Datatable
        table = $("#sawnTimberTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            'processing': true,
            'serverSide': true,
            'searching': false,
            'serverMethod': 'post',
            'ajax': {
                'url': 'php/modules/sawnTimber/loadSawnTimber.php',
                'data': {
                    fromDate: fromDateI,
                    toDate: toDateI,
                    company: companyI,
                    supplier: supplierNoI,
                    plant: plantI,
                    transactionId: transactionIdI,
                    lot: lotI,
                    species: speciesI,
                }
            },
            'columns': [
                {
                    data: 'id',
                    className: 'select-checkbox',
                    orderable: false,
                    render: function (data, type, row) {
                        return '<input type="checkbox" class="select-checkbox" id="checkbox_' + data + '" value="' + data + '"/>';
                    }
                },
                { data: 'transaction_id' },
                { data: 'transaction_date' },
                { data: 'company' },
                { data: 'plant' },
                { data: 'supplier' },
                { data: 'lot' },
                { data: 'total_pieces' },
                { data: 'total_tons' },
                { data: 'id', orderable: false, className: 'sawn-action-cell', render: function(data) {
                    return '<button class="btn btn-sm btn-warning me-1" onclick="editRecord(\'' + data + '\')"><i class="ri-edit-line"></i></button>' +
                        '<button class="btn btn-sm btn-danger" onclick="deleteRecord(\'' + data + '\')"><i class="ri-delete-bin-line"></i></button>';
                }}
            ]
        });
    }

    function format(row) {
        var returnString = `
        <!-- Weighing Section -->
        <div class="row">
            <p><span><strong style="font-size:120%; text-decoration: underline;"><?=$languageArray['goods_received_information_code'][$language]?></strong></span><br>
            <div class="col-4">
                <p><strong class="text-uppercase"><?=$languageArray['total_received_amount_code'][$language]?>:</strong> ${parseFloat(row.total_final_weight)/1000} MT</p>
            </div>`;

            if (isSADMIN) {
                returnString += `
                    <div class="col-4">
                        <p><strong class="text-uppercase"><?=$languageArray['unit_price_code'][$language]?>:</strong> RM ${row.weights[0].unit_price}</p>
                    </div>
                    <div class="col-4">
                        <p><strong class="text-uppercase"><?=$languageArray['total_price_code'][$language]?>:</strong> RM ${parseFloat(parseFloat(row.weights[0].unit_price) * (parseFloat(row.total_final_weight)/1000)).toFixed(2)}</p>
                    </div>
                `;
            }

            returnString += `
        </div>
        <hr>
        <div class="row">
            <table class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th><?=$languageArray['transaction_id_code'][$language]?></th>
                        <th><?=$languageArray['po_no_code'][$language]?></th>
                        <th><?=$languageArray['vehicle_no_code'][$language]?></th>
                        <th><?=$languageArray['transporter_code'][$language]?></th>
                        <th><?=$languageArray['destination_code'][$language]?></th>
                        <th><?=$languageArray['gross_incoming_code'][$language]?></th>
                        <th><?=$languageArray['incoming_date_code'][$language]?></th>
                        <th><?=$languageArray['tare_outgoing_code'][$language]?></th>
                        <th><?=$languageArray['outgoing_date_code'][$language]?></th>
                        <th><?=$languageArray['nett_weight_code'][$language]?></th>`;
                        // if (isSADMIN) {
                        //     returnString += `<th>Action</th>`;
                        // }

                        returnString += `</tr>
                </thead>
                <tbody>`;

                for (var i = 0; i < row.weights.length; i++) {
                    var weights = row.weights; 
                    
                    returnString += `
                        <tr>
                            <td>${weights[i].transaction_id}</td>
                            <td>${weights[i].delivery_no}</td>
                            <td>${weights[i].lorry_plate_no1}</td>
                            <td>${weights[i].transporter}</td>
                            <td>${weights[i].destination}</td>
                            <td>${parseFloat(weights[i].gross_weight1)/1000} MT</td>
                            <td>${weights[i].gross_weight1_date}</td>
                            <td>${parseFloat(weights[i].tare_weight1)/1000} MT</td>
                            <td>${weights[i].tare_weight1_date}</td>
                            <td>${parseFloat(weights[i].nett_weight1)/1000} MT</td>`
                            // if (isSADMIN) {
                            //     returnString += `
                            //     <td>
                            //         <button title="Edit" type="button" id="edit${weights[i].id}" onclick="edit(${weights[i].id})" class="btn btn-warning btn-sm">
                            //             <i class="fas fa-pen"></i>
                            //         </button>
                            //     </td>`;
                            // }
                        returnString += `</tr>`;
                }

                returnString += `</tbody>
            </table>
        </div>
        `;
        
        return returnString;
    }

    function addDetailRow(detail) {
        detail = detail || {};
        var $addContents = $("#detailRowTemplate").clone();
        $("#detailTable tbody").append($addContents.html());

        var $row = $("#detailTable tbody").find('.detail-row:last');
        $row.attr("data-index", detailRowCount);

        $row.find('#species').attr('name', 'species['+detailRowCount+']').attr('id', 'species'+detailRowCount);
        $row.find('#thick').attr('name', 'thick['+detailRowCount+']').attr('id', 'thick'+detailRowCount).val(detail.thick || '');
        $row.find('#width').attr('name', 'width['+detailRowCount+']').attr('id', 'width'+detailRowCount).val(detail.width || '');
        $row.find('#length').attr('name', 'length['+detailRowCount+']').attr('id', 'length'+detailRowCount).val(detail.length || '');
        $row.find('#pieces').attr('name', 'pieces['+detailRowCount+']').attr('id', 'pieces'+detailRowCount).val(detail.pieces || '');
        $row.find('#tons').attr('name', 'tons['+detailRowCount+']').attr('id', 'tons'+detailRowCount).val(detail.tons || '0.0000');

        $row.find('#species'+detailRowCount).val(detail.species || '').select2({
            dropdownParent: $row.find('#species'+detailRowCount).closest('td'),
            placeholder: 'Please Select',
            allowClear: true,
            width: '100%'
        });

        // Apply custom styling to Select2 elements
        $('#detailTable .select2-container .select2-selection--single').css({
            'padding-top': '4px',
            'padding-bottom': '4px',
            'height': 'auto'
        });
        $('#detailTable .select2-container .select2-selection__arrow').css({
            'padding-top': '33px',
            'height': 'auto'
        });

        detailRowCount++;
    }

    function deleteRecord(id) {
        if (!confirm('Are you sure you want to delete this record?')) {
            return;
        }

        $.post('php/modules/sawnTimber/deleteSawnTimber.php', {userID: id}, function(data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                table.ajax.reload();
                toastr.success(obj.message);
            } else {
                toastr.error(obj.message);
            }
        });
    }

    function calculateTons(row) {
        var thick = parseFloat(row.find('.thick').val()) || 0;
        var width = parseFloat(row.find('.width').val()) || 0;
        var length = parseFloat(row.find('.length').val()) || 0;
        var pieces = parseFloat(row.find('.pieces').val()) || 0;
        row.find('.tons').val(((thick * width * length * pieces) / 7200).toFixed(4));
    }

    function editRecord(id) {
        $('#spinnerLoading').show();
        $.post('php/modules/sawnTimber/getSawnTimber.php', {id: id}, function(data) {
            $('#spinnerLoading').hide();
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                var record = obj.message.header;
                record.details = obj.message.details;
                openEntry(record);
            } else {
                toastr.error(obj.message);
            }
        });
    }

    function openEntry(record) {
        record = record || {};
        $('#sawnTimberForm')[0].reset();
        $('#detailTable tbody').html('');
        detailRowCount = 0;

        $('#id').val(record.id || '');
        $('#companyId').val(record.company_id || '').trigger('change');
        $('#plantId').val(record.plant_id || '').trigger('change');
        $('#transactionId').val(record.transaction_id || '');
        $('#transactionDate').val(record.transaction_date ? record.transaction_date.split(' ')[0].split('-').reverse().join('-') : '');
        $('#supplier').val(record.supplier || '').trigger('change');
        $('#lot').val(record.lot || '');
        $('#bundle').val(record.bundle || '');
        $('#remarks').val(record.remarks || '');

        if (record.details && record.details.length > 0) {
            for (var i = 0; i < record.details.length; i++) {
                addDetailRow({
                    species: record.details[i].species,
                    thick: record.details[i].thick,
                    width: record.details[i].width,
                    length: record.details[i].length,
                    pieces: record.details[i].pieces,
                    tons: record.details[i].tons
                });
            }
        }

        $('#addModal').modal('show');
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

        // Ensure we handle cases where there may be less than 14 columns
        while (headers.length < 14) {
            headers.push('');
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

            // Ensure we handle cases where there may be less than 14 cells in a row
            while (rowData.length < 14) {
                rowData.push('');
            }

            for (var j = 0; j < 14; j++) {
                var cellData = rowData[j];
                var formattedData = cellData;

                // Check if cellData is a valid Excel date serial number and format it to DD-MM-YYYY (column 3 = TransactionDate)
                if (j === 3 && typeof cellData === 'number' && cellData > 0) {
                    var excelDate = XLSX.SSF.parse_date_code(cellData);
                    if (excelDate) {
                        formattedData = ('0' + excelDate.d).slice(-2) + '-' + ('0' + excelDate.m).slice(-2) + '-' + excelDate.y;
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
    </script>
</body>
</html>