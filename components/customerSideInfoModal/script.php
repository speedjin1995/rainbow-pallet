<?php
// Customer side info modal component script (#customerSideInfoModal).
// Include after jQuery and components/customerSideInfoModal/modal.php.
//
// Public functions:
//   initCustomerSideInfoModal({ onSaved: function(obj){} })  - page decides what happens after a save
//   openCustomerSideInfo(id)                                   - load a weighing's customer side info and open the modal
?>
<script type="text/javascript">
(function ($) {
    // Guard against the component being included twice on a page
    if (window.initCustomerSideInfoModal) {
        return;
    }

    var WEIGHING_URL = 'php/modules/weighing/index.php';
    var settings = { onSaved: null };

    $(function () {
        inModal('.cust-side-weight').on('input', function(){
            updateNettWeight();
        });

        inModal('#submitCustomerSideInfo').on('click', function(){
            saveCustomerSideInfo();
        });
    });

    function initCustomerSideInfoModal(options) {
        settings = $.extend(settings, options || {});
    }

    function openCustomerSideInfo(id) {
        inModal('#customerSideInfoForm')[0].reset();
        inModal('#customerSideInfoId').val(id);
        inModal('#custSideNettWeight').val('');

        $.post(WEIGHING_URL, { id: id, action: 'customerSideInfo', custAction: 'get' }, function(data){
            var obj = JSON.parse(data);

            if (obj.status === 'success'){
                inModal('#custSideDoNo').val(obj.message.cust_side_do_no);
                inModal('#custSideFirstWeight').val(obj.message.cust_side_first_weight);
                inModal('#custSideSecondWeight').val(obj.message.cust_side_second_weight);
                inModal('#custSideMc').val(obj.message.cust_side_mc);
                inModal('#custSideNettWeight').val(obj.message.cust_side_nett_weight);
                updateNettWeight();
                $('#customerSideInfoModal').modal('show');
            }
            else {
                notify('#failBtn', obj.message);
            }
        });
    }

    function saveCustomerSideInfo() {
        $('#spinnerLoading').show();

        $.post(WEIGHING_URL, inModal('#customerSideInfoForm').serialize() + '&action=customerSideInfo&custAction=save', function(data){
            var obj = JSON.parse(data);
            $('#spinnerLoading').hide();

            if (obj.status === 'success'){
                // Let the including page refresh its own table
                if (typeof settings.onSaved === 'function') {
                    settings.onSaved(obj);
                }
                $('#customerSideInfoModal').modal('hide');
                notify('#successBtn', obj.message);
            }
            else {
                notify('#failBtn', obj.message);
            }
        });
    }

    // Nett weight = difference between the customer's first and second weights
    function updateNettWeight() {
        var firstWeight = inModal('#custSideFirstWeight').val();
        var secondWeight = inModal('#custSideSecondWeight').val();

        if (firstWeight !== '' && secondWeight !== '') {
            var nettWeight = Math.abs(parseFloat(firstWeight) - parseFloat(secondWeight));
            inModal('#custSideNettWeight').val(isNaN(nettWeight) ? '' : nettWeight.toFixed(0));
        } else {
            inModal('#custSideNettWeight').val('');
        }
    }

    // Find elements inside the customer side info modal
    function inModal(selector) {
        return $('#customerSideInfoModal').find(selector);
    }

    // Legacy toast triggers
    function notify(button, message) {
        $(button).attr('data-toast-text', message);
        $(button).click();
    }

    window.initCustomerSideInfoModal = initCustomerSideInfoModal;
    window.openCustomerSideInfo = openCustomerSideInfo;
})(jQuery);
</script>
