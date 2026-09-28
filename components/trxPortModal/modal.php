<?php
// Transfer to port info modal component markup (#trxPortModal).
// Script: components/trxPortModal/script.php
?>
<div class="modal fade" id="trxPortModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form role="form" id="trxPortForm">
                <div class="modal-header bg-gray-dark color-palette">
                    <h4 class="modal-title"><?=$languageArray['trx_to_port_code'][$language]?></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <label for="portSpQty" class="col-sm-4 col-form-label"><?=$languageArray['sp_quantity_nett_weight_code'][$language]?></label>
                        <div class="col-sm-8">
                            <input type="number" class="form-control" id="portSpQty" name="portSpQty" placeholder="0" step="any">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="portRefNo" class="col-sm-4 col-form-label"><?=$languageArray['ref_no_code'][$language]?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="portRefNo" name="portRefNo" maxlength="100">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="portLocation" class="col-sm-4 col-form-label"><?=$languageArray['location_code'][$language]?></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="portLocation" name="portLocation" maxlength="255">
                        </div>
                    </div>
                    <input type="hidden" class="form-control" id="trxPortId" name="id">
                    <input type="hidden" class="form-control" id="trxPortIsContainer" name="isContainer">
                </div>
                <div class="modal-footer justify-content-between bg-gray-dark color-palette">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                    <button type="button" class="btn btn-success" id="submitTrxPort"><?=$languageArray['submit_code'][$language]?></button>
                </div>
            </form>
        </div>
    </div>
</div>
