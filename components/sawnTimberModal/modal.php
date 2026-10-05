<?php
// Sawn timber modal component markup (#sawnTimberModal) and the detail templates it clones.
// Data: components/sawnTimberModal/data.php
// Script: components/sawnTimberModal/script.php
// Element ids are prefixed with "st" so they don't clash with other modals on the page (e.g. the weighing modal's #addModal).
// Field names are kept as-is - php/modules/sawnTimber/index.php?action=save reads them.
?>
<style>
    #sawnTimberModal .btn-close {
        filter: brightness(0) invert(1);
    }
    #sawnTimberModal .select2-container--open {
        z-index: 9999 !important;
    }
    #sawnTimberModal .modal-content {
        max-height: 92vh;
    }
    #sawnTimberModal .modal-body {
        overflow-y: auto;
    }
    #sawnTimberModal .modal-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        z-index: 10;
    }
    #sawnTimberModal .readonly-field {
        background-color: #f8f9fa !important;
    }
    #sawnTimberModal .tons-highlight {
        background-color: #d4edda !important;
        font-weight: 600;
    }
    #stDetailTable {
        display: none !important;
    }
</style>

<div class="modal fade" id="sawnTimberModal" tabindex="-1" role="dialog" aria-labelledby="sawnTimberModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #405189;">
                <h5 class="modal-title text-white" id="sawnTimberModalTitle">
                    <i class="ri-file-list-3-line me-2"></i><?=$languageArray['sawn_timber_code'][$language]?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <form role="form" id="sawnTimberForm" class="needs-validation" novalidate autocomplete="off">

                    <!-- Header Information Section -->
                    <div class="card mb-3">
                        <div class="card-header py-2" style="background-color: #405189;">
                            <h6 class="mb-0 text-white small">
                                <i class="ri-file-text-line me-1"></i> <?=$languageArray['weighing_transactions_code'][$language]?>
                            </h6>
                        </div>
                        <div class="card-body py-2">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label small mb-1"><?=$languageArray['record_date_code'][$language]?> <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" data-provider="flatpickr" id="sawnTimberDate" name="sawnTimberDate" required>
                                    <div class="invalid-feedback">
                                        <?=$languageArray['please_fill_in_the_field_code'][$language] ?? 'Please fill in the field'?>
                                    </div>
                                </div>
                                <div class="col-md-3" style="<?= !$stmCanViewAllCompanies ? 'display:none' : '' ?>">
                                    <label class="form-label small mb-1"><?=$languageArray['company_code'][$language]?> <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="stCompanyId" name="companyId" required>
                                        <?php while($rowStmCompany=mysqli_fetch_assoc($stmCompany)){ ?>
                                            <option value="<?=$rowStmCompany['id'] ?>" <?=($rowStmCompany['id'] == $stmCompanyId) ? 'selected' : ''?>><?=$rowStmCompany['name'] ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?=$languageArray['please_fill_in_the_field_code'][$language] ?? 'Please fill in the field'?>
                                    </div>
                                </div>
                                <div class="col-md-3" style="<?= !$stmCanViewAllPlants ? 'display:none' : '' ?>">
                                    <label class="form-label small mb-1"><?=$languageArray['plant_code'][$language]?> <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="stPlantId" name="plantId" required>
                                        <option value="">-</option>
                                        <?php while($rowStmPlant=mysqli_fetch_assoc($stmPlant)){ ?>
                                            <option value="<?=$rowStmPlant['id'] ?>" <?= ($rowStmPlant['id'] == $stmSelectedPlantId) ? 'selected' : '' ?>><?=$rowStmPlant['plant_code'] ?> - <?=$rowStmPlant['name'] ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?=$languageArray['please_fill_in_the_field_code'][$language] ?? 'Please fill in the field'?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1"><?=$languageArray['transaction_id_code'][$language]?> <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="stWeightId" name="weightId" required>
                                        <option value="">-</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        <?=$languageArray['please_fill_in_the_field_code'][$language] ?? 'Please fill in the field'?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1"><?=$languageArray['transaction_date_code'][$language]?></label>
                                    <input type="text" class="form-control readonly-field" id="stTransactionDate" name="transactionDate" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1"><?=$languageArray['transaction_status_code'][$language]?></label>
                                    <input type="text" class="form-control readonly-field" id="stTransactionStatus" name="transactionStatus" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1"><?=$languageArray['do_no_code'][$language]?></label>
                                    <input type="text" class="form-control readonly-field" id="stDoNo" name="doNo" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1"><?=$languageArray['customer_code'][$language]?> / <?=$languageArray['supplier_code'][$language]?></label>
                                    <input type="text" class="form-control readonly-field" id="stCustomerSupplier" name="customerSupplier" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1"><?=$languageArray['destination_code'][$language]?></label>
                                    <input type="text" class="form-control readonly-field" id="stDeliveredTo" name="deliveredTo" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1"><?=$languageArray['vehicle_no_code'][$language]?></label>
                                    <input type="text" class="form-control readonly-field" id="stLorryNo" name="lorryNo" readonly>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small mb-1"><?=$languageArray['remarks_code'][$language]?></label>
                                    <textarea class="form-control" id="stRemarks" name="remarks" rows="3" placeholder="<?=$languageArray['enter_remarks_message_code'][$language]?>"></textarea>
                                </div>
                            </div>
                            <input type="hidden" id="stId" name="id">
                            <input type="hidden" id="stTransactionId" name="transactionId">
                        </div>
                    </div>

                    <!-- Timber Details Section -->
                    <div class="card mb-0">
                        <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background-color: #405189;">
                            <h6 class="mb-0 text-white small">
                                <i class="ri-stack-line me-1"></i> <?=$languageArray['details_code'][$language]?>
                            </h6>
                            <button type="button" class="btn btn-success btn-sm" id="stAddDetail">
                                <i class="ri-add-circle-line align-middle me-1"></i>
                                <?=$languageArray['add_new_code'][$language]?>
                            </button>
                        </div>
                        <div class="card-body bg-light p-3" id="stDetailCardsContainer">
                            <div class="text-center text-muted py-4" id="stEmptyDetailState">
                                <i class="ri-inbox-line d-block fs-1 mb-2 opacity-50"></i>
                                <p class="mb-0"><?=$languageArray['no_timber_details_code'][$language] ?? 'No timber details added yet.'?></p>
                                <small><?=$languageArray['click_add_timber_code'][$language] ?? 'Click "Add New" to add timber details.'?></small>
                            </div>
                        </div>
                        <div class="card-footer py-2 px-3" id="stDetailTotalsFooter" style="display:none; background-color:#405189;">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-2">
                                    <span class="fw-bold text-white"><i class="ri-calculator-line me-1"></i><?=$languageArray['total_code'][$language]?></span>
                                </div>
                                <div class="col-md-10">
                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-white"><?=$languageArray['tons_code'][$language]?>:</span>
                                                <span class="fw-bold" style="color:#5eff5e;" id="stTotalTons">0.0000</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-white"><?=$languageArray['kd_charges_code'][$language]?>:</span>
                                                <span class="fw-bold" style="color:#ffeb3b;" id="stTotalKdCharges">0.00</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-white"><?=$languageArray['bundling_charges_code'][$language]?>:</span>
                                                <span class="fw-bold" style="color:#ffeb3b;" id="stTotalBundlingCharges">0.00</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-white"><?=$languageArray['kd_charges_code'][$language]?>:</span>
                                                <span class="fw-bold" style="color:#ffeb3b;" id="stTotalGraderFees">0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden table for form serialization -->
                        <table id="stDetailTable"><tbody></tbody></table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <?=$languageArray['close_code'][$language]?>
                </button>
                <button type="button" class="btn btn-success" id="saveSawnTimber">
                    <?=$languageArray['submit_code'][$language]?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form row per detail - its inputs carry the posted field names -->
<script type="text/html" id="stDetailRowTemplate">
    <tr class="detail-row">
        <td><input type="text" class="form-control species" data-field="species"></td>
        <td><input type="text" class="form-control lot" data-field="lot"></td>
        <td><input type="text" class="form-control bundle" data-field="bundle"></td>
        <td><input type="number" step="0.0001" class="form-control thick" data-field="thick"></td>
        <td><input type="number" step="0.0001" class="form-control width" data-field="width"></td>
        <td><input type="number" step="0.0001" class="form-control length" data-field="length"></td>
        <td><input type="number" step="1" class="form-control pieces" data-field="pieces"></td>
        <td><input type="number" step="0.0001" class="form-control tons" data-field="tons" readonly value="0.0000"></td>
        <td><input type="number" step="0.01" class="form-control kdCharges" data-field="kdCharges"></td>
        <td><input type="number" step="0.01" class="form-control bundlingCharges" data-field="bundlingCharges"></td>
        <td><input type="number" step="0.01" class="form-control graderFees" data-field="graderFees"></td>
    </tr>
</script>

<!-- Visible detail card per detail -->
<script type="text/html" id="stDetailCardTemplate">
    <div class="card mb-2 detail-card" data-card-index="{INDEX}">
        <div class="card-header py-1 px-2 d-flex justify-content-between align-items-center" style="background-color:#e9ecef; border-left:3px solid #405189;">
            <span class="fw-semibold text-primary small card-number card-toggle" style="cursor:pointer; flex:1;">
                <i class="ri-arrow-down-s-line me-1 collapse-icon"></i><?=$languageArray['item_code'][$language]?> #{NUMBER} <span class="card-summary text-muted fw-normal"></span>
            </span>
            <button type="button" class="btn btn-outline-danger btn-sm py-0 px-1 remove-card">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
        <div class="card-body p-2 card-collapse-body">
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['species_code'][$language]?></label>
                    <input type="text" class="form-control form-control-sm card-species" placeholder="Species">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['lot_code'][$language]?></label>
                    <input type="text" class="form-control form-control-sm card-lot" placeholder="Lot">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['bundle_code'][$language]?></label>
                    <input type="text" class="form-control form-control-sm card-bundle" placeholder="Bundle">
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['thick_code'][$language]?> (m) <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" class="form-control form-control-sm card-thick" placeholder="0">
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['width_code'][$language]?> (m) <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" class="form-control form-control-sm card-width" placeholder="0">
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['length_code'][$language]?> (m) <span class="text-danger">*</span></label>
                    <input type="number" step="0.0001" class="form-control form-control-sm card-length" placeholder="0">
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['pieces_code'][$language]?> <span class="text-danger">*</span></label>
                    <input type="number" step="1" class="form-control form-control-sm card-pieces" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['tons_code'][$language]?></label>
                    <input type="number" step="0.0001" class="form-control form-control-sm tons-highlight card-tons" readonly value="0.0000">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['kd_charges_code'][$language]?></label>
                    <input type="number" step="0.01" class="form-control form-control-sm card-kdCharges" placeholder="0.00">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['bundling_charges_code'][$language]?></label>
                    <input type="number" step="0.01" class="form-control form-control-sm card-bundlingCharges" placeholder="0.00">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0"><?=$languageArray['grader_fees_code'][$language]?></label>
                    <input type="number" step="0.01" class="form-control form-control-sm card-graderFees" placeholder="0.00">
                </div>
            </div>
        </div>
    </div>
</script>
