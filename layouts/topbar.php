<?php
## Fetch records
// Lorry SQL
$pendingLorryColumns = "id, transaction_id, transaction_status, weight_type, container_no";
$lorryWeighingSQL = "(select $pendingLorryColumns from Weight where status = '0' AND is_complete = 'N' AND is_cancel='N') UNION ALL (select $pendingLorryColumns from Weight_Container where status = '0' AND is_complete = 'N' AND is_cancel='N')";
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
    $username = implode("', '", $_SESSION["plant"]);
    $lorryWeighingSQL = "(select $pendingLorryColumns from Weight where status = '0' AND is_complete = 'N' AND is_cancel='N' AND plant_code IN ('$username')) UNION ALL (select $pendingLorryColumns from Weight_Container where status = '0' AND is_complete = 'N' AND is_cancel='N' AND plant_code IN ('$username'))";
}
$normalWeighing = $db->query($lorryWeighingSQL);

// Container SQL
$containerWeighingSQL = "select * from Weight_Container where status = '0' AND is_complete = 'Y' AND is_cancel='N'";
if($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN'){
    $username = implode("', '", $_SESSION["plant"]);
    $containerWeighingSQL = "select * from Weight_Container where status = '0' AND is_complete = 'Y' AND is_cancel='N' AND plant_code IN ('$username')";
}
$containerWeighing = $db->query($containerWeighingSQL);

$weighing2 = $db->query("SELECT * FROM Weight WHERE is_approved = 'N'");
# Lorry
$salesList = array();
$purchaseList = array();
$localList = array();
$miscList = array();
$count = 0;
# Container
$salesContainerList = array();
$purchaseContainerList = array();
$localContainerList = array();
$miscContainerList = array();
$containerCount = 0;

$salesList2 = array();
$purchaseList2 = array();
$localList2 = array();
$miscList2 = array();
$count2 = 0;

while($row=mysqli_fetch_assoc($normalWeighing)){
    $weightType = '';
    if ($row['weight_type'] == 'Empty Container') {
        $weightType = 'Primer Mover + Container';
    } elseif ($row['weight_type'] == 'Container') {
        $weightType = 'Primer Mover';
    } else if($row['weight_type'] == 'Different Container'){
        $weightType = 'Primer Mover + Different Bins';
    } else {
        $weightType = $row['weight_type'];
    }

    if($row['transaction_status'] == 'Sales'){
        $salesList[] = array(
            "id" => $row['id'],
            "transaction_id" => $row['transaction_id'],
            "weight_type" => $weightType
        );
    }
    else if($row['transaction_status'] == 'Purchase'){
        $purchaseList[] = array(
            "id" => $row['id'],
            "transaction_id" => $row['transaction_id'],
            "weight_type" => $weightType
        );
    }
    else if($row['transaction_status'] == 'Local'){
        $localList[] = array(
            "id" => $row['id'],
            "transaction_id" => $row['transaction_id'],
            "weight_type" => $weightType
        );
    }
    else{
        $miscList[] = array(
            "id" => $row['id'],
            "transaction_id" => $row['transaction_id'],
            "weight_type" => $weightType
        );
    }
}

while($row3=mysqli_fetch_assoc($containerWeighing)){
    $weightType = ''; 
    if ($row3['weight_type'] == 'Empty Container') {
        $weightType = 'Primer Mover + Container';
    } else if($row3['weight_type'] == 'Different Container'){
        $weightType = 'Primer Mover + Different Bins';
    } elseif ($row3['weight_type'] == 'Container') {
        $weightType = 'Primer Mover';
    } else {
        $weightType = $row3['weight_type'];
    }

    if($row3['transaction_status'] == 'Sales'){
        $salesContainerList[] = array(
            "id" => $row3['id'],
            "transaction_id" => $row3['transaction_id'],
            "container_no" => $row3['container_no'],
            "weight_type" => $weightType
        );
    }
    else if($row3['transaction_status'] == 'Purchase'){
        $purchaseContainerList[] = array(
            "id" => $row3['id'],
            "transaction_id" => $row3['transaction_id'],
            "container_no" => $row3['container_no'],
            "weight_type" => $weightType
        );
    }
    else if($row3['transaction_status'] == 'Local'){
        $localContainerList[] = array(
            "id" => $row3['id'],
            "transaction_id" => $row3['transaction_id'],
            "container_no" => $row3['container_no'],
            "weight_type" => $weightType
        );
    }
    else{
        $miscContainerList[] = array(
            "id" => $row3['id'],
            "transaction_id" => $row3['transaction_id'],
            "container_no" => $row3['container_no'],
            "weight_type" => $weightType
        );
    }
}

while($row2=mysqli_fetch_assoc($weighing2)){
    if($row2['transaction_status'] == 'Sales'){
        $salesList2[] = array(
            "id" => $row2['id'],
            "transaction_id" => $row2['transaction_id'],
            "weight_type" => $row2['weight_type']
        );
    }
    else if($row2['transaction_status'] == 'Purchase'){
        $purchaseList2[] = array(
            "id" => $row2['id'],
            "transaction_id" => $row2['transaction_id'],
            "weight_type" => $row2['weight_type']
        );
    }
    else if($row2['transaction_status'] == 'Local'){
        $localList2[] = array(
            "id" => $row2['id'],
            "transaction_id" => $row2['transaction_id'],
            "weight_type" => $row2['weight_type']
        );
    }
    else{
        $miscList2[] = array(
            "id" => $row2['id'],
            "transaction_id" => $row2['transaction_id'],
            "weight_type" => $row2['weight_type']
        );
    }
}

$compids = '1';
$stmtComp = $db->prepare("SELECT * FROM Company WHERE id=?");
$stmtComp->bind_param('s', $compids);
$stmtComp->execute();
$resultC = $stmtComp->get_result();
$compname = '';
        
if ($rowc = $resultC->fetch_assoc()) {
    $compname = $rowc['name'];
}

$count = count($salesList) + count($purchaseList) + count($localList) + count($miscList);
$containerCount = count($salesContainerList) + count($purchaseContainerList) + count($localContainerList) + count($miscContainerList);
$count2 = count($salesList2) + count($purchaseList2) + count($localList2) + count($miscList2);
?>
<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex align-items-center">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="index.php" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="assets/images/logo-sm2.png" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="assets/images/logo-lg.png" alt="" height="17">
                        </span>
                    </a>

                    <a href="index.php" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="assets/images/logo-sm2.png" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="assets/images/logo-lg.png" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                    id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <h3><?=$compname ?></h3>
            </div>

            <div class="d-flex align-items-center">
                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                    <span class="fw-bold">LW</span>
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        aria-haspopup="true" aria-expanded="false">
                        <i class='bx bx-bell fs-22'></i>
                        <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger"><?=$count ?>
                        <span class="visually-hidden"><?=$languageArray['unread_messages_code'][$language] ?></span></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                        aria-labelledby="page-header-notifications-dropdown" style="width: 580px;">

                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white"> <?=$languageArray['pending_lorry_weighing_code'][$language] ?> </h6>
                                    </div>
                                    <div class="col-auto dropdown-tabs">
                                        <span class="badge badge-soft-light fs-13"> <?=$count ?> <?=$languageArray['new_code'][$language] ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="px-2 pt-2">
                                <ul class="nav nav-tabs dropdown-tabs nav-tabs-custom" data-dropdown-tabs="true"
                                    id="notificationItemsTab" role="tablist">
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#all-noti-tab" role="tab"
                                            aria-selected="true">
                                            <?=$languageArray['dispatch_code'][$language] ?> <?php echo (count($salesList) == 0 ? '' : '('.count($salesList).')'); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" href="#messages-tab" role="tab"
                                            aria-selected="false">
                                            <?=$languageArray['receiving_code'][$language] ?> <?php echo (count($purchaseList) == 0 ? '' : '('.count($purchaseList).')'); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" href="#alerts-tab" role="tab"
                                            aria-selected="false">
                                            <?=$languageArray['internal_transfer_code'][$language] ?> <?php echo (count($localList) == 0 ? '' : '('.count($localList).')'); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" href="#misc-tab" role="tab"
                                            aria-selected="false">
                                            <?=$languageArray['miscellaneous_code'][$language] ?> <?php echo (count($miscList) == 0 ? '' : '('.count($miscList).')'); ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                        </div>

                        <div class="tab-content position-relative" id="notificationItemsTabContent">
                            <div class="tab-pane fade show active py-2 ps-2" id="all-noti-tab" role="tabpanel">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php for($i=0; $i<count($salesList); $i++){ ?>
                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <div class="flex-1">
                                                    <a href="index.php?weight=<?=$salesList[$i]['id'] ?>" class="stretched-link">
                                                        <h6 class="mt-0 mb-2 lh-base">There is a <?=$salesList[$i]['weight_type'] ?> weighing with <b><?=$salesList[$i]['transaction_id'] ?></b>
                                                            is <span class="text-secondary">Pending</span>
                                                        </h6>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="tab-pane fade py-2 ps-2" id="messages-tab" role="tabpanel" aria-labelledby="messages-tab">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php for($i=0; $i<count($purchaseList); $i++){ ?>
                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <div class="flex-1">
                                                    <a href="index.php?weight=<?=$purchaseList[$i]['id'] ?>" class="stretched-link">
                                                        <h6 class="mt-0 mb-2 lh-base">There is a <?=$purchaseList[$i]['weight_type'] ?> weighing with <b><?=$purchaseList[$i]['transaction_id'] ?></b>
                                                            is <span class="text-secondary">Pending</span>
                                                        </h6>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="tab-pane fade py-2 ps-2" id="alerts-tab" role="tabpanel" aria-labelledby="alerts-tab">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php for($i=0; $i<count($localList); $i++){ ?>
                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <div class="flex-1">
                                                    <a href="index.php?weight=<?=$localList[$i]['id'] ?>" class="stretched-link">
                                                        <h6 class="mt-0 mb-2 lh-base">There is a <?=$localList[$i]['weight_type'] ?> weighing with <b><?=$localList[$i]['transaction_id'] ?></b>
                                                            is <span class="text-secondary">Pending</span>
                                                        </h6>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="tab-pane fade py-2 ps-2" id="misc-tab" role="tabpanel" aria-labelledby="misc-tab">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php for($i=0; $i<count($miscList); $i++){ ?>
                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <div class="flex-1">
                                                    <a href="index.php?weight=<?=$miscList[$i]['id'] ?>" class="stretched-link">
                                                        <h6 class="mt-0 mb-2 lh-base">There is a <?=$miscList[$i]['weight_type'] ?> weighing with <b><?=$miscList[$i]['transaction_id'] ?></b>
                                                            is <span class="text-secondary">Pending</span>
                                                        </h6>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="notification-actions" id="notification-actions">
                                <div class="d-flex text-muted justify-content-center">
                                    Select <div id="select-content" class="text-body fw-semibold px-1">0</div> Result
                                    <button type="button" class="btn btn-link link-danger p-0 ms-3"
                                        data-bs-toggle="modal" data-bs-target="#removeNotificationModal">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="cwNotificationDropdown">
                    <span class="fw-bold">CW</span>
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                        id="page-header-cw-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        aria-haspopup="true" aria-expanded="false">
                        <i class='bx bx-bell fs-22'></i>
                        <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger"><?=$containerCount ?>
                        <span class="visually-hidden"><?=$languageArray['unread_messages_code'][$language] ?></span></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                        aria-labelledby="page-header-cw-dropdown" style="width: 580px;">

                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white"> <?=$languageArray['pending_container_weighing_code'][$language] ?> </h6>
                                    </div>
                                    <div class="col-auto dropdown-tabs">
                                        <span class="badge badge-soft-light fs-13"> <?=$containerCount ?> <?=$languageArray['new_code'][$language] ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="px-2 pt-2">
                                <ul class="nav nav-tabs dropdown-tabs nav-tabs-custom" data-dropdown-tabs="true"
                                    id="cwNotificationItemsTab" role="tablist">
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#sales-cw-tab" role="tab"
                                            aria-selected="true">
                                            <?=$languageArray['dispatch_code'][$language] ?> <?php echo (count($salesContainerList) == 0 ? '' : '('.count($salesContainerList).')'); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" href="#purchase-cw-tab" role="tab"
                                            aria-selected="false">
                                            <?=$languageArray['receiving_code'][$language] ?> <?php echo (count($purchaseContainerList) == 0 ? '' : '('.count($purchaseContainerList).')'); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" href="#local-cw-tab" role="tab"
                                            aria-selected="false">
                                            <?=$languageArray['internal_transfer_code'][$language] ?> <?php echo (count($localContainerList) == 0 ? '' : '('.count($localContainerList).')'); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" href="#misc-cw-tab" role="tab"
                                            aria-selected="false">
                                            <?=$languageArray['miscellaneous_code'][$language] ?> <?php echo (count($miscContainerList) == 0 ? '' : '('.count($miscContainerList).')'); ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                        </div>

                        <div class="tab-content position-relative" id="notificationItemsTabContent">
                            <div class="tab-pane fade show active py-2 ps-2" id="sales-cw-tab" role="tabpanel">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php for($i=0; $i<count($salesContainerList); $i++){ ?>
                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <div class="flex-1">
                                                    <!-- <a href="index.php?weight=<?=$salesContainerList[$i]['id'] ?>" class="stretched-link"> -->
                                                        <h6 class="mt-0 mb-2 lh-base"><span class="text-secondary">Pending</span> weighing: <b><?=$salesContainerList[$i]['container_no'] ?></b> (<?=$salesContainerList[$i]['weight_type'] ?> )</h6>
                                                    <!-- </a> -->
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="tab-pane fade py-2 ps-2" id="purchase-cw-tab" role="tabpanel" aria-labelledby="messages-tab">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php for($i=0; $i<count($purchaseContainerList); $i++){ ?>
                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <div class="flex-1">
                                                    <!-- <a href="index.php?weight=<?=$purchaseContainerList[$i]['id'] ?>" class="stretched-link"> -->
                                                        <h6 class="mt-0 mb-2 lh-base"><span class="text-secondary">Pending</span> weighing: <b><?=$purchaseContainerList[$i]['container_no'] ?></b> (<?=$purchaseContainerList[$i]['weight_type'] ?> )</h6>
                                                    <!-- </a> -->
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="tab-pane fade py-2 ps-2" id="local-cw-tab" role="tabpanel" aria-labelledby="local-cw-tab">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php for($i=0; $i<count($localContainerList); $i++){ ?>
                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <div class="flex-1">
                                                    <!-- <a href="index.php?weight=<?=$localContainerList[$i]['id'] ?>" class="stretched-link"> -->
                                                        <h6 class="mt-0 mb-2 lh-base"><span class="text-secondary">Pending</span> weighing: <b><?=$localContainerList[$i]['container_no'] ?></b> (<?=$localContainerList[$i]['weight_type'] ?> )</h6>
                                                    <!-- </a> -->
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="tab-pane fade py-2 ps-2" id="misc-cw-tab" role="tabpanel" aria-labelledby="misc-cw-tab">
                                <div data-simplebar style="max-height: 300px;" class="pe-2">
                                    <?php for($i=0; $i<count($miscContainerList); $i++){ ?>
                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <div class="flex-1">
                                                    <!-- <a href="index.php?weight=<?=$miscContainerList[$i]['id'] ?>" class="stretched-link"> -->
                                                        <h6 class="mt-0 mb-2 lh-base"><span class="text-secondary">Pending</span> weighing: <b><?=$miscContainerList[$i]['container_no'] ?></b> (<?=$miscContainerList[$i]['weight_type'] ?> )</h6>
                                                    <!-- </a> -->
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="notification-actions" id="notification-actions">
                                <div class="d-flex text-muted justify-content-center">
                                    Select <div id="select-content" class="text-body fw-semibold px-1">0</div> Result
                                    <button type="button" class="btn btn-link link-danger p-0 ms-3"
                                        data-bs-toggle="modal" data-bs-target="#removeNotificationModal">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <!--img class="rounded-circle header-profile-user" src="assets/images/users/avatar-1.jpg"
                                alt="Header Avatar"-->
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?=$_SESSION["username"] ?></span>
                                <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text"><?=$_SESSION["roles"] ?></span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome <?=$_SESSION["username"] ?>!</h6>
                        <a class="dropdown-item" href="myProfile.php">
                            <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> 
                            <span class="align-middle">Profile</span>
                        </a>
                        <a class="dropdown-item" href="php/logout.php">
                            <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> 
                            <span class="align-middle" data-key=t-logout><?=$languageArray['logout_code'][$language]?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- removeNotificationModal -->
<div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="NotificationModalbtn-close"></button>
            </div>
            <div class="modal-body">
                <div class="mt-2 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                    <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                        <h4>Are you sure ?</h4>
                        <p class="text-muted mx-4 mb-0">Are you sure you want to remove this Notification ?</p>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                    <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete It!</button>
                </div>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
