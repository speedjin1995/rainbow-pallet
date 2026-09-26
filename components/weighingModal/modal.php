<?php
// Weighing modal component markup (#addModal + #prePrintModal).
// Requires components/weighingModal/data.php to be included first.
?>
                                    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable custom-xxl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalScrollableTitle"><?=$languageArray['add_new_code'][$language]?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form role="form" id="weightForm" class="needs-validation" novalidate autocomplete="off">
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <div class="hstack gap-2 justify-content-center">
                                                                    <div class="col-xl-12 col-md-12 col-md-12">
                                                                        <div class="card bg-primary">
                                                                            <div class="card-body">
                                                                                <div class="d-flex justify-content-between">
                                                                                    <div>
                                                                                        <h3 class="ff-secondary fw-semibold text-white"><?=$languageArray['indicator_weight_code'][$language]?></h3>
                                                                                        <h2 class="mt-4 ff-secondary fw-semibold display-3 text-white"><span class="counter-value" id="indicatorWeight">0</span> Kg</h2>
                                                                                    </div>
                                                                                    <div>
                                                                                        <div class="avatar-sm flex-shrink-0">
                                                                                            <span class="avatar-title bg-soft-light rounded-circle fs-2">
                                                                                                <i class="mdi mdi-weight-kilogram"></i>
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div><!-- end card body -->
                                                                        </div> <!-- end card-->
                                                                    </div> <!-- end col-->
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <div class="hstack gap-2 justify-content-center">
                                                                    <div class="col-xl-12 col-md-12 col-md-12">
                                                                        <div class="card bg-primary">
                                                                            <div class="card-body">
                                                                                <div class="d-flex justify-content-between">
                                                                                    <div>
                                                                                        <h3 class="ff-secondary fw-semibold text-white"><?=$languageArray['final_weight_code'][$language]?></h3>
                                                                                        <h2 class="mt-4 ff-secondary fw-semibold display-3 text-white"><span class="counter-value" id="currentWeight">0</span> Kg</h2>
                                                                                    </div>
                                                                                    <div>
                                                                                        <div class="avatar-sm flex-shrink-0">
                                                                                            <span class="avatar-title bg-soft-light rounded-circle fs-2">
                                                                                                <i class="mdi mdi-weight-kilogram"></i>
                                                                                            </span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div><!-- end card body -->
                                                                        </div> <!-- end card-->
                                                                    </div> <!-- end col-->
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row col-12">
                                                            <div class="col-xxl-12 col-lg-12">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="companyId" class="col-sm-4 col-form-label"><?=$languageArray['company_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-select select2" id="companyId" name="companyId" required>
                                                                                            <?php while($rowCompany=mysqli_fetch_assoc($wmCompany)){ ?>
                                                                                                <option value="<?=$rowCompany['id'] ?>" <?=($rowCompany['id'] == $wmCompanyId) ? 'selected' : ''?>><?=$rowCompany['name'] ?></option>
                                                                                            <?php } ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3" id="divCustomerName">
                                                                                <div class="row">
                                                                                    <label for="customerName" class="col-sm-4 col-form-label"><?=$languageArray['customer_name_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <div class="input-group">
                                                                                            <div class="input-group-text">
                                                                                                <input class="form-check-input mt-0" id="manualCustomer" name="manualCustomer" type="checkbox" value="0">
                                                                                            </div>
                                                                                            <input type="text" class="form-control" id="customerNameTxt" name="customerNameTxt" placeholder="<?=$languageArray['customer_name_code'][$language]?>" style="display:none">
                                                                                            <div class="col-10 index-customer">
                                                                                                <select class="form-select js-choice select2" id="customerName" name="customerName" required>
                                                                                                    <option selected="-">-</option>
                                                                                                    <?php while($rowCustomer=mysqli_fetch_assoc($wmCustomer)){ ?>
                                                                                                        <option value="<?=$rowCustomer['name'] ?>" data-code="<?=$rowCustomer['customer_code'] ?>"><?=$rowCustomer['name'] ?></option>
                                                                                                    <?php } ?>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3" id="divSupplierName" style="display:none;">
                                                                                <div class="row">
                                                                                    <label for="supplierName" class="col-sm-4 col-form-label"><?=$languageArray['supplier_name_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <div class="input-group">
                                                                                            <div class="input-group-text">
                                                                                                <input class="form-check-input mt-0" id="manualSupplier" name="manualSupplier" type="checkbox" value="0">
                                                                                            </div>
                                                                                            <input type="text" class="form-control" id="supplierNameTxt" name="supplierNameTxt" placeholder="<?=$languageArray['supplier_name_code'][$language]?>" style="display:none">
                                                                                            <div class="col-10 index-supplier">
                                                                                                <select class="form-select select2" id="supplierName" name="supplierName" required>
                                                                                                    <option selected="-">-</option>
                                                                                                    <?php while($rowSupplier=mysqli_fetch_assoc($wmSupplier)){ ?>
                                                                                                        <option value="<?=$rowSupplier['name'] ?>" data-code="<?=$rowSupplier['supplier_code'] ?>"><?=$rowSupplier['name'] ?></option>
                                                                                                    <?php } ?>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row" id="containerDisplay">
                                                                                    <label for="containerNoInput" class="col-sm-4 col-form-label"><?=$languageArray['container_no1_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="containerNoInput" name="containerNoInput" placeholder="<?=$languageArray['container_no_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row" id="emptyContainerDisplay" style="display:none" >
                                                                                    <label for="emptyContainerNo" class="col-sm-4 col-form-label" id="containerNo1Label"><?=$languageArray['container_no1_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-select select2" id="emptyContainerNo" name="emptyContainerNo">
                                                                                            <option selected="-">-</option>
                                                                                            <?php /*while($rowContainer=mysqli_fetch_assoc($wmContainer)){ ?>
                                                                                                <option value="<?=$rowContainer['container_no'] ?>"><?=$rowContainer['container_no'] ?></option>
                                                                                            <?php }*/ ?>
                                                                                        </select>                   
                                                                                    </div>
                                                                                </div>
                                                                                <input type="text" class="form-control" id="containerNo" name="containerNo" hidden>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="transactionId" class="col-sm-4 col-form-label"><?=$languageArray['transaction_id_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control input-readonly" id="transactionId" name="transactionId" placeholder="<?=$languageArray['transaction_id_code'][$language]?>" readonly>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row" id="productNameDisplay">
                                                                                    <label for="productName" class="col-sm-4 col-form-label"><?=$languageArray['product_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <div class="input-group">
                                                                                            <div class="input-group-text">
                                                                                                <input class="form-check-input mt-0" id="manualProduct" name="manualProduct" type="checkbox" value="0">
                                                                                            </div>
                                                                                            <input type="text" class="form-control" id="productNameTxt" name="productNameTxt" placeholder="<?=$languageArray['product_code'][$language]?>" style="display:none">
                                                                                            <div class="col-10 index-product">
                                                                                                <select class="form-select select2" id="productName" name="productName" required>
                                                                                                    <option selected="-">-</option>
                                                                                                    <?php while($rowProduct=mysqli_fetch_assoc($wmProduct)){ ?>
                                                                                                        <option 
                                                                                                            value="<?=$rowProduct['name'] ?>" 
                                                                                                            data-code="<?=$rowProduct['product_code'] ?>" 
                                                                                                            data-high="<?=$rowProduct['high'] ?>" 
                                                                                                            data-low="<?=$rowProduct['low'] ?>" 
                                                                                                            data-variance="<?=$rowProduct['variance'] ?>" 
                                                                                                            data-description="<?=$rowProduct['description'] ?>"
                                                                                                            data-is-sales="<?=$rowProduct['is_sales'] ?>"
                                                                                                            data-is-purchase="<?=$rowProduct['is_purchase'] ?>"
                                                                                                            data-is-port="<?=$rowProduct['is_port'] ?>"
                                                                                                            data-is-misc="<?=$rowProduct['is_misc'] ?>">
                                                                                                            <?=$rowProduct['product_code'] .' - '. $rowProduct['name']?>
                                                                                                        </option>
                                                                                                    <?php } ?>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row" id="rawMaterialDisplay" style="display:none;">
                                                                                    <label for="rawMaterialName" class="col-sm-4 col-form-label"><?=$languageArray['raw_material_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <div class="input-group">
                                                                                            <div class="input-group-text">
                                                                                                <input class="form-check-input mt-0" id="manualRawMaterial" name="manualRawMaterial" type="checkbox" value="0">
                                                                                            </div>
                                                                                            <input type="text" class="form-control" id="rawMaterialNameTxt" name="rawMaterialNameTxt" placeholder="<?=$languageArray['raw_material_code'][$language]?>" style="display:none">
                                                                                            <div class="col-10 index-rawmaterial">
                                                                                                <select class="form-select select2" id="rawMaterialName" name="rawMaterialName" required>
                                                                                                    <option selected="-">-</option>
                                                                                                    <?php while($rowRowMat=mysqli_fetch_assoc($wmRawMaterial)){ ?>
                                                                                                        <option value="<?=$rowRowMat['name'] ?>" data-code="<?=$rowRowMat['product_code'] ?>" data-is-sales="<?=$rowRowMat['is_sales'] ?>" data-is-purchase="<?=$rowRowMat['is_purchase'] ?>" data-is-port="<?=$rowRowMat['is_port'] ?>" data-is-misc="<?=$rowRowMat['is_misc'] ?>"><?=$rowRowMat['product_code'] .' - '. $rowRowMat['name'] ?></option>
                                                                                                    <?php } ?>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3" id="sealNoDisplay">
                                                                                <div class="row">
                                                                                    <label for="sealNo" class="col-sm-4 col-form-label"><?=$languageArray['seal_no_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="sealNo" name="sealNo" placeholder="<?=$languageArray['seal_no_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="transactionDate" class="col-sm-4 col-form-label"><?=$languageArray['transaction_date_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="date" class="form-control input-readonly" data-provider="flatpickr" id="transactionDate" name="transactionDate" required>
                                                                                        <div class="invalid-feedback">
                                                                                            <?=$languageArray['please_fill_in_the_field_code'][$language] ?? 'Please fill in the field'?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="purchaseOrder" class="col-sm-4 col-form-label"><?=$languageArray['po_no_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="purchaseOrder" name="purchaseOrder">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3" id="containerNo2Display">
                                                                                <div class="row">
                                                                                    <label for="containerNo2" class="col-sm-4 col-form-label"><?=$languageArray['container_no2_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="containerNo2" name="containerNo2" placeholder="<?=$languageArray['container_no2_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="transactionStatus" class="col-sm-4 col-form-label"><?=$languageArray['transaction_status_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select id="transactionStatus" name="transactionStatus" class="form-select select2" required>
                                                                                            <?php if(hasModulePermission('Weighing', 'Sales', ['create', 'edit'])) { ?>
                                                                                                <option value="Sales" selected><?=$languageArray['dispatch_code'][$language]?></option>
                                                                                            <?php } ?>
                                                                                            <?php if(hasModulePermission('Weighing', 'Purchase', ['create', 'edit'])) { ?>
                                                                                                <option value="Purchase"><?=$languageArray['receiving_code'][$language]?></option>
                                                                                            <?php } ?>
                                                                                            <?php if(hasModulePermission('Weighing', 'Local', ['create', 'edit'])) { ?>
                                                                                                <!-- <option value="Local"><?=$languageArray['internal_transfer_code'][$language]?></option> -->
                                                                                            <?php } ?>
                                                                                            <?php if(hasModulePermission('Weighing', 'Port', ['create', 'edit'])) { ?>
                                                                                                <option value="Port"><?=$languageArray['trx_to_port_code'][$language]?></option>
                                                                                            <?php } ?>
                                                                                            <?php if(hasModulePermission('Weighing', 'Miscellaneous', ['create', 'edit'])) { ?>
                                                                                                <option value="Misc"><?=$languageArray['miscellaneous_code'][$language]?></option>
                                                                                            <?php } ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3" id="doDisplay">
                                                                                <div class="row">
                                                                                    <label for="deliveryNo" class="col-sm-4 col-form-label"><?=$languageArray['delivery_no_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="deliveryNo" name="deliveryNo" placeholder="<?=$languageArray['delivery_no_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3" id="sealNo2Display">
                                                                                <div class="row">
                                                                                    <label for="sealNo2" class="col-sm-4 col-form-label"><?=$languageArray['seal_no2_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="sealNo2" name="sealNo2" placeholder="<?=$languageArray['seal_no2_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3" id="replacementContainerDisplay" style="display:none">
                                                                                <div class="row">
                                                                                    <label for="replacementContainer" class="col-sm-4 col-form-label"><?=$languageArray['new_empty_bin_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="replacementContainer" name="replacementContainer" placeholder="Replacement Container" required>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="weightType" class="col-sm-4 col-form-label"><?=$languageArray['weight_type_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select id="weightType" name="weightType" class="form-select select2" required>
                                                                                            <?php if(hasModulePermission('Weighing', 'Normal Type', ['view'])) { ?>
                                                                                                <option value="Normal" selected><?=$languageArray['normal_weighing_code'][$language]?></option>
                                                                                            <?php } ?>
                                                                                            <?php if(hasModulePermission('Weighing', 'Container Type', ['view'])) { ?>
                                                                                                <option value="Container"><?=$languageArray['primer_mover_code'][$language]?></option>
                                                                                            <?php } ?>
                                                                                            <?php if(hasModulePermission('Weighing', 'Empty Container Type', ['view'])) { ?>
                                                                                                <option value="Empty Container"><?=$languageArray['primer_mover_container_code'][$language]?></option>
                                                                                            <?php } ?>
                                                                                            <?php if(hasModulePermission('Weighing', 'Different Container Type', ['view'])) { ?>
                                                                                                <option value="Different Container"><?=$languageArray['primer_mover_different_bins_code'][$language]?></option>
                                                                                            <?php } ?>
                                                                                        </select>   
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="invoiceNo" class="col-sm-4 col-form-label"><?=$languageArray['invoice_no_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="invoiceNo" name="invoiceNo" placeholder="<?=$languageArray['invoice_no_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="project" class="col-sm-4 col-form-label"><?=$languageArray['project_code_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-select select2" id="project" name="project">
                                                                                            <option selected="-">-</option>
                                                                                            <?php while($rowProject=mysqli_fetch_assoc($wmProjects)){ ?>
                                                                                                <option value="<?=$rowProject['id'] ?>"><?=$rowProject['project_code'] ?></option>
                                                                                            <?php } ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="plant" class="col-sm-4 col-form-label"><?=$languageArray['plant_code'][$language]?> <span class="text-danger">*</span></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-select select2" id="plant" name="plant" required>
                                                                                            <?php while($rowPlant=mysqli_fetch_assoc($wmPlant)){ ?>
                                                                                                <option value="<?=$rowPlant['name'] ?>" data-code="<?=$rowPlant['plant_code'] ?>"><?=$rowPlant['name'] ?></option>
                                                                                            <?php } ?>
                                                                                        </select>        
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="transporter" class="col-sm-4 col-form-label"><?=$languageArray['transporter_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-select select2" id="transporter" name="transporter" required>
                                                                                            <option selected="-">-</option>
                                                                                            <option value="Own Transport" data-code="Own Transport"><?=$languageArray['own_transport_code'][$language]?></option>
                                                                                            <option value="Third Party" data-code="Third Party"><?=$languageArray['third_party_code'][$language]?></option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="destination" class="col-sm-4 col-form-label"><?=$languageArray['destination_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-select select2" id="destination" name="destination" required>
                                                                                            <option selected="-">-</option>
                                                                                            <?php while($rowDestination=mysqli_fetch_assoc($wmDestination)){ ?>
                                                                                                <option value="<?=$rowDestination['name'] ?>" data-code="<?=$rowDestination['destination_code'] ?>"><?=$rowDestination['name'] ?></option>
                                                                                            <?php } ?>
                                                                                        </select>            
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-xxl-12 col-lg-12">
                                                                                <div class="row">
                                                                                    <label for="otherRemarks" class="col-sm-1 col-form-label" style="width: 11%;"><?=$languageArray['other_remarks_code'][$language]?></label>
                                                                                    <div class="col-sm-11" style="width: 89%;">
                                                                                        <textarea class="form-control" id="otherRemarks" name="otherRemarks" placeholder="<?=$languageArray['other_remarks_code'][$language]?>"></textarea>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row col-12 mb-2">
                                                            <div class="col-12">
                                                                <div class="d-flex align-items-center border-bottom pb-2">
                                                                    <i class="ri-scales-3-line fs-5 text-primary me-2"></i>
                                                                    <span class="fw-semibold"><?=$languageArray['weighing_code'][$language]?></span>
                                                                    <div class="d-flex align-items-center ms-4  <?php if(!hasPermission('Weighing', ['manual_weighing'])){ echo 'd-none'; }?>">
                                                                        <span class="text-dark me-2"><?=$languageArray['manual_weight_code'][$language]?></span>
                                                                        <div class="form-check form-switch mb-0">
                                                                            <input class="form-check-input" type="checkbox" role="switch" id="manualWeightToggle" name="manualWeight" value="false" style="width: 4em; height: 1.5em; cursor: pointer;">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row col-12">
                                                            <div class="col-xxl-4 col-lg-4" id="normalCard">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row mb-3">
                                                                            <label for="vehiclePlateNo1" class="col-sm-4 col-form-label">
                                                                                <?=$languageArray['vehicle_plate_no_code'][$language]?> <span class="text-danger">*</span>
                                                                            </label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <div class="input-group-text">
                                                                                        <input class="form-check-input mt-0" id="manualVehicle" name="manualVehicle" type="checkbox" value="0" aria-label="Checkbox for following text input">
                                                                                    </div>
                                                                                    <input type="text" class="form-control" id="vehicleNoTxt" name="vehicleNoTxt" placeholder="Vehicle Plate No" style="display:none" >
                                                                                    <div class="col-10 index-vehicle">
                                                                                        <select class="form-select select2" id="vehiclePlateNo1" name="vehiclePlateNo1">
                                                                                            <option selected="-">-</option>
                                                                                            <?php while($row2=mysqli_fetch_assoc($wmVehicles)){ ?>
                                                                                                <option value="<?=$row2['veh_number'] ?>" data-weight="<?=$row2['vehicle_weight'] ?>"><?=$row2['veh_number'] ?></option>
                                                                                            <?php } ?>
                                                                                        </select>
                                                                                        <input type="text" class="form-control" id="vehiclePlateNo1Edit" name="vehiclePlateNo1Edit" hidden>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label for="grossIncoming" class="col-sm-4 col-form-label"><?=$languageArray['incoming_code'][$language]?> <span class="text-danger">*</span></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="grossIncoming" name="grossIncoming" placeholder="0" required readonly>
                                                                                    <div class="input-group-text">Kg</div>
                                                                                    <button class="input-group-text btn btn-success fs-5" id="grossCapture" type="button"><i class="mdi mdi-sync"></i></button>
                                                                                    <div class="invalid-feedback">
                                                                                        <?=$languageArray['please_fill_in_the_field_code'][$language] ?? 'Please fill in the field'?>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row mb-3">
                                                                            <label for="grossIncomingDate" class="col-sm-4 col-form-label"><?=$languageArray['incoming_date_code'][$language]?> <span class="text-danger">*</span></label>
                                                                            <div class="col-sm-8">
                                                                                <input type="text" class="form-control input-readonly" id="grossIncomingDate" name="grossIncomingDate" required>
                                                                                <div class="invalid-feedback">
                                                                                    <?=$languageArray['please_fill_in_the_field_code'][$language] ?? 'Please fill in the field'?>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row mb-3">
                                                                            <label for="tareOutgoing" class="col-sm-4 col-form-label"><?=$languageArray['outgoing_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <!-- <div class="input-group-text">
                                                                                        <input class="form-check-input mt-0" id="manualOutgoing" name="manualOutgoing" type="checkbox" value="0" aria-label="Checkbox for following text input">
                                                                                    </div>-->
                                                                                    <input type="number" class="form-control input-readonly" id="tareOutgoing" name="tareOutgoing" placeholder="0" readonly>
                                                                                    <div class="input-group-text">Kg</div>
                                                                                    <button class="input-group-text btn btn-success fs-5" id="tareCapture" type="button"><i class="mdi mdi-sync"></i></button>
                                                                                </div>                                                                                       
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label for="tareOutgoingDate" class="col-sm-4 col-form-label"><?=$languageArray['outgoing_date_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <input type="text" class="form-control input-readonly" id="tareOutgoingDate" name="tareOutgoingDate">
                                                                            </div>
                                                                        </div>                                                                        
                                                                        <div class="row mb-3">
                                                                            <label for="nettWeight" class="col-sm-4 col-form-label"><?=$languageArray['nett_weight_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="nettWeight" name="nettWeight" placeholder="0" readonly>
                                                                                    <div class="input-group-text">Kg</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>                        
                                                                </div>
                                                            </div>
                                                            <div class="col-xxl-4 col-lg-4" id="containerCard" style="display:none;">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row mb-3">
                                                                            <label for="vehiclePlateNo2" class="col-sm-4 col-form-label"><?=$languageArray['vehicle_plate_no_code'][$language]?>2</label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <div class="input-group-text">
                                                                                        <input class="form-check-input mt-0" id="manualVehicle2" name="manualVehicle2" type="checkbox" value="0" aria-label="Checkbox for following text input">
                                                                                    </div>
                                                                                    <input type="text" class="form-control" id="vehicleNoTxt2" name="vehicleNoTxt2" placeholder="Vehicle Plate No" style="display:none">
                                                                                    <div class="col-10 index-vehicle2">
                                                                                        <select class="form-select select2" id="vehiclePlateNo2" name="vehiclePlateNo2">
                                                                                            <option selected="-">-</option>
                                                                                            <?php while($rowv2=mysqli_fetch_assoc($wmVehicles2)){ ?>
                                                                                                <option value="<?=$rowv2['veh_number'] ?>" data-weight="<?=$rowv2['vehicle_weight'] ?>"><?=$rowv2['veh_number'] ?></option>
                                                                                            <?php } ?>
                                                                                        </select>
                                                                                    </div>
                                                                                    <div class="invalid-feedback">
                                                                                        Please fill in the field.
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3" id="vehicleWeight2Display" style="display:none">
                                                                            <label for="vehicleWeight2" class="col-sm-4 col-form-label"><?=$languageArray['vehicle_weight_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="vehicleWeight2" name="vehicleWeight2" placeholder="0">
                                                                                    <div class="input-group-text">Kg</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label for="grossIncoming2" class="col-sm-4 col-form-label"><?=$languageArray['incoming_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="grossIncoming2" name="grossIncoming2" placeholder="0" readonly>
                                                                                    <div class="input-group-text">Kg</div>
                                                                                    <button class="input-group-text btn btn-success fs-5" id="grossCapture2"><i class="mdi mdi-sync" type="button"></i></button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label for="grossIncomingDate2" class="col-sm-4 col-form-label"><?=$languageArray['incoming_date_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <input type="text" class="form-control input-readonly" id="grossIncomingDate2" name="grossIncomingDate2">
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3" id="container2WeightDisplay" style="display:none">
                                                                            <label for="emptyContainerWeight2" class="col-sm-4 col-form-label"><?=$languageArray['empty_container_weight_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="emptyContainerWeight2" name="emptyContainerWeight2" placeholder="0" readonly>
                                                                                    <div class="input-group-text">Kg</div>
                                                                                    <div class="input-group-text">-</div>
                                                                                    <div class="input-group-text" id="replaceContainerText"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label for="tareOutgoing2" class="col-sm-4 col-form-label"><?=$languageArray['outgoing_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="tareOutgoing2" name="tareOutgoing2" placeholder="0" readonly>
                                                                                    <div class="input-group-text">Kg</div>
                                                                                    <button class="input-group-text btn btn-success fs-5" id="tareCapture2" type="button"><i class="mdi mdi-sync"></i></button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label for="tareOutgoingDate2" class="col-sm-4 col-form-label"><?=$languageArray['outgoing_date_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <input type="text" class="form-control input-readonly" placeholder="" id="tareOutgoingDate2" name="tareOutgoingDate2">
                                                                            </div>
                                                                        </div>                                                                        
                                                                        <div class="row mb-3">
                                                                            <label for="nettWeight2" class="col-sm-4 col-form-label"><?=$languageArray['nett_weight_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="nettWeight2" name="nettWeight2" placeholder="0" readonly>
                                                                                    <div class="input-group-text">Kg</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>                                                                    
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xxl-4 col-lg-4" id="extraWeightCard">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row mb-3" id="divOrderWeight">
                                                                            <label for="orderWeight" class="col-sm-4 col-form-label"><?=$languageArray['order_weight_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control" id="orderWeight" name="orderWeight"  placeholder="<?=$languageArray['order_weight_code'][$language]?>">
                                                                                    <div class="input-group-text">Kg</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3" id="divSupplierWeight" style="display:none;">
                                                                            <label for="supplierWeight" class="col-sm-4 col-form-label"><?=$languageArray['supplier_weight_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control" id="supplierWeight" name="supplierWeight"  placeholder="<?=$languageArray['supplier_weight_code'][$language]?>">
                                                                                    <div class="input-group-text">Kg</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label for="reduceWeight" class="col-sm-4 col-form-label"><?=$languageArray['reduce_weight_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control" id="reduceWeight" name="reduceWeight" placeholder="0">
                                                                                    <div class="input-group-text">Kg</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label for="weightDifference" class="col-sm-4 col-form-label"><?=$languageArray['weight_difference_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="weightDifference" name="weightDifference" placeholder="Weight Difference" readonly>
                                                                                    <div class="input-group-text">Kg</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3">
                                                                            <label for="weightDifferencePerc" class="col-sm-4 col-form-label">% <?=$languageArray['variance_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="weightDifferencePerc" name="weightDifferencePerc" placeholder="Variance %" readonly>
                                                                                    <div class="input-group-text">%</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>                                                                   
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xxl-12 col-lg-12 mb-2" id="customerSideLabel" style="display:none;">
                                                                <div class="d-flex align-items-center border-bottom pb-2">
                                                                    <i class="ri-user-location-line fs-5 text-primary me-2"></i>
                                                                    <span class="fw-semibold"><?=$languageArray['customer_side_info_code'][$language]?></span>
                                                                </div>
                                                            </div>
                                                            <div class="col-xxl-12 col-lg-12" id="customerSideCard" style="display:none;">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="customerSideCompany" class="col-sm-4 col-form-label"><?=$languageArray['customer_side_company_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="customerSideCompany" name="customerSideCompany" placeholder="<?=$languageArray['customer_side_company_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="customerSideRemovalPassNo" class="col-sm-4 col-form-label"><?=$languageArray['customer_side_removal_pass_no_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="customerSideRemovalPassNo" name="customerSideRemovalPassNo" placeholder="<?=$languageArray['customer_side_removal_pass_no_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="customerSideLicenseNo" class="col-sm-4 col-form-label"><?=$languageArray['customer_side_license_no_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="customerSideLicenseNo" name="customerSideLicenseNo" placeholder="<?=$languageArray['customer_side_license_no_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="customerSideMoistureContent" class="col-sm-4 col-form-label"><?=$languageArray['customer_side_moisture_content_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="customerSideMoistureContent" name="customerSideMoistureContent" placeholder="<?=$languageArray['customer_side_moisture_content_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="customerSideOfficerName" class="col-sm-4 col-form-label"><?=$languageArray['customer_side_officer_name_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="customerSideOfficerName" name="customerSideOfficerName" placeholder="<?=$languageArray['customer_side_officer_name_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="customerSideRainbowDriver" class="col-sm-4 col-form-label"><?=$languageArray['customer_side_rainbow_driver_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="customerSideRainbowDriver" name="customerSideRainbowDriver" placeholder="<?=$languageArray['customer_side_rainbow_driver_code'][$language]?>">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="customerSideTimeIn" class="col-sm-4 col-form-label"><?=$languageArray['customer_side_time_in_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="customerSideTimeIn" name="customerSideTimeIn">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-4 col-lg-4 mb-3">
                                                                                <div class="row">
                                                                                    <label for="customerSideTimeOut" class="col-sm-4 col-form-label"><?=$languageArray['customer_side_time_out_code'][$language]?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="customerSideTimeOut" name="customerSideTimeOut">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-xxl-4 col-lg-4" id="priceCard" style="display:none;">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row mb-3">
                                                                            <label for="unitPrice" class="col-sm-4 col-form-label"><?=$languageArray['unit_price_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="unitPrice" name="unitPrice" placeholder="0" readonly>
                                                                                    <div class="input-group-text">RM</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3" id="sstDisplay">
                                                                            <label for="sstPrice" class="col-sm-4 col-form-label"><?=$languageArray['sst_code'][$language]?> (6%)</label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="sstPrice" name="sstPrice" placeholder="0" readonly>
                                                                                    <div class="input-group-text">RM</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3" id="subTotalPriceDisplay">
                                                                            <label for="subTotalPrice" class="col-sm-4 col-form-label"><?=$languageArray['sub_total_price_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="subTotalPrice" name="subTotalPrice" placeholder="0" readonly>
                                                                                    <div class="input-group-text">RM</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row mb-3" id="totalPriceDisplay">
                                                                            <label for="totalPrice" class="col-sm-4 col-form-label"><?=$languageArray['total_price_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <div class="input-group">
                                                                                    <input type="number" class="form-control input-readonly" id="totalPrice" name="totalPrice" placeholder="0" readonly>
                                                                                    <div class="input-group-text">RM</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>                                                                 
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-lg-12">
                                                            <div class="hstack gap-2 justify-content-end">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                                <button type="button" class="btn btn-success" id="submitWeightPrint"><?=$languageArray['submit_print_code'][$language]?></button>
                                                                <button type="button" class="btn btn-primary" id="submitWeight"><?=$languageArray['submit_code'][$language]?></button>
                                                            </div>
                                                        </div><!--end col-->   

                                                        <!-- All Hidden Fields -->
                                                        <div class="col-xxl-4 col-lg-4 mb-3" style="display:none;">
                                                            <div class="row">
                                                                <label for="customerType" class="col-sm-4 col-form-label">Customer Type</label>
                                                                <div class="col-sm-8">
                                                                    <select id="customerType" name="customerType" class="form-select select2">
                                                                        <option>Cash</option>
                                                                        <option selected>Normal</option>
                                                                    </select>   
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-xxl-4 col-lg-4 mb-3" style="display:none;">
                                                            <div class="row">
                                                                <label for="poSupplyWeight" class="col-sm-4 col-form-label">P/O Supply Weight</label>
                                                                <div class="col-sm-8">
                                                                    <div class="input-group">
                                                                        <input type="number" class="form-control input-readonly" id="poSupplyWeight" name="poSupplyWeight" placeholder="P/O Supply Weight" readonly>
                                                                        <div class="input-group-text">Kg</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div> 
                                                        <div class="col-xxl-4 col-lg-4 mb-3" style="display:none;">
                                                            <div class="row">
                                                                <label for="balance" class="col-sm-4 col-form-label">Balance</label>
                                                                <div class="col-sm-8">
                                                                    <input type="text" class="form-control input-readonly text-danger" id="balance" name="balance" placeholder="0" readonly>   
                                                                </div>
                                                            </div>
                                                            <div class="row mt-2" id="insufficientBalDisplay" style="display:none;">
                                                                <span class="col-sm-4"></span>
                                                                <label class="col-sm-8 text-danger">Insufficient Balance</label>
                                                            </div>
                                                        </div>   
                                                        <div class="col-xxl-4 col-lg-4 mb-3" style="display:none;">
                                                            <div class="row">
                                                                <label for="indicatorId" class="col-sm-4 col-form-label">Indicator ID</label>
                                                                <div class="col-sm-8">
                                                                    <select id="indicatorId" name="indicatorId" class="form-select select2" >
                                                                        <option selected>ind12345</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <input type="hidden" id="finalWeight" name="finalWeight">
                                                        <input type="hidden" id="customerCode" name="customerCode">
                                                        <input type="hidden" id="destinationCode" name="destinationCode">
                                                        <input type="hidden" id="plantCode" name="plantCode">
                                                        <input type="hidden" id="status" name="status">
                                                        <input type="hidden" id="productCode" name="productCode">
                                                        <input type="hidden" id="productDescription" name="productDescription">
                                                        <input type="hidden" id="productHigh" name="productHigh">
                                                        <input type="hidden" id="productLow" name="productLow">
                                                        <input type="hidden" id="productVariance" name="productVariance">
                                                        <input type="hidden" id="transporterCode" name="transporterCode">
                                                        <input type="hidden" id="supplierCode" name="supplierCode">
                                                        <input type="hidden" id="rawMaterialCode" name="rawMaterialCode">
                                                        <input type="hidden" id="id" name="id">  
                                                        <input type="hidden" id="weighbridge" name="weighbridge" value="Weigh1">
                                                        <input type="hidden" id="previousRecordsTag" name="previousRecordsTag">
                                                        <input type="hidden" id="grossWeightBy1" name="grossWeightBy1">
                                                        <input type="hidden" id="tareWeightBy1" name="tareWeightBy1">
                                                        <input type="hidden" id="grossWeightBy2" name="grossWeightBy2">
                                                        <input type="hidden" id="tareWeightBy2" name="tareWeightBy2">
                                                    </form>
                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->

                                    <div class="modal fade" id="prePrintModal">
                                        <div class="modal-dialog modal-xl" style="max-width: 90%;">
                                            <div class="modal-content">
                                                <form role="form" id="prePrintForm">
                                                    <div class="modal-header bg-gray-dark color-palette">
                                                        <h4 class="modal-title"><?=$languageArray['language_code'][$language]?></h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <label for="prePrint" class="col-sm-4 col-form-label"><?=$languageArray['language_code'][$language]?></label>
                                                            <div class="col-sm-8">
                                                                <div class="input-group">
                                                                    <div class="col-12">
                                                                        <select class="form-select select2" id="prePrint" name="prePrint" >
                                                                            <option value="en">English</option>
                                                                            <option value="zh">Chinese</option>
                                                                            <option value="my">Bahasa Malaysia</option>
                                                                            <option value="ne">नेपाली</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3" id="printTemplateDisplay">
                                                            <label for="printTemplate" class="col-sm-4 col-form-label"><?=$languageArray['print_template_code'][$language]?></label>
                                                            <div class="col-sm-8">
                                                                <div class="input-group">
                                                                    <div class="col-12">
                                                                        <select class="form-select select2" id="printTemplate" name="printTemplate" >
                                                                            <option value="with_weight" selected><?=$languageArray['with_weight_code'][$language]?></option>
                                                                            <option value="without_weight"><?=$languageArray['without_weight_code'][$language]?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                            
                                                        <input type="hidden" class="form-control" id="isEmptyContainer" name="isEmptyContainer">
                                                        <input type="hidden" class="form-control" id="prePrintTransactionStatus" name="prePrintTransactionStatus">
                                                        <input type="hidden" class="form-control" id="id" name="id">
                                                    </div>
                                                    <div class="modal-footer justify-content-between bg-gray-dark color-palette">
                                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                        <button type="button" class="btn btn-success" id="submitPrePrint"><?=$languageArray['submit_code'][$language]?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
