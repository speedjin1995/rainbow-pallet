<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
    if (!hasModulePermission('Master Data', 'Companies', ['view'])){
        header('Location: no-permission.php');
        exit;
    }

    // Document number setup: transaction status value => label, reset period value => label
    $statusLabels = [
        'Sales' => $languageArray['dispatch_code'][$language],
        'Purchase' => $languageArray['receiving_code'][$language],
        // 'Local' => $languageArray['internal_transfer_code'][$language],
        'Port' => $languageArray['trx_to_port_code'][$language],
        'Misc' => $languageArray['miscellaneous_code'][$language],
    ];
    $resetLabels = [
        'Never'   => $languageArray['never_code'][$language],
        'Yearly'  => $languageArray['yearly_code'][$language],
        'Monthly' => $languageArray['monthly_code'][$language],
    ];
    $documentTypeLabels = [
        'DO'  => $languageArray['delivery_order_no_code'][$language],
        'PO'  => $languageArray['purchase_order_no_code'][$language],
        'INV' => $languageArray['invoice_no_code'][$language],
    ];
?>

<head>
    <title><?=$languageArray['companies_code'][$language] ?? 'Companies'?> | Synctronix - Weighing System</title>
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
        .select2-multiple .select2-selection__choice {
            background-color: rgb(64, 81, 137) !important;
            color: white !important;
            border-color: rgb(64, 81, 137) !important;
        }
        .select2-multiple .select2-selection__choice__remove {
            color: white !important;
        }
        .select2-multiple .select2-selection__choice__remove:hover {
            color: #ddd !important;
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
                                                    <form role="form" id="companyForm" class="needs-validation" novalidate autocomplete="off">
                                                        <div class=" row col-12">
                                                            <div class="col-xxl-12 col-lg-12">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="companyCode" class="col-sm-4 col-form-label"><?=$languageArray['company_code_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="companyCode" name="companyCode" placeholder="<?=$languageArray['company_code_code'][$language]?>" required>
                                                                                        <div class="invalid-feedback">
                                                                                            <?=$languageArray['please_fill_in_the_field_code'][$language]?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="companyRegNo" class="col-sm-4 col-form-label"><?=$languageArray['company_reg_no_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="companyRegNo" name="companyRegNo" placeholder="<?=$languageArray['company_reg_no_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="companyNewRegNo" class="col-sm-4 col-form-label"><?=$languageArray['company_new_reg_no_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="companyNewRegNo" name="companyNewRegNo" placeholder="<?=$languageArray['company_new_reg_no_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="companyName" class="col-sm-4 col-form-label"><?=$languageArray['company_name_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="companyName" name="companyName" placeholder="<?=$languageArray['company_name_code'][$language]?>" required>
                                                                                        <div class="invalid-feedback">
                                                                                            <?=$languageArray['please_fill_in_the_field_code'][$language]?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="addressLine1" class="col-sm-4 col-form-label"><?=$languageArray['address_code'][$language]?> 1</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="addressLine1" name="addressLine1" placeholder="<?=$languageArray['address_code'][$language]?> 1">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="addressLine2" class="col-sm-4 col-form-label"><?=$languageArray['address_code'][$language]?> 2</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="addressLine2" name="addressLine2" placeholder="<?=$languageArray['address_code'][$language]?> 2">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="addressLine3" class="col-sm-4 col-form-label"><?=$languageArray['address_code'][$language]?> 3</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="addressLine3" name="addressLine3" placeholder="<?=$languageArray['address_code'][$language]?> 3">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="phoneNo" class="col-sm-4 col-form-label"><?=$languageArray['phone_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="phoneNo" name="phoneNo" placeholder="<?=$languageArray['phone_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="faxNo" class="col-sm-4 col-form-label"><?=$languageArray['fax_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="faxNo" name="faxNo" placeholder="<?=$languageArray['fax_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>                                                                        
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="tinNo" class="col-sm-4 col-form-label"><?=$languageArray['tin_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="tinNo" name="tinNo" placeholder="<?=$languageArray['tin_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>                                                                        
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="mobileNo" class="col-sm-4 col-form-label"><?=$languageArray['mobile_no_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="mobileNo" name="mobileNo" placeholder="<?=$languageArray['mobile_no_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>                                                                        
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="hasSawnTimber" class="col-sm-4 col-form-label"><?=$languageArray['sawn_timber_code'][$language] ?? 'Sawn Timber'?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-select" id="hasSawnTimber" name="hasSawnTimber">
                                                                                            <option value="N"><?=$languageArray['no_code'][$language] ?? 'No'?></option>
                                                                                            <option value="Y"><?=$languageArray['yes_code'][$language] ?? 'Yes'?></option>
                                                                                        </select>
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
                                                                <button type="button" class="btn btn-success" id="submitCompany"><?=$languageArray['submit_code'][$language]?></button>
                                                            </div>
                                                        </div><!--end col-->                                                               
                                                    </form>
                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->

                                    <!-- Document Number Modal -->
                                    <div class="modal fade" id="documentNumberModal" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header py-2">
                                                    <h5 class="modal-title"><?=$languageArray['document_number_code'][$language]?> - <span id="documentNumberCompanyName"></span></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" id="documentNumberCompanyId">
                                                    <div class="row mb-3">
                                                        <label for="documentType" class="col-sm-2 col-form-label"><?=$languageArray['document_type_code'][$language]?></label>
                                                        <div class="col-sm-4">
                                                            <select class="form-select" id="documentType">
                                                                <?php foreach ($documentTypeLabels as $typeValue => $typeLabel): ?>
                                                                    <option value="<?=$typeValue?>"><?=$typeLabel?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <label for="documentStatuses" class="col-sm-2 col-form-label"><?=$languageArray['transaction_status_code'][$language]?></label>
                                                        <div class="col-sm-4">
                                                            <select class="form-select" id="documentStatuses" multiple="multiple" style="width: 100%;">
                                                                <?php foreach ($statusLabels as $statusValue => $statusLabel): ?>
                                                                    <option value="<?=$statusValue?>"><?=$statusLabel?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <p class="text-muted mb-2"><?=$languageArray['format_hint_code'][$language]?></p>
                                                    <table class="table table-bordered align-middle">
                                                        <thead>
                                                            <tr>
                                                                <th><?=$languageArray['transaction_status_code'][$language]?></th>
                                                                <th><?=$languageArray['format_code'][$language]?></th>
                                                                <th width="10%"><?=$languageArray['digits_code'][$language]?></th>
                                                                <th width="14%"><?=$languageArray['reset_period_code'][$language]?></th>
                                                                <th width="12%"><?=$languageArray['next_number_code'][$language]?></th>
                                                                <th><?=$languageArray['preview_code'][$language]?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="documentNumberRows"></tbody>
                                                    </table>
                                                    <div class="hstack gap-2 justify-content-end">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                        <button type="button" class="btn btn-success" id="submitDocumentNumbers"><?=$languageArray['submit_code'][$language]?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="uploadModal" style="display:none">
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
                                                        <button type="button" class="btn btn-success" id="uploadCompany"><?=$languageArray['submit_code'][$language]?></button>
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
                                                                <h5 class="card-title mb-0 text-white"><?=$languageArray['previous_records_code'][$language]?></h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <?php if(hasModulePermission('Master Data', 'Companies', ['download_template'])): ?>
                                                                <a href="template/Company_Template.xlsx" download>
                                                                    <button type="button" id="downloadTemplate" class="btn btn-info waves-effect waves-light">
                                                                        <i class="ri-file-pdf-line align-middle me-1"></i>
                                                                        <?=$languageArray['download_template_code'][$language]?>
                                                                    </button>
                                                                </a>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('Master Data', 'Companies', ['upload_excel'])): ?>
                                                                <button type="button" id="uploadExcel" class="btn btn-success waves-effect waves-light">
                                                                    <i class="ri-file-pdf-line align-middle me-1"></i>
                                                                    <?=$languageArray['upload_excel_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('Master Data', 'Companies', ['cancelled'])): ?>
                                                                <button type="button" id="multiDeactivate" class="btn btn-warning waves-effect waves-light">
                                                                    <i class="ri-delete-bin-fill align-middle me-1"></i>
                                                                    <?=$languageArray['delete_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('Master Data', 'Companies', ['create'])): ?>
                                                                <button type="button" id="addCompany" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                                                    <i class="ri-add-circle-line align-middle me-1"></i>
                                                                    <?=$languageArray['add_new_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>
                                                            </div> 
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="companyTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                                    <th><?=$languageArray['company_code_code'][$language]?></th>
                                                                    <th><?=$languageArray['company_name_code'][$language]?></th> 
                                                                    <th><?=$languageArray['company_reg_no_code'][$language]?></th> 
                                                                    <th><?=$languageArray['company_new_reg_no_code'][$language]?></th>
                                                                    <th><?=$languageArray['address_code'][$language]?> 1</th>
                                                                    <th><?=$languageArray['address_code'][$language]?> 2</th>
                                                                    <th><?=$languageArray['address_code'][$language]?> 3</th>
                                                                    <th><?=$languageArray['phone_code'][$language]?></th>
                                                                    <th><?=$languageArray['fax_code'][$language]?></th>
                                                                    <th><?=$languageArray['tin_code'][$language]?></th>
                                                                    <th><?=$languageArray['mobile_no_code'][$language]?></th>
                                                                    <th><?=$languageArray['sawn_timber_code'][$language] ?? 'Sawn Timber'?></th>
                                                                    <th><?=$languageArray['status_code'][$language]?></th>
                                                                    <th><?=$languageArray['action_code'][$language]?><?=actionPermissionNote([hasModulePermission('Master Data', 'Companies', ['edit']), hasModulePermission('Master Data', 'Companies', ['cancelled'])])?></th>
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
var permissions = <?= json_encode($_SESSION['permissions'] ?? []) ?>;
var isSADMIN = <?= json_encode($_SESSION['roles'] == 'SADMIN') ?>;
var statusLabels = <?= json_encode($statusLabels) ?>;
var resetLabels = <?= json_encode($resetLabels) ?>;

$(function () {
    $('#selectAllCheckbox').on('change', function() {
        var checkboxes = $('#companyTable tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
    });

    table = $("#companyTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url':'php/modules/company/index.php?action=getAll'
        },
        'columns': [
            {
                // Add a checkbox with a unique ID for each row
                data: 'id', // Assuming 'serialNo' is a unique identifier for each row
                className: 'select-checkbox',
                orderable: false,
                render: function (data, type, row) {
                    return '<input type="checkbox" class="select-checkbox" id="checkbox_' + data + '" value="'+data+'"/>';
                }
            },
            { data: 'company_code' },
            { data: 'name' },
            { data: 'company_reg_no' },
            { data: 'new_reg_no' },
            { data: 'address_line_1' },
            { data: 'address_line_2' },
            { data: 'address_line_3' },
            { data: 'phone_no' },
            { data: 'fax_no' },
            { data: 'tin_no' },
            { data: 'mobile_no' },
            {
                data: 'has_sawn_timber',
                render: function (data, type, row) {
                    return data === 'Y' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
                }
            },
            {
                data: 'id',
                render: function ( data, type, row ) {
                    if (row.status == '1'){
                        return '<button title="Reactivate" type="button" id="reactivate'+data+'" onclick="reactivate('+data+')" class="btn btn-warning btn-sm">Reactivate</button>';
                    }else{
                        return 'Active';
                    }
                }
            },
            { 
                data: 'id',
                responsivePriority: 1,
                render: function ( data, type, row ) {
                    if (isSADMIN || (permissions['Master Data'] && permissions['Master Data']['Companies'] && ['edit', 'cancelled'].some(p => permissions['Master Data']['Companies'].includes(p)))) {
                        var buttons = `
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">`;

                        if (isSADMIN || (permissions['Master Data'] && permissions['Master Data']['Companies'] && permissions['Master Data']['Companies'].includes('edit'))) {
                            buttons += `
                                    <li>
                                        <a class="dropdown-item edit-item-btn" id="edit${data}" onclick="edit(${data})">
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> <?=$languageArray['edit_code'][$language]?>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" id="documentNumber${data}" onclick="manageDocumentNumbers(${data})">
                                            <i class="ri-file-list-3-fill align-bottom me-2 text-muted"></i> <?=$languageArray['document_number_code'][$language]?>
                                        </a>
                                    </li>`;
                        }

                        if (isSADMIN || (permissions['Master Data'] && permissions['Master Data']['Companies'] && permissions['Master Data']['Companies'].includes('cancelled'))) {
                            buttons += `
                                    <li>
                                        <a class="dropdown-item remove-item-btn" id="deactivate${data}" onclick="deactivate(${data})">
                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> <?=$languageArray['delete_code'][$language]?>
                                        </a>
                                    </li>`;
                        }

                        buttons += `
                                </ul>
                            </div>`;

                        return buttons;
                    }

                    return '';
                }
            }
        ]       
    });
    
    $('#submitCompany').on('click', function(){
        if($('#companyForm').valid()){
            $('#spinnerLoading').show();
            $.post('php/modules/company/index.php', $('#companyForm').serialize() + '&action=' + ($('#addModal').find('#id').val() ? 'update' : 'create'), function(data){
                var obj = JSON.parse(data);
                if(obj.status === 'success') {
                    table.ajax.reload();
                    $('#spinnerLoading').hide();
                    $('#addModal').modal('hide');
                    toastr["success"](obj.message, "Success:");
                }
                else if(obj.status === 'failed') {
                    $('#spinnerLoading').hide();
                    toastr["error"](obj.message, "Failed:");
                }
                else {
                    toastr["error"]("Something went wrong!", "Failed:");
                }
            });
        }
    });

    $('#addCompany').on('click', function(){
        $('#addModal').find('#id').val("");
        $('#addModal').find('#companyCode').val("");
        $('#addModal').find('#companyRegNo').val("");
        $('#addModal').find('#companyNewRegNo').val("");
        $('#addModal').find('#companyName').val("");
        $('#addModal').find('#addressLine1').val("");
        $('#addModal').find('#addressLine2').val("");
        $('#addModal').find('#addressLine3').val("");
        $('#addModal').find('#phoneNo').val("");
        $('#addModal').find('#faxNo').val("");
        $('#addModal').find('#tinNo').val("");
        $('#addModal').find('#mobileNo').val("");
        $('#addModal').find('#hasSawnTimber').val("N");

        // Remove Validation Error Message
        $('#addModal .is-invalid').removeClass('is-invalid');

        $('#addModal').modal('show');
        
        $('#companyForm').validate({
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

    $('#uploadCompany').on('click', function(){
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

        // Send the JSON array to the server
        $.ajax({
            url: 'php/modules/company/index.php?action=upload',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                var obj = JSON.parse(response);
                if (obj.status === 'success') {
                    $('#spinnerLoading').hide();
                    $('#uploadModal').modal('hide');
                    toastr["success"](obj.message, "Success:");
                    $('#companyTable').DataTable().ajax.reload(null, false);
                }
                else if (obj.status === 'failed') {
                    $('#spinnerLoading').hide();
                    toastr["error"](obj.message, "Failed:");
                }
                else if (obj.status === 'error') {
                    $('#spinnerLoading').hide();
                    $('#uploadModal').modal('hide');
                    $('#companyTable').DataTable().ajax.reload(null, false);
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
            // Process data and display preview
            displayPreview(data);
        };

        reader.readAsBinaryString(file);
    });

    $('#multiDeactivate').on('click', function () {
        $('#spinnerLoading').show();
        var selectedIds = []; // An array to store the selected 'id' values

        $("#companyTable tbody input[type='checkbox']").each(function () {
            if (this.checked) {
                selectedIds.push($(this).val());
            }
        });

        if (selectedIds.length > 0) {
            if (confirm('Are you sure you want to delete these companies?')) {
                $.post('php/modules/company/index.php', {action: 'delete', id: selectedIds, type: 'MULTI'}, function(data){
                    var obj = JSON.parse(data);
                    
                    if(obj.status === 'success'){
                        table.ajax.reload();
                        toastr["success"](obj.message, "Success:");
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

            $('#spinnerLoading').hide();
        } 
        else {
            // Optionally, you can display a message or take another action if no IDs are selected
            alert("Please select at least one company to delete.");
            $('#spinnerLoading').hide();
        }     
    });
    
    $('#documentNumberRows').on('input change', 'input, select', function() {
        updateDocumentNumberPreview($(this).closest('tr'));
    });

    $('#documentType').on('change', function() {
        loadDocumentNumberRows(false);
    });

    $('#documentStatuses').select2({
        placeholder: "Please Select",
        dropdownParent: $('#documentNumberModal')
    }).on('change', function() {
        toggleDocumentNumberRows();
    });

    // Select2 doesn't copy the select's classes, so tag its container for the .select2-multiple chip styling
    $('#documentStatuses').next('.select2-container').addClass('select2-multiple');

    $('#submitDocumentNumbers').on('click', function(){
        var $btn = $(this);
        var rows = [];
        var missingFormat = false;
        var selectedStatuses = $('#documentStatuses').val() || [];

        // Unselected statuses are sent with a blank format, which removes their numbering
        $('#documentNumberRows tr').each(function(){
            var $row = $(this);
            var isSelected = selectedStatuses.indexOf($row.data('status')) !== -1;
            if (isSelected && $.trim($row.find('.doc-format').val()) === '') {
                missingFormat = true;
            }
            rows.push({
                transactionStatus: $row.data('status'),
                format: isSelected ? $row.find('.doc-format').val() : '',
                digits: $row.find('.doc-digits').val(),
                resetPeriod: $row.find('.doc-reset').val(),
                nextNumber: $row.find('.doc-next').val()
            });
        });

        if (missingFormat) {
            toastr["error"]("<?=$languageArray['please_fill_in_the_field_code'][$language]?>", "Failed:");
            return;
        }

        $btn.prop('disabled', true);
        $('#spinnerLoading').show();
        $.post('php/modules/company/index.php', { action: 'saveDocumentNumbers', companyId: $('#documentNumberCompanyId').val(), documentType: $('#documentType').val(), data: JSON.stringify(rows) }, function(data){
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                $('#documentNumberModal').modal('hide');
                toastr["success"](obj.message, "Success:");
            } else {
                toastr["error"](obj.message, "Failed:");
            }
        }).fail(function(){
            toastr["error"]("Something went wrong", "Failed:");
        }).always(function(){
            $btn.prop('disabled', false);
            $('#spinnerLoading').hide();
        });
    });
});

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/modules/company/index.php', {action: 'get', id: id}, function(data)
    {
        var obj = JSON.parse(data);
        if(obj.status === 'success'){
            $('#addModal').find('#id').val(obj.message.id);
            $('#addModal').find('#companyCode').val(obj.message.company_code);
            $('#addModal').find('#companyRegNo').val(obj.message.company_reg_no);
            $('#addModal').find('#companyNewRegNo').val(obj.message.new_reg_no);
            $('#addModal').find('#companyName').val(obj.message.name);
            $('#addModal').find('#addressLine1').val(obj.message.address_line_1);
            $('#addModal').find('#addressLine2').val(obj.message.address_line_2);
            $('#addModal').find('#addressLine3').val(obj.message.address_line_3);
            $('#addModal').find('#phoneNo').val(obj.message.phone_no);
            $('#addModal').find('#faxNo').val(obj.message.fax_no);
            $('#addModal').find('#tinNo').val(obj.message.tin_no);
            $('#addModal').find('#mobileNo').val(obj.message.mobile_no);
            $('#addModal').find('#hasSawnTimber').val(obj.message.has_sawn_timber === 'Y' ? 'Y' : 'N');

            // Remove Validation Error Message
            $('#addModal .is-invalid').removeClass('is-invalid');

            $('#addModal').modal('show');

            $('#companyForm').validate({
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
        else if(obj.status === 'failed'){
            $('#spinnerLoading').hide();
            toastr["error"](obj.message, "Failed:");
        }
        else{
            $('#spinnerLoading').hide();
            toastr["error"](obj.message, "Failed:");
        }
        $('#spinnerLoading').hide();
    });
}

function deactivate(id){
    $('#spinnerLoading').show();
    if (confirm('Are you sure you want to delete this company?')) {
        $.post('php/modules/company/index.php', {action: 'delete', id: id}, function(data){
            var obj = JSON.parse(data);

            if(obj.status === 'success'){
                table.ajax.reload();
                $('#spinnerLoading').hide();
                toastr["success"](obj.message, "Success:");
            }
            else if(obj.status === 'failed'){
                $('#spinnerLoading').hide();
                toastr["error"](obj.message, "Failed:");
            }
            else{
                $('#spinnerLoading').hide();
                toastr["error"](obj.message, "Failed:");
            }
        });
    }
    $('#spinnerLoading').hide();
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

    // Ensure we handle cases where there may be less than 11 columns
    while (headers.length < 11) {
        headers.push(''); // Adding empty headers to reach 11 columns
    }

    // Create HTML table headers
    var htmlTable = '<table style="width:50%;"><thead><tr>';
    headers.forEach(function(header) {
        htmlTable += '<th>' + header + '</th>';
    });
    htmlTable += '</tr></thead><tbody>';

    // Iterate over the data and create table rows
    for (var i = 1; i < jsonData.length; i++) {
        htmlTable += '<tr>';
        var rowData = jsonData[i];

        // Ensure we handle cases where there may be less than 11 cells in a row
        while (rowData.length < 11) {
            rowData.push(''); // Adding empty cells to reach 11 columns
        }

        for (var j = 0; j < 11; j++) {
            var cellData = rowData[j];
            var formattedData = cellData;

            // Check if cellData is a valid Excel date serial number and format it to DD/MM/YYYY
            if (typeof cellData === 'number' && cellData > 0) {
                var excelDate = XLSX.SSF.parse_date_code(cellData);
            }

            htmlTable += '<td><input type="text" id="'+headers[j].replace(/[^a-zA-Z0-9]/g, '')+(i-1)+'" name="'+headers[j].replace(/[^a-zA-Z0-9]/g, '')+'['+(i-1)+']" value="' + (formattedData == null ? '' : formattedData) + '" /></td>';
        }
        htmlTable += '</tr>';
    }

    htmlTable += '</tbody></table>';

    var previewTable = document.getElementById('previewTable');
    previewTable.innerHTML = htmlTable;
}

function reactivate(id) {
  if (confirm('Do you want to reactivate this plant?')) {
    $('#spinnerLoading').show();
    $.post('php/reactivateMasterData.php', {userID: id, type: "Plant"}, function(data){
        var obj = JSON.parse(data);

        if(obj.status === 'success'){
            table.ajax.reload();
            $('#spinnerLoading').hide();
            toastr["success"](obj.message, "Success:");
        }
        else if(obj.status === 'failed'){
            $('#spinnerLoading').hide();
            toastr["error"](obj.message, "Failed:");
        }
        else{
            $('#spinnerLoading').hide();
            toastr["error"](obj.message, "Failed:");
        }

        $('#spinnerLoading').hide();
    });
  }

  $('#spinnerLoading').hide();
}

// Document number formats of a company, opened on the first document type (Delivery Order No)
function manageDocumentNumbers(id) {
    var company = table.rows().data().toArray().find(function(row){ return row.id == id; });

    $('#documentNumberCompanyId').val(id);
    $('#documentNumberCompanyName').text(company ? company.name : '');
    $('#documentType').prop('selectedIndex', 0);
    loadDocumentNumberRows(true);
}

// One row per transaction status for the selected document type (blank format = no numbering)
function loadDocumentNumberRows(showModal) {
    $('#spinnerLoading').show();
    $.post('php/modules/company/index.php', { action: 'getDocumentNumbers', companyId: $('#documentNumberCompanyId').val(), documentType: $('#documentType').val() }, function(data){
        var obj = JSON.parse(data);
        if (obj.status !== 'success') {
            toastr["error"](obj.message, "Failed:");
            return;
        }

        $('#documentNumberRows').html('');

        $.each(obj.message, function(i, row){
            var resetOptions = '';
            $.each(resetLabels, function(value, label){
                resetOptions += $('<option>').val(value).text(label).prop('selected', value === row.reset_period).prop('outerHTML');
            });

            var $row = $(`
                <tr>
                    <td class="doc-status"></td>
                    <td><input type="text" class="form-control doc-format" placeholder="RPS-{YY}{MM}/{NUMBER}"></td>
                    <td><input type="number" class="form-control doc-digits" min="1" max="10"></td>
                    <td><select class="form-select doc-reset">${resetOptions}</select></td>
                    <td><input type="number" class="form-control doc-next" min="1"></td>
                    <td><input type="text" class="form-control doc-preview" readonly></td>
                </tr>`);
            $row.data('status', row.transaction_status);
            $row.find('.doc-status').text(statusLabels[row.transaction_status] || row.transaction_status);
            $row.find('.doc-format').val(row.format);
            $row.find('.doc-digits').val(row.digits);
            $row.find('.doc-next').val(row.next_number);
            $('#documentNumberRows').append($row);
            updateDocumentNumberPreview($row);
        });

        // Preselect the statuses that already have a format for this document type
        var configured = $.map(obj.message, function(row){ return row.format !== '' ? row.transaction_status : null; });
        $('#documentStatuses').val(configured).trigger('change');

        if (showModal) {
            $('#documentNumberModal').modal('show');
        }
    }).fail(function(){
        toastr["error"]("Something went wrong", "Failed:");
    }).always(function(){
        $('#spinnerLoading').hide();
    });
}

// Only the statuses selected in the dropdown are shown; hidden rows keep their values until saved
function toggleDocumentNumberRows() {
    var selectedStatuses = $('#documentStatuses').val() || [];
    $('#documentNumberRows tr').each(function(){
        $(this).toggle(selectedStatuses.indexOf($(this).data('status')) !== -1);
    });
}

// Same token replacement as DocumentNumberService::render(), using today's date
function updateDocumentNumberPreview($row) {
    var format = $row.find('.doc-format').val() || '';
    var digits = parseInt($row.find('.doc-digits').val(), 10) || 0;
    var nextNumber = String(parseInt($row.find('.doc-next').val(), 10) || 1);
    var today = new Date();
    var year = String(today.getFullYear());
    var month = String(today.getMonth() + 1).padStart(2, '0');

    $row.find('.doc-preview').val(format === '' ? '' : format
        .replace(/\{YYYY\}/g, year)
        .replace(/\{YY\}/g, year.substring(2))
        .replace(/\{MM\}/g, month)
        .replace(/\{NUMBER\}/g, nextNumber.padStart(digits, '0')));
}

</script>
    </body>

    </html>