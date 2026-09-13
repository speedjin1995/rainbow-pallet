<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
require_once "php/requires/lookup.php";
if (!hasModulePermission('Accounting', 'Goods Received', ['view'])){
    header('Location: no-permission.php');
    exit;
}

$plantId = $_SESSION['plant'];
$selectedPlantId = $_SESSION['selected_plant_id'] ?? null;

$company = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
$vehicles = $db->query("SELECT DISTINCT veh_number FROM Vehicle WHERE status = '0' ORDER BY veh_number ASC");
$vehicles2 = $db->query("SELECT * FROM Vehicle WHERE status = '0' ORDER BY veh_number ASC");
$supplier = $db->query("SELECT * FROM Supplier WHERE status = '0' ORDER BY name ASC");
$supplier2 = $db->query("SELECT * FROM Supplier WHERE status = '0' ORDER BY name ASC");
$rawMaterial = $db->query("SELECT * FROM Raw_Mat WHERE status = '0' ORDER BY name ASC");
$rawMaterial2 = $db->query("SELECT * FROM Raw_Mat WHERE status = '0' ORDER BY name ASC");

$plantName = '-';
$plantCode = '-';
if (!hasModulePermission('Accounting', 'Goods Received', ['view_all_plants'])){
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
                                                            <select id="supplierSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <?php while($rowSF=mysqli_fetch_assoc($supplier2)){ ?>
                                                                    <option value="<?=$rowSF['supplier_code'] ?>"><?=$rowSF['name'] ?></option>
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
                                                                    <option value="<?=$rowRawMatF['raw_mat_code'] ?>"><?=$rowRawMatF['name'] ?></option>
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
                                                        <table id="weightTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                                    <th><?=$languageArray['company_code'][$language]?></th>
                                                                    <th><?=$languageArray['supplier_code'][$language]?></th>
                                                                    <th><?=$languageArray['plant_code'][$language]?></th>
                                                                    <th><?=$languageArray['raw_material_code'][$language]?></th>
                                                                    <th><?=$languageArray['received_date_code'][$language]?></th>
                                                                    <th><?=$languageArray['total_received_amount_code'][$language]?> (KG)</th>
                                                                    <!-- <th>Action</th> -->
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
                $.post('php/getWeight.php', { userID: row.data().id, fromDate: fromDateI, toDate: toDateI, format: 'EXPANDABLE', acctType: 'GR' }, function (data) {
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
            $('#spinnerLoading').show();
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();
            var companyI = $('#companySearch').val() || '';
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
                    $.post('php/modules/goodsReceived/postGr.php', {
                        fromDate: fromDateI,
                        toDate: toDateI,
                        company: companyI,
                        supplier: supplierNoI,
                        rawMat: rawMatI,
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

                //$('#spinnerLoading').hide();
            } 
            else {
                if (confirm('Are you sure you want to post to SQL?')) {
                    $.post('php/modules/goodsReceived/postGr.php', {
                        fromDate: fromDateI,
                        toDate: toDateI,
                        company: companyI,
                        supplier: supplierNoI,
                        rawMat: rawMatI,
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

                //$('#spinnerLoading').hide();
            }     
        });

        // Export Excel
        $('#exportExcel').on('click', function () {
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();
            var companyI = $('#companySearch').val() || '';
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
                window.open("php/modules/goodsReceived/exportExcel.php?&isMulti=Y&fromDate="+fromDateI+"&toDate="+toDateI+"&company="+companyI+"&supplier="+supplierNoI+
                "&rawMaterial="+rawMatI+"&plant="+plantI+"&purchaseOrder="+poI+"&transactionId="+transactionIdI+"&id="+selectedIds);
            } 
            else {
                window.open("php/modules/goodsReceived/exportExcel.php?&isMulti=N&fromDate="+fromDateI+"&toDate="+toDateI+"&company="+companyI+"&supplier="+supplierNoI+"&rawMaterial="+rawMatI+"&plant="+plantI+"&purchaseOrder="+poI+"&transactionId="+transactionIdI);
            }     
        });
    });

    function renderTable() {
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();
        var companyI = $('#companySearch').val() || '';
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
            "responsive": true,
            "autoWidth": false,
            'processing': true,
            'serverSide': true,
            'searching': false,
            'serverMethod': 'post',
            'ajax': {
                'url': 'php/modules/goodsReceived/filterGr.php',
                'data': {
                    fromDate: fromDateI,
                    toDate: toDateI,
                    company: companyI,
                    supplier: supplierNoI,
                    rawMaterial: rawMatI,
                    plant: plantI,
                    purchaseOrder: poI,
                    transactionId: transactionIdI,
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
                { data: 'supplier_name' },
                { data: 'plant_name' },
                { data: 'raw_mat_name' },
                { data: 'transaction_date' },
                { data: 'total_final_weight' }
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
    </script>
</body>
</html>