<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
require_once "php/requires/lookup.php";
if (!hasModulePermission('Accounting', 'Delivery Order', ['view'])){
    header('Location: no-permission.php');
    exit;
}

$plantId = $_SESSION['plant'];
$selectedPlantId = $_SESSION['selected_plant_id'] ?? null;

$company = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
$vehicles = $db->query("SELECT DISTINCT veh_number FROM Vehicle WHERE status = '0' ORDER BY veh_number ASC");
$vehicles2 = $db->query("SELECT * FROM Vehicle WHERE status = '0' ORDER BY veh_number ASC");
$customer = $db->query("SELECT * FROM Customer WHERE status = '0' ORDER BY name ASC");
$customer2 = $db->query("SELECT * FROM Customer WHERE status = '0' ORDER BY name ASC");
$product = $db->query("SELECT * FROM Product WHERE status = '0' ORDER BY name ASC");
$product2 = $db->query("SELECT * FROM Product WHERE status = '0' ORDER BY name ASC");

$plantName = '-';
$plantCode = '-';
if (!hasModulePermission('Accounting', 'Delivery Order', ['view_all_plants'])){
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
                                                                    <option value="<?=$rowProductF['product_code'] ?>"><?=$rowProductF['name'] ?></option>
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

    <script type="text/javascript">
    var userRole = '<?=$_SESSION["roles"] ?>';
    var table = null;
    var permissions = <?= json_encode($_SESSION['permissions']) ?>;
    var isSADMIN = <?= json_encode($_SESSION['roles'] == 'SADMIN') ?>;

    $(function () {
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
            } else {
                $.post('php/getWeight.php', { userID: row.data().id, fromDate: fromDateI, toDate: toDateI, format: 'EXPANDABLE', acctType: 'DO' }, function (data) {
                    var obj = JSON.parse(data);
                    if (obj.status === 'success') {
                        row.child(format(obj.message)).show();
                        tr.addClass("shown");
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
                    $.post('php/modules/deliveryOrder/postDo.php', {
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
                    $.post('php/modules/deliveryOrder/postDo.php', {
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
                window.open("php/modules/deliveryOrder/exportExcel.php?isMulti=Y&fromDate="+fromDateI+"&toDate="+toDateI+"&company="+companyI+
                "&customer="+customerNoI+"&product="+productI+"&plant="+plantI+"&deliveryNo="+deliveryNoI+"&transactionId="+transactionIdI+"&id="+selectedIds);
            } 
            else {
                window.open("php/modules/deliveryOrder/exportExcel.php?isMulti=N&fromDate="+fromDateI+"&toDate="+toDateI+"&company="+companyI+
                "&customer="+customerNoI+"&product="+productI+"&plant="+plantI+"&deliveryNo="+deliveryNoI+"&transactionId="+transactionIdI);
            }     
        });
    });

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
                'url': 'php/modules/deliveryOrder/filterDeliveryOrder.php',
                'data': {
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
                    data: 'id',
                    class: 'action-button',
                    orderable: false,
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

    function format(row) {
        var returnString = `
        <!-- Weighing Section -->
        <div class="row">
            <p><span><strong style="font-size:120%; text-decoration: underline;"><?=$languageArray['delivery_order_information_code'][$language]?></strong></span><br>
            <div class="col-4">
                <p><strong class="text-uppercase"><?=$languageArray['total_delivery_amount_code'][$language]?>:</strong> ${parseFloat(row.totalDeliverAmt)/1000} MT</p>
            </div>`;
        
        if (isSADMIN) {
            returnString += `
            <div class="col-4">
                <p><strong class="text-uppercase"><?=$languageArray['unit_price_code'][$language]?>:</strong> RM ${row.weights[0].unit_price}</p>
            </div>
            <div class="col-4">
                <p><strong class="text-uppercase"><?=$languageArray['total_price_code'][$language]?>:</strong> RM ${parseFloat(parseFloat(row.weights[0].unit_price) * (parseFloat(row.totalDeliverAmt)/1000)).toFixed(2)}</p>
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

    function print(id) {
        $.post('php/print.php', {userID: id, file: 'weight'}, function(data){
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
        $.post('php/getWeight.php', { userID: id, fromDate: fromDateI, toDate: toDateI, format: 'EXPANDABLE', acctType: 'DO' }, function (data) {
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
                            <td>${w.transaction_id}</td>
                            <td>${w.delivery_no}</td>
                            <td>${w.lorry_plate_no1}</td>
                            <td>${w.transporter}</td>
                            <td>${w.destination}</td>
                            <td>${(parseFloat(w.gross_weight1) / 1000).toFixed(2)} MT</td>
                            <td>${w.gross_weight1_date}</td>
                            <td>${(parseFloat(w.tare_weight1) / 1000).toFixed(2)} MT</td>
                            <td>${w.tare_weight1_date}</td>
                            <td>${(parseFloat(w.nett_weight1) / 1000).toFixed(2)} MT</td>
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
                            $.post('php/modules/deliveryOrder/postDo.php', {
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
</body>
</html>