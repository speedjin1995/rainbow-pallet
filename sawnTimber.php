<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
function msg($code, $fallback) {
    global $languageArray, $language;
    return isset($languageArray[$code][$language]) && $languageArray[$code][$language] !== '' ? $languageArray[$code][$language] : $fallback;
}
?>
<head>
    <title><?=msg('sawn_timber_code', 'Sawn Timber')?> | Synctronix - Weighing System</title>
    <?php include 'layouts/title-meta.php'; ?>
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <?php include 'layouts/head-css.php'; ?>
    <style>
        .sawn-table thead th {
            background: var(--vz-primary, #405189);
            color: #ffffff;
            border-color: var(--vz-primary, #405189);
            font-weight: 600;
            letter-spacing: 0;
            white-space: nowrap;
        }
        .sawn-table tbody tr.summary-row:hover td {
            background: rgba(64, 81, 137, 0.06);
        }
        .sawn-action-cell {
            white-space: nowrap;
            text-align: center;
        }
        .detail-panel {
            background: var(--vz-light, #f3f6f9);
            border: 1px solid var(--vz-border-color, #e9ebec);
            border-radius: 6px;
            padding: 12px;
        }
        .detail-table thead th {
            background: var(--vz-secondary, #74788d);
            color: #ffffff;
            border-color: var(--vz-secondary, #74788d);
            font-weight: 600;
            white-space: nowrap;
        }
        .detail-table tbody tr:nth-child(even) td {
            background: var(--vz-light, #f3f6f9);
        }
        .detail-table th, .detail-table td { vertical-align: middle; }
        .detail-table input, .detail-table select { min-width: 100px; }
        .detail-table .species-column { min-width: 260px; width: 32%; }
        .detail-table .species-select { min-width: 190px; }
        .summary-row { cursor: pointer; }
        .upload-preview-table thead th {
            background: var(--vz-secondary, #74788d);
            color: #ffffff;
            border-color: var(--vz-secondary, #74788d);
            white-space: nowrap;
        }
    </style>
</head>

<?php include 'layouts/body.php'; ?>
<div class="loading" id="spinnerLoading" style="display:none"><div class='mdi mdi-loading' style='transform:scale(0.79);'><div></div></div></div>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h5 class="card-title mb-0"><?=msg('sawn_timber_code', 'Sawn Timber')?></h5>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-outline-secondary" href="php/modules/sawnTimber/exportSawnTimber.php?template=1"><?=msg('download_template_code', 'Download Template')?></a>
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><?=msg('upload_excel_code', 'Upload Excel')?></button>
                                    <button class="btn btn-primary" id="addEntry"><?=msg('add_new_entry_code', 'Add New Entry')?></button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-2">
                                        <label class="form-label"><?=msg('from_date_code', 'From Date')?></label>
                                        <input type="date" class="form-control" id="fromDate">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><?=msg('to_date_code', 'To Date')?></label>
                                        <input type="date" class="form-control" id="toDate">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><?=msg('transaction_id_code', 'Transaction ID')?></label>
                                        <input type="text" class="form-control" id="filterTransactionId">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><?=msg('supplier_code', 'Supplier')?></label>
                                        <select class="form-control option-supplier" id="filterSupplier"><option value=""><?=msg('all_code', 'All')?></option></select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><?=msg('lot_code', 'Lot')?></label>
                                        <input type="text" class="form-control" id="filterLot">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label"><?=msg('species_code', 'Species')?></label>
                                        <select class="form-control option-species" id="filterSpecies"><option value=""><?=msg('all_code', 'All')?></option></select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label"><?=msg('remarks_code', 'Remarks')?></label>
                                        <input type="text" class="form-control" id="filterRemarks">
                                    </div>
                                    <div class="col-md-8 d-flex align-items-end justify-content-end gap-2">
                                        <button class="btn btn-success" id="filterBtn"><?=msg('filter_code', 'Filter')?></button>
                                        <button class="btn btn-light" id="clearBtn"><?=msg('clear_all_code', 'Clear All')?></button>
                                        <button class="btn btn-info" id="exportBtn"><?=msg('export_excel_code', 'Export Excel')?></button>
                                    </div>
                                </div>
                                <table id="sawnTimberTable" class="table table-bordered table-striped align-middle sawn-table" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th><?=msg('transaction_id_code', 'Transaction ID')?></th>
                                            <th><?=msg('transaction_date_code', 'Trans Date')?></th>
                                            <th><?=msg('supplier_code', 'Supplier')?></th>
                                            <th><?=msg('lot_code', 'Lot')?></th>
                                            <th><?=msg('total_pcs_code', 'Total Pcs')?></th>
                                            <th><?=msg('total_tons_code', 'Total Tons')?></th>
                                            <th><?=msg('remarks_code', 'Remarks')?></th>
                                            <th><?=msg('action_code', 'Action')?></th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="modal fade" id="entryModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?=msg('sawn_timber_code', 'Sawn Timber')?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="entryForm" autocomplete="off">
                    <input type="hidden" id="id" name="id">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label"><?=msg('transaction_id_code', 'Transaction ID')?></label>
                            <input type="text" class="form-control" id="transactionId" name="transactionId" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?=msg('transaction_date_code', 'Transaction Date')?></label>
                            <input type="datetime-local" class="form-control" id="transactionDate" name="transactionDate" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?=msg('supplier_code', 'Supplier')?></label>
                            <select class="form-control option-supplier" id="supplier" name="supplier" required></select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?=msg('lot_code', 'Lot')?></label>
                            <input type="text" class="form-control" id="lot" name="lot" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><?=msg('bundle_code', 'Bundle')?></label>
                            <input type="text" class="form-control" id="bundle" name="bundle" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label"><?=msg('remarks_code', 'Remarks')?></label>
                            <input type="text" class="form-control" id="remarks" name="remarks">
                        </div>
                    </div>
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered detail-table" id="detailTable">
                            <thead>
                                <tr>
                                    <th class="species-column"><?=msg('species_code', 'Species')?></th>
                                    <th><?=msg('thick_code', 'Thick')?></th>
                                    <th><?=msg('width_code', 'Width')?></th>
                                    <th><?=msg('length_code', 'Length')?></th>
                                    <th><?=msg('pieces_code', 'Pieces')?></th>
                                    <th><?=msg('tons_code', 'Tons')?></th>
                                    <th><?=msg('action_code', 'Action')?></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="addDetailRow"><?=msg('add_code', 'Add')?></button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=msg('close_code', 'Close')?></button>
                <button type="button" class="btn btn-success" id="saveEntry"><?=msg('submit_code', 'Submit')?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?=msg('upload_excel_code', 'Upload Excel')?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="file" class="form-control" id="fileInput" accept=".xlsx,.xls,.csv">
                <button type="button" class="btn btn-outline-primary mt-3" id="previewButton"><?=msg('preview_data_code', 'Preview Data')?></button>
                <div id="previewTable" class="table-responsive mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=msg('close_code', 'Close')?></button>
                <button type="button" class="btn btn-success" id="submitUpload"><?=msg('submit_code', 'Submit')?></button>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/customizer.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>

<script>
var table;
var optionData = {suppliers: [], species: []};

function htmlEscape(value) {
    return $('<div>').text(value == null ? '' : value).html();
}

function formatDateTimeForInput(value) {
    if (!value) return '';
    return value.replace(' ', 'T').substring(0, 16);
}

function excelDateToMysql(value) {
    if (typeof value !== 'number') return value;
    var date = XLSX.SSF.parse_date_code(value);
    if (!date) return value;
    return date.y + '-' + String(date.m).padStart(2, '0') + '-' + String(date.d).padStart(2, '0') + ' ' + String(date.H || 0).padStart(2, '0') + ':' + String(date.M || 0).padStart(2, '0') + ':' + String(date.S || 0).padStart(2, '0');
}

function calculateTons(row) {
    var thick = parseFloat(row.find('.thick').val()) || 0;
    var width = parseFloat(row.find('.width').val()) || 0;
    var length = parseFloat(row.find('.length').val()) || 0;
    var pieces = parseFloat(row.find('.pieces').val()) || 0;
    row.find('.tons').val(((thick * width * length * pieces) / 7200).toFixed(4));
}

function fillSelect(selector, items, valueKey, codeKey, selected, includeAll) {
    var html = includeAll ? '<option value=""><?=msg('all_code', 'All')?></option>' : '<option value="" selected disabled hidden><?=msg('please_select_code', 'Please Select')?></option>';
    items.forEach(function(item) {
        var text = item[valueKey];
        var value = item[valueKey];
        html += '<option value="' + htmlEscape(value) + '">' + htmlEscape(text) + '</option>';
    });
    $(selector).html(html);
    if (selected !== undefined && selected !== null) {
        $(selector).val(selected);
    }
}

function loadOptions(callback) {
    $.getJSON('php/modules/sawnTimber/options.php', function(data) {
        optionData = data.message || {suppliers: [], species: []};
        fillSelect('#filterSupplier', optionData.suppliers, 'value', null, $('#filterSupplier').val(), true);
        fillSelect('#filterSpecies', optionData.species, 'value', 'code', $('#filterSpecies').val(), true);
        fillSelect('#supplier', optionData.suppliers, 'value', null, $('#supplier').val(), false);
        $('.species-select').each(function() {
            var selected = $(this).val();
            fillSelect(this, optionData.species, 'value', 'code', selected, false);
        });
        if (callback) callback();
    });
}

function addDetailRow(detail) {
    detail = detail || {};
    var row = $('<tr>' +
        '<td class="species-column"><select class="form-control species-select" required></select></td>' +
        '<td><input type="number" step="0.0001" class="form-control thick" value="' + htmlEscape(detail.thick || '') + '"></td>' +
        '<td><input type="number" step="0.0001" class="form-control width" value="' + htmlEscape(detail.width || '') + '"></td>' +
        '<td><input type="number" step="0.0001" class="form-control length" value="' + htmlEscape(detail.length || '') + '"></td>' +
        '<td><input type="number" step="1" class="form-control pieces" value="' + htmlEscape(detail.pieces || '') + '"></td>' +
        '<td><input type="number" step="0.0001" class="form-control tons" readonly value="' + htmlEscape(detail.tons || '0.0000') + '"></td>' +
        '<td><button type="button" class="btn btn-sm btn-danger remove-detail">x</button></td>' +
        '</tr>');
    $('#detailTable tbody').append(row);
    fillSelect(row.find('.species-select'), optionData.species, 'value', 'code', detail.species, false);
}

function detailHtml(details) {
    var html = '<div class="detail-panel"><div class="table-responsive"><table class="table table-sm table-bordered detail-table mb-0"><thead><tr>' +
        '<th><?=msg('bundle_code', 'Bundle')?></th><th><?=msg('species_code', 'Species')?></th><th><?=msg('thick_code', 'Thick')?></th><th><?=msg('width_code', 'Width')?></th><th><?=msg('length_code', 'Length')?></th><th><?=msg('pieces_code', 'Pieces')?></th><th><?=msg('tons_code', 'Tons')?></th>' +
        '</tr></thead><tbody>';
    details.forEach(function(row) {
        html += '<tr><td>' + htmlEscape(row.bundle || '') + '</td><td>' + htmlEscape(row.species || '') + '</td><td>' + htmlEscape(row.thick) + '</td><td>' + htmlEscape(row.width) + '</td><td>' + htmlEscape(row.length) + '</td><td>' + htmlEscape(row.pieces) + '</td><td>' + htmlEscape(row.tons) + '</td></tr>';
    });
    return html + '</tbody></table></div></div>';
}

function collectDetails() {
    var details = [];
    $('#detailTable tbody tr').each(function() {
        var speciesSelect = $(this).find('.species-select');
        details.push({
            species: speciesSelect.val(),
            thick: $(this).find('.thick').val(),
            width: $(this).find('.width').val(),
            length: $(this).find('.length').val(),
            pieces: $(this).find('.pieces').val()
        });
    });
    return details;
}

function openEntry(record) {
    $('#entryForm')[0].reset();
    $('#id').val(record ? record.id : '');
    $('#transactionId').val(record ? record.transaction_id : '');
    $('#transactionDate').val(record ? formatDateTimeForInput(record.transaction_date) : formatDateTimeForInput(new Date().toISOString().slice(0, 19).replace('T', ' ')));
    $('#bundle').val(record ? record.bundle : '');
    $('#remarks').val(record ? record.remarks : '');
    $('#detailTable tbody').empty();
    loadOptions(function() {
        $('#supplier').val(record ? record.supplier : '');
        $('#lot').val(record ? record.lot : '');
        if (record && record.details && record.details.length) {
            record.details.forEach(addDetailRow);
        } else {
            addDetailRow();
        }
        $('#entryModal').modal('show');
    });
}

function editRecord(id) {
    $('#spinnerLoading').show();
    $.post('php/modules/sawnTimber/getSawnTimber.php', {id: id}, function(data) {
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

function deleteRecord(id) {
    if (!confirm('Are you sure you want to delete this record?')) return;
    $.post('php/modules/sawnTimber/deleteSawnTimber.php', {id: id}, function(data) {
        var obj = JSON.parse(data);
        if (obj.status === 'success') {
            table.ajax.reload();
            toastr.success(obj.message);
        } else {
            toastr.error(obj.message);
        }
    });
}

function exportExcel() {
    var params = $.param({
        fromDate: $('#fromDate').val(),
        toDate: $('#toDate').val(),
        transactionId: $('#filterTransactionId').val(),
        supplier: $('#filterSupplier').val(),
        lot: $('#filterLot').val(),
        species: $('#filterSpecies').val(),
        remarks: $('#filterRemarks').val()
    });
    window.location = 'php/modules/sawnTimber/exportSawnTimber.php?' + params;
}

$(document).ready(function() {
    loadOptions();

    table = $('#sawnTimberTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        responsive: true,
        ajax: {
            url: 'php/modules/sawnTimber/loadSawnTimber.php',
            type: 'POST',
            data: function(d) {
                d.fromDate = $('#fromDate').val();
                d.toDate = $('#toDate').val();
                d.transactionId = $('#filterTransactionId').val();
                d.supplier = $('#filterSupplier').val();
                d.lot = $('#filterLot').val();
                d.species = $('#filterSpecies').val();
                d.remarks = $('#filterRemarks').val();
            }
        },
        columns: [
            { data: 'transaction_id' },
            { data: 'transaction_date' },
            { data: 'supplier' },
            { data: 'lot' },
            { data: 'total_pieces' },
            { data: 'total_tons' },
            { data: 'remarks' },
            { data: 'id', orderable: false, className: 'sawn-action-cell', render: function(data) {
                return '<button class="btn btn-sm btn-warning me-1" onclick="editRecord(\'' + data + '\')"><i class="ri-edit-line"></i></button>' +
                    '<button class="btn btn-sm btn-danger" onclick="deleteRecord(\'' + data + '\')"><i class="ri-delete-bin-line"></i></button>';
            }}
        ],
        createdRow: function(row) {
            $(row).addClass('summary-row');
        }
    });

    $('#sawnTimberTable tbody').on('click', 'tr.summary-row td:not(.sawn-action-cell)', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);
        if (row.child.isShown()) {
            row.child.hide();
            return;
        }
        $.post('php/modules/sawnTimber/getSawnTimber.php', {id: row.data().id}, function(data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                var detailRows = obj.message.details.map(function(detail) {
                    detail.bundle = obj.message.header.bundle;
                    return detail;
                });
                row.child(detailHtml(detailRows)).show();
            } else {
                toastr.error(obj.message);
            }
        });
    });

    $('#filterBtn').on('click', function() { table.ajax.reload(); });
    $('#clearBtn').on('click', function() {
        $('#fromDate,#toDate,#filterTransactionId,#filterRemarks').val('');
        $('#filterSupplier,#filterSpecies').val('');
        table.ajax.reload();
    });
    $('#exportBtn').on('click', exportExcel);
    $('#addEntry').on('click', function() { openEntry(null); });
    $('#addDetailRow').on('click', function() { addDetailRow(); });
    $('#detailTable').on('click', '.remove-detail', function() { $(this).closest('tr').remove(); });
    $('#detailTable').on('input', '.thick,.width,.length,.pieces', function() { calculateTons($(this).closest('tr')); });

    $('#saveEntry').on('click', function() {
        $('#detailTable tbody tr').each(function() { calculateTons($(this)); });
        $.post('php/modules/sawnTimber/saveSawnTimber.php', {
            id: $('#id').val(),
            transactionId: $('#transactionId').val(),
            transactionDate: $('#transactionDate').val().replace('T', ' '),
            supplier: $('#supplier').val(),
            lot: $('#lot').val(),
            bundle: $('#bundle').val(),
            remarks: $('#remarks').val(),
            details: JSON.stringify(collectDetails())
        }, function(data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                $('#entryModal').modal('hide');
                table.ajax.reload();
                toastr.success(obj.message);
            } else {
                toastr.error(obj.message);
            }
        });
    });

    $('#previewButton').on('click', function() {
        var file = document.getElementById('fileInput').files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            var workbook = XLSX.read(e.target.result, { type: 'binary' });
            var sheet = workbook.Sheets[workbook.SheetNames[0]];
            var rows = XLSX.utils.sheet_to_json(sheet, { defval: '' });
            rows = rows.map(function(row) {
                if (row.TransactionDate !== undefined) {
                    row.TransactionDate = excelDateToMysql(row.TransactionDate);
                }
                return row;
            });
            window.sawnTimberUploadRows = rows;
            var html = '<table class="table table-bordered table-sm upload-preview-table"><thead><tr>';
            Object.keys(rows[0] || {}).forEach(function(header) { html += '<th>' + htmlEscape(header) + '</th>'; });
            html += '</tr></thead><tbody>';
            rows.forEach(function(row) {
                html += '<tr>';
                Object.keys(row).forEach(function(key) { html += '<td>' + htmlEscape(row[key]) + '</td>'; });
                html += '</tr>';
            });
            $('#previewTable').html(html + '</tbody></table>');
        };
        reader.readAsBinaryString(file);
    });

    $('#submitUpload').on('click', function() {
        $.ajax({
            url: 'php/modules/sawnTimber/uploadSawnTimber.php',
            type: 'POST',
            data: JSON.stringify(window.sawnTimberUploadRows || []),
            contentType: 'application/json',
            success: function(data) {
                var obj = JSON.parse(data);
                if (obj.status === 'success') {
                    $('#uploadModal').modal('hide');
                    loadOptions(function() { table.ajax.reload(); });
                    toastr.success(obj.message);
                } else {
                    toastr.error(obj.message);
                }
            }
        });
    });
});
</script>
</body>
</html>
