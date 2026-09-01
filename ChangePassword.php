<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

    <head>
        
        <title>Change Password | Synctronix - Weighing System</title>
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
                                    <form action="php/changepassword.php" method="post">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="transactionId" class="col-sm-4 col-form-label"><?=$languageArray['old_password_code'][$language]?></label>
                                                    <div class="col-sm-8 ">
                                                        <input type="password" class="form-control" id="oldPassword" name="oldPassword" placeholder="<?=$languageArray['old_password_code'][$language]?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="transactionDate" class="col-sm-4 col-form-label"><?=$languageArray['new_password_code'][$language]?></label>
                                                    <div class="col-sm-8">
                                                        <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="<?=$languageArray['new_password_code'][$language]?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="transactionDate" class="col-sm-4 col-form-label"><?=$languageArray['confirm_password_code'][$language]?></label>
                                                    <div class="col-sm-8">
                                                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="<?=$languageArray['confirm_password_code'][$language]?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <button class="btn btn-success w-100" type="submit"><?=$languageArray['submit_code'][$language]?></button>
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