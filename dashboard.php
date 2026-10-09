<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
require_once "php/requires/lookup.php";
if (!hasModulePermission('Dashboard', 'Dashboard', ['view'])){
    header('Location: no-permission.php');
    exit;
}

$plantId = $_SESSION['plant_id'] ?? [];
$selectedPlantId = intval($_SESSION['selected_plant_id'] ?? 0);
$selectedCompanyId = intval($_SESSION['company_id']);
$dashboardViewAllCompanies = hasModulePermission('Dashboard', 'Dashboard', ['view_all_companies']);
$dashboardViewAllPlants = hasModulePermission('Dashboard', 'Dashboard', ['view_all_plants']);

if ($dashboardViewAllCompanies) {
    $company = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
}

$customer = $db->query("SELECT * FROM Customer WHERE status = '0' AND company IN ($selectedCompanyId) ORDER BY name ASC");
$supplier = $db->query("SELECT * FROM Supplier WHERE status = '0' AND company IN ($selectedCompanyId) ORDER BY name ASC");
$product = $db->query("SELECT * FROM Product WHERE status = '0' AND company IN ($selectedCompanyId) ORDER BY name ASC");

if (!$dashboardViewAllPlants) {
    if (!empty($selectedPlantId)){
        // Locked to the plant selected at login - backend restricts "-" to this plant only
        $plant = searchPlantById($selectedPlantId, $db);
    }else{
        // No plant selected - list every plant the user is tied to
        $plant = searchPlantsByIds($plantId, $db);
    }
} else {
    $plant = $db->query("SELECT * FROM Plant WHERE status = '0' ORDER BY name ASC");
}

// Translated label with an English fallback until the key is added
$label = function($key, $fallback) use ($languageArray, $language) {
    return $languageArray[$key][$language] ?? $fallback;
};

$stockInLabel = $label('stock_in_code', 'Stock In');
$transferLabel = $label('stock_transfer_code', 'Stock Transfer');
$doLabel = $label('delivery_order_code', 'Delivery Order');
$balanceLabel = $label('balance_code', 'Balance');
$salesLabel = $label('dispatch_code', 'Sales');
$productionLabel = $label('production_code', 'Production');
?>

<head>

    <title><?=$label('dashboard_code', 'Dashboard')?> | Synctronix - Weighing System</title>
    <?php include 'layouts/title-meta.php'; ?>

    <!--datatable css-->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />

    <!-- Include jQuery library -->
    <script src="plugins/jquery/jquery.min.js"></script>

    <?php include 'layouts/head-css.php'; ?>

    <style>
        .dashboard-card { border-top: 4px solid; border-radius: 0.75rem; }
        .dashboard-card .eyebrow { font-size: 0.7rem; letter-spacing: 0.08em; }
        .dashboard-card .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .dashboard-card .total { font-size: 1.9rem; }
        .dashboard-panel { border-radius: 0.75rem; }
        #productTable thead th { font-size: 0.7rem; letter-spacing: 0.08em; text-transform: uppercase; }
        #productTable td { vertical-align: middle; padding-top: 0.9rem; padding-bottom: 0.9rem; }
        #productTable .item-avatar { width: 36px; height: 36px; font-size: 0.7rem; }
        .dashboard-date { width: auto; flex-wrap: nowrap; }
        .dashboard-date .form-control { width: 115px; }
        /* Phones: toolbar, date range and table tools take the full width */
        @media (max-width: 575.98px) {
            .dashboard-toolbar, .dashboard-date, .product-tools { width: 100%; }
            .dashboard-date .form-control { width: auto; flex: 1 1 0; min-width: 0; }
            .dashboard-toolbar .btn-filters, .product-tools > * { flex: 1 1 0; min-width: 0; }
            .product-tools .select2-container { width: auto !important; }
            .dashboard-card .total { font-size: 1.6rem; }
        }
        .filter-chip { font-size: 0.75rem; font-weight: 500; }
        .filter-chip .remove-filter { cursor: pointer; opacity: 0.7; }
        .filter-chip .remove-filter:hover { opacity: 1; }
        #filterDrawer .select2-container { width: 100% !important; }
        .product-tools .select2-container .select2-selection--single { height: 100%; display: flex; align-items: center; }
        .party-table thead th { font-size: 0.7rem; letter-spacing: 0.08em; text-transform: uppercase; }
        .party-table td { vertical-align: middle; padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .party-table .item-avatar { width: 32px; height: 32px; font-size: 0.65rem; }
        .party-table tbody tr[data-party] { cursor: pointer; }
        .party-table .progress { height: 6px; width: 80px; }
        /* Primary indigo to match the sidebar / DO chart */
        .recent-movement { background: linear-gradient(135deg, var(--vz-primary) 0%, #2f3d6b 100%); border-radius: 0.75rem; }
        .recent-movement .movement-badge { width: 34px; height: 34px; font-size: 0.65rem; background-color: rgba(255, 255, 255, 0.12); }
        .recent-movement .movement-row + .movement-row { border-top: 1px solid rgba(255, 255, 255, 0.08); }
        .recent-movement .text-white-50 { color: rgba(255, 255, 255, 0.6) !important; }
    </style>

</head>

<?php include 'layouts/body.php'; ?>

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
                            <!-- Header: date range, filter drawer button and active filter chips -->
                            <div class="card dashboard-panel">
                                <div class="card-body py-3">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                        <div>
                                            <h5 class="card-title mb-1"><?=$label('dashboard_code', 'Dashboard')?></h5>
                                            <p class="text-muted mb-0 fs-12" id="periodText"></p>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 dashboard-toolbar">
                                            <div class="input-group dashboard-date">
                                                <span class="input-group-text"><i class="ri-calendar-2-line"></i></span>
                                                <input type="text" class="form-control" data-provider="flatpickr" id="fromDateSearch" placeholder="<?=$languageArray['from_date_code'][$language]?>">
                                                <span class="input-group-text">&rarr;</span>
                                                <input type="text" class="form-control" data-provider="flatpickr" id="toDateSearch" placeholder="<?=$languageArray['to_date_code'][$language]?>">
                                            </div>
                                            <button type="button" class="btn btn-soft-primary position-relative btn-filters" data-bs-toggle="offcanvas" data-bs-target="#filterDrawer" aria-controls="filterDrawer">
                                                <i class="ri-filter-3-line align-bottom me-1"></i><?=$label('filters_code', 'Filters')?>
                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="filterCount">0</span>
                                            </button>
                                            <button type="button" class="btn btn-soft-secondary btn-icon" id="refreshDashboard" title="<?=$languageArray['search_code'][$language]?>">
                                                <i class="ri-refresh-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 d-none" id="activeFilters"></div>
                                </div>
                            </div>

                            <!-- Filter drawer -->
                            <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="filterDrawer" aria-labelledby="filterDrawerLabel">
                                <div class="offcanvas-header border-bottom">
                                    <h5 class="offcanvas-title" id="filterDrawerLabel"><i class="ri-filter-3-line align-bottom me-1"></i><?=$languageArray['search_records_code'][$language]?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <form action="javascript:void(0);" id="filterForm">
                                        <div class="mb-3">
                                            <label for="transactionStatusSearch" class="form-label"><?=$languageArray['transaction_status_code'][$language]?></label>
                                            <select id="transactionStatusSearch" class="form-select select2">
                                                <option value="-" selected>-</option>
                                                <option value="Purchase"><?=$stockInLabel?></option>
                                                <option value="Port"><?=$transferLabel?></option>
                                                <option value="DO"><?=$doLabel?></option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="plantSearch" class="form-label"><?=$languageArray['plant_code'][$language]?></label>
                                            <select id="plantSearch" class="form-select select2">
                                                <option value="-" selected>-</option>
                                                <?php while($plant && $rowPlant=mysqli_fetch_assoc($plant)){ ?>
                                                    <option value="<?=htmlspecialchars($rowPlant['plant_code'])?>"><?=htmlspecialchars($rowPlant['name'])?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php if ($dashboardViewAllCompanies) { ?>
                                        <div class="mb-3">
                                            <label for="companySearch" class="form-label"><?=$languageArray['company_code'][$language]?></label>
                                            <select id="companySearch" class="form-select select2">
                                                <option value="-">-</option>
                                                <?php while($rowCompany=mysqli_fetch_assoc($company)){ ?>
                                                    <option value="<?=$rowCompany['id']?>" <?=($rowCompany['id'] == $selectedCompanyId) ? 'selected' : ''?>><?=htmlspecialchars($rowCompany['name'])?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <?php } ?>
                                        <div class="mb-3">
                                            <label for="productSearch" class="form-label"><?=$label('product_code', 'Product')?></label>
                                            <select id="productSearch" class="form-select select2">
                                                <option value="-" selected>-</option>
                                                <?php while($rowProduct=mysqli_fetch_assoc($product)){ ?>
                                                    <option value="<?=htmlspecialchars($rowProduct['product_code'])?>"><?=htmlspecialchars($rowProduct['name'])?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="customerSupplierSearch" class="form-label"><?=$label('customer_supplier_code', 'Customer / Supplier')?></label>
                                            <select id="customerSupplierSearch" class="form-select select2">
                                                <option value="-" selected>-</option>
                                                <optgroup label="<?=$label('customer_code', 'Customer')?>">
                                                    <?php while($rowCustomer=mysqli_fetch_assoc($customer)){ ?>
                                                        <option value="C|<?=htmlspecialchars($rowCustomer['customer_code'])?>"><?=htmlspecialchars($rowCustomer['name'])?></option>
                                                    <?php } ?>
                                                </optgroup>
                                                <optgroup label="<?=$label('supplier_code', 'Supplier')?>">
                                                    <?php while($rowSupplier=mysqli_fetch_assoc($supplier)){ ?>
                                                        <option value="S|<?=htmlspecialchars($rowSupplier['supplier_code'])?>"><?=htmlspecialchars($rowSupplier['name'])?></option>
                                                    <?php } ?>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                                <div class="offcanvas-footer border-top p-3 d-flex gap-2">
                                    <button type="button" class="btn btn-danger w-50" id="clearAllSearch">
                                        <i class="bx bx-reset"></i>
                                        <?=$languageArray['clear_all_code'][$language]?></button>
                                    <button type="button" class="btn btn-success w-50" id="filterSearch">
                                        <i class="bx bx-search-alt"></i>
                                        <?=$languageArray['search_code'][$language]?></button>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="row">
                                <?php
                                $cards = [
                                    ['id' => 'stockIn', 'eyebrow' => $label('receiving_code', 'Purchase'), 'title' => $stockInLabel, 'colour' => 'success'],
                                    ['id' => 'transfer', 'eyebrow' => $label('trx_to_port_code', 'Transfer To Port'), 'title' => $transferLabel, 'colour' => 'warning'],
                                    ['id' => 'deliveryOrder', 'eyebrow' => $salesLabel . ' + ' . $productionLabel, 'title' => $doLabel, 'colour' => 'primary'],
                                    ['id' => 'balance', 'eyebrow' => $label('available_stock_code', 'Available Stock'), 'title' => $balanceLabel, 'colour' => 'dark'],
                                ];
                                foreach ($cards as $card) { ?>
                                <div class="col-xl-3 col-md-6">
                                    <div class="card dashboard-card border-<?=$card['colour']?>">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div>
                                                    <p class="eyebrow text-uppercase fw-semibold text-muted mb-1"><?=$card['eyebrow']?></p>
                                                    <h6 class="fs-15 mb-0"><?=$card['title']?></h6>
                                                </div>
                                                <span class="dot bg-<?=$card['colour']?> mt-1"></span>
                                            </div>
                                            <h2 class="total fw-semibold ff-secondary mt-4 mb-2"><span id="<?=$card['id']?>Weight">0.00</span> <span class="fs-14 fw-normal text-muted">kg</span></h2>
                                            <p class="text-muted fs-12 mb-0" id="<?=$card['id']?>Trips">
                                                <?php if ($card['id'] === 'balance') { ?>
                                                    <?=$stockInLabel?> - <?=$transferLabel?> - <?=$doLabel?>
                                                <?php } ?>
                                            </p>
                                        </div><!-- end card body -->
                                    </div><!-- end card -->
                                </div><!-- end col -->
                                <?php } ?>
                            </div> <!-- end row-->

                            <div class="row">
                                <!-- Product inventory -->
                                <div class="col-xl-8">
                                    <div class="card dashboard-panel card-height-100">
                                        <div class="card-header border-0 pb-0">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div>
                                                    <h5 class="card-title mb-1"><?=$label('product_inventory_code', 'Product Inventory')?></h5>
                                                    <p class="text-muted mb-0"><?=$label('product_inventory_desc_code', 'Stock balance by product for the filtered period')?></p>
                                                </div>
                                                <div class="d-flex gap-2 product-tools">
                                                    <input type="text" class="form-control" id="productTableSearch" placeholder="<?=$languageArray['search_code'][$language]?>">
                                                    <select class="form-select select2" id="categoryFilter">
                                                        <option value=""><?=$label('all_categories_code', 'All Categories')?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body px-0 flex-grow-1">
                                            <table id="productTable" class="table table-hover nowrap align-middle mb-0" style="width:100%">
                                                <thead class="table-light text-muted">
                                                    <tr>
                                                        <th class="ps-4"><?=$label('product_code', 'Product')?></th>
                                                        <th><?=$label('category_code', 'Category')?></th>
                                                        <th class="text-end"><?=$stockInLabel?></th>
                                                        <th class="text-end"><?=$transferLabel?></th>
                                                        <th class="text-end"><?=$doLabel?></th>
                                                        <th class="text-end"><?=$balanceLabel?></th>
                                                        <th class="pe-4"><?=$languageArray['status_code'][$language]?></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                        <div class="card-footer d-flex justify-content-between text-muted fs-12">
                                            <span id="productTableInfo"></span>
                                            <span>kg</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-4">
                                    <div class="row">
                                    <!-- DO breakdown -->
                                    <div class="col-md-6 col-xl-12">
                                    <div class="card dashboard-panel card-height-100">
                                        <div class="card-header border-0 pb-0">
                                            <h5 class="card-title mb-1"><?=$label('do_breakdown_code', 'DO Breakdown')?></h5>
                                            <p class="text-muted mb-0"><?=$label('do_breakdown_desc_code', 'Delivery orders for the filtered period')?></p>
                                        </div>
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-sm-6">
                                                    <div id="doChart" dir="ltr"></div>
                                                </div>
                                                <div class="col-sm-6 mt-3 mt-sm-0">
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <span><span class="dot d-inline-block rounded-circle bg-primary me-2" style="width:8px;height:8px;"></span><?=$salesLabel?></span>
                                                        <span class="fw-semibold" id="salesPercent">0%</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span><span class="dot d-inline-block rounded-circle bg-warning me-2" style="width:8px;height:8px;"></span><?=$productionLabel?></span>
                                                        <span class="fw-semibold" id="productionPercent">0%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </div>

                                    <!-- Recent movement -->
                                    <div class="col-md-6 col-xl-12">
                                    <div class="card recent-movement text-white card-height-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h5 class="text-white mb-1"><?=$label('recent_movement_code', 'Recent Movement')?></h5>
                                                    <p class="text-white-50 mb-0"><?=$label('recent_movement_desc_code', 'Latest weighing activity')?></p>
                                                </div>
                                                <a href="index.php" class="text-warning"><?=$label('view_all_code', 'View All')?></a>
                                            </div>
                                            <div id="recentMovement"></div>
                                        </div>
                                    </div>
                                    </div>
                                    </div>
                                </div>
                            </div><!--end row-->

                            <div class="row">
                                <?php foreach ([
                                    ['id' => 'customerTable', 'title' => $label('top_customers_code', 'Top Customers'), 'desc' => $label('top_customers_desc_code', 'By delivery order weight for the filtered period'), 'party' => $label('customer_code', 'Customer')],
                                    ['id' => 'supplierTable', 'title' => $label('top_suppliers_code', 'Top Suppliers'), 'desc' => $label('top_suppliers_desc_code', 'By stock in weight for the filtered period'), 'party' => $label('supplier_code', 'Supplier')],
                                ] as $partyPanel) { ?>
                                <!-- <?=$partyPanel['title']?> -->
                                <div class="col-xl-6">
                                    <div class="card dashboard-panel">
                                        <div class="card-header border-0 pb-0">
                                            <h5 class="card-title mb-1"><?=$partyPanel['title']?></h5>
                                            <p class="text-muted mb-0"><?=$partyPanel['desc']?></p>
                                        </div>
                                        <div class="card-body px-0">
                                            <div class="table-responsive">
                                                <table id="<?=$partyPanel['id']?>" class="table table-hover nowrap align-middle mb-0 party-table">
                                                    <thead class="table-light text-muted">
                                                        <tr>
                                                            <th class="ps-4">#</th>
                                                            <th><?=$partyPanel['party']?></th>
                                                            <th class="text-end"><?=$label('trips_code', 'Trips')?></th>
                                                            <th class="text-end">kg</th>
                                                            <th class="pe-4"><?=$label('share_code', 'Share')?></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div><!--end row-->

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

    <!-- apexcharts -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>
    <script src="plugins/datatables/jquery.dataTables.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

    <script type="text/javascript">
    var fromDateSearchPicker;
    var toDateSearchPicker;
    var doChart = null;
    var productTable = null;
    var labels = {
        sales: '<?=addslashes($salesLabel)?>',
        production: '<?=addslashes($productionLabel)?>',
        trips: '<?=addslashes($label('trips_code', 'Trips'))?>',
        inStock: '<?=addslashes($label('in_stock_code', 'In Stock'))?>',
        outOfStock: '<?=addslashes($label('out_of_stock_code', 'Out of Stock'))?>',
        showing: '<?=addslashes($label('showing_of_code', 'Showing %s of %s'))?>',
        noRecord: '<?=addslashes($label('no_record_code', 'No records found'))?>',
        total: '<?=addslashes($label('total_code', 'Total'))?>',
        clearAll: '<?=addslashes($languageArray['clear_all_code'][$language])?>'
    };
    // Drawer filters shown as chips when they differ from their default
    var drawerFilters = [
        { id: '#transactionStatusSearch', label: '<?=addslashes($languageArray['transaction_status_code'][$language])?>', def: '-' },
        { id: '#plantSearch', label: '<?=addslashes($languageArray['plant_code'][$language])?>', def: '-' },
        { id: '#companySearch', label: '<?=addslashes($languageArray['company_code'][$language])?>', def: '<?=$selectedCompanyId?>' },
        { id: '#productSearch', label: '<?=addslashes($label('product_code', 'Product'))?>', def: '-' },
        { id: '#customerSupplierSearch', label: '<?=addslashes($label('customer_supplier_code', 'Customer / Supplier'))?>', def: '-' }
    ];
    // Recent movement badge / sign per movement type
    var movementTypes = {
        stockIn: { badge: 'IN', sign: '+', label: '<?=addslashes($stockInLabel)?>' },
        transfer: { badge: 'TR', sign: '-', label: '<?=addslashes($transferLabel)?>' },
        sales: { badge: 'DO', sign: '-', label: '<?=addslashes($salesLabel)?>' },
        production: { badge: 'PR', sign: '-', label: '<?=addslashes($productionLabel)?>' }
    };

    $(function () {
        const today = new Date();
        const firstOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

        // Initialize all Select2 elements in the filter drawer
        $('#filterDrawer .select2').select2({
            allowClear: true,
            placeholder: "Please Select",
            dropdownParent: $('#filterDrawer') // Keeps the dropdown search usable inside the offcanvas
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

        // Product table category filter (after the drawer styling above, which is only meant for the drawer fields)
        $('#categoryFilter').select2({
            width: '200px'
        });

        //Date picker
        fromDateSearchPicker = $('#fromDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: firstOfMonth,
            onChange: function(){ loadDashboard(); }
        });

        toDateSearchPicker = $('#toDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: today,
            onChange: function(){ loadDashboard(); }
        });

        $('#filterSearch').on('click', function(){
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('filterDrawer')).hide();
            loadDashboard();
        });

        $('#refreshDashboard').on('click', function(){
            loadDashboard();
        });

        // Remove one filter chip / clear all chips
        $('#activeFilters').on('click', '.remove-filter', function(){
            var filter = drawerFilters[$(this).data('index')];
            $(filter.id).val(filter.def).trigger('change');
            loadDashboard();
        });

        $('#activeFilters').on('click', '#clearAllChips', function(){
            $('#clearAllSearch').trigger('click');
            loadDashboard();
        });

        // Clear All Filter Function
        $('#clearAllSearch').on('click', function(){
            fromDateSearchPicker.setDate(firstOfMonth);
            toDateSearchPicker.setDate(today);
            $('#transactionStatusSearch').val('-').trigger('change');
            $('#companySearch').val('<?=$selectedCompanyId?>').trigger('change');
            $('#plantSearch').val('-').trigger('change');
            $('#productSearch').val('-').trigger('change');
            $('#customerSupplierSearch').val('-').trigger('change');
        });

        // Product / customer / supplier lists follow the selected company
        $('#companySearch').on('change', function(){
            loadCompanyLists($(this).val());
        });

        // Product table search / category filter
        $('#productTableSearch').on('keyup', function(){
            if (productTable) {
                productTable.search($(this).val()).draw();
            }
        });

        $('#categoryFilter').on('change', function(){
            if (productTable) {
                productTable.column(1).search($(this).val() ? '^' + $.fn.dataTable.util.escapeRegex($(this).val()) + '$' : '', true, false).draw();
            }
        });

        // Top customer / supplier row => filter the dashboard by that party
        $('.party-table').on('click', 'tbody tr[data-party]', function(){
            var party = $(this).attr('data-party');
            if ($('#customerSupplierSearch option').filter(function(){ return this.value === party; }).length) {
                $('#customerSupplierSearch').val(party).trigger('change');
                loadDashboard();
            }
        });

        loadDashboard();
    });

    function loadDashboard() {
        renderActiveFilters();

        $.post('php/modules/dashboard/index.php', {
            action: 'summary',
            fromDate: $('#fromDateSearch').val(),
            toDate: $('#toDateSearch').val(),
            transactionStatus: $('#transactionStatusSearch').val() || '',
            company: $('#companySearch').val() || '',
            plant: $('#plantSearch').val() || '',
            product: $('#productSearch').val() || '',
            customerSupplier: $('#customerSupplierSearch').val() || ''
        }, function(data){
            var obj = JSON.parse(data);

            if (obj.status === 'success') {
                renderTotals(obj.totals);
                renderDoChart(obj.totals);
                renderProductTable(obj.products);
                renderRecent(obj.recent);
                renderPartyTable('#customerTable', obj.customers, obj.totals.deliveryOrder.weight, 'C', 'bg-primary');
                renderPartyTable('#supplierTable', obj.suppliers, obj.totals.stockIn.weight, 'S', 'bg-success');
            }
            else {
                toastr["error"](obj.message || "Something went wrong", "Failed:");
            }
        }).fail(function(){
            toastr["error"]("Something went wrong", "Failed:");
        });
    }

    // Period text and chips for the filters applied from the drawer
    function renderActiveFilters() {
        var $chips = $('#activeFilters').empty();
        var count = 0;

        $('#periodText').text(($('#fromDateSearch').val() || '-') + ' → ' + ($('#toDateSearch').val() || '-'));

        $.each(drawerFilters, function(i, filter){
            var $select = $(filter.id);
            var value = $select.val();
            if (!$select.length || !value || value === filter.def) {
                return;
            }
            count++;
            $chips.append(
                $('<span class="badge bg-soft-primary text-primary filter-chip d-inline-flex align-items-center gap-1 px-2 py-1">')
                    .append($('<span>').text(filter.label + ': ' + $select.find('option:selected').text()))
                    .append($('<i class="ri-close-line remove-filter">').attr('data-index', i))
            );
        });

        if (count) {
            $chips.append($('<a href="javascript:void(0);" class="fs-12 text-danger ms-1" id="clearAllChips">').text(labels.clearAll));
        }
        $chips.toggleClass('d-none', count === 0);
        $('#filterCount').text(count).toggleClass('d-none', count === 0);
    }

    function loadCompanyLists(companyId) {
        var $product = $('#productSearch');
        var $party = $('#customerSupplierSearch');
        var $customers = $party.find('optgroup').eq(0);
        var $suppliers = $party.find('optgroup').eq(1);

        $product.find('option:not(:first)').remove();
        $customers.empty();
        $suppliers.empty();
        $product.val('-').trigger('change');
        $party.val('-').trigger('change');

        // "-" (all companies) has no single company list to load
        if (!companyId || companyId === '-') {
            return;
        }

        $.post('php/modules/item/index.php', { action: 'list', company: companyId }, function(data){
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                $.each(obj.data, function(i, item){
                    $product.append($('<option>').val(item.product_code).text(item.name));
                });
            }
        });

        $.post('php/modules/customer/index.php', { action: 'list', company: companyId }, function(data){
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                $.each(obj.data, function(i, item){
                    $customers.append($('<option>').val('C|' + item.customer_code).text(item.name));
                });
            }
        });

        $.post('php/modules/supplier/index.php', { action: 'list', company: companyId }, function(data){
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                $.each(obj.data, function(i, item){
                    $suppliers.append($('<option>').val('S|' + item.supplier_code).text(item.name));
                });
            }
        });
    }

    function renderTotals(totals) {
        $.each(['stockIn', 'transfer', 'deliveryOrder'], function(i, key){
            $('#' + key + 'Weight').text(formatWeight(totals[key].weight));
            $('#' + key + 'Trips').text(totals[key].trips + ' ' + labels.trips);
        });

        $('#balanceWeight').text(formatWeight(totals.balance.weight))
            .toggleClass('text-danger', totals.balance.weight < 0);
    }

    function renderDoChart(totals) {
        var sales = totals.sales.weight;
        var production = totals.production.weight;
        var total = sales + production;

        $('#salesPercent').text(total > 0 ? Math.round(sales / total * 100) + '%' : '0%');
        $('#productionPercent').text(total > 0 ? Math.round(production / total * 100) + '%' : '0%');

        var options = {
            chart: { type: 'donut', height: 200 },
            series: [sales, production],
            labels: [labels.sales, labels.production],
            colors: ['#405189', '#f7b84b'],
            legend: { show: false },
            dataLabels: { enabled: false },
            stroke: { width: 0 },
            tooltip: { y: { formatter: function(v){ return formatWeight(v) + ' kg'; } } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            value: { formatter: function(v){ return formatWeight(v); } },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'kg',
                                fontSize: '12px',
                                formatter: function(){ return formatWeight(total); }
                            }
                        }
                    }
                }
            }
        };

        if (doChart) {
            doChart.destroy();
        }
        doChart = new ApexCharts(document.querySelector('#doChart'), options);
        doChart.render();
    }

    function renderProductTable(products) {
        if (productTable) {
            productTable.destroy();
        }

        // Category options from the filtered products
        var selectedCategory = $('#categoryFilter').val();
        var categories = [];
        $.each(products, function(i, row){
            if (row.category_name && categories.indexOf(row.category_name) < 0) {
                categories.push(row.category_name);
            }
        });
        categories.sort();
        $('#categoryFilter').find('option:not(:first)').remove();
        $.each(categories, function(i, category){
            $('#categoryFilter').append($('<option>').val(category).text(category));
        });
        $('#categoryFilter').val(categories.indexOf(selectedCategory) >= 0 ? selectedCategory : '');

        var weightColumn = function(key, bold){
            return {
                data: key,
                className: 'text-end' + (bold ? ' fw-semibold' : ' text-muted'),
                render: function(v, type){ return type === 'display' ? formatWeight(v) : v; }
            };
        };

        productTable = $('#productTable').DataTable({
            data: products,
            dom: "<'table-responsive't><'d-flex justify-content-center justify-content-sm-end px-4 pt-3'p>",
            paging: true,
            pageLength: 10,
            lengthChange: false,
            order: [[0, 'asc']],
            language: { emptyTable: labels.noRecord, zeroRecords: labels.noRecord },
            columns: [
                {
                    data: 'item_name',
                    className: 'ps-4',
                    render: function(v, type, row){
                        if (type !== 'display') {
                            return (v || '') + ' ' + (row.item_code || '');
                        }
                        var subtitle = escapeHtml(row.item_code || '-') + (row.category_name ? ' · ' + escapeHtml(row.category_name) : '');
                        return '<div class="d-flex align-items-center">' +
                            '<div class="item-avatar flex-shrink-0 rounded bg-soft-success text-success fw-semibold d-flex align-items-center justify-content-center me-3">' + escapeHtml(initials(v || row.item_code)) + '</div>' +
                            '<div><h6 class="fs-14 mb-0">' + escapeHtml(v || row.item_code || '-') + '</h6><p class="text-muted fs-12 mb-0">' + subtitle + '</p></div>' +
                            '</div>';
                    }
                },
                { data: 'category_name', visible: false },
                weightColumn('stockIn', true),
                weightColumn('transfer', false),
                weightColumn('deliveryOrder', false),
                {
                    data: 'balance',
                    className: 'text-end fw-semibold',
                    render: function(v, type){
                        if (type !== 'display') {
                            return v;
                        }
                        return '<span class="' + (v < 0 ? 'text-danger' : '') + '">' + formatWeight(v) + '</span>';
                    }
                },
                {
                    data: 'balance',
                    className: 'pe-4',
                    orderable: false,
                    render: function(v){
                        return v > 0
                            ? '<span class="badge bg-soft-success text-success">' + labels.inStock + '</span>'
                            : '<span class="badge bg-soft-danger text-danger">' + labels.outOfStock + '</span>';
                    }
                }
            ],
            drawCallback: function(){
                var info = this.api().page.info();
                $('#productTableInfo').text(labels.showing.replace('%s', info.end - info.start).replace('%s', info.recordsDisplay));
                // No pager when everything fits on one page
                $(this.api().table().container()).find('.dataTables_paginate').toggle(info.pages > 1);
            }
        });

        // Re-apply the table search / category filter to the new data
        productTable.search($('#productTableSearch').val());
        $('#categoryFilter').trigger('change');
    }

    function renderRecent(recent) {
        var html = '';

        $.each(recent, function(i, row){
            var type = movementTypes[row.type];
            html += '<div class="d-flex align-items-center movement-row py-2">' +
                '<div class="movement-badge flex-shrink-0 rounded d-flex align-items-center justify-content-center fw-semibold text-warning me-3">' + type.badge + '</div>' +
                '<div class="flex-grow-1 overflow-hidden">' +
                    '<h6 class="text-white fs-13 mb-0 text-truncate">' + escapeHtml(type.label) + ' · ' + escapeHtml(row.item_name || '-') + '</h6>' +
                    '<p class="text-white-50 fs-11 mb-0 text-truncate">' + escapeHtml(row.transaction_id) + ' · ' + escapeHtml(row.date) + '</p>' +
                '</div>' +
                '<div class="flex-shrink-0 fw-semibold fs-13 ms-2">' + type.sign + ' ' + formatWeight(row.weight) + ' kg</div>' +
                '</div>';
        });

        $('#recentMovement').html(html || '<p class="text-white-50 mb-0">' + labels.noRecord + '</p>');
    }

    // Top 5 customers / suppliers with their share of the DO / Stock In total
    function renderPartyTable(selector, rows, total, prefix, barClass) {
        var $tbody = $(selector).find('tbody').empty();

        if (!rows || !rows.length) {
            $tbody.append('<tr><td colspan="5" class="text-center text-muted py-4">' + labels.noRecord + '</td></tr>');
            return;
        }

        $.each(rows, function(i, row){
            var share = total > 0 ? Math.round(row.weight / total * 100) : 0;
            var $tr = $('<tr>').attr('data-party', prefix + '|' + row.code);
            $tr.append('<td class="ps-4 text-muted">' + (i + 1) + '</td>');
            $tr.append('<td><div class="d-flex align-items-center">' +
                '<div class="item-avatar flex-shrink-0 rounded bg-soft-success text-success fw-semibold d-flex align-items-center justify-content-center me-3">' + escapeHtml(initials(row.name || row.code)) + '</div>' +
                '<div><h6 class="fs-14 mb-0">' + escapeHtml(row.name || row.code) + '</h6><p class="text-muted fs-12 mb-0">' + escapeHtml(row.code) + '</p></div>' +
                '</div></td>');
            $tr.append('<td class="text-end text-muted">' + row.trips + '</td>');
            $tr.append('<td class="text-end fw-semibold">' + formatWeight(row.weight) + '</td>');
            $tr.append('<td class="pe-4"><div class="d-flex align-items-center gap-2">' +
                '<div class="progress flex-shrink-0"><div class="progress-bar ' + barClass + '" style="width:' + share + '%"></div></div>' +
                '<span class="fs-12 text-muted">' + share + '%</span>' +
                '</div></td>');
            $tbody.append($tr);
        });
    }

    function initials(name) {
        var words = $.trim(name || '').split(/\s+/);
        return ((words[0] || '').charAt(0) + (words[1] || words[0] || '').charAt(words[1] ? 0 : 1)).toUpperCase();
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function formatWeight(value) {
        return parseFloat(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    </script>
    </body>

</html>
