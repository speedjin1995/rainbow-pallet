<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title><?=$languageArray['species_code'][$language] ?? 'Species'?> | Synctronix - Weighing System</title>
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

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="h-100">
                            <div class="row mb-3 pb-1">
                                <div class="col-12">
                                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                                        <div class="flex-grow-1">
                                        </div>
                                    </div><!-- end card header -->
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                            
                            <div class="row">
                                <div class="col-xl-3 col-md-6 add-new-weight">

                                    <!-- /.modal-dialog -->
                                    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalScrollableTitle"><?=$languageArray['add_new_code'][$language] ?? 'Add New'?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form role="form" id="speciesForm" class="needs-validation" novalidate autocomplete="off">
                                                        <div class=" row col-12">
                                                            <div class="col-xxl-12 col-lg-12">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="speciesName" class="col-sm-4 col-form-label"><?=$languageArray['species_code'][$language] ?? 'Species'?></label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="speciesName" name="speciesName" placeholder="<?=$languageArray['species_code'][$language] ?? 'Species'?>" required>
                                                                                        <div class="invalid-feedback">
                                                                                            <?=$languageArray['please_fill_in_the_field_code'][$language] ?? 'Please fill in the field'?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <input type="hidden" class="form-control" id="id" name="id">                                                                        
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-12">
                                                            <div class="hstack gap-2 justify-content-end">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language] ?? 'Close'?></button>
                                                                <button type="button" class="btn btn-success" id="submitSpecies"><?=$languageArray['submit_code'][$language] ?? 'Submit'?></button>
                                                            </div>
                                                        </div><!--end col-->                                                               
                                                    </form>
                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->
                                </div>
                            </div> <!-- end row-->

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
                                                                <h5 class="card-title mb-0"><?=$languageArray['previous_records_code'][$language] ?? 'Previous Records'?></h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <button type="button" id="multiDeactivate" class="btn btn-warning waves-effect waves-light">
                                                                    <i class="ri-delete-bin-fill align-middle me-1"></i>
                                                                    <?=$languageArray['delete_code'][$language] ?? 'Delete'?>
                                                                </button>
                                                                <button type="button" id="addSpecies" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                                                    <i class="ri-add-circle-line align-middle me-1"></i>
                                                                    <?=$languageArray['add_new_code'][$language] ?? 'Add New'?>
                                                                </button>
                                                            </div> 
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="speciesTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                                    <th><?=$languageArray['species_code'][$language] ?? 'Species'?></th>
                                                                    <th><?=$languageArray['status_code'][$language] ?? 'Status'?></th>
                                                                    <th><?=$languageArray['action_code'][$language] ?? 'Action'?></th>
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

                        </div> <!-- end .h-100-->
                    </div> <!-- end col -->
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <?php include 'layouts/footer.php'; ?>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <?php include 'layouts/customizer.php'; ?>

    <?php include 'layouts/vendor-scripts.php'; ?>

    <!--Swiper slider js-->
    <script src="assets/libs/swiper/swiper-bundle.min.js"></script>

    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard-ecommerce.init.js"></script>   
    <script src="assets/js/pages/form-validation.init.js"></script>
    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <!-- prismjs plugin -->
    <script src="assets/libs/prismjs/prism.js"></script>

    <!-- notifications init -->
    <script src="assets/js/pages/notifications.init.js"></script>
    <script src="plugins/datatables/jquery.dataTables.js"></script>
    <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.print.min.js"></script>
    <script src="plugins/datatables-buttons/js/buttons.html5.min.js"></script>
    <script src="assets/js/pages/datatables.init.js"></script>

<script type="text/javascript">

var table;

$(function () {
    $('#selectAllCheckbox').on('change', function() {
        var checkboxes = $('#speciesTable tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
    });

    table = $("#speciesTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url':'php/modules/sawnTimberSpecies/loadSpecies.php'
        },
        'columns': [
            {
                data: 'id',
                className: 'select-checkbox',
                orderable: false,
                render: function (data, type, row) {
                    return '<input type="checkbox" class="select-checkbox" id="checkbox_' + data + '" value="'+data+'"/>';
                }
            },
            { data: 'name' },
            {
                data: 'id',
                render: function ( data, type, row ) {
                    if (row.status == '1'){
                        return '<button title="Reactivate" type="button" id="reactivate'+data+'" onclick="reactivate('+data+')" class="btn btn-warning btn-sm">Reactivate</button>';
                    }else{
                        return 'Active';
                    }
                }
            },
            { 
                data: 'id',
                render: function ( data, type, row ) {
                    return '<div class="dropdown d-inline-block"><button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
                    '<i class="ri-more-fill align-middle"></i></button><ul class="dropdown-menu dropdown-menu-end">' +
                    '<li><a class="dropdown-item edit-item-btn" id="edit'+data+'" onclick="edit('+data+')"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> <?=$languageArray['edit_code'][$language] ?? 'Edit' ?></a></li>' +
                    '<li><a class="dropdown-item remove-item-btn" id="deactivate'+data+'" onclick="deactivate('+data+')"><i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> <?=$languageArray['delete_code'][$language] ?? 'Delete' ?> </a></li></ul></div>';
                }
            }
        ]       
    });
    
    $('#submitSpecies').on('click', function(){
        if($('#speciesForm').valid()){
            $('#spinnerLoading').show();
            $.post('php/modules/sawnTimberSpecies/species.php', $('#speciesForm').serialize(), function(data){
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
                    toastr["error"]("Something went wrong!", "Failed:");
                }
            });
        }
    });

    $('#addSpecies').on('click', function(){
        $('#addModal').find('#id').val("");
        $('#addModal').find('#speciesName').val("");

        // Remove Validation Error Message
        $('#addModal .is-invalid').removeClass('is-invalid');

        $('#addModal').modal('show');
        
        $('#speciesForm').validate({
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

    $('#multiDeactivate').on('click', function () {
        $('#spinnerLoading').show();
        var selectedIds = [];

        $("#speciesTable tbody input[type='checkbox']").each(function () {
            if (this.checked) {
                selectedIds.push($(this).val());
            }
        });

        if (selectedIds.length > 0) {
            if (confirm('Are you sure you want to delete these species?')) {
                $.post('php/modules/sawnTimberSpecies/deleteSpecies.php', {userID: selectedIds, type: 'MULTI'}, function(data){
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
            alert("Please select at least one species to delete.");
            $('#spinnerLoading').hide();
        }     
    });
});

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/modules/sawnTimberSpecies/getSpecies.php', {userID: id}, function(data)
    {
        var obj = JSON.parse(data);
        if(obj.status === 'success'){
            $('#addModal').find('#id').val(obj.message.id);
            $('#addModal').find('#speciesName').val(obj.message.name);

            // Remove Validation Error Message
            $('#addModal .is-invalid').removeClass('is-invalid');

            $('#addModal').modal('show');

            $('#speciesForm').validate({
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
            $('#spinnerLoading').hide();
            toastr["error"](obj.message, "Failed:");
        }
        else{
            $('#spinnerLoading').hide();
            toastr["error"](obj.message, "Failed:");
        }
        $('#spinnerLoading').hide();
    });
}

function deactivate(id){
    $('#spinnerLoading').show();
    if (confirm('Are you sure you want to delete this species?')) {
        $.post('php/modules/sawnTimberSpecies/deleteSpecies.php', {userID: id}, function(data){
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

function reactivate(id) {
  if (confirm('Do you want to reactivate this species?')) {
    $('#spinnerLoading').show();
    $.post('php/modules/sawnTimberSpecies/reactivateSpecies.php', {userID: id}, function(data){
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

        $('#spinnerLoading').hide();
    });
  }

  $('#spinnerLoading').hide();
}

</script>
    </body>

    </html>
