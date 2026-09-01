<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
// Check if the user is already logged in, if yes then redirect him to index page
$id = $_SESSION['id'];
$name = $_SESSION["username"];

$query = "SELECT role_code, role_name from roles WHERE role_code <> 'SADMIN' AND deleted = '0'";

if($_SESSION["roles"] == 'ADMIN'){
    $query = "SELECT role_code, role_name from roles WHERE role_code <> 'SADMIN' AND role_code <> 'ADMIN' AND deleted = '0'";
}

$stmt2 = $db->prepare($query);
mysqli_stmt_execute($stmt2);
mysqli_stmt_store_result($stmt2);
mysqli_stmt_bind_result($stmt2, $code, $name);

// Pull plants
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
    $username = implode("', '", $_SESSION["plant"]);
    $query4 = "SELECT id, name FROM Plant WHERE status = '0' and plant_code IN ('$username')";
}
else{
    $query4 = "SELECT id, name FROM Plant WHERE status = '0'";
}

$stmt4 = $db->prepare($query4);
mysqli_stmt_execute($stmt4);
mysqli_stmt_store_result($stmt4);
mysqli_stmt_bind_result($stmt4, $pcode, $pname);
?>

<head>

    <title><?=$languageArray['staff_code'][$language]?> | Synctronix - Weighing System</title>
    <?php include 'layouts/title-meta.php'; ?>

    <!-- jsvectormap css -->
    <link href="assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />
    <!--datatable css-->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.min.css" />
    <link rel="stylesheet" href="plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

    <!-- Include jQuery library -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Include jQuery Validate plugin -->
    <script src="plugins/jquery-validation/jquery.validate.min.js"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">

    <?php include 'layouts/head-css.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>

<!-- Begin page -->
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="h-100">
                            <!--datatable--> 
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h5 class="card-title mb-0"><?=$languageArray['user_records_code'][$language]?></h5>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <a href="template/User_Template.xlsx" download>
                                                        <button type="button" id="downloadTemplate" class="btn btn-info waves-effect waves-light">
                                                            <i class="ri-file-pdf-line align-middle me-1"></i>
                                                            <?=$languageArray['download_template_code'][$language]?>
                                                        </button>
                                                    </a>
                                                    <button type="button" id="uploadExcel" class="btn btn-success waves-effect waves-light">
                                                        <i class="ri-file-pdf-line align-middle me-1"></i>
                                                        <?=$languageArray['upload_excel_code'][$language]?>
                                                    </button>
                                                    <button type="button" id="multiDeactivate" class="btn btn-warning waves-effect waves-light">
                                                        <i class="ri-delete-bin-fill align-middle me-1"></i>
                                                        <?=$languageArray['delete_code'][$language]?>
                                                    </button>
                                                    <button type="button" id="addMembers" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                                        <i class="ri-add-circle-line align-middle me-1"></i>
                                                        <?=$languageArray['add_new_code'][$language]?>
                                                    </button>
                                                </div> 
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <table id="usersTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                        <th><?=$languageArray['employee_code_code'][$language]?></th>
                                                        <th><?=$languageArray['username_code'][$language]?></th>
                                                        <th><?=$languageArray['name_code'][$language]?></th>
                                                        <th><?=$languageArray['email_code'][$language]?></th>
                                                        <th><?=$languageArray['role_code'][$language]?></th>
                                                        <th><?=$languageArray['plant_name_code'][$language]?></th>
                                                        <th><?=$languageArray['action_code'][$language]?></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div><!--end row-->
                        </div> <!-- end .h-100-->
                    </div> <!-- end col -->
                </div><!-- container-fluid -->
            </div> <!-- End Page-content -->

            <?php include 'layouts/footer.php'; ?>
        </div><!-- end main content-->
    </div><!-- END layout-wrapper -->

    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable custom-xxl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalScrollableTitle"><?=$languageArray['add_new_code'][$language]?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form id="memberForm" class="needs-validation" novalidate autocomplete="off">
                        <div class="row col-12">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <input type="hidden" class="form-control" id="id" name="id"> 
                                            <div class="col-12 mb-3">
                                                <div class="row">
                                                    <label for="employeeCode" class="col-sm-4 col-form-label"><?=$languageArray['employee_code_code'][$language]?> *</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" id="employeeCode" name="employeeCode" placeholder="<?=$languageArray['employee_code_code'][$language]?>" required>
                                                        <div class="invalid-feedback">
                                                            <?=$languageArray['please_fill_in_the_field_code'][$language]?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="row">
                                                <label for="username" class="col-sm-4 col-form-label"><?=$languageArray['username_code'][$language]?> *</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" id="username" name="username" placeholder="<?=$languageArray['username_code'][$language]?>" required>
                                                        <div class="invalid-feedback">
                                                            <?=$languageArray['please_fill_in_the_field_code'][$language]?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="row">
                                                <label for="name" class="col-sm-4 col-form-label"><?=$languageArray['name_code'][$language]?> *</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" id="name" name="name" placeholder="<?=$languageArray['name_code'][$language]?>" required>
                                                        <div class="invalid-feedback">
                                                            <?=$languageArray['please_fill_in_the_field_code'][$language]?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="row">
                                                <label for="useremail" class="col-sm-4 col-form-label"><?=$languageArray['email_code'][$language]?></label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" id="useremail" name="useremail" placeholder="<?=$languageArray['email_code'][$language]?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="row">
                                                    <label for="roles" class="col-sm-4 col-form-label"><?=$languageArray['role_code'][$language]?> *</label>
                                                    <div class="col-sm-8">
                                                        <select id="roles" name="roles" class="select2" required>
                                                            <option select="selected" value="">Please Select</option>
                                                            <?php while(mysqli_stmt_fetch($stmt2)){ ?>
                                                                <option value="<?=$code ?>"><?=$name ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="row">
                                                    <label for="plantId" class="col-sm-4 col-form-label"><?=$languageArray['plant_code'][$language]?></label>
                                                    <div class="col-sm-8">
                                                        <select id="plantId" name="plantId[]" class="form-control" multiple="multiple">
                                                            <?php while(mysqli_stmt_fetch($stmt4)){ ?>
                                                                <option value="<?=$pcode ?>"><?=$pname ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>                                              
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-12">
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                <button type="button" class="btn btn-success" id="submitMember"><?=$languageArray['submit_code'][$language]?></button>
                            </div>
                        </div><!--end col-->                                                               
                    </form>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <div class="modal fade" id="uploadModal">
        <div class="modal-dialog modal-xl" style="max-width: 90%;">
            <div class="modal-content">
                <form role="form" id="uploadForm">
                    <div class="modal-header bg-gray-dark color-palette">
                        <h4 class="modal-title"><?=$languageArray['upload_excel_code'][$language]?></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="file" id="fileInput">
                        <button type="button" id="previewButton"><?=$languageArray['preview_data_code'][$language]?></button>
                        <div id="previewTable" style="overflow: auto;"></div>
                    </div>
                    <div class="modal-footer justify-content-between bg-gray-dark color-palette">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                        <button type="button" class="btn btn-success" id="uploadUser"><?=$languageArray['submit_code'][$language]?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="errorModal" style="display:none">
        <div class="modal-dialog modal-xl" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-header bg-gray-dark color-palette">
                    <h4 class="modal-title"><?=$languageArray['error_log_code'][$language]?></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group">
                            <ol id="errorList" class="text-danger mt-2" style="padding-left: 20px;"></ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'layouts/customizer.php'; ?>
    <?php include 'layouts/vendor-scripts.php'; ?>

    <!-- apexcharts -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

    <!-- Vector map-->
    <script src="assets/libs/jsvectormap/js/jsvectormap.min.js"></script>
    <script src="assets/libs/jsvectormap/maps/world-merc.js"></script>

    <!--Swiper slider js-->
    <script src="assets/libs/swiper/swiper-bundle.min.js"></script>

    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard-ecommerce.init.js"></script>   
    <script src="assets/js/pages/form-validation.init.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <!-- notifications init -->
    <script src="assets/js/pages/notifications.init.js"></script>
    <script src="plugins/datatables/jquery.dataTables.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="assets/js/pages/datatables.init.js"></script>
    <script src="plugins/select2/js/select2.full.min.js"></script>

    <script>
    $(function () {
        $('#selectAllCheckbox').on('change', function() {
            var checkboxes = $('#usersTable tbody input[type="checkbox"]');
            checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
        });

        // Initialize all Select2 elements in the modal
        $('#addModal .select2').select2({
            allowClear: true,
            placeholder: "Please Select",
            dropdownParent: $('#addModal') // Ensures dropdown is not cut off
        });

        // Initialize plantId elements in the modal
        $('#addModal #plantId').select2({
            allowClear: true,
            multiple: true,
            dropdownParent: $('#addModal') // Ensures dropdown is not cut off
        });

        $("#plantId").on("select2:select change", function () {
            $(".select2-selection__choice").css({
                "background-color": "rgb(64, 81, 137)",
                "color": "white"
            });

            $(".select2-selection__choice__remove").css({
                "color": "white"
            });
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
        
        table = $("#usersTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'ajax': {
                'url':'php/modules/user/loadMembers.php'
            },
            'columns': [
                {
                    // Add a checkbox with a unique ID for each row
                    data: 'id', // Assuming 'serialNo' is a unique identifier for each row
                    className: 'select-checkbox',
                    orderable: false,
                    render: function (data, type, row) {
                        return '<input type="checkbox" class="select-checkbox" id="checkbox_' + data + '" value="'+data+'"/>';
                    }
                },
                { data: 'employee_code' },
                { data: 'username' },
                { data: 'name' },
                { data: 'useremail' },
                { data: 'role' },
                { data: 'plant' },
                { 
                    data: 'id',
                    render: function ( data, type, row ) {
                        // return '<div class="row"><div class="col-3"><button type="button" id="edit'+data+'" onclick="edit('+data+')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" id="deactivate'+data+'" onclick="deactivate('+data+')" class="btn btn-success btn-sm"><i class="fas fa-trash"></i></button></div></div>';
                        return '<div class="dropdown d-inline-block"><button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
                        '<i class="ri-more-fill align-middle"></i></button><ul class="dropdown-menu dropdown-menu-end">' +
                        '<li><a class="dropdown-item edit-item-btn" id="edit'+data+'" onclick="edit('+data+')"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> <?=$languageArray['edit_code'][$language] ?></a></li>' +
                        '<li><a class="dropdown-item remove-item-btn" id="deactivate'+data+'" onclick="deactivate('+data+')"><i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> <?=$languageArray['delete_code'][$language] ?> </a></li></ul></div>';
                    }
                }
            ]
        });
        
        $('#submitMember').on('click', function(){
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
            if($('#memberForm').valid()){
                $('#spinnerLoading').show();
                $.post('php/modules/user/users.php', $('#memberForm').serialize(), function(data){
                    var obj = JSON.parse(data);
                    if(obj.status === 'success') {
                        table.ajax.reload();
                        $('#spinnerLoading').hide();
                        $('#addModal').modal('hide');
                        toastr["success"](obj.message, "Success:");
                    }
                    else if(obj.status === 'failed') {
                        $('#spinnerLoading').hide();
                        toastr["error"](obj.message, "Failed:");
                    }
                    else {
                        $('#spinnerLoading').hide();
                        toastr["error"]("Something went wrong", "Failed:");
                    }
                });
            }
        });

        $('#addMembers').on('click', function(){
            $('#addModal').find('#id').val("");
            $('#addModal').find('#employeeCode').val("");
            $('#addModal').find('#username').val("");
            $('#addModal').find('#name').val("");
            $('#addModal').find('#useremail').val("");
            $('#addModal').find('#roles').val("");
            $('#addModal').find('#plantId').val('').trigger('change');

            // Remove Validation Error Message
            $('#addModal .is-invalid').removeClass('is-invalid');

            $('#addModal .select2[required]').each(function () {
                var select2Field = $(this);
                var select2Container = select2Field.next('.select2-container');
                
                select2Container.find('.select2-selection').css('border', ''); // Remove red border
                select2Container.next('.select2-error').remove(); // Remove error message
            });

            $('#addModal').modal('show');
            
            $('#memberForm').validate({
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

        $('#uploadUser').on('click', function(){
            $('#spinnerLoading').show();
            var formData = $('#uploadForm').serializeArray();
            var data = [];
            var rowIndex = -1;
            formData.forEach(function(field) {
            var match = field.name.match(/([a-zA-Z0-9]+)\[(\d+)\]/);
            if (match) {
                var fieldName = match[1];
                var index = parseInt(match[2], 10);
                if (index !== rowIndex) {
                rowIndex = index;
                data.push({});
                }
                data[index][fieldName] = field.value;
            }
            });

            // Send the JSON array to the server
            $.ajax({
                url: 'php/modules/user/uploadUser.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    var obj = JSON.parse(response);
                    if (obj.status === 'success') {
                        $('#spinnerLoading').hide();
                        $('#uploadModal').modal('hide');
                        toastr["success"](obj.message, "Success:");
                        $('#usersTable').DataTable().ajax.reload(null, false);
                    }
                    else if (obj.status === 'failed') {
                        $('#spinnerLoading').hide();
                        toastr["error"](obj.message, "Failed:");
                    }
                    else if (obj.status === 'error') {
                        $('#spinnerLoading').hide();
                        $('#uploadModal').modal('hide');
                        $('#usersTable').DataTable().ajax.reload(null, false);
                        $('#errorModal').find('#errorList').empty();
                        var errorMessage = obj.message;
                        for (var i = 0; i < errorMessage.length; i++) {
                            $('#errorModal').find('#errorList').append(`<li>${errorMessage[i]}</li>`);
                        }
                        $('#errorModal').modal('show');
                    }
                    else {
                        $('#spinnerLoading').hide();
                        toastr["error"]("Failed to save", "Failed:");
                    }
                }
            });
        });

        $('#uploadExcel').on('click', function(){
            $('#previewTable').html('');
            $('#fileInput').val('');
            $('#uploadModal').modal('show');

            $('#uploadForm').validate({
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

        $('#uploadModal').find('#previewButton').on('click', function(){
            var fileInput = document.getElementById('fileInput');
            var file = fileInput.files[0];
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var data = e.target.result;
                // Process data and display preview
                displayPreview(data);
            };

            reader.readAsBinaryString(file);
        });

        $('#multiDeactivate').on('click', function () {
            $('#spinnerLoading').show();
            var selectedIds = []; // An array to store the selected 'id' values

            $("#usersTable tbody input[type='checkbox']").each(function () {
                if (this.checked) {
                    selectedIds.push($(this).val());
                }
            });

            if (selectedIds.length > 0) {
                if (confirm('Are you sure you want to delete these users?')) {
                    $.post('php/modules/user/deleteUser.php', {userID: selectedIds, type: 'MULTI'}, function(data){
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
                alert("Please select at least one user to delete.");
                $('#spinnerLoading').hide();
            }     
        });
    });

    function edit(id){
        $('#spinnerLoading').show();
        $.post('php/modules/user/getUser.php', {userID: id}, function(data){
            var obj = JSON.parse(data);
            
            if(obj.status === 'success'){
                $('#addModal').find('#id').val(obj.message.id);
                $('#addModal').find('#employeeCode').val(obj.message.employee_code);
                $('#addModal').find('#username').val(obj.message.username);
                $('#addModal').find('#name').val(obj.message.name);
                $('#addModal').find('#useremail').val(obj.message.useremail);
                $('#addModal').find('#roles').val(obj.message.role_code).trigger("change");
                $("#addModal").find("#plantId").val(JSON.parse(obj.message.plant)).trigger("change");

                // Remove Validation Error Message
                $('#addModal .is-invalid').removeClass('is-invalid');

                $('#addModal').modal('show');
                
                $('#memberForm').validate({
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
            else if(obj.status === 'failed'){
                toastr["error"](obj.message, "Failed:");
            }
            else{
                toastr["error"]("Something went wrong", "Failed:");
            }
            $('#spinnerLoading').hide();
        });
    }

    function deactivate(id){
        $('#spinnerLoading').show();
        if (confirm('Are you sure you want to delete this user?')) {
        $.post('php/modules/user/deleteUser.php', {userID: id}, function(data){
                var obj = JSON.parse(data);

                if(obj.status === 'success'){
                    table.ajax.reload();
                    $('#spinnerLoading').hide();
                    toastr["success"](obj.message, "Success:");
                }
                else if(obj.status === 'failed'){
                    $('#spinnerLoading').hide();
                    toastr["error"](obj.message, "Failed:");
                }
                else{
                    $('#spinnerLoading').hide();
                    toastr["error"](obj.message, "Failed:");
                }
            });
        }

        $('#spinnerLoading').hide();
    }

    function displayPreview(data) {
        // Parse the Excel data
        var workbook = XLSX.read(data, { type: 'binary' });

        // Get the first sheet
        var sheetName = workbook.SheetNames[0];
        var sheet = workbook.Sheets[sheetName];

        // Convert the sheet to an array of objects
        var jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 });

        // Get the headers
        var headers = jsonData[0];

        // Ensure we handle cases where there may be less than 5 columns
        while (headers.length < 5) {
            headers.push(''); // Adding empty headers to reach 5 columns
        }

        // Create HTML table headers
        var htmlTable = '<table style="width:40%;"><thead><tr>';
        headers.forEach(function(header) {
            htmlTable += '<th>' + header + '</th>';
        });
        htmlTable += '</tr></thead><tbody>';

        // Iterate over the data and create table rows
        for (var i = 1; i < jsonData.length; i++) {
            htmlTable += '<tr>';
            var rowData = jsonData[i];

            // Ensure we handle cases where there may be less than 5 cells in a row
            while (rowData.length < 5) {
                rowData.push(''); // Adding empty cells to reach 5 columns
            }

            for (var j = 0; j < 5; j++) {
                var cellData = rowData[j];
                var formattedData = cellData;

                // Check if cellData is a valid Excel date serial number and format it to DD/MM/YYYY
                if (typeof cellData === 'number' && cellData > 0) {
                    var excelDate = XLSX.SSF.parse_date_code(cellData);
                }

                htmlTable += '<td><input type="text" id="'+headers[j].replace(/[^a-zA-Z0-9]/g, '')+(i-1)+'" name="'+headers[j].replace(/[^a-zA-Z0-9]/g, '')+'['+(i-1)+']" value="' + (formattedData == null ? '' : formattedData) + '" /></td>';
            }
            htmlTable += '</tr>';
        }

        htmlTable += '</tbody></table>';

        var previewTable = document.getElementById('previewTable');
        previewTable.innerHTML = htmlTable;
    }

    function reactivate(id) {
        if (confirm('Do you want to reactivate this user?')) {
            $('#spinnerLoading').show();
            $.post('php/reactivateMasterData.php', {userID: id, type: "User"}, function(data){
                var obj = JSON.parse(data);

                if(obj.status === 'success'){
                    table.ajax.reload();
                    $('#spinnerLoading').hide();
                    toastr["success"](obj.message, "Success:");
                }
                else if(obj.status === 'failed'){
                    $('#spinnerLoading').hide();
                    toastr["error"](obj.message, "Failed:");
                }
                else{
                    $('#spinnerLoading').hide();
                    toastr["error"](obj.message, "Failed:");
                }
            });
        }

        $('#spinnerLoading').hide();
    }
    </script>

    </body>

    </html>