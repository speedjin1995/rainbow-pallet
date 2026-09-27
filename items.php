<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
    $companyId = $_SESSION['company_id'];
    if (!hasModulePermission('Master Data', 'Items', ['view'])){
        header('Location: no-permission.php');
        exit;
    }

    if (!hasModulePermission('Master Data', 'Items', ['view_all_companies'])){
        // Get companies
        $company_ids = implode(',', array_map('intval', $_SESSION['company_ids']));
        $companies = $db->query("SELECT * FROM Company WHERE status = 0 AND id IN ($company_ids) ORDER BY name");
        $companies2 = $db->query("SELECT * FROM Company WHERE status = 0 AND id IN ($company_ids) ORDER BY name");
        $companies3 = $db->query("SELECT * FROM Company WHERE status = 0 AND id IN ($company_ids) ORDER BY name");
        $companies4 = $db->query("SELECT * FROM Company WHERE status = 0 AND id IN ($company_ids) ORDER BY name");
        $categories = $db->query("SELECT * FROM Product_Categories WHERE status = '0' AND company IN ($companyId) ORDER BY category_name ASC");
        $units = $db->query("SELECT * FROM Units WHERE status = '0' AND company IN ($companyId) ORDER BY unit ASC");
        $units2 = $db->query("SELECT * FROM Units WHERE status = '0' AND company IN ($companyId) ORDER BY unit ASC");

    }else{
        $companies = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
        $companies2 = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
        $companies3 = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
        $companies4 = $db->query("SELECT * FROM Company WHERE status = '0' ORDER BY name ASC");
        $categories = $db->query("SELECT * FROM Product_Categories WHERE status = '0' ORDER BY category_name ASC");
        $units = $db->query("SELECT * FROM Units WHERE status = '0' ORDER BY unit ASC");
        $units2 = $db->query("SELECT * FROM Units WHERE status = '0' ORDER BY unit ASC");
    }
?>

<head>
    <title><?=$languageArray['items_code'][$language]?> | Synctronix - Weighing System</title>
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
                                    <div class="card-header fs-5 text-white" href="#collapseSearch" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseSearch" style="background-color: #405189; cursor:pointer;">
                                        <i class="mdi mdi-chevron-down pull-right"></i>
                                        <?=$languageArray['search_records_code'][$language] ?? 'Search Records'?>
                                    </div>
                                    <div id="collapseSearch" class="collapse" aria-labelledby="collapseSearch">
                                        <div class="card-body">
                                            <form action="javascript:void(0);">
                                                <div class="row">
                                                    <div class="col-3" <?= !hasModulePermission('Master Data', 'Items', ['view_all_companies']) ? "style='display:none'" : '' ?>>
                                                        <div class="mb-3">
                                                            <label class="form-label"><?=$languageArray['company_code'][$language] ?? 'Company'?></label>
                                                            <select class="form-select select2" id="companySearch" name="companySearch" required>
                                                                <?php while($rowCompany=mysqli_fetch_assoc($companies2)){ ?>
                                                                    <option value="<?=$rowCompany['id'] ?>" <?=($rowCompany['id'] == $companyId) ? 'selected' : ''?>><?=$rowCompany['name'] ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label class="form-label"><?=$languageArray['item_code_code'][$language]?></label>
                                                            <input type="text" class="form-control" id="itemCodeSearch" placeholder="<?=$languageArray['item_code_code'][$language]?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="mb-3">
                                                            <label class="form-label"><?=$languageArray['item_name_code'][$language]?></label>
                                                            <input type="text" class="form-control" id="itemNameSearch" placeholder="<?=$languageArray['item_name_code'][$language]?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="text-end">
                                                            <button type="submit" class="btn btn-success" id="filterSearch"><i class="bx bx-search-alt"></i> <?=$languageArray['search_code'][$language] ?? 'Search'?></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xl-3 col-md-6 add-new-weight">

                                    <!-- Item Modal -->
                                    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header py-2">
                                                    <h5 class="modal-title" id="exampleModalScrollableTitle"><?=$languageArray['add_new_code'][$language]?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form role="form" id="productForm" class="needs-validation" novalidate autocomplete="off">
                                                        <div class="row col-12">
                                                            <div class="col-xxl-12 col-lg-12">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-xxl-12 col-lg-12 mb-3" <?= !hasModulePermission('Master Data', 'Items', ['view_all_companies']) ? "style='display:none'" : '' ?>>
                                                                                <div class="row">
                                                                                    <label for="company" class="col-sm-4 col-form-label"><?=$languageArray['company_code'][$language] ?? 'Company'?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-control select2" style="width: 100%;" id="company" name="company" required>
                                                                                            <option value="">Please Select</option>
                                                                                            <?php while ($comp = $companies->fetch_assoc()): ?>
                                                                                            <option value="<?=$comp['id']?>"><?=$comp['name']?></option>
                                                                                            <?php endwhile; ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="productCode" class="col-sm-4 col-form-label"><?=$languageArray['item_code_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="productCode" name="productCode" placeholder="<?=$languageArray['item_code_code'][$language]?>" required>
                                                                                        <div class="invalid-feedback"><?=$languageArray['please_fill_in_the_field_code'][$language]?></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="productName" class="col-sm-4 col-form-label"><?=$languageArray['item_name_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="productName" name="productName" placeholder="<?=$languageArray['item_name_code'][$language]?>" required>
                                                                                        <div class="invalid-feedback"><?=$languageArray['please_fill_in_the_field_code'][$language]?></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="categoryId" class="col-sm-4 col-form-label"><?=$languageArray['category_code'][$language] ?? 'Category'?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-control select2" style="width: 100%;" id="categoryId" name="categoryId" required>
                                                                                            <option value="">Please Select</option>
                                                                                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                                                                            <option value="<?=$cat['id']?>"><?=$cat['category_name']?></option>
                                                                                            <?php endwhile; ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="uom" class="col-sm-4 col-form-label"><?=$languageArray['uom_code'][$language] ?? 'UOM'?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-control select2" style="width: 100%;" id="uom" name="uom" required>
                                                                                            <option value="">Please Select</option>
                                                                                            <?php while ($unit = $units->fetch_assoc()): ?>
                                                                                            <option value="<?=$unit['id']?>"><?=$unit['unit']?></option>
                                                                                            <?php endwhile; ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="description" class="col-sm-4 col-form-label"><?=$languageArray['description_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="description" name="description" placeholder="<?=$languageArray['description_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="varianceType" class="col-sm-4 col-form-label"><?=$languageArray['variance_type_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-control select2" style="width: 100%;" id="varianceType" name="varianceType">
                                                                                            <option value="" selected disabled hidden>Please Select</option>
                                                                                            <option value="W">kg</option>
                                                                                            <option value="P">%</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="high" class="col-sm-4 col-form-label"><?=$languageArray['high_code'][$language]?> (+)</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="high" name="high" placeholder="0" value="0">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="low" class="col-sm-4 col-form-label"><?=$languageArray['low_code'][$language]?> (-)</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="low" name="low" placeholder="0" value="0">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <input type="hidden" class="form-control" id="id" name="id">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row col-12">
                                                            <div class="col-xxl-12 col-lg-12">
                                                                <div class="card bg-light">
                                                                    <div class="card-header">
                                                                        <div class="d-flex justify-content-between">
                                                                            <div>
                                                                                <h5 class="card-title mb-0"><?=$languageArray['uom_conversion_code'][$language]?></h5>
                                                                            </div>
                                                                            <div class="flex-shrink-0">
                                                                                <button type="button" class="btn btn-success add-uom"><i class="ri-add-circle-line align-middle me-1"></i><?=$languageArray['add_uom_code'][$language]?></button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <table class="table table-primary">
                                                                                    <thead>
                                                                                        <tr>
                                                                                            <th width="10%"><?=$languageArray['no_code'][$language]?></th>
                                                                                            <th><?=$languageArray['uom_code'][$language]?></th>
                                                                                            <th><?=$languageArray['rate_code'][$language]?></th>
                                                                                            <th><?=$languageArray['action_code'][$language]?></th>
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody id="uomTable"></tbody>
                                                                                </table>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-lg-12">
                                                            <div class="hstack gap-2 justify-content-end">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                                <button type="button" class="btn btn-success" id="submitProduct"><?=$languageArray['submit_code'][$language]?></button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Manage Prices Modal -->
                                    <div class="modal fade" id="priceModal" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header py-2">
                                                    <h5 class="modal-title"><?=$languageArray['manage_prices_code'][$language]?> - <span id="priceItemName"></span></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form role="form" id="priceForm" autocomplete="off">
                                                        <input type="hidden" id="priceProductId">
                                                        <div class="card bg-light">
                                                            <div class="card-header">
                                                                <h5 class="card-title mb-0"><?=$languageArray['product_pricing_code'][$language]?></h5>
                                                            </div>
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-6 mb-3">
                                                                        <label for="productPurchasePrice" class="form-label"><?=$languageArray['purchase_price_code'][$language]?></label>
                                                                        <input type="number" class="form-control" id="productPurchasePrice" min="0" step="0.01" placeholder="0.00">
                                                                    </div>
                                                                    <div class="col-md-6 mb-3">
                                                                        <label for="productSellingPrice" class="form-label"><?=$languageArray['selling_price_code'][$language]?></label>
                                                                        <input type="number" class="form-control" id="productSellingPrice" min="0" step="0.01" placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="card bg-light">
                                                            <div class="card-header">
                                                                <div class="d-flex justify-content-between">
                                                                    <h5 class="card-title mb-0"><?=$languageArray['customer_supplier_pricing_code'][$language]?></h5>
                                                                    <button type="button" class="btn btn-success" id="addPriceEntry"><i class="ri-add-circle-line align-middle me-1"></i><?=$languageArray['add_price_entry_code'][$language]?></button>
                                                                </div>
                                                            </div>
                                                            <div class="card-body" id="priceEntries"></div>
                                                        </div>

                                                        <div class="col-lg-12">
                                                            <div class="hstack gap-2 justify-content-end">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                                <button type="button" class="btn btn-success" id="submitPrices"><?=$languageArray['submit_code'][$language]?></button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="uploadModal">
                                        <div class="modal-dialog modal-xl" style="max-width: 90%;">
                                            <div class="modal-content">
                                                <form role="form" id="uploadForm">
                                                    <div class="modal-header bg-gray-dark color-palette">
                                                        <h4 class="modal-title"><?=$languageArray['upload_excel_code'][$language]?></h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3" <?= !hasModulePermission('Master Data', 'Items', ['view_all_companies']) ? "style='display:none'" : '' ?>>
                                                            <label for="uploadCompany" class="col-sm-2 col-form-label"><?=$languageArray['company_code'][$language] ?? 'Company'?> <span class="text-danger">*</span></label>
                                                            <div class="col-sm-4">
                                                                <select class="form-select select2" id="uploadCompany" name="uploadCompany" required>
                                                                    <?php while($rowCompany=mysqli_fetch_assoc($companies3)){ ?>
                                                                        <option value="<?=$rowCompany['id'] ?>" <?=($rowCompany['id'] == $companyId) ? 'selected' : ''?>><?=$rowCompany['name'] ?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <input type="file" id="fileInput">
                                                        <button type="button" id="previewButton"><?=$languageArray['preview_data_code'][$language]?></button>
                                                        <div id="previewTable" style="overflow: auto;"></div>
                                                    </div>
                                                    <div class="modal-footer justify-content-between bg-gray-dark color-palette">
                                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                        <button type="button" class="btn btn-success" id="submitWeights"><?=$languageArray['submit_code'][$language]?></button>
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
                                    <div class="modal fade" id="downloadTemplateModal" style="display:none">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><?=$languageArray['company_code'][$language] ?? 'Company'?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="downloadTemplateCompany" class="form-label"><?=$languageArray['company_code'][$language] ?? 'Company'?> <span class="text-danger">*</span></label>
                                                        <select class="form-select select2" id="downloadTemplateCompany" name="downloadTemplateCompany">
                                                            <?php while($rowCompany=mysqli_fetch_assoc($companies4)){ ?>
                                                                <option value="<?=$rowCompany['id'] ?>"><?=$rowCompany['name'] ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                    <button type="button" class="btn btn-info" id="confirmDownloadTemplate"><?=$languageArray['download_template_code'][$language]?></button>
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
                                                    <div class="card-header">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <h5 class="card-title mb-0"><?=$languageArray['previous_records_code'][$language]?></h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <?php if(hasModulePermission('Master Data', 'Items', ['download_template'])): ?>
                                                                <button type="button" id="downloadTemplate" class="btn btn-info waves-effect waves-light">
                                                                    <i class="mdi mdi-file-import-outline align-middle me-1"></i>
                                                                    <?=$languageArray['download_template_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('Master Data', 'Items', ['upload_excel'])): ?>
                                                                <button type="button" id="uploadExcel" class="btn btn-success waves-effect waves-light">
                                                                    <i class="ri-file-pdf-line align-middle me-1"></i>
                                                                    <?=$languageArray['upload_excel_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('Master Data', 'Items', ['cancelled'])): ?>
                                                                <button type="button" id="multiDeactivate" class="btn btn-warning waves-effect waves-light">
                                                                    <i class="ri-delete-bin-fill align-middle me-1"></i>
                                                                    <?=$languageArray['delete_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('Master Data', 'Items', ['create'])): ?>
                                                                <button type="button" id="addProduct" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                                                    <i class="ri-add-circle-line align-middle me-1"></i>
                                                                    <?=$languageArray['add_new_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>
                                                            </div> 
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="productTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                                    <th><?=$languageArray['company_code'][$language] ?? 'Company'?></th>
                                                                    <th><?=$languageArray['manual_code'][$language] ?? 'Manual'?></th>
                                                                    <th><?=$languageArray['item_code_code'][$language] ?? 'Item Code'?></th>
                                                                    <th><?=$languageArray['item_name_code'][$language] ?? 'Item Name'?></th>
                                                                    <th><?=$languageArray['category_code'][$language] ?? 'Category'?></th>
                                                                    <th><?=$languageArray['description_code'][$language] ?? 'Description'?></th>
                                                                    <th><?=$languageArray['status_code'][$language] ?? 'Status'?></th>
                                                                    <th><?=$languageArray['action_code'][$language] ?? 'Action'?></th>
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

    <script type="text/html" id="uomDetail">
        <tr class="details">
            <td>
                <input type="text" class="form-control text-center" id="uomNo" name="uomNo" readonly>
                <input type="hidden" id="uomId" name="uomId">
            </td>
            <td>
                <select class="form-control select2" style="width: 100%;" id="convUom" name="convUom">
                    <?php while($unitRow=mysqli_fetch_assoc($units2)){ ?>
                        <option value="<?=$unitRow['id'] ?>"><?=$unitRow['unit']?></option>
                    <?php } ?>
                </select>
            </td>
            <td>
                <input type="number" class="form-control" id="rate" name="rate">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger" id="remove"><i class="ri-delete-bin-line"></i></button>
            </td>
        </tr>
    </script>

    <script type="text/html" id="priceEntryTemplate">
        <div class="card border price-entry mb-3">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-2">
                        <label class="form-label"><?=$languageArray['type_code'][$language]?></label>
                        <select class="form-select party-type">
                            <option value="Customer"><?=$languageArray['customer_code'][$language]?></option>
                            <option value="Supplier"><?=$languageArray['supplier_code'][$language]?></option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label"><?=$languageArray['customer_supplier_code'][$language]?> <span class="text-danger">*</span></label>
                        <select class="form-control party-id" style="width: 100%;"></select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label"><?=$languageArray['from_date_code'][$language]?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control date-from" placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label"><?=$languageArray['to_date_code'][$language]?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control date-to" placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label"><?=$languageArray['type_code'][$language]?></label>
                        <select class="form-select price-type">
                            <option value="Single"><?=$languageArray['single_code'][$language]?></option>
                            <option value="Range"><?=$languageArray['range_code'][$language]?></option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-2 text-end">
                        <button type="button" class="btn btn-danger remove-price-entry" title="<?=$languageArray['delete_code'][$language]?>"><i class="ri-delete-bin-line"></i></button>
                    </div>
                </div>
                <table class="table table-sm table-bordered mb-2">
                    <thead>
                        <tr>
                            <th><?=$languageArray['qty_from_code'][$language]?></th>
                            <th><?=$languageArray['qty_to_code'][$language]?></th>
                            <th><?=$languageArray['purchase_price_code'][$language]?></th>
                            <th><?=$languageArray['selling_price_code'][$language]?></th>
                            <th><?=$languageArray['discount_type_code'][$language]?></th>
                            <th><?=$languageArray['discount_code'][$language]?></th>
                            <th class="tier-action"><?=$languageArray['action_code'][$language]?></th>
                        </tr>
                    </thead>
                    <tbody class="price-tiers"></tbody>
                </table>
                <button type="button" class="btn btn-sm btn-soft-success add-price-tier"><i class="ri-add-line align-middle me-1"></i><?=$languageArray['add_tier_code'][$language]?></button>
            </div>
        </div>
    </script>

    <script type="text/html" id="priceTierTemplate">
        <tr class="price-tier">
            <td><input type="number" class="form-control qty-from" min="0" step="any"></td>
            <td><input type="number" class="form-control qty-to" min="0" step="any"></td>
            <td><input type="number" class="form-control tier-purchase-price" min="0" step="0.01"></td>
            <td><input type="number" class="form-control tier-selling-price" min="0" step="0.01"></td>
            <td>
                <select class="form-select tier-discount-type">
                    <option value="Amount"><?=$languageArray['amount_code'][$language]?></option>
                    <option value="Percent"><?=$languageArray['percentage_code'][$language]?> (%)</option>
                </select>
            </td>
            <td>
                <div class="input-group">
                    <input type="number" class="form-control tier-discount" min="0" step="0.01" value="0">
                    <div class="input-group-text tier-discount-unit" style="display:none">%</div>
                </div>
            </td>
            <td class="text-center tier-action">
                <button type="button" class="btn btn-sm btn-danger remove-price-tier"><i class="ri-delete-bin-line"></i></button>
            </td>
        </tr>
    </script>

    <script type="text/javascript">
        var table;
        var permissions = <?= json_encode($_SESSION['permissions'] ?? []) ?>;
        var isSADMIN = <?= json_encode($_SESSION['roles'] == 'SADMIN') ?>;
        var uomRowCount = $("#uomTable").find(".details").length;
        var uomNoCount = 1;
        var sessionCompanyId = <?= intval($_SESSION['company_id'] ?? 0) ?>;
        var canViewAllCompanies = <?= json_encode(hasModulePermission('Master Data', 'Items', ['view_all_companies'])) ?>;
        var priceParties = { Customer: [], Supplier: [] };

        $(function () {
            $('#selectAllCheckbox').on('change', function() {
                var checkboxes = $('#productTable tbody input[type="checkbox"]');
                checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
            });

            // Initialize all Select2 elements in the modal
            $('#collapseSearch .select2').select2({
                allowClear: true,
                placeholder: "Please Select",
                dropdownParent: $('#collapseSearch') // Ensures dropdown is not cut off
            });

            // Initialize all Select2 elements in the modal
            $('#addModal .select2').select2({
                allowClear: true,
                placeholder: "Please Select",
                dropdownParent: $('#addModal') // Ensures dropdown is not cut off
            });

            // Initialize all Select2 elements in the upload modal
            $('#uploadModal .select2').select2({
                allowClear: true,
                placeholder: "Please Select",
                dropdownParent: $('#uploadModal') // Ensures dropdown is not cut off
            });

            // Initialize all Select2 elements in the download template modal
            $('#downloadTemplateModal .select2').select2({
                allowClear: true,
                placeholder: "Please Select",
                dropdownParent: $('#downloadTemplateModal') // Ensures dropdown is not cut off
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

            renderTable();

            $('#filterSearch').on('click', function() {
                renderTable();
            });

            $('#submitProduct').on('click', function(){
                // custom validation for select2
                $('#addModal .select2[required]').each(function () {
                    var select2Field = $(this);
                    var select2Container = select2Field.next('.select2-container'); // Get Select2 UI
                    var errorMsg = "<span class='select2-error text-danger' style='font-size: 11.375px;'>Please fill in the field.</span>";

                    // Check if the value is empty
                    if (select2Field.val() === "" || select2Field.val() === null) {
                        select2Container.find('.select2-selection').css('border', '1px solid red'); // Add red border

                        // Add error message if not already present
                        if (select2Container.next('.select2-error').length === 0) {
                            select2Container.after(errorMsg);
                        }

                        isValid = false;
                    } else {
                        select2Container.find('.select2-selection').css('border', ''); // Remove red border
                        select2Container.next('.select2-error').remove(); // Remove error message
                    }
                });

                if($('#productForm').valid()){
                    $('#spinnerLoading').show();
                    var action = $('#addModal').find('#id').val() ? 'update' : 'create';
                    $.post('php/modules/item/index.php', $('#productForm').serialize() + '&action=' + action, function(data){
                        var obj = JSON.parse(data); 
                        if(obj.status === 'success')
                        {
                            table.ajax.reload();
                            $('#spinnerLoading').hide();
                            $('#addModal').modal('hide');
                            toastr["success"](obj.message, "Success:");
                        }
                        else if(obj.status === 'failed')
                        {
                            $('#spinnerLoading').hide();
                            toastr["error"](obj.message, "Failed:");
                        }
                        else
                        {

                        }
                    });
                }
            });

            $('#submitWeights').on('click', function(){
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

                // Company is only used for users with view_all_companies; backend enforces session company otherwise
                var uploadCompany = $('#uploadModal').find('#uploadCompany').val() || '';

                // Send the JSON array to the server
                $.ajax({
                    url: 'php/modules/item/index.php?action=upload&company=' + encodeURIComponent(uploadCompany),
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    success: function(response) {
                        var obj = JSON.parse(response);
                        if (obj.status === 'success') {
                            $('#spinnerLoading').hide();
                            $('#uploadModal').modal('hide');
                            toastr["success"](obj.message, "Success:");
                            $('#productTable').DataTable().ajax.reload(null, false);
                        } 
                        else if (obj.status === 'failed') {
                            $('#spinnerLoading').hide();
                            toastr["error"](obj.message, "Failed:");
                        } 
                        else if (obj.status === 'error') {
                            $('#spinnerLoading').hide();
                            $('#uploadModal').modal('hide');
                            $('#productTable').DataTable().ajax.reload(null, false);
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

            $('#addProduct').on('click', function(){
                $('#addModal').find('#id').val("");
                $('#addModal').find('#company').val(sessionCompanyId).trigger('change');
                $('#addModal').find('#productCode').val("");
                $('#addModal').find('#productName').val("");
                $('#addModal').find('#description').val("");
                $('#addModal').find('#varianceType').val("");
                $('#addModal').find('#high').val("0");
                $('#addModal').find('#low').val("0");
                $('#uomTable').html('');
                uomNoCount = 1;

                // Load categories/units for initial company
                loadCategoriesByCompany(sessionCompanyId);
                loadUomsByCompany(sessionCompanyId);

                // Remove Validation Error Message
                $('#addModal .is-invalid').removeClass('is-invalid');

                $('#addModal .select2[required]').each(function () {
                    var select2Field = $(this);
                    var select2Container = select2Field.next('.select2-container');
                    
                    select2Container.find('.select2-selection').css('border', ''); // Remove red border
                    select2Container.next('.select2-error').remove(); // Remove error message
                });

                $('#addModal').modal('show');
                
                $('#productForm').validate({
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

            // Filter category/uom by company
            $('#company').on('change', function(){
                var companyId = $(this).val();
                loadCategoriesByCompany(companyId);
                loadUomsByCompany(companyId);
            });

            $('#downloadTemplate').on('click', function(){
                if (canViewAllCompanies) {
                    $('#downloadTemplateModal').find('#downloadTemplateCompany').val(sessionCompanyId).trigger('change');
                    $('#downloadTemplateModal').modal('show');
                } else {
                    window.location = 'php/modules/item/index.php?action=downloadTemplate';
                }
            });

            $('#confirmDownloadTemplate').on('click', function(){
                var companyId = $('#downloadTemplateModal').find('#downloadTemplateCompany').val();
                if (!companyId) {
                    toastr["error"]("Please select a company", "Failed:");
                    return;
                }
                window.location = 'php/modules/item/index.php?action=downloadTemplate&company=' + encodeURIComponent(companyId);
                $('#downloadTemplateModal').modal('hide');
            });

            $('#uploadExcel').on('click', function(){
                $('#previewTable').html('');
                $('#fileInput').val('');
                $('#uploadModal').find('#uploadCompany').val(sessionCompanyId).trigger('change');
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

                $("#productTable tbody input[type='checkbox']").each(function () {
                    if (this.checked) {
                        selectedIds.push($(this).val());
                    }
                });

                if (selectedIds.length > 0) {
                    if (confirm('Are you sure you want to delete these products?')) {
                        $.post('php/modules/item/index.php', {action: 'delete', id: selectedIds, type: 'MULTI'}, function(data){
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
                    alert("Please select at least one product to delete.");
                    $('#spinnerLoading').hide();
                }     
            });

            // Find and remove selected table rows
            $("#uomTable").on('click', 'button[id^="remove"]', function () {
                $(this).parents("tr").remove();

                $("#uomTable tr").each(function (index) {
                    $(this).find('input[name^="uomNo"]').val(index + 1);
                });

                uomNoCount = ($("#uomTable").find(".details").length)+1; //Fixed for no issue
            });

            $(".add-uom").click(function(){
                var $addContents = $("#uomDetail").clone();
                $("#uomTable").append($addContents.html());

                $("#uomTable").find('.details:last').attr("id", "detail" + uomRowCount);
                $("#uomTable").find('.details:last').attr("data-index", uomRowCount);
                $("#uomTable").find('#remove:last').attr("id", "remove" + uomRowCount);

                $("#uomTable").find('#uomNo:last').attr('name', 'uomNo['+uomRowCount+']').attr("id", "uomNo" + uomRowCount).val(uomNoCount);
                $("#uomTable").find('#uomId:last').attr('name', 'uomId['+uomRowCount+']').attr("id", "uomId" + uomRowCount);
                $("#uomTable").find('#convUom:last').attr('name', 'convUom['+uomRowCount+']').attr("id", "convUom" + uomRowCount).select2({
                    allowClear: true,
                    placeholder: "Please Select",
                    dropdownParent: $('#uomTable') // Prevents dropdown cutoff inside modals/tables
                });
                $("#uomTable").find('#rate:last').attr('name', 'rate['+uomRowCount+']').attr("id", "rate" + uomRowCount);
            
                // Apply custom styling to Select2 elements in addModal
                $('#uomTable .select2-container .select2-selection--single').css({
                    'padding-top': '4px',
                    'padding-bottom': '4px',
                    'height': 'auto'
                });

                $('#uomTable .select2-container .select2-selection__arrow').css({
                    'padding-top': '33px',
                    'height': 'auto'
                });

                uomRowCount++;
                uomNoCount++;
            });

            // ─── Manage Prices ───────────────────────────────────────────────
            $('#addPriceEntry').on('click', function(){
                addPriceEntry();
            });

            $('#priceEntries').on('click', '.remove-price-entry', function(){
                $(this).closest('.price-entry').remove();
            });

            $('#priceEntries').on('change', '.party-type', function(){
                loadPriceParties($(this).closest('.price-entry'), '');
            });

            $('#priceEntries').on('change', '.price-type', function(){
                var $entry = $(this).closest('.price-entry');
                $entry.find('.price-tiers').html('');
                addPriceTier($entry);
                togglePriceType($entry);
            });

            $('#priceEntries').on('change', '.tier-discount-type', function(){
                toggleDiscountUnit($(this).closest('.price-tier'));
            });

            $('#priceEntries').on('click', '.add-price-tier', function(){
                addPriceTier($(this).closest('.price-entry'));
            });

            $('#priceEntries').on('click', '.remove-price-tier', function(){
                var $entry = $(this).closest('.price-entry');
                if ($entry.find('.price-tier').length > 1) {
                    $(this).closest('.price-tier').remove();
                }
            });

            $('#submitPrices').on('click', function(){
                var $btn = $(this);
                var data = {
                    purchasePrice: $('#productPurchasePrice').val(),
                    sellingPrice: $('#productSellingPrice').val(),
                    entries: []
                };

                $('#priceEntries .price-entry').each(function(){
                    var $entry = $(this);
                    var entry = {
                        partyType: $entry.find('.party-type').val(),
                        partyId: $entry.find('.party-id').val() || '',
                        dateFrom: $entry.find('.date-from').val(),
                        dateTo: $entry.find('.date-to').val(),
                        priceType: $entry.find('.price-type').val(),
                        tiers: []
                    };

                    $entry.find('.price-tier').each(function(){
                        var $tier = $(this);
                        entry.tiers.push({
                            qtyFrom: $tier.find('.qty-from').val(),
                            qtyTo: $tier.find('.qty-to').val(),
                            purchasePrice: $tier.find('.tier-purchase-price').val(),
                            sellingPrice: $tier.find('.tier-selling-price').val(),
                            discount: $tier.find('.tier-discount').val(),
                            discountType: $tier.find('.tier-discount-type').val()
                        });
                    });

                    data.entries.push(entry);
                });

                $btn.prop('disabled', true);
                $('#spinnerLoading').show();
                $.post('php/modules/item/index.php', { action: 'savePrices', id: $('#priceProductId').val(), data: JSON.stringify(data) }, function(response){
                    var obj = JSON.parse(response);
                    if (obj.status === 'success') {
                        $('#priceModal').modal('hide');
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

        function managePrices(id) {
            $('#spinnerLoading').show();
            $.post('php/modules/item/index.php', { action: 'getPrices', id: id }, function(response){
                var obj = JSON.parse(response);
                if (obj.status !== 'success') {
                    toastr["error"](obj.message, "Failed:");
                    return;
                }

                var priceData = obj.data;
                priceParties = { Customer: priceData.customers, Supplier: priceData.suppliers };

                $('#priceProductId').val(priceData.product.id);
                $('#priceItemName').text(priceData.product.product_code + ' - ' + priceData.product.name);
                $('#productPurchasePrice').val(priceData.product.purchase_price || '');
                $('#productSellingPrice').val(priceData.product.selling_price || '');

                $('#priceEntries').html('');
                $.each(priceData.entries, function(i, entry){
                    addPriceEntry(entry);
                });

                $('#priceModal').modal('show');
            }).fail(function(){
                toastr["error"]("Something went wrong", "Failed:");
            }).always(function(){
                $('#spinnerLoading').hide();
            });
        }

        function addPriceEntry(entry) {
            var $entry = $($('#priceEntryTemplate').html());
            $('#priceEntries').append($entry);

            $entry.find('.date-from').flatpickr({ dateFormat: "d-m-Y", defaultDate: entry ? entry.date_from : null });
            $entry.find('.date-to').flatpickr({ dateFormat: "d-m-Y", defaultDate: entry ? entry.date_to : null });

            if (entry) {
                $entry.find('.party-type').val(entry.party_type);
                $entry.find('.price-type').val(entry.price_type);
            }
            loadPriceParties($entry, entry ? entry.party_id : '');

            if (entry && entry.tiers.length > 0) {
                $.each(entry.tiers, function(i, tier){
                    addPriceTier($entry, tier);
                });
            } else {
                addPriceTier($entry);
            }
            togglePriceType($entry);
        }

        function addPriceTier($entry, tier) {
            var $tier = $($('#priceTierTemplate').html());
            if (tier) {
                $tier.find('.qty-from').val(parseFloat(tier.qty_from));
                $tier.find('.qty-to').val(parseFloat(tier.qty_to));
                $tier.find('.tier-purchase-price').val(tier.purchase_price || '');
                $tier.find('.tier-selling-price').val(tier.selling_price || '');
                $tier.find('.tier-discount').val(tier.discount);
                $tier.find('.tier-discount-type').val(tier.discount_type);
            }
            toggleDiscountUnit($tier);
            $entry.find('.price-tiers').append($tier);
            togglePriceType($entry);

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
        }

        // Show % beside the discount when the discount type is Percentage
        function toggleDiscountUnit($tier) {
            $tier.find('.tier-discount-unit').toggle($tier.find('.tier-discount-type').val() === 'Percent');
        }

        // Single = one tier with qty fixed to 1, Range = editable qty tiers
        function togglePriceType($entry) {
            var isRange = $entry.find('.price-type').val() === 'Range';
            $entry.find('.add-price-tier, .tier-action').toggle(isRange);
            if (!isRange) {
                $entry.find('.qty-from, .qty-to').val(1).prop('readonly', true);
            } else {
                $entry.find('.qty-from, .qty-to').prop('readonly', false);
            }
        }

        function loadPriceParties($entry, selectedValue) {
            var $party = $entry.find('.party-id');
            var partyType = $entry.find('.party-type').val();

            if ($party.hasClass('select2-hidden-accessible')) {
                $party.select2('destroy');
            }

            $party.empty().append('<option value="">Please Select</option>');
            $.each(priceParties[partyType] || [], function(i, party){
                $party.append($('<option>').val(party.id).text(party.code + ' - ' + party.name));
            });
            $party.val(selectedValue ? String(selectedValue) : '');

            $party.select2({
                allowClear: true,
                placeholder: "Please Select",
                dropdownParent: $('#priceModal')
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
        }

        function renderTable(){
            var companyId = $('#companySearch').val() || '';
            var itemCode = $('#itemCodeSearch').val() || '';
            var itemName = $('#itemNameSearch').val() || '';

            // Destroy old DataTables if exist
            if ($.fn.DataTable.isDataTable('#productTable')) {
                $("#productTable").DataTable().clear().destroy();
            }

            table = $("#productTable").DataTable({
                "responsive": true,
                "autoWidth": false,
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url':'php/modules/item/index.php',
                    'data': { action: 'getAll', companyId: companyId, itemCode: itemCode, itemName: itemName }
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
                    { data: 'company_name' },
                    {
                        data: 'is_manual',
                        render: function (data, type, row) {
                            return data === 'Y' ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-secondary">No</span>';
                        }
                    },
                    { data: 'product_code' },
                    { data: 'name' },
                    { data: 'category_name' },
                    { data: 'description' },
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
                        render: function ( data, type, row ) {
                            if (isSADMIN || (permissions['Master Data'] && permissions['Master Data']['Items'] && ['edit', 'cancelled', 'manage_price'].some(p => permissions['Master Data']['Items'].includes(p)))) {
                                var buttons = `
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-more-fill align-middle"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">`;

                                if (isSADMIN || (permissions['Master Data'] && permissions['Master Data']['Items'] && permissions['Master Data']['Items'].includes('edit'))) {
                                    buttons += `
                                            <li>
                                                <a class="dropdown-item edit-item-btn" id="edit${data}" onclick="edit(${data})">
                                                    <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> <?=$languageArray['edit_code'][$language]?>
                                                </a>
                                            </li>`;
                                }

                                if (isSADMIN || (permissions['Master Data'] && permissions['Master Data']['Items'] && permissions['Master Data']['Items'].includes('manage_price'))) {
                                    buttons += `
                                            <li>
                                                <a class="dropdown-item" id="managePrices${data}" onclick="managePrices(${data})">
                                                    <i class="ri-price-tag-3-fill align-bottom me-2 text-muted"></i> <?=$languageArray['manage_prices_code'][$language]?>
                                                </a>
                                            </li>`;
                                }

                                if (isSADMIN || (permissions['Master Data'] && permissions['Master Data']['Items'] && permissions['Master Data']['Items'].includes('cancelled'))) {
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
                ],
                'rowCallback': function(row, data) {
                    if (data.is_manual === 'Y') {
                        $(row).css('background-color', '#ffcccc');
                    }
                }
            });
        }

        function edit(id){
            $('#spinnerLoading').show();
            $.post('php/modules/item/index.php', {action: 'get', id: id}, function(data)
            {
                var obj = JSON.parse(data);
                if(obj.status === 'success'){
                    var itemData = obj.data;
                    $('#addModal').find('#id').val(itemData.id);
                    $('#addModal').find('#company').val(itemData.company).trigger('change');
                    $('#addModal').find('#productCode').val(itemData.product_code);
                    $('#addModal').find('#productName').val(itemData.name);
                    $('#addModal').find('#description').val(itemData.description);
                    $('#addModal').find('#varianceType').val(itemData.variance).trigger('change');
                    $('#addModal').find('#high').val(itemData.high || 0);
                    $('#addModal').find('#low').val(itemData.low || 0);

                    // Load categories/units for the item's company, then set selected values
                    loadCategoriesByCompany(itemData.company, itemData.category);
                    loadUomsByCompany(itemData.company, itemData.uom);
                    
                    // Remove Validation Error Message
                    $('#addModal .is-invalid').removeClass('is-invalid');

                    $('#addModal .select2[required]').each(function () {
                        var select2Field = $(this);
                        var select2Container = select2Field.next('.select2-container');
                        
                        select2Container.find('.select2-selection').css('border', ''); // Remove red border
                        select2Container.next('.select2-error').remove(); // Remove error message
                    });

                    // Populate UOM Conversion table
                    $('#uomTable').html('');
                    uomRowCount = 0;
                    uomNoCount = 1;
                    
                    if (itemData.uom_conversions && itemData.uom_conversions.length > 0) {
                        $.each(itemData.uom_conversions, function(index, uomRow) {
                            var $addContents = $("#uomDetail").clone();
                            $("#uomTable").append($addContents.html());

                            $("#uomTable").find('.details:last').attr("id", "detail" + uomRowCount);
                            $("#uomTable").find('.details:last').attr("data-index", uomRowCount);
                            $("#uomTable").find('#remove:last').attr("id", "remove" + uomRowCount);

                            $("#uomTable").find('#uomNo:last').attr('name', 'uomNo['+uomRowCount+']').attr("id", "uomNo" + uomRowCount).val(uomNoCount);
                            $("#uomTable").find('#uomId:last').attr('name', 'uomId['+uomRowCount+']').attr("id", "uomId" + uomRowCount).val(uomRow.id);
                            $("#uomTable").find('#convUom:last').attr('name', 'convUom['+uomRowCount+']').attr("id", "convUom" + uomRowCount).val(uomRow.unit_id).select2({
                                allowClear: true,
                                placeholder: "Please Select",
                                dropdownParent: $('#uomTable')
                            });
                            $("#uomTable").find('#rate:last').attr('name', 'rate['+uomRowCount+']').attr("id", "rate" + uomRowCount).val(uomRow.rate);

                            // Apply custom styling
                            $('#uomTable .select2-container .select2-selection--single').css({
                                'padding-top': '4px',
                                'padding-bottom': '4px',
                                'height': 'auto'
                            });
                            $('#uomTable .select2-container .select2-selection__arrow').css({
                                'padding-top': '33px',
                                'height': 'auto'
                            });

                            uomRowCount++;
                            uomNoCount++;
                        });
                    }
                    
                    $('#addModal').modal('show');

                    $('#productForm').validate({
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
            if (confirm('Are you sure you want to delete this product?')) {
                $.post('php/modules/item/index.php', {action: 'delete', id: id}, function(data){
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

            // Ensure we handle cases where there may be less than 5 columns
            while (headers.length < 5) {
                headers.push(''); // Adding empty headers to reach 5 columns
            }

            // Create HTML table headers
            var htmlTable = '<table style="width:30%;"><thead><tr>';
            headers.forEach(function(header) {
                htmlTable += '<th>' + header + '</th>';
            });
            htmlTable += '</tr></thead><tbody>';

            // Iterate over the data and create table rows
            for (var i = 1; i < jsonData.length; i++) {
                htmlTable += '<tr>';
                var rowData = jsonData[i];

                // Ensure we handle cases where there may be less than 5 cells in a row
                while (rowData.length < 5) {
                    rowData.push(''); // Adding empty cells to reach 5 columns
                }

                for (var j = 0; j < 5; j++) {
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
            if (confirm('Do you want to reactivate this item?')) {
                $('#spinnerLoading').show();
                $.post('php/modules/item/index.php', {action: 'reactivate', id: id}, function(data){
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

        function loadCategoriesByCompany(companyId, selectedValue) {
            $.post('php/modules/productCategory/index.php', { action: 'list', company: companyId }, function(data) {
                var obj = JSON.parse(data);
                var $category = $('#categoryId');
                $category.empty().append('<option value="">Please Select</option>');
                if (obj.status === 'success') {
                    $.each(obj.data, function(i, item) {
                        $category.append('<option value="' + item.id + '">' + item.category_name + '</option>');
                    });
                }
                if (selectedValue) {
                    $category.val(selectedValue).trigger('change');
                } else {
                    $category.trigger('change');
                }
            });
        }

        function loadUomsByCompany(companyId, selectedValue) {
            $.post('php/modules/unit/index.php', { action: 'list', company: companyId }, function(data) {
                var obj = JSON.parse(data);
                var $uom = $('#uom');
                $uom.empty().append('<option value="">Please Select</option>');
                if (obj.status === 'success') {
                    $.each(obj.data, function(i, item) {
                        $uom.append('<option value="' + item.id + '">' + item.unit + '</option>');
                    });
                }
                if (selectedValue) {
                    $uom.val(selectedValue).trigger('change');
                } else {
                    $uom.trigger('change');
                }
            });
        }
    </script>
    </body>

    </html>