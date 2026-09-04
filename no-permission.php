<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<head>
    <title><?=$languageArray['access_denied_code'][$language]?> | PWS - Weighing System</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>
<?php include 'layouts/body.php'; ?>
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <div class="card" style="min-height: calc(100vh - 200px);">
                            <div class="card-body p-5 d-flex flex-column justify-content-center align-items-center">
                                <i class="mdi mdi-lock-outline text-secondary" style="font-size: 80px;"></i>
                                <h3 class="mt-3"><?=$languageArray['access_denied_code'][$language]?></h3>
                                <p class="text-muted"><?=$languageArray['no_permission_code'][$language]?></p>
                                <a href="index.php" class="btn btn-secondary mt-3"><i class="mdi mdi-home"></i> <?=$languageArray['back_to_weighing_code'][$language]?></a>
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
<script src="assets/js/app.js"></script>
