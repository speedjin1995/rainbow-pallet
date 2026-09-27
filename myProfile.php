<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php
require_once "layouts/config.php";

$id = $_SESSION['id'];
$stmt2 = $link->prepare("SELECT username, useremail, languages from Users where id = ?");
mysqli_stmt_bind_param($stmt2, "s", $id);
mysqli_stmt_execute($stmt2);
mysqli_stmt_store_result($stmt2);
mysqli_stmt_bind_result($stmt2, $name, $email, $languages);

if (mysqli_stmt_fetch($stmt2)) {
    $useremail = $email;
    $username = $name;
    $userLanguage = $languages;
}
?>

<head>
    <title><?=$languageArray['my_profile_code'][$language] ?? 'My Profile'?> | Synctronix - Weighing System</title>
    <?php include 'layouts/title-meta.php'; ?>
    <link rel="stylesheet" href="assets/libs/swiper/swiper-bundle.min.css">
    <script src="plugins/jquery/jquery.min.js"></script>
    <?php include 'layouts/head-css.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><?=$languageArray['profile_info_code'][$language] ?? 'Profile Information'?></h5>
                            </div>
                            <div class="card-body">
                                <form id="profileForm">
                                    <div class="mb-3">
                                        <label for="userEmail" class="form-label"><?=$languageArray['email_code'][$language]?></label>
                                        <input type="email" class="form-control" id="userEmail" name="userEmail" value="<?=$useremail?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="userName" class="form-label"><?=$languageArray['username_code'][$language]?></label>
                                        <input type="text" class="form-control" id="userName" name="userName" value="<?=$username?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label for="language" class="form-label"><?=$languageArray['language_code'][$language]?></label>
                                        <select class="form-control" id="language" name="language" required>
                                            <option value="en" <?=($userLanguage == 'en') ? 'selected' : ''?>>English</option>
                                            <option value="zh" <?=($userLanguage == 'zh') ? 'selected' : ''?>>Chinese</option>
                                            <option value="my" <?=($userLanguage == 'my') ? 'selected' : ''?>>Bahasa Malaysia</option>
                                            <option value="ne" <?=($userLanguage == 'ne') ? 'selected' : ''?>>नेपाली</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100"><?=$languageArray['update_profile_code'][$language] ?? 'Update Profile'?></button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><?=$languageArray['change_password_code'][$language] ?? 'Change Password'?></h5>
                            </div>
                            <div class="card-body">
                                <form id="changePasswordForm">
                                    <div class="mb-3">
                                        <label for="oldPassword" class="form-label"><?=$languageArray['old_password_code'][$language]?></label>
                                        <input type="password" class="form-control" id="oldPassword" name="oldPassword" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newPassword" class="form-label"><?=$languageArray['new_password_code'][$language]?></label>
                                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirmPassword" class="form-label"><?=$languageArray['confirm_password_code'][$language]?></label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100"><?=$languageArray['change_password_code'][$language] ?? 'Change Password'?></button>
                                </form>
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
<script src="assets/js/app.js"></script>

<script>
$(function() {
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        $.post('php/modules/user/index.php', $(this).serialize() + '&action=updateProfile', function(data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                toastr['success'](obj.message, 'Success:');
                location.reload();
            } else {
                toastr['error'](obj.message, 'Failed:');
            }
        });
    });

    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        if ($('#newPassword').val() !== $('#confirmPassword').val()) {
            toastr['error']('New password and confirm password do not match', 'Failed:');
            return;
        }
        $.post('php/modules/user/index.php', $(this).serialize() + '&action=changePassword', function(data) {
            var obj = JSON.parse(data);
            if (obj.status === 'success') {
                toastr['success'](obj.message, 'Success:');
                $('#changePasswordForm')[0].reset();
            } else {
                toastr['error'](obj.message, 'Failed:');
            }
        });
    });
});
</script>

</body>
</html>
