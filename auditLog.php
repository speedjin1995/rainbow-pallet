<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

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
                                                        <select id="reportType" name="reportType" class="form-select" data-choices data-choices-sorting="true" >
                                                            <option value="Customer" selected><?=$languageArray['customer_code'][$language]?></option>
                                                            <option value="Destination"><?=$languageArray['destination_code'][$language]?></option>
                                                            <option value="Product"><?=$languageArray['product_code'][$language]?></option>
                                                            <option value="Raw Materials"><?=$languageArray['raw_material_code'][$language]?></option>
                                                            <option value="Supplier"><?=$languageArray['supplier_code'][$language]?></option>
                                                            <option value="Vehicle"><?=$languageArray['vehicle_code'][$language]?></option>
                                                            <option value="Transporter"><?=$languageArray['transporter_code'][$language]?></option>
                                                            <option value="User"><?=$languageArray['staff_code'][$language]?></option>
                                                            <option value="Weight"><?=$languageArray['weighing_code'][$language]?></option>
                                                            <option value="Plant"><?=$languageArray['plant_code'][$language]?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode customerInput">
                                                    <div class="mb-3">
                                                        <label for="customerCode" class="form-label"><?=$languageArray['customer_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['customer_code_code'][$language]?>" name="customerCode" id="customerCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode destinationInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="destinationCode" class="form-label"><?=$languageArray['destination_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['destination_code_code'][$language]?>" name="destinationCode" id="destinationCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode productInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="productCode" class="form-label"><?=$languageArray['product_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['product_code_code'][$language]?>" name="productCode" id="productCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode rawMatInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="rawMatCode" class="form-label"><?=$languageArray['raw_material_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['raw_material_code_code'][$language]?>" name="rawMatCode" id="rawMatCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode supplierInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="supplierCode" class="form-label"><?=$languageArray['supplier_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['supplier_code_code'][$language]?>" name="supplierCode" id="supplierCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode vehicleInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="vehicleNo" class="form-label"><?=$languageArray['vehicle_no_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['vehicle_no_code'][$language]?>" name="vehicleNo" id="vehicleNo">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode transporterInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="transporterCode" class="form-label"><?=$languageArray['transporter_code_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['transporter_code_code'][$language]?>" name="transporterCode" id="transporterCode">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode unitInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="unit" class="form-label">Unit</label>
                                                        <input type="text" class="form-control" placeholder="Unit" name="unit" id="unit">
                                                    </div>
                                                </div>
                                                <div class="col-3 inputCode userInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="userCode" class="form-label"><?=$languageArray['username_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['username_code'][$language]?>" name="userCode" id="userCode">
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
                                                <div class="col-3 inputCode weightInput" style="display:none">
                                                    <div class="mb-3">
                                                        <label for="weight" class="form-label"><?=$languageArray['transaction_id_code'][$language]?></label>
                                                        <input type="text" class="form-control" placeholder="<?=$languageArray['transaction_id_code'][$language]?>" name="weight" id="weight">
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
    $('#reportType').on('change', function(){
        if($(this).val() == "Customer")
        {
            $('.inputCode').hide();
            $('.customerInput').show();
        }
        else if($(this).val() == "Destination")
        {
            $('.inputCode').hide();
            $('.destinationInput').show();
        }
        else if($(this).val() == "Product")
        {
            $('.inputCode').hide();
            $('.productInput').show();
        }
        else if($(this).val() == "Raw Materials")
        {
            $('.inputCode').hide();
            $('.rawMatInput').show();
        }
        else if($(this).val() == "Supplier")
        {
            $('.inputCode').hide();
            $('.supplierInput').show();
        }
        else if($(this).val() == "Vehicle")
        {
            $('.inputCode').hide();
            $('.vehicleInput').show();
        }
        else if($(this).val() == "Transporter")
        {
            $('.inputCode').hide();
            $('.transporterInput').show();
        }
        else if($(this).val() == "Unit")
        {
            $('.inputCode').hide();
            $('.unitInput').show();
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
        dateFormat: "y-m-d"
    });

    $(".flatpickrEnd").flatpickr({
        defaultDate: new Date(), 
        dateFormat: "y-m-d"
    });

    // Add event listener for opening and closing details on row click
    $('#dataTable tbody').on('click', 'tr', function (e) {
        var tr = $(this); // The row that was clicked
        var row = table.row(tr); 

        // Exclude specific td elements by checking the event target
        if ($(e.target).closest('td').hasClass('dtr-control') || $(e.target).closest('td').hasClass('action-button')) {
            return;
        }

        if ($('#reportType').val() == 'Weight'){
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                $.post('php/getWeight.php', { userID: row.data().id, format: 'EXPANDABLE', type: 'Log' }, function (data) {
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
        // $.ajax({
        //     url: "php/filterAuditLog.php",
        //     type: "POST",
        //     data: { 
        //         selectedValue: selectedValue,
        //         fromDateSearch: $('#fromDateSearch').val(),
        //         toDateSearch: $('#toDateSearch').val(),
        //         customerCode: $('#customerCode').val(),
        //         destinationCode: $('#destinationCode').val(),
        //         productCode: $('#productCode').val(),
        //         rawMatCode: $('#rawMatCode').val(),
        //         supplierCode: $('#supplierCode').val(),
        //         vehicleNo: $('#vehicleNo').val(),
        //         transporterCode: $('#transporterCode').val(),
        //         unit: $('#unit').val(),
        //         userCode: $('#userCode').val(),
        //         plantCode: $('#plantCode').val(),
        //         siteCode: $('#siteCode').val(),
        //         weight: $('#weight').val(),
        //         custPoNo: $('#custPoNo').val(),
        //         poNo: $('#poNo').val(),
        //     },
        //     success: function(data) {

        //         if (table) {
        //             table.destroy();
        //         }
        //         // Once you receive the updated DataTable from the server, update the HTML table
        //         var dataTable = data.dataTable;
        //         var columnNames = data.columnNames;

        //         var headerRow = $("#headerRow");
        //         headerRow.empty();

        //         // Update the column names
        //         $.each(columnNames, function(index, columnName) {
        //         var th = $("<th>").text(columnName);
        //         headerRow.append(th);
        //         });

        //         var tableBody = $("#dataTable tbody");
        //         tableBody.empty();

        //         $.each(dataTable, function(index, item) {
        //         var row = $("<tr>");
        //         $.each(columnNames, function(index, columnName) {
        //             var cell = $("<td>").text(item[columnName]);
        //             row.append(cell);
        //         });
        //         tableBody.append(row);
        //         });

        //         // table.draw();
        //         table = $("#dataTable").DataTable();
        //     },
        //     error: function(error) {
        //         console.log("Error occurred while fetching the updated DataTable.");
        //     }
        // });

        $.ajax({
            url: "php/filterAuditLog.php",
            type: "POST",
            data: {
                selectedValue: selectedValue,
                fromDateSearch: $('#fromDateSearch').val(),
                toDateSearch: $('#toDateSearch').val(),
                customerCode: $('#customerCode').val(),
                destinationCode: $('#destinationCode').val(),
                productCode: $('#productCode').val(),
                rawMatCode: $('#rawMatCode').val(),
                supplierCode: $('#supplierCode').val(),
                vehicleNo: $('#vehicleNo').val(),
                transporterCode: $('#transporterCode').val(),
                unit: $('#unit').val(),
                userCode: $('#userCode').val(),
                plantCode: $('#plantCode').val(),
                siteCode: $('#siteCode').val(),
                weight: $('#weight').val(),
                custPoNo: $('#custPoNo').val(),
                poNo: $('#poNo').val(),
            },
            dataType: "json",
            success: function (response) {
                if ($.fn.DataTable.isDataTable("#dataTable")) {
                    $("#dataTable").DataTable().destroy();
                }

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
    var custSupplier = '';
    var productRawMat = '';
    var orderSuppWeight = '';

    if (row.transaction_status == 'Sales'){
        custSupplier = row.customer_code + '-' + row.customer_name;
        productRawMat = row.product_code + '-' + row.product_name;
        orderSuppWeight = row.order_weight;
    }else{
        custSupplier = row.supplier_code + '-' + row.supplier_name;
        productRawMat = row.raw_mat_code + '-' + row.raw_mat_name;
        orderSuppWeight = row.supplier_weight;
    }

    var returnString = `
    <!-- Weighing Section -->
    <div class="row">
        <div class="col-3">
            <p><strong>TRANSACTION ID:</strong> ${row.transaction_id}</p>
            <p><strong>CUSTOMER TYPE:</strong> ${row.weight_type}</p>
            <p><strong>WEIGHT STATUS:</strong> ${row.transaction_status}</p>
            <p><strong>TRANSACTION DATE:</strong> ${row.transaction_date}</p>
            <p><strong>INVOICE NO:</strong> ${row.invoice_no}</p>
            <p><strong>MANUAL WEIGHT:</strong> ${row.manual_weight}</p>
            <p><strong>DELIVERY NO:</strong> ${row.delivery_no}</p>
            <p><strong>SO/PO NO:</strong> ${row.purchase_order}</p>
        </div>
        <div class="col-3">
            <p><strong>CONTAINER NO:</strong> ${row.container_no}</p>
            <p><strong>CUSTOMER/SUPPLIER:</strong> ${custSupplier}</p>
            <p><strong>PRODUCT/RAW MATERIAL:</strong> ${productRawMat}</p>
            <p><strong>TRANSPORTER:</strong> ${row.transporter_code} - ${row.transporter}</p>
            <p><strong>DESTINATION:</strong> ${row.destination_code} - ${row.destination}</p>
            <p><strong>PLANT:</strong> ${row.plant_code} - ${row.plant_name}</p>
        </div>
        <div class="col-3">
            <p><strong>ORDER/SUPPLIER WEIGHT:</strong> ${orderSuppWeight}</p>
            <p><strong>WEIGHT DIFFERENCE:</strong> ${row.reduce_weight}</p>
            <p><strong>UNIT PRICE:</strong> ${row.unit_price}</p>
            <p><strong>SUB-TOTAL PRICE:</strong> ${row.sub_total}</p>
            <p><strong>SST (6%):</strong> ${row.sst}</p>
            <p><strong>TOTAL PRICE:</strong> ${row.total_price}</p>
        </div>
        <div class="col-3">
            <p><strong>VEHICLE PLATE:</strong> ${row.lorry_plate_no1}</p>
            <p><strong>IN WEIGHT:</strong> ${row.gross_weight1} KG</p>
            <p><strong>IN DATE/TIME:</strong> ${row.gross_weight1_date}</p>
            <p><strong>OUT WEIGHT:</strong> ${row.tare_weight1} KG</p>
            <p><strong>OUT DATE/TIME:</strong> ${row.tare_weight1_date}</p>
            <p><strong>NETT WEIGHT:</strong> ${row.nett_weight1} KG</p>
            <p><strong>REMARK:</strong> ${row.remarks}</p>
        </div>
    </div>`;
    
    return returnString;
}

</script>
    </body>

    </html>