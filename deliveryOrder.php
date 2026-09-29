<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
require_once "php/requires/lookup.php";
if (!hasModulePermission('Accounting', 'Delivery Order', ['view'])){
    header('Location: no-permission.php');
    exit;
}

$plantId = $_SESSION['plant_id'];
$selectedPlantId = $_SESSION['selected_plant_id'] ?? null;

$companyId = $_SESSION['company_id'];
if (!hasModulePermission('Accounting', 'Delivery Order', ['view_all_companies'])){
    // Get companies
    $company_ids = implode(',', array_map('intval', $_SESSION['company_ids']));
    $company = $db->query("SELECT * FROM Company WHERE status = 0 AND id IN ($company_ids) ORDER BY name");
    // Only the selected company's customers and products
    $selectedCompanyId = intval($companyId);
    $customer = $db->query("SELECT * FROM Customer WHERE status = '0' AND company IN ($selectedCompanyId) ORDER BY name ASC");
    $customer2 = $db->query("SELECT * FROM Customer WHERE status = '0' AND company IN ($selectedCompanyId) ORDER BY name ASC");
    $product = $db->query("SELECT p.*, IFNULL(c.is_sales, 'Y') as is_sales, IFNULL(c.is_purchase, 'Y') as is_purchase, IFNULL(c.is_local, 'Y') as is_local, IFNULL(c.is_port, 'Y') as is_port, IFNULL(c.is_misc, 'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = '0' AND p.company IN ($selectedCompanyId) ORDER BY p.name ASC");
    $product2 = $db->query("SELECT p.*, IFNULL(c.is_sales, 'Y') as is_sales, IFNULL(c.is_purchase, 'Y') as is_purchase, IFNULL(c.is_local, 'Y') as is_local, IFNULL(c.is_port, 'Y') as is_port, IFNULL(c.is_misc, 'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = '0' AND p.company IN ($selectedCompanyId) ORDER BY p.name ASC");
}else{
    $company = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
    $customer = $db->query("SELECT * FROM Customer WHERE status = '0' ORDER BY name ASC");
    $customer2 = $db->query("SELECT * FROM Customer WHERE status = '0' ORDER BY name ASC");
    $product = $db->query("SELECT p.*, IFNULL(c.is_sales, 'Y') as is_sales, IFNULL(c.is_purchase, 'Y') as is_purchase, IFNULL(c.is_local, 'Y') as is_local, IFNULL(c.is_port, 'Y') as is_port, IFNULL(c.is_misc, 'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = '0' ORDER BY p.name ASC");
    $product2 = $db->query("SELECT p.*, IFNULL(c.is_sales, 'Y') as is_sales, IFNULL(c.is_purchase, 'Y') as is_purchase, IFNULL(c.is_local, 'Y') as is_local, IFNULL(c.is_port, 'Y') as is_port, IFNULL(c.is_misc, 'Y') as is_misc FROM Product p LEFT JOIN Product_Categories c ON p.category = c.id WHERE p.status = '0' ORDER BY p.name ASC");
}

$plantName = '-';
$plantCode = '-';
if (!hasModulePermission('Accounting', 'Delivery Order', ['view_all_plants'])){
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

// Weighing modal component - used to edit / print the DO's weighings, which are Sales
$canEditWeight = hasModulePermission('Weighing', 'Sales', ['edit']);
$canPrintWeight = hasModulePermission('Weighing', 'Sales', ['print']);
$canUpdatePrice = hasModulePermission('Accounting', 'Delivery Order', ['update_price']);
$canIncludePrice = hasModulePermission('Accounting', 'Delivery Order', ['include_price']);
if ($canEditWeight || $canPrintWeight) {
    require_once "components/weighingModal/data.php";
}
?>

<head>

    <title><?=$languageArray['delivery_order_code'][$language]?> | Synctronix - Weighing System</title>
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
                                                    <div class="col-3" <?= !hasModulePermission('Accounting', 'Delivery Order', ['view_all_companies']) ? "style='display:none'" : '' ?>>
                                                        <div class="mb-3">
                                                            <label for="companySearch" class="form-label"><?=$languageArray['company_code'][$language]?></label>
                                                            <select class="form-select select2" id="companySearch" name="companySearch" required>
                                                                <?php while($rowCompany=mysqli_fetch_assoc($company)){ ?>
                                                                    <option value="<?=$rowCompany['id'] ?>" <?=($rowCompany['id'] == $companyId) ? 'selected' : ''?>><?=$rowCompany['name'] ?></option>
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
                                                            <label for="deliveryNoSearch" class="form-label"><?=$languageArray['delivery_no_code'][$language]?></label>
                                                            <input id="deliveryNoSearch" name="deliveryNoSearch" class="form-control">
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
                                                                <h5 class="card-title mb-0 text-white"><?=$languageArray['delivery_order_records'][$language]?></h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <?php if(hasModulePermission('Accounting', 'Delivery Order', ['export'])): ?>
                                                                <button type="button" id="exportExcel" class="btn btn-success waves-effect waves-light">
                                                                    <i class="ri-file-excel-line align-middle me-1"></i>
                                                                    <?=$languageArray['export_excel_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>
                                                                <?php if(hasModulePermission('Accounting', 'Delivery Order', ['post_to_sql'])): ?>
                                                                <button type="button" id="postSQL" class="btn btn-warning waves-effect waves-light">
                                                                    <i class="ri-send-plane-line align-middle me-1"></i>
                                                                    <?=$languageArray['post_to_sql_code'][$language]?>
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
                                                                    <th><?=$languageArray['customer_code'][$language]?></th>
                                                                    <th><?=$languageArray['product_code'][$language]?></th>
                                                                    <th><?=$languageArray['plant_code'][$language]?></th>
                                                                    <th><?=$languageArray['delivery_date_code'][$language]?></th>
                                                                    <th><?=$languageArray['total_delivery_amount_code'][$language]?></th>
                                                                    <th><?=$languageArray['issues_code'][$language] ?? 'Issues'?></th>
                                                                    <th><?=$languageArray['action_code'][$language]?><?=actionPermissionNote([hasModulePermission('Accounting', 'Delivery Order', ['post_to_sql'])])?></th>
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
                            <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableDO" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable custom-xxl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalScrollableDO"></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form role="form" id="doForm" class="needs-validation" novalidate autocomplete="off">
                                                
                                            </form>
                                        </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->

                            <?php if ($canEditWeight || $canPrintWeight): ?>
                            <?php include 'components/weighingModal/modal.php'; ?>
                            <?php endif; ?>

                            <?php if ($canEditWeight): ?>
                            <?php include 'components/customerSideInfoModal/modal.php'; ?>
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
    var allProductOptions = null;
    var allProductSearchOptions = null;
    var canEditWeight = <?= $canEditWeight ? 'true' : 'false' ?>;
    var canPrintWeight = <?= $canPrintWeight ? 'true' : 'false' ?>;
    var canUpdatePrice = <?= $canUpdatePrice ? 'true' : 'false' ?>;
    var canIncludePrice = <?= $canIncludePrice ? 'true' : 'false' ?>;
    var issueLabels = {
        customer: <?= json_encode($languageArray['customer_code_not_match_code'][$language] ?? 'Customer code not match') ?>,
        item: <?= json_encode($languageArray['item_code_not_match_code'][$language] ?? 'Item code not match') ?>,
        price: <?= json_encode($languageArray['need_unit_price_code'][$language] ?? 'Need unit price and total price') ?>
    };
    var expandedWeights = {}; // groupId -> last fetched weighing detail (weights, totalDeliverAmt, purchase_order)
    var updatePriceModes = {}; // groupId -> true while that row's table is in Update Price edit mode

    $(function () {
        // Weighing modal: refresh the DO list after a weighing is saved
        if (window.initWeighingModal) {
            initWeighingModal({
                onSaved: function(obj, withPrint){
                    table.ajax.reload(null, false);
                }
            });
        }

        // Customer side info modal: refresh the DO list after saving
        if (window.initCustomerSideInfoModal) {
            initCustomerSideInfoModal({
                onSaved: function(obj){
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
            //dateFormat: "d-m-Y",
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

        // Add event listener for opening and closing details on row click
        $('#weightTable tbody').on('click', 'tr', function (e) {
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
                delete updatePriceModes[row.data().id]; // re-expanding starts fresh in view mode
            } else {
                var groupId = row.data().id;
                $.post('php/modules/weighing/index.php', { action: 'getWeight', userID: groupId, fromDate: fromDateI, toDate: toDateI, format: 'EXPANDABLE', acctType: 'DO' }, function (data) {
                    var obj = JSON.parse(data);
                    if (obj.status === 'success') {
                        expandedWeights[groupId] = obj.message;
                        row.child(format(obj.message, groupId, false)).show();
                        tr.addClass("shown").attr('data-group-id', groupId);
                    }
                });
            }
        });

        // Post to SQL Handling
        $('#postSQL').on('click', function () {
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();
            var companyI = $('#companySearch').val() || '';
            var customerNoI = $('#customerNoSearch').val() || '';
            var productI = $('#productSearch').val() || '';
            var plantI = $('#plantSearch').val() || '';
            var deliveryNoI = $('#deliveryNoSearch').val() || '';
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
                    $.post('php/modules/deliveryOrder/index.php', {
                        action: 'post',
                        fromDate: fromDateI,
                        toDate: toDateI,
                        company: companyI,
                        customer: customerNoI,
                        product: productI,
                        plant: plantI,
                        deliveryNo: deliveryNoI,
                        transactionId: transactionIdI,
                        userID: selectedIds, 
                        type: 'MULTI'
                    }, function(data){
                        var obj = JSON.parse(data);
                        
                        if(obj.status === 'success'){
                            $('#weightTable').DataTable().ajax.reload(null, false);
                            $('#spinnerLoading').hide();
                            toastr["success"](obj.message, "Success:");
                        }
                        else if(obj.status === 'failed'){
                            $('#spinnerLoading').hide();
                            toastr["error"](obj.message, "Failed:");
                        }
                        else{
                            $('#spinnerLoading').hide();
                            toastr["error"]("Something wrong when activate", "Failed:");
                        }
                    });
                }
            } 
            else {
                if (confirm('Are you sure you want to post to SQL?')) {
                    $('#spinnerLoading').show();
                    $.post('php/modules/deliveryOrder/index.php', {
                        action: 'post',
                        fromDate: fromDateI,
                        toDate: toDateI,
                        company: companyI,
                        customer: customerNoI,
                        product: productI,
                        plant: plantI,
                        deliveryNo: deliveryNoI,
                        transactionId: transactionIdI,
                        type: 'ALL'
                    }, function(data){
                        var obj = JSON.parse(data);
                        
                        if(obj.status === 'success'){
                            $('#weightTable').DataTable().ajax.reload(null, false);
                            $('#spinnerLoading').hide();
                            toastr["success"](obj.message, "Success:");
                        }
                        else if(obj.status === 'failed'){
                            $('#spinnerLoading').hide();
                            toastr["error"](obj.message, "Failed:");
                        }
                        else{
                            $('#spinnerLoading').hide();
                            toastr["error"]("Something wrong when activate", "Failed:");
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
            var customerNoI = $('#customerNoSearch').val() || '';
            var productI = $('#productSearch').val() || '';
            var plantI = $('#plantSearch').val() || '';
            var deliveryNoI = $('#deliveryNoSearch').val() || '';
            var transactionIdI = $('#transactionIdSearch').val() || '';
            var selectedIds = []; // An array to store the selected 'id' values

            $("#weightTable tbody input[type='checkbox']").each(function () {
                if (this.checked) {
                    selectedIds.push($(this).val());
                }
            });

            if (selectedIds.length > 0) {
                window.open("php/modules/deliveryOrder/index.php?action=export&isMulti=Y&fromDate="+fromDateI+"&toDate="+toDateI+"&company="+companyI+
                "&customer="+customerNoI+"&product="+productI+"&plant="+plantI+"&deliveryNo="+deliveryNoI+"&transactionId="+transactionIdI+"&id="+selectedIds);
            } 
            else {
                window.open("php/modules/deliveryOrder/index.php?action=export&isMulti=N&fromDate="+fromDateI+"&toDate="+toDateI+"&company="+companyI+
                "&customer="+customerNoI+"&product="+productI+"&plant="+plantI+"&deliveryNo="+deliveryNoI+"&transactionId="+transactionIdI);
            }     
        });

        filterDropdownByTransactionStatus('#productSearch', 'allProductOptions', 'Sales');
    });

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

    function renderTable() {
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();
        var companyI = $('#companySearch').val() || '';
        var customerNoI = $('#customerNoSearch').val() || '';
        var productI = $('#productSearch').val() || '';
        var plantI = $('#plantSearch').val() || '';
        var deliveryNoI = $('#deliveryNoSearch').val() || '';
        var transactionIdI = $('#transactionIdSearch').val() || '';

        // Destroy the old Datatable if exists
        if ($.fn.DataTable.isDataTable('#weightTable')) {
            $("#weightTable").DataTable().clear().destroy();
        }

        // Create new Datatable
        table = $("#weightTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            'processing': true,
            'serverSide': true,
            'searching': false,
            'serverMethod': 'post',
            'ajax': {
                'url': 'php/modules/deliveryOrder/index.php',
                'data': {
                    action: 'filter',
                    fromDate: fromDateI,
                    toDate: toDateI,
                    company: companyI,
                    customer: customerNoI,
                    product: productI,
                    plant: plantI,
                    deliveryNo: deliveryNoI,
                    transactionId: transactionIdI
                }
            },
            'columnDefs': [{ targets: '_all', defaultContent: '' }], // show "" instead of null
            'columns': [
                {
                    data: 'id',
                    className: 'select-checkbox',
                    orderable: false,
                    render: function (data, type, row) {
                        return '<input type="checkbox" class="select-checkbox" id="checkbox_' + data + '" value="' + data + '"/>';
                    }
                },
                { data: 'company' },
                { data: 'customer_name' },
                { data: 'product_name' },
                { data: 'plant_name' },
                { data: 'transaction_date' },
                { data: 'order_weight' },
                {
                    data: 'issues',
                    orderable: false,
                    render: function (data, type, row) {
                        return renderIssues(data);
                    }
                },
                {
                    data: 'id',
                    class: 'action-button',
                    orderable: false,
                    responsivePriority: 1,
                    render: function (data, type, row) {
                        if (isSADMIN || (permissions['Accounting'] && permissions['Accounting']['Delivery Order'] && permissions['Accounting']['Delivery Order'].includes('post_to_sql'))) {
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
                        return '';
                    }
                }
            ]
        });
    }

    // One badge per issue of the group, a tick when there is none
    function renderIssues(issues) {
        if (!issues || issues.length === 0) {
            return '<i class="ri-checkbox-circle-fill text-success fs-5"></i>';
        }
        return issues.map(function (issue) {
            return '<span class="badge bg-danger me-1">' + (issueLabels[issue] || issue) + '</span>';
        }).join('');
    }

    function format(row, groupId, editMode) {
        var returnString = `
        <!-- Weighing Section -->
        <div class="d-flex justify-content-between align-items-center">
            <span style="font-size:120%; text-decoration: underline;"><strong><?=$languageArray['delivery_order_information_code'][$language]?></strong></span>`;

        if (canUpdatePrice && row.weights && row.weights.length > 0) {
            returnString += `<div class="flex-shrink-0">`;
            if (editMode) {
                returnString += `
                <button type="button" class="btn btn-success btn-sm" onclick="event.stopPropagation(); savePrices(${groupId});">
                    <i class="fas fa-save align-middle me-1"></i><?=$languageArray['save_prices_code'][$language]?>
                </button>
                <button type="button" class="btn btn-light btn-sm" onclick="event.stopPropagation(); toggleUpdatePrice(${groupId});"><?=$languageArray['cancel_code'][$language]?></button>`;
            } else {
                returnString += `
                <button type="button" class="btn btn-info btn-sm" onclick="event.stopPropagation(); toggleUpdatePrice(${groupId});"><i class="fas fa-tag align-middle me-1"></i><?=$languageArray['update_price_code'][$language]?></button>`;
            }
            returnString += `</div>`;
        }

        returnString += `
        </div>
        <div class="row mt-2">
            <div class="col-4">
                <p><strong class="text-uppercase"><?=$languageArray['total_delivery_amount_code'][$language]?>:</strong> ${displayWeightMT(row.totalDeliverAmt)}</p>
            </div>`;

        if (isSADMIN && row.weights && row.weights.length > 0) {
            returnString += `
            <div class="col-4">
                <p><strong class="text-uppercase"><?=$languageArray['unit_price_code'][$language]?>:</strong> RM ${displayValue(row.weights[0].unit_price)}</p>
            </div>
            <div class="col-4">
                <p><strong class="text-uppercase"><?=$languageArray['total_price_code'][$language]?>:</strong> RM ${displayNumber(parseFloat(row.weights[0].unit_price) * (parseFloat(row.totalDeliverAmt)/1000), 2)}</p>
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
                        <th><?=$languageArray['do_no_code'][$language]?></th>
                        <th><?=$languageArray['vehicle_no_code'][$language]?></th>
                        <th><?=$languageArray['transporter_code'][$language]?></th>
                        <th><?=$languageArray['destination_code'][$language]?></th>
                        <th><?=$languageArray['gross_incoming_code'][$language]?></th>
                        <th><?=$languageArray['incoming_date_code'][$language]?></th>
                        <th><?=$languageArray['tare_outgoing_code'][$language]?></th>
                        <th><?=$languageArray['outgoing_date_code'][$language]?></th>
                        <th><?=$languageArray['nett_weight_code'][$language]?></th>`;
                        if (editMode || canIncludePrice) {
                            returnString += `
                        <th><?=$languageArray['unit_price_code'][$language]?></th>
                        <th><?=$languageArray['total_price_code'][$language]?></th>`;
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

                                    // Same rule as the weighing page: customer side info for non-purchase weighings, needs edit
                                    if (weights[i].transaction_status != 'Purchase' && weights[i].transaction_status != 'Local') {
                                        returnString += `
                                        <div class="col-auto">
                                            <button title="<?=$languageArray['fill_in_customer_side_info_code'][$language]?>" type="button" id="customerSideInfo${weights[i].id}" onclick="event.stopPropagation(); openCustomerSideInfo(${weights[i].id})" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-clipboard-list"></i>
                                            </button>
                                        </div>`;
                                    }
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
        `;

        return returnString;
    }

    // ─── Update Price (expandable weighing table) ─────────────────────────────
    // Toggle a DO group's inline table between view mode and price-edit mode
    function toggleUpdatePrice(groupId) {
        var tr = $('#weightTable tbody tr[data-group-id="' + groupId + '"]');
        var rowApi = table.row(tr);
        if (!rowApi.length || !rowApi.child.isShown()) {
            return;
        }

        updatePriceModes[groupId] = !updatePriceModes[groupId];
        rowApi.child(format(expandedWeights[groupId], groupId, updatePriceModes[groupId])).show();

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

    // Prefill blank Unit Price inputs from the customer's item price, or the item's default Selling Price
    function loadPriceSuggestions(groupId) {
        var weights = (expandedWeights[groupId] && expandedWeights[groupId].weights) || [];
        var ids = weights.filter(function (w) { return w.synced !== 'Y'; }).map(function (w) { return w.id; });
        if (!ids.length) {
            return;
        }

        var companyI = $('#companySearch').val() || '';
        $.post('php/modules/deliveryOrder/index.php', { action: 'getPriceSuggestions', ids: ids, company: companyI }, function (data) {
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

    // Save the unit prices typed for a DO group's weighings, then refresh that row in view mode
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
        $.post('php/modules/deliveryOrder/index.php', { action: 'updatePrices', prices: prices, company: companyI }, function (data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                toastr["success"](obj.message, "Success:");
                refreshGroupPrices(groupId);
                table.ajax.reload(null, false);
            } else {
                toastr["error"](obj.message, "Failed:");
            }
        }).fail(function () {
            toastr["error"]("Something wrong when saving prices", "Failed:");
        });
    }

    // Re-fetch a DO group's weighings after a price save and re-render its child row in view mode
    function refreshGroupPrices(groupId) {
        var tr = $('#weightTable tbody tr[data-group-id="' + groupId + '"]');
        var rowApi = table.row(tr);
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();

        $.post('php/modules/weighing/index.php', { action: 'getWeight', userID: groupId, fromDate: fromDateI, toDate: toDateI, format: 'EXPANDABLE', acctType: 'DO' }, function (data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                expandedWeights[groupId] = obj.message;
                updatePriceModes[groupId] = false;
                rowApi.child(format(obj.message, groupId, false)).show();
            }
        });
    }

    function print(id) {
        $.post('php/modules/weighing/index.php', {action: 'print', userID: id, file: 'weight'}, function(data){
            var obj = JSON.parse(data);

            if(obj.status === 'success'){
                var printWindow = window.open('', '', 'height=400,width=800');
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
        });
    }

    function post(id){
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();

        // Exclude specific td elements by checking the event target
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();
        $.post('php/modules/weighing/index.php', { action: 'getWeight', userID: id, fromDate: fromDateI, toDate: toDateI, format: 'EXPANDABLE', acctType: 'DO' }, function (data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                var weights = obj.message.weights;
                $('#exampleModalScrollableDO').text(obj.message.purchase_order);

                var tableHtml = `
                    <div class="table-responsive">
                        <table class="table table-bordered nowrap table-striped align-middle" style="width:100%" id="doModalTable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th><?=$languageArray['transaction_id_code'][$language]?></th>
                                    <th><?=$languageArray['do_no_code'][$language]?></th>
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
                            <td><input type="checkbox" class="do-checkbox" value="${w.id}"></td>
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

                $('#doForm').html(tableHtml + '<div class="col-lg-12"><div class="hstack gap-2 justify-content-end"><button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button><button type="button" class="btn btn-primary" id="submitDO"><?=$languageArray['submit_code'][$language]?></button></div></div>');

                // Select all handler
                $('#selectAll').on('change', function () {
                    $('.do-checkbox').prop('checked', this.checked);
                });

                $('#submitDO').off('click').on('click', function () {
                    var selectedDOs = [];

                    $("#doModalTable tbody input[type='checkbox']").each(function () {
                        if (this.checked) {
                            selectedDOs.push($(this).val());
                        }
                    });

                    if (selectedDOs.length > 0) {
                        if (confirm('Are you sure you want to post to SQL these items?')) {
                            $('#spinnerLoading').show();
                            $.post('php/modules/deliveryOrder/index.php', {
                        action: 'post',
                                userID: selectedDOs, 
                                type: 'MULTIDO'
                            }, function(data){
                                var obj = JSON.parse(data);
                                
                                if(obj.status === 'success'){
                                    $('#weightTable').DataTable().ajax.reload(null, false);
                                    $('#spinnerLoading').hide();
                                    $('#viewModal').modal('hide');
                                    toastr["success"](obj.message, "Success:");
                                }
                                else if(obj.status === 'failed'){
                                    $('#spinnerLoading').hide();
                                    toastr["error"](obj.message, "Failed:");
                                }
                                else{
                                    $('#spinnerLoading').hide();
                                    toastr["error"]("Something wrong when activate", "Failed:");
                                }
                            });
                        }
                    } 
                    else {
                        alert('Please select at least one DO to post');
                    }   
                });

                // Show modal
                $('#viewModal').modal('show');
            }
        });
    }
    </script>

    <?php if ($canEditWeight || $canPrintWeight): ?>
    <!-- Weighing modal component (after the page script so its Select2 / date picker setup runs last) -->
    <?php include 'components/weighingModal/script.php'; ?>
    <?php endif; ?>

    <?php if ($canEditWeight): ?>
    <!-- Customer side info modal component -->
    <?php include 'components/customerSideInfoModal/script.php'; ?>
    <?php endif; ?>
</body>
</html>
