<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
require_once "php/db_connect.php";
if (!hasModulePermission('User Management', 'Permission', ['view', 'create', 'edit'])){
    header('Location: no-permission.php');
    exit;
}

$modules = $db->query("SELECT id, name, category FROM modules ORDER BY category ASC, name ASC");
$grouped = array();
while($m = $modules->fetch_assoc()){
    $grouped[$m['category']][] = array('id' => $m['id'], 'name' => $m['name']);
}
?>

<head>
    <title>Permissions | PWS - Weighing System</title>
    <?php include 'layouts/title-meta.php'; ?>

    <link href="assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

    <?php include 'layouts/head-css.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div class="loading" id="spinnerLoading" style="display:none">
    <div class='mdi mdi-loading' style='transform:scale(0.79);'>
        <div></div>
    </div>
</div>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="h-100">

                            <button type="button" hidden id="successBtn" data-toast data-toast-text="" data-toast-gravity="top" data-toast-position="center" data-toast-duration="3000" data-toast-close="close" class="btn btn-light w-xs">Top Center</button>
                            <button type="button" hidden id="failBtn" data-toast data-toast-text="" data-toast-gravity="top" data-toast-position="center" data-toast-duration="3000" data-toast-close="close" class="btn btn-light w-xs">Top Center</button>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h5 class="card-title mb-0">Permission Records</h5>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <?php if(hasModulePermission('User Management', 'Permission', ['cancelled'])): ?>
                                                    <button type="button" id="multiDelete" class="btn btn-warning waves-effect waves-light">
                                                        <i class="fa-solid fa-ban align-middle me-1"></i>
                                                        Delete Permission
                                                    </button>
                                                    <?php endif; ?>

                                                    <?php if(hasModulePermission('User Management', 'Permission', ['create'])): ?>
                                                    <button type="button" id="addPermission" class="btn btn-danger waves-effect waves-light">
                                                        <i class="ri-add-circle-line align-middle me-1"></i>
                                                        Add New Permission
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <table id="permissionTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                        <th>Permission Name</th>
                                                        <th>Applicable Modules</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<!-- Add/Edit Permission Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="permissionForm" class="needs-validation" novalidate autocomplete="off">
                    <input type="hidden" id="permissionId" name="permissionId">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="row">
                                        <label for="permissionName" class="col-sm-4 col-form-label">Permission Name *</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="permissionName" name="permissionName" placeholder="Permission Name" required>
                                            <div class="invalid-feedback">Please fill in the field.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="row">
                                        <label class="col-sm-4 col-form-label">Applicable Modules</label>
                                        <div class="col-sm-8">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="modulesAll" name="modulesAll" value="All">
                                                <label class="form-check-label" for="modulesAll"><strong>All Modules</strong></label>
                                            </div>
                                            <?php foreach($grouped as $category => $mods): ?>
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2"><?=htmlspecialchars($category)?></h6>
                                                <?php foreach($mods as $mod): ?>
                                                <div class="form-check mb-1 ms-3">
                                                    <input class="form-check-input module-check" type="checkbox" name="modules[]" value="<?=$mod['id']?>" id="mod_<?=$mod['id']?>">
                                                    <label class="form-check-label" for="mod_<?=$mod['id']?>"><?=htmlspecialchars($mod['name'])?></label>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" id="submitPermission">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/customizer.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/libs/swiper/swiper-bundle.min.js"></script>
<script src="assets/js/pages/dashboard-ecommerce.init.js"></script>
<script src="assets/js/pages/form-validation.init.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/js/pages/notifications.init.js"></script>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="assets/js/pages/datatables.init.js"></script>

<script>
var table;
var permissions = <?= json_encode($_SESSION['permissions']) ?>;
var isSADMIN = <?= json_encode($_SESSION['roles'] == 'SADMIN') ?>;

$(function () {
    $('#selectAllCheckbox').on('change', function() {
        var checkboxes = $('#permissionTable tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
    });

    // Toggle "All" checkbox
    $('#modulesAll').on('change', function() {
        $('.module-check').prop('checked', false).prop('disabled', $(this).is(':checked'));
    });

    table = $("#permissionTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': { 'url': 'php/loadPermissions.php' },
        'columns': [
            {
                data: 'id',
                className: 'select-checkbox',
                orderable: false,
                render: function (data) {
                    return '<input type="checkbox" class="select-checkbox" value="'+data+'"/>';
                }
            },
            { data: 'name' },
            { data: 'modules', render: function(data) { return data; } },
            {
                data: 'id',
                orderable: false,
                render: function (data, type, row) {
                    var perms = (permissions['User Management'] && permissions['User Management']['Permission']) || [];
                    if (isSADMIN || ['edit', 'cancelled'].some(p => perms.includes(p))) {
                        var buttons = `
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-fill align-middle"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">`;

                        if (isSADMIN || perms.includes('edit')) {
                            buttons += `
                                <li>
                                    <a class="dropdown-item edit-item-btn" onclick="edit(${data})">
                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                    </a>
                                </li>`;
                        }

                        if (isSADMIN || perms.includes('cancelled')) {
                            buttons += `
                                <li>
                                    <a class="dropdown-item remove-item-btn" onclick="deactivate(${data})">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                    </a>
                                </li>`;
                        }

                        buttons += `
                                </ul>
                            </div>`;
                        return buttons;
                    }
                    return '';
                }
            }
        ]
    });

    $('#addPermission').on('click', function() {
        $('#addModal').find('#permissionId').val('');
        $('#addModal').find('#permissionName').val('');
        $('#addModal').find('#modulesAll').prop('checked', false).trigger('change');
        $('.module-check').prop('checked', false);
        $('#addModal .is-invalid').removeClass('is-invalid');
        $('#addModal .modal-title').text('Add New Permission');
        $('#addModal').modal('show');

        $('#permissionForm').validate({
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element) { $(element).addClass('is-invalid'); },
            unhighlight: function (element) { $(element).removeClass('is-invalid'); }
        });
    });

    $('#submitPermission').on('click', function() {
        if ($('#permissionForm').valid()) {
            $('#spinnerLoading').show();
            $.post('php/permission.php', $('#permissionForm').serialize(), function(data) {
                var obj = JSON.parse(data);
                if (obj.status === 'success') {
                    table.ajax.reload();
                    $('#spinnerLoading').hide();
                    $('#addModal').modal('hide');
                    $("#successBtn").attr('data-toast-text', obj.message);
                    $("#successBtn").click();
                } else {
                    $('#spinnerLoading').hide();
                    $("#failBtn").attr('data-toast-text', obj.message);
                    $("#failBtn").click();
                }
            });
        }
    });

    $('#multiDelete').on('click', function() {
        $('#spinnerLoading').show();
        var selectedIds = [];
        $("#permissionTable tbody input[type='checkbox']").each(function() {
            if (this.checked) selectedIds.push($(this).val());
        });

        if (selectedIds.length > 0) {
            if (confirm('Are you sure you want to delete these permissions?')) {
                $.post('php/deletePermission.php', { permissionID: selectedIds, type: 'MULTI' }, function(data) {
                    var obj = JSON.parse(data);
                    if (obj.status === 'success') {
                        table.ajax.reload();
                        $("#successBtn").attr('data-toast-text', obj.message);
                        $("#successBtn").click();
                    } else {
                        $("#failBtn").attr('data-toast-text', obj.message);
                        $("#failBtn").click();
                    }
                    $('#spinnerLoading').hide();
                });
            } else {
                $('#spinnerLoading').hide();
            }
        } else {
            alert('Please select at least one permission to delete.');
            $('#spinnerLoading').hide();
        }
    });
});

function edit(id) {
    $('#spinnerLoading').show();
    $.post('php/getPermission.php', { id: id }, function(data) {
        var obj = JSON.parse(data);
        if (obj.status === 'success') {
            $('#addModal').find('#permissionId').val(obj.message.id);
            $('#addModal').find('#permissionName').val(obj.message.name);

            // Reset checkboxes
            $('.module-check').prop('checked', false).prop('disabled', false);
            $('#modulesAll').prop('checked', false);

            var mods = obj.message.modules || [];
            if (mods.length === 1 && mods[0] === 'All') {
                $('#modulesAll').prop('checked', true).trigger('change');
            } else {
                mods.forEach(function(m) {
                    $('.module-check[value="' + m + '"]').prop('checked', true);
                });
            }

            $('#addModal .is-invalid').removeClass('is-invalid');
            $('#addModal .modal-title').text('Edit Permission');
            $('#addModal').modal('show');
        } else {
            $("#failBtn").attr('data-toast-text', obj.message);
            $("#failBtn").click();
        }
        $('#spinnerLoading').hide();
    });
}

function deactivate(id) {
    if (confirm('Are you sure you want to delete this permission?')) {
        $('#spinnerLoading').show();
        $.post('php/deletePermission.php', { permissionID: id }, function(data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                table.ajax.reload();
                $("#successBtn").attr('data-toast-text', obj.message);
                $("#successBtn").click();
            } else {
                $("#failBtn").attr('data-toast-text', obj.message);
                $("#failBtn").click();
            }
            $('#spinnerLoading').hide();
        });
    }
}
</script>

</body>
</html>
