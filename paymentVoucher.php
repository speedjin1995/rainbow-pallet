<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
$plantId = $_SESSION['plant'];

$supplier = $db->query("SELECT * FROM Supplier WHERE status = '0' AND payment_term = 'Term' ORDER BY name ASC");
$supplier2 = $db->query("SELECT * FROM Supplier WHERE status = '0' AND payment_term = 'Term' ORDER BY name ASC");
$supplierCash2 = $db->query("SELECT * FROM Supplier WHERE status = '0' AND payment_term = 'Cash' ORDER BY name ASC");

// Get Company Detail
$stmt = $db->prepare("SELECT * from Company WHERE id = 1");
$stmt->execute();
$result = $stmt->get_result();

$includePrice = '';
$includeContainer = '';
if(($row = $result->fetch_assoc()) !== null){
    $includePrice = $row['include_price'];
    // $includeContainer = $row['include_container'];
}
?>

<head>
    <title><?=$languageArray['payment_voucher_code'][$language]?> | Synctronix - Weighing System</title>
    <style>
        #pvPageTabs .nav-link { color: #6c757d; border: 1px solid transparent; padding: 8px 20px; font-weight: 500; }
        #pvPageTabs .nav-link.active { background-color: #0ab39c; color: #fff !important; border-color: #0ab39c; border-radius: 4px 4px 0 0; }
        #pvPageTabs .nav-link:not(.active):hover { color: #0ab39c; border-color: #dee2e6 #dee2e6 transparent; }
    </style>
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
                        <div class="h-100">
                            <!-- Page Tabs -->
                            <ul class="nav nav-tabs mb-3" id="pvPageTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#" id="btnTermTab"><?=$languageArray['term_supplier_code'][$language]?></a>
                                </li>
                                <!-- <li class="nav-item">
                                    <a class="nav-link" href="#" id="btnCashTab"><?=$languageArray['cash_supplier_code'][$language]?></a>
                                </li> -->
                            </ul>

                            <!-- Term Supplier Content -->
                            <div id="termContent">
                                <div class="col-xxl-12 col-lg-12">
                                    <div class="card">
                                        <div class="card-header fs-5" href="#collapseSearch" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseSearch" >
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
                                                                <label for="weighingTypeSearch" class="form-label"><?=$languageArray['weighing_type_code'][$language]?></label>
                                                                <select id="weighingTypeSearch" class="form-select">
                                                                    <option value="Normal"><?=$languageArray['normal_weighing_code'][$language]?></option>
                                                                </select>
                                                            </div>
                                                        </div><!--end col-->
                                                        <div class="col-3" id="supplierSearchDisplay">
                                                            <div class="mb-3">
                                                                <label for="supplierSearch" class="form-label"><?=$languageArray['supplier_name_code'][$language]?></label>
                                                                <select id="supplierSearch" class="form-select select2">
                                                                    <option selected>-</option>
                                                                    <?php while($rowSF=mysqli_fetch_assoc($supplier2)){ ?>
                                                                        <option value="<?=$rowSF['id'] ?>"><?=$rowSF['name'] ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div><!--end col-->
                                                        <div class="col-3">
                                                            <div class="mb-3">
                                                                <label for="invoiceSearch" class="form-label"><?=$languageArray['invoice_no_code'][$language]?></label>
                                                                <input type="text" class="form-control" id="invoiceSearch" name="invoiceSearch" placeholder="Invoice No.">
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
                                                        <div class="card-header" >
                                                            <div class="d-flex justify-content-between">
                                                                <div>
                                                                    <h5 class="card-title text-white mb-0"><?=$languageArray['payment_voucher_code'][$language]?></h5>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <button type="button" id="exportPdfTerm" class="btn btn-warning waves-effect waves-light">
                                                                        <i class="ri-file-pdf-line align-middle me-1"></i>
                                                                        <?=$languageArray['export_pdf_code'][$language]?>
                                                                    </button>
                                                                    <!-- <button type="button" id="cutOffBtn" class="btn btn-success waves-effect waves-light">
                                                                        <i class="ri-scissors-cut-line align-middle me-1"></i>
                                                                        <?=$languageArray['cut_off_code'][$language]?>
                                                                    </button> -->
                                                                    <button type="button" id="addPv" class="btn btn-success waves-effect waves-light">
                                                                        <i class="ri-add-circle-line align-middle me-1"></i>
                                                                        <?=$languageArray['add_new_code'][$language]?>
                                                                    </button>
                                                                </div> 
                                                            </div> 
                                                        </div>
                                                        <div class="card-body">
                                                            <table id="weightTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                                <thead>
                                                                    <tr>
                                                                        <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                                        <th><?=$languageArray['voucher_date_code'][$language]?></th>
                                                                        <th><?=$languageArray['voucher_no_code'][$language]?></th>
                                                                        <!-- <th><?=$languageArray['weighing_type_code'][$language]?></th> -->
                                                                        <!-- <th><?=$languageArray['transaction_status_code'][$language]?></th> -->
                                                                        <th><?=$languageArray['supplier_code'][$language]?></th>
                                                                        <th><?=$languageArray['invoice_no_code'][$language]?></th>
                                                                        <th><?=$languageArray['outstanding_amount_code'][$language]?> (RM)</th>
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
                            </div><!-- end termContent -->

                            <!-- Cash Supplier Content -->
                            <div id="cashContent" style="display:none;">
                                <div class="col-xxl-12 col-lg-12">
                                    <div class="card">
                                        <div class="card-header fs-5" href="#collapseSearchCash" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseSearchCash" >
                                            <i class="mdi mdi-chevron-down pull-right"></i>
                                            <?=$languageArray['search_records_code'][$language]?>
                                        </div>
                                        <div id="collapseSearchCash" class="collapse" aria-labelledby="collapseSearchCash">
                                            <div class="card-body">
                                                <form action="javascript:void(0);">
                                                    <div class="row">
                                                        <div class="col-3">
                                                            <div class="mb-3">
                                                                <label class="form-label"><?=$languageArray['from_date_code'][$language]?></label>
                                                                <input type="date" class="form-control" data-provider="flatpickr" id="fromDateSearchCash">
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="mb-3">
                                                                <label class="form-label"><?=$languageArray['to_date_code'][$language]?></label>
                                                                <input type="date" class="form-control" data-provider="flatpickr" id="toDateSearchCash">
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="mb-3">
                                                                <label class="form-label"><?=$languageArray['supplier_name_code'][$language]?></label>
                                                                <select id="supplierSearchCash" class="form-select select2cash">
                                                                    <option selected>-</option>
                                                                    <?php while($rowSCF = mysqli_fetch_assoc($supplierCash2)){ ?>
                                                                        <option value="<?=$rowSCF['supplier_code'] ?>"><?=$rowSCF['name'] ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <div class="text-end">
                                                                <button type="submit" class="btn btn-success" id="filterSearchCash">
                                                                    <i class="bx bx-search-alt"></i><?=$languageArray['search_code'][$language]?>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <div class="h-100">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="card">
                                                        <div class="card-header">
                                                            <div class="d-flex justify-content-between">
                                                                <div><h5 class="card-title text-white mb-0"><?=$languageArray['ffb_statement_code'][$language]?> (Cash)</h5></div>
                                                                <div class="flex-shrink-0">
                                                                    <button type="button" id="exportPdfCash" class="btn btn-warning waves-effect waves-light">
                                                                        <i class="ri-file-pdf-line align-middle me-1"></i>
                                                                        <?=$languageArray['export_pdf_code'][$language]?>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <table id="weightTableCash" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                                <thead>
                                                                    <tr>
                                                                        <th><?=$languageArray['date_code'][$language]?></th>
                                                                        <th><?=$languageArray['supplier_code'][$language]?></th>
                                                                        <th><?=$languageArray['nett_weight_code'][$language]?> (MT)</th>
                                                                        <th><?=$languageArray['total_price_code'][$language]?> (RM)</th>
                                                                        <th><?=$languageArray['action_code'][$language]?></th>
                                                                    </tr>
                                                                </thead>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end cashContent -->

                            <div class="row">
                                <div class="col-xl-3 col-md-6">
                                    <!-- /.modal-dialog -->
                                    <div class="modal fade" id="pricingModal" tabindex="-1" role="dialog" aria-labelledby="pricingModalTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable custom-xxl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="pricingModalTitle"><?=$languageArray['payment_voucher_details_code'][$language]?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form role="form" id="pricingForm" class="needs-validation" novalidate autocomplete="off">
                                                        <!-- Voucher Info -->
                                                        <div class="row g-3 mb-4">
                                                            <div class="col-md-4">
                                                                <label class="form-label"><?=$languageArray['voucher_date_code'][$language]?></label>
                                                                <input type="text" class="form-control" id="voucherDate" name="voucherDate" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label"><?=$languageArray['voucher_no_code'][$language]?></label>
                                                                <input type="text" class="form-control" id="voucherNo" name="voucherNo" readonly style="background-color: var(--vz-input-disabled-bg);">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label"><?=$languageArray['invoice_no_code'][$language]?></label>
                                                                <input type="text" class="form-control" id="invoiceNo" name="invoiceNo" readonly style="background-color: var(--vz-input-disabled-bg);">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label"><?=$languageArray['type_code'][$language]?></label>
                                                                <select class="form-control select2" id="pvType" name="pvType">
                                                                    <option selected><?=$languageArray['term_code'][$language]?></option>
                                                                    <option value="Internal"><?=$languageArray['internal_code'][$language]?></option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4" id="supplierNameDisplay" style="display:none;">
                                                                <label class="form-label"><?=$languageArray['supplier_code'][$language]?></label>
                                                                <select class="form-control select2" id="supplierId" name="supplierId">
                                                                    <option selected>-</option>
                                                                    <?php while($rowSF=mysqli_fetch_assoc($supplier)){ ?>
                                                                        <option value="<?=$rowSF['id'] ?>"><?=$rowSF['name'] ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4" id="fromDateDisplay" style="display:none;">
                                                                <label class="form-label"><?=$languageArray['from_date_code'][$language]?></label>
                                                                <input type="text" class="form-control" id="transactionFromDate" name="transactionFromDate">
                                                            </div>
                                                            <div class="col-md-4" id="toDateDisplay" style="display:none;">
                                                                <label class="form-label"><?=$languageArray['to_date_code'][$language]?></label>
                                                                <input type="text" class="form-control" id="transactionToDate" name="transactionToDate">
                                                            </div>
                                                        </div>

                                                        <!-- Price Details Section -->
                                                        <div class="card mb-4" id="priceDetailSection">
                                                            <div class="card-header py-2">
                                                                <h6 class="card-title mb-0"><?=$languageArray['price_details_code'][$language]?></h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row g-3">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label"><?=$languageArray['unit_price_code'][$language]?></label>
                                                                        <input type="number" class="form-control" id="unitPrice" name="unitPrice" step="0.01" required>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label"><?=$languageArray['tax_code'][$language]?> %</label>
                                                                        <input type="number" class="form-control" id="tax" name="tax" step="0.01" min="0" max="100" value="0" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Transaction Details -->
                                                        <div class="card mb-4" id="transactionDetailsSection">
                                                            <div class="card-header py-2">
                                                                <h6 class="card-title mb-0"><?=$languageArray['transaction_details_code'][$language]?></h6>
                                                            </div>
                                                            <div class="card-body p-0">
                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered table-striped align-middle mb-0">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th class="text-center" style="width:3%;"><input type="checkbox" id="selectAllCheckboxTransactions" class="form-check-input"></th>
                                                                                <th style="width:8%;"><?=$languageArray['pv_no_code'][$language]?></th>
                                                                                <th style="width:9%;"><?=$languageArray['transaction_id_code'][$language]?></th>
                                                                                <th style="width:6%;"><?=$languageArray['vehicle_no_code'][$language]?></th>
                                                                                <th class="text-end" style="width:9%;"><?=$languageArray['gross_incoming_code'][$language]?> (MT)</th>
                                                                                <th style="width:9%;"><?=$languageArray['incoming_date_code'][$language]?></th>
                                                                                <th class="text-end" style="width:9%;"><?=$languageArray['tare_outgoing_code'][$language]?> (MT)</th>
                                                                                <th style="width:9%;"><?=$languageArray['outgoing_date_code'][$language]?></th>
                                                                                <th class="text-end" style="width:7%;"><?=$languageArray['nett_weight_code'][$language]?> (MT)</th>
                                                                                <th class="text-end" style="width:7%;"><?=$languageArray['unit_price_code'][$language]?> (RM)</th>
                                                                                <th class="text-end" style="width:8%;"><?=$languageArray['nett_amount_code'][$language]?> (RM)</th>
                                                                                <th class="text-end" style="width:8%;"><?=$languageArray['sst_code'][$language]?> (RM)</th>
                                                                                <th class="text-end" style="width:8%;"><?=$languageArray['total_price_code'][$language]?> (RM)</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="paymentDetailsTable"></tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Selected Rows Summary -->
                                                        <div class="card mb-4 border-primary border-opacity-25" id="selectedSummarySection">
                                                            <div class="card-header py-2 bg-primary bg-opacity-10">
                                                                <h6 class="card-title mb-0 text-primary"><?=$languageArray['selected_total_code'][$language] ?? 'Selected Total'?> (<span id="selectedCount">0</span> <?=$languageArray['rows_code'][$language] ?? 'rows'?>)</h6>
                                                            </div>
                                                            <div class="card-body py-3">
                                                                <div class="row text-center">
                                                                    <div class="col-md-3">
                                                                        <small class="text-muted d-block"><?=$languageArray['nett_weight_code'][$language]?> (MT)</small>
                                                                        <span class="fs-6"><strong id="grandNettWeight">0.00</strong></span>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <small class="text-muted d-block"><?=$languageArray['nett_amount_code'][$language]?> (RM)</small>
                                                                        <span class="fs-6"><strong id="grandSubTotal">0.00</strong></span>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <small class="text-muted d-block"><?=$languageArray['sst_code'][$language]?> (RM)</small>
                                                                        <span class="fs-6"><strong id="grandSST">0.00</strong></span>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <small class="text-muted d-block"><?=$languageArray['total_price_code'][$language]?> (RM)</small>
                                                                        <span class="fs-5 text-primary"><strong id="grandTotalPrice">0.00</strong></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Price Details Section -->
                                                        <div id="deductionAdditionSection">
                                                            <!-- Deductions & Additions -->
                                                            <div class="row g-3 mb-4">
                                                                <div class="col-lg-6">
                                                                    <div class="card h-100 border-danger border-opacity-25">
                                                                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                                                            <h6 class="card-title mb-0 text-danger"><?=$languageArray['deductions_code'][$language]?> (-)</h6>
                                                                            <button type="button" class="btn btn-sm btn-outline-danger" id="addDeductionRow"><i class="bx bx-plus"></i></button>
                                                                        </div>
                                                                        <div class="card-body p-0">
                                                                            <table class="table table-bordered align-middle mb-0">
                                                                                <thead class="table-light">
                                                                                    <tr>
                                                                                        <th style="width:50px;"><?=$languageArray['bil_code'][$language]?></th>
                                                                                        <th><?=$languageArray['description_code'][$language]?></th>
                                                                                        <th style="width:130px;"><?=$languageArray['amount_code'][$language]?> (RM)</th>
                                                                                        <th style="width:50px;"></th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody id="deductionsTable"></tbody>
                                                                                <tfoot class="table-light">
                                                                                    <tr>
                                                                                        <th colspan="2" class="text-end"><?=$languageArray['total_code'][$language]?>:</th>
                                                                                        <th><input type="number" class="form-control form-control-sm" id="totalDeductions" name="totalDeductions" readonly></th>
                                                                                        <th></th>
                                                                                    </tr>
                                                                                </tfoot>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="card h-100 border-success border-opacity-25">
                                                                        <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                                                            <h6 class="card-title mb-0 text-success"><?=$languageArray['additions_code'][$language]?> (+)</h6>
                                                                            <button type="button" class="btn btn-sm btn-outline-success" id="addAdditionRow"><i class="bx bx-plus"></i></button>
                                                                        </div>
                                                                        <div class="card-body p-0">
                                                                            <table class="table table-bordered align-middle mb-0">
                                                                                <thead class="table-light">
                                                                                    <tr>
                                                                                        <th style="width:50px;"><?=$languageArray['bil_code'][$language]?></th>
                                                                                        <th><?=$languageArray['description_code'][$language]?></th>
                                                                                        <th style="width:130px;"><?=$languageArray['amount_code'][$language]?> (RM)</th>
                                                                                        <th style="width:50px;"></th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody id="additionsTable"></tbody>
                                                                                <tfoot class="table-light">
                                                                                    <tr>
                                                                                        <th colspan="2" class="text-end"><?=$languageArray['total_code'][$language]?>:</th>
                                                                                        <th><input type="number" class="form-control form-control-sm" id="totalAdditions" name="totalAdditions" readonly></th>
                                                                                        <th></th>
                                                                                    </tr>
                                                                                </tfoot>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Summary -->
                                                            <div class="card bg-light mb-3">
                                                                <div class="card-body py-3">
                                                                    <div class="row text-center">
                                                                        <div class="col-md-3">
                                                                            <small class="text-muted d-block"><?=$languageArray['subtotal_code'][$language]?></small>
                                                                            <span class="fs-6">RM <strong id="subtotal">0.00</strong></span>
                                                                            <input type="hidden" id="subtotalInput" name="subtotal" value="0.00">
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <small class="text-muted d-block"><?=$languageArray['deductions_code'][$language]?></small>
                                                                            <span class="fs-6 text-danger">- RM <strong id="displayDeductions">0.00</strong></span>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <small class="text-muted d-block"><?=$languageArray['additions_code'][$language]?></small>
                                                                            <span class="fs-6 text-success">+ RM <strong id="displayAdditions">0.00</strong></span>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <small class="text-muted d-block"><?=$languageArray['final_amount_code'][$language]?></small>
                                                                            <span class="fs-5 text-primary"><strong>RM <span id="finalAmount">0.00</span></strong></span>
                                                                            <input type="hidden" id="finalAmountInput" name="finalAmount" value="0.00">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <input type="hidden" id="pvId" name="pvId">
                                                        <input type="hidden" id="invoiceNo" name="invoiceNo">

                                                        <!-- Actions -->
                                                        <div class="hstack gap-2 justify-content-end pt-3 border-top">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                            <button type="submit" class="btn btn-primary"><?=$languageArray['submit_code'][$language]?></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- /.modal -->
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

                                    <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="printModalTitle"><?=$languageArray['select_slip_to_print_code'][$language]?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form id="printSlipForm">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="printSlipType" class="form-label"><?=$languageArray['slip_type_code'][$language]?> *</label>
                                                            <select id="printSlipType" name="printSlipType" class="form-select" required>
                                                                <option value="ffbStatement"><?=$languageArray['ffb_statement_code'][$language]?></option>
                                                                <option value="pv"><?=$languageArray['payment_voucher_code'][$language]?></option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="exportMethod" class="form-label"><?=$languageArray['select_export_method_code'][$language]?> *</label>
                                                            <select id="exportMethod" name="exportMethod" class="form-select" required>
                                                                <option value="exportDownload" selected><?=$languageArray['download_pdf_code'][$language]?></option>
                                                                <option value="exportPrint"><?=$languageArray['print_code'][$language]?></option>
                                                            </select>
                                                        </div>

                                                        <input type="hidden" id="customerSupplierPrint" name="customerSupplierPrint">
                                                        <input type="hidden" id="transactionDatePrint" name="transactionDatePrint">
                                                        <input type="hidden" id="weighingTypePrint" name="weighingTypePrint">
                                                        <input type="hidden" id="transactionStatusPrint" name="transactionStatusPrint">
                                                        <input type="hidden" id="pvIdPrint" name="pvIdPrint">
                                                        <input type="hidden" id="supplierType" name="supplierType">
                                                    </div> 

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                        <button type="submit" class="btn btn-success"><?=$languageArray['print_code'][$language]?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="modal fade" id="monthEndPrintModal" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Month End FFB Summary</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form id="monthEndPrintForm">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="monthEndExportMethod" class="form-label">Export Method *</label>
                                                            <select id="monthEndExportMethod" name="monthEndExportMethod" class="form-select" required>
                                                                <option value="exportDownload" selected>Download PDF</option>
                                                                <option value="exportPrint">Print</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-success">Submit</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <div class="modal fade" id="cutOffModal" tabindex="-1" role="dialog" aria-labelledby="printModalTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="cutOffModalTitle"><?=$languageArray['select_cutoff_date_code'][$language]?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form id="cutOffForm">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="cutOffDate" class="form-label"><?=$languageArray['cut_off_date_code'][$language]?> *</label>
                                                            <input type="date" id="cutOffDate" name="cutOffDate" class="form-control" required>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                        <button type="submit" class="btn btn-success"><?=$languageArray['submit_code'][$language]?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                            </div> <!-- end row-->
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
    
    var deductionRowCount = 0;
    var additionRowCount = 0;
    var table;
    var cashTable;
    var monthEndPaymentTerm = 'Term';
    var voucherDatePicker;
    const today = new Date();
    const tomorrow = new Date(today);
    const yesterday = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    yesterday.setDate(yesterday.getDate() - 1);
    $(function () {
        //Date picker
        $('#fromDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: yesterday
        });

        $('#toDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: today
        });

        $('#fromDateSearchCash').flatpickr({ 
            dateFormat: "d-m-Y", 
            defaultDate: yesterday 
        });

        $('#toDateSearchCash').flatpickr({ 
            dateFormat: "d-m-Y", 
            defaultDate: today 
        });

        voucherDatePicker = $('#voucherDate').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: today
        });

        $('#cutOffDate').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: today
        });

        $('#transactionFromDate').flatpickr({
            dateFormat: "d-m-Y",
        });

        $('#transactionToDate').flatpickr({
            dateFormat: "d-m-Y",
        });

        // Initialize Select2
        $('#pricingModal .select2').select2({
            allowClear: true,
            placeholder: "Please Select",
            dropdownParent: $('#pricingModal')
        });

        $('#collapseSearch .select2').select2({
            allowClear: true,
            placeholder: "Please Select",
            dropdownParent: $('#collapseSearch')
        });
        
        $('#collapseSearchCash .select2cash').select2({
            allowClear: true,
            placeholder: "Please Select",
            dropdownParent: $('#collapseSearchCash')
        });

        // Apply custom styling to Select2 elements in collapseSearch
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

        renderTable('Term');

        // Tab switching
        $('#btnTermTab').on('click', function(e) {
            e.preventDefault();
            $(this).addClass('active'); 
            $('#btnCashTab').removeClass('active');
            $('#termContent').show(); 
            $('#cashContent').hide();
        });
        
        $('#btnCashTab').on('click', function(e) {
            e.preventDefault();
            $(this).addClass('active'); 
            $('#btnTermTab').removeClass('active');
            $('#cashContent').show(); 
            $('#termContent').hide();
            
            renderTable('Cash');
        });

        $('#filterSearch').on('click', function(){
            renderTable('Term');
        });

        $('#filterSearchCash').on('click', function(){
            renderTable('Cash');
        });

        $.validator.setDefaults({
            submitHandler: function () {
                if($('#printModal').hasClass('show')){
                    var customerSupplierI = $('#printModal').find('#customerSupplierPrint').val();
                    var transactionDateI = $('#printModal').find('#transactionDatePrint').val();
                    var transactionStatusI = $('#printModal').find('#transactionStatusPrint').val();
                    var weightTypeI = $('#printModal').find('#weighingTypePrint').val();
                    var printSlipTypeI = $('#printModal').find('#printSlipType').val();
                    var exportMethod = $('#printModal').find('#exportMethod').val();
                    var pvIdI = $('#printModal').find('#pvIdPrint').val();
                    var supplierType = $('#printModal').find('#supplierType').val();

                    if (exportMethod == 'exportDownload') {
                        // Direct download (no AJAX)
                        if (supplierType == 'Cash'){
                            window.open(
                                'php/printCashSupplierFfbStatement.php?supplierCode=' + customerSupplierI + '&monthLabel=' + transactionDateI + '&printType=exportDownload',
                                '_blank'
                            );
                        }else{
                            window.open(
                                'php/printPaymentVoucherSlip.php?slipType=' + printSlipTypeI + '&customerSupplier=' + customerSupplierI + '&transactionDate=' + transactionDateI + '&transactionStatus=' + transactionStatusI + '&weightType=' + weightTypeI + '&printType=exportDownload&pvId=' + pvIdI,
                                '_blank'
                            );
                        }
                    } else {
                        if (supplierType == 'Cash'){
                            $.post('php/printCashSupplierFfbStatement.php', {supplierCode: customerSupplierI, monthLabel: transactionDateI}, function(data){
                                var obj = JSON.parse(data);

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
                                    alert(obj.message);
                                }
                                else{
                                    alert("Something wrong when printing");
                                }
                            }).fail(function(error){
                                console.error("Error exporting PDF:", error);
                                alert("An error occurred while generating the PDF.");
                            });
                        }else{
                            $.post('php/printPaymentVoucherSlip.php', {slipType: printSlipTypeI, customerSupplier: customerSupplierI, transactionDate: transactionDateI, transactionStatus: transactionStatusI, weightType: weightTypeI, pvId: pvIdI}, function(data){
                                var obj = JSON.parse(data);

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
                                    alert(obj.message);
                                }
                                else{
                                    alert("Something wrong when printing");
                                }
                            }).fail(function(error){
                                console.error("Error exporting PDF:", error);
                                alert("An error occurred while generating the PDF.");
                            });
                        }
                        
                    }
                }else if($('#cutOffModal').hasClass('show')){
                    var cutOffDate = $('#cutOffModal').find('#cutOffDate').val();
                    if(cutOffDate){
                        $('#spinnerLoading').show();
                        $.post('php/cutOffPaymentVoucher.php', {cutOffDate: cutOffDate}, function(data){
                            var obj = JSON.parse(data);
                            
                            if(obj.status === 'success'){
                                $('#weightTable').DataTable().ajax.reload();
                                $('#spinnerLoading').hide();
                                $('#cutOffModal').modal('hide');
                            }
                            else if(obj.status === 'error'){
                                $('#weightTable').DataTable().ajax.reload();
                                $('#spinnerLoading').hide();
                                $('#cutOffModal').modal('hide');

                                $('#errorModal').find('#errorList').empty();
                                var errors = Array.isArray(obj.errors) ? obj.errors : [obj.errors];
                                $.each(errors, function(i, msg){
                                    $('#errorModal').find('#errorList').append('<li>' + msg + '</li>');
                                });
                                $('#errorModal').modal('show');
                            }
                            else if(obj.status === 'failed'){
                                $('#spinnerLoading').hide();
                                alert(obj.message);
                            }
                            else{
                                $('#spinnerLoading').hide();
                                alert("Something wrong when processing cut-off.");
                            }
                        }).fail(function(){
                            $('#spinnerLoading').hide();
                            alert("An error occurred while processing payment voucher cut-off.");
                        });
                    }else{
                        alert('Please select a cut-off date.');
                    }
                }
            }
        });

        $('#addPv').on('click', function(){
            voucherDatePicker.setDate(new Date(), false);  // Use current date directly
            $('#pricingModal').find('#voucherNo').val('');
            $('#pricingModal').find('#invoiceNo').val('');
            $('#pricingModal').find('#pvType').val('Term').trigger('change');
            $('#pricingModal').find('#supplierId').val('').trigger('change');
            $('#pricingModal').find('#transactionFromDate').val('');
            $('#pricingModal').find('#transactionToDate').val('');
            $('#pricingModal').find('#unitPrice').val('0.00');
            $('#pricingModal').find('#tax').val('0');
            $('#pricingModal').find('#paymentDetailsTable').empty();
            $('#pricingModal').find('#transactionDetailsSection').hide();
            $('#pricingModal').find('#priceDetailSection').hide();
            resetDeductionAdditionSection();
            $('#pricingModal').modal('show');
        })

        $('#pricingModal').find('#pvType').on('change', function(){
            var pvType = $(this).val();
            if(pvType === 'Term'){
                $('#pricingModal').find('#supplierNameDisplay').show();
                $('#pricingModal').find('#fromDateDisplay').show();
                $('#pricingModal').find('#toDateDisplay').show();
                $('#pricingModal').find('#transactionDetailsSection').show();
                $('#pricingModal').find('#selectedSummarySection').show();
                $('#pricingModal').find('#priceDetailSection').show();
            }else{
                $('#pricingModal').find('#supplierNameDisplay').hide();
                $('#pricingModal').find('#fromDateDisplay').hide();
                $('#pricingModal').find('#toDateDisplay').hide();
                $('#pricingModal').find('#transactionDetailsSection').hide();
                $('#pricingModal').find('#selectedSummarySection').hide();
                $('#pricingModal').find('#priceDetailSection').hide();
                
                // Reset fields when not Term
                $('#pricingModal').find('#supplierId').val('').trigger('change.select2');
                $('#pricingModal').find('#transactionFromDate').val('');
                $('#pricingModal').find('#transactionToDate').val('');
                $('#pricingModal').find('#unitPrice').val('0.00');
                $('#pricingModal').find('#tax').val('0');
                $('#paymentDetailsTable').empty();
                $('#grandNettWeight').text('0.00');
                $('#grandSubTotal').text('0.00');
                $('#grandSST').text('0.00');
                $('#grandTotalPrice').text('0.00');
                $('#selectedCount').text('0');
            }
        });

        $('#pricingModal').find('#transactionFromDate').on('change', function(){
            $('#pricingModal').find('#supplierId').trigger('change');
        });

        $('#pricingModal').find('#transactionToDate').on('change', function(){
            $('#pricingModal').find('#supplierId').trigger('change');
        });

        $('#pricingModal').find('#supplierId').on('change', function(){
            var supplier = $(this).val();
            var fromDate = $('#pricingModal').find('#transactionFromDate').val();
            var toDate = $('#pricingModal').find('#transactionToDate').val();
            if(supplier && fromDate && toDate){
                $.post('php/modules/paymentVoucher/getSupplierWeighing.php', { 
                    supplierId: $(this).val(), 
                    fromDate: fromDate, 
                    toDate: toDate 
                }, function(data) {
                    var obj = JSON.parse(data);
                    if(obj.status === 'success'){
                        $('#pricingModal').find('#priceDetailSection').show();
                        $('#pricingModal').find('#transactionDetailsSection').show();

                        // Build table rows from JSON array
                        var tableBody = $('#paymentDetailsTable');
                        tableBody.empty();
                        
                        obj.message.forEach(function(weight) {
                            var hasPvId = weight.pv_id && weight.pv_id !== '' && weight.pv_id !== null;
                            var checkboxCell = hasPvId 
                                ? '<td class="text-center"></td>' 
                                : '<td class="text-center"><input type="checkbox" class="form-check-input row-checkbox" name="selected[]" value="' + weight.id + '"></td>';
                            var pvNoCell = '<td>' + (weight.voucher_no || '-') + '</td>';
                            
                            var row = $('<tr>' +
                                checkboxCell +
                                pvNoCell +
                                '<td>' + weight.transaction_id + '</td>' +
                                '<td>' + weight.lorry_plate_no1 + '</td>' +
                                '<td class="text-end">' + (parseFloat(weight.gross_weight1)/1000).toFixed(2) + '</td>' +
                                '<td>' + weight.gross_weight1_date + '</td>' +
                                '<td class="text-end">' + (parseFloat(weight.tare_weight1)/1000).toFixed(2) + '</td>' +
                                '<td>' + weight.tare_weight1_date + '</td>' +
                                '<td class="text-end">' + (parseFloat(weight.nett_weight1)/1000).toFixed(2) + '</td>' +
                                '<td><input type="number" class="form-control form-control-sm row-unit-price" name="unit_price[]" value="' + parseFloat(weight.unit_price).toFixed(2) + '" step="0.01"' + (hasPvId ? ' readonly' : '') + '></td>' +
                                '<td class="text-end"><input type="text" class="form-control form-control-sm row-sub-total text-end" name="sub_total[]" value="' + parseFloat(weight.sub_total).toFixed(2) + '" readonly></td>' +
                                '<td class="text-end"><input type="text" class="form-control form-control-sm row-sst text-end" name="sst[]" value="' + parseFloat(weight.sst).toFixed(2) + '" readonly></td>' +
                                '<td class="text-end"><input type="text" class="form-control form-control-sm row-total-price text-end" name="total_price[]" value="' + parseFloat(weight.total_price).toFixed(2) + '" readonly></td>' +
                                '<input type="hidden" name="id[]" value="' + weight.id + '">' +
                                '<input type="hidden" name="nett_weight[]" value="' + (parseFloat(weight.nett_weight1)/1000).toFixed(2) + '">' +
                                (hasPvId ? '<input type="hidden" name="tied[]" value="' + weight.id + '">' : '') +
                                '</tr>');
                            
                            if (hasPvId) {
                                row.data('tied', true);
                            }
                            tableBody.append(row);
                        });
                        
                        // Calculate totals after table is populated
                        calculateTotals();
                    }
                    else if(obj.status === 'failed'){
                        alert(obj.message);
                    }
                    else{
                        alert("Something wrong when fetching supplier weighing data.");
                    }
                }).fail(function(){
                    alert("An error occurred while fetching supplier weighing data.");
                });
            }
        });

        // Handle row checkbox change
        $(document).on('change', '.row-checkbox, #selectAllCheckboxTransactions', function() {
            var checkedCount = $('#paymentDetailsTable .row-checkbox:checked').length;
            calculateTotals();
        });

        // Handle unit price change - set unit price for each checked row and tied row
        $('#pricingModal').find('#unitPrice').on('input', function() {
            var unitPrice = parseFloat($(this).val()) || 0;
            $('#paymentDetailsTable tr').each(function() {
                var $row = $(this);
                var isChecked = $row.find('.row-checkbox').is(':checked');
                var isTiedRow = $row.data('tied') === true;
                
                if (isChecked || isTiedRow) {
                    $row.find('.row-unit-price').val(unitPrice.toFixed(2));
                    calculateRowTotals($row);
                }
            });
            calculateTotals();
        });

        // Handle tax change - calculate SST based on nett amount (sub_total) * tax %
        $('#pricingModal').find('#tax').on('input', function() {
            var taxRate = parseFloat($(this).val()) || 0;
            $('#paymentDetailsTable tr').each(function() {
                var $row = $(this);
                var isChecked = $row.find('.row-checkbox').is(':checked');
                var isTiedRow = $row.data('tied') === true;
                
                if (isChecked || isTiedRow) {
                    var subTotal = parseFloat($row.find('.row-sub-total').val()) || 0;
                    var sst = subTotal * (taxRate / 100);
                    var totalPrice = subTotal + sst;
                    
                    $row.find('.row-sst').val(sst.toFixed(2));
                    $row.find('.row-total-price').val(totalPrice.toFixed(2));
                }
            });
            calculateTotals();
        });

        // Handle row checkbox change
        $(document).on('change', '.row-checkbox', function() {
            var $row = $(this).closest('tr');
            var isTiedRow = $row.data('tied') === true;
            
            if ($(this).is(':checked')) {
                // Populate row with unitPrice value when checked (only for non-tied rows)
                if (!isTiedRow) {
                    var unitPrice = parseFloat($('#unitPrice').val()) || 0;
                    $row.find('.row-unit-price').val(unitPrice.toFixed(2));
                }
                calculateRowTotals($row);
            } else {
                // Reset row values when unchecked (only for non-tied rows)
                if (!isTiedRow) {
                    $row.find('.row-unit-price').val('0.00');
                    $row.find('.row-sub-total').val('0.00');
                    $row.find('.row-sst').val('0.00');
                    $row.find('.row-total-price').val('0.00');
                }
            }
            calculateTotals();
        });

        // Handle row unit price input change
        $(document).on('input', '.row-unit-price', function() {
            var $row = $(this).closest('tr');
            calculateRowTotals($row);
            calculateTotals();
        });

        // Handle select all checkbox
        $(document).on('change', '#selectAllCheckboxTransactions', function() {
            var isChecked = $(this).is(':checked');
            $('#paymentDetailsTable .row-checkbox').each(function() {
                if ($(this).is(':checked') !== isChecked) {
                    $(this).prop('checked', isChecked).trigger('change');
                }
            });
        });

        $('#addDeductionRow').on('click', function(event, desc = '', amount = 0, descReadonly = false, amtReadonly = false) {
            var descReadonlyAttr = descReadonly ? 'readonly' : '';
            var amtReadonlyAttr = amtReadonly ? 'readonly' : '';
            var newRow = `<tr>
                <td>${++deductionRowCount}</td>
                <td><input type="text" class="form-control form-control-sm" name="deduction_desc[]" value="${desc}" ${descReadonlyAttr}></td>
                <td><input type="number" class="form-control form-control-sm deduction-amount" name="deduction_amount[]" step="0.01" value="${amount}" ${amtReadonlyAttr}></td>
                <td><button type="button" class="btn btn-sm btn-danger removeDeductionRow"><i class="bx bx-trash"></i></button></td>
            </tr>`;
            $('#deductionsTable').append(newRow);
        });

        $(document).on('click', '.removeDeductionRow', function() {
            $(this).closest('tr').remove();
            $('#deductionsTable tr').each(function(i) {
                $(this).find('td:first').text(i + 1);
            });
            deductionRowCount = $('#deductionsTable tr').length;
            calculateTotals();
        });

        $('#addAdditionRow').on('click', function() {
            var newRow = `<tr>
                <td>${++additionRowCount}</td>
                <td><input type="text" class="form-control form-control-sm" name="addition_desc[]"></td>
                <td><input type="number" class="form-control form-control-sm addition-amount" name="addition_amount[]" step="0.01" value="0"></td>
                <td><button type="button" class="btn btn-sm btn-danger removeAdditionRow"><i class="bx bx-trash"></i></button></td>
            </tr>`;
            $('#additionsTable').append(newRow);
        });

        $(document).on('click', '.removeAdditionRow', function() {
            $(this).closest('tr').remove();
            $('#additionsTable tr').each(function(i) {
                $(this).find('td:first').text(i + 1);
            });
            additionRowCount = $('#additionsTable tr').length;
            calculateTotals();
        });

        $(document).on('input', '.deduction-amount, .addition-amount', function() {
            var $row = $(this).closest('tr');
            var deductionDesc = $row.find('input[name="deduction_desc[]"]').val();
            var totalNettWeight = parseFloat($('#totalNettWeight').val()) || 0;

            calculateTotals();
        });

        $('#pricingForm').on('submit', function(e) {
            e.preventDefault();
            $('#spinnerLoading').show();

            var formData = new FormData(this);
            
            $.ajax({
                url: 'php/modules/paymentVoucher/paymentVoucher.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    var obj = JSON.parse(response);
                    if(obj.status === 'success'){
                        $('#weightTable').DataTable().ajax.reload();
                        $('#spinnerLoading').hide();
                        $('#pricingModal').modal('hide');
                    }
                    else if(obj.status === 'failed'){
                        $('#spinnerLoading').hide();
                        alert(obj.message);
                    }
                    else{
                        $('#spinnerLoading').hide();
                        alert(obj.message);
                    }
                }
            });
        });
    });

    function renderTable(termCash){
        if (termCash == 'Term'){
            var fromDateI = $('#fromDateSearch').val();
            var toDateI = $('#toDateSearch').val();
            var weighingTypeI = $('#weighingTypeSearch').val() ? $('#weighingTypeSearch').val() : '';
            var supplierNoI = $('#supplierSearch').val() ? $('#supplierSearch').val() : '';
            var invoiceNoI = $('#invoiceSearch').val() ? $('#invoiceSearch').val() : '';

            //Destroy the old Datatable
            if ($.fn.DataTable.isDataTable('#weightTable')) { 
                $("#weightTable").DataTable().clear().destroy(); 
            }

            //Create new Datatable
            table = $("#weightTable").DataTable({
                "responsive": true,
                "autoWidth": false,
                'processing': true,
                'serverSide': true,
                'searching': false,
                'serverMethod': 'post',
                'ajax': {
                    'url':'php/modules/paymentVoucher/filterPaymentVoucher.php',
                    'data': {
                        fromDate: fromDateI,
                        toDate: toDateI,
                        weighingType: weighingTypeI,
                        supplier: supplierNoI,
                        invoiceNo: invoiceNoI
                    } 
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
                    { data: 'voucher_date' },
                    { data: 'voucher_no' },
                    { data: 'supplier' },
                    { data: 'invoice_no' },
                    { data: 'outstanding_amount' },
                    { 
                        data: 'id',
                        render: function ( data, type, row ) {
                            return '<div class="dropdown d-inline-block">' +
                                '<button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
                                    '<i class="ri-more-fill align-middle"></i>' +
                                '</button>' +
                                '<ul class="dropdown-menu dropdown-menu-end">' +
                                    // '<li>' +
                                    //     '<a class="dropdown-item print-item-btn" id="print'+data+'" onclick="print(\'' + row.customer + '\', \'' + row.voucher_date + '\', \'' + row.transaction_status + '\', \'' + row.weight_type + '\', \'' + (row.pv_id || '') + '\')">' +
                                    //         '<i class="ri-printer-fill align-bottom me-2 text-muted"></i> Print' +
                                    //     '</a>' +
                                    // '</li>' +
                                    '<li>' +
                                        '<a class="dropdown-item apply-unit-price-btn" onclick="edit(\'' + row.id + '\')">' +
                                            '<i class="ri-calculator-fill align-bottom me-2 text-muted"></i> Edit' +
                                        '</a>' +
                                    '</li>' +
                                '</ul>' +
                            '</div>';
                        }
                    }
                ]   
            });
        }else if (termCash == 'Cash'){
            var fromDateI = $('#fromDateSearchCash').val();
            var toDateI = $('#toDateSearchCash').val();
            var supplierNoI = $('#supplierSearchCash').val() ? $('#supplierSearchCash').val() : '';

            //Destroy the old Datatable
            if ($.fn.DataTable.isDataTable('#weightTableCash')) { 
                $("#weightTableCash").DataTable().clear().destroy(); 
            }

            //Create new Datatable
            table = $("#weightTableCash").DataTable({
                "responsive": true,
                "autoWidth": false,
                'processing': true,
                'serverSide': true,
                'searching': false,
                'serverMethod': 'post',
                'ajax': {
                    'url':'php/filterCashSupplierFfb.php',
                    'data': {
                        fromDate: fromDateI,
                        toDate: toDateI,
                        supplier: supplierNoI,
                    } 
                },
                'columns': [
                    { data: 'month_label' },
                    { data: 'supplier_name' },
                    { data: 'total_nett_weight_mt' },
                    { data: 'total_price' },
                    { 
                        data: 'month_label',
                        render: function ( data, type, row ) {
                            return '<div class="dropdown d-inline-block">' +
                                '<button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
                                    '<i class="ri-more-fill align-middle"></i>' +
                                '</button>' +
                                '<ul class="dropdown-menu dropdown-menu-end">' +
                                    '<li>' +
                                        '<a class="dropdown-item print-item-btn" id="printCash'+data+'" onclick="printCash(\'' + row.supplier_code + '\', \'' + row.month_label + '\')">' +
                                            '<i class="ri-printer-fill align-bottom me-2 text-muted"></i> Print' +
                                        '</a>' +
                                    '</li>' +
                                '</ul>' +
                            '</div>';
                        }
                    }
                ]   
            });
        }
    }

    function resetDeductionAdditionSection() {
        // $('#deductionAdditionSection').hide();
        $('#deductionsTable').empty();
        $('#additionsTable').empty();
        $('#totalDeductions').val('0.00');
        $('#totalAdditions').val('0.00');
        $('#subtotal').text('0.00');
        $('#displayDeductions').text('0.00');
        $('#displayAdditions').text('0.00');
        $('#finalAmount').text('0.00');
        deductionRowCount = 0;
        additionRowCount = 0;
    }

    // Calculate row totals
    function calculateRowTotals(row) {
        var unitPrice = parseFloat(row.find('.row-unit-price').val()) || 0;
        var nettWeight = parseFloat(row.find('input[name="nett_weight[]"]').val()) || 0;
        var taxRate = parseFloat($('#tax').val()) || 0;
        
        var subTotal = unitPrice * nettWeight;
        var sst = subTotal * (taxRate / 100);
        var totalPrice = subTotal + sst;
        
        row.find('.row-sub-total').val(subTotal.toFixed(2));
        row.find('.row-sst').val(sst.toFixed(2));
        row.find('.row-total-price').val(totalPrice.toFixed(2));
    }

    function calculateTotals() {
        var grandNettWeight = 0;
        var grandSubTotal = 0;
        var grandSST = 0;
        var grandTotalPrice = 0;
        var checkedCount = 0;
        
        $('#paymentDetailsTable tr').each(function() {
            var $row = $(this);
            var isChecked = $row.find('.row-checkbox').is(':checked');
            var isTiedRow = $row.data('tied') === true;
            
            // Include both checked rows and tied rows (rows without checkbox)
            if (isChecked || isTiedRow) {
                checkedCount++;
                var nettWeight = parseFloat($row.find('input[name="nett_weight[]"]').val()) || 0;
                var subTotal = parseFloat($row.find('.row-sub-total').val()) || 0;
                var sst = parseFloat($row.find('.row-sst').val()) || 0;
                var totalPrice = parseFloat($row.find('.row-total-price').val()) || 0;
                
                grandNettWeight += nettWeight;
                grandSubTotal += subTotal;
                grandSST += sst;
                grandTotalPrice += totalPrice;
            }
        });

        // Show/hide selected summary section
        if (checkedCount > 0) {
            $('#selectedCount').text(checkedCount);
        }
    
        $('#grandNettWeight').text(grandNettWeight.toFixed(2));
        $('#grandSubTotal').text(grandSubTotal.toFixed(2));
        $('#grandSST').text(grandSST.toFixed(2));
        $('#grandTotalPrice').text(grandTotalPrice.toFixed(2));
        
        $('#totalNettWeight').val(grandNettWeight.toFixed(2));
        $('#totalAmount').val(grandTotalPrice.toFixed(2));
        
        // Calculate deductions and additions
        var totalDeductions = 0;
        var totalAdditions = 0;
        
        $('.deduction-amount').each(function() {
            totalDeductions += parseFloat($(this).val()) || 0;
        });
        $('.addition-amount').each(function() {
            totalAdditions += parseFloat($(this).val()) || 0;
        });
        
        var subtotal = grandTotalPrice;
        var finalAmount = subtotal - totalDeductions + totalAdditions;
        
        $('#totalDeductions').val(totalDeductions.toFixed(2));
        $('#totalAdditions').val(totalAdditions.toFixed(2));
        $('#subtotal').text(subtotal.toFixed(2));
        $('#subtotalInput').val(subtotal.toFixed(2));
        $('#displayDeductions').text(totalDeductions.toFixed(2));
        $('#displayAdditions').text(totalAdditions.toFixed(2));
        $('#finalAmount').text(finalAmount.toFixed(2));
        $('#finalAmountInput').val(finalAmount.toFixed(2));
    }

    function edit(pvId) {
        $.post('php/modules/paymentVoucher/getPaymentVoucher.php', { pvId: pvId }, function(response){
            var obj = JSON.parse(response);

            if (obj.status == 'success'){
                var data = obj.message;
                $('#pricingModal').find('#pvId').val(data.id);
                $('#voucherDate').val(formatDate2(new Date(data.voucher_date)));
                $('#pricingModal').find('#voucherNo').val(data.voucher_no);
                $('#pricingModal').find('#invoiceNo').val(data.invoice_no);
                $('#transactionFromDate').val(formatDate2(new Date(data.from_date)));
                $('#transactionToDate').val(formatDate2(new Date(data.to_date)));
                $('#pricingModal').find('#supplierId').val(data.supplier_id).trigger('change');
                $('#pricingModal').find('#unitPrice').val(data.unit_price);
                $('#pricingModal').find('#tax').val(data.tax);


                $('#pricingModal').find('#subtotal').text(data.total_amount);
                $('#pricingModal').find('#subtotalInput').val(data.total_amount);
                $('#pricingModal').find('#totalDeductions').val(data.deduction_amount);
                $('#pricingModal').find('#displayDeductions').text(data.deduction_amount);
                $('#pricingModal').find('#totalAdditions').val(data.addition_amount);
                $('#pricingModal').find('#displayAdditions').text(data.addition_amount);
                $('#pricingModal').find('#finalAmount').text(data.final_amount);
                $('#pricingModal').find('#finalAmountInput').val(data.final_amount);

                // Build deduction table
                $('#deductionsTable').empty();
                deductionRowCount = 0;
                if (data.deduction_details) {
                    var deductions = JSON.parse(data.deduction_details);
                    deductions.forEach(function(item) {
                        var newRow = `<tr>
                            <td>${++deductionRowCount}</td>
                            <td><input type="text" class="form-control form-control-sm" name="deduction_desc[]" value="${item.deduction_desc || ''}"></td>
                            <td><input type="number" class="form-control form-control-sm deduction-amount" name="deduction_amount[]" step="0.01" value="${item.deduction_amount || 0}"></td>
                            <td><button type="button" class="btn btn-sm btn-danger removeDeductionRow"><i class="bx bx-trash"></i></button></td>
                        </tr>`;
                        $('#deductionsTable').append(newRow);
                    });
                }

                // Build addition table
                $('#additionsTable').empty();
                additionRowCount = 0;
                if (data.addition_details) {
                    var additions = JSON.parse(data.addition_details);
                    additions.forEach(function(item) {
                        var newRow = `<tr>
                            <td>${++additionRowCount}</td>
                            <td><input type="text" class="form-control form-control-sm" name="addition_desc[]" value="${item.addition_desc || ''}"></td>
                            <td><input type="number" class="form-control form-control-sm addition-amount" name="addition_amount[]" step="0.01" value="${item.addition_amount || 0}"></td>
                            <td><button type="button" class="btn btn-sm btn-danger removeAdditionRow"><i class="bx bx-trash"></i></button></td>
                        </tr>`;
                        $('#additionsTable').append(newRow);
                    });
                }

                $('#pricingModal').find('#pvType').val(data.type).trigger('change');
                $('#pricingModal').modal('show');
            }
            else if(obj.status === 'failed'){
                toastr["error"](obj.message, "Failed:");
            }
            else{
                toastr["error"]("Something went wrong", "Failed:");
            }
        });
    }

    function print(customerSupplier, transactionDate, transactionStatus, weightType, id) {
        $('#printModal').find('#customerSupplierPrint').val(customerSupplier);
        $('#printModal').find('#transactionDatePrint').val(transactionDate);
        $('#printModal').find('#weighingTypePrint').val(weightType);
        $('#printModal').find('#transactionStatusPrint').val(transactionStatus);
        $('#printModal').find('#pvIdPrint').val(id);
        $('#printModal').find('#supplierType').val('Term');
        $('#printModal').modal('show');

        $('#printSlipForm').validate({
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

    function printCash(supplierCode, monthLabel) {
        $('#printModal').find('#customerSupplierPrint').val(supplierCode);
        $('#printModal').find('#transactionDatePrint').val(monthLabel);
        $('#printModal').find('#supplierType').val('Cash');
        $('#printModal').modal('show');

        $('#printSlipForm').validate({
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
