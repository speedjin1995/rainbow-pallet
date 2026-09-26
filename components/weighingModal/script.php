<?php
// Weighing modal component script (#addModal, #prePrintModal, #setupModal).
// Include after jQuery, jquery-validate, Select2, flatpickr and assets/js/additional.js,
// and after components/weighingModal/data.php + modal.php.
//
// Public functions:
//   initWeighingModal({ onSaved: function(obj, withPrint){} })  - page decides what happens after a save
//   addWeight(prefill)                                           - open the modal for a new weighing; optional prefill:
//       { companyId, transactionStatus, plantCode, customerCode, supplierCode, productCode, rawMaterialCode, deliveryNo, purchaseOrder }
//   editWeight(id, isContainer)                                  - open the modal for an existing weighing ('Y' = empty container)
//   printWeight(id, transactionStatus, isEmptyContainer)         - open the pre-print modal
//
// Sections:
//   1. Configuration & state        6. Form layout (transaction status / weight type)
//   2. Initialisation               7. Empty container
//   3. Public API                   8. Weight calculation
//   4. Indicator (live weight)      9. Save
//   5. Dropdown lists              10. Print
//                                  11. Helpers
?>
<script type="text/javascript">
(function ($) {
    // Guard against the component being included twice on a page
    if (window.initWeighingModal) {
        return;
    }

    // =========================================================================
    // 1. Configuration & state
    // =========================================================================
    var WEIGHING_URL = 'php/modules/weighing/index.php';
    var VEHICLE_URL = 'php/modules/vehicle/index.php';
    var INDICATOR_URL = 'http://127.0.0.1:5002/';

    var INDICATOR_TYPE = '<?=$wmIndicator ?>';
    var USERNAME = '<?php echo $wmUsername; ?>';
    var DEFAULT_PLANT = "<?=$wmPlantName ?>";
    var PRINT_LANGUAGE = "<?=$language ?>";
    var CAN_CHANGE_DATE = <?= hasPermission('Weighing', ['manual_date_change']) ? 'true' : 'false' ?>;
    var sessionCompanyId = '<?=$wmCompanyId?>';
    var DEFAULT_COMPANY_ID = <?= hasPermission('Weighing', ['view_all_companies']) ? 1 : 'sessionCompanyId' ?>;

    var LANG = {
        pendingBin: "<?= $languageArray['pending_bin_code'][$language] ?>",
        dispatch: '<?=$languageArray['dispatch_code'][$language]?>',
        receiving: '<?=$languageArray['receiving_code'][$language]?>',
        trxToPort: '<?=$languageArray['trx_to_port_code'][$language]?>',
        internalTransfer: '<?=$languageArray['internal_transfer_code'][$language]?>',
        miscellaneous: '<?=$languageArray['miscellaneous_code'][$language]?>'
    };

    // "Manual" checkboxes: tick to type a value instead of picking it from the dropdown.
    // silent = don't trigger change on the dropdown when it's reset; code = hidden code field to clear.
    var MANUAL_ENTRIES = {
        vehicle:     { checkbox: '#manualVehicle',     select: '#vehiclePlateNo1', wrapper: '.index-vehicle',     text: '#vehicleNoTxt' },
        vehicle2:    { checkbox: '#manualVehicle2',    select: '#vehiclePlateNo2', wrapper: '.index-vehicle2',    text: '#vehicleNoTxt2', silent: true },
        product:     { checkbox: '#manualProduct',     select: '#productName',     wrapper: '.index-product',     text: '#productNameTxt',     code: '#productCode' },
        rawMaterial: { checkbox: '#manualRawMaterial', select: '#rawMaterialName', wrapper: '.index-rawmaterial', text: '#rawMaterialNameTxt', code: '#rawMaterialCode' },
        customer:    { checkbox: '#manualCustomer',    select: '#customerName',    wrapper: '.index-customer',    text: '#customerNameTxt',    code: '#customerCode' },
        supplier:    { checkbox: '#manualSupplier',    select: '#supplierName',    wrapper: '.index-supplier',    text: '#supplierNameTxt',    code: '#supplierCode' }
    };

    // Picking one of these dropdowns copies the selected option's data-code into the hidden code field
    var CODE_FIELDS = {
        '#supplierName': '#supplierCode',
        '#transporter': '#transporterCode',
        '#destination': '#destinationCode',
        '#plant': '#plantCode',
        '#customerName': '#customerCode',
        '#rawMaterialName': '#rawMaterialCode'
    };

    var settings = { onSaved: null };
    var optionCache = { allProductOptions: null, allRawMatOptions: null };
    var modalCompanyId = sessionCompanyId;
    var modalListsReady = $.Deferred().resolve().promise();
    var indicatorTimer = null;
    var today = new Date();

    var grossIncomingDatePicker;
    var tareOutgoingDatePicker;
    var grossIncomingDatePicker2;
    var tareOutgoingDatePicker2;
    var customerSideTimeInPicker;
    var customerSideTimeOutPicker;

    // =========================================================================
    // 2. Initialisation
    // =========================================================================
    $(function () {
        initModalSelect2();
        initDatePickers();

        bindHeaderEvents();
        bindManualEntryEvents();
        bindContainerEvents();
        bindWeighingEvents();
        bindActionEvents();

        connectIndicator();
    });

    function initModalSelect2() {
        $('#addModal .select2').select2({
            allowClear: true,
            placeholder: "Please Select",
            dropdownParent: $('#addModal') // Ensures dropdown is not cut off
        });

        $('#addModal .select2-container .select2-selection--single').css({
            'padding-top': '4px',
            'padding-bottom': '4px',
            'height': 'auto'
        });

        $('#addModal .select2-container .select2-selection__arrow').css({
            'padding-top': '33px',
            'height': 'auto'
        });
    }

    function initDatePickers() {
        inModal('#transactionDate').flatpickr(datePickerOptions({
            dateFormat: "d-m-Y",
            defaultDate: ''
        }));

        grossIncomingDatePicker = createDateTimePicker('#grossIncomingDate');
        tareOutgoingDatePicker = createDateTimePicker('#tareOutgoingDate');
        grossIncomingDatePicker2 = createDateTimePicker('#grossIncomingDate2');
        tareOutgoingDatePicker2 = createDateTimePicker('#tareOutgoingDate2');
        customerSideTimeInPicker = createDateTimePicker('#customerSideTimeIn');
        customerSideTimeOutPicker = createDateTimePicker('#customerSideTimeOut');
    }

    // Shared flatpickr options - users without manual_date_change can't open or type in the pickers
    function datePickerOptions(options) {
        return $.extend({
            allowInput: true,
            clickOpens: CAN_CHANGE_DATE,
            onReady: function(selectedDates, dateStr, instance) {
                if (!CAN_CHANGE_DATE) {
                    instance._input.setAttribute('readonly', true);
                    instance.close();
                }
            }
        }, options);
    }

    function createDateTimePicker(selector) {
        return inModal(selector).flatpickr(datePickerOptions({
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i:S",
            altInput: true,
            altFormat: "d/m/Y H:i:S K"
        }));
    }

    // Company, transaction status, weight type, product and the code-carrying dropdowns
    function bindHeaderEvents() {
        inModal('#companyId').on('change', function(){
            loadModalListsByCompany($(this).val());
        });

        inModal('#transactionStatus').on('change', function(){
            applyTransactionStatusLayout($(this).val());
        });

        inModal('#weightType').on('change', function(){
            applyWeightTypeLayout($(this).val());
        });

        inModal('#productName').on('change', function(){
            var selected = inModal('#productName :selected');
            inModal('#productCode').val(selected.data('code'));
            inModal('#productDescription').val(selected.data('description'));
            inModal('#productHigh').val(selected.data('high'));
            inModal('#productLow').val(selected.data('low'));
            inModal('#productVariance').val(selected.data('variance'));
            updatePrices(true);
        });

        $.each(CODE_FIELDS, function(select, codeField){
            inModal(select).on('change', function(){
                inModal(codeField).val(inModal(select + ' :selected').data('code'));
            });
        });
    }

    function bindManualEntryEvents() {
        $.each(MANUAL_ENTRIES, function(key, entry){
            inModal(entry.checkbox).on('change', function(){
                if ($(this).is(':checked')) {
                    $(this).val(1);
                    var select = inModal(entry.select).val('-');
                    if (!entry.silent) {
                        select.trigger('change');
                    }
                    inModal(entry.wrapper).hide();
                    inModal(entry.text).show();
                    if (entry.code) {
                        inModal(entry.code).val('');
                    }
                }
                else {
                    $(this).val(0);
                    inModal(entry.text).hide();
                    inModal(entry.text).val('');
                    inModal(entry.wrapper).show();
                }
            });
        });

        // Vehicle 2 (Different Container): look up the vehicle's own weight
        inModal('#vehicleNoTxt2').on('keyup', function(){
            uppercaseInput(this);
            pullVehicleWeight2($(this).val());
        });

        inModal('#vehiclePlateNo2').on('change', function(){
            pullVehicleWeight2($(this).val());
        });
    }

    function bindContainerEvents() {
        inModal('#emptyContainerNo').on('change', function(){
            var containerNo = $(this).val();
            inModal('#containerNo').val(containerNo);

            if (containerNo && containerNo != '-') {
                loadEmptyContainer(containerNo);
            }
            else {
                clearEmptyContainer();
            }
        });

        inModal('#replacementContainer').on('keyup', function(){
            inModal('#replaceContainerText').text($(this).val());
        });

        inModal('#containerNoInput').on('keyup', function(){
            uppercaseInput(this);
            inModal('#containerNo').val($(this).val());
        });

        inModal('#containerNoInput').on('change', function(){
            inModal('#containerNo').val($(this).val());
        });

        inModal('#sealNo, #containerNo2, #sealNo2').on('keyup', function(){
            uppercaseInput(this);
        });
    }

    function bindWeighingEvents() {
        inModal('#manualWeightToggle').on('change', function(){
            var weightInputs = inModal('#grossIncoming, #tareOutgoing, #grossIncoming2, #tareOutgoing2');

            if ($(this).is(':checked')) {
                $(this).val('true');
                weightInputs.removeAttr('readonly');
            }
            else {
                $(this).val('false');
                weightInputs.attr('readonly', 'readonly');
            }
        });

        // Typing (or capturing) a weight recalculates the nett weight and stamps who/when
        inModal('#grossIncoming').on('keyup', function(){
            updateNettWeight1();
            stampWeighing('#grossWeightBy1', grossIncomingDatePicker, '#grossIncomingDate');
        });

        inModal('#tareOutgoing').on('keyup', function(){
            updateNettWeight1();
            stampWeighing('#tareWeightBy1', tareOutgoingDatePicker, '#tareOutgoingDate');
        });

        inModal('#grossIncoming2').on('keyup', function(){
            updateNettWeight2();
            stampWeighing('#grossWeightBy2', grossIncomingDatePicker2, '#grossIncomingDate2');
        });

        inModal('#tareOutgoing2').on('keyup', function(){
            updateNettWeight2();
            stampWeighing('#tareWeightBy2', tareOutgoingDatePicker2, '#tareOutgoingDate2');
        });

        // Capture buttons copy the live indicator reading into the weight field
        bindCapture('#grossCapture', '#grossIncoming');
        bindCapture('#tareCapture', '#tareOutgoing');
        bindCapture('#grossCapture2', '#grossIncoming2');
        bindCapture('#tareCapture2', '#tareOutgoing2');

        // Nett weights -> current weight -> reduce weight -> final weight -> difference / price
        inModal('#nettWeight, #nettWeight2').on('change', function(){
            showCurrentWeight(calculateCurrentWeight());
            inModal('#reduceWeight').trigger('change');
        });

        inModal('#reduceWeight').on('change', function(){
            var finalWeight = Math.abs(calculateCurrentWeight() - numberValue('#reduceWeight'));
            showCurrentWeight(finalWeight);
            inModal('#currentWeight').trigger('change');
            inModal('#finalWeight').trigger('change');
        });

        inModal('#finalWeight').on('change', function(){
            var finalWeight = numberValue('#finalWeight');
            var referenceWeight = isIncomingStatus() ? parseFloat(inModal('#supplierWeight').val()) : parseFloat(inModal('#orderWeight').val());
            var difference = finalWeight - referenceWeight;
            inModal('#weightDifference').val(difference.toFixed(0));

            var variancePercent = (difference / parseFloat($(this).val())) * 100;
            inModal('#weightDifferencePerc').val(variancePercent.toFixed(2));
        });

        inModal('#orderWeight, #supplierWeight').on('change', function(){
            var referenceWeight = $(this).val() ? parseFloat($(this).val()) : 0;
            var difference = numberValue('#finalWeight') - referenceWeight;
            inModal('#weightDifference').val(difference.toFixed(0));

            if (inModal('#previousRecordsTag').val() == 'false'){
                inModal('#balance').val($(this).val());
                if ($(this).val() <= 0) {
                    inModal('#insufficientBalDisplay').hide();
                } else {
                    inModal('#insufficientBalDisplay').show();
                }
            }

            var variancePercent = (difference / parseFloat($(this).val())) * 100;
            inModal('#weightDifferencePerc').val(variancePercent.toFixed(2));
        });

        inModal('#currentWeight').on('change', function(){
            updatePrices(false);
        });
    }

    function bindActionEvents() {
        inModal('#submitWeight').on('click', function () {
            handleWeightSubmit(false);
        });

        inModal('#submitWeightPrint').on('click', function () {
            handleWeightSubmit(true);
        });

        $('#submitPrePrint').on('click', function(){
            submitPrePrint();
        });
    }

    // =========================================================================
    // 3. Public API
    // =========================================================================
    function initWeighingModal(options) {
        settings = $.extend(settings, options || {});
    }

    // Reset the form and open it for a new weighing
    function addWeight(prefill) {
        // Force the company lists to reload
        modalCompanyId = null;

        // Header
        inModal('#grossCapture, #tareCapture').show();
        inModal('#id').val("");
        inModal('#currentWeight').text("0");
        inModal('#transactionId').val("");
        inModal('#companyId').val(prefill && prefill.companyId ? prefill.companyId : DEFAULT_COMPANY_ID).trigger('change');
        inModal('#transactionStatus').val("Sales").trigger('change');
        inModal('#emptyContainerNo').val("").trigger('change');
        inModal('#weightType').val("Normal").trigger('change');
        inModal('#customerType').val("Normal").trigger('change');
        inModal('#transactionDate').val(formatDate2(today));
        inModal('#vehiclePlateNo1').val("").trigger('change');
        inModal('#vehiclePlateNo2').val("").trigger('change');
        inModal('#supplierWeight').val("");

        // Customer / supplier, product / raw material
        inModal('#customerCode').val("");
        inModal('#customerName').val("-").trigger('change');
        inModal('#supplierCode').val("");
        inModal('#supplierName').val("-").trigger('change');
        inModal('#productCode').val("");
        inModal('#productName').val("-").trigger('change');
        inModal('#rawMaterialCode').val("");
        inModal('#rawMaterialName').val("-").trigger('change');

        // Order / delivery details
        inModal('#plantCode, #sealNo, #invoiceNo').val("");
        inModal('#purchaseOrder').val("").trigger('change');
        inModal('#salesOrder').val("").trigger('change');
        inModal('#deliveryNo, #transporterCode').val("");
        inModal('#transporter').val("-").trigger('change');
        inModal('#project').val("-").trigger('change');
        inModal('#destinationCode, #plantCode').val("");
        inModal('#plant').val(DEFAULT_PLANT).trigger('change');
        inModal('#destination').val("-").trigger('change');
        inModal('#replacementContainer').val('').trigger('keyup');
        inModal('#otherRemarks').val("");

        $.each(MANUAL_ENTRIES, function(key, entry){
            inModal(entry.checkbox).prop('checked', false).trigger('change');
        });

        // Weighing 1 and 2
        inModal('#grossIncoming').val("");
        grossIncomingDatePicker.clear();
        inModal('#tareOutgoing').val("");
        tareOutgoingDatePicker.clear();
        inModal('#nettWeight, #vehicleWeight2, #emptyContainerWeight2, #grossIncoming2, #status').val("");
        grossIncomingDatePicker2.clear();
        inModal('#tareOutgoing2').val("");
        tareOutgoingDatePicker2.clear();
        inModal('#nettWeight2, #reduceWeight').val("");

        // Customer side
        inModal('#customerSideCompany, #customerSideRemovalPassNo, #customerSideLicenseNo, #customerSideMoistureContent, #customerSideOfficerName, #customerSideRainbowDriver').val("");
        customerSideTimeInPicker.clear();
        customerSideTimeOutPicker.clear();

        // Totals and hidden fields
        inModal('#weightDifference, #weightDifferencePerc').val("");
        inModal('#manualWeightToggle').prop('checked', false).val('false').trigger('change');
        inModal('#weighbridge, #productDescription, #productHigh, #productLow, #productVariance').val("");
        inModal('#orderWeight').val("0");
        inModal('#unitPrice, #subTotalPrice, #sstPrice, #totalPrice').val("0.00");
        inModal('#finalWeight, #balance').val("");
        inModal('#insufficientBalDisplay').hide();
        inModal('#containerNoInput, #containerNo, #containerNo2, #sealNo2').val("");

        // Show select and hide input readonly
        inModal('#salesOrderEdit, #purchaseOrderEdit').val("").hide();
        inModal('#salesOrder').next('.select2-container').show();

        // Prefill from the calling page (e.g. Delivery Order / Goods Received row)
        if (prefill) {
            applyPrefill(prefill);
        }

        showWeightModal();
    }

    // Load a saved weighing into the form
    function editWeight(id, isContainer){
        $('#spinnerLoading').show();

        $.post(WEIGHING_URL, {action: 'getWeight', userID: id, type: isContainer == 'Y' ? 'Container' : 'Weight'}, function(data){
            var obj = JSON.parse(data);

            if (obj.status === 'success'){
                var record = obj.message;

                // Completed weighings can't be re-captured from the indicator
                inModal('#grossCapture, #tareCapture').toggle(record.is_complete != 'Y');

                inModal('#id').val(record.id);
                inModal('#companyId').val(record.company_id).trigger('change');
                modalListsReady.then(function(){
                    fillFormFromRecord(record);
                });
            }
            else {
                hideSpinnerAndNotifyFailed(obj.message);
            }
            $('#spinnerLoading').hide();
        });
    }

    function fillFormFromRecord(record) {
        // Header
        inModal('#transactionId').val(record.transaction_id);
        inModal('#transactionStatus').val(record.transaction_status).trigger('change');
        inModal('#weightType').val(record.weight_type).trigger('change');
        inModal('#customerType').val(record.customer_type).trigger('change');
        inModal('#transactionDate').val(formatDate2(new Date(record.transaction_date)));

        var isIncoming = record.transaction_status == "Purchase" || record.transaction_status == "Local";
        inModal('#divWeightDifference').show();
        inModal('#divSupplierWeight, #divSupplierName').toggle(isIncoming);
        inModal('#divOrderWeight, #divCustomerName').toggle(!isIncoming);

        // Vehicles
        if (record.vehicleNoTxt != null){
            inModal('#vehicleNoTxt').val(record.vehicleNoTxt);
            setManualDisplay('vehicle', true);
        }
        else{
            inModal('#vehiclePlateNo1Edit').val('EDIT');
            inModal('#vehiclePlateNo1').val(record.lorry_plate_no1).select2('destroy').select2();
            setManualDisplay('vehicle', false);
        }

        if (record.vehicleNoTxt2 != null){
            inModal('#vehicleNoTxt2').val(record.vehicleNoTxt2);
            setManualDisplay('vehicle2', true);
        }
        else{
            inModal('#vehiclePlateNo2').val(record.lorry_plate_no2).select2('destroy').select2();
            setManualDisplay('vehicle2', false);
        }

        // Order / delivery details
        inModal('#purchaseOrder').val(record.purchase_order);
        inModal('#invoiceNo').val(record.invoice_no);
        inModal('#deliveryNo').val(record.delivery_no);
        inModal('#transporterCode').val(record.transporter_code);
        inModal('#transporter').val(record.transporter).trigger('change');
        inModal('#project').val(record.project_id).trigger('change');

        // Customer / supplier, product / raw material
        inModal('#customerName').val(record.customer_name).select2('destroy').select2();
        inModal('#customerCode').val(record.customer_code);
        inModal('#supplierName').val(record.supplier_name).select2('destroy').select2();
        inModal('#supplierCode').val(record.supplier_code);
        inModal('#rawMaterialCode').val(record.raw_mat_code);
        inModal('#rawMaterialName').val(record.raw_mat_name).trigger('change');
        inModal('#productName').val(record.product_name).trigger('change');
        inModal('#productCode').val(record.product_code);

        if (record.is_manual_product == 'Y'){
            inModal('#productNameTxt').val(record.product_name);
        }
        setManualDisplay('product', record.is_manual_product == 'Y');

        if (record.is_manual_raw_material == 'Y'){
            inModal('#rawMaterialNameTxt').val(record.raw_mat_name);
        }
        setManualDisplay('rawMaterial', record.is_manual_raw_material == 'Y');

        inModal('#supplierWeight').val(record.supplier_weight);
        inModal('#orderWeight').val(record.order_weight);
        inModal('#destinationCode').val(record.destination_code);
        inModal('#destination').val(record.destination).trigger('change');
        inModal('#plant').val(record.plant_name).trigger('change');
        inModal('#plantCode').val(record.plant_code);
        inModal('#otherRemarks').val(record.remarks);

        // Weighing 1
        inModal('#grossIncoming').val(record.gross_weight1);
        grossIncomingDatePicker.setDate(new Date(record.gross_weight1_date));
        inModal('#grossWeightBy1').val(record.gross_weight_by1);
        inModal('#tareOutgoing').val(record.tare_weight1);
        tareOutgoingDatePicker.setDate(dateOrNull(record.tare_weight1_date));
        inModal('#tareWeightBy1').val(record.tare_weight_by1);
        inModal('#nettWeight').val(record.nett_weight1);

        // Weighing 2
        inModal('#vehicleWeight2').val(record.lorry_no2_weight);
        inModal('#emptyContainerWeight2').val(record.empty_container2_weight);
        inModal('#replacementContainer').val(record.replacement_container).trigger('keyup');
        inModal('#grossIncoming2').val(record.gross_weight2);
        grossIncomingDatePicker2.setDate(dateOrNull(record.gross_weight2_date));
        inModal('#grossWeightBy2').val(record.gross_weight_by2);
        inModal('#tareOutgoing2').val(record.tare_weight2);
        tareOutgoingDatePicker2.setDate(dateOrNull(record.tare_weight2_date));
        inModal('#tareWeightBy2').val(record.tare_weight_by2);
        inModal('#nettWeight2').val(record.nett_weight2);
        inModal('#reduceWeight').val(record.reduce_weight);

        // Customer side
        inModal('#customerSideCompany').val(record.customer_side_company);
        inModal('#customerSideRemovalPassNo').val(record.customer_side_removal_pass_no);
        inModal('#customerSideLicenseNo').val(record.customer_side_license_no);
        inModal('#customerSideMoistureContent').val(record.customer_side_moisture_content);
        inModal('#customerSideOfficerName').val(record.customer_side_officer_name);
        inModal('#customerSideRainbowDriver').val(record.customer_side_rainbow_driver);
        customerSideTimeInPicker.setDate(record.customer_side_time_in != null && record.customer_side_time_in != '' ? new Date(record.customer_side_time_in) : null);
        customerSideTimeOutPicker.setDate(record.customer_side_time_out != null && record.customer_side_time_out != '' ? new Date(record.customer_side_time_out) : null);

        // Totals
        inModal('#weightDifference').val(record.weight_different);
        inModal('#weightDifferencePerc').val(record.weight_different_perc);
        inModal('#currentWeight').text(record.final_weight);

        var isManualWeight = record.manual_weight == 'true';
        inModal('#manualWeightToggle').prop('checked', isManualWeight).val(isManualWeight ? 'true' : 'false');
        inModal('#manualWeightToggle').trigger('change');

        // Hidden fields, price and containers
        inModal('#indicatorId').val(record.indicator_id);
        inModal('#weighbridge').val(record.weighbridge_id);
        inModal('#indicatorId2').val(record.indicator_id_2);
        inModal('#productDescription').val(record.product_description);
        inModal('#unitPrice').val(record.unit_price);
        inModal('#subTotalPrice').val(record.sub_total);
        inModal('#sstPrice').val(record.sst);
        inModal('#totalPrice').val(record.total_price);
        inModal('#finalWeight').val(record.final_weight);
        inModal('#containerNoInput').val(record.container_no);
        inModal('#containerNo').val(record.container_no);
        inModal('#containerNo2').val(record.container_no2);
        inModal('#sealNo').val(record.seal_no);
        inModal('#sealNo2').val(record.seal_no2);

        // Container weighings: select the saved pending container once the list is loaded
        if ((record.weight_type == 'Container' || record.weight_type == 'Different Container') && record.container_no){
            loadContainerOptions(inModal('#transactionStatus').val(), function() {
                inModal('#normalCard').show();

                var containerSelect = inModal('#emptyContainerNo');
                var containerExists = containerSelect.find('option').filter(function() {
                    return $(this).val() === record.container_no;
                }).length > 0;

                // The saved container is no longer pending, so add it back to the list
                if (!containerExists){
                    containerSelect.append('<option value="'+record.container_no+'">'+record.container_no+'</option>');
                }

                containerSelect.val(record.container_no).select2('destroy').select2();
                initModalSelect2();
            });
        }

        initModalSelect2();
        showWeightModal();
    }

    // prefill: { companyId, transactionStatus, plantCode, customerCode, supplierCode, productCode, rawMaterialCode, deliveryNo, purchaseOrder }
    // Codes are matched against the options' data-code because the dropdown values are names.
    function applyPrefill(prefill) {
        modalListsReady.then(function(){
            // Transaction status first - it rebuilds the product / raw material lists
            if (prefill.transactionStatus) {
                inModal('#transactionStatus').val(prefill.transactionStatus).trigger('change');
            }

            selectOptionByCode('#plant', prefill.plantCode);
            selectOptionByCode('#customerName', prefill.customerCode);
            selectOptionByCode('#supplierName', prefill.supplierCode);
            selectOptionByCode('#productName', prefill.productCode);
            selectOptionByCode('#rawMaterialName', prefill.rawMaterialCode);

            if (prefill.deliveryNo) {
                inModal('#deliveryNo').val(prefill.deliveryNo);
            }

            if (prefill.purchaseOrder) {
                inModal('#purchaseOrder').val(prefill.purchaseOrder);
            }
        });
    }

    function selectOptionByCode(selector, code) {
        if (code === undefined || code === null || code === '') {
            return;
        }

        var option = inModal(selector + ' option').filter(function() {
            return String($(this).data('code')) === String(code);
        }).first();

        if (option.length > 0) {
            inModal(selector).val(option.val()).trigger('change');
        }
    }

    function showWeightModal() {
        clearValidationErrors();
        $('#addModal').modal('show');
        validateForm('#weightForm');
    }

    // =========================================================================
    // 4. Indicator (live weight from the local serial port service)
    // =========================================================================
    function connectIndicator() {
        $.post(INDICATOR_URL, $('#setupForm').serialize(), function(data){
            setIndicatorStatus(data == "true");
        });

        // Poll the indicator only while the weighing modal is open
        $('#addModal').on('shown.bs.modal', startIndicatorPolling);
        $('#addModal').on('hidden.bs.modal', stopIndicatorPolling);
    }

    function startIndicatorPolling() {
        if (indicatorTimer === null) {
            readIndicator();
            indicatorTimer = setInterval(readIndicator, 500);
        }
    }

    function stopIndicatorPolling() {
        clearInterval(indicatorTimer);
        indicatorTimer = null;
    }

    function readIndicator() {
        $.post(INDICATOR_URL + 'handshaking', function(data){
            if (data == "Error"){
                inModal('#indicatorWeight').html('0');
                setIndicatorStatus(false);
                return;
            }

            console.log("Data Received:" + data);

            var weight = parseIndicatorReading(data);
            if (weight !== null) {
                inModal('#indicatorWeight').html(weight);
                setIndicatorStatus(true);
            }
        });
    }

    // Extract the weight from the raw indicator string; returns null when the reading isn't a weight
    function parseIndicatorReading(data) {
        if (INDICATOR_TYPE == 'X2S' || INDICATOR_TYPE == 'X722'){
            if (data.includes("GS")){
                return removeWeightUnit(lastPart(data, " "));
            }
        }
        else if (INDICATOR_TYPE == 'BDI'){
            if (data.includes("GS") || data.includes("NT") || data.includes("ST") || data.includes("US")){
                return removeWeightUnit(lastPart(data, " "));
            }
        }
        else if (INDICATOR_TYPE == 'EX2001'){
            data = removeWeightUnit(data).replace("g", "");
            if (data != null && data != ''){
                return parseInt(lastPart(data, ",").replaceAll(",", "").trim()).toString();
            }
        }
        else if (INDICATOR_TYPE == 'D2008'){
            if (data.includes("GS")){
                return parseInt(removeWeightUnit(lastPart(data, ","))).toString();
            }
        }

        return null;
    }

    function setIndicatorStatus(connected) {
        $('#indicatorConnected').toggleClass('bg-primary', connected);
        $('#checkingConnection').toggleClass('bg-danger', !connected);
    }

    function lastPart(text, separator) {
        var parts = text.split(separator);
        return parts[parts.length - 1];
    }

    function removeWeightUnit(text) {
        return text.replace("kg", "").replace("KG", "").replace("Kg", "");
    }

    // =========================================================================
    // 5. Dropdown lists
    // =========================================================================

    // Reload the modal listings for the selected company; returns a promise resolved once all lists are rebuilt
    function loadModalListsByCompany(companyId) {
        if (!companyId || companyId === '-' || companyId == modalCompanyId) {
            return modalListsReady;
        }
        modalCompanyId = companyId;
        var productFiltered = optionCache.allProductOptions !== null;
        var rawMatFiltered = optionCache.allRawMatOptions !== null;

        // One request returns every list (customers, suppliers, products, destinations, projects, vehicles)
        modalListsReady = $.post(WEIGHING_URL, { action: 'companyLists', company: companyId }).then(function(data) {
            var lists = {};
            try {
                var obj = JSON.parse(data);
                if (obj.status === 'success') {
                    lists = obj.data;
                }
            } catch (e) {
                console.error('Failed to load weighing modal lists', e);
            }

            fillOptions('#customerName', lists.customers, function(item) {
                return $('<option>').val(item.name).text(item.name).attr('data-code', item.customer_code);
            });
            fillOptions('#supplierName', lists.suppliers, function(item) {
                return $('<option>').val(item.name).text(item.name).attr('data-code', item.supplier_code);
            });
            fillOptions('#productName', lists.products, function(item) {
                return productOption(item, item.name, item.product_code + ' - ' + item.name);
            });
            fillOptions('#rawMaterialName', lists.products, function(item) {
                return productOption(item, item.name, item.product_code + ' - ' + item.name);
            });
            fillOptions('#destination', lists.destinations, function(item) {
                return $('<option>').val(item.name).text(item.name).attr('data-code', item.destination_code);
            });
            fillOptions('#project', lists.projects, function(item) {
                return $('<option>').val(item.id).text(item.project_code);
            });
            fillOptions('#vehiclePlateNo1', lists.vehicles, vehicleOption);
            fillOptions('#vehiclePlateNo2', lists.vehicles, vehicleOption);

            optionCache.allProductOptions = null;
            optionCache.allRawMatOptions = null;
            var status = inModal('#transactionStatus').val();
            if (productFiltered) filterDropdownByTransactionStatus('#productName', 'allProductOptions', status);
            if (rawMatFiltered) filterDropdownByTransactionStatus('#rawMaterialName', 'allRawMatOptions', status);
        }, function() {
            // Request failed - keep the current lists, but still resolve so callers can chain on it
            return $.Deferred().resolve();
        });
        return modalListsReady;
    }

    // Rebuild a dropdown: "-" placeholder followed by one option per item
    function fillOptions(selector, items, buildOption) {
        var $sel = inModal(selector);
        $sel.empty().append('<option selected>-</option>');
        $.each(items || [], function(i, item) {
            $sel.append(buildOption(item));
        });
        $sel.val('-').trigger('change');
    }

    function vehicleOption(item) {
        return $('<option>').val(item.veh_number).text(item.veh_number).attr('data-weight', item.vehicle_weight);
    }

    // Keep only the products allowed for the transaction status (the full list is cached on first use)
    function filterDropdownByTransactionStatus(selector, cacheKey, status) {
        if (!optionCache[cacheKey]) {
            optionCache[cacheKey] = inModal(selector + ' option').clone(true);
        }

        var dataAttr = 'is-sales';
        if (status === 'Sales') dataAttr = 'is-sales';
        else if (status === 'Purchase') dataAttr = 'is-purchase';
        else if (status === 'Port') dataAttr = 'is-port';
        else if (status === 'Misc') dataAttr = 'is-misc';

        var $sel = inModal(selector);
        $sel.empty();
        optionCache[cacheKey].each(function() {
            var $option = $(this).clone(true);
            if ($option.val() === '-' || $option.val() === '') {
                $sel.append($option);
            } else if ($option.data(dataAttr) === 'Y') {
                $sel.append($option);
            }
        });

        $sel.val('-').trigger('change');
    }

    function productOption(item, value, text) {
        return $('<option>').val(value).text(text)
            .attr('data-code', item.product_code)
            .attr('data-high', item.high)
            .attr('data-low', item.low)
            .attr('data-variance', item.variance)
            .attr('data-description', item.description)
            .attr('data-is-sales', item.is_sales)
            .attr('data-is-purchase', item.is_purchase)
            .attr('data-is-port', item.is_port)
            .attr('data-is-misc', item.is_misc);
    }

    // Fill the pending container dropdown for the transaction status
    function loadContainerOptions(transactionStatus, callback) {
        $.post(WEIGHING_URL, {action: 'getContainers', userID: transactionStatus}, function (data){
            var obj = JSON.parse(data);

            if (obj.status != 'success'){
                hideSpinnerAndNotifyFailed(obj.message);
                return;
            }

            if (obj.message.length > 0){
                var containerSelect = inModal('#emptyContainerNo');
                containerSelect.empty();
                containerSelect.append('<option selected="-">-</option>');

                $.each(obj.message, function(i, container){
                    containerSelect.append('<option value="'+container.container_no+'">'+container.container_no+'</option>');
                });
            }

            if (callback) {
                callback();
            }
        });
    }

    // =========================================================================
    // 6. Form layout (transaction status / weight type)
    // =========================================================================

    // Sales / Port / Misc use customer + product; Purchase / Local use supplier + raw material
    function applyTransactionStatusLayout(status) {
        var isIncoming = status == "Purchase" || status == "Local";

        filterDropdownByTransactionStatus('#productName', 'allProductOptions', status);
        filterDropdownByTransactionStatus('#rawMaterialName', 'allRawMatOptions', status);

        if (inModal('#weightType').val() == 'Container'){
            loadContainerOptions(status);
        }

        inModal('#divWeightDifference').show();
        inModal('#divSupplierWeight, #divSupplierName, #rawMaterialDisplay, #divPoSupplyWeight').toggle(isIncoming);
        inModal('#divOrderWeight, #divCustomerName, #productNameDisplay').toggle(!isIncoming);
        inModal('#orderWeight').val(isIncoming ? "" : "0");
        inModal('#supplierWeight').val(isIncoming ? "0" : "");
        inModal('#divPurchaseOrder').find('label[for="purchaseOrder"]').text(status == "Purchase" ? 'Purchase Order' : 'Sale Order');

        // Customer side details only apply to Purchase
        inModal('#customerSideCard, #customerSideLabel').toggle(status == "Purchase");
    }

    // Normal / Empty Container weigh the lorry directly; Container / Different Container
    // pick up weighing 1 from a pending (empty) container record
    function applyWeightTypeLayout(weightType) {
        var isDifferentContainer = weightType == 'Different Container';
        var usesPendingContainer = weightType == 'Container' || isDifferentContainer;

        if (usesPendingContainer){
            loadContainerOptions(inModal('#transactionStatus').val());
        }

        showWeighingCards(weightType);

        inModal('#containerNo1Label').text(isDifferentContainer ? LANG.pendingBin : "Container No 1");
        inModal('#emptyContainerDisplay').toggle(usesPendingContainer);
        inModal('#containerDisplay').toggle(!usesPendingContainer);
        inModal('#containerNoInput').attr('required', weightType == 'Empty Container');
        inModal('#emptyContainerNo').attr('required', usesPendingContainer);

        // Different Container swaps the container 2 / seal fields for the replacement container fields
        inModal('#replacementContainerDisplay, #vehicleWeight2Display, #container2WeightDisplay, #containerNo2ReplaceDisplay, #sealNoReplaceDisplay, #sealNo2ReplaceDisplay').toggle(isDifferentContainer);
        inModal('#containerNo2Display, #sealNoDisplay, #sealNo2Display').toggle(!isDifferentContainer);
    }

    function showWeighingCards(weightType) {
        if (weightType == 'Container' || weightType == 'Different Container'){
            resetFirstWeighing();
            inModal('#normalCard').hide();
            inModal('#grossCapture, #tareCapture').hide();
            inModal('#containerCard').show();
        }
        else {
            resetSecondWeighing();
            inModal('#containerCard').hide();
            inModal('#grossCapture, #tareCapture').show();
            inModal('#normalCard').show();
        }
    }

    function resetFirstWeighing() {
        inModal('#manualVehicle').prop('checked', false).trigger('change');
        inModal('#grossIncoming').val(0);
        inModal('#grossIncomingDate').val("");
        inModal('#tareOutgoing').val(0);
        inModal('#tareOutgoingDate').val("");
        inModal('#nettWeight').val(0);
    }

    function resetSecondWeighing() {
        inModal('#manualVehicle2').prop('checked', false).trigger('change');
        inModal('#grossIncoming2').val(0);
        inModal('#grossIncomingDate2').val("");
        inModal('#tareOutgoing2').val(0);
        inModal('#tareOutgoingDate2').val("");
        inModal('#nettWeight2').val(0);
    }

    function setManualDisplay(entryKey, isManual) {
        var entry = MANUAL_ENTRIES[entryKey];
        inModal(entry.checkbox).val(isManual ? 1 : 0);
        inModal(entry.checkbox).prop('checked', isManual);
        inModal(entry.wrapper).toggle(!isManual);
        inModal(entry.text).toggle(isManual);
    }

    // =========================================================================
    // 7. Empty container
    // =========================================================================

    // Copy the pending container's weighing 1 and details into the form
    function loadEmptyContainer(containerNo) {
        var weightType = inModal('#weightType').val();

        $.post(WEIGHING_URL, {action: 'getEmptyContainer', userID: containerNo}, function (data){
            var obj = JSON.parse(data);

            if (obj.status != 'success'){
                hideSpinnerAndNotifyFailed(obj.message);
                return;
            }

            var record = obj.message;
            inModal('#companyId').val(record.company_id).trigger('change');
            modalListsReady.then(function(){
                fillFromEmptyContainer(record, weightType);
            });
        });
    }

    function fillFromEmptyContainer(record, weightType) {
        inModal('#project').val(record.project_id).trigger('change');
        inModal('#invoiceNo').val(record.invoice_no);
        inModal('#deliveryNo').val(record.delivery_no);
        inModal('#purchaseOrder').val(record.purchase_order);
        inModal('#sealNo').val(record.seal_no);

        if (weightType != 'Different Container'){
            inModal('#containerNo2').val(record.container_no2);
            inModal('#sealNo2').val(record.seal_no2);
        }

        if (record.transaction_status == 'Purchase' || record.transaction_status == 'Local'){
            inModal('#supplierName').val(record.supplier_name).trigger('change');
            inModal('#rawMaterialName').val(record.raw_mat_name).trigger('change');

            if (record.is_manual_raw_material == 'Y'){
                inModal('#rawMaterialNameTxt').val(record.raw_mat_name);
            }
            setManualDisplay('rawMaterial', record.is_manual_raw_material == 'Y');
        }
        else {
            inModal('#customerName').val(record.customer_name).trigger('change');
            inModal('#productName').val(record.product_name).trigger('change');

            if (record.is_manual_product == 'Y'){
                inModal('#productNameTxt').val(record.product_name);
            }
            setManualDisplay('product', record.is_manual_product == 'Y');
        }

        inModal('#plant').val(record.plant_name).trigger('change');
        inModal('#transporter').val(record.transporter).trigger('change');
        inModal('#destination').val(record.destination).trigger('change');
        inModal('#vehiclePlateNo1').val(record.lorry_plate_no1).trigger('change');

        // Weighing 1 comes from the container record
        inModal('#grossIncoming').val(record.gross_weight1);
        grossIncomingDatePicker.setDate(new Date(record.gross_weight1_date));
        inModal('#grossWeightBy1').val(record.gross_weight_by1);
        inModal('#tareOutgoing').val(record.tare_weight1);
        tareOutgoingDatePicker.setDate(new Date(record.tare_weight1_date));
        inModal('#tareWeightBy1').val(record.tare_weight_by1);
        inModal('#nettWeight').val(record.nett_weight1);

        if (record.vehicleNoTxt != null){
            inModal('#vehicleNoTxt').val(record.vehicleNoTxt);
            setManualDisplay('vehicle', true);
        }
        else {
            inModal('#vehiclePlateNo1').val(record.lorry_plate_no1).trigger('change');
            setManualDisplay('vehicle', false);
        }

        inModal('#tareOutgoing2').trigger('keyup');

        inModal('#normalCard').show();
        inModal('#grossCapture, #tareCapture').hide();
    }

    function clearEmptyContainer() {
        resetFirstWeighing();
        inModal('#tareOutgoing2').trigger('keyup');
        inModal('#normalCard').hide();
        inModal('#grossCapture, #tareCapture').show();
    }

    // Different Container: fill in vehicle 2's registered weight
    function pullVehicleWeight2(vehicleNo) {
        if (inModal('#weightType').val() != 'Different Container' || !vehicleNo) {
            return;
        }

        $.post(VEHICLE_URL, {userID: vehicleNo, type: 'pullCustomer', action: 'get'}, function (data){
            var obj = JSON.parse(data);

            if (obj.status == 'success'){
                inModal('#vehicleWeight2').val(obj.message.vehicle_weight);
            }
            else if (obj.status === 'error'){
                alert(obj.message);
                inModal('#vehicleNoTxt').val('');
            }
            else {
                hideSpinnerAndNotifyFailed(obj.message);
            }
        });
    }

    // =========================================================================
    // 8. Weight calculation
    // =========================================================================
    function updateNettWeight1() {
        var nett = Math.abs(numberValue('#grossIncoming') - numberValue('#tareOutgoing'));
        inModal('#nettWeight').val(nett.toFixed(0));
        inModal('#nettWeight').trigger('change');
    }

    function updateNettWeight2() {
        var gross2 = numberValue('#grossIncoming2');
        var tare2 = numberValue('#tareOutgoing2');
        var nett;

        if (inModal('#weightType').val() == 'Different Container'){
            // Weighing 2 is vehicle 2 carrying the replacement container; container 1's weight is weighing 1's nett
            var vehicleWeight2 = numberValue('#vehicleWeight2');
            inModal('#emptyContainerWeight2').val(Math.abs(gross2 - vehicleWeight2));
            nett = Math.abs(tare2 - vehicleWeight2 - numberValue('#nettWeight'));
        }
        else {
            nett = Math.abs(gross2 - tare2);
        }

        inModal('#nettWeight2').val(nett.toFixed(0));
        inModal('#nettWeight2').trigger('change');
    }

    // Record who weighed and when
    function stampWeighing(weighedByField, datePicker, dateField) {
        inModal(weighedByField).val(USERNAME);
        datePicker.setDate(new Date());
        inModal(dateField).trigger('change');
    }

    function bindCapture(button, weightInput) {
        inModal(button).on('click', function(event){
            event.preventDefault();
            var text = inModal('#indicatorWeight').text();
            inModal(weightInput).val(parseFloat(text).toFixed(0));
            inModal(weightInput).trigger('keyup');
        });
    }

    // Different Container uses weighing 2's nett; the others use the difference between both nett weights
    function calculateCurrentWeight() {
        if (inModal('#weightType').val() == 'Different Container'){
            return numberValue('#nettWeight2');
        }

        return Math.abs(numberValue('#nettWeight') - numberValue('#nettWeight2'));
    }

    function showCurrentWeight(weight) {
        inModal('#currentWeight').text(weight.toFixed(0));
        inModal('#finalWeight').val(weight.toFixed(0));
    }

    // Price = unit price x weight, plus 8% SST
    function updatePrices(setUnitPrice) {
        var price = inModal('#unitPrice').val() ? parseFloat(inModal('#unitPrice').val()).toFixed(2) : 0.00;
        var weight = inModal('#currentWeight').text() ? parseFloat(inModal('#currentWeight').text()) : 0;
        var subTotalPrice = price * weight;
        var sstPrice = subTotalPrice * 0.08;
        var totalPrice = subTotalPrice + sstPrice;

        if (setUnitPrice) {
            inModal('#unitPrice').val(price);
        }
        inModal('#subTotalPrice').val(subTotalPrice.toFixed(2));
        inModal('#sstPrice').val(sstPrice.toFixed(2));
        inModal('#totalPrice').val(totalPrice.toFixed(2));
    }

    // =========================================================================
    // 9. Save
    // =========================================================================
    function handleWeightSubmit(withPrint) {
        // Tolerance check (product high / low / variance) is currently disabled - see isWithinTolerance()
        var pass = true;

        if (!isWeighingDirectionValid()) {
            return;
        }

        if (!withPrint) {
            highlightEmptyRequiredSelect2();
        }

        var isEmptyContainer = inModal('#weightType').val() == 'Empty Container' ? 'Y' : 'N';

        if (pass && inModal('#weightForm').valid()) {
            saveWeight(withPrint, isEmptyContainer);
        }
    }

    function saveWeight(withPrint, isEmptyContainer) {
        $('#spinnerLoading').show();

        // Unchecked checkboxes are not serialized, so always send manualWeight
        var formData = inModal('#weightForm').serialize() + (inModal('#manualWeightToggle').is(':checked') ? '' : '&manualWeight=false');

        $.post(WEIGHING_URL, formData, function (data) {
            var obj = JSON.parse(data);
            $('#spinnerLoading').hide();

            if (obj.status === 'success') {
                $('#addModal').modal('hide');
                notifySuccess(obj.message);

                if (withPrint) {
                    printWeight(obj.id, inModal('#transactionStatus').val(), isEmptyContainer);
                }

                // Let the including page refresh its own tables / redirect
                if (typeof settings.onSaved === 'function') {
                    settings.onSaved(obj, withPrint);
                }
            }
            else {
                if (!withPrint) { alert(obj.message); }
                notifyFailed(obj.status === 'failed' ? obj.message : 'Failed to save');
            }
        });
    }

    // The final weight must be within the product's high / low tolerance of the order / supplier weight
    function isWithinTolerance() {
        var variance = inModal('#productVariance').val() || '';
        var high = inModal('#productHigh').val() || '';
        var low = inModal('#productLow').val() || '';
        var final = inModal('#finalWeight').val() || '0';
        var weightType = inModal('#weightType').val();
        var trueWeight = isIncomingStatus() ? parseFloat(inModal('#supplierWeight').val()) : parseFloat(inModal('#orderWeight').val());

        var isComplete =
            (weightType == 'Normal' && inModal('#grossIncoming').val() && inModal('#tareOutgoing').val()) ||
            (weightType == 'Container' && inModal('#grossIncoming').val() && inModal('#tareOutgoing').val() && inModal('#grossIncoming2').val() && inModal('#tareOutgoing2').val());

        if (!isComplete || variance == '') {
            return true;
        }

        final = parseFloat(final);
        low = low != '' ? parseFloat(low) : null;
        high = high != '' ? parseFloat(high) : null;

        if (variance == 'W') {
            if (low !== null && (final < trueWeight - low)) return false;
            if (high !== null && (final > trueWeight + high)) return false;
        }
        else if (variance == 'P') {
            if (low !== null && (final < trueWeight * (1 - low / 100))) return false;
            if (high !== null && (final > trueWeight * (1 + high / 100))) return false;
        }

        return true;
    }

    // Sales: the lorry leaves heavier (outgoing > incoming). Purchase: it leaves lighter (outgoing < incoming).
    function isWeighingDirectionValid() {
        var transStatus = inModal('#transactionStatus').val();
        var weightType = inModal('#weightType').val();
        var incomingField, outgoingField, suffix;

        if (weightType == 'Normal' && inModal('#grossIncoming').val() && inModal('#tareOutgoing').val()) {
            incomingField = '#grossIncoming';
            outgoingField = '#tareOutgoing';
            suffix = '';
        }
        else if (weightType == 'Container' && inModal('#grossIncoming2').val() && inModal('#tareOutgoing2').val()) {
            incomingField = '#grossIncoming2';
            outgoingField = '#tareOutgoing2';
            suffix = ' 2';
        }
        else {
            return true;
        }

        var incoming = parseFloat(inModal(incomingField).val()) || 0;
        var outgoing = parseFloat(inModal(outgoingField).val()) || 0;

        if (transStatus == 'Sales' && incoming >= outgoing) {
            alert('For ' + transactionStatusLabel(transStatus) + ' transaction, outgoing' + suffix + ' weight must be greater than incoming' + suffix + ' weight.');
            return false;
        }

        if (transStatus == 'Purchase' && outgoing >= incoming) {
            alert('For ' + transactionStatusLabel(transStatus) + ' transaction, outgoing' + suffix + ' weight must be lesser than incoming' + suffix + ' weight.');
            return false;
        }

        return true;
    }

    function transactionStatusLabel(status) {
        if (status == 'Sales') return LANG.dispatch;
        if (status == 'Purchase') return LANG.receiving;
        if (status == 'Port') return LANG.trxToPort;
        if (status == 'Local') return LANG.internalTransfer;
        return LANG.miscellaneous;
    }

    // jquery-validate doesn't see Select2 fields, so mark empty required ones manually
    function highlightEmptyRequiredSelect2() {
        $('#addModal .select2[required]').each(function () {
            var select2Field = $(this);
            var select2Container = select2Field.next('.select2-container');
            var errorMsg = "<span class='select2-error text-danger' style='font-size: 11.375px;'>Please fill in the field.</span>";

            if (select2Field.val() === "" || select2Field.val() === null) {
                select2Container.find('.select2-selection').css('border', '1px solid red');

                if (select2Container.next('.select2-error').length === 0) {
                    select2Container.after(errorMsg);
                }
            } else {
                select2Container.find('.select2-selection').css('border', '');
                select2Container.next('.select2-error').remove();
            }
        });
    }

    // =========================================================================
    // 10. Print
    // =========================================================================
    function printWeight(id, transactionStatus, isEmptyContainer = 'N') {
        preparePrePrintModal(id, transactionStatus, isEmptyContainer);
        $("#prePrintModal").modal("show");
        validateForm('#prePrintForm');
    }

    function preparePrePrintModal(id, transactionStatus, isEmptyContainer) {
        var prePrintModal = $('#prePrintModal');
        prePrintModal.find('#id').val(id);
        prePrintModal.find('#isEmptyContainer').val(isEmptyContainer);
        prePrintModal.find('#prePrintTransactionStatus').val(transactionStatus);
        prePrintModal.find('#prePrint').val(PRINT_LANGUAGE);
        prePrintModal.find('#printTemplate').val("with_weight");

        // Purchase and empty container slips have a single template
        prePrintModal.find('#printTemplateDisplay').toggle(!(transactionStatus == 'Purchase' || isEmptyContainer == 'Y'));
    }

    function submitPrePrint() {
        if (!$('#prePrintForm').valid()) {
            return;
        }

        $('#spinnerLoading').show();
        var prePrintModal = $('#prePrintModal');

        $.post(WEIGHING_URL, {
            action: 'print',
            userID: prePrintModal.find('#id').val(),
            file: 'weight',
            prePrint: prePrintModal.find('#prePrint').val(),
            isEmptyContainer: prePrintModal.find('#isEmptyContainer').val(),
            printTemplate: prePrintModal.find('#printTemplate').val(),
            transactionStatus: prePrintModal.find('#prePrintTransactionStatus').val()
        }, function(data){
            var obj = JSON.parse(data);

            if (obj.status === 'success'){
                openPrintWindow(obj.message);
                $("#prePrintModal").modal("hide");
                $('#spinnerLoading').hide();
            }
            else {
                notifyFailed(obj.status === 'failed' ? obj.message : "Something wrong when print");
            }
        });
    }

    function openPrintWindow(html) {
        var printWindow = window.open('', '', 'height=' + screen.height + ',width=' + screen.width);
        printWindow.document.write(html);
        printWindow.document.close();
        setTimeout(function(){
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    // =========================================================================
    // 11. Helpers
    // =========================================================================

    // Find elements inside the weighing modal (keeps selectors from hitting the including page)
    function inModal(selector) {
        return $('#addModal').find(selector);
    }

    function numberValue(selector) {
        var value = inModal(selector).val();
        return value ? parseFloat(value) : 0;
    }

    function dateOrNull(value) {
        return value != null ? new Date(value) : null;
    }

    function isIncomingStatus() {
        var status = inModal('#transactionStatus').val();
        return status == "Purchase" || status == "Local";
    }

    function uppercaseInput(input) {
        $(input).val($(input).val().toUpperCase());
    }

    function validateForm(selector) {
        $(selector).validate({
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

    function clearValidationErrors() {
        inModal('.is-invalid').removeClass('is-invalid');

        $('#addModal .select2[required]').each(function () {
            var select2Container = $(this).next('.select2-container');
            select2Container.find('.select2-selection').css('border', ''); // Remove red border
            select2Container.next('.select2-error').remove(); // Remove error message
        });
    }

    // Legacy toast triggers
    function notifySuccess(message) {
        $("#successBtn").attr('data-toast-text', message);
        $("#successBtn").click();
    }

    function notifyFailed(message) {
        $("#failBtn").attr('data-toast-text', message);
        $("#failBtn").click();
    }

    function hideSpinnerAndNotifyFailed(message) {
        $('#spinnerLoading').hide();
        notifyFailed(message);
    }

    window.initWeighingModal = initWeighingModal;
    window.addWeight = addWeight;
    window.editWeight = editWeight;
    window.printWeight = printWeight;
})(jQuery);
</script>
