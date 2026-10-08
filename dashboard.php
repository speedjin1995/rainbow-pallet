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
        .recent-movement { background-color: #1f3b33; border-radius: 0.75rem; }
        .recent-movement .movement-badge { width: 34px; height: 34px; font-size: 0.65rem; background-color: rgba(255, 255, 255, 0.1); }
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
                            <div class="col-xxl-12 col-lg-12">
                                <div class="card">
                                    <div class="card-header fs-5" href="#collapseSearch" data-bs-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseSearch">
                                        <i class="mdi mdi-chevron-down pull-right"></i>
                                        <?=$languageArray['search_records_code'][$language]?>
                                    </div>
                                    <div id="collapseSearch" class="collapse show" aria-labelledby="collapseSearch">
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
                                                                <option value="-" selected>-</option>
                                                                <option value="Purchase"><?=$stockInLabel?></option>
                                                                <option value="Port"><?=$transferLabel?></option>
                                                                <option value="DO"><?=$doLabel?></option>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="plantSearch" class="form-label"><?=$languageArray['plant_code'][$language]?></label>
                                                            <select id="plantSearch" class="form-select select2">
                                                                <option value="-" selected>-</option>
                                                                <?php while($plant && $rowPlant=mysqli_fetch_assoc($plant)){ ?>
                                                                    <option value="<?=htmlspecialchars($rowPlant['plant_code'])?>"><?=htmlspecialchars($rowPlant['name'])?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <?php if ($dashboardViewAllCompanies) { ?>
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="companySearch" class="form-label"><?=$languageArray['company_code'][$language]?></label>
                                                            <select id="companySearch" class="form-select select2">
                                                                <option value="-">-</option>
                                                                <?php while($rowCompany=mysqli_fetch_assoc($company)){ ?>
                                                                    <option value="<?=$rowCompany['id']?>" <?=($rowCompany['id'] == $selectedCompanyId) ? 'selected' : ''?>><?=htmlspecialchars($rowCompany['name'])?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <?php } ?>
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label for="productSearch" class="form-label"><?=$label('product_code', 'Product')?></label>
                                                            <select id="productSearch" class="form-select select2">
                                                                <option value="-" selected>-</option>
                                                                <?php while($rowProduct=mysqli_fetch_assoc($product)){ ?>
                                                                    <option value="<?=htmlspecialchars($rowProduct['product_code'])?>"><?=htmlspecialchars($rowProduct['name'])?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div><!--end col-->
                                                    <div class="col-3">
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
                                                    </div><!--end col-->
                                                    <div class="col">
                                                        <div class="text-end mt-4">
                                                            <button type="button" class="btn btn-danger" id="clearAllSearch">
                                                                <i class="bx bx-reset"></i>
                                                                <?=$languageArray['clear_all_code'][$language]?></button>
                                                            <button type="submit" class="btn btn-success" id="filterSearch">
                                                                <i class="bx bx-search-alt"></i>
                                                                <?=$languageArray['search_code'][$language]?></button>
                                                        </div>
                                                    </div><!--end col-->
                                                </div><!--end row-->
                                            </form>
                                        </div>
                                    </div>
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
                                    <div class="card dashboard-panel">
                                        <div class="card-header border-0 pb-0">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div>
                                                    <h5 class="card-title mb-1"><?=$label('product_inventory_code', 'Product Inventory')?></h5>
                                                    <p class="text-muted mb-0"><?=$label('product_inventory_desc_code', 'Stock balance by product for the filtered period')?></p>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <input type="text" class="form-control" id="productTableSearch" placeholder="<?=$languageArray['search_code'][$language]?>">
                                                    <select class="form-select" id="categoryFilter">
                                                        <option value=""><?=$label('all_categories_code', 'All Categories')?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body px-0">
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
                                    <!-- DO breakdown -->
                                    <div class="card dashboard-panel">
                                        <div class="card-header border-0 pb-0">
                                            <h5 class="card-title mb-1"><?=$label('do_breakdown_code', 'DO Breakdown')?></h5>
                                            <p class="text-muted mb-0"><?=$label('do_breakdown_desc_code', 'Delivery orders for the filtered period')?></p>
                                        </div>
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-6">
                                                    <div id="doChart" dir="ltr"></div>
                                                </div>
                                                <div class="col-6">
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

                                    <!-- Recent movement -->
                                    <div class="card recent-movement text-white">
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
        total: '<?=addslashes($label('total_code', 'Total'))?>'
    };
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
            defaultDate: firstOfMonth
        });

        toDateSearchPicker = $('#toDateSearch').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: today
        });

        $('#filterSearch').on('click', function(){
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

        loadDashboard();
    });

    function loadDashboard() {
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
            }
            else {
                toastr["error"](obj.message || "Something went wrong", "Failed:");
            }
        }).fail(function(){
            toastr["error"]("Something went wrong", "Failed:");
        });
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
            dom: 't',
            paging: false,
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
                $('#productTableInfo').text(labels.showing.replace('%s', info.recordsDisplay).replace('%s', info.recordsTotal));
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
            html += '<div class="d-flex align-items-center' + (i < recent.length - 1 ? ' mb-3' : '') + '">' +
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
