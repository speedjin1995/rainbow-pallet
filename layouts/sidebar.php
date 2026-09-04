<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index.php" class="logo logo-dark">
            <span class="logo-sm">
                <img src="assets/images/logo-sm.jpg" alt="" height="70">
            </span>
            <span class="logo-lg">
                <img src="assets/images/logo-lg.png" alt="" height="60">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index.php" class="logo logo-light">
            <span class="logo-sm">
                <img src="assets/images/logo-sm.jpg" alt="" height="70">
            </span>
            <span class="logo-lg">
                <img src="assets/images/logo-lg.png" alt="" height="60">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span><?=$lang['t-menu']?></span></li>
                <!--li class="nav-item">
                    <a href="dashboard.php" class="nav-link"><i class="mdi mdi-billboard"></i><?=$lang['t-billboard']?></a>
                </li-->
                <li class="nav-item">
                    <a href="index.php" class="nav-link"><i class="mdi mdi-weight"></i><span><?=$languageArray['daily_weighing_code'][$language]?></span></a>
                </li>                
                <!--li class="nav-item">
                    <a href="bitumen.php" class="nav-link"><i class="mdi mdi-domain"></i></i><?=$lang['t-bitumen']?></a>
                </li-->         
                <!-- <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                        aria-expanded="true" aria-controls="sidebarDashboards">
                        <i class="ri-dashboard-2-line"></i> <span><?=$lang['t-weightweighing']?></span>
                    </a>
                    <div class="collapse show menu-dropdown" id="sidebarDashboards">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="weighing.php" class="nav-link"><?=$lang['t-weighing']?></a>
                            </li>
                        </ul>
                    </div>
                </li> -->
                <!--li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarAccounting" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarAccounting">
                        <i class="ri-pages-line"></i> <span><?=$lang['t-accounting']?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarAccounting">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="salesOrder.php" class="nav-link"><?=$lang['t-so']?></a>
                            </li>
                            <li class="nav-item">
                                <a href="purchaseOrder.php" class="nav-link"><?=$lang['t-po']?></a>
                            </li>               
                        </ul>
                    </div>
                </li-->
                <?php
                    if($_SESSION["roles"] == 'SADMIN' || $_SESSION["roles"] == 'MANAGER' || $_SESSION["roles"] == 'ADMIN'){
                        echo '<!--li class="nav-item">
                            <a href="inventory.php" class="nav-link"><i class="mdi mdi-shipping-pallet"></i></i>'.$lang['t-inventory'].'</a>
                        </li--> 
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#sidebarMasterdata" data-bs-toggle="collapse" role="button"
                                aria-expanded="false" aria-controls="sidebarMasterdata">
                                <i class="ri-pages-line"></i> <span>'.$languageArray['master_data_code'][$language].'</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarMasterdata">
                                <ul class="nav nav-sm flex-column">';

                                    if($_SESSION["roles"] == 'SADMIN'){
                                        echo '<li class="nav-item">
                                            <a href="message.php" class="nav-link">'.$languageArray['message_resource_code'][$language].'</a>
                                        </li>';
                                    }

                                    if($_SESSION["roles"] == 'MANAGER' || $_SESSION["roles"] == 'ADMIN' || $_SESSION["roles"] == 'SADMIN'){
                                        echo '
                                            <li class="nav-item">
                                                <a href="companies.php" class="nav-link">'.$languageArray['companies_code'][$language].'</a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="customer.php" class="nav-link">'.$languageArray['customer_code'][$language].'</a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="destination.php" class="nav-link">'.$languageArray['destination_code'][$language].'</a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="product.php" class="nav-link">'.$languageArray['product_code'][$language].'</a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="rawMaterial.php" class="nav-link">'.$languageArray['raw_material_code'][$language].'</a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="supplier.php" class="nav-link">'.$languageArray['supplier_code'][$language].'</a>
                                            </li>       
                                            <li class="nav-item">
                                                <a href="vehicle.php" class="nav-link">'.$languageArray['vehicle_code'][$language].'</a>
                                            </li>             
                                            <li class="nav-item">
                                                <a href="transporter.php" class="nav-link">'.$languageArray['transporter_code'][$language].'</a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="user.php" class="nav-link">'.$languageArray['staff_code'][$language].'</a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="plant.php" class="nav-link">'.$languageArray['plant_code'][$language].'</a>
                                            </li>
                                            <li class="nav-item">
                                                <a href="locations.php" class="nav-link">'.$languageArray['locations_code'][$language].'</a>
                                            </li>
                                        ';
                                    }
                            echo '</ul>
                            </div>
                        </li>';
                    }
                ?>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarReport" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarReport">
                        <i class="ri-file-chart-line"></i> <span><?=$languageArray['report_code'][$language]?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarReport">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <li class="nav-item">
                                    <a href="weighingReport.php" class="nav-link"><?=$languageArray['weighing_report_code'][$language]?></a>
                                </li>
                                <!--li class="nav-item">
                                    <a href="salesReport.php" class="nav-link"><?=$lang['t-soReport']?></a>
                                </li>
                                <li class="nav-item">
                                    <a href="purchaseReport.php" class="nav-link"><?=$lang['t-poReport']?></a>
                                </li-->
                                <?php
                                    if($_SESSION["roles"] == 'ADMIN' || $_SESSION["roles"] == 'SADMIN'){
                                        echo '<li class="nav-item">
                                            <a href="auditLog.php" class="nav-link">'.$languageArray['audit_log_code'][$language].'</a>
                                        </li> ';
                                    }
                                ?>                            
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#userManagement" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="userManagement">
                        <i class="ri-shield-user-line"></i> <span><?=$languageArray['user_management_code'][$language]?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="userManagement">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <li class="nav-item">
                                    <a href="modules/userManagement/permissions.php" class="nav-link"><?=$languageArray['permissions_code'][$language]?></a>
                                </li>                    
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarAuth" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarAuth">
                        <i class="ri-account-circle-line"></i> <span><?=$languageArray['setting_code'][$language]?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarAuth">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <!-- <?php
                                    if($_SESSION["roles"] == 'ADMIN' || $_SESSION["roles"] == 'SADMIN'){
                                        echo '<li class="nav-item">
                                            <a href="companyProfile.php" class="nav-link">'.$languageArray['company_profile_code'][$language].'</a>
                                        </li> ';
                                    }
                                ?> -->
                                
                                <li class="nav-item">
                                    <a href="portSetup.php" class="nav-link"><?=$languageArray['port_setup_code'][$language]?></a>
                                </li> 
                                <li class="nav-item">
                                    <a href="myProfile.php" class="nav-link"><?=$languageArray['profile_code'][$language]?></a>
                                </li> 
                                <li class="nav-item">
                                    <a href="ChangePassword.php" class="nav-link"><?=$languageArray['change_password_code'][$language]?></a>
                                </li>                                 
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a href="php/logout.php" class="nav-link"><i class="mdi mdi-logout-variant"></i> <span><?=$languageArray['logout_code'][$language]?></span></a>
                </li>                 
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
