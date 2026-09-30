<?php
// Sawn timber modal component script (#sawnTimberModal).
// Include after jQuery, jquery-validate, Select2 and flatpickr,
// and after components/sawnTimberModal/data.php + modal.php.
// Only include it for users who may create/edit sawn timber - the save endpoint is the Sawn Timber module.
//
// Public functions:
//   initSawnTimberModal({ onSaved: function(obj){} })  - page decides what happens after a save
//   openSawnTimberNew()                                - open the modal for a new sawn timber record
//   editSawnTimber(id)                                 - load an existing sawn timber record and open the modal
?>
<script type="text/javascript">
(function ($) {
    // Guard against the component being included twice on a page
    if (window.initSawnTimberModal) {
        return;
    }

    var SAWN_TIMBER_URL = 'php/modules/sawnTimber/index.php';
    var DETAIL_FIELDS = ['species', 'lot', 'bundle', 'thick', 'width', 'length', 'pieces', 'tons', 'kdCharges', 'bundlingCharges', 'graderFees'];
    var settings = { onSaved: null };
    var detailRowCount = 0;

    $(function () {
        initModalSelect2();

        inModal('#sawnTimberDate').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: new Date()
        });

        inModal('#sawnTimberForm').validate({
            errorPlacement: function (error, element) {
                // Don't append - use existing invalid-feedback divs
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            }
        });

        inModal('#stAddDetail').on('click', function() {
            addDetailRow();
        });

        // Card UI events
        inModal('#stDetailCardsContainer').on('click', '.card-toggle', function() {
            var $card = $(this).closest('.detail-card');
            var $body = $card.find('.card-collapse-body');
            var $icon = $(this).find('.collapse-icon');
            $body.slideToggle(150);
            $icon.toggleClass('ri-arrow-down-s-line ri-arrow-right-s-line');
            updateCardSummary($card);
        });
        inModal('#stDetailCardsContainer').on('click', '.remove-card', function(e) {
            e.preventDefault();
            var $card = $(this).closest('.detail-card');
            detailRowFor($card).remove();
            $card.remove();
            updateEmptyState();
            renumberCards();
        });
        inModal('#stDetailCardsContainer').on('input', '.card-thick,.card-width,.card-length,.card-pieces', function() {
            var $card = $(this).closest('.detail-card');
            syncCardToRow($card);
            var $row = detailRowFor($card);
            calculateTons($row);
            $card.find('.card-tons').val($row.find('.tons').val());
            updateTotals();
        });
        inModal('#stDetailCardsContainer').on('input', '.card-species,.card-lot,.card-bundle,.card-kdCharges,.card-bundlingCharges,.card-graderFees', function() {
            var $card = $(this).closest('.detail-card');
            syncCardToRow($card);
            updateTotals();
        });

        // Reload transaction list when company or plant changes
        inModal('#stCompanyId, #stPlantId').on('change', function() {
            loadSawnTimberWeighing();
        });

        // Fill the read-only weighing fields from the selected transaction
        inModal('#stWeightId').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                inModal('#stTransactionId').val(selectedOption.data('transaction-id'));
                inModal('#stTransactionStatus').val(selectedOption.data('transaction-status'));
                var customer = selectedOption.data('customer-name') || '';
                var supplier = selectedOption.data('supplier-name') || '';
                inModal('#stCustomerSupplier').val(customer || supplier);
                inModal('#stDeliveredTo').val(selectedOption.data('destination') || '');
                inModal('#stLorryNo').val(selectedOption.data('lorry-no') || '');
                inModal('#stDoNo').val(selectedOption.data('do-no') || '');
                var transDate = selectedOption.data('transaction-date');
                if (transDate) {
                    var d = new Date(transDate);
                    inModal('#stTransactionDate').val(formatDate(d));
                }
            } else {
                inModal('#stTransactionId').val('');
                inModal('#stTransactionStatus').val('');
                inModal('#stTransactionDate').val('');
                inModal('#stCustomerSupplier').val('');
                inModal('#stDeliveredTo').val('');
                inModal('#stLorryNo').val('');
                inModal('#stDoNo').val('');
            }
        });

        inModal('#saveSawnTimber').on('click', function() {
            saveSawnTimber();
        });
    });

    // =========================================================================
    // Public API
    // =========================================================================
    function initSawnTimberModal(options) {
        settings = $.extend(settings, options || {});
    }

    function openSawnTimberNew() {
        var $form = inModal('#sawnTimberForm');
        $form[0].reset();
        $form.removeClass('was-validated');
        $form.find('.is-invalid').removeClass('is-invalid');
        inModal('#stCompanyId, #stPlantId').trigger('change.select2');
        inModal('#stWeightId').val('').trigger('change');
        inModal('#stId').val('');
        clearDetails();
        loadSawnTimberWeighing();

        // Record date defaults to today
        inModal('#sawnTimberDate').val(formatDate(new Date()));

        $('#sawnTimberModal').modal('show');
    }

    function editSawnTimber(id) {
        $('#spinnerLoading').show();
        $.post(SAWN_TIMBER_URL + '?action=get', {id: id}, function(data) {
            $('#spinnerLoading').hide();
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                var record = obj.message.header;
                record.details = obj.message.details;
                openEntry(record);
            } else {
                toastr.error(obj.message);
            }
        });
    }

    // =========================================================================
    // Form
    // =========================================================================
    function openEntry(record) {
        record = record || {};
        inModal('#sawnTimberForm')[0].reset();
        clearDetails();

        inModal('#stId').val(record.id || '');
        inModal('#stCompanyId').val(record.company_id || '').trigger('change.select2');
        inModal('#stPlantId').val(record.plant_id || '').trigger('change.select2');
        inModal('#stTransactionId').val(record.transaction_id || '');
        inModal('#stTransactionStatus').val(record.transaction_status || '');
        inModal('#stTransactionDate').val(record.transaction_date ? record.transaction_date.split(' ')[0].split('-').reverse().join('-') : '');
        inModal('#stCustomerSupplier').val(record.customer_name || record.supplier_name || '');
        inModal('#stDeliveredTo').val(record.destination || '');
        inModal('#stLorryNo').val(record.lorry_plate_no1 || '');
        inModal('#stDoNo').val(record.delivery_no || '');
        inModal('#sawnTimberDate').val(record.record_date ? record.record_date.split(' ')[0].split('-').reverse().join('-') : '');
        inModal('#stRemarks').val(record.remarks || '');

        // Set weightId dropdown - add option if not exists
        if (record.weight_id) {
            if (inModal('#stWeightId option[value="' + record.weight_id + '"]').length === 0) {
                inModal('#stWeightId').append('<option value="' + record.weight_id + '">' + record.transaction_id + '</option>');
            }
            inModal('#stWeightId').val(record.weight_id);
        }

        if (record.details && record.details.length > 0) {
            for (var i = 0; i < record.details.length; i++) {
                addDetailRow(record.details[i]);
            }
        }

        updateEmptyState();
        $('#sawnTimberModal').modal('show');
    }

    function saveSawnTimber() {
        if (!inModal('#sawnTimberForm').valid()) {
            return;
        }

        inModal('#stDetailTable tbody tr').each(function() { calculateTons($(this)); });
        $.post(SAWN_TIMBER_URL + '?action=save', inModal('#sawnTimberForm').serialize(), function(data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                $('#sawnTimberModal').modal('hide');
                // Let the including page refresh its own table
                if (typeof settings.onSaved === 'function') {
                    settings.onSaved(obj);
                }
                toastr.success(obj.message);
            } else {
                toastr.error(obj.message);
            }
        });
    }

    function loadSawnTimberWeighing() {
        $.post(SAWN_TIMBER_URL + '?action=getWeighing', {
            company: inModal('#stCompanyId').val() || '',
            plant: inModal('#stPlantId').val() || ''
        }, function(data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                var options = '<option value="">-</option>';
                obj.data.forEach(function(item) {
                    options += '<option value="' + item.id + '"' +
                        ' data-transaction-id="' + item.transaction_id + '"' +
                        ' data-transaction-status="' + item.transaction_status + '"' +
                        ' data-customer-name="' + (item.customer_name || '') + '"' +
                        ' data-supplier-name="' + (item.supplier_name || '') + '"' +
                        ' data-destination="' + (item.destination || '') + '"' +
                        ' data-lorry-no="' + (item.lorry_plate_no1 || '') + '"' +
                        ' data-do-no="' + (item.delivery_no || '') + '"' +
                        ' data-transaction-date="' + (item.transaction_date || '') + '"' +
                        ' data-company-id="' + (item.company_id || '') + '"' +
                        ' data-company-name="' + (item.company_name || '') + '"' +
                        ' data-plant-id="' + (item.plant_id || '') + '"' +
                        ' data-plant-display="' + (item.plant_display || '') + '"' +
                        '>' + item.transaction_id + '</option>';
                });
                inModal('#stWeightId').html(options).trigger('change');
            } else {
                toastr.error(obj.message);
            }
        });
    }

    // =========================================================================
    // Details (visible cards + hidden rows that carry the posted field names)
    // =========================================================================
    function addDetailRow(detail) {
        detail = detail || {};
        var currentIndex = detailRowCount;
        var values = {
            species: detail.species || '',
            lot: detail.lot || '',
            bundle: detail.bundle || '',
            thick: detail.thick || '',
            width: detail.width || '',
            length: detail.length || '',
            pieces: detail.pieces || '',
            tons: detail.tons || '0.0000',
            kdCharges: detail.kd_charges || '',
            bundlingCharges: detail.bundling_charges || '',
            graderFees: detail.grader_fees || ''
        };

        // Hidden table row for form serialization
        var $row = $($('#stDetailRowTemplate').html());
        $row.attr('data-index', currentIndex);
        DETAIL_FIELDS.forEach(function(field) {
            $row.find('[data-field="' + field + '"]').attr('name', field + '[' + currentIndex + ']').val(values[field]);
        });
        inModal('#stDetailTable tbody').append($row);

        // Visible card UI
        var cardHtml = $('#stDetailCardTemplate').html()
            .replace(/{INDEX}/g, currentIndex)
            .replace(/{NUMBER}/g, currentIndex + 1);
        inModal('#stDetailCardsContainer').append(cardHtml);

        var $card = inModal('#stDetailCardsContainer .detail-card[data-card-index="' + currentIndex + '"]');
        DETAIL_FIELDS.forEach(function(field) {
            $card.find('.card-' + field).val(values[field]);
        });

        detailRowCount++;
        updateEmptyState();
        renumberCards();
    }

    function clearDetails() {
        inModal('#stDetailTable tbody').html('');
        inModal('#stDetailCardsContainer .detail-card').remove();
        detailRowCount = 0;
        updateEmptyState();
    }

    function detailRowFor($card) {
        return inModal('#stDetailTable tbody tr[data-index="' + $card.data('card-index') + '"]');
    }

    function calculateTons(row) {
        var thick = parseFloat(row.find('.thick').val()) || 0;
        var width = parseFloat(row.find('.width').val()) || 0;
        var length = parseFloat(row.find('.length').val()) || 0;
        var pieces = parseFloat(row.find('.pieces').val()) || 0;
        row.find('.tons').val(((thick * width * length * pieces) / 7200).toFixed(4));
    }

    function syncCardToRow($card) {
        var $row = detailRowFor($card);
        DETAIL_FIELDS.forEach(function(field) {
            if (field !== 'tons') {
                $row.find('.' + field).val($card.find('.card-' + field).val());
            }
        });
    }

    function updateEmptyState() {
        var cardCount = inModal('#stDetailCardsContainer .detail-card').length;
        if (cardCount === 0) {
            inModal('#stEmptyDetailState').show();
            inModal('#stDetailTotalsFooter').hide();
        } else {
            inModal('#stEmptyDetailState').hide();
            inModal('#stDetailTotalsFooter').show();
        }
        updateTotals();
    }

    function updateTotals() {
        var totalTons = 0, totalKd = 0, totalBundling = 0, totalGrader = 0;
        inModal('#stDetailCardsContainer .detail-card').each(function() {
            totalTons += parseFloat($(this).find('.card-tons').val()) || 0;
            totalKd += parseFloat($(this).find('.card-kdCharges').val()) || 0;
            totalBundling += parseFloat($(this).find('.card-bundlingCharges').val()) || 0;
            totalGrader += parseFloat($(this).find('.card-graderFees').val()) || 0;
        });
        inModal('#stTotalTons').text(totalTons.toFixed(4));
        inModal('#stTotalKdCharges').text(totalKd.toFixed(2));
        inModal('#stTotalBundlingCharges').text(totalBundling.toFixed(2));
        inModal('#stTotalGraderFees').text(totalGrader.toFixed(2));
    }

    function renumberCards() {
        inModal('#stDetailCardsContainer .detail-card').each(function(i) {
            var $card = $(this);
            var summary = $card.find('.card-summary').html();
            var iconClass = $card.find('.collapse-icon').hasClass('ri-arrow-right-s-line') ? 'ri-arrow-right-s-line' : 'ri-arrow-down-s-line';
            $card.find('.card-number').html('<i class="' + iconClass + ' me-1 collapse-icon"></i>Item #' + (i + 1) + ' <span class="card-summary text-muted fw-normal">' + (summary || '') + '</span>');
        });
    }

    function updateCardSummary($card) {
        var species = $card.find('.card-species').val() || '';
        var tons = $card.find('.card-tons').val() || '0';
        var pcs = $card.find('.card-pieces').val() || '0';
        var summary = '';
        if (species || parseFloat(tons) > 0) {
            summary = '- ' + (species ? species + ', ' : '') + pcs + ' pcs, ' + tons + ' tons';
        }
        $card.find('.card-summary').html(summary);
    }

    // =========================================================================
    // Helpers
    // =========================================================================
    // Select2 for the modal's dropdowns - runs after the page's own Select2 setup
    function initModalSelect2() {
        inModal('.select2').select2({
            allowClear: true,
            placeholder: "Please Select",
            dropdownParent: inModal('.modal-body')
        });

        inModal('.select2-container .select2-selection--single').css({
            'padding-top': '4px',
            'padding-bottom': '4px',
            'height': 'auto'
        });

        inModal('.select2-container .select2-selection__arrow').css({
            'padding-top': '33px',
            'height': 'auto'
        });
    }

    // d-m-Y, the format the save endpoint parses
    function formatDate(d) {
        return ('0' + d.getDate()).slice(-2) + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + d.getFullYear();
    }

    // Find elements inside the sawn timber modal
    function inModal(selector) {
        return $('#sawnTimberModal').find(selector);
    }

    window.initSawnTimberModal = initSawnTimberModal;
    window.openSawnTimberNew = openSawnTimberNew;
    window.editSawnTimber = editSawnTimber;
})(jQuery);
</script>
