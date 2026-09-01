<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
// Initialize the session
//session_start();
// Include config file
require_once "layouts/config.php";

// Check if the user is already logged in, if yes then redirect him to index page
$id = $_SESSION['id'];
$stmt2 = $link->prepare("SELECT username, useremail, languages from Users where id = ?");
mysqli_stmt_bind_param($stmt2, "s", $id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_store_result($stmt2);
mysqli_stmt_bind_result($stmt2, $name, $email, $languages);

if (mysqli_stmt_fetch($stmt2)) {
    $useremail = $email;
    $username = $name;
    $language = $languages;
}
?>

    <head>
        
        <title>My Profile | Synctronix - Weighing System</title>
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
                                    <form action="php/updateProfile.php" method="post">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="transactionId" class="col-sm-4 col-form-label"><?=$languageArray['email_code'][$language]?></label>
                                                    <div class="col-sm-8 ">
                                                        <input type="email" class="form-control" id="email" name="userEmail" placeholder="<?=$languageArray['email_code'][$language]?>" value="<?=$useremail ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="transactionDate" class="col-sm-4 col-form-label"><?=$languageArray['username_code'][$language]?></label>
                                                    <div class="col-sm-8">
                                                        <input type="text" class="form-control" id="username" name="userName" placeholder="<?=$languageArray['download_template_code'][$language]?>" value="<?=$username ?>" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="row">
                                                    <label for="transactionDate" class="col-sm-4 col-form-label"><?=$languageArray['language_code'][$language]?></label>
                                                    <div class="col-sm-8">
                                                        <select class="form-control" style="width: 100%;" id="language" name="language" required>
                                                            <option value="en" <?= ($language == 'en') ? 'selected' : '' ?>>English</option>
                                                            <option value="zh" <?= ($language == 'zh') ? 'selected' : '' ?>>Chinese</option>
                                                            <option value="my" <?= ($language == 'my') ? 'selected' : '' ?>>Bahasa Malaysia</option>
                                                            <option value="ne" <?= ($language == 'ne') ? 'selected' : '' ?>>नेपाली</option>
                                                        </select>
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