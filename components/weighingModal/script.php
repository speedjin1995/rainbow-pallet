<?php
// Weighing modal component script (#addModal + #prePrintModal).
// Include after jQuery, jquery-validate, Select2, flatpickr and assets/js/additional.js,
// and after components/weighingModal/data.php + modal.php.
//
// Public functions:
//   initWeighingModal({ onSaved: function(obj, withPrint){} })  - page decides what happens after a save
//   addWeight(prefill)                                           - open the modal for a new weighing; optional prefill:
//       { companyId, transactionStatus, plantCode, customerCode, supplierCode, productCode, rawMaterialCode, deliveryNo, purchaseOrder }
//   editWeight(id, isContainer)                                  - open the modal for an existing weighing ('Y' = empty container)
//   printWeight(id, transactionStatus, isEmptyContainer)         - open the pre-print modal
?>
<script type="text/javascript">
(function ($) {
    // Guard against the component being included twice on a page
    if (window.initWeighingModal) {
        return;
    }

    var settings = { onSaved: null };
    var optionCache = { allProductOptions: null, allRawMatOptions: null };
    var sessionCompanyId = '<?=$wmCompanyId?>';
    var modalCompanyId = sessionCompanyId;
    var modalListsReady = $.Deferred().resolve().promise();
    var today = new Date();

    var grossIncomingDatePicker;
    var tareOutgoingDatePicker; 
    var grossIncomingDatePicker2;
    var tareOutgoingDatePicker2; 
    var customerSideTimeInPicker;
    var customerSideTimeOutPicker;

    $(function () {
        var ind = '<?=$wmIndicator ?>';

        // Initialize all Select2 elements in the modal
        $('#addModal .select2').select2({
            allowClear: true,
            placeholder: "Please Select",
            dropdownParent: $('#addModal') // Ensures dropdown is not cut off
        });

        $('#transactionDate').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: '',
            allowInput: true,
            clickOpens: <?= hasPermission('Weighing', ['manual_date_change']) ? 'true' : 'false' ?>,
            onReady: function(selectedDates, dateStr, instance) {
                <?php if (!hasPermission('Weighing', ['manual_date_change'])): ?>
                    instance._input.setAttribute('readonly', true);
                    instance.close();
                <?php endif; ?>
            }
        });

        grossIncomingDatePicker = $('#grossIncomingDate').flatpickr({
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i:S",
            altInput: true,
            altFormat: "d/m/Y H:i:S K",
            allowInput: true,
            clickOpens: <?= hasPermission('Weighing', ['manual_date_change']) ? 'true' : 'false' ?>,
            onReady: function(selectedDates, dateStr, instance) {
                <?php if (!hasPermission('Weighing', ['manual_date_change'])): ?>
                    instance._input.setAttribute('readonly', true);
                    instance.close();
                <?php endif; ?>
            }
        });

        tareOutgoingDatePicker = $('#tareOutgoingDate').flatpickr({
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i:S",
            altInput: true,
            altFormat: "d/m/Y H:i:S K",
            allowInput: true,
            clickOpens: <?= hasPermission('Weighing', ['manual_date_change']) ? 'true' : 'false' ?>,
            onReady: function(selectedDates, dateStr, instance) {
                <?php if (!hasPermission('Weighing', ['manual_date_change'])): ?>
                    instance._input.setAttribute('readonly', true);
                    instance.close();
                <?php endif; ?>
            }
        });

        grossIncomingDatePicker2 = $('#grossIncomingDate2').flatpickr({
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i:S",
            altInput: true,
            altFormat: "d/m/Y H:i:S K",
            allowInput: true,
            clickOpens: <?= hasPermission('Weighing', ['manual_date_change']) ? 'true' : 'false' ?>,
            onReady: function(selectedDates, dateStr, instance) {
                <?php if (!hasPermission('Weighing', ['manual_date_change'])): ?>
                    instance._input.setAttribute('readonly', true);
                    instance.close();
                <?php endif; ?>
            }
        });

        tareOutgoingDatePicker2 = $('#tareOutgoingDate2').flatpickr({
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i:S",
            altInput: true,
            altFormat: "d/m/Y H:i:S K",
            allowInput: true,
            clickOpens: <?= hasPermission('Weighing', ['manual_date_change']) ? 'true' : 'false' ?>,
            onReady: function(selectedDates, dateStr, instance) {
                <?php if (!hasPermission('Weighing', ['manual_date_change'])): ?>
                    instance._input.setAttribute('readonly', true);
                    instance.close();
                <?php endif; ?>
            }
        });

        customerSideTimeInPicker = $('#customerSideTimeIn').flatpickr({
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i:S",
            altInput: true,
            altFormat: "d/m/Y H:i:S K",
            allowInput: true,
            clickOpens: <?= hasPermission('Weighing', ['manual_date_change']) ? 'true' : 'false' ?>,
            onReady: function(selectedDates, dateStr, instance) {
                <?php if (!hasPermission('Weighing', ['manual_date_change'])): ?>
                    instance._input.setAttribute('readonly', true);
                    instance.close();
                <?php endif; ?>
            }
        });

        customerSideTimeOutPicker = $('#customerSideTimeOut').flatpickr({
            enableTime: true,
            enableSeconds: true,
            time_24hr: true,
            dateFormat: "Y-m-d H:i:S",
            altInput: true,
            altFormat: "d/m/Y H:i:S K",
            allowInput: true,
            clickOpens: <?= hasPermission('Weighing', ['manual_date_change']) ? 'true' : 'false' ?>,
            onReady: function(selectedDates, dateStr, instance) {
                <?php if (!hasPermission('Weighing', ['manual_date_change'])): ?>
                    instance._input.setAttribute('readonly', true);
                    instance.close();
                <?php endif; ?>
            }
        });

        $('#companyId').on('change', function(){
            loadModalListsByCompany($(this).val());
        });

        $('#submitWeight').on('click', function () { 
            handleWeightSubmit(false); 
        });

        $('#submitWeightPrint').on('click', function () { 
            handleWeightSubmit(true); 
        });

        $('#submitPrePrint').on('click', function(){
            if($('#prePrintForm').valid()){
                $('#spinnerLoading').show();
                var id = $('#prePrintModal').find('#id').val();
                var prePrintStatus = $('#prePrintModal').find('#prePrint').val();
                var isEmptyContainer = $('#prePrintModal').find('#isEmptyContainer').val();
                var printTemplate = $('#prePrintModal').find('#printTemplate').val();
                var transactionStatus = $('#prePrintModal').find('#prePrintTransactionStatus').val();
                $.post('php/modules/weighing/index.php', {action: 'print', userID: id, file: 'weight', prePrint: prePrintStatus, isEmptyContainer: isEmptyContainer, printTemplate: printTemplate, transactionStatus: transactionStatus}, function(data){
                    var obj = JSON.parse(data);

                    if(obj.status === 'success'){
                        var printWindow = window.open('', '', 'height=' + screen.height + ',width=' + screen.width);
                        printWindow.document.write(obj.message);
                        printWindow.document.close();
                        setTimeout(function(){
                            printWindow.print();
                            printWindow.close();
                        }, 500);

                        $("#prePrintModal").modal("hide");
                        $('#spinnerLoading').hide();
                    }
                    else if(obj.status === 'failed'){
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                    else{
                        $("#failBtn").attr('data-toast-text', "Something wrong when print");
                        $("#failBtn").click();
                    }
                });
            }
        });

        $.post('http://127.0.0.1:5002/', $('#setupForm').serialize(), function(data){
            if(data == "true"){
                $('#indicatorConnected').addClass('bg-primary');
                $('#checkingConnection').removeClass('bg-danger');
                //$('#captureWeight').removeAttr('disabled');
            }
            else{
                $('#indicatorConnected').removeClass('bg-primary');
                $('#checkingConnection').addClass('bg-danger');
                //$('#captureWeight').attr('disabled', true);
            }
        });

        // Poll the indicator only while the weighing modal is open
        var indicatorTimer = null;

        $('#addModal').on('shown.bs.modal', function(){
            if (indicatorTimer === null) {
                readIndicator();
                indicatorTimer = setInterval(readIndicator, 500);
            }
        });

        $('#addModal').on('hidden.bs.modal', function(){
            clearInterval(indicatorTimer);
            indicatorTimer = null;
        });

        function readIndicator() {
            $.post('http://127.0.0.1:5002/handshaking', function(data){
                if(data != "Error"){
                    console.log("Data Received:" + data);
                    
                    if(ind == 'X2S' || ind == 'X722'){
                        if(data.includes("GS")){
                            var text = data.split(" ");
                            var text2 = text[text.length - 1];
                            text2 = text2.replace("kg", "").replace("KG", "").replace("Kg", "");
                            $('#indicatorWeight').html(text2);
                            $('#indicatorConnected').addClass('bg-primary');
                            $('#checkingConnection').removeClass('bg-danger');
                        }
                    }
                    else if(ind == 'BDI'){
                        if(data.includes("GS") || data.includes("NT") || data.includes("ST") || data.includes("US")){
                            var text = data.split(" ");
                            var text2 = text[text.length - 1];
                            text2 = text2.replace("kg", "").replace("KG", "").replace("Kg", "");
                            $('#indicatorWeight').html(text2);
                            $('#indicatorConnected').addClass('bg-primary');
                            $('#checkingConnection').removeClass('bg-danger');
                        }
                    }
                    else if(ind == 'EX2001'){
                        data = data.replace("kg", "").replace("KG", "").replace("Kg", "").replace("g", "");
                        if(data != null && data != ''){
                            var text = data.split(",");
                            var text2 = text[text.length - 1];
                            //text2 = text2.replace("kg", "").replace("KG", "").replace("Kg", "");
                            $('#indicatorWeight').html(parseInt(text2.replaceAll(",", "").trim()).toString());
                            $('#indicatorConnected').addClass('bg-primary');
                            $('#checkingConnection').removeClass('bg-danger');
                        }
                    }
                    else if(ind == 'D2008'){
                        if(data.includes("GS")){
                            var text = data.split(",");
                            var text2 = text[text.length - 1];
                            text2 = text2.replace("kg", "").replace("KG", "").replace("Kg", "");
                            $('#indicatorWeight').html(parseInt(text2).toString());
                            $('#indicatorConnected').addClass('bg-primary');
                            $('#checkingConnection').removeClass('bg-danger');
                        }
                    }
                }
                else{
                    $('#indicatorWeight').html('0');
                    $('#indicatorConnected').removeClass('bg-primary');
                    $('#checkingConnection').addClass('bg-danger');
                }
            });
        }

        $('#weightType').on('change', function(){
            var weightType = $(this).val();
            var transaType = $('#transactionStatus').val();

            if (weightType == 'Container'){
                $.post('php/modules/weighing/index.php', {action: 'getContainers', userID: transaType}, function (data){
                    var obj = JSON.parse(data);

                    if (obj.status == 'success'){
                        if (obj.message.length > 0){
                            $('#addModal').find('#emptyContainerNo').empty();
                            $('#addModal').find('#emptyContainerNo').append(`<option selected="-">-</option>`);

                            var deliveredTransporter;

                            for (var i = 0; i < obj.message.length; i++) {
                                var id = obj.message[i].id;
                                var container_no = obj.message[i].container_no;

                                $('#addModal').find('#emptyContainerNo').append(
                                    '<option value="'+container_no+'">'+container_no+'</option>'
                                );  
                            }
                        }
                    }
                    else if(obj.status === 'failed'){
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                    else{
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                });

                handleWeightType(weightType);
                $('#addModal').find('#containerNo1Label').text("Container No 1");
                $('#addModal').find('#emptyContainerDisplay').show();
                $('#addModal').find('#replacementContainerDisplay').hide();
                $('#addModal').find('#vehicleWeight2Display').hide();
                $('#addModal').find('#container2WeightDisplay').hide();
                $('#addModal').find('#containerNo2Display').show();
                $('#addModal').find('#containerNo2ReplaceDisplay').hide();
                $('#addModal').find('#sealNoDisplay').show();
                $('#addModal').find('#sealNoReplaceDisplay').hide();
                $('#addModal').find('#sealNo2Display').show();
                $('#addModal').find('#sealNo2ReplaceDisplay').hide();
                $('#addModal').find('#containerDisplay').hide();
                $('#addModal').find('#containerNoInput').attr('required', false);
                $('#addModal').find('#emptyContainerNo').attr('required', true);
            }else if (weightType == 'Empty Container'){
                handleWeightType(weightType);
                $('#addModal').find('#containerNo1Label').text("Container No 1");
                $('#addModal').find('#emptyContainerDisplay').hide();
                $('#addModal').find('#replacementContainerDisplay').hide();
                $('#addModal').find('#vehicleWeight2Display').hide();
                $('#addModal').find('#container2WeightDisplay').hide();
                $('#addModal').find('#containerNo2Display').show();
                $('#addModal').find('#containerNo2ReplaceDisplay').hide();
                $('#addModal').find('#sealNoDisplay').show();
                $('#addModal').find('#sealNoReplaceDisplay').hide();
                $('#addModal').find('#sealNo2Display').show();
                $('#addModal').find('#sealNo2ReplaceDisplay').hide();
                $('#addModal').find('#containerDisplay').show();
                $('#addModal').find('#containerNoInput').attr('required', true);
                $('#addModal').find('#emptyContainerNo').attr('required', false);
            }else if (weightType == 'Different Container') {
                $.post('php/modules/weighing/index.php', {action: 'getContainers', userID: transaType}, function (data){
                    var obj = JSON.parse(data);

                    if (obj.status == 'success'){
                        if (obj.message.length > 0){
                            $('#addModal').find('#emptyContainerNo').empty();
                            $('#addModal').find('#emptyContainerNo').append(`<option selected="-">-</option>`);

                            var deliveredTransporter;

                            for (var i = 0; i < obj.message.length; i++) {
                                var id = obj.message[i].id;
                                var container_no = obj.message[i].container_no;

                                $('#addModal').find('#emptyContainerNo').append(
                                    '<option value="'+container_no+'">'+container_no+'</option>'
                                );  
                            }
                        }
                    }
                    else if(obj.status === 'failed'){
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                    else{
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                });
                handleWeightType(weightType);
                $('#addModal').find('#containerNo1Label').text("<?= $languageArray['pending_bin_code'][$language] ?>");
                $('#addModal').find('#emptyContainerDisplay').show();
                $('#addModal').find('#replacementContainerDisplay').show();
                $('#addModal').find('#vehicleWeight2Display').show();
                $('#addModal').find('#container2WeightDisplay').show();
                $('#addModal').find('#containerNo2Display').hide();
                $('#addModal').find('#containerNo2ReplaceDisplay').show();
                $('#addModal').find('#sealNoDisplay').hide();
                $('#addModal').find('#sealNoReplaceDisplay').show();
                $('#addModal').find('#sealNo2Display').hide();
                $('#addModal').find('#sealNo2ReplaceDisplay').show();
                $('#addModal').find('#containerDisplay').hide();
                $('#addModal').find('#containerNoInput').attr('required', false);
                $('#addModal').find('#emptyContainerNo').attr('required', true);
            }else{
                handleWeightType(weightType);
                $('#addModal').find('#containerNo1Label').text("Container No 1");
                $('#addModal').find('#emptyContainerDisplay').hide();
                $('#addModal').find('#replacementContainerDisplay').hide();
                $('#addModal').find('#vehicleWeight2Display').hide();
                $('#addModal').find('#container2WeightDisplay').hide();
                $('#addModal').find('#containerNo2Display').show();
                $('#addModal').find('#containerNo2ReplaceDisplay').hide();
                $('#addModal').find('#sealNoDisplay').show();
                $('#addModal').find('#sealNoReplaceDisplay').hide();
                $('#addModal').find('#sealNo2Display').show();
                $('#addModal').find('#sealNo2ReplaceDisplay').hide();
                $('#addModal').find('#containerDisplay').show();
                $('#addModal').find('#containerNoInput').attr('required', false);
                $('#addModal').find('#emptyContainerNo').attr('required', false);
            }
        });

        $('#replacementContainer').on('keyup', function(){
            var replacementContainer = $(this).val();
            $('#replaceContainerText').text(replacementContainer);
        });

        /*$('#customerType').on('change', function(){
            var transactionStatus = $('#addModal').find('#transactionStatus').val();
            if (transactionStatus == 'Purchase'){
                $('#unitPriceDisplay').hide();
                $('#subTotalPriceDisplay').hide();
                $('#sstDisplay').hide();
                $('#totalPriceDisplay').hide();
            }else{
                if($(this).val() == "Cash")
                {
                    $('#unitPriceDisplay').show();
                    $('#subTotalPriceDisplay').show();
                    $('#sstDisplay').show();
                    $('#totalPriceDisplay').show();
                }
                else
                {
                    $('#unitPriceDisplay').hide();
                    $('#subTotalPriceDisplay').hide();
                    $('#sstDisplay').hide();
                    $('#totalPriceDisplay').hide();
                }
            }
        });*/

        $('#manualVehicle').on('change', function(){
            if($(this).is(':checked')){
                $(this).val(1);
                $('#vehiclePlateNo1').val('-').trigger('change');
                $('.index-vehicle').hide();
                $('#vehicleNoTxt').show();
            }
            else{
                $(this).val(0);
                $('#vehicleNoTxt').hide();
                $('#vehicleNoTxt').val('');
                $('.index-vehicle').show();
            }
        });

        $('#manualVehicle2').on('change', function(){
            if($(this).is(':checked')){
                $(this).val(1);
                $('#vehiclePlateNo2').val('-');
                $('.index-vehicle2').hide();
                $('#vehicleNoTxt2').show();
            }
            else{
                $(this).val(0);
                $('#vehicleNoTxt2').hide();
                $('#vehicleNoTxt2').val('');
                $('.index-vehicle2').show();
            }
        });

        // Manual Product checkbox handler
        $('#manualProduct').on('change', function(){
            if($(this).is(':checked')){
                $(this).val(1);
                $('#productName').val('-').trigger('change');
                $('.index-product').hide();
                $('#productNameTxt').show();
                $('#productCode').val('');
            }
            else{
                $(this).val(0);
                $('#productNameTxt').hide();
                $('#productNameTxt').val('');
                $('.index-product').show();
            }
        });

        // Manual Raw Material checkbox handler
        $('#manualRawMaterial').on('change', function(){
            if($(this).is(':checked')){
                $(this).val(1);
                $('#rawMaterialName').val('-').trigger('change');
                $('.index-rawmaterial').hide();
                $('#rawMaterialNameTxt').show();
                $('#rawMaterialCode').val('');
            }
            else{
                $(this).val(0);
                $('#rawMaterialNameTxt').hide();
                $('#rawMaterialNameTxt').val('');
                $('.index-rawmaterial').show();
            }
        });

        // Manual Customer checkbox handler
        $('#manualCustomer').on('change', function(){
            if($(this).is(':checked')){
                $(this).val(1);
                $('#customerName').val('-').trigger('change');
                $('.index-customer').hide();
                $('#customerNameTxt').show();
                $('#customerCode').val('');
            }
            else{
                $(this).val(0);
                $('#customerNameTxt').hide();
                $('#customerNameTxt').val('');
                $('.index-customer').show();
            }
        });

        // Manual Supplier checkbox handler
        $('#manualSupplier').on('change', function(){
            if($(this).is(':checked')){
                $(this).val(1);
                $('#supplierName').val('-').trigger('change');
                $('.index-supplier').hide();
                $('#supplierNameTxt').show();
                $('#supplierCode').val('');
            }
            else{
                $(this).val(0);
                $('#supplierNameTxt').hide();
                $('#supplierNameTxt').val('');
                $('.index-supplier').show();
            }
        });

        $('#vehicleNoTxt2').on('keyup', function(){
            var x = $('#vehicleNoTxt2').val();
            x = x.toUpperCase();
            $('#vehicleNoTxt2').val(x);
            var weightType = $('#weightType').val();

            if (weightType == 'Different Container' && x) {
                $.post('php/modules/vehicle/index.php', {userID: x, type: 'pullCustomer', action: 'get'}, function (data){
                    var obj = JSON.parse(data);

                    if (obj.status == 'success'){
                        var vehicleWeight = obj.message.vehicle_weight;
                        $('#vehicleWeight2').val(vehicleWeight);
                    }
                    else if(obj.status === 'error'){
                        alert(obj.message);
                        $('#vehicleNoTxt').val('');
                    }
                    else if(obj.status === 'failed'){
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                    else{
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                });
            }
        });

        $('#vehiclePlateNo2').on('change', function(){
            var vehiclePlateNo2 = $(this).val();
            var weightType = $('#weightType').val();
            if (weightType == 'Different Container' && vehiclePlateNo2){
                $.post('php/modules/vehicle/index.php', {userID: vehiclePlateNo2, type: 'pullCustomer', action: 'get'}, function (data){
                    var obj = JSON.parse(data);

                    if (obj.status == 'success'){
                        var vehicleWeight = obj.message.vehicle_weight;
                        $('#vehicleWeight2').val(vehicleWeight);
                    }
                    else if(obj.status === 'error'){
                        alert(obj.message);
                        $('#vehicleNoTxt').val('');
                    }
                    else if(obj.status === 'failed'){
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                    else{
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                });
            }
        });

        $('#manualWeightToggle').on('change', function(){
            if($(this).is(':checked')){
                $(this).val('true');
                $('#tareOutgoing').removeAttr('readonly');
                $('#grossIncoming').removeAttr('readonly');
                $('#tareOutgoing2').removeAttr('readonly');
                $('#grossIncoming2').removeAttr('readonly');
            }
            else{
                $(this).val('false');
                $('#grossIncoming').attr('readonly', 'readonly');
                $('#tareOutgoing').attr('readonly', 'readonly');
                $('#grossIncoming2').attr('readonly', 'readonly');
                $('#tareOutgoing2').attr('readonly', 'readonly');
            }
        });

        $('#grossIncoming').on('keyup', function(){
            var gross = $(this).val() ? parseFloat($(this).val()) : 0;
            var tare = $('#tareOutgoing').val() ? parseFloat($('#tareOutgoing').val()) : 0;
            var nett = Math.abs(gross - tare);
            $('#nettWeight').val(nett.toFixed(0));
            $('#nettWeight').trigger('change');
            $('#grossWeightBy1').val('<?php echo $wmUsername; ?>');

            // Update the Flatpickr instance
            grossIncomingDatePicker.setDate(new Date()); // sets it to current date/time
            $('#grossIncomingDate').trigger('change');
        });

        $('#grossCapture').on('click', function(event){
            event.preventDefault();
            var text = $('#indicatorWeight').text();
            $('#grossIncoming').val(parseFloat(text).toFixed(0));
            $('#grossIncoming').trigger('keyup');
        });

        $('#tareOutgoing').on('keyup', function(){
            var tare = $(this).val() ? parseFloat($(this).val()) : 0;
            var gross = $('#grossIncoming').val() ? parseFloat($('#grossIncoming').val()) : 0;
            var nett = Math.abs(gross - tare);
            $('#nettWeight').val(nett.toFixed(0));
            $('#nettWeight').trigger('change');
            $('#tareWeightBy1').val('<?php echo $wmUsername; ?>');

            // Update the Flatpickr instance
            tareOutgoingDatePicker.setDate(new Date()); // sets it to current date/time
            $('#tareOutgoingDate').trigger('change');

        });

        $('#tareCapture').on('click', function(event){
            event.preventDefault();
            var text = $('#indicatorWeight').text();
            $('#tareOutgoing').val(parseFloat(text).toFixed(0));
            $('#tareOutgoing').trigger('keyup');
        });

        $('#nettWeight').on('change', function(){
            var weightType = $('#weightType').val();

            if (weightType == 'Different Container'){
                var current = $('#nettWeight2').val() ? parseFloat($('#nettWeight2').val()) : 0;
            }else{
                var nett2 = $('#nettWeight2').val() ? parseFloat($('#nettWeight2').val()) : 0;
                var nett1 = $(this).val() ? parseFloat($(this).val()) : 0;
                var current = Math.abs(nett1 - nett2);
            }

            $('#currentWeight').text(current.toFixed(0));
            $('#finalWeight').val(current.toFixed(0));
            $('#reduceWeight').trigger('change');
            //$('#finalWeight').trigger('change');
        });
        
        $('#reduceWeight').on('change', function(){
            var weightType = $('#weightType').val();

            if (weightType == 'Different Container'){
                var current = $('#nettWeight2').val() ? parseFloat($('#nettWeight2').val()) : 0;
            }else{
                var nett2 = $('#nettWeight2').val() ? parseFloat($('#nettWeight2').val()) : 0;
                var nett1 = $('#nettWeight').val() ? parseFloat($('#nettWeight').val()) : 0;
                var current = Math.abs(nett1 - nett2);
            }

            var reduce = $(this).val() ? parseFloat($(this).val()) : 0;
            //var nett1 = $('#finalWeight').val() ? parseFloat($('#finalWeight').val()) : 0;
            var final = Math.abs(current - reduce);
            $('#currentWeight').text(final.toFixed(0));
            $('#finalWeight').val(final.toFixed(0));
            $('#currentWeight').trigger('change');
            $('#finalWeight').trigger('change');
        });

        $('#finalWeight').on('change', function(){
            var nett1 = $(this).val() ? parseFloat($(this).val()) : 0;
            var nett2 = 0;

            if($('#transactionStatus').val() == "Purchase" || $('#transactionStatus').val() == "Local"){
                nett2 = parseFloat($('#addModal').find('#supplierWeight').val());
            }
            else{
                nett2 = parseFloat($('#addModal').find('#orderWeight').val());
            }
            
            var current = nett1 - nett2;
            $('#weightDifference').val(current.toFixed(0));

            // Processing for variance %
            var variancePercent = (current / parseFloat($(this).val())) * 100;
            $('#weightDifferencePerc').val(variancePercent.toFixed(2));

        });

        $('#orderWeight').on('change', function(){
            var nett1 = $('#finalWeight').val() ? parseFloat($('#finalWeight').val()) : 0;
            var nett2 = $(this).val() ? parseFloat($(this).val()) : 0;
            var current = nett1 - nett2;
            $('#weightDifference').val(current.toFixed(0));

            var previousRecordsTag = $('#addModal').find('#previousRecordsTag').val();

            if (previousRecordsTag == 'false'){
                $('#addModal').find('#balance').val($(this).val());
                if ($(this).val() <= 0) {
                    $('#addModal').find('#insufficientBalDisplay').hide();
                } else {
                    $('#addModal').find('#insufficientBalDisplay').show();
                }
            }

            // Processing for variance %
            var variancePercent = (current / parseFloat($(this).val())) * 100;
            $('#weightDifferencePerc').val(variancePercent.toFixed(2));
        });

        $('#supplierWeight').on('change', function(){
            var nett1 = $('#finalWeight').val() ? parseFloat($('#finalWeight').val()) : 0;
            var nett2 = $(this).val() ? parseFloat($(this).val()) : 0;
            var current = nett1 - nett2;
            $('#weightDifference').val(current.toFixed(0));
            
            var previousRecordsTag = $('#addModal').find('#previousRecordsTag').val();

            if (previousRecordsTag == 'false'){
                $('#addModal').find('#balance').val($(this).val());
                if ($(this).val() <= 0) {
                    $('#addModal').find('#insufficientBalDisplay').hide();
                } else {
                    $('#addModal').find('#insufficientBalDisplay').show();
                }
            }

            // Processing for variance %
            var variancePercent = (current / parseFloat($(this).val())) * 100;
            $('#weightDifferencePerc').val(variancePercent.toFixed(2));
        });

        $('#grossIncoming2').on('keyup', function(){
            var weightType = $('#weightType').val();

            if (weightType == 'Different Container'){
                var gross2 = $(this).val() ? parseFloat($(this).val()) : 0;
                var tare2 = $('#tareOutgoing2').val() ? parseFloat($('#tareOutgoing2').val()) : 0;
                var vehicleWeight2 = $('#vehicleWeight2').val() ? parseFloat($('#vehicleWeight2').val()) : 0;
                var emptyContainerWeight2 = Math.abs(gross2 - vehicleWeight2);

                // Container 1 weights
                var emptyContainer1 = $('#nettWeight').val() ? parseFloat($('#nettWeight').val()) : 0;
                var nett = Math.abs(tare2 - vehicleWeight2 - emptyContainer1);

                $('#emptyContainerWeight2').val(emptyContainerWeight2);
            }else{
                var gross = $(this).val() ? parseFloat($(this).val()) : 0;
                var tare = $('#tareOutgoing2').val() ? parseFloat($('#tareOutgoing2').val()) : 0;
                var nett = Math.abs(gross - tare);
            }

            $('#nettWeight2').val(nett.toFixed(0));
            $('#nettWeight2').trigger('change');
            $('#grossWeightBy2').val('<?php echo $wmUsername; ?>');

            // Update the Flatpickr instance
            grossIncomingDatePicker2.setDate(new Date()); // sets it to current date/time
            $('#grossIncomingDate2').trigger('change');
        });

        $('#grossCapture2').on('click', function(event){
            event.preventDefault();
            var text = $('#indicatorWeight').text();
            $('#grossIncoming2').val(parseFloat(text).toFixed(0));
            $('#grossIncoming2').trigger('keyup');
        });

        $('#tareOutgoing2').on('keyup', function(){
            var weightType = $('#weightType').val();

            if (weightType == 'Different Container'){
                var gross2 = $('#grossIncoming2').val() ? parseFloat($('#grossIncoming2').val()) : 0;
                var tare2 = $(this).val() ? parseFloat($(this).val()) : 0;
                var vehicleWeight2 = $('#vehicleWeight2').val() ? parseFloat($('#vehicleWeight2').val()) : 0;
                var emptyContainerWeight2 = Math.abs(gross2 - vehicleWeight2);
                $('#emptyContainerWeight2').val(emptyContainerWeight2);

                // Container 1 weights
                var emptyContainer1 = $('#nettWeight').val() ? parseFloat($('#nettWeight').val()) : 0;
                var nett = Math.abs(tare2 - vehicleWeight2 - emptyContainer1);
            }else{
                var tare = $(this).val() ? parseFloat($(this).val()) : 0;
                var gross = $('#grossIncoming2').val() ? parseFloat($('#grossIncoming2').val()) : 0;
                var nett = Math.abs(gross - tare);
            }

            $('#nettWeight2').val(nett.toFixed(0));
            $('#nettWeight2').trigger('change');
            $('#tareWeightBy2').val('<?php echo $wmUsername; ?>');

            // Update the Flatpickr instance
            tareOutgoingDatePicker2.setDate(new Date()); // sets it to current date/time
            $('#tareOutgoingDate2').trigger('change');

        });

        $('#tareCapture2').on('click', function(event){
            event.preventDefault();
            var text = $('#indicatorWeight').text();
            $('#tareOutgoing2').val(parseFloat(text).toFixed(0));
            $('#tareOutgoing2').trigger('keyup');
        });

        $('#nettWeight2').on('change', function(){
            var weightType = $('#weightType').val();

            if (weightType == 'Different Container'){
                var current = $(this).val() ? parseFloat($(this).val()) : 0;
            }else{
                var nett2 = $(this).val() ? parseFloat($(this).val()) : 0;
                var nett1 = $('#nettWeight').val() ? parseFloat($('#nettWeight').val()) : 0;
                var current = Math.abs(nett1 - nett2);
            }

            $('#currentWeight').text(current.toFixed(0));
            $('#finalWeight').val(current.toFixed(0));
            $('#reduceWeight').trigger('change');
            //$('#finalWeight').trigger('change');
        });

        $('#currentWeight').on('change', function(){
            var price = $('#unitPrice').val() ? parseFloat($('#unitPrice').val()).toFixed(2) : 0.00;
            var weight = $('#currentWeight').text() ? parseFloat($('#currentWeight').text()) : 0;
            var subTotalPrice = price * weight;
            var sstPrice = subTotalPrice * 0.08;
            var totalPrice = subTotalPrice + sstPrice;
            $('#subTotalPrice').val(subTotalPrice.toFixed(2));
            $('#sstPrice').val(sstPrice.toFixed(2));
            $('#totalPrice').val(totalPrice.toFixed(2));
        });

        $('#transactionStatus').on('change', function(){
            var customerType = $('#addModal').find('#customerType').val();
            var weightType = $('#addModal').find('#weightType').val();

            // Filter products based on transaction status
            filterDropdownByTransactionStatus('#productName', 'allProductOptions', $(this).val());
            filterDropdownByTransactionStatus('#rawMaterialName', 'allRawMatOptions', $(this).val());

            if(weightType == 'Container'){
                $.post('php/modules/weighing/index.php', {action: 'getContainers', userID: $(this).val()}, function (data){
                    var obj = JSON.parse(data);

                    if (obj.status == 'success'){
                        if (obj.message.length > 0){
                            $('#addModal').find('#emptyContainerNo').empty();
                            $('#addModal').find('#emptyContainerNo').append(`<option selected="-">-</option>`);

                            var deliveredTransporter;

                            for (var i = 0; i < obj.message.length; i++) {
                                var id = obj.message[i].id;
                                var container_no = obj.message[i].container_no;

                                $('#addModal').find('#emptyContainerNo').append(
                                    '<option value="'+container_no+'">'+container_no+'</option>'
                                );  
                            }
                        }
                    }
                    else if(obj.status === 'failed'){
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                    else{
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                });
            }

            if($(this).val() == "Purchase" || $(this).val() == "Local"){
                $('#divWeightDifference').show();
                $('#divSupplierWeight').show();
                $('#addModal').find('#orderWeight').val("");
                $('#addModal').find('#supplierWeight').val("0");
                $('#divSupplierName').show();
                $('#divOrderWeight').hide();
                $('#divCustomerName').hide();
                $('#rawMaterialDisplay').show();
                $('#productNameDisplay').hide();
                $('#addModal').find('#divPoSupplyWeight').show();
                
                if ($(this).val() == "Purchase"){
                    $('#divPurchaseOrder').find('label[for="purchaseOrder"]').text('Purchase Order');
                    $('#customerSideCard').show();
                    $('#customerSideLabel').show();
                }else{
                    $('#divPurchaseOrder').find('label[for="purchaseOrder"]').text('Sale Order');
                    $('#customerSideCard').hide();
                    $('#customerSideLabel').hide();
                }
            }
            else{
                $('#divOrderWeight').show();
                $('#addModal').find('#orderWeight').val("0");
                $('#addModal').find('#supplierWeight').val("");
                $('#divWeightDifference').show();
                $('#divSupplierWeight').hide();
                $('#divSupplierName').hide();
                $('#divCustomerName').show();
                $('#rawMaterialDisplay').hide();
                $('#productNameDisplay').show();
                $('#divPurchaseOrder').find('label[for="purchaseOrder"]').text('Sale Order');
                // $('#divPurchaseOrder').find('#purchaseOrder').attr('placeholder', 'Sale Order');
                $('#addModal').find('#divPoSupplyWeight').hide();
                $('#customerSideCard').hide();
                $('#customerSideLabel').hide();
            }
        });

        //productName
        $('#productName').on('change', function(){
            $('#productCode').val($('#productName :selected').data('code'));
            $('#productDescription').val($('#productName :selected').data('description'));
            $('#productHigh').val($('#productName :selected').data('high'));
            $('#productLow').val($('#productName :selected').data('low'));
            $('#productVariance').val($('#productName :selected').data('variance'));

            var price = $('#unitPrice').val() ? parseFloat($('#unitPrice').val()).toFixed(2) : 0.00;
            var weight = $('#currentWeight').text() ? parseFloat($('#currentWeight').text()) : 0;
            var subTotalPrice = price * weight;
            var sstPrice = subTotalPrice * 0.08;
            var totalPrice = subTotalPrice + sstPrice;

            $('#unitPrice').val(price);
            $('#subTotalPrice').val(subTotalPrice.toFixed(2));
            $('#sstPrice').val(sstPrice.toFixed(2));
            $('#totalPrice').val(totalPrice.toFixed(2));
        });

        //supplierName
        $('#supplierName').on('change', function(){
            $('#supplierCode').val($('#supplierName :selected').data('code'));
        });

        //transporter
        $('#transporter').on('change', function(){
            $('#transporterCode').val($('#transporter :selected').data('code'));
        });

        //destination
        $('#destination').on('change', function(){
            $('#destinationCode').val($('#destination :selected').data('code'));
        });

        //plant
        $('#plant').on('change', function(){
            $('#plantCode').val($('#plant :selected').data('code'));
        });

        //customerName
        $('#customerName').on('change', function(){
            $('#customerCode').val($('#customerName :selected').data('code'));
        });

        //rawMaterialName
        $('#rawMaterialName').on('change', function(){
            $('#rawMaterialCode').val($('#rawMaterialName :selected').data('code'));
        });

        //Empty Container No
        $('#emptyContainerNo').on('change', function (){
            var emptyContainerNo = $(this).val();
            var weightType = $('#weightType').val();
            $('#containerNo').val(emptyContainerNo);

            if (emptyContainerNo == '-'){
                $('#addModal').find('#manualVehicle').prop('checked', false).trigger('change');
                $('#addModal').find('#grossIncoming').val(0);
                $('#addModal').find('#grossIncomingDate').val("");
                $('#addModal').find('#tareOutgoing').val(0);
                $('#addModal').find('#tareOutgoingDate').val("");
                $('#addModal').find('#nettWeight').val(0);
                $('#tareOutgoing2').trigger('keyup');
                $('#normalCard').hide();
                $('#grossCapture').show();
                $('#tareCapture').show();
            } else if (emptyContainerNo) { 
                $.post('php/modules/weighing/index.php', {action: 'getEmptyContainer', userID: emptyContainerNo}, function (data){
                    var obj = JSON.parse(data);

                    if (obj.status == 'success'){ 
                        $('#addModal').find('#companyId').val(obj.message.company_id).trigger('change');
                        modalListsReady.then(function(){
                        $('#addModal').find('#project').val(obj.message.project_id).trigger('change');
                        $('#addModal').find('#invoiceNo').val(obj.message.invoice_no);
                        $('#addModal').find('#deliveryNo').val(obj.message.delivery_no);
                        $('#addModal').find('#purchaseOrder').val(obj.message.purchase_order);
                        $('#addModal').find('#sealNo').val(obj.message.seal_no);

                        if (weightType != 'Different Container'){
                            $('#addModal').find('#containerNo2').val(obj.message.container_no2);
                            $('#addModal').find('#sealNo2').val(obj.message.seal_no2);
                        }

                        if (obj.message.transaction_status == 'Purchase' || obj.message.transaction_status == 'Local'){
                            $('#addModal').find('#supplierName').val(obj.message.supplier_name).trigger('change');
                            $('#addModal').find('#rawMaterialName').val(obj.message.raw_mat_name).trigger('change');

                            // Check if manual raw material was used
                            if(obj.message.is_manual_raw_material == 'Y'){
                                $('#addModal').find('#rawMaterialNameTxt').val(obj.message.raw_mat_name);
                                $('#manualRawMaterial').val(1);
                                $('#manualRawMaterial').prop("checked", true);
                                $('.index-rawmaterial').hide();
                                $('#rawMaterialNameTxt').show();
                            }
                            else{
                                $('#manualRawMaterial').val(0);
                                $('#manualRawMaterial').prop("checked", false);
                                $('.index-rawmaterial').show();
                                $('#rawMaterialNameTxt').hide();
                            }
                        }else{
                            $('#addModal').find('#customerName').val(obj.message.customer_name).trigger('change');
                            $('#addModal').find('#productName').val(obj.message.product_name).trigger('change');

                            // Check if manual product was used
                            if(obj.message.is_manual_product == 'Y'){
                                $('#addModal').find('#productNameTxt').val(obj.message.product_name);
                                $('#manualProduct').val(1);
                                $('#manualProduct').prop("checked", true);
                                $('.index-product').hide();
                                $('#productNameTxt').show();
                            }
                            else{
                                $('#manualProduct').val(0);
                                $('#manualProduct').prop("checked", false);
                                $('.index-product').show();
                                $('#productNameTxt').hide();
                            }
                        }
                        $('#addModal').find('#plant').val(obj.message.plant_name).trigger('change');
                        $('#addModal').find('#transporter').val(obj.message.transporter).trigger('change');
                        $('#addModal').find('#destination').val(obj.message.destination).trigger('change');
                        $('#addModal').find('#vehiclePlateNo1').val(obj.message.lorry_plate_no1).trigger('change');
                        $('#addModal').find('#grossIncoming').val(obj.message.gross_weight1);
                        grossIncomingDatePicker.setDate(new Date(obj.message.gross_weight1_date)); 
                        // $('#addModal').find('#grossIncomingDate').val(obj.message.gross_weight1_date);
                        $('#addModal').find('#grossWeightBy1').val(obj.message.gross_weight_by1);
                        $('#addModal').find('#tareOutgoing').val(obj.message.tare_weight1);
                        tareOutgoingDatePicker.setDate(new Date(obj.message.tare_weight1_date));
                        // $('#addModal').find('#tareOutgoingDate').val(obj.message.tare_weight1_date);
                        $('#addModal').find('#tareWeightBy1').val(obj.message.tare_weight_by1);
                        $('#addModal').find('#nettWeight').val(obj.message.nett_weight1);

                        if(obj.message.vehicleNoTxt != null){
                            $('#addModal').find('#vehicleNoTxt').val(obj.message.vehicleNoTxt);
                            $('#manualVehicle').val(1);
                            $('#manualVehicle').prop("checked", true);
                            $('.index-vehicle').hide();
                            $('#vehicleNoTxt').show();
                        }
                        else{
                            $('#addModal').find('#vehiclePlateNo1').val(obj.message.lorry_plate_no1).trigger('change');
                            $('#manualVehicle').val(0);
                            $('#manualVehicle').prop("checked", false);
                            $('.index-vehicle').show();
                            $('#vehicleNoTxt').hide();
                        }
                        
                        $('#tareOutgoing2').trigger('keyup');
                        
                        $('#normalCard').show();
                        $('#grossCapture').hide();
                        $('#tareCapture').hide();
                        });
                    }
                    else if(obj.status === 'failed'){
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                    else{
                        $('#spinnerLoading').hide();
                        $("#failBtn").attr('data-toast-text', obj.message );
                        $("#failBtn").click();
                    }
                });
            }else{
                $('#addModal').find('#manualVehicle').prop('checked', false).trigger('change');
                $('#addModal').find('#grossIncoming').val(0);
                $('#addModal').find('#grossIncomingDate').val("");
                $('#addModal').find('#tareOutgoing').val(0);
                $('#addModal').find('#tareOutgoingDate').val("");
                $('#addModal').find('#nettWeight').val(0);
                $('#tareOutgoing2').trigger('keyup');
                $('#normalCard').hide();
                $('#grossCapture').show();
                $('#tareCapture').show();
            }
        });

        //Container No
        $('#containerNoInput').on('keyup', function(){
            var x = $('#containerNoInput').val();
            x = x.toUpperCase();
            $('#containerNoInput').val(x);
            $('#containerNo').val(x);
        });
        
        $('#containerNoInput').on('change', function () {
            $('#containerNo').val($(this).val());
        });

        //Seal No
        $('#sealNo').on('keyup', function(){
            var x = $('#sealNo').val();
            x = x.toUpperCase();
            $('#sealNo').val(x);
        });

        //Container No 2
        $('#containerNo2').on('keyup', function(){
            var x = $('#containerNo2').val();
            x = x.toUpperCase();
            $('#containerNo2').val(x);
        });

        //Seal No 2
        $('#sealNo2').on('keyup', function(){
            var x = $('#sealNo2').val();
            x = x.toUpperCase();
            $('#sealNo2').val(x);
        });
    });

    function addWeight(prefill) {
            modalCompanyId = null;
            
            // Show Capture Buttons When Add New
            $('#addModal').find('#grossCapture').show();
            $('#addModal').find('#tareCapture').show();
            $('#addModal').find('#id').val("");
            $('#addModal').find('#currentWeight').text("0");
            $('#addModal').find('#transactionId').val("");
            var defaultCompanyId = <?= hasPermission('Weighing', ['view_all_companies']) ? 1 : 'sessionCompanyId' ?>;
            $('#addModal').find('#companyId').val(prefill && prefill.companyId ? prefill.companyId : defaultCompanyId).trigger('change');
            $('#addModal').find('#transactionStatus').val("Sales").trigger('change');
            $('#addModal').find('#emptyContainerNo').val("").trigger('change');
            $('#addModal').find('#weightType').val("Normal").trigger('change');
            $('#addModal').find('#customerType').val("Normal").trigger('change');
            $('#addModal').find('#transactionDate').val(formatDate2(today));
            $('#addModal').find('#vehiclePlateNo1').val("").trigger('change');
            $('#addModal').find('#vehiclePlateNo2').val("").trigger('change');
            $('#addModal').find('#supplierWeight').val("");
            $('#addModal').find('#customerCode').val("");
            $('#addModal').find('#customerName').val("-").trigger('change');
            $('#addModal').find('#supplierCode').val("");
            $('#addModal').find('#supplierName').val("-").trigger('change');
            $('#addModal').find('#productCode').val("");
            $('#addModal').find('#productName').val("-").trigger('change');
            $('#addModal').find('#rawMaterialCode').val("");
            $('#addModal').find('#rawMaterialName').val("-").trigger('change');
            $('#addModal').find('#plantCode').val("");
            $('#addModal').find('#sealNo').val("");
            $('#addModal').find('#invoiceNo').val("");
            $('#addModal').find('#purchaseOrder').val("").trigger('change');
            $('#addModal').find('#salesOrder').val("").trigger('change');
            $('#addModal').find('#deliveryNo').val("");
            $('#addModal').find('#transporterCode').val("");
            $('#addModal').find('#transporter').val("-").trigger('change');
            $('#addModal').find('#project').val("-").trigger('change');
            $('#addModal').find('#destinationCode').val("");
            $('#addModal').find('#plantCode').val("");
            $('#addModal').find('#plant').val("<?=$wmPlantName ?>").trigger('change');
            $('#addModal').find('#destination').val("-").trigger('change');
            $('#addModal').find('#replacementContainer').val('').trigger('keyup');
            $('#addModal').find('#otherRemarks').val("");
            $('#addModal').find('#manualVehicle').prop('checked', false).trigger('change');
            $('#addModal').find('#manualVehicle2').prop('checked', false).trigger('change');
            $('#addModal').find('#manualProduct').prop('checked', false).trigger('change');
            $('#addModal').find('#manualRawMaterial').prop('checked', false).trigger('change');
            $('#addModal').find('#manualCustomer').prop('checked', false).trigger('change');
            $('#addModal').find('#manualSupplier').prop('checked', false).trigger('change');
            $('#addModal').find('#grossIncoming').val("");
            grossIncomingDatePicker.clear();
            $('#addModal').find('#tareOutgoing').val("");
            tareOutgoingDatePicker.clear();
            $('#addModal').find('#nettWeight').val("");
            $('#addModal').find('#vehicleWeight2').val("");
            $('#addModal').find('#emptyContainerWeight2').val("");
            $('#addModal').find('#grossIncoming2').val("");
            $('#addModal').find('#status').val("");
            grossIncomingDatePicker2.clear();
            $('#addModal').find('#tareOutgoing2').val("");
            tareOutgoingDatePicker2.clear();
            $('#addModal').find('#nettWeight2').val("");
            $('#addModal').find('#reduceWeight').val("");
            $('#addModal').find('#customerSideCompany').val("");
            $('#addModal').find('#customerSideRemovalPassNo').val("");
            $('#addModal').find('#customerSideLicenseNo').val("");
            $('#addModal').find('#customerSideMoistureContent').val("");
            $('#addModal').find('#customerSideOfficerName').val("");
            $('#addModal').find('#customerSideRainbowDriver').val("");
            customerSideTimeInPicker.clear();
            customerSideTimeOutPicker.clear();
            $('#addModal').find('#weightDifference').val("");
            $('#addModal').find('#weightDifferencePerc').val("");
            $('#addModal').find('#manualWeightToggle').prop('checked', false).val('false').trigger('change');
            $('#addModal').find('#weighbridge').val("");
            $('#addModal').find('#productDescription').val("");
            $('#addModal').find('#productHigh').val("");
            $('#addModal').find('#productLow').val("");
            $('#addModal').find('#productVariance').val("");
            $('#addModal').find('#orderWeight').val("0");
            $('#addModal').find('#unitPrice').val("0.00");
            $('#addModal').find('#subTotalPrice').val("0.00");
            $('#addModal').find('#sstPrice').val("0.00");
            $('#addModal').find('#totalPrice').val("0.00");
            $('#addModal').find('#finalWeight').val("");
            $('#addModal').find('#balance').val("");
            $('#addModal').find('#insufficientBalDisplay').hide();
            $('#addModal').find('#containerNoInput').val("");
            $('#addModal').find('#containerNo').val("");
            $('#addModal').find('#containerNo2').val("");
            $('#addModal').find('#sealNo2').val("");

            // Show select and hide input readonly
            $('#addModal').find('#salesOrderEdit').val("").hide();
            $('#addModal').find('#purchaseOrderEdit').val("").hide();
            $('#addModal').find('#salesOrder').next('.select2-container').show();

            // Remove Validation Error Message
            $('#addModal .is-invalid').removeClass('is-invalid');

            $('#addModal .select2[required]').each(function () {
                var select2Field = $(this);
                var select2Container = select2Field.next('.select2-container');
                
                select2Container.find('.select2-selection').css('border', ''); // Remove red border
                select2Container.next('.select2-error').remove(); // Remove error message
            });

            // Prefill from the calling page (e.g. Delivery Order / Goods Received row)
            if (prefill) {
                applyPrefill(prefill);
            }

            $('#addModal').modal('show');

            $('#weightForm').validate({
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

    function handleWeightSubmit(withPrint) {
        var trueWeight = 0;
        var variance = $('#productVariance').val() || '';
        var high = $('#productHigh').val() || '';
        var low = $('#productLow').val() || '';
        var final = $('#finalWeight').val() || '0';
        var pass = true;

        if ($('#transactionStatus').val() == "Purchase" || $('#transactionStatus').val() == "Local") {
            trueWeight = parseFloat($('#addModal').find('#supplierWeight').val());
        }
        else {
            trueWeight = parseFloat($('#addModal').find('#orderWeight').val());
        }

        if ($('#weightType').val() == 'Normal' && ($('#grossIncoming').val() && $('#tareOutgoing').val())) {
            isComplete = 'Y';
        }
        else if ($('#weightType').val() == 'Container' && ($('#grossIncoming').val() && $('#tareOutgoing').val() && $('#grossIncoming2').val() && $('#tareOutgoing2').val())) {
            isComplete = 'Y';
        }
        else {
            isComplete = 'N';
        }

        if (isComplete == 'Y' && variance != '') {
            final = parseFloat(final);
            low = low != '' ? parseFloat(low) : null;
            high = high != '' ? parseFloat(high) : null;

            if (variance == 'W') {
                if (low !== null && (final < trueWeight - low)) {
                    pass = false;
                }
                else if (high !== null && (final > trueWeight + high)) {
                    pass = false;
                }
            }
            else if (variance == 'P') {
                if (low !== null && (final < trueWeight * (1 - low / 100))) {
                    pass = false;
                }
                else if (high !== null && (final > trueWeight * (1 + high / 100))) {
                    pass = false;
                }
            }
        }

        pass = true;

        // Validation for Normal weighing type
        var transStatus = $('#transactionStatus').val();
        var transStatusLabel = '';
        if (transStatus == 'Sales') {
            transStatusLabel = '<?=$languageArray['dispatch_code'][$language]?>';
        } else if (transStatus == 'Purchase') {
            transStatusLabel = '<?=$languageArray['receiving_code'][$language]?>';
        } else if (transStatus == 'Port') {
            transStatusLabel = '<?=$languageArray['trx_to_port_code'][$language]?>';
        } else if (transStatus == 'Local') {
            transStatusLabel = '<?=$languageArray['internal_transfer_code'][$language]?>';
        } else {
            transStatusLabel = '<?=$languageArray['miscellaneous_code'][$language]?>';
        }
        
        if ($('#weightType').val() == 'Normal' && $('#grossIncoming').val() && $('#tareOutgoing').val()) {
            var incoming = parseFloat($('#grossIncoming').val()) || 0;
            var outgoing = parseFloat($('#tareOutgoing').val()) || 0;

            // if (transStatus == 'Sales' || transStatus == 'Port' || transStatus == 'Misc') {
            if (transStatus == 'Sales') {
                // Sales | Port | Misc: incoming < outgoing
                if (incoming >= outgoing) {
                    alert('For ' + transStatusLabel + ' transaction, outgoing weight must be greater than incoming weight.');
                    return;
                }
            // } else if (transStatus == 'Purchase' || transStatus == 'Local') {
            } else if (transStatus == 'Purchase') {
                // Purchase | Local: outgoing < incoming
                if (outgoing >= incoming) {
                    alert('For ' + transStatusLabel + ' transaction, outgoing weight must be lesser than incoming weight.');
                    return;
                }
            }
        }else if ($('#weightType').val() == 'Container' && $('#grossIncoming2').val() && $('#tareOutgoing2').val()){
            var incoming = parseFloat($('#grossIncoming2').val()) || 0;
            var outgoing = parseFloat($('#tareOutgoing2').val()) || 0;

            // if (transStatus == 'Sales' || transStatus == 'Port' || transStatus == 'Misc') {
            if (transStatus == 'Sales') {
                // Sales | Port | Misc: incoming < outgoing
                if (incoming >= outgoing) {
                    alert('For ' + transStatusLabel + ' transaction, outgoing 2 weight must be greater than incoming 2 weight.');
                    return;
                }
            // } else if (transStatus == 'Purchase' || transStatus == 'Local') {
            } else if (transStatus == 'Purchase') {
                // Purchase | Local: outgoing < incoming
                if (outgoing >= incoming) {
                    alert('For ' + transStatusLabel + ' transaction, outgoing 2 weight must be lesser than incoming 2 weight.');
                    return;
                }
            }
        }

        if (!withPrint) {
            var isValid = true;

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
        }

        var isEmptyContainer = $('#weightType').val() == 'Empty Container' ? 'Y' : 'N';

        if (pass && $('#weightForm').valid()) {
            $('#spinnerLoading').show();
            // Unchecked checkboxes are not serialized, so always send manualWeight
            var formData = $('#weightForm').serialize() + ($('#manualWeightToggle').is(':checked') ? '' : '&manualWeight=false');
            $.post('php/modules/weighing/index.php', formData, function (data) {
                var obj = JSON.parse(data);
                if (obj.status === 'success') {
                    $('#spinnerLoading').hide();
                    $('#addModal').modal('hide');
                    $("#successBtn").attr('data-toast-text', obj.message);
                    $("#successBtn").click();
                    if (withPrint) {
                        preparePrePrintModal(obj.id, $('#transactionStatus').val(), isEmptyContainer);
                        $("#prePrintModal").modal("show");
                        $('#prePrintForm').validate({
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

                    // Let the including page refresh its own tables / redirect
                    if (typeof settings.onSaved === 'function') {
                        settings.onSaved(obj, withPrint);
                    }
                }
                else if (obj.status === 'failed') {
                    $('#spinnerLoading').hide();
                    if (!withPrint) { alert(obj.message); }
                    $("#failBtn").attr('data-toast-text', obj.message);
                    $("#failBtn").click();
                }
                else {
                    $('#spinnerLoading').hide();
                    if (!withPrint) { alert(obj.message); }
                    $("#failBtn").attr('data-toast-text', 'Failed to save');
                    $("#failBtn").click();
                }
            });
        }
    }

    // Rebuild a dropdown from a master data 'list' endpoint; always resolves so callers can chain on it
    function loadCompanyOptions(url, companyId, selector, buildOption) {
        return $.post(url, { action: 'list', company: companyId }).then(function(data) {
            var $sel = $(selector);
            $sel.empty().append('<option selected>-</option>');
            try {
                var obj = JSON.parse(data);
                if (obj.status === 'success') {
                    $.each(obj.data, function(i, item) {
                        $sel.append(buildOption(item));
                    });
                }
            } catch (e) {
                console.error('Failed to load ' + selector, e);
            }
            $sel.val('-').trigger('change');
        }, function() {
            return $.Deferred().resolve();
        });
    }

    // Reload the add/edit modal listings for the selected company; returns a promise resolved once all lists are rebuilt
    function loadModalListsByCompany(companyId) {
        if (!companyId || companyId === '-' || companyId == modalCompanyId) {
            return modalListsReady;
        }
        modalCompanyId = companyId;
        var productFiltered = optionCache.allProductOptions !== null;
        var rawMatFiltered = optionCache.allRawMatOptions !== null;

        modalListsReady = $.when(
            loadCompanyOptions('php/modules/customer/index.php', companyId, '#customerName', function(item) {
                return $('<option>').val(item.name).text(item.name).attr('data-code', item.customer_code);
            }),
            loadCompanyOptions('php/modules/supplier/index.php', companyId, '#supplierName', function(item) {
                return $('<option>').val(item.name).text(item.name).attr('data-code', item.supplier_code);
            }),
            loadCompanyOptions('php/modules/item/index.php', companyId, '#productName', function(item) {
                return productOption(item, item.name, item.product_code + ' - ' + item.name);
            }),
            loadCompanyOptions('php/modules/item/index.php', companyId, '#rawMaterialName', function(item) {
                return productOption(item, item.name, item.product_code + ' - ' + item.name);
            }),
            loadCompanyOptions('php/modules/destination/index.php', companyId, '#destination', function(item) {
                return $('<option>').val(item.name).text(item.name).attr('data-code', item.destination_code);
            }),
            loadCompanyOptions('php/modules/project/index.php', companyId, '#project', function(item) {
                return $('<option>').val(item.id).text(item.project_code);
            }),
            loadCompanyOptions('php/modules/vehicle/index.php', companyId, '#vehiclePlateNo1', function(item) {
                return $('<option>').val(item.veh_number).text(item.veh_number).attr('data-weight', item.vehicle_weight);
            }),
            loadCompanyOptions('php/modules/vehicle/index.php', companyId, '#vehiclePlateNo2', function(item) {
                return $('<option>').val(item.veh_number).text(item.veh_number).attr('data-weight', item.vehicle_weight);
            })
        ).then(function() {
            optionCache.allProductOptions = null;
            optionCache.allRawMatOptions = null;
            var status = $('#transactionStatus').val();
            if (productFiltered) filterDropdownByTransactionStatus('#productName', 'allProductOptions', status);
            if (rawMatFiltered) filterDropdownByTransactionStatus('#rawMaterialName', 'allRawMatOptions', status);
        });
        return modalListsReady;
    }

    // Filter dropdown options based on transaction status
    function filterDropdownByTransactionStatus(selector, allOptionsVar, status) {
        if (!optionCache[allOptionsVar]) {
            optionCache[allOptionsVar] = $(selector + ' option').clone(true);
        }

        var dataAttr = 'is-sales';
        if (status === 'Sales') dataAttr = 'is-sales';
        else if (status === 'Purchase') dataAttr = 'is-purchase';
        else if (status === 'Port') dataAttr = 'is-port';
        else if (status === 'Misc') dataAttr = 'is-misc';

        $(selector).empty();
        optionCache[allOptionsVar].each(function() {
            var $option = $(this).clone(true);
            if ($option.val() === '-' || $option.val() === '') {
                $(selector).append($option);
            } else if ($option.data(dataAttr) === 'Y') {
                $(selector).append($option);
            }
        });

        $(selector).val('-').trigger('change');
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

    function handleWeightType(weightType){
        if (weightType == 'Container'){
            $('#addModal').find('#manualVehicle').prop('checked', false).trigger('change');
            $('#addModal').find('#grossIncoming').val(0);
            $('#addModal').find('#grossIncomingDate').val("");
            $('#addModal').find('#tareOutgoing').val(0);
            $('#addModal').find('#tareOutgoingDate').val("");
            $('#addModal').find('#nettWeight').val(0);
            $('#normalCard').hide();
            $('#grossCapture').hide();
            $('#tareCapture').hide();
            $('#containerCard').show();
        }else if(weightType == 'Empty Container'){
            $('#addModal').find('#manualVehicle2').prop('checked', false).trigger('change');
            $('#addModal').find('#grossIncoming2').val(0);
            $('#addModal').find('#grossIncomingDate2').val("");
            $('#addModal').find('#tareOutgoing2').val(0);
            $('#addModal').find('#tareOutgoingDate2').val("");
            $('#addModal').find('#nettWeight2').val(0);
            $('#containerCard').hide();
            $('#grossCapture').show();
            $('#tareCapture').show();
            $('#normalCard').show();
        }else if(weightType == 'Different Container'){
            $('#addModal').find('#manualVehicle').prop('checked', false).trigger('change');
            $('#addModal').find('#grossIncoming').val(0);
            $('#addModal').find('#grossIncomingDate').val("");
            $('#addModal').find('#tareOutgoing').val(0);
            $('#addModal').find('#tareOutgoingDate').val("");
            $('#addModal').find('#nettWeight').val(0);
            $('#normalCard').hide();
            $('#grossCapture').hide();
            $('#tareCapture').hide();
            $('#containerCard').show();
        }else{
            $('#addModal').find('#manualVehicle2').prop('checked', false).trigger('change');
            $('#addModal').find('#grossIncoming2').val(0);
            $('#addModal').find('#grossIncomingDate2').val("");
            $('#addModal').find('#tareOutgoing2').val(0);
            $('#addModal').find('#tareOutgoingDate2').val("");
            $('#addModal').find('#nettWeight2').val(0);
            $('#normalCard').show();
            $('#grossCapture').show();
            $('#tareCapture').show();
            $('#containerCard').hide();
        }
    }

    function editWeight(id, isContainer){
        $('#spinnerLoading').show();

        var type = '';
        if (isContainer == 'Y'){
            type = 'Container';
        }else{
            type = 'Weight'
        }

        $.post('php/modules/weighing/index.php', {action: 'getWeight', userID: id, type: type}, function(data)
        {
            var obj = JSON.parse(data);
            if(obj.status === 'success'){
                if(obj.message.is_complete == 'Y'){
                    // Hide Capture Button When Edit
                    $('#addModal').find('#grossCapture').hide();
                    $('#addModal').find('#tareCapture').hide();
                }
                else{
                    // Show Capture Button When Edit
                    $('#addModal').find('#grossCapture').show();
                    $('#addModal').find('#tareCapture').show();
                }

                $('#addModal').find('#id').val(obj.message.id);
                $('#addModal').find('#companyId').val(obj.message.company_id).trigger('change');
                modalListsReady.then(function(){
                    $('#addModal').find('#transactionId').val(obj.message.transaction_id);
                    $('#addModal').find('#transactionStatus').val(obj.message.transaction_status).trigger('change');
                    $('#addModal').find('#weightType').val(obj.message.weight_type).trigger('change');
                    $('#addModal').find('#customerType').val(obj.message.customer_type).trigger('change');
                    $('#addModal').find('#transactionDate').val(formatDate2(new Date(obj.message.transaction_date)));

                    if(obj.message.transaction_status == "Purchase" || obj.message.transaction_status == "Local"){
                        $('#divWeightDifference').show();
                        $('#divSupplierWeight').show();
                        $('#divSupplierName').show();
                        $('#divOrderWeight').hide();
                        $('#divCustomerName').hide();
                    }
                    else{
                        $('#divOrderWeight').show();
                        $('#divWeightDifference').show();
                        $('#divSupplierWeight').hide();
                        $('#divSupplierName').hide();
                        $('#divCustomerName').show();
                    }

                    if(obj.message.vehicleNoTxt != null){
                        $('#addModal').find('#vehicleNoTxt').val(obj.message.vehicleNoTxt);
                        $('#manualVehicle').val(1);
                        $('#manualVehicle').prop("checked", true);
                        $('.index-vehicle').hide();
                        $('#vehicleNoTxt').show();
                    }
                    else{
                        $('#addModal').find('#vehiclePlateNo1Edit').val('EDIT');
                        $('#addModal').find('#vehiclePlateNo1').val(obj.message.lorry_plate_no1).select2('destroy').select2();
                        $('#manualVehicle').val(0);
                        $('#manualVehicle').prop("checked", false);
                        $('.index-vehicle').show();
                        $('#vehicleNoTxt').hide();
                    }

                    if(obj.message.vehicleNoTxt2 != null){
                        $('#addModal').find('#vehicleNoTxt2').val(obj.message.vehicleNoTxt2);
                        $('#manualVehicle2').val(1);
                        $('#manualVehicle2').prop("checked", true);
                        $('.index-vehicle2').hide();
                        $('#vehicleNoTxt2').show();
                    }
                    else{
                        $('#addModal').find('#vehiclePlateNo2').val(obj.message.lorry_plate_no2).select2('destroy').select2();
                        $('#manualVehicle2').val(0);
                        $('#manualVehicle2').prop("checked", false);
                        $('.index-vehicle2').show();
                        $('#vehicleNoTxt2').hide();
                    }
                    
                    $('#addModal').find('#productCode').val(obj.message.product_code);                
                    $('#addModal').find('#purchaseOrder').val(obj.message.purchase_order);
                    $('#addModal').find('#invoiceNo').val(obj.message.invoice_no);
                    $('#addModal').find('#deliveryNo').val(obj.message.delivery_no);
                    $('#addModal').find('#transporterCode').val(obj.message.transporter_code);
                    $('#addModal').find('#transporter').val(obj.message.transporter).trigger('change');
                    $('#addModal').find('#project').val(obj.message.project_id).trigger('change');
                    $('#addModal').find('#customerName').val(obj.message.customer_name).select2('destroy').select2();
                    $('#addModal').find('#customerCode').val(obj.message.customer_code);
                    $('#addModal').find('#supplierName').val(obj.message.supplier_name).select2('destroy').select2();
                    $('#addModal').find('#supplierCode').val(obj.message.supplier_code);
                    $('#addModal').find('#rawMaterialCode').val(obj.message.raw_mat_code);
                    $('#addModal').find('#rawMaterialName').val(obj.message.raw_mat_name).trigger('change');
                    $('#addModal').find('#productName').val(obj.message.product_name).trigger('change');
                    $('#addModal').find('#productCode').val(obj.message.product_code);

                    // Check if manual product was used
                    if(obj.message.is_manual_product == 'Y'){
                        $('#addModal').find('#productNameTxt').val(obj.message.product_name);
                        $('#manualProduct').val(1);
                        $('#manualProduct').prop("checked", true);
                        $('.index-product').hide();
                        $('#productNameTxt').show();
                    }
                    else{
                        $('#manualProduct').val(0);
                        $('#manualProduct').prop("checked", false);
                        $('.index-product').show();
                        $('#productNameTxt').hide();
                    }

                    // Check if manual raw material was used
                    if(obj.message.is_manual_raw_material == 'Y'){
                        $('#addModal').find('#rawMaterialNameTxt').val(obj.message.raw_mat_name);
                        $('#manualRawMaterial').val(1);
                        $('#manualRawMaterial').prop("checked", true);
                        $('.index-rawmaterial').hide();
                        $('#rawMaterialNameTxt').show();
                    }
                    else{
                        $('#manualRawMaterial').val(0);
                        $('#manualRawMaterial').prop("checked", false);
                        $('.index-rawmaterial').show();
                        $('#rawMaterialNameTxt').hide();
                    }
                    $('#addModal').find('#supplierWeight').val(obj.message.supplier_weight);
                    $('#addModal').find('#orderWeight').val(obj.message.order_weight);
                    $('#addModal').find('#destinationCode').val(obj.message.destination_code);
                    $('#addModal').find('#destination').val(obj.message.destination).trigger('change');
                    $('#addModal').find('#plant').val(obj.message.plant_name).trigger('change');
                    $('#addModal').find('#plantCode').val(obj.message.plant_code);
                    
                    $('#addModal').find('#otherRemarks').val(obj.message.remarks);
                    $('#addModal').find('#grossIncoming').val(obj.message.gross_weight1);
                    grossIncomingDatePicker.setDate(new Date(obj.message.gross_weight1_date));
                    $('#addModal').find('#grossWeightBy1').val(obj.message.gross_weight_by1);
                    $('#addModal').find('#tareOutgoing').val(obj.message.tare_weight1);
                    tareOutgoingDatePicker.setDate(obj.message.tare_weight1_date != null ? new Date(obj.message.tare_weight1_date) : null);
                    $('#addModal').find('#tareWeightBy1').val(obj.message.tare_weight_by1);
                    $('#addModal').find('#nettWeight').val(obj.message.nett_weight1);
                    $('#addModal').find('#vehicleWeight2').val(obj.message.lorry_no2_weight);
                    $('#addModal').find('#emptyContainerWeight2').val(obj.message.empty_container2_weight);
                    $('#addModal').find('#replacementContainer').val(obj.message.replacement_container).trigger('keyup');
                    $('#addModal').find('#grossIncoming2').val(obj.message.gross_weight2);
                    grossIncomingDatePicker2.setDate(obj.message.gross_weight2_date != null ? new Date(obj.message.gross_weight2_date) : null);
                    $('#addModal').find('#grossWeightBy2').val(obj.message.gross_weight_by2);
                    $('#addModal').find('#tareOutgoing2').val(obj.message.tare_weight2);
                    tareOutgoingDatePicker2.setDate(obj.message.tare_weight2_date != null ? new Date(obj.message.tare_weight2_date) : null);
                    $('#addModal').find('#tareWeightBy2').val(obj.message.tare_weight_by2);
                    $('#addModal').find('#nettWeight2').val(obj.message.nett_weight2);
                    $('#addModal').find('#reduceWeight').val(obj.message.reduce_weight);
                    $('#addModal').find('#customerSideCompany').val(obj.message.customer_side_company);
                    $('#addModal').find('#customerSideRemovalPassNo').val(obj.message.customer_side_removal_pass_no);
                    $('#addModal').find('#customerSideLicenseNo').val(obj.message.customer_side_license_no);
                    $('#addModal').find('#customerSideMoistureContent').val(obj.message.customer_side_moisture_content);
                    $('#addModal').find('#customerSideOfficerName').val(obj.message.customer_side_officer_name);
                    $('#addModal').find('#customerSideRainbowDriver').val(obj.message.customer_side_rainbow_driver);
                    customerSideTimeInPicker.setDate(obj.message.customer_side_time_in != null && obj.message.customer_side_time_in != '' ? new Date(obj.message.customer_side_time_in) : null);
                    customerSideTimeOutPicker.setDate(obj.message.customer_side_time_out != null && obj.message.customer_side_time_out != '' ? new Date(obj.message.customer_side_time_out) : null);
                    $('#addModal').find('#weightDifference').val(obj.message.weight_different);
                    $('#addModal').find('#weightDifferencePerc').val(obj.message.weight_different_perc);
                    $('#addModal').find('#currentWeight').text(obj.message.final_weight);

                    if(obj.message.manual_weight == 'true'){
                        $("#manualWeightToggle").prop("checked", true).val('true');
                        $('#manualWeightToggle').trigger('change');
                    }
                    else{
                        $("#manualWeightToggle").prop("checked", false).val('false');
                        $('#manualWeightToggle').trigger('change');
                    }

                    $('#addModal').find('#indicatorId').val(obj.message.indicator_id);
                    $('#addModal').find('#weighbridge').val(obj.message.weighbridge_id);
                    $('#addModal').find('#indicatorId2').val(obj.message.indicator_id_2);
                    $('#addModal').find('#productDescription').val(obj.message.product_description);
                    $('#addModal').find('#unitPrice').val(obj.message.unit_price);
                    $('#addModal').find('#subTotalPrice').val(obj.message.sub_total);
                    $('#addModal').find('#sstPrice').val(obj.message.sst);
                    $('#addModal').find('#totalPrice').val(obj.message.total_price);
                    $('#addModal').find('#finalWeight').val(obj.message.final_weight);
                    $('#addModal').find('#containerNoInput').val(obj.message.container_no);
                    $('#addModal').find('#containerNo').val(obj.message.container_no);
                    $('#addModal').find('#containerNo2').val(obj.message.container_no2);
                    $('#addModal').find('#sealNo').val(obj.message.seal_no);
                    $('#addModal').find('#sealNo2').val(obj.message.seal_no2);

                    // Load container data and update the emptyContainerNo field if it's a container
                    if((obj.message.weight_type == 'Container' || obj.message.weight_type == 'Different Container') && obj.message.container_no){
                        loadContainerData(function() {
                            $('#normalCard').show();

                            // Check if container value exist in the select tag
                            var emptyContainerExists = $('#addModal').find('#emptyContainerNo option').filter(function() {
                                return $(this).val() === obj.message.container_no;
                            }).length > 0;

                            if (!emptyContainerExists){
                                // Append missing empty container no
                                $('#addModal').find('#emptyContainerNo').append(
                                    '<option value="'+obj.message.container_no+'">'+obj.message.container_no+'</option>'
                                );
                            }

                            // Callback to ensure the dropdown is updated before setting the value
                            $('#addModal').find('#emptyContainerNo').val(obj.message.container_no).select2('destroy').select2();

                            // Initialize all Select2 elements in the modal
                            $('#addModal .select2').select2({
                                allowClear: true,
                                placeholder: "Please Select",
                                dropdownParent: $('#addModal') // Ensures dropdown is not cut off
                            });

                            // Apply custom styling to Select2 elements in addModal
                            $('#addModal .select2-container .select2-selection--single').css({
                                'padding-top': '4px',
                                'padding-bottom': '4px',
                                'height': 'auto'
                            });

                            $('#addModal .select2-container .select2-selection__arrow').css({
                                'padding-top': '33px',
                                'height': 'auto'
                            });
                        });
                    }

                    // Load these field after PO/SO is loaded
                    /*$('#addModal').on('orderLoaded', function() {
                        $('#addModal').find('#customerCode').val(obj.message.customer_code);
                        $('#addModal').find('#customerName').val(obj.message.customer_name).trigger('change');
                        $('#addModal').find('#supplierCode').val(obj.message.supplier_code);
                        $('#addModal').find('#supplierName').val(obj.message.supplier_name).trigger('change')
                        $('#addModal').find('#rawMaterialCode').val(obj.message.raw_mat_code);
                        $('#addModal').find('#rawMaterialName').val(obj.message.raw_mat_name).trigger('change');
                        $('#addModal').find('#productName').val(obj.message.product_name).trigger('change');
                        $('#addModal').find('#productCode').val(obj.message.product_code);
                        $('#addModal').find('#supplierWeight').val(obj.message.supplier_weight);
                        $('#addModal').find('#orderWeight').val(obj.message.order_weight);
                        $('#addModal').find('#destinationCode').val(obj.message.destination_code);
                        $('#addModal').find('#destination').val(obj.message.destination).trigger('change');
                        $('#addModal').find('#plant').val(obj.message.plant_name).trigger('change');
                        $('#addModal').find('#plantCode').val(obj.message.plant_code);

                        // Hide select and show input readonly
                        // if (obj.message.transaction_status == 'Purchase'){
                        //     $('#addModal').find('#purchaseOrder').next('.select2-container').hide();
                        //     $('#addModal').find('#purchaseOrderEdit').val(obj.message.purchase_order).show();
                        // }else{
                        //     $('#addModal').find('#salesOrder').next('.select2-container').hide();
                        //     $('#addModal').find('#salesOrderEdit').val(obj.message.purchase_order).show();
                        // }
                    });*/

                    // Initialize all Select2 elements in the modal
                    $('#addModal .select2').select2({
                        allowClear: true,
                        placeholder: "Please Select",
                        dropdownParent: $('#addModal') // Ensures dropdown is not cut off
                    });

                    // Apply custom styling to Select2 elements in addModal
                    $('#addModal .select2-container .select2-selection--single').css({
                        'padding-top': '4px',
                        'padding-bottom': '4px',
                        'height': 'auto'
                    });

                    $('#addModal .select2-container .select2-selection__arrow').css({
                        'padding-top': '33px',
                        'height': 'auto'
                    });

                    // Remove Validation Error Message
                    $('#addModal .is-invalid').removeClass('is-invalid');

                    $('#addModal .select2[required]').each(function () {
                        var select2Field = $(this);
                        var select2Container = select2Field.next('.select2-container');
                        
                        select2Container.find('.select2-selection').css('border', ''); // Remove red border
                        select2Container.next('.select2-error').remove(); // Remove error message
                    });

                    $('#addModal').modal('show');
                
                    $('#weightForm').validate({
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
            }
            else if(obj.status === 'failed'){
                $('#spinnerLoading').hide();
                $("#failBtn").attr('data-toast-text', obj.message );
                $("#failBtn").click();
            }
            else{
                $('#spinnerLoading').hide();
                $("#failBtn").attr('data-toast-text', obj.message );
                $("#failBtn").click();
            }
            $('#spinnerLoading').hide();
        });
    }

    function loadContainerData(callback) {
        var transactionStatus = $('#transactionStatus').val();
        $.post('php/modules/weighing/index.php', {action: 'getContainers', userID: transactionStatus}, function (data){
            var obj = JSON.parse(data);

            if (obj.status == 'success'){
                if (obj.message.length > 0){
                    $('#addModal').find('#emptyContainerNo').empty();
                    $('#addModal').find('#emptyContainerNo').append('<option selected="-">-</option>');

                    // Populate container numbers
                    for (var i = 0; i < obj.message.length; i++) {
                        var id = obj.message[i].id;
                        var container_no = obj.message[i].container_no;

                        $('#addModal').find('#emptyContainerNo').append(
                            '<option value="'+container_no+'">'+container_no+'</option>'
                        );
                    }

                    // Execute the callback to finalize the process
                                    }

                // Execute the callback to finalize the process
                if (callback) { callback(); }
            } else {
                $('#spinnerLoading').hide();
                $("#failBtn").attr('data-toast-text', obj.message );
                $("#failBtn").click();
            }
        });
    }

    function preparePrePrintModal(id, transactionStatus, isEmptyContainer) {
        $('#prePrintModal').find('#id').val(id);
        $('#prePrintModal').find('#isEmptyContainer').val(isEmptyContainer);
        $('#prePrintModal').find('#prePrintTransactionStatus').val(transactionStatus);
        $('#prePrintModal').find('#prePrint').val("<?=$language ?>");
        $('#prePrintModal').find('#printTemplate').val("with_weight");

        if (transactionStatus == 'Purchase' || isEmptyContainer == 'Y') {
            $('#prePrintModal').find('#printTemplateDisplay').hide();
        } else {
            $('#prePrintModal').find('#printTemplateDisplay').show();
        }
    }

    function printWeight(id, transactionStatus, isEmptyContainer = 'N') {
        preparePrePrintModal(id, transactionStatus, isEmptyContainer);
        $("#prePrintModal").modal("show");

        $('#prePrintForm').validate({
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

    // prefill: { companyId, transactionStatus, plantCode, customerCode, supplierCode, productCode, rawMaterialCode, deliveryNo, purchaseOrder }
    // Codes are matched against the options' data-code because the dropdown values are names.
    function applyPrefill(prefill) {
        modalListsReady.then(function(){
            // Transaction status first - it rebuilds the product / raw material lists
            if (prefill.transactionStatus) {
                $('#addModal').find('#transactionStatus').val(prefill.transactionStatus).trigger('change');
            }

            selectOptionByCode('#plant', prefill.plantCode);
            selectOptionByCode('#customerName', prefill.customerCode);
            selectOptionByCode('#supplierName', prefill.supplierCode);
            selectOptionByCode('#productName', prefill.productCode);
            selectOptionByCode('#rawMaterialName', prefill.rawMaterialCode);

            if (prefill.deliveryNo) {
                $('#addModal').find('#deliveryNo').val(prefill.deliveryNo);
            }

            if (prefill.purchaseOrder) {
                $('#addModal').find('#purchaseOrder').val(prefill.purchaseOrder);
            }
        });
    }

    function selectOptionByCode(selector, code) {
        if (code === undefined || code === null || code === '') {
            return;
        }

        var option = $('#addModal').find(selector + ' option').filter(function() {
            return String($(this).data('code')) === String(code);
        }).first();

        if (option.length > 0) {
            $('#addModal').find(selector).val(option.val()).trigger('change');
        }
    }

    function initWeighingModal(options) {
        settings = $.extend(settings, options || {});
    }

    window.initWeighingModal = initWeighingModal;
    window.addWeight = addWeight;
    window.editWeight = editWeight;
    window.printWeight = printWeight;
})(jQuery);
</script>
