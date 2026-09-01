<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
// Initialize the session
//session_start();
// Include config file
require_once "layouts/config.php";

// Check if the user is already logged in, if yes then redirect him to index page
$user = $_SESSION['id'];
$id = '1';
$stmt2 = $link->prepare("SELECT company_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no from Company where id = ?");
mysqli_stmt_bind_param($stmt2, "s", $id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_store_result($stmt2);
mysqli_stmt_bind_result($stmt2, $company_reg_no, $name, $address_line_1, $address_line_2, $address_line_3, $phone_no, $fax_no);

if (mysqli_stmt_fetch($stmt2)) {
    $usercompany_reg_no = $company_reg_no;
    $username = $name;
    $useraddress_line_1 = $address_line_1;
    $useraddress_line_2 = $address_line_2;
    $useraddress_line_3 = $address_line_3;
    $userphone_no = $phone_no;
    $userfax_no = $fax_no;
}

$role = 'NORMAL';
if ($user != null && $user != ''){
    $stmt3 = $link->prepare("SELECT * from Users WHERE id = ?");
    $stmt3->bind_param('s', $user);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
        
    if(($row3 = $result3->fetch_assoc()) !== null){
        $role = $row3['role'];
    }
}

$readonly = '';
$hidden = false;
if ($role != 'SADMIN'){
    $readonly = 'readonly';
    $hidden = true;
}

?>

    <head>
        
        <title>Company Profile | Synctronix - Weighing System</title>
        <?php include 'layouts/title-meta.php'; ?>

        <!-- swiper css -->
        <link rel="stylesheet" href="assets/libs/swiper/swiper-bundle.min.css">

        <?php include 'layouts/head-css.php'; ?>

    </head>

    <?php include 'layouts/body.php'; ?>

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include 'layouts/menu.php'; ?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
                        <div class="row col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <form action="php/updateCompany.php" method="post">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="companyRegNo" class="col-sm-4 col-form-label"><?=$languageArray['company_reg_no_code'][$language]?> *</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control input-readonly" id="companyRegNo" name="companyRegNo" placeholder="<?=$languageArray['search_code'][$language]?>Company Reg No" value="<?=$usercompany_reg_no ?>" required <?= $readonly ?>>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="companyName" class="col-sm-4 col-form-label"><?=$languageArray['company_name_code'][$language]?>Company Name *</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control input-readonly" id="companyName" name="companyName" placeholder="Company Name" value="<?=$username ?>" required <?= $readonly ?>>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="companyAddress" class="col-sm-4 col-form-label"><?=$languageArray['company_address_code'][$language]?> 1 *</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control input-readonly" id="companyAddress" name="companyAddress" placeholder="<?=$languageArray['company_address_code'][$language]?> 1" value="<?=$useraddress_line_1 ?>" required <?= $readonly ?>>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="companyAddress2" class="col-sm-4 col-form-label"><?=$languageArray['company_address_code'][$language]?> 2</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control input-readonly" id="companyAddress2" name="companyAddress2" placeholder="<?=$languageArray['company_address_code'][$language]?> 2" value="<?=$useraddress_line_2 ?>" <?= $readonly ?>>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="companyAddress3" class="col-sm-4 col-form-label"><?=$languageArray['company_address_code'][$language]?> 3</label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control input-readonly" id="companyAddress3" name="companyAddress3" placeholder="<?=$languageArray['company_address_code'][$language]?> 3" value="<?=$useraddress_line_3 ?>" <?= $readonly ?>>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="companyPhone" class="col-sm-4 col-form-label"><?=$languageArray['company_phone_code'][$language]?></label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control input-readonly" id="companyPhone" name="companyPhone" placeholder="<?=$languageArray['company_phone_code'][$language]?>" value="<?=$userphone_no ?>" required <?= $readonly ?>>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="companyFax" class="col-sm-4 col-form-label"><?=$languageArray['company_fax_code'][$language]?></label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control input-readonly" id="companyFax" name="companyFax" placeholder="<?=$languageArray['company_fax_code'][$language]?>" value="<?=$userfax_no ?>" <?= $readonly ?>>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4" <?= $hidden ? 'style="display:none;"' : '' ?>>
                                                <button class="btn btn-success w-100" type="submit"><?=$languageArray['update_code'][$language]?></button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div><!-- container-fluid -->
                </div><!-- End Page-content -->

                <?php include 'layouts/footer.php'; ?>
            </div><!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

        <?php include 'layouts/customizer.php'; ?>

        <?php include 'layouts/vendor-scripts.php'; ?>

        <!-- swiper js -->
        <script src="assets/libs/swiper/swiper-bundle.min.js"></script>

        <!-- profile init js -->
        <script src="assets/js/pages/profile.init.js"></script>
        
        <!-- App js -->
        <script src="assets/js/app.js"></script>
    </body>
</html>