<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title><?=$languageArray['message_resource_code'][$language] ?> | Synctronix - Weighing System</title>
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
                                            <!--h4 class="fs-16 mb-1">Good Morning, Anna!</h4>
                                            <p class="text-muted mb-0">Here's what's happening with your store
                                                today.</p-->
                                        </div>
                                    </div><!-- end card header -->
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                            
                            <div class="row">
                                <div class="col-xl-3 col-md-6 add-new-weight">

                                    <!-- /.modal-dialog -->
                                    <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalScrollableTitle"><?=$languageArray['add_new_code'][$language] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form role="form" id="messageForm" class="needs-validation" novalidate autocomplete="off">
                                                        <div class=" row col-12">
                                                            <div class="col-xxl-12 col-lg-12">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="keyCode" class="col-sm-4 col-form-label"><?=$languageArray['message_code_code'][$language] ?> *</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="keyCode" name="keyCode" placeholder="<?=$languageArray['message_code_code'][$language] ?>" required>
                                                                                        <div class="invalid-feedback">
                                                                                            <?=$languageArray['please_fill_in_the_field_code'][$language] ?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="englishDecs" class="col-sm-4 col-form-label">English *</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="englishDecs" name="englishDecs" placeholder="English" required>
                                                                                        <div class="invalid-feedback">
                                                                                            <?=$languageArray['please_fill_in_the_field_code'][$language] ?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="chineseDecs" class="col-sm-4 col-form-label">中文</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="chineseDecs" name="chineseDecs" placeholder="中文">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="malayDecs" class="col-sm-4 col-form-label">Bahasa Malaysia</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="malayDecs" name="malayDecs" placeholder="Bahasa">
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="nepaliDecs" class="col-sm-4 col-form-label">नेपाली</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="nepaliDecs" name="nepaliDecs" placeholder="नेपाली">
                                                                                    </div>
                                                                                </div>
                                                                            </div>                                                                      
                                                                            <input type="hidden" class="form-control" id="keyId" name="keyId">                                                                                                                                                         
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        
                                                        <div class="col-lg-12">
                                                            <div class="hstack gap-2 justify-content-end">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                                <button type="button" class="btn btn-success" id="submitMessage"><?=$languageArray['submit_code'][$language]?></button>
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
                                                                <h5 class="card-title mb-0"><?=$languageArray['previous_records_code'][$language]?></h5>
                                                            </div>
                                                            <div class="flex-shrink-0">
                                                                <button type="button" id="addMessage" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#messageModal">
                                                                    <i class="ri-add-circle-line align-middle me-1"></i>
                                                                    <?=$languageArray['add_new_code'][$language] ?>
                                                                </button>
                                                            </div> 
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="messageTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th>No.</th>
                                                                    <th><?=$languageArray['message_code_code'][$language] ?></th>
                                                                    <th>English</th>
                                                                    <th>中文</th>
                                                                    <th>Bahasa Malaysia</th>
                                                                    <th>नेपाली</th>
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
        var checkboxes = $('#plantTable tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
    });

    table = $("#messageTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'order': [[ 1, 'asc' ]],
        'columnDefs': [ { orderable: false, targets: [0] }],
        'ajax': {
            'url':'php/modules/message/loadMessages.php'
        },
        'columns': [
            { data: 'counter' },
            { data: 'message_key_code' },
            { data: 'en' },
            { data: 'zh' },
            { data: 'my' },
            { data: 'ne' },
            { 
                data: 'id',
                render: function ( data, type, row ) {
                    return '<div class="dropdown d-inline-block"><button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
                    '<i class="ri-more-fill align-middle"></i></button><ul class="dropdown-menu dropdown-menu-end">' +
                    '<li><a class="dropdown-item edit-item-btn" id="edit'+data+'" onclick="edit('+data+')"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> <?=$languageArray['edit_code'][$language] ?></a></li>' +
                    '<li><a class="dropdown-item remove-item-btn" id="deactivate'+data+'" onclick="deactivate('+data+')"><i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> <?=$languageArray['delete_code'][$language] ?> </a></li></ul></div>';
                }
            }
        ]       
    });
    
    $('#submitMessage').on('click', function(){
        if($('#messageForm').valid()){
            $('#spinnerLoading').show();
            $.post('php/modules/message/message.php', $('#messageForm').serialize(), function(data){
                var obj = JSON.parse(data);
                if(obj.status === 'success') {
                    table.ajax.reload();
                    $('#spinnerLoading').hide();
                    $('#messageModal').modal('hide');
                    toastr["success"](obj.message, "Success:");
                }
                else if(obj.status === 'failed') {
                    $('#spinnerLoading').hide();
                    toastr["error"](obj.message, "Failed:");
                }
                else {}
            });
        }
        // }
    });

    $('#addMessage').on('click', function(){
        $('#messageModal').find('#keyId').val('');
        $('#messageModal').find('#keyCode').val('');
        $('#messageModal').find('#englishDecs').val('');
        $('#messageModal').find('#chineseDecs').val('');
        $('#messageModal').find('#malayDecs').val('');
        $('#messageModal').find('#nepaliDecs').val('');
        $('#messageModal').modal('show');

        // Remove Validation Error Message
        $('#messageModal .is-invalid').removeClass('is-invalid');
        $('#messageModal').modal('show');
        
        $('#messageForm').validate({
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
});

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/modules/message/getMessage.php', {messageId: id}, function(data) {
        var decode = JSON.parse(data);

        if(decode.status === 'success'){
            $('#messageModal').find('#keyId').val(decode.message.id);
            $('#messageModal').find('#keyCode').val(decode.message.message_key_code);
            $('#messageModal').find('#englishDecs').val(decode.message.en);
            $('#messageModal').find('#chineseDecs').val(decode.message.zh);
            $('#messageModal').find('#malayDecs').val(decode.message.my);
            $('#messageModal').find('#nepaliDecs').val(decode.message.ne);

            // Remove Validation Error Message
            $('#messageModal .is-invalid').removeClass('is-invalid');
            $('#messageModal').modal('show');
            $('#spinnerLoading').hide();
        }
        else if(decode.status === 'failed'){
            $('#spinnerLoading').hide();
            toastr["error"](decode.message, "Failed:");
        }
        else{
            $('#spinnerLoading').hide();
            toastr["error"](decode.message, "Failed:");
        }
        
    });
}

function deactivate(id){
    $('#spinnerLoading').show();
    if (confirm('Are you sure you want to delete this message resource?')) {
        $.post('php/modules/message/deleteMessage.php', {messageId: id}, function(data){
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