<?php
// Transfer to port info modal component script (#trxPortModal).
// Include after jQuery, Select2 and components/trxPortModal/modal.php.
//
// Public functions:
//   initTrxPortModal({ onSaved: function(obj){} })  - page decides what happens after a save
//   openTrxPort(id, isContainer)                    - load a weighing's port info and open the modal
//                                                     (isContainer 'Y' = Weight_Container record)
?>
<script type="text/javascript">
(function ($) {
    // Guard against the component being included twice on a page
    if (window.initTrxPortModal) {
        return;
    }

    var WEIGHING_URL = 'php/modules/weighing/index.php';
    var settings = { onSaved: null };

    $(function () {
        inModal('#portLocation').select2({
            allowClear: true,
            placeholder: "Please Select",
            dropdownParent: $('#trxPortModal') // Ensures dropdown is not cut off
        });

        inModal('#submitTrxPort').on('click', function(){
            saveTrxPort();
        });
    });

    function initTrxPortModal(options) {
        settings = $.extend(settings, options || {});
    }

    function openTrxPort(id, isContainer) {
        inModal('#trxPortForm')[0].reset();
        inModal('#trxPortId').val(id);
        inModal('#trxPortIsContainer').val(isContainer === 'Y' ? 'Y' : 'N');

        $.post(WEIGHING_URL, { id: id, isContainer: isContainer, action: 'trxPort', trxPortAction: 'get' }, function(data){
            var obj = JSON.parse(data);

            if (obj.status === 'success'){
                fillLocations(obj.message.destinations, obj.message.port_location);
                inModal('#portSpQty').val(obj.message.port_sp_qty);
                inModal('#portRefNo').val(obj.message.port_ref_no);
                $('#trxPortModal').modal('show');
            }
            else {
                notify('#failBtn', obj.message);
            }
        });
    }

    function saveTrxPort() {
        $('#spinnerLoading').show();

        $.post(WEIGHING_URL, inModal('#trxPortForm').serialize() + '&action=trxPort&trxPortAction=save', function(data){
            var obj = JSON.parse(data);
            $('#spinnerLoading').hide();

            if (obj.status === 'success'){
                // Let the including page refresh its own table
                if (typeof settings.onSaved === 'function') {
                    settings.onSaved(obj);
                }
                $('#trxPortModal').modal('hide');
                notify('#successBtn', obj.message);
            }
            else {
                notify('#failBtn', obj.message);
            }
        });
    }

    // Location dropdown = the record company's destinations
    function fillLocations(items, selected) {
        var $sel = inModal('#portLocation');
        $sel.empty().append('<option value="">-</option>');
        $.each(items || [], function(i, item) {
            $sel.append($('<option>').val(item.name).text(item.name));
        });
        $sel.val(selected || '').trigger('change');
    }

    // Find elements inside the transfer to port modal
    function inModal(selector) {
        return $('#trxPortModal').find(selector);
    }

    // Legacy toast triggers
    function notify(button, message) {
        $(button).attr('data-toast-text', message);
        $(button).click();
    }

    window.initTrxPortModal = initTrxPortModal;
    window.openTrxPort = openTrxPort;
})(jQuery);
</script>
