<?php
// Customer side info modal component markup (#customerSideInfoModal).
// Script: components/customerSideInfoModal/script.php
?>
<div class="modal fade" id="customerSideInfoModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" id="customerSideInfoForm">
                <div class="modal-header bg-gray-dark color-palette">
                    <h4 class="modal-title"><?=$languageArray['fill_in_customer_side_info_code'][$language]?></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label for="custSideDoNo" class="col-sm-4 col-form-label"><?=$languageArray['customer_side_do_no_code'][$language]?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="custSideDoNo" name="custSideDoNo">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="custSideFirstWeight" class="col-sm-4 col-form-label"><?=$languageArray['first_code'][$language]?> (KG)</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="number" class="form-control cust-side-weight" id="custSideFirstWeight" name="custSideFirstWeight" placeholder="0">
                                <div class="input-group-text">Kg</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="custSideSecondWeight" class="col-sm-4 col-form-label"><?=$languageArray['second_code'][$language]?> (KG)</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input type="number" class="form-control cust-side-weight" id="custSideSecondWeight" name="custSideSecondWeight" placeholder="0">
                                <div class="input-group-text">Kg</div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="custSideMc" class="col-sm-4 col-form-label"><?=$languageArray['customer_side_mc_code'][$language]?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="custSideMc" name="custSideMc">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="custSideNettWeight" class="col-sm-4 col-form-label"><?=$languageArray['nett_weight_code'][$language]?> (KG)</label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control input-readonly" id="custSideNettWeight" name="custSideNettWeight" placeholder="0" readonly>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" id="customerSideInfoId" name="id">
                    <input type="hidden" class="form-control" name="action" value="save">
                </div>
                <div class="modal-footer justify-content-between bg-gray-dark color-palette">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                    <button type="button" class="btn btn-success" id="submitCustomerSideInfo"><?=$languageArray['submit_code'][$language]?></button>
                </div>
            </form>
        </div>
    </div>
</div>
