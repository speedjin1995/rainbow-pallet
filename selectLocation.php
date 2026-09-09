<?php
session_start();
require_once 'php/db_connect.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if user has plant_id assigned
if(!isset($_SESSION['plant_id']) || empty($_SESSION['plant_id'])) {
    // Show error page
    include 'layouts/head-main.php';
    ?>
    <head>
        <title>Access Error | Synctronix - Weighing System</title>
        <?php include 'layouts/title-meta.php'; ?>
        <?php include 'layouts/head-css.php'; ?>
    </head>
    <?php include 'layouts/body.php'; ?>
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                    <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
                </svg>
            </div>
        </div>
        <div class="auth-page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <a href="index.php" class="d-inline-block auth-logo">
                                <img src="assets/images/logo-lg.png" alt="" width="50%">
                            </a>
                            <p class="mt-3 fs-15 fw-medium">Synctronix - Weighing System</p>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4">
                            <div class="card-body p-4 text-center">
                                <div class="avatar-lg mx-auto mt-2">
                                    <div class="avatar-title bg-light text-danger display-3 rounded-circle">
                                        <i class="ri-error-warning-fill"></i>
                                    </div>
                                </div>
                                <div class="mt-4 pt-2">
                                    <h4 class="text-danger">Access Denied</h4>
                                    <p class="text-muted">No plant has been assigned to your account.</p>
                                    <p class="text-muted">Please contact your <strong>IT Administrator</strong> to assign a plant to your user account.</p>
                                    <div class="mt-4">
                                        <a href="php/logout.php" class="btn btn-danger w-100"><i class="ri-logout-box-line me-1"></i> Logout</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Weighing System. Crafted by Synctronix</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <?php include 'layouts/vendor-scripts.php'; ?>
    <script src="assets/libs/particles.js/particles.js"></script>
    <script src="assets/js/pages/particles.app.js"></script>
    </body>
    </html>
    <?php
    exit;
}

$plant_ids = implode(',', array_map('intval', $_SESSION['plant_id']));
$plants = $db->query("SELECT * FROM Plant WHERE status = '0' AND id IN ($plant_ids)");

// Build locations array grouped by plant_id for JavaScript
$locationsResult = $db->query("SELECT id, plant_id, location_code, location_name FROM Location WHERE status = 0 AND plant_id IN ($plant_ids)");
$locationsByPlant = [];
while ($loc = $locationsResult->fetch_assoc()) {
    $locationsByPlant[$loc['plant_id']][] = $loc;
}
?>
<?php include 'layouts/head-main.php'; ?>
<head>
    <title>Select Location | Synctronix - Weighing System</title>
    <?php include 'layouts/title-meta.php'; ?>
    <?php include 'layouts/head-css.php'; ?>
</head>
<?php include 'layouts/body.php'; ?>

<div class="auth-page-wrapper pt-5">
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
        <div class="bg-overlay"></div>
        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
            </svg>
        </div>
    </div>

    <div class="auth-page-content">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white-50">
                        <a href="index.php" class="d-inline-block auth-logo">
                            <img src="assets/images/logo-lg.png" alt="" width="50%">
                        </a>
                        <p class="mt-3 fs-15 fw-medium">Synctronix - Weighing System</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card mt-4">
                        <div class="card-body p-4">
                            <div class="text-center mt-2">
                                <h5 class="text-primary">Select Location</h5>
                                <p class="text-muted">Choose your location to continue.</p>
                            </div>
                            <?php if(isset($_GET['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php 
                                    if($_GET['error'] == 'choose_plant') echo 'Please select a plant.';
                                    elseif($_GET['error'] == 'choose_location') echo 'Please select a location.';
                                    else echo 'An error occurred. Please try again.';
                                ?>
                            </div>
                            <?php endif; ?>
                            <div class="p-2 mt-4">
                                <form method="POST" action="php/modules/locations/select_location_process.php">
                                    <div class="mb-3">
                                        <label class="form-label">Plant</label>
                                        <select name="plant" id="plantSelect" class="form-select" required>
                                            <option value="">-- Choose Plant --</option>
                                            <?php foreach($plants as $plant): ?>
                                                <option value="<?= $plant['id']; ?>">
                                                    <?= $plant['name']; ?> (<?= $plant['plant_code']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Location</label>
                                        <select name="location_id" id="locationSelect" class="form-select" required disabled>
                                            <option value="">-- Choose Plant First --</option>
                                        </select>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-success w-100">Continue</button>
                                    </div>
                                    <div class="mt-2">
                                        <a href="php/logout.php" class="btn btn-outline-secondary w-100">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">
                        <p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Weighing System. Crafted by Synctronix</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/libs/particles.js/particles.js"></script>
<script src="assets/js/pages/particles.app.js"></script>
<script>
var locationsByPlant = <?= json_encode($locationsByPlant) ?>;

$(document).ready(function() {
    $('#plantSelect').on('change', function() {
        var plantId = $(this).val();
        var $locationSelect = $('#locationSelect');
        
        $locationSelect.empty();
        
        if (plantId && locationsByPlant[plantId]) {
            $locationSelect.append('<option value="">-- Choose Location --</option>');
            locationsByPlant[plantId].forEach(function(loc) {
                $locationSelect.append('<option value="' + loc.id + '">' + loc.location_name + ' (' + loc.location_code + ')</option>');
            });
            $locationSelect.prop('disabled', false);
        } else {
            $locationSelect.append('<option value="">-- Choose Plant First --</option>');
            $locationSelect.prop('disabled', true);
        }
    });
});
</script>
</body>
</html>
