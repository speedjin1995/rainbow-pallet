<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
function speciesMsg($code, $fallback) {
    global $languageArray, $language;
    return isset($languageArray[$code][$language]) && $languageArray[$code][$language] !== '' ? $languageArray[$code][$language] : $fallback;
}
?>
<head>
    <title><?=speciesMsg('species_code', 'Species')?> | Synctronix - Weighing System</title>
    <?php include 'layouts/title-meta.php'; ?>
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>
    <?php include 'layouts/head-css.php'; ?>
</head>
<?php include 'layouts/body.php'; ?>
<div class="loading" id="spinnerLoading" style="display:none"><div class='mdi mdi-loading' style='transform:scale(0.79);'><div></div></div></div>
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><?=speciesMsg('species_code', 'Species')?></h5>
                        <button class="btn btn-primary" id="addSpecies"><?=speciesMsg('add_new_code', 'Add New')?></button>
                    </div>
                    <div class="card-body">
                        <table id="speciesTable" class="table table-bordered table-striped align-middle" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?=speciesMsg('species_code', 'Species')?></th>
                                    <th><?=speciesMsg('created_date_code', 'Created Date')?></th>
                                    <th><?=speciesMsg('action_code', 'Action')?></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<div class="modal fade" id="speciesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?=speciesMsg('species_code', 'Species')?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="speciesForm" autocomplete="off">
                    <input type="hidden" id="id">
                    <label class="form-label"><?=speciesMsg('species_code', 'Species')?></label>
                    <input type="text" class="form-control" id="name" required>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=speciesMsg('close_code', 'Close')?></button>
                <button type="button" class="btn btn-success" id="saveSpecies"><?=speciesMsg('submit_code', 'Submit')?></button>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/customizer.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script>
var speciesTable;

function editSpecies(id) {
    $.post('php/modules/sawnTimberSpecies/getSpecies.php', {id: id}, function(data) {
        var obj = JSON.parse(data);
        if (obj.status === 'success') {
            $('#id').val(obj.message.id);
            $('#name').val(obj.message.name);
            $('#speciesModal').modal('show');
        } else {
            toastr.error(obj.message);
        }
    });
}

function deleteSpecies(id) {
    if (!confirm('Are you sure you want to delete this species?')) return;
    $.post('php/modules/sawnTimberSpecies/deleteSpecies.php', {id: id}, function(data) {
        var obj = JSON.parse(data);
        if (obj.status === 'success') {
            speciesTable.ajax.reload();
            toastr.success(obj.message);
        } else {
            toastr.error(obj.message);
        }
    });
}

function saveSpecies() {
    $.post('php/modules/sawnTimberSpecies/species.php', {
        id: $('#id').val(),
        name: $('#name').val()
    }, function(data) {
        var obj = JSON.parse(data);
        if (obj.status === 'success') {
            $('#speciesModal').modal('hide');
            speciesTable.ajax.reload();
            toastr.success(obj.message);
        } else {
            toastr.error(obj.message);
        }
    });
}

$(document).ready(function() {
    speciesTable = $('#speciesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'php/modules/sawnTimberSpecies/loadSpecies.php',
            type: 'POST'
        },
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'created_date' },
            { data: 'id', orderable: false, render: function(data) {
                return '<button class="btn btn-sm btn-warning me-1" onclick="editSpecies(\'' + data + '\')"><i class="ri-edit-line"></i></button>' +
                    '<button class="btn btn-sm btn-danger" onclick="deleteSpecies(\'' + data + '\')"><i class="ri-delete-bin-line"></i></button>';
            }}
        ]
    });

    $('#addSpecies').on('click', function() {
        $('#id').val('');
        $('#name').val('');
        $('#speciesModal').modal('show');
    });
    $('#saveSpecies').on('click', saveSpecies);
    $('#speciesForm').on('submit', function(e) {
        e.preventDefault();
        saveSpecies();
    });
});
</script>
</body>
</html>
