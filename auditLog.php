<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
$companyId = $_SESSION['company_id'];
$viewAllCompanies = hasModulePermission('Reports', 'Audit Log', ['view_all_companies']);

if (!$viewAllCompanies) {
    $company_ids = implode(',', array_map('intval', $_SESSION['company_ids'] ?? [])) ?: '0';
    $company = $db->query("SELECT * FROM Company WHERE status = 0 AND id IN ($company_ids) ORDER BY name");
} else {
    $company = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
}
?>

<head>
    <title>Audit Log | Synctronix - Weighing System</title>
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
                        <div>
                            <div class="row mb-3 pb-1">
                                <div class="col-12">
                                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                                        <div class="flex-grow-1">
                                            <!--h4 class="fs-16 mb-1">Good Morning, Anna!</h4>
                                            <p class="text-muted mb-0">Here's what's happening with your store
                                                today.</p-->
                                        </div>
                                    </div><!-- end card header -->
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->

                            <div class="col-xxl-12 col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form action="javascript:void(0);">
                                            <div class="row">
                                                <div class="col-3">
                                                    <div class="mb-3">
                                                        <label for="fromDateSearch" class="form-label"><?=$languageArray['from_date_code'][$language]?></label>
                                                        <input type="date" class="form-control flatpickrStart" data-provider="flatpickr" id="fromDateSearch">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="mb-3">
                                                        <label for="toDateSearch" class="form-label"><?=$languageArray['to_date_code'][$language]?></label>
                                                        <input type="date" class="form-control flatpickrEnd" data-provider="flatpickr" id="toDateSearch">
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="mb-3">
                                                        <label for="reportType" class="form-label"><?=$languageArray['data_category_code'][$language]?></label>
                                                        <select id="reportType" name="reportType" class="form-select">
                                                            <option value="Company" selected><?=$languageArray['company_code'][$language]?></option>
                                                            <option value="Customer"><?=$languageArray['customer_code'][$language]?></option>
                                                            <option value="Supplier"><?=$languageArray['supplier_code'][$language]?></option>
                                                            <option value="Product Category"><?=$languageArray['product_category_code'][$language]?></option>
                                                            <option value="Unit"><?=$languageArray['units_code'][$language]?></option>
                                                            <option value="Product"><?=$languageArray['product_code'][$language]?></option>
                                                            <!-- <option value="Raw Materials"><?=$languageArray['raw_material_code'][$language]?></option> -->
                                                            <option value="Destination"><?=$languageArray['destination_code'][$language]?></option>
                                                            <option value="Location"><?=$languageArray['locations_code'][$language]?></option>
                                                            <option value="Vehicle"><?=$languageArray['vehicle_code'][$language]?></option>
                                                            <option value="Project"><?=$languageArray['project_code'][$language]?></option>
                                                            <!-- <option value="Transporter"><?=$languageArray['transporter_code'][$language]?></option> -->
                                                            <!-- <option value="Plant"><?=$languageArray['plant_code'][$language]?></option> -->
                                                            <option value="User"><?=$languageArray['staff_code'][$language]?></option>
                                                            <option value="Weight"><?=$languageArray['weighing_code'][$language]?></option>
                                                            <option value="Empty Container"><?=$languageArray['empty_container_code'][$language]?></option>
                                                            <option value="Sawn Timber"><?=$languageArray['sawn_timber_code'][$language]?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode companyInput">
                                                    <div class="mb-3">
                                                        <label for="companyCode" class="form-label"><?=$languageArray['company_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['company_code_code'][$language]?>" name="companyCode" id="companyCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode customerInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="customerCode" class="form-label"><?=$languageArray['customer_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['customer_code_code'][$language]?>" name="customerCode" id="customerCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode supplierInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="supplierCode" class="form-label"><?=$languageArray['supplier_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['supplier_code_code'][$language]?>" name="supplierCode" id="supplierCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode productCategoryInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="categoryName" class="form-label"><?=$languageArray['category_name_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['category_name_code'][$language]?>" name="categoryName" id="categoryName">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode unitInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="unit" class="form-label"><?=$languageArray['unit_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['unit_code'][$language]?>" name="unit" id="unit">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode productInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="productCode" class="form-label"><?=$languageArray['item_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['item_code_code'][$language]?>" name="productCode" id="productCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode destinationInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="destinationCode" class="form-label"><?=$languageArray['destination_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['destination_code_code'][$language]?>" name="destinationCode" id="destinationCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode locationInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="locationCode" class="form-label"><?=$languageArray['location_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['location_code_code'][$language]?>" name="locationCode" id="locationCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode vehicleInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="vehicleNo" class="form-label"><?=$languageArray['vehicle_no_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['vehicle_no_code'][$language]?>" name="vehicleNo" id="vehicleNo">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode projectInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="projectCode" class="form-label"><?=$languageArray['project_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['project_code_code'][$language]?>" name="projectCode" id="projectCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode userInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="userCode" class="form-label"><?=$languageArray['username_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['username_code'][$language]?>" name="userCode" id="userCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode weightInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="weight" class="form-label"><?=$languageArray['transaction_id_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['transaction_id_code'][$language]?>" name="weight" id="weight">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode emptyContainerInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="emptyContainer" class="form-label"><?=$languageArray['transaction_id_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['transaction_id_code'][$language]?>" name="emptyContainer" id="emptyContainer">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode sawnTimberInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="sawnTimber" class="form-label"><?=$languageArray['transaction_id_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['transaction_id_code'][$language]?>" name="sawnTimber" id="sawnTimber">
                                                    </div>
                                                </div>

                                                <!-- Hidden Log -->
                                                <div class="col-3 inputCode rawMatInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="rawMatCode" class="form-label"><?=$languageArray['raw_material_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['raw_material_code_code'][$language]?>" name="rawMatCode" id="rawMatCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode transporterInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="transporterCode" class="form-label"><?=$languageArray['transporter_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['transporter_code_code'][$language]?>" name="transporterCode" id="transporterCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode plantInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="plantCode" class="form-label"><?=$languageArray['plant_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['plant_code_code'][$language]?>" name="plantCode" id="plantCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode siteInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="siteCode" class="form-label">Site Code</label>
                                                        <input type="text" class="form-control" placeholder="Site Code" name="siteCode" id="siteCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode soInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="custPoNo" class="form-label">Customer P/O No</label>
                                                        <input type="text" class="form-control" placeholder="Customer P/O No" name="custPoNo" id="custPoNo">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode poInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="poNo" class="form-label">P/O No</label>
                                                        <input type="text" class="form-control" placeholder="P/O No" name="poNo" id="poNo">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <div id="companySearchDisplay" style="display:none">
                                                        <div class="mb-3" <?= !$viewAllCompanies ? "style='display:none'" : '' ?>>
                                                            <label for="companySearch" class="form-label"><?=$languageArray['company_code'][$language]?></label>
                                                            <select id="companySearch" class="form-select select2">
                                                                <option>-</option>
                                                                <?php while($rowCompany = mysqli_fetch_assoc($company)){ ?>
                                                                    <option value="<?=$rowCompany['id'] ?>" <?=($rowCompany['id'] == $companyId) ? 'selected' : ''?>><?=$rowCompany['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                </div>
                                                <div class="col-3">
                                                </div>                                                                                                                                                                                                                                                                                                                                        
                                                <div class="col-3">
                                                    <div class="text-end mt-4">
                                                        <button type="button" class="btn btn-success" id="searchLog">
                                                            <i class="bx bx-search-alt"></i>
                                                            <?=$languageArray['search_code'][$language]?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>                                                                        
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" hidden id="successBtn" data-toast data-toast-text="Welcome Back ! This is a Toast Notification" data-toast-gravity="top" data-toast-position="center" data-toast-duration="3000" data-toast-close="close" class="btn btn-light w-xs">Top Center</button>
                            <button type="button" hidden id="failBtn" data-toast data-toast-text="Welcome Back ! This is a Toast Notification" data-toast-gravity="top" data-toast-position="center" data-toast-duration="3000" data-toast-close="close" class="btn btn-light w-xs">Top Center</button>

                            <div class="row">
                                <div class="col-xl-3 col-md-6 add-new-weight">

                                    <!-- /.modal-dialog -->
                                    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalScrollableTitle"><?=$languageArray['add_new_code'][$language]?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form role="form" id="transporterForm" class="needs-validation" novalidate autocomplete="off">
                                                        <div class=" row col-12">
                                                            <div class="col-xxl-12 col-lg-12">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="transporterCode" class="col-sm-4 col-form-label">Transporter Code</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="transporterCode" name="transporterCode" placeholder="Transporter Code" required>
                                                                                        <div class="invalid-feedback">
                                                                                            Please fill in the field.
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="companyRegNo" class="col-sm-4 col-form-label">Company Reg No</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="companyRegNo" name="companyRegNo" placeholder="Company Reg No">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="companyName" class="col-sm-4 col-form-label">Company Name</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="companyName" name="companyName" placeholder="Customer Code">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="addressLine1" class="col-sm-4 col-form-label">Address Line 1</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="addressLine1" name="addressLine1" placeholder="Address Line 1">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="addressLine2" class="col-sm-4 col-form-label">Address Line 2</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="addressLine2" name="addressLine2" placeholder="Address Line 2">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="addressLine3" class="col-sm-4 col-form-label">Address Line 3</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="addressLine3" name="addressLine3" placeholder="Address Line 3">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="phoneNo" class="col-sm-4 col-form-label">Phone No</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="phoneNo" name="phoneNo" placeholder="Phone No">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="faxNo" class="col-sm-4 col-form-label">Fax No</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="faxNo" name="faxNo" placeholder="Fax No">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <input type="hidden" class="form-control" id="id" name="id">                                                                                                                                                         
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        
                                                        <div class="col-lg-12">
                                                            <div class="hstack gap-2 justify-content-end">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                                <button type="button" class="btn btn-success" id="submitTransporter"><?=$languageArray['submit_code'][$language]?></button>
                                                            </div>
                                                        </div><!--end col-->                                                               
                                                    </form>
                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->

                                </div>
                            </div> <!-- end row-->

                            <div class="row">
                                <div class="col">
                                    <div class="h-100">
                                        <!--datatable--> 
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <h5 class="card-title mb-0"><?=$languageArray['previous_records_code'][$language]?></h5>
                                                            </div>
                                                            <!-- <div class="flex-shrink-0">
                                                                <button type="button" id="addTransporter" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                                                <i class="ri-add-circle-line align-middle me-1"></i>
                                                                <?=$languageArray['add_new_code'][$language]?>
                                                                </button>
                                                            </div>  -->
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="dataTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr id="headerRow">
                                                                <!-- Column names will be dynamically updated here -->
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <!-- Table rows will be dynamically updated here -->
                                                            </tbody>
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

    <!--Swiper slider js-->
    <script src="assets/libs/swiper/swiper-bundle.min.js"></script>

    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard-ecommerce.init.js"></script>   
    <script src="assets/js/pages/form-validation.init.js"></script>
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



<script type="text/javascript">

var table;

$(function () {
    $('#companySearch').select2({
        width: '100%',
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

    $('#reportType').on('change', function(){
        // Company category lists the companies themselves, so no company filter
        if($(this).val() == "Company")
        {
            $('#companySearchDisplay').hide();
        }
        else
        {
            $('#companySearchDisplay').show();
        }

        if($(this).val() == "Company")
        {
            $('.inputCode').hide();
            $('.companyInput').show();
        }
        else if($(this).val() == "Customer")
        {
            $('.inputCode').hide();
            $('.customerInput').show();
        }
        else if($(this).val() == "Supplier")
        {
            $('.inputCode').hide();
            $('.supplierInput').show();
        }
        else if($(this).val() == "Product Category")
        {
            $('.inputCode').hide();
            $('.productCategoryInput').show();
        }
        else if($(this).val() == "Unit")
        {
            $('.inputCode').hide();
            $('.unitInput').show();
        }
        else if($(this).val() == "Product")
        {
            $('.inputCode').hide();
            $('.productInput').show();
        }
        // else if($(this).val() == "Raw Materials")
        // {
        //     $('.inputCode').hide();
        //     $('.rawMatInput').show();
        // }
        else if($(this).val() == "Destination")
        {
            $('.inputCode').hide();
            $('.destinationInput').show();
        }
        else if($(this).val() == "Location")
        {
            $('.inputCode').hide();
            $('.locationInput').show();
        }
        else if($(this).val() == "Vehicle")
        {
            $('.inputCode').hide();
            $('.vehicleInput').show();
        }
        else if($(this).val() == "Project")
        {
            $('.inputCode').hide();
            $('.projectInput').show();
        }
        else if($(this).val() == "Transporter")
        {
            $('.inputCode').hide();
            $('.transporterInput').show();
        }
        else if($(this).val() == "User")
        {
            $('.inputCode').hide();
            $('.userInput').show();
        }
        else if($(this).val() == "Plant")
        {
            $('.inputCode').hide();
            $('.plantInput').show();
        }
        else if($(this).val() == "Site")
        {
            $('.inputCode').hide();
            $('.siteInput').show();
        }
        else if($(this).val() == "Weight")
        {
            $('.inputCode').hide();
            $('.weightInput').show();
        }
        else if($(this).val() == "Empty Container")
        {
            $('.inputCode').hide();
            $('.emptyContainerInput').show();
        }
        else if($(this).val() == "Sawn Timber")
        {
            $('.inputCode').hide();
            $('.sawnTimberInput').show();
        }
        else if($(this).val() == "SO")
        {
            $('.inputCode').hide();
            $('.soInput').show();
        }
        else if($(this).val() == "PO")
        {
            $('.inputCode').hide();
            $('.poInput').show();
        }
        
    });

    var startDate = new Date();
    startDate.setDate(startDate.getDate() - 1);

    $(".flatpickrStart").flatpickr({
        defaultDate: new Date(startDate), 
        dateFormat: "d-m-Y"
    });

    $(".flatpickrEnd").flatpickr({
        defaultDate: new Date(), 
        dateFormat: "d-m-Y"
    });

    // Add event listener for opening and closing details on row click
    $('#dataTable tbody').on('click', 'tr', function (e) {
        var tr = $(this); // The row that was clicked
        var row = table.row(tr); 

        // Exclude specific td elements by checking the event target
        if ($(e.target).closest('td').hasClass('dtr-control') || $(e.target).closest('td').hasClass('action-button')) {
            return;
        }

        if ($('#reportType').val() == 'Weight' || $('#reportType').val() == 'Empty Container'){
            var logType = $('#reportType').val() == 'Empty Container' ? 'ContainerLog' : 'Log';

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                $.post('php/modules/weighing/index.php', { action: 'getWeight', userID: row.data().id, format: 'EXPANDABLE', type: logType }, function (data) {
                    var obj = JSON.parse(data);
                    if (obj.status === 'success') {
                        row.child(format(obj.message)).show();
                        tr.addClass("shown");
                    }
                });
            }
        }        
    });

    // Handle change event of the dropdown list
    $('#searchLog').click(function() {
        var selectedValue = $('#reportType').val();
        // Call a function to update the DataTable based on the selected value
        updateDataTable(selectedValue);
    });

    // Function to update the DataTable
    function updateDataTable(selectedValue) {
        $.ajax({
            url: "php/modules/report/index.php",
            type: "POST",
            data: {
                action: 'filterAuditLog',
                selectedValue: selectedValue,
                company: selectedValue == 'Company' ? '' : ($('#companySearch').val() || ''),
                fromDateSearch: $('#fromDateSearch').val(),
                toDateSearch: $('#toDateSearch').val(),
                companyCode: $('#companyCode').val(),
                customerCode: $('#customerCode').val(),
                destinationCode: $('#destinationCode').val(),
                productCode: $('#productCode').val(),
                rawMatCode: $('#rawMatCode').val(),
                supplierCode: $('#supplierCode').val(),
                vehicleNo: $('#vehicleNo').val(),
                transporterCode: $('#transporterCode').val(),
                unit: $('#unit').val(),
                productCategory: $('#categoryName').val(),
                locationCode: $('#locationCode').val(),
                projectCode: $('#projectCode').val(),
                userCode: $('#userCode').val(),
                plantCode: $('#plantCode').val(),
                siteCode: $('#siteCode').val(),
                weight: $('#weight').val(),
                emptyContainer: $('#emptyContainer').val(),
                sawnTimber: $('#sawnTimber').val(),
                custPoNo: $('#custPoNo').val(),
                poNo: $('#poNo').val(),
            },
            dataType: "json",
            success: function (response) {
                if (response.status === 'failed') {
                    toastr.error(response.message);
                    return;
                }

                if ($.fn.DataTable.isDataTable("#dataTable")) {
                    $("#dataTable").DataTable().destroy();
                }
                
                // Clear table headers and body
                $('#dataTable thead tr').empty();
                $('#dataTable tbody').empty();

                // Generate column definitions dynamically
                let columns = response.columnNames.map(column => ({
                    data: column,
                    title: column
                }));

                // Initialize DataTable with dynamic columns
                table = $("#dataTable").DataTable({
                    data: response.dataTable,
                    columns: columns,
                    responsive: true,
                    autoWidth: false,
                    processing: true,
                    searching: true
                });
            },
            error: function (error) {
                console.error("Error fetching data:", error);
            }
        });
    }
});

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
            <p><strong>${displayValue(row.name)}</strong></p>
            <p>${displayValue(row.address_line_1)}</p>
            <p>${displayValue(row.address_line_2)}</p>
            <p>${displayValue(row.address_line_3)}</p>
            <p>TEL: ${displayValue(row.phone_no)} FAX: ${displayValue(row.fax_no)}</p>
        </div>
    </div>
    <hr>
    <!-- Delivery Order Section -->
    <div class="row">
        <p><span><strong style="font-size:120%; text-decoration: underline;">Delivery Order Information</strong></span><br>
        <div class="col-6">
            <p><strong>COMPANY:</strong> ${displayValue(row.company_name)}</p>
            <p><strong>TRANSPORTER NAME:</strong> ${displayValue(row.transporter)}</p>
            <p><strong>DESTINATION NAME:</strong> ${displayValue(row.destination)}</p>
            <p><strong>PLANT NAME:</strong> ${displayValue(row.plant_name)}</p>`;
            if (row.transaction_status == 'Purchase' || row.transaction_status == 'Local'){
                returnString += `<p><strong>PURCHASE PRODUCT:</strong> ${displayValue(row.product_rawmat_name)}</p>`;
            }else{
                returnString += `<p><strong>SALES PRODUCT:</strong> ${displayValue(row.product_rawmat_name)}</p>`;
            }
    
        returnString += `
            <p><strong>PURCHASE ORDER:</strong> ${displayValue(row.purchase_order)}</p>
            <p><strong>CONTAINER NO:</strong> ${displayValue(row.container_no)}</p>
            <p><strong>CONTAINER NO 2:</strong> ${displayValue(row.container_no2)}</p>
        </div>
        <div class="col-6">
            <p><strong>TRANSACTION ID:</strong> ${displayValue(row.transaction_id)}</p>
            <p><strong>PROJECT:</strong> ${displayValue(row.project_code)}</p>
            <p><strong>WEIGHT STATUS:</strong> ${transactionStatus}</p>
            <p><strong>WEIGHT TYPE:</strong> ${weightType}</p>
            <p><strong>DELIVERY NO:</strong> ${displayValue(row.delivery_no)}</p>
            <p><strong>SEAL NO:</strong> ${displayValue(row.seal_no)}</p>
            <p><strong>SEAL NO 2:</strong> ${displayValue(row.seal_no2)}</p>
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
            <p><strong>VEHICLE PLATE:</strong> ${displayValue(row.lorry_plate_no1)}</p>
            <p><strong>IN WEIGHT:</strong> ${displayValue(row.gross_weight1)}</p>
            <p><strong>IN DATE / TIME:</strong> ${displayValue(row.gross_weight1_date)}</p>
            <p><strong>IN WEIGH BY:</strong> ${displayValue(row.gross_weight_by1)}</p>
            <p><strong>OUT WEIGHT:</strong> ${displayValue(row.tare_weight1)}</p>
            <p><strong>OUT DATE / TIME:</strong> ${displayValue(row.tare_weight1_date)}</p>
            <p><strong>OUT WEIGH BY:</strong> ${displayValue(row.tare_weight_by1)}</p>
            <p><strong>NETT WEIGHT:</strong> ${displayValue(row.nett_weight1)}</p>
            <p><strong>SUB TOTAL WEIGHT:</strong> ${displayValue(row.final_weight)}</p>
        </div>
        <!-- Container -->
        <div class="col-6">
            <p><strong>VEHICLE PLATE 2:</strong> ${displayValue(row.lorry_plate_no2)}</p>
            <p><strong>IN WEIGHT 2:</strong> ${displayValue(row.gross_weight2)}</p>
            <p><strong>IN DATE / TIME 2:</strong> ${displayValue(row.gross_weight2_date)}</p>
            <p><strong>IN WEIGH BY 2:</strong> ${displayValue(row.gross_weight_by2)}</p>
            <p><strong>OUT WEIGHT 2:</strong> ${displayValue(row.tare_weight2)}</p>
            <p><strong>OUT DATE / TIME 2:</strong> ${displayValue(row.tare_weight2_date)}</p>
            <p><strong>OUT WEIGH BY 2:</strong> ${displayValue(row.tare_weight_by2)}</p>
            <p><strong>NETT WEIGHT 2:</strong> ${displayValue(row.nett_weight2)}</p>            
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

</script>
    </body>

    </html>