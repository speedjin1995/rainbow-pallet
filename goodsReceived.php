<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
require_once "php/requires/lookup.php";
if (!hasModulePermission('Accounting', 'Goods Received', ['view'])){
    header('Location: no-permission.php');
    exit;
}

$plantId = $_SESSION['plant_id'];
$selectedPlantId = $_SESSION['selected_plant_id'] ?? null;

$companyId = $_SESSION['company_id'];
if (!hasModulePermission('Accounting', 'Goods Received', ['view_all_companies'])){
    // Get companies
    $company_ids = implode(',', array_map('intval', $_SESSION['company_ids']));
    $company = $db->query("SELECT * FROM Company WHERE status = 0 AND id IN ($company_ids) ORDER BY name");
    // Only the selected company's suppliers and raw materials
    $selectedCompanyId = intval($companyId);
    $supplier = $db->query("SELECT * FROM Supplier WHERE status = '0' AND company IN ($selectedCompanyId) ORDER BY name ASC");
    $supplier2 = $db->query("SELECT * FROM Supplier WHERE status = '0' AND company IN ($selectedCompanyId) ORDER BY name ASC");
    $rawMaterial = $db->query("SELECT p.*, IFNULL(c.is_sales, 'Y') as is_sales, IFNULL(c.is_purchase, 'Y') as is_purchase, IFNULL(c.is_local, 'Y') as is_local, IFNULL(c.is_port, 'Y') as is_port, IFNULL(c.is_misc, 'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = '0' AND p.company IN ($selectedCompanyId) ORDER BY p.name ASC");
    $rawMaterial2 = $db->query("SELECT p.*, IFNULL(c.is_sales, 'Y') as is_sales, IFNULL(c.is_purchase, 'Y') as is_purchase, IFNULL(c.is_local, 'Y') as is_local, IFNULL(c.is_port, 'Y') as is_port, IFNULL(c.is_misc, 'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = '0' AND p.company IN ($selectedCompanyId) ORDER BY p.name ASC");
}else{
    $company = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
    $supplier = $db->query("SELECT * FROM Supplier WHERE status = '0' ORDER BY name ASC");
    $supplier2 = $db->query("SELECT * FROM Supplier WHERE status = '0' ORDER BY name ASC");
    $rawMaterial = $db->query("SELECT p.*, IFNULL(c.is_sales, 'Y') as is_sales, IFNULL(c.is_purchase, 'Y') as is_purchase, IFNULL(c.is_local, 'Y') as is_local, IFNULL(c.is_port, 'Y') as is_port, IFNULL(c.is_misc, 'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = '0' ORDER BY p.name ASC");
    $rawMaterial2 = $db->query("SELECT p.*, IFNULL(c.is_sales, 'Y') as is_sales, IFNULL(c.is_purchase, 'Y') as is_purchase, IFNULL(c.is_local, 'Y') as is_local, IFNULL(c.is_port, 'Y') as is_port, IFNULL(c.is_misc, 'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = '0' ORDER BY p.name ASC");
}

$plantName = '-';
$plantCode = '-';
if (!hasModulePermission('Accounting', 'Goods Received', ['view_all_plants'])){
    if (!empty($selectedPlantId)){
        // Locked to the plant selected at login - backend restricts "-" to this plant only
        $plant = searchPlantById($selectedPlantId, $db);
    }else{
        // No plant selected - list every plant the user is tied to
        $plant = searchPlantsByIds($plantId ?? [], $db);
    }

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

// Weighing modal component - used to edit / print the GR's weighings, which are Purchase
$canEditWeight = hasModulePermission('Weighing', 'Purchase', ['edit']);
$canPrintWeight = hasModulePermission('Weighing', 'Purchase', ['print']);
if ($canEditWeight || $canPrintWeight) {
    require_once "components/weighingModal/data.php";
}

$canUpdatePrice = hasModulePermission('Accounting', 'Goods Received', ['update_price']);
$canIncludePrice = hasModulePermission('Accounting', 'Goods Received', ['include_price']);
?>

<head>

    <title><?=$languageArray['goods_received_code'][$language]?> | Synctronix - Weighing System</title>
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

        .modal-header {
            padding: var(1rem, 1rem) !important;
        }

        .number-spinner::-webkit-inner-spin-button,
        .number-spinner::-webkit-outer-spin-button {
            -webkit-appearance: auto;
            opacity: 1;
        }

        .number-spinner {
            -moz-appearance: number-input;
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
                                                    <div class="col-3" <?= !hasModulePermission('Accounting', 'Goods Received', ['view_all_companies']) ? "style='display:none'" : '' ?>>
                                                        <div class="mb-3">
                                                            <label for="companySearch" class="form-label"><?=$languageArray['company_code'][$language]?></label>
                                                            <select class="form-select select2" id="companySearch" name="companySearch" required>
                                                                <?php while($rowCompany=mysqli_fetch_assoc($company)){ ?>
                                                                    <option value="<?=$rowCompany['id'] ?>" <?=($rowCompany['id'] == $companyId) ? 'selected' : ''?>><?=$rowCompany['name'] ?></option>
                                                                <?php } ?>
                                                            </select>           
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="paymentTermSearch" class="form-label"><?=$languageArray['payment_term_code'][$language]?></label>
                                                            <select id="paymentTermSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <option value="Cash"><?=$languageArray['cash_code'][$language]?></option>
                                                                <option value="Term"><?=$languageArray['term_code'][$language]?></option>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3" id="paymentTermPeriodSearchDisplay" style="display:none">
                                                        <div class="mb-3">
                                                            <label for="paymentTermPeriodSearch" class="form-label"><?=$languageArray['payment_term_period_code'][$language]?></label>
                                                            <input type="number" class="form-control number-spinner" id="paymentTermPeriodSearch" name="paymentTermPeriodSearch" min="0" step="1" placeholder="<?=$languageArray['payment_term_period_code'][$language]?>">
                                                        </div>
                                                    </div><!--end col-->

                                                    <div class="col-3" id="supplierSearchDisplay">
                                                        <div class="mb-3">
                                                            <label for="supplierSearch" class="form-label"><?=$languageArray['supplier_code'][$language]?></label>
                                                            <select id="supplierSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <?php while($rowSF=mysqli_fetch_assoc($supplier2)){ ?>
                                                                    <option value="<?=$rowSF['supplier_code'] ?>" data-payment-term="<?=$rowSF['payment_term'] ?>"><?=$rowSF['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3" id="rawMatSearchDisplay">
                                                        <div class="mb-3">
                                                            <label for="ForminputState" class="form-label"><?=$languageArray['raw_material_code'][$language]?></label>
                                                            <select id="rawMatSearch" class="form-select select2">
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
                                                            <label for="poSearch" class="form-label"><?=$languageArray['po_no_code'][$language]?></label>
                                                            <input id="poSearch" name="poSearch" class="form-control">
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="transactionIdSearch" class="form-label"><?=$languageArray['transaction_id_code'][$language]?></label>
                                                            <input id="transactionIdSearch" name="transactionIdSearch" class="form-control">
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
                                <div class="col">
                                    <div class="h-100">
                                        <!--datatable--> 
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card">
                                                    <div class="card-header" style="background-color: #405189;">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <h5 class="card-title mb-0 text-white"><?=$languageArray['goods_received_records_code'][$language]?></h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <?php if(hasModulePermission('Accounting', 'Goods Received', ['export'])): ?>
                                                                <button type="button" id="exportExcel" class="btn btn-success waves-effect waves-light">
                                                                    <i class="ri-file-excel-line align-middle me-1"></i>
                                                                    <?=$languageArray['export_excel_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>
                                                                <?php if(hasModulePermission('Accounting', 'Goods Received', ['post_to_sql'])): ?>
                                                                <button type="button" id="postSQL" class="btn btn-warning waves-effect waves-light">
                                                                    <i class="ri-send-plane-line align-middle me-1"></i>
                                                                    <?=$languageArray['post_to_sql_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>
                                                            </div> 
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="weightTable" class="table table-expandable table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                                    <th><?=$languageArray['company_code'][$language]?></th>
                                                                    <th><?=$languageArray['supplier_code'][$language]?></th>
                                                                    <th><?=$languageArray['plant_code'][$language]?></th>
                                                                    <th><?=$languageArray['raw_material_code'][$language]?></th>
                                                                    <th><?=$languageArray['received_date_code'][$language]?></th>
                                                                    <th><?=$languageArray['total_received_amount_code'][$language]?> (KG)</th>
                                                                    <th><?=$languageArray['issues_code'][$language] ?? 'Issues'?></th>
                                                                    <th><?=$languageArray['action_code'][$language]?><?=actionPermissionNote([hasModulePermission('Accounting', 'Goods Received', ['post_to_sql'])])?></th>
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

                            <!-- Post to SQL: pick the weighings of a GR group -->
                            <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="postModalTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable custom-xxl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="postModalTitle"></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form role="form" id="grForm" class="needs-validation" novalidate autocomplete="off">
                                            </form>
                                        </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->

                            <?php if ($canEditWeight || $canPrintWeight): ?>
                            <?php include 'components/weighingModal/modal.php'; ?>
                            <?php endif; ?>
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

    <script type="text/javascript">
    var userRole = '<?=$_SESSION["roles"] ?>';
    var table = null;
    var permissions = <?= json_encode($_SESSION['permissions']) ?>;
    var isSADMIN = <?= json_encode($_SESSION['roles'] == 'SADMIN') ?>;
    var allRawMatSearchOptions = null;
    var canEditWeight = <?= $canEditWeight ? 'true' : 'false' ?>;
    var canPrintWeight = <?= $canPrintWeight ? 'true' : 'false' ?>;
    var allSupplierSearchOptions = null;
    var canUpdatePrice = <?= $canUpdatePrice ? 'true' : 'false' ?>;
    var canIncludePrice = <?= $canIncludePrice ? 'true' : 'false' ?>;
    var canPostToSql = <?= hasModulePermission('Accounting', 'Goods Received', ['post_to_sql']) ? 'true' : 'false' ?>;
    var issueLabels = {
        supplier: <?= json_encode($languageArray['supplier_code_not_match_code'][$language] ?? 'Supplier code not match') ?>,
        item: <?= json_encode($languageArray['item_code_not_match_code'][$language] ?? 'Item code not match') ?>,
        price: <?= json_encode($languageArray['need_unit_price_code'][$language] ?? 'Need unit price and total price') ?>
    };
    var expandedWeights = {}; // groupId -> last fetched weighing detail (weights, total_final_weight)
    var updatePriceModes = {}; // groupId -> true while that row's table is in Update Price edit mode

    $(function () {
        // Weighing modal: refresh the GR list after a weighing is saved
        if (window.initWeighingModal) {
            initWeighingModal({
                onSaved: function(obj, withPrint){
                    table.ajax.reload(null, false);
                }
            });
        }

        const today = new Date();
        const tomorrow = new Date(today);
        const yesterday = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        yesterday.setDate(yesterday.getDate() - 1);

        //Date picker
        $('#fromDateSearch').flatpickr({
            dateFormat: "d-m-Y H:i:S",
            enableTime: true,
            time_24hr: true,
            defaultDate: yesterday
        });

        $('#toDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            dateFormat: "d-m-Y H:i:S",
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
            var checkboxes = $('#weightTable tbody input[type="checkbox"]');
            checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
        });

        // Initial load
        renderTable();

        // Filter search
        $('#filterSearch').on('click', function () {
            renderTable();
        });

        $('#paymentTermSearch').on('change', function () {
            filterSupplierDropdown();
            togglePaymentTermPeriodSearch();
        });

        // Add event listener for opening and closing details on row click
        $('#weightTable tbody').on('click', 'tr', function (e) {
            var tr = $(this); // The row that was clicked
            var row = table.row(tr);
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();

            // Exclude specific td elements by checking the event target, and rows of the nested weighing table
            if ($(e.target).closest('td').hasClass('select-checkbox') || $(e.target).closest('td').hasClass('action-button') || !row.data()) {
                return;
            }

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                cleanupDetailTables();
            } else {
                var groupId = row.data().id;
                $.post('php/modules/weighing/index.php', { action: 'getWeight', userID: groupId, fromDate: fromDateI, toDate: toDateI, format: 'EXPANDABLE', acctType: 'GR' }, function (data) {
                    var obj = JSON.parse(data);
                    if (obj.status === 'success') {
                        expandedWeights[groupId] = obj.message;
                        updatePriceModes[groupId] = false; // expanding always starts in view mode
                        tr.addClass("shown");
                        showWeighingDetails(row, groupId, false);
                    }
                });
            }
        });

        // Post to SQL Handling
        $('#postSQL').on('click', function () {
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();
            var companyI = $('#companySearch').val() || '';
            var paymentTermI = $('#paymentTermSearch').val() || '';
            var paymentTermPeriodI = $('#paymentTermPeriodSearch').val() || '';
            var supplierNoI = $('#supplierSearch').val() || '';
            var rawMatI = $('#rawMatSearch').val() || '';
            var plantI = $('#plantSearch').val() || '';
            var poI = $('#poSearch').val() || '';
            var transactionIdI = $('#transactionIdSearch').val() || '';
            var selectedIds = []; // An array to store the selected 'id' values

            $("#weightTable tbody input[type='checkbox']").each(function () {
                if (this.checked) {
                    selectedIds.push($(this).val());
                }
            });

            if (selectedIds.length > 0) {
                if (confirm('Are you sure you want to post to SQL these items?')) {
                    $('#spinnerLoading').show();
                    $.post('php/modules/goodsReceived/index.php', {
                        action: 'post',
                        fromDate: fromDateI,
                        toDate: toDateI,
                        company: companyI,
                        paymentTerm: paymentTermI,
                        paymentTermPeriod: paymentTermPeriodI,
                        supplier: supplierNoI,
                        rawMaterial: rawMatI,
                        plant: plantI,
                        purchaseOrder: poI,
                        transactionId: transactionIdI,
                        userID: selectedIds, 
                        type: 'MULTI'
                    }, function(data){
                        var obj = JSON.parse(data);
                        
                        if(obj.status === 'success'){
                            toastr["success"](obj.message, "Success:");
                            $('#weightTable').DataTable().ajax.reload(null, false);
                            $('#spinnerLoading').hide();
                        }
                        else if(obj.status === 'failed'){
                            toastr["error"](obj.message, "Failed:");
                            $('#spinnerLoading').hide();
                        }
                        else{
                            toastr["error"]("Something wrong when activate", "Failed:");
                            $('#spinnerLoading').hide();
                        }
                    });
                }
            } 
            else {
                if (confirm('Are you sure you want to post to SQL?')) {
                    $('#spinnerLoading').show();
                    $.post('php/modules/goodsReceived/index.php', {
                        action: 'post',
                        fromDate: fromDateI,
                        toDate: toDateI,
                        company: companyI,
                        paymentTerm: paymentTermI,
                        paymentTermPeriod: paymentTermPeriodI,
                        supplier: supplierNoI,
                        rawMaterial: rawMatI,
                        plant: plantI,
                        purchaseOrder: poI,
                        transactionId: transactionIdI,
                        type: 'ALL'
                    }, function(data){
                        var obj = JSON.parse(data);
                        
                        if(obj.status === 'success'){
                            toastr["success"](obj.message, "Success:");
                            $('#weightTable').DataTable().ajax.reload(null, false);
                            $('#spinnerLoading').hide();
                        }
                        else if(obj.status === 'failed'){
                            toastr["error"](obj.message, "Failed:");
                            $('#spinnerLoading').hide();
                        }
                        else{
                            toastr["error"]("Something wrong when activate", "Failed:");
                            $('#spinnerLoading').hide();
                        }
                    });
                }
            }     
        });

        // Export Excel
        $('#exportExcel').on('click', function () {
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();
            var companyI = $('#companySearch').val() || '';
            var paymentTermI = $('#paymentTermSearch').val() || '';
            var paymentTermPeriodI = $('#paymentTermPeriodSearch').val() || '';
            var supplierNoI = $('#supplierSearch').val() || '';
            var rawMatI = $('#rawMatSearch').val() || '';
            var plantI = $('#plantSearch').val() || '';
            var poI = $('#poSearch').val() || '';
            var transactionIdI = $('#transactionIdSearch').val() || '';
            var selectedIds = []; // An array to store the selected 'id' values

            $("#weightTable tbody input[type='checkbox']").each(function () {
                if (this.checked) {
                    selectedIds.push($(this).val());
                }
            });

            if (selectedIds.length > 0) {
                window.open("php/modules/goodsReceived/index.php?action=export&isMulti=Y&fromDate="+fromDateI+"&toDate="+toDateI+"&company="+companyI+"&supplier="+supplierNoI+
                "&paymentTerm="+paymentTermI+"&paymentTermPeriod="+paymentTermPeriodI+"&rawMaterial="+rawMatI+"&plant="+plantI+"&purchaseOrder="+poI+"&transactionId="+transactionIdI+"&id="+selectedIds);
            } 
            else {
                window.open("php/modules/goodsReceived/index.php?action=export&isMulti=N&fromDate="+fromDateI+"&toDate="+toDateI+"&company="+companyI+"&supplier="+supplierNoI+"&paymentTerm="+paymentTermI+"&paymentTermPeriod="+paymentTermPeriodI+"&rawMaterial="+rawMatI+"&plant="+plantI+"&purchaseOrder="+poI+"&transactionId="+transactionIdI);
            }
        });

        filterDropdownByTransactionStatus('#rawMatSearch', 'allRawMatSearchOptions', 'Purchase');
        filterSupplierDropdown();
        togglePaymentTermPeriodSearch();
    });

    function renderTable() {
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();
        var companyI = $('#companySearch').val() || '';
        var paymentTermI = $('#paymentTermSearch').val() || '';
        var paymentTermPeriodI = $('#paymentTermPeriodSearch').val() || '';
        var supplierNoI = $('#supplierSearch').val() || '';
        var rawMatI = $('#rawMatSearch').val() || '';
        var plantI = $('#plantSearch').val() || '';
        var poI = $('#poSearch').val() || '';
        var transactionIdI = $('#transactionIdSearch').val() || '';

        // Destroy the old Datatable if exists
        if ($.fn.DataTable.isDataTable('#weightTable')) {
            $("#weightTable").DataTable().clear().destroy();
        }

        // Create new Datatable
        table = $("#weightTable").DataTable({
            "responsive": { details: false }, // hidden columns are shown in the expanded row (bindExpandableRows)
            "autoWidth": false,
            'processing': true,
            'serverSide': true,
            'searching': false,
            'serverMethod': 'post',
            'ajax': {
                'url': 'php/modules/goodsReceived/index.php',
                'data': {
                    action: 'filter',
                    fromDate: fromDateI,
                    toDate: toDateI,
                    company: companyI,
                    paymentTerm: paymentTermI,
                    paymentTermPeriod: paymentTermPeriodI,
                    supplier: supplierNoI,
                    rawMaterial: rawMatI,
                    plant: plantI,
                    purchaseOrder: poI,
                    transactionId: transactionIdI,
                }
            },
            'columnDefs': [{ targets: '_all', defaultContent: '' }], // show "" instead of null
            'columns': [
                {
                    data: 'id',
                    className: 'select-checkbox',
                    orderable: false,
                    responsivePriority: 1,
                    render: function (data, type, row) {
                        return '<input type="checkbox" class="select-checkbox" id="checkbox_' + data + '" value="' + data + '"/>';
                    }
                },
                { data: 'company' },
                { data: 'supplier_name' },
                { data: 'plant_name' },
                { data: 'raw_mat_name' },
                { data: 'transaction_date' },
                { data: 'total_final_weight' },
                {
                    data: 'issues',
                    orderable: false,
                    render: function (data, type, row) {
                        return renderIssues(data);
                    }
                },
                {
                    data: 'id',
                    className: 'action-button',
                    orderable: false,
                    responsivePriority: 1,
                    render: function (data, type, row) {
                        if (!canPostToSql) {
                            return '';
                        }
                        return `
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item post-item-btn" id="post${data}" onclick="post(${data})">
                                            <i class="ri-send-plane-line align-middle me-1"></i> <?=$languageArray['post_code'][$language]?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        `;
                    }
                }
            ]
        });
        bindExpandableRows(table);
    }

    // ─── Expandable rows ──────────────────────────────────────────────────────
    // The list table runs Responsive with details: false - its child row holds our own details, so the
    // columns Responsive hides are listed at the top of that child row (.dt-hidden-cols) instead
    function bindExpandableRows(dtApi) {
        var node = dtApi.table().node();
        dtApi.on('responsive-resize', function (e) {
            if (e.target === node) { // the inner detail tables' events bubble up to here too
                dtApi.rows().every(function () {
                    if (this.child.isShown()) {
                        renderHiddenColumns(dtApi, this);
                    }
                });
            }
        });

        // A redraw drops the child rows - clear out the detail tables that went with them
        dtApi.on('draw', cleanupDetailTables);
    }

    function renderHiddenColumns(dtApi, rowApi) {
        var visible = dtApi.columns().responsiveHidden(); // true = shown, false = hidden by Responsive
        var html = '';
        var visibleCount = 0;
        for (var i = 0; i < visible.length; i++) {
            if (visible[i]) {
                visibleCount++;
            } else {
                html += `
                <div class="col-auto">
                    <p class="mb-2"><strong class="text-uppercase">${$(dtApi.column(i).header()).text()}:</strong> ${$(dtApi.cell(rowApi.index(), i).node()).html()}</p>
                </div>`;
            }
        }
        var $child = $(rowApi.child());
        $child.find('.dt-hidden-cols').first().html(html ? '<div class="row">' + html + '</div><hr class="mt-0">' : '');

        // DataTables sets the child cell's colspan to the columns shown when the row opened - keep it spanning
        // the current ones, then let the inner table re-fit the new width
        $child.children('td').attr('colspan', visibleCount);
        $child.find('table.dataTable').each(function () {
            $(this).DataTable().columns.adjust().responsive.recalc();
        });
    }

    // Responsive DataTable for the table inside an expanded row
    function initDetailTable(selector, options) {
        $(selector).DataTable($.extend({
            // default renderer (copies the cell HTML) - listHiddenNodes in Responsive 2.2.9 keeps one page-wide
            // store keyed only by row-col, so it moves cells between this table and the list table
            "responsive": true,
            "autoWidth": false,
            "paging": false,
            "searching": false,
            "ordering": false,
            "info": false
        }, options || {}));
    }

    // Destroy detail tables no longer on the page (row collapsed or list redrawn) so they stop handling resizes
    function cleanupDetailTables() {
        $.each($.fn.dataTable.tables(), function (i, node) {
            if (!$.contains(document.documentElement, node)) {
                $(node).DataTable().destroy();
            }
        });
    }

    // One badge per issue of the group, listed one per line; a tick when there is none
    function renderIssues(issues) {
        if (!issues || issues.length === 0) {
            return '<i class="ri-checkbox-circle-fill text-success fs-5"></i>';
        }
        return '<div class="d-flex flex-column align-items-start gap-1">' + issues.map(function (issue) {
            return '<span class="badge bg-danger">' + (issueLabels[issue] || issue) + '</span>';
        }).join('') + '</div>';
    }

    function format(row, groupId, editMode) {
        // width:0 + min-width:100% keeps the inner table from stretching the parent row's cell,
        // so it follows the outer table's width and its responsive columns collapse when the screen shrinks
        var returnString = `
        <div style="width:0; min-width:100%;">
        <div class="dt-hidden-cols"></div>
        <!-- Weighing Section -->
        <div class="expand-summary">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="expand-title"><i class="ri-file-list-3-fill"></i><?=$languageArray['goods_received_information_code'][$language]?></h5>`;

        if (canUpdatePrice && row.weights && row.weights.length > 0) {
            returnString += `<div class="flex-shrink-0">`;
            if (editMode) {
                returnString += `
                <button type="button" class="btn btn-success btn-sm" onclick="event.stopPropagation(); savePrices(${groupId});"><i class="fas fa-save align-middle me-1"></i><?=$languageArray['save_prices_code'][$language]?></button>
                <button type="button" class="btn btn-light btn-sm" onclick="event.stopPropagation(); toggleUpdatePrice(${groupId});"><?=$languageArray['cancel_code'][$language]?></button>`;
            } else {
                returnString += `
                <button type="button" class="btn btn-warning btn-sm" onclick="event.stopPropagation(); toggleUpdatePrice(${groupId});"><i class="fas fa-tag align-middle me-1"></i><?=$languageArray['update_price_code'][$language]?></button>`;
            }
            returnString += `</div>`;
        }

        returnString += `
        </div>
        <div class="row mt-2">
            <div class="col-4">
                <p><strong class="text-uppercase"><?=$languageArray['total_received_amount_code'][$language]?>:</strong> ${displayWeightMT(row.total_final_weight)}</p>
            </div>`;

            if (isSADMIN && row.weights && row.weights.length > 0) {
                returnString += `
                    <div class="col-4">
                        <p><strong class="text-uppercase"><?=$languageArray['unit_price_code'][$language]?>:</strong> RM ${displayValue(row.weights[0].unit_price)}</p>
                    </div>
                    <div class="col-4">
                        <p><strong class="text-uppercase"><?=$languageArray['total_price_code'][$language]?>:</strong> RM ${displayNumber(parseFloat(row.weights[0].unit_price) * (parseFloat(row.total_final_weight)/1000), 2)}</p>
                    </div>
                `;
            }

            returnString += `
        </div>
        </div>
        <div>
            <table id="grWeightTable${groupId}" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
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
                        if (editMode || canIncludePrice) {
                            // "all" keeps the price inputs from collapsing - collapsed cells are copied as HTML, which would duplicate the input ids
                            var priceClass = editMode ? ' class="all"' : '';
                            returnString += `
                        <th${priceClass}><?=$languageArray['unit_price_code'][$language]?></th>
                        <th${priceClass}><?=$languageArray['total_price_code'][$language]?></th>`;
                        }
                        if (canEditWeight || canPrintWeight) {
                            returnString += `<th><?=$languageArray['action_code'][$language]?><?=actionPermissionNote([$canEditWeight, $canPrintWeight])?></th>`;
                        }

                        returnString += `</tr>
                </thead>
                <tbody>`;

                for (var i = 0; i < row.weights.length; i++) {
                    var weights = row.weights;

                    returnString += `
                        <tr>
                            <td>${displayValue(weights[i].transaction_id)}</td>
                            <td>${displayValue(weights[i].delivery_no)}</td>
                            <td>${displayValue(weights[i].lorry_plate_no1)}</td>
                            <td>${displayValue(weights[i].transporter)}</td>
                            <td>${displayValue(weights[i].destination)}</td>
                            <td>${displayWeightMT(weights[i].gross_weight1)}</td>
                            <td>${displayValue(weights[i].gross_weight1_date)}</td>
                            <td>${displayWeightMT(weights[i].tare_weight1)}</td>
                            <td>${displayValue(weights[i].tare_weight1_date)}</td>
                            <td>${displayWeightMT(weights[i].nett_weight1)}</td>`
                            if (editMode) {
                                var existingPrice = parseFloat(weights[i].unit_price);
                                var hasPrice = !isNaN(existingPrice) && existingPrice > 0;
                                var startValue = hasPrice ? existingPrice.toFixed(2) : '';
                                var startTotal = hasPrice ? displayNumber(existingPrice * (parseFloat(weights[i].nett_weight1) / 1000), 2) : '-';
                                var isSynced = weights[i].synced === 'Y';
                                if (isSynced) {
                                    returnString += `
                                        <td onclick="event.stopPropagation();">
                                            <input type="number" class="form-control form-control-sm" style="width: 100px;" value="${startValue}" disabled title="<?=$languageArray['price_locked_code'][$language]?>">
                                        </td>
                                        <td class="text-end">${startTotal}</td>
                                    `;
                                } else {
                                    returnString += `
                                        <td onclick="event.stopPropagation();">
                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" style="width: 100px;" id="priceInput${weights[i].id}" data-nett="${weights[i].nett_weight1}" value="${startValue}" oninput="updatePriceRowTotal(${weights[i].id});">
                                        </td>
                                        <td id="priceTotal${weights[i].id}" class="text-end">${startTotal}</td>
                                    `;
                                }
                            } else if (canIncludePrice) {
                                var unitPrice = parseFloat(weights[i].unit_price);
                                var hasUnitPrice = !isNaN(unitPrice) && unitPrice > 0;
                                returnString += `
                                    <td class="text-end">${hasUnitPrice ? displayNumber(unitPrice, 2) : '-'}</td>
                                    <td class="text-end">${hasUnitPrice ? displayNumber(unitPrice * (parseFloat(weights[i].nett_weight1) / 1000), 2) : '-'}</td>
                                `;
                            }
                            if (canEditWeight || canPrintWeight) {
                                // stopPropagation: don't let the clicks reach the parent row's expand handler
                                returnString += `
                                <td>
                                    <div class="row g-1 d-flex">`;

                                if (canEditWeight) {
                                    returnString += `
                                        <div class="col-auto">
                                            <button title="<?=$languageArray['edit_code'][$language]?>" type="button" id="editWeight${weights[i].id}" onclick="event.stopPropagation(); editWeight(${weights[i].id}, 'N')" class="btn btn-warning btn-sm">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                        </div>`;
                                }

                                if (canPrintWeight) {
                                    returnString += `
                                        <div class="col-auto">
                                            <button title="<?=$languageArray['print_code'][$language]?>" type="button" id="printWeight${weights[i].id}" onclick="event.stopPropagation(); printWeight('${weights[i].id}', '${weights[i].transaction_status}')" class="btn btn-info btn-sm">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </div>`;
                                }

                                returnString += `
                                    </div>
                                </td>`;
                            }
                        returnString += `</tr>`;
                }

                returnString += `</tbody>
            </table>
        </div>
        </div>
        `;
        
        return returnString;
    }

    // Render a GR group's weighings into its child row and turn the inner table into a responsive DataTable
    function showWeighingDetails(rowApi, groupId, editMode) {
        var selector = '#grWeightTable' + groupId;
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
        }

        rowApi.child(format(expandedWeights[groupId], groupId, editMode)).show();
        renderHiddenColumns(table, rowApi);

        initDetailTable(selector, {
            "columnDefs": [
                { targets: 0, responsivePriority: 1 },
                { targets: -1, responsivePriority: 2 }
            ]
        });
    }

    // The list row of a GR group - looked up by data, since a reload rebuilds the row nodes
    function findGroupRow(groupId) {
        return table.row(function (idx, data) { return data.id == groupId; });
    }

    // ─── Update Price (expandable weighing table) ─────────────────────────────
    // Toggle a GR group's inline table between view mode and price-edit mode
    function toggleUpdatePrice(groupId) {
        var rowApi = findGroupRow(groupId);
        if (!rowApi.length || !rowApi.child.isShown()) {
            return;
        }

        updatePriceModes[groupId] = !updatePriceModes[groupId];
        showWeighingDetails(rowApi, groupId, updatePriceModes[groupId]);

        if (updatePriceModes[groupId]) {
            loadPriceSuggestions(groupId);
        }
    }

    // Recalculate a row's Total cell as its Unit Price input changes; marks the input as user-edited
    // so a price suggestion arriving later doesn't overwrite what was typed
    function updatePriceRowTotal(id) {
        var $input = $('#priceInput' + id);
        var unitPrice = parseFloat($input.val());
        var nett = parseFloat($input.data('nett'));
        var $total = $('#priceTotal' + id);

        if (!isNaN(unitPrice) && unitPrice >= 0 && !isNaN(nett)) {
            $total.text(displayNumber(unitPrice * (nett / 1000), 2));
        } else {
            $total.text('-');
        }
        $input.data('user-edited', true);
    }

    // Prefill blank Unit Price inputs from the supplier's item price, or the item's default Purchase Price
    function loadPriceSuggestions(groupId) {
        var weights = (expandedWeights[groupId] && expandedWeights[groupId].weights) || [];
        var ids = weights.filter(function (w) { return w.synced !== 'Y'; }).map(function (w) { return w.id; });
        if (!ids.length) {
            return;
        }

        var companyI = $('#companySearch').val() || '';
        $.post('php/modules/goodsReceived/index.php', { action: 'getPriceSuggestions', ids: ids, company: companyI }, function (data) {
            var obj = JSON.parse(data);
            if (obj.status !== 'success') {
                return;
            }
            ids.forEach(function (id) {
                var suggestion = obj.message[id];
                var $input = $('#priceInput' + id);
                if (!$input.length || $input.val() !== '' || $input.data('user-edited')) {
                    return; // already has a value (existing price or typed by the user) - don't override
                }
                if (suggestion && suggestion.unitPrice !== null && suggestion.unitPrice !== undefined) {
                    $input.val(parseFloat(suggestion.unitPrice).toFixed(2));
                    updatePriceRowTotal(id);
                }
            });
        }).fail(function () {
            toastr["error"]("Something wrong when loading price suggestions", "Failed:");
        });
    }

    // Save the unit prices typed for a GR group's weighings, then refresh that row in view mode
    function savePrices(groupId) {
        var weights = (expandedWeights[groupId] && expandedWeights[groupId].weights) || [];
        var prices = [];
        weights.forEach(function (w) {
            var $input = $('#priceInput' + w.id);
            if (!$input.length) {
                return;
            }
            var val = $input.val();
            if (val === '' || val === null) {
                return; // leave weighings the user didn't set a price for untouched
            }
            var unitPrice = parseFloat(val);
            if (isNaN(unitPrice) || unitPrice < 0) {
                return;
            }
            prices.push({ id: w.id, unitPrice: unitPrice });
        });

        if (!prices.length) {
            toastr["warning"]("Please enter at least one unit price", "Warning:");
            return;
        }

        var companyI = $('#companySearch').val() || '';
        $.post('php/modules/goodsReceived/index.php', { action: 'updatePrices', prices: prices, company: companyI }, function (data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                toastr["success"](obj.message, "Success:");
                // reload first, then reopen the group - the reload rebuilds the rows and drops their child rows
                table.ajax.reload(function () {
                    refreshGroupPrices(groupId);
                }, false);
            } else {
                toastr["error"](obj.message, "Failed:");
            }
        }).fail(function () {
            toastr["error"]("Something wrong when saving prices", "Failed:");
        });
    }

    // Re-fetch a GR group's weighings after a price save and reopen its child row in view mode
    function refreshGroupPrices(groupId) {
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();

        $.post('php/modules/weighing/index.php', { action: 'getWeight', userID: groupId, fromDate: fromDateI, toDate: toDateI, format: 'EXPANDABLE', acctType: 'GR' }, function (data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                var rowApi = findGroupRow(groupId);
                if (!rowApi.length) {
                    return; // no longer in the list (e.g. filtered out after the save)
                }
                expandedWeights[groupId] = obj.message;
                updatePriceModes[groupId] = false;
                $(rowApi.node()).addClass('shown');
                showWeighingDetails(rowApi, groupId, false);
            }
        });
    }

    // Post to SQL for one GR group: pick its weighings in a modal, then post only those
    function post(id) {
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();
        var group = findGroupRow(id).data() || {};

        $.post('php/modules/weighing/index.php', { action: 'getWeight', userID: id, fromDate: fromDateI, toDate: toDateI, format: 'EXPANDABLE', acctType: 'GR' }, function (data) {
            var obj = JSON.parse(data);
            if (obj.status !== 'success') {
                toastr["error"](obj.message || "Something wrong when loading the weighings", "Failed:");
                return;
            }

            var weights = obj.message.weights || [];
            $('#postModalTitle').text([group.supplier_name, group.raw_mat_name].filter(Boolean).join(' - '));

            var tableHtml = `
                <div class="table-responsive">
                    <table class="table table-bordered nowrap table-striped align-middle" style="width:100%" id="grModalTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="grModalSelectAll"></th>
                                <th><?=$languageArray['transaction_id_code'][$language]?></th>
                                <th><?=$languageArray['po_no_code'][$language]?></th>
                                <th><?=$languageArray['vehicle_no_code'][$language]?></th>
                                <th><?=$languageArray['transporter_code'][$language]?></th>
                                <th><?=$languageArray['destination_code'][$language]?></th>
                                <th><?=$languageArray['gross_incoming_code'][$language]?></th>
                                <th><?=$languageArray['incoming_date_code'][$language]?></th>
                                <th><?=$languageArray['tare_outgoing_code'][$language]?></th>
                                <th><?=$languageArray['outgoing_date_code'][$language]?></th>
                                <th><?=$languageArray['nett_weight_code'][$language]?></th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            for (var i = 0; i < weights.length; i++) {
                var w = weights[i];
                tableHtml += `
                    <tr>
                        <td><input type="checkbox" class="gr-checkbox" value="${w.id}"></td>
                        <td>${displayValue(w.transaction_id)}</td>
                        <td>${displayValue(w.delivery_no)}</td>
                        <td>${displayValue(w.lorry_plate_no1)}</td>
                        <td>${displayValue(w.transporter)}</td>
                        <td>${displayValue(w.destination)}</td>
                        <td>${displayWeightMT(w.gross_weight1, 2)}</td>
                        <td>${displayValue(w.gross_weight1_date)}</td>
                        <td>${displayWeightMT(w.tare_weight1, 2)}</td>
                        <td>${displayValue(w.tare_weight1_date)}</td>
                        <td>${displayWeightMT(w.nett_weight1, 2)}</td>
                    </tr>
                `;
            }

            tableHtml += `
                        </tbody>
                    </table>
                </div>
            `;

            $('#grForm').html(tableHtml + '<div class="col-lg-12"><div class="hstack gap-2 justify-content-end"><button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button><button type="button" class="btn btn-primary" id="submitGR"><?=$languageArray['submit_code'][$language]?></button></div></div>');

            $('#grModalSelectAll').on('change', function () {
                $('.gr-checkbox').prop('checked', this.checked);
            });

            $('#submitGR').on('click', function () {
                var selectedIds = $('#grModalTable tbody .gr-checkbox:checked').map(function () { return this.value; }).get();

                if (!selectedIds.length) {
                    alert('Please select at least one GR to post');
                    return;
                }
                if (!confirm('Are you sure you want to post to SQL these items?')) {
                    return;
                }

                $('#spinnerLoading').show();
                $.post('php/modules/goodsReceived/index.php', {
                    action: 'post',
                    company: $('#companySearch').val() || '',
                    userID: selectedIds,
                    type: 'MULTIGR'
                }, function (data) {
                    var obj = JSON.parse(data);
                    $('#spinnerLoading').hide();

                    if (obj.status === 'success') {
                        table.ajax.reload(null, false);
                        $('#viewModal').modal('hide');
                        toastr["success"](obj.message, "Success:");
                    } else if (obj.status === 'failed') {
                        toastr["error"](obj.message, "Failed:");
                    } else {
                        toastr["error"]("Something wrong when activate", "Failed:");
                    }
                }).fail(function () {
                    $('#spinnerLoading').hide();
                    toastr["error"]("Something wrong when posting", "Failed:");
                });
            });

            $('#viewModal').modal('show');
        });
    }

    function togglePaymentTermPeriodSearch() {
        var isTerm = $('#paymentTermSearch').val() === 'Term';
        $('#paymentTermPeriodSearchDisplay').toggle(isTerm);
        if (!isTerm) {
            $('#paymentTermPeriodSearch').val('');
        }
    }

    function filterSupplierDropdown() {
        if (!allSupplierSearchOptions) {
            allSupplierSearchOptions = $('#supplierSearch option').clone(true);
        }

        var paymentTerm = $('#paymentTermSearch').val() || '-';
        $('#supplierSearch').empty();

        allSupplierSearchOptions.each(function() {
            var $option = $(this).clone(true);
            if ($option.val() === '-' || $option.val() === '') {
                $('#supplierSearch').append($option);
            } else if (paymentTerm === '-' || $option.data('payment-term') == paymentTerm) {
                $('#supplierSearch').append($option);
            }
        });

        $('#supplierSearch').val('-').trigger('change');
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
    </script>

    <?php if ($canEditWeight || $canPrintWeight): ?>
    <!-- Weighing modal component (after the page script so its Select2 / date picker setup runs last) -->
    <?php include 'components/weighingModal/script.php'; ?>
    <?php endif; ?>
</body>
</html>
