<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
require_once "php/requires/lookup.php";

$plantId = $_SESSION['plant'];
$selectedPlantId = $_SESSION['selected_plant_id'];

$customer = $db->query("SELECT * FROM Customer WHERE status = '0' ORDER BY name ASC");
$product = $db->query("SELECT * FROM Product WHERE status = '0'");
$transporter = $db->query("SELECT * FROM Transporter WHERE status = '0'");
$destination = $db->query("SELECT * FROM Destination WHERE status = '0'");

$plantName = '-';
$plantCode = '-';
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
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

    <title><?=$languageArray['report_code'][$language]?> | Synctronix - Weighing System</title>
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

<!-- <div class="loading" id="spinnerLoading" style="display:none">
  <div class='mdi mdi-loading' style='transform:scale(0.79);'>
    <div></div>
  </div>
</div> -->

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
                                                            <label for="transactionStatusSearch" class="form-label"><?=$languageArray['transaction_status_code'][$language]?></label>
                                                            <select id="transactionStatusSearch" class="form-select select2">
                                                                <option value="Port" selected><?=$languageArray['trx_to_port_code'][$language]?></option>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3" id="customerSearchDisplay">
                                                        <div class="mb-3">
                                                            <label for="customerNoSearch" class="form-label"><?=$languageArray['customer_name_code'][$language]?></label>
                                                            <select id="customerNoSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <?php while($rowPF = mysqli_fetch_assoc($customer)){ ?>
                                                                    <option value="<?=$rowPF['customer_code'] ?>"><?=$rowPF['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="vehicleNo" class="form-label"><?=$languageArray['vehicle_no_code'][$language]?></label>
                                                            <input type="text" class="form-control" placeholder="<?=$languageArray['vehicle_no_code'][$language]?>" id="vehicleNo">
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="invoiceNoSearch" class="form-label"><?=$languageArray['weighing_type_code'][$language]?></label>
                                                            <select id="invoiceNoSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <option value="Normal"><?=$languageArray['normal_weighing_code'][$language]?></option>
                                                                <!-- <option value="Container">Primer Mover</option> -->
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->                                               
                                                    <!--<div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="customerTypeSearch" class="form-label">Customer Type</label>
                                                            <select id="customerTypeSearch" class="form-select">
                                                                <option selected>-</option>
                                                                <option value="Cash">Cash</option>
                                                                <option value="Normal">Normal</option>
                                                            </select>
                                                        </div>
                                                    </div>--><!--end col-->
                                                    <div class="col-3" id="productSearchDisplay">
                                                        <div class="mb-3">
                                                            <label for="ForminputState" class="form-label"><?=$languageArray['product_code_code'][$language]?></label>
                                                            <select id="productSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <?php while($rowProductF=mysqli_fetch_assoc($product)){ ?>
                                                                    <option value="<?=$rowProductF['product_code'] ?>"><?=$rowProductF['product_code'] .' - '. $rowProductF['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="destinationSearch" class="form-label"><?=$languageArray['destination_code'][$language]?></label>
                                                            <select id="destinationSearch" class="form-select select2">
                                                                <option selected>-</option>
                                                                <?php while($rowDestination=mysqli_fetch_assoc($destination)){ ?>
                                                                    <option value="<?=$rowDestination['name'] ?>" data-code="<?=$rowDestination['destination_code'] ?>"><?=$rowDestination['name'] ?></option>
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
                                                            <label for="statusSearch" class="form-label"><?=$languageArray['status_code'][$language]?></label>
                                                            <select id="statusSearch" class="form-select select2">
                                                                <option value="Complete" selected><?=$languageArray['complete_code'][$language]?></option>
                                                                <option value="Cancelled"><?=$languageArray['cancelled_code'][$language]?></option>
                                                            </select>
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
                                <div class="col">
                                    <div class="h-100">
                                        <!--datatable--> 
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card">
                                                    <div class="card-header" style="background-color: #405189;">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <h5 class="card-title text-white mb-0"><?=$languageArray['weighing_records_code'][$language]?></h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <button type="button" id="exportPdf" class="btn btn-danger waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#exportPdfModal">
                                                                    <i class="ri-file-pdf-line align-middle me-1"></i>
                                                                    <?=$languageArray['export_pdf_code'][$language]?>
                                                                </button>
                                                                <button type="button" id="exportExcel" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#exportExcelModal">
                                                                    <i class="ri-file-excel-line align-middle me-1"></i>
                                                                    <?=$languageArray['export_excel_code'][$language]?>
                                                                </button>
                                                            </div> 
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="weightTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
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
                                                                    <th><?=$languageArray['vehicle_code'][$language]?>2</th>
                                                                    <th><?=$languageArray['gross_incoming_code'][$language]?>2</th>
                                                                    <th><?=$languageArray['incoming_date_code'][$language]?>2</th>
                                                                    <th><?=$languageArray['tare_outgoing_code'][$language]?>2</th>
                                                                    <th><?=$languageArray['outgoing_date_code'][$language]?>2</th>
                                                                    <th><?=$languageArray['nett_weight_code'][$language]?>2</th>
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
    
    <div class="modal fade" id="exportExcelModal" tabindex="-1" role="dialog" aria-labelledby="exportExcelModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable custom-xxl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportExcelModalTitle"><?=$languageArray['export_weighing_records_code'][$language]?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exportExcelForm" class="needs-validation" novalidate autocomplete="off">
                        <div class="row col-12">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="excelReportType" class="col-sm-4 col-form-label"><?=$languageArray['report_type_code'][$language]?> *</label>
                                                    <div class="col-sm-8">
                                                        <select id="excelReportType" name="reportType" class="form-select" required>
                                                            <option value="SUMMARY"><?=$languageArray['summary_report_code'][$language]?></option>
                                                        </select>   
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-12">
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                <button type="submit" class="btn btn-success" id="submitExcel"><?=$languageArray['submit_code'][$language]?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exportPdfModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable custom-xxl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalScrollableTitle"><?=$languageArray['export_weighing_records_code'][$language]?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form id="exportPdfForm" class="needs-validation" novalidate autocomplete="off">
                        <div class="row col-12">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <input type="hidden" class="form-control" id="id" name="id"> 
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="reportType" class="col-sm-4 col-form-label"><?=$languageArray['report_type_code'][$language]?> *</label>
                                                    <div class="col-sm-8">
                                                        <select id="reportType" name="reportType" class="form-select" required>
                                                            <!-- <option value="CUSTOMER">Customer Report</option> -->
                                                            <option value="SUMMARY"><?=$languageArray['summary_report_code'][$language]?></option>
                                                            <option value="PRODUCT"><?=$languageArray['product_report_code'][$language]?></option>
                                                            <option value="S&P"><?=$languageArray['sales_purchase_report_code'][$language]?> - <?=$languageArray['product_code'][$language]?></option>
                                                            <option value="S&PC"><?=$languageArray['sales_purchase_report_code'][$language]?> - <?=$languageArray['customer_code'][$language]?></option>
                                                        </select>   
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" class="form-control" id="fromDate" name="fromDate">                                   
                                            <input type="hidden" class="form-control" id="toDate" name="toDate">                                   
                                            <input type="hidden" class="form-control" id="transactionStatus" name="transactionStatus">                                   
                                            <input type="hidden" class="form-control" id="customer" name="customer">     
                                            <input type="hidden" class="form-control" id="supplier" name="supplier"> 
                                            <input type="hidden" class="form-control" id="vehicle" name="vehicle">     
                                            <input type="hidden" class="form-control" id="weighingType" name="weighingType">     
                                            <input type="hidden" class="form-control" id="customerType" name="customerType">     
                                            <input type="hidden" class="form-control" id="product" name="product">  
                                            <input type="hidden" class="form-control" id="rawMat" name="rawMat">   
                                            <input type="hidden" class="form-control" id="destination" name="destination">     
                                            <input type="hidden" class="form-control" id="plant" name="plant">   
                                            <input type="hidden" class="form-control" id="status" name="status">                                     
                                            <input type="hidden" class="form-control" id="file" name="file">     
                                            <input type="hidden" class="form-control" id="isMulti" name="isMulti">     
                                            <input type="hidden" class="form-control" id="ids" name="ids">     
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-12">
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                <button type="submit" class="btn btn-success" id="submit"><?=$languageArray['submit_code'][$language]?></button>
                            </div>
                        </div><!--end col-->                                                               
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="prePrintModal">
        <div class="modal-dialog modal-xl" style="max-width: 90%;">
            <div class="modal-content">
                <form role="form" id="prePrintForm">
                    <div class="modal-header bg-gray-dark color-palette">
                        <h4 class="modal-title"><?=$languageArray['language_code'][$language]?></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <label for="prePrint" class="col-sm-4 col-form-label"><?=$languageArray['language_code'][$language]?></label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <div class="col-12">
                                        <select class="form-select select2" id="prePrint" name="prePrint" >
                                            <option value="en">English</option>
                                            <option value="zh">Chinese</option>
                                            <option value="my">Bahasa Malaysia</option>
                                            <option value="ne">नेपाली</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3" id="printTemplateDisplay">
                            <label for="printTemplate" class="col-sm-4 col-form-label"><?=$languageArray['print_template_code'][$language]?></label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <div class="col-12">
                                        <select class="form-select select2" id="printTemplate" name="printTemplate" >
                                            <option value="with_weight" selected><?=$languageArray['with_weight_code'][$language]?></option>
                                            <option value="without_weight"><?=$languageArray['without_weight_code'][$language]?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                            
                        <input type="hidden" class="form-control" id="isEmptyContainer" name="isEmptyContainer">
                        <input type="hidden" class="form-control" id="prePrintTransactionStatus" name="prePrintTransactionStatus">
                        <input type="hidden" class="form-control" id="id" name="id">
                    </div>
                    <div class="modal-footer justify-content-between bg-gray-dark color-palette">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                        <button type="button" class="btn btn-success" id="submitPrePrint"><?=$languageArray['submit_code'][$language]?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

    <script type="text/javascript">
    
    var fromDateSearchPicker;
    var toDateSearchPicker;
    var table = null;

    $(function () {
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
            defaultDate: yesterday
        });

        toDateSearchPicker = $('#toDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: today
        });

        $('#transactionDate').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: today
        });

        // Clear All Filter Function
        $('#clearAllSearch').on('click', function(){
            fromDateSearchPicker.setDate(yesterday);
            toDateSearchPicker.setDate(today);
            $('#transactionStatusSearch').val('Port').trigger('change');
            $('#customerNoSearch').val('-').trigger('change');
            $('#vehicleNo').val('');
            $('#invoiceNoSearch').val('-').trigger('change');
            $('#productSearch').val('-').trigger('change');
            $('#destinationSearch').val('-').trigger('change');
            $('#plantSearch').val('-').trigger('change');
            $('#statusSearch').val('Complete').trigger('change');
            $('#invDelPoSearch').val('');
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

        $('#exportPdfForm').on('submit', function(e) {
            e.preventDefault();
            $('#exportPdfModal').modal('hide');

            $.post('php/exportPdf.php', $(this).serialize(), function(response){
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
                    toastr["error"]("Something wrong when exporting", "Failed:");
                }
            }).fail(function(error){
                console.error("Error exporting PDF:", error);
                toastr["error"]("An error occurred while generating the PDF.", "Failed:");
            });
        });

        $('#exportPdf').on('click', function(){
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();
            var transactionStatusI = $('#transactionStatusSearch').val() || '';
            var customerNoI = $('#customerNoSearch').val() || '';
            var vehicleNoI = $('#vehicleNo').val() || '';
            var weightTypeI = $('#invoiceNoSearch').val() || '';
            var customerTypeI = $('#customerTypeSearch').val() || '';
            var productI = $('#productSearch').val() || '';
            var destinationI = $('#destinationSearch').val() || '';
            var plantI = $('#plantSearch').val() || '';
            var statusI = $('#statusSearch').val() || '';

            $('#exportPdfForm').find('#fromDate').val(fromDateI);
            $('#exportPdfForm').find('#toDate').val(toDateI);
            $('#exportPdfForm').find('#transactionStatus').val(transactionStatusI);
            $('#exportPdfForm').find('#customer').val(customerNoI);
            $('#exportPdfForm').find('#vehicle').val(vehicleNoI);
            $('#exportPdfForm').find('#weighingType').val(weightTypeI);
            $('#exportPdfForm').find('#customerType').val(customerTypeI);
            $('#exportPdfForm').find('#product').val(productI);
            $('#exportPdfForm').find('#destination').val(destinationI);
            $('#exportPdfForm').find('#plant').val(plantI);
            $('#exportPdfForm').find('#status').val(statusI);
            $('#exportPdfForm').find('#file').val('weight');

            var selectedIds = [];
            $("#weightTable tbody input[type='checkbox']").each(function () {
                if (this.checked) {
                    selectedIds.push($(this).val());
                }
            });

            if (selectedIds.length > 0){
                $('#exportPdfForm').find('#isMulti').val('Y');
                $('#exportPdfForm').find('#ids').val(selectedIds);
            } else {
                $('#exportPdfForm').find('#isMulti').val('N');
            }
        });

        $('#exportExcelForm').on('submit', function(e) {
            e.preventDefault();
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();
            var transactionStatusI = $('#transactionStatusSearch').val() || '';
            var customerNoI = $('#customerNoSearch').val() || '';
            var supplierNoI = $('#supplierSearch').val() || '';
            var vehicleNoI = $('#vehicleNo').val() || '';
            var weightTypeI = $('#invoiceNoSearch').val() || '';
            var productI = $('#productSearch').val() || '';
            var rawMatI = $('#rawMatSearch').val() || '';
            var destinationI = $('#destinationSearch').val() || '';
            var plantI = $('#plantSearch').val() || '';
            var statusI = $('#statusSearch').val() || '';
            var reportTypeI = $('#excelReportType').val() || '';
            
            var selectedIds = [];
            $("#weightTable tbody input[type='checkbox']").each(function () {
                if (this.checked) {
                    selectedIds.push($(this).val());
                }
            });

            var isMulti = selectedIds.length > 0 ? 'Y' : 'N';
            var url = "php/export.php?file=weight&fromDate="+encodeURIComponent(fromDateI)+"&toDate="+encodeURIComponent(toDateI)+
                "&transactionStatus="+encodeURIComponent(transactionStatusI)+"&customer="+encodeURIComponent(customerNoI)+"&supplier="+encodeURIComponent(supplierNoI)+"&vehicle="+encodeURIComponent(vehicleNoI)+
                "&weighingType="+encodeURIComponent(weightTypeI)+"&product="+encodeURIComponent(productI)+"&rawMat="+encodeURIComponent(rawMatI)+
                "&destination="+encodeURIComponent(destinationI)+"&plant="+encodeURIComponent(plantI)+"&status="+encodeURIComponent(statusI)+
                "&reportType="+encodeURIComponent(reportTypeI)+"&isMulti="+isMulti;

            if (selectedIds.length > 0) {
                url += "&ids="+encodeURIComponent(selectedIds);
            }

            window.open(url);
            $('#exportExcelModal').modal('hide');
        });

        $('#submitPrePrint').on('click', function(){
            if($('#prePrintForm').valid()){
                $('#spinnerLoading').show();
                var id = $('#prePrintModal').find('#id').val();
                var prePrintStatus = $('#prePrintModal').find('#prePrint').val();
                var isEmptyContainer = $('#prePrintModal').find('#isEmptyContainer').val();
                var printTemplate = $('#prePrintModal').find('#printTemplate').val();
                var transactionStatus = $('#prePrintModal').find('#prePrintTransactionStatus').val();
                $.post('php/print.php', {userID: id, file: 'weight', prePrint: prePrintStatus, isEmptyContainer: isEmptyContainer, printTemplate: printTemplate, transactionStatus: transactionStatus}, function(data){
                    var obj = JSON.parse(data);

                    if(obj.status === 'success'){
                        var printWindow = window.open('', '', 'height=' + screen.height + ',width=' + screen.width);
                        printWindow.document.write(obj.message);
                        printWindow.document.close();
                        setTimeout(function(){
                            printWindow.print();
                            printWindow.close();
                        }, 500);

                        $("#prePrintModal").modal("hide");
                        $('#spinnerLoading').hide();
                    }
                    else if(obj.status === 'failed'){
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                    else{
                        $("#failBtn").attr('data-toast-text', "Something wrong when print");
                        $("#failBtn").click();
                    }
                });
            }
        });
    });

    function renderTable() {
        var fromDateI = $('#fromDateSearch').val();
        var toDateI = $('#toDateSearch').val();
        var transactionStatusI = $('#transactionStatusSearch').val() || '';
        var customerNoI = $('#customerNoSearch').val() || '';
        var supplierNoI = $('#supplierSearch').val() || '';
        var vehicleNoI = $('#vehicleNo').val() || '';
        var weightTypeI = $('#invoiceNoSearch').val() || '';
        var customerTypeI = $('#customerTypeSearch').val() || '';
        var productI = $('#productSearch').val() || '';
        var rawMatI = $('#rawMatSearch').val() || '';
        var destinationI = $('#destinationSearch').val() || '';
        var plantI = $('#plantSearch').val() || '';
        var statusI = $('#statusSearch').val() || '';
        var invDelPoI = $('#invDelPoSearch').val() || '';

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
            'searching': true,
            'serverMethod': 'post',
            'ajax': {
                'url': 'php/filterReports.php',
                'data': {
                    fromDate: fromDateI,
                    toDate: toDateI,
                    transactionStatus: transactionStatusI,
                    customer: customerNoI,
                    supplier: supplierNoI,
                    vehicle: vehicleNoI,
                    weightType: weightTypeI,
                    customerType: customerTypeI,
                    product: productI,
                    rawMaterial: rawMatI,
                    destination: destinationI,
                    plant: plantI,
                    status: statusI,
                    invDelPo: invDelPoI
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
                    render: function (data, type, row) {
                        return '<div class="dropdown d-inline-block"><button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
                            '<i class="ri-more-fill align-middle"></i></button><ul class="dropdown-menu dropdown-menu-end">' +
                            '<li><a class="dropdown-item print-item-btn" id="print' + data + '" onclick="print(' + data + ')"><i class="ri-printer-fill align-bottom me-2 text-muted"></i> Print</a></li></ul></div>';
                    }
                }
            ]
        });
    }

    function preparePrePrintModal(id, transactionStatus, isEmptyContainer) {
        $('#prePrintModal').find('#id').val(id);
        $('#prePrintModal').find('#isEmptyContainer').val(isEmptyContainer);
        $('#prePrintModal').find('#prePrintTransactionStatus').val(transactionStatus);
        $('#prePrintModal').find('#prePrint').val("<?=$language ?>");
        $('#prePrintModal').find('#printTemplate').val("with_weight");

        if (transactionStatus == 'Purchase' || isEmptyContainer == 'Y') {
            $('#prePrintModal').find('#printTemplateDisplay').hide();
        } else {
            $('#prePrintModal').find('#printTemplateDisplay').show();
        }
    }

    function print(id, transactionStatus, isEmptyContainer = 'N') {
        preparePrePrintModal(id, transactionStatus, isEmptyContainer);
        $("#prePrintModal").modal("show");

        $('#prePrintForm').validate({
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
    </script>
</body>
</html>
