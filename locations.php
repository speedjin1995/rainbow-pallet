<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
    include 'php/db_connect.php';

    $plant = $db->query("SELECT * FROM Plant WHERE status = '0'");
?>
<head>
    <title>Weighing | Synctronix - Weighing System</title>
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
                            
                            <button type="button" hidden id="successBtn" data-toast data-toast-text="Welcome Back ! This is a Toast Notification" data-toast-gravity="top" data-toast-position="center" data-toast-duration="3000" data-toast-close="close" class="btn btn-light w-xs">Top Center</button>
                            <button type="button" hidden id="failBtn" data-toast data-toast-text="Welcome Back ! This is a Toast Notification" data-toast-gravity="top" data-toast-position="center" data-toast-duration="3000" data-toast-close="close" class="btn btn-light w-xs">Top Center</button>

                            <div class="row">
                                <div class="col-xl-3 col-md-6 add-new-weight">

                                    <!-- /.modal-dialog -->
                                    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-scrollable modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalScrollableTitle"><?=$languageArray['add_new_location_code'][$language]?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form role="form" id="locationForm" class="needs-validation" novalidate autocomplete="off">
                                                        <div class=" row col-12">
                                                            <div class="col-xxl-12 col-lg-12">
                                                                <div class="card bg-light">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="locationCode" class="col-sm-4 col-form-label"><?=$languageArray['location_code_code'][$language]?> *</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="locationCode" name="locationCode" placeholder="Location Code" required>
                                                                                        <div class="invalid-feedback">
                                                                                            Please fill in the field.
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="locationName" class="col-sm-4 col-form-label"><?=$languageArray['location_name_code'][$language]?> *</label>
                                                                                    <div class="col-sm-8">
                                                                                        <input type="text" class="form-control" id="locationName" name="locationName" placeholder="Location Name" required>
                                                                                        <div class="invalid-feedback">
                                                                                            Please fill in the field.
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="plant" class="col-sm-4 col-form-label"><?=$languageArray['plant_code'][$language]?> *</label>
                                                                                    <div class="col-sm-8">
                                                                                        <select id="plant" name="plant" class="form-select select2" required>
                                                                                            <?php while($rowPlant=mysqli_fetch_assoc($plant)){ ?>
                                                                                                <option value="<?=$rowPlant['id'] ?>"><?=$rowPlant['name'] ?></option>
                                                                                            <?php } ?>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-xxl-12 col-lg-12 mb-3">
                                                                                <div class="row">
                                                                                    <label for="weighingCount" class="col-sm-4 col-form-label"><?=$languageArray['weighing_count_code'][$language]?> *</label>
                                                                                    <div class="col-sm-8">
                                                                                        <select class="form-control" id="weighingCount" name="weighingCount" required>
                                                                                            <option value="1">1</option>
                                                                                            <option value="2" selected>2</option>
                                                                                        </select>
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
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                                <button type="button" class="btn btn-success" id="submitLocation"><?=$languageArray['submit_code'][$language]?></button>
                                                            </div>
                                                        </div><!--end col-->                                                               
                                                    </form>
                                                </div>
                                            </div><!-- /.modal-content -->
                                        </div><!-- /.modal-dialog -->
                                    </div><!-- /.modal -->
                                </div>
                            </div> <!-- end row-->

                            <!-- Port Setup Modal -->
                            <div class="modal fade" id="portSetupModal" tabindex="-1" role="dialog" aria-labelledby="portSetupModalTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="portSetupModalTitle"><?=$languageArray['port_setup_code'][$language]?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form role="form" id="portSetupForm" autocomplete="off">
                                                <div class="row col-12">
                                                    <div class="col-xxl-12 col-lg-12">
                                                        <div class="card bg-light">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-xxl-12 col-lg-12 mb-3">
                                                                        <div class="row">
                                                                            <label for="indicator" class="col-sm-4 col-form-label"><?=$languageArray['indicator_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control" style="width: 100%;" id="indicator" name="indicator">
                                                                                    <option value="BX23">BAYKON BX23</option>
                                                                                    <option value="X2S">SYNCTRONIX X2S</option>
                                                                                    <option value="X722">SYNCTRONIX X722</option>
                                                                                    <option value="205">CARDINAL STORM 205</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-xxl-12 col-lg-12 mb-3">
                                                                        <div class="row">
                                                                            <label for="serialPort" class="col-sm-4 col-form-label"><?=$languageArray['serial_port_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control" style="width: 100%;" id="serialPort" name="serialPort"></select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-xxl-12 col-lg-12 mb-3">
                                                                        <div class="row">
                                                                            <label for="serialPortBaudRate" class="col-sm-4 col-form-label"><?=$languageArray['baud_rate_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control" style="width: 100%;" id="serialPortBaudRate" name="serialPortBaudRate">
                                                                                    <option value="110">110</option>
                                                                                    <option value="300">300</option>
                                                                                    <option value="600">600</option>
                                                                                    <option value="1200">1200</option>
                                                                                    <option value="2400">2400</option>
                                                                                    <option value="4800">4800</option>
                                                                                    <option value="9600">9600</option>
                                                                                    <option value="14400">14400</option>
                                                                                    <option value="19200">19200</option>
                                                                                    <option value="38400">38400</option>
                                                                                    <option value="57600">57600</option>
                                                                                    <option value="115200">115200</option>
                                                                                    <option value="128000">128000</option>
                                                                                    <option value="256000">256000</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-xxl-12 col-lg-12 mb-3">
                                                                        <div class="row">
                                                                            <label for="serialPortDataBits" class="col-sm-4 col-form-label"><?=$languageArray['data_bits_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control" style="width: 100%;" id="serialPortDataBits" name="serialPortDataBits">
                                                                                    <option value="8">8</option>
                                                                                    <option value="7">7</option>
                                                                                    <option value="6">6</option>
                                                                                    <option value="5">5</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-xxl-12 col-lg-12 mb-3">
                                                                        <div class="row">
                                                                            <label for="serialPortParity" class="col-sm-4 col-form-label"><?=$languageArray['parity_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control" style="width: 100%;" id="serialPortParity" name="serialPortParity">
                                                                                    <option value="N">None</option>
                                                                                    <option value="O">Odd</option>
                                                                                    <option value="E">Even</option>
                                                                                    <option value="M">Mark</option>
                                                                                    <option value="S">Space</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-xxl-12 col-lg-12 mb-3">
                                                                        <div class="row">
                                                                            <label for="serialPortStopBits" class="col-sm-4 col-form-label"><?=$languageArray['stop_bits_code'][$language]?></label>
                                                                            <div class="col-sm-8">
                                                                                <select class="form-control" style="width: 100%;" id="serialPortStopBits" name="serialPortStopBits">
                                                                                    <option value="1">1</option>
                                                                                    <option value="1.5">1.5</option>
                                                                                    <option value="2">2</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <input type="hidden" id="portId" name="id">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="hstack gap-2 justify-content-end">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?=$languageArray['close_code'][$language]?></button>
                                                        <button type="button" class="btn btn-success" id="submitPortSetup"><?=$languageArray['submit_code'][$language]?></button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end Port Setup Modal -->

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
                                                                <button type="button" id="addLocation" class="btn btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                                                    <i class="ri-add-circle-line align-middle me-1"></i>
                                                                    <?=$languageArray['add_new_code'][$language]?>
                                                                </button>
                                                            </div> 
                                                        </div> 
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="locationTable" class="table table-bordered nowrap table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <tr>
                                                                    <th><input type="checkbox" id="selectAllCheckbox" class="selectAllCheckbox"></th>
                                                                    <th><?=$languageArray['location_code_code'][$language]?></th>
                                                                    <th><?=$languageArray['location_name_code'][$language]?></th>
                                                                    <th><?=$languageArray['weighing_count_code'][$language]?></th>
                                                                    <th><?=$languageArray['plant_code'][$language]?></th>
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
    <script src="plugins/select2/js/select2.full.min.js"></script>
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
    debugger;

    $.post('http://127.0.0.1:5002/getcomport', function(data){
        var decoded = JSON.parse(data);
        var options = '';

        for (var i = 0; i < decoded.length; i++) {
            options += '<option value="' + decoded[i] + '">' + decoded[i] + '</option>';
        }

        $('#serialPort').html(options);
    });

    $('#selectAllCheckbox').on('change', function() {
        var checkboxes = $('#locationTable tbody input[type="checkbox"]');
        checkboxes.prop('checked', $(this).prop('checked')).trigger('change');
    });

    table = $("#locationTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url':'php/modules/locations/loadLocations.php'
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
            { data: 'location_code' },
            { data: 'location_name' },
            { data: 'weighing_count' },
            { data: 'plant' },
            { 
                data: 'id',
                render: function ( data, type, row ) {
                    return '<div class="dropdown d-inline-block"><button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
                    '<i class="ri-more-fill align-middle"></i></button><ul class="dropdown-menu dropdown-menu-end">' +
                    '<li><a class="dropdown-item edit-item-btn" id="edit'+data+'" onclick="edit('+data+')"><i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit Location</a></li>' +
                    '<li><a class="dropdown-item" id="portSetup'+data+'" onclick="openPortSetup('+data+')"><i class="ri-settings-3-line align-bottom me-2 text-muted"></i> Port Setup</a></li>' +
                    '<li><a class="dropdown-item remove-item-btn" id="deactivate'+data+'" onclick="deactivate('+data+')"><i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete </a></li></ul></div>';
                }
            }
        ]       
    });
    
    $('#submitLocation').on('click', function(){
        if($('#locationForm').valid()){
            $('#spinnerLoading').show();
            $.post('php/modules/locations/locations.php', $('#locationForm').serialize(), function(data){
                var obj = JSON.parse(data); 
                if(obj.status === 'success'){
                    table.ajax.reload();
                    $('#spinnerLoading').hide();
                    $('#addModal').modal('hide');
                    $("#successBtn").attr('data-toast-text', obj.message);
                    $("#successBtn").click();
                }
                else if(obj.status === 'failed'){
                    $('#spinnerLoading').hide();
                    $("#failBtn").attr('data-toast-text', obj.message );
                    $("#failBtn").click();
                }
                else{
                    $('#spinnerLoading').hide();
                    $("#failBtn").attr('data-toast-text', 'Something wrong when saving!');
                    $("#failBtn").click();
                }
            });
        }
        else{
            alert('Please filled in all the mandatory fields!!!');
        }
    });

    $('#submitPortSetup').on('click', function(){
        $('#spinnerLoading').show();
        $.post('php/modules/locations/savePortSetup.php', $('#portSetupForm').serialize(), function(data){
            var obj = JSON.parse(data);
            if(obj.status === 'success'){
                table.ajax.reload();
                $('#spinnerLoading').hide();
                $('#portSetupModal').modal('hide');
                $("#successBtn").attr('data-toast-text', obj.message);
                $("#successBtn").click();
            }
            else{
                $('#spinnerLoading').hide();
                $("#failBtn").attr('data-toast-text', obj.message || 'Something wrong when saving!');
                $("#failBtn").click();
            }
        });
    });

    $('#addLocation').on('click', function(){
        $('#addModal').find('#id').val("");
        $('#addModal').find('#locationCode').val("");
        $('#addModal').find('#locationName').val("");
        $('#addModal').find('#plant').val("");
        $('#addModal').find('#weighingCount').val("2");

        // Remove Validation Error Message
        $('#addModal .is-invalid').removeClass('is-invalid');

        $('#addModal').modal('show');
        
        $('#locationForm').validate({
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
    $.post('php/modules/locations/getLocation.php', {userID: id}, function(data)
    {
        var obj = JSON.parse(data);
        if(obj.status === 'success'){
            $('#addModal').find('#id').val(obj.message.id);
            $('#addModal').find('#locationCode').val(obj.message.location_code);
            $('#addModal').find('#locationName').val(obj.message.location_name);
            $('#addModal').find('#plant').val(obj.message.plant_id);
            $('#addModal').find('#weighingCount').val(obj.message.weighing_count);

            // Remove Validation Error Message
            $('#addModal .is-invalid').removeClass('is-invalid');
            $('#addModal').modal('show');
        }
        else if(obj.status === 'failed'){
            $('#spinnerLoading').hide();
            $("#failBtn").attr('data-toast-text', obj.message );
            $("#failBtn").click();
        }
        else{
            $('#spinnerLoading').hide();
            $("#failBtn").attr('data-toast-text', obj.message );
            $("#failBtn").click();
        }
        $('#spinnerLoading').hide();
    });
}

function openPortSetup(id){
    $('#spinnerLoading').show();
    $.post('php/modules/locations/getLocation.php', {userID: id, port: 'Y'}, function(data)
    {
        var obj = JSON.parse(data);
        if(obj.status === 'success'){
            $('#portId').val(obj.message.port_id);
            $('#indicator').val(obj.message.indicator);
            $('#serialPort').val(obj.message.com_port);
            $('#serialPortBaudRate').val(obj.message.bits_per_second);
            $('#serialPortDataBits').val(obj.message.data_bits);
            $('#serialPortParity').val(obj.message.parity);
            $('#serialPortStopBits').val(obj.message.stop_bits);
            $('#portSetupModal').modal('show');
        }
        else{
            $("#failBtn").attr('data-toast-text', obj.message);
            $("#failBtn").click();
        }
        $('#spinnerLoading').hide();
    });
}

function deactivate(id){
    $('#spinnerLoading').show();
    if (confirm('Are you sure you want to cancel this item?')) {
        $.post('php/modules/locations/deleteLocation.php', {userID: id}, function(data){
            var obj = JSON.parse(data);
            
            if(obj.status === 'success'){
                table.ajax.reload();
                $('#spinnerLoading').hide();
                $("#successBtn").attr('data-toast-text', obj.message);
                $("#successBtn").click();
            }
            else if(obj.status === 'failed'){
                $('#spinnerLoading').hide();
                $("#failBtn").attr('data-toast-text', obj.message );
                $("#failBtn").click();
            }
            else{
                $('#spinnerLoading').hide();
                $("#failBtn").attr('data-toast-text', obj.message );
                $("#failBtn").click();
            }
        });
    }
    $('#spinnerLoading').hide();
}

$('#locationForm').validate({
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
</script>
</body>

</html>