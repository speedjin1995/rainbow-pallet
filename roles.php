<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
require_once "layouts/config.php";
require_once "php/db_connect.php";
if (!hasModulePermission('User Management', 'Role', ['view', 'create', 'edit'])){
    header('Location: no-permission.php');
    exit;
}

$id = $_SESSION['id'];
$name = $_SESSION["username"];

$modules = $db->query("SELECT * FROM modules ORDER BY category ASC, name ASC");
$categories = $db->query("SELECT DISTINCT category FROM modules ORDER BY category ASC");
$permissionsResult = $db->query("SELECT * FROM permissions ORDER BY id ASC");
$permissions = array();
while($p = $permissionsResult->fetch_assoc()){
    $p['modules_arr'] = json_decode($p['modules'], true) ?: ['All'];
    $permissions[] = $p;
}

?>

<head>
    <title><?=$languageArray['role_and_permissions_code'][$language]?> | Synctronix - Weighing System</title>
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

<!-- Begin page -->
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="h-100">

                            <!-- Roles DataTable -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h5 class="card-title mb-0"><?=$languageArray['role_records_code'][$language]?></h5>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <?php if(hasModulePermission('User Management', 'Role', ['cancelled'])): ?>
                                                    <button type="button" id="multiDeactivate" class="btn btn-warning waves-effect waves-light">
                                                        <i class="ri-delete-bin-fill align-middle me-1"></i>
                                                        <?=$languageArray['delete_role_code'][$language]?>
                                                    </button>
                                                    <?php endif; ?>

                                                    <?php if(hasModulePermission('User Management', 'Role', ['create'])): ?>
                                                    <button type="button" id="addRole" class="btn btn-success waves-effect waves-light">
                                                        <i class="ri-add-circle-line align-middle me-1"></i>
                                                        <?=$languageArray['add_new_role_code'][$language]?>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <table id="rolesTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                        <th><?=$languageArray['role_code_code'][$language]?></th>
                                                        <th><?=$languageArray['role_name_code'][$language]?></th>
                                                        <th><?=$languageArray['status_code'][$language]?></th>
                                                        <th><?=$languageArray['action_code'][$language]?></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div><!--end row-->

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<!-- Add/Edit Role Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?=$languageArray['add_new_role_code'][$language]?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="roleForm" class="needs-validation" novalidate autocomplete="off">
                    <input type="hidden" id="roleId" name="roleId">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="row">
                                        <label for="roleCode" class="col-sm-4 col-form-label"><?=$languageArray['role_code_code'][$language]?> *</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="roleCode" name="roleCode" placeholder="<?=$languageArray['role_code_code'][$language]?>" maxlength="10" required>
                                            <div class="invalid-feedback"><?=$languageArray['please_fill_in_the_field_code'][$language]?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="row">
                                        <label for="roleName" class="col-sm-4 col-form-label"><?=$languageArray['role_name_code'][$language]?> *</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="roleName" name="roleName" placeholder="<?=$languageArray['role_name_code'][$language]?>" maxlength="15" required>
                                            <div class="invalid-feedback"><?=$languageArray['please_fill_in_the_field_code'][$language]?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                            <button type="button" class="btn btn-success" id="submitRole"><?=$languageArray['submit_code'][$language]?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Permissions Modal -->
<div class="modal fade" id="permModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?=$languageArray['manage_permissions_code'][$language]?> - <span id="permRoleName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="permForm">
                    <input type="hidden" id="permRoleId" name="permRoleId">
                    <?php if($categories->num_rows == 0){ ?>
                        <p class="text-muted"><?=$languageArray['no_module_found_code'][$language]?>.</p>
                    <?php } else { ?>
                    <div class="mb-2 text-end">
                        <a href="javascript:void(0)" class="btn btn-sm btn-soft-danger" id="selectAllPermissions"><?=$languageArray['select_all_code'][$language]?></a>
                    </div>
                    <div class="accordion" id="permAccordion">
                        <?php $catIdx = 0; while($cat = $categories->fetch_assoc()): $catIdx++; ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cat_<?=$catIdx?>">
                                    <?=htmlspecialchars($cat['category'])?>
                                </button>
                            </h2>
                            <div id="cat_<?=$catIdx?>" class="accordion-collapse collapse" data-bs-parent="#permAccordion">
                                <div class="accordion-body p-2">
                                    <?php $modules->data_seek(0); while($mod = $modules->fetch_assoc()): ?>
                                    <?php if($mod['category'] !== $cat['category']) continue; ?>
                                    <div class="card mb-2">
                                        <div class="card-header py-2 text-dark d-flex justify-content-between align-items-center">
                                            <strong><?=htmlspecialchars($mod['name'])?></strong>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-soft-danger select-all-module" data-module="<?=$mod['id']?>"><?=$languageArray['select_all_code'][$language]?></a>
                                        </div>
                                        <div class="card-body py-2">
                                            <div class="row">
                                                <?php foreach($permissions as $perm): ?>
                                                <?php if(in_array('All', $perm['modules_arr']) || in_array((string)$mod['id'], $perm['modules_arr'])): ?>
                                                <div class="col-md-3 mb-1">
                                                    <div class="form-check">
                                                        <input class="form-check-input perm-check" type="checkbox" name="permissions[<?=$mod['id']?>][]" value="<?=$perm['id']?>" id="perm_<?=$mod['id']?>_<?=$perm['id']?>">
                                                        <label class="form-check-label text-dark" for="perm_<?=$mod['id']?>_<?=$perm['id']?>"><?=ucwords(str_replace('_',' ',$perm['name']))?></label>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php } ?>
                    <div class="col-lg-12 mt-3">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                            <button type="button" class="btn btn-success" id="submitPermissions"><?=$languageArray['submit_code'][$language]?></button>
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
        var checkboxes = $('#rolesTable tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
    });

    table = $("#rolesTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': { 'url':'php/modules/roles/loadRoles.php' },
        'columns': [
            {
                data: 'id',
                className: 'select-checkbox',
                orderable: false,
                render: function (data) {
                    return '<input type="checkbox" class="select-checkbox" value="'+data+'"/>';
                }
            },
            { data: 'role_code' },
            { data: 'role_name' },
            { data: 'deleted' },
            {
                data: 'id',
                orderable: false,
                render: function (data, type, row) {
                    var perms = (permissions['User Management'] && permissions['User Management']['Role']) || [];                    
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
                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> <?=$languageArray['edit_code'][$language]?>
                                        </a>
                                    </li>
                                `;
                        }

                        if (isSADMIN || perms.includes('assign_permissions')) {
                            buttons += `
                                    <li>
                                        <a class="dropdown-item" onclick="managePermissions(${data},'${row.role_name}')">
                                            <i class="ri-shield-keyhole-fill align-bottom me-2 text-muted"></i> <?=$languageArray['permissions_code'][$language]?>
                                        </a>
                                    </li>
                                `;
                        }

                        if (isSADMIN || perms.includes('cancelled')) {
                            buttons += `
                                    <li>
                                        <a class="dropdown-item remove-item-btn" onclick="deactivate(${data})">
                                            <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> <?=$languageArray['delete_code'][$language]?>
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

    // Submit Role
    $('#submitRole').on('click', function(){
        if($('#roleForm').valid()){
            $('#spinnerLoading').show();
            $.post('php/modules/roles/role.php', $('#roleForm').serialize(), function(data){
                var obj = JSON.parse(data);
                if(obj.status === 'success'){
                    table.ajax.reload();
                    $('#spinnerLoading').hide();
                    $('#addModal').modal('hide');
                    toastr["success"](obj.message, "Success:");
                } else {
                    $('#spinnerLoading').hide();
                    toastr["error"](obj.message, "Failed:");
                }
            });
        }
    });

    // Submit Permissions
    $('#submitPermissions').on('click', function(){
        $('#spinnerLoading').show();
        $.post('php/modules/roles/rolePermission.php', $('#permForm').serialize(), function(data){
            var obj = JSON.parse(data);
            if(obj.status === 'success'){
                window.location.reload();
                $('#spinnerLoading').hide();
                $('#permModal').modal('hide');
                toastr["success"](obj.message, "Success:");
            } else {
                $('#spinnerLoading').hide();
                toastr["error"](obj.message, "Failed:");
            }
        });
    });

    // Add Role button
    $('#addRole').on('click', function(){
        $('#addModal').find('#roleId').val("");
        $('#addModal').find('#roleCode').val("");
        $('#addModal').find('#roleName').val("");
        $('#addModal .is-invalid').removeClass('is-invalid');
        $('#addModal .modal-title').text('Add New Role');
        $('#addModal').modal('show');

        $('#roleForm').validate({
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element) { $(element).addClass('is-invalid'); },
            unhighlight: function (element) { $(element).removeClass('is-invalid'); }
        });
    });

    $('#multiDeactivate').on('click', function () {
        $('#spinnerLoading').show();
        var selectedIds = []; // An array to store the selected 'id' values

        $("#rolesTable tbody input[type='checkbox']").each(function () {
            if (this.checked) {
                selectedIds.push($(this).val());
            }
        });

        if (selectedIds.length > 0) {
            if (confirm('<?=$languageArray['multi_roles_delete_confirmation_code'][$language]?>')) {
                $.post('php/modules/roles/deleteRole.php', {roleID: selectedIds, type: 'MULTI'}, function(data){
                    var obj = JSON.parse(data);
                    
                    if(obj.status === 'success'){
                        table.ajax.reload();
                        toastr["success"](obj.message, "Success:");
                        $('#spinnerLoading').hide();
                    }
                    else if(obj.status === 'failed'){
                        toastr["error"](obj.message, "Failed:");
                        $('#spinnerLoading').hide();
                    }
                    else{
                        toastr["error"]("Something wrong when activate", "Failed:");
                        $('#spinnerLoading').hide();
                    }
                });
            }

            $('#spinnerLoading').hide();
        } 
        else {
            // Optionally, you can display a message or take another action if no IDs are selected
            alert("<?=$languageArray['min_one_role_delete_code'][$language]?>");
            $('#spinnerLoading').hide();
        }     
    });
});

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/modules/roles/getRole.php', {id: id}, function(data)
    {
        var obj = JSON.parse(data);
        if(obj.status === 'success'){
            $('#addModal').find('#roleId').val(obj.message.id);
            $('#addModal').find('#roleCode').val(obj.message.role_code);
            $('#addModal').find('#roleName').val(obj.message.role_name);

            // Remove Validation Error Message
            $('#addModal .is-invalid').removeClass('is-invalid');

            $('#addModal').modal('show');
        }
        else {
            $('#spinnerLoading').hide();
            toastr["error"](obj.message, "Failed:");
        }
        $('#spinnerLoading').hide();
    });
}

$(document).on('click', '.select-all-module', function() {
    var moduleId = $(this).data('module');
    var checkboxes = $('input[name="permissions[' + moduleId + '][]"]');
    var allChecked = checkboxes.length === checkboxes.filter(':checked').length;
    checkboxes.prop('checked', !allChecked);
    $(this).text(allChecked ? '<?=$languageArray['select_all_code'][$language]?>' : '<?=$languageArray['deselect_all_code'][$language]?>');
});

$('#selectAllPermissions').on('click', function() {
    var checkboxes = $('#permForm .perm-check');
    var allChecked = checkboxes.length === checkboxes.filter(':checked').length;
    checkboxes.prop('checked', !allChecked);
    $(this).text(allChecked ? '<?=$languageArray['select_all_code'][$language]?>' : '<?=$languageArray['deselect_all_code'][$language]?>');
    $('.select-all-module').text(allChecked ? '<?=$languageArray['select_all_code'][$language]?>' : '<?=$languageArray['deselect_all_code'][$language]?>');
});

function managePermissions(id, roleName) {
    $('#permModal .modal-title').text('<?=$languageArray['permissions_code'][$language]?> - ' + roleName);
    $('#permForm').find('#permRoleId').val(id);
    // Uncheck all first
    $('#permForm .perm-check').prop('checked', false);

    $('#spinnerLoading').show();
    $.post('php/modules/roles/getRolePermissions.php', { id: id }, function(data) {
        var obj = JSON.parse(data);
        if (obj.status === 'success') {
            obj.message.forEach(function(p) {
                $('#perm_' + p.module_id + '_' + p.permission_id).prop('checked', true);
            });
        }
        $('#spinnerLoading').hide();
        $('#permModal').modal('show');
    });
}

function deactivate(id){
    if (confirm('<?=$languageArray['roles_delete_confirmation_code'][$language]?>?')) {
        $('#spinnerLoading').show();
        $.post('php/modules/roles/deleteRole.php', {roleID: id}, function(data){
            var obj = JSON.parse(data);
            if(obj.status === 'success'){
                table.ajax.reload();
                toastr["success"](obj.message, "Success:");
            } else {
                toastr["error"](obj.message, "Failed:");
            }
            $('#spinnerLoading').hide();
        });
    }
}
</script>

</body>
</html>
