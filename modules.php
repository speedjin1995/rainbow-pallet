<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
require_once "php/db_connect.php";
if (!hasModulePermission('User Management', 'Module', ['view', 'create', 'edit'])){
    header('Location: no-permission.php');
    exit;
}

$categories = $db->query("SELECT DISTINCT category FROM modules ORDER BY category ASC");
?>
<head>
    <title><?=$languageArray['module_code'][$language]?> | PWS - Weighing System</title>
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
                                <div class="col-xl-3 col-md-6 add-new-weight">
                                    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><?=$languageArray['add_new_code'][$language]?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form role="form" id="moduleForm" class="needs-validation" novalidate autocomplete="off">
                                                        <div class="row col-12">
                                                            <div class="col-xxl-12 col-lg-12">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="moduleName" class="col-sm-4 col-form-label"><?=$languageArray['module_name_code'][$language]?> *</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="moduleName" name="moduleName" placeholder="<?=$languageArray['module_name_code'][$language]?>" required>
                                                                                        <div class="invalid-feedback"><?=$languageArray['please_fill_in_the_field_code'][$language]?></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="moduleCategory" class="col-sm-4 col-form-label"><?=$languageArray['category_code'][$language]?> *</label>
                                                                                    <div class="col-sm-8">
                                                                                        <div class="input-group">
                                                                                            <div class="input-group-text">
                                                                                                <input class="form-check-input mt-0" id="manualCategory" type="checkbox" aria-label="Checkbox for manual category">
                                                                                            </div>
                                                                                            <select class="form-select" id="moduleCategory" name="moduleCategory" required>
                                                                                                <option value="">Select Category</option>
                                                                                                <?php while($cat = $categories->fetch_assoc()): ?>
                                                                                                <option value="<?= htmlspecialchars($cat['category']) ?>"><?= htmlspecialchars($cat['category']) ?></option>
                                                                                                <?php endwhile; ?>
                                                                                            </select>
                                                                                            <input type="text" class="form-control" id="moduleCategoryText" name="moduleCategoryText" placeholder="New Category" style="display:none">
                                                                                        </div>
                                                                                        <div class="invalid-feedback"><?=$languageArray['please_fill_in_the_field_code'][$language]?></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <input type="hidden" id="moduleId" name="moduleId">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <div class="hstack gap-2 justify-content-end">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                                <button type="button" class="btn btn-danger" id="submitModule"><?=$languageArray['submit_code'][$language]?></button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="h-100">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <h5 class="card-title mb-0"><?=$languageArray['module_records_code'][$language]?></h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <?php if(hasModulePermission('User Management', 'Module', ['cancelled'])): ?>
                                                                <button type="button" id="multiDelete" class="btn btn-warning waves-effect waves-light">
                                                                    <i class="ri-delete-bin-fill align-middle me-1"></i>
                                                                    <?=$languageArray['delete_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>

                                                                <?php if(hasModulePermission('User Management', 'Module', ['create'])): ?>
                                                                <button type="button" id="addModule" class="btn btn-danger waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                                                    <i class="ri-add-circle-line align-middle me-1"></i>
                                                                    <?=$languageArray['add_new_code'][$language]?>
                                                                </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="moduleTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                                    <th><?=$languageArray['module_name_code'][$language]?></th>
                                                                    <th><?=$languageArray['category_code'][$language]?></th>
                                                                    <th><?=$languageArray['action_code'][$language]?></th>
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
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
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

<script type="text/javascript">
var table;
var permissions = <?= json_encode($_SESSION['permissions']) ?>;
var isSADMIN = <?= json_encode($_SESSION['roles'] == 'SADMIN') ?>;

$(function () {
    $('#selectAllCheckbox').on('change', function() {
        var checkboxes = $('#moduleTable tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
    });

    // Toggle manual category input
    $('#manualCategory').on('change', function() {
        if ($(this).is(':checked')) {
            $('#moduleCategory').hide().removeAttr('required');
            $('#moduleCategoryText').show().attr('required', true);
        } else {
            $('#moduleCategoryText').hide().removeAttr('required').val('');
            $('#moduleCategory').show().attr('required', true);
        }
    });

    table = $("#moduleTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'order': [[2, 'asc']],
        'ajax': { 'url': 'php/modules/modules/loadModules.php' },
        'columns': [
            {
                data: 'id',
                className: 'select-checkbox',
                orderable: false,
                render: function (data) {
                    return `<input type="checkbox" class="select-checkbox" value="${data}"/>`;
                }
            },
            { data: 'name' },
            { data: 'category' },
            {
                data: 'id',
                orderable: false,
                render: function (data, type, row) {
                    var perms = (permissions['User Management'] && permissions['User Management']['Module']) || [];
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

    $('#addModule').on('click', function() {
        $('#addModal').find('#moduleId').val('');
        $('#addModal').find('#moduleName').val('');
        $('#addModal').find('#moduleCategory').val('');
        $('#addModal').find('#moduleCategoryText').val('');
        $('#addModal').find('#manualCategory').prop('checked', false).trigger('change');
        $('#addModal .is-invalid').removeClass('is-invalid');
        $('#addModal .modal-title').text('Add New Module');

        $('#moduleForm').validate({
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element) { $(element).addClass('is-invalid'); },
            unhighlight: function (element) { $(element).removeClass('is-invalid'); }
        });
    });

    $('#submitModule').on('click', function() {
        // Use manual category text if checkbox is checked
        if ($('#manualCategory').is(':checked')) {
            var catVal = $('#moduleCategoryText').val();
            if (!catVal) { alert('Please enter a category'); return; }
            $('#moduleCategory').append('<option value="' + catVal + '">' + catVal + '</option>').val(catVal);
        }

        if ($('#moduleForm').valid()) {
            $('#spinnerLoading').show();
            $.post('php/modules/modules/modules.php', $('#moduleForm').serialize(), function(data) {
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
        $("#moduleTable tbody input[type='checkbox']").each(function() {
            if (this.checked) selectedIds.push($(this).val());
        });

        if (selectedIds.length > 0) {
            if (confirm('Are you sure you want to delete these modules?')) {
                $.post('php/modules/modules/deleteModule.php', { moduleID: selectedIds, type: 'MULTI' }, function(data) {
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
            alert('Please select at least one module to delete.');
            $('#spinnerLoading').hide();
        }
    });
});

function edit(id) {
    $('#spinnerLoading').show();
    $.post('php/modules/modules/getModule.php', { id: id }, function(data) {
        var obj = JSON.parse(data);
        if (obj.status === 'success') {
            $('#addModal').find('#moduleId').val(obj.message.id);
            $('#addModal').find('#moduleName').val(obj.message.name);
            $('#addModal').find('#manualCategory').prop('checked', false).trigger('change');
            $('#addModal').find('#moduleCategory').val(obj.message.category);
            $('#addModal .is-invalid').removeClass('is-invalid');
            $('#addModal .modal-title').text('Edit Module');
            $('#addModal').modal('show');
        } else {
            $("#failBtn").attr('data-toast-text', obj.message);
            $("#failBtn").click();
        }
        $('#spinnerLoading').hide();
    });
}

function deactivate(id) {
    if (confirm('Are you sure you want to delete this module?')) {
        $('#spinnerLoading').show();
        $.post('php/modules/modules/deleteModule.php', { moduleID: id }, function(data) {
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
