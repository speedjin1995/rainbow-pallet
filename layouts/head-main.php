<?php
require_once __DIR__ . '/../php/requires/permissions.php';

require_once ("lang.php");

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    require_once dirname(__DIR__, 1) . '/php/db_connect.php';
    // Language
    $language = $_SESSION['language'];

    // Load message
    $message_resource = $db->query("SELECT * FROM message_resource");
    $languageArray = Array();

    while($row=mysqli_fetch_assoc($message_resource)){
        $languageArray[$row['message_key_code']] = array("en"=>$row['en'],"zh"=>$row['zh'],"my"=>$row['my'],"ne"=>$row['ne']);
    }

    $_SESSION['languageArray'] = $languageArray;

    // Load user permissions
    $permSql = "SELECT m.category, m.name AS module_name, p.name AS permission_name 
        FROM role_permissions rp 
        JOIN roles r ON r.id = rp.role_id 
        JOIN modules m ON m.id = rp.module_id 
        JOIN permissions p ON p.id = rp.permission_id 
        WHERE r.role_code = ?";
    $permStmt = mysqli_prepare($db, $permSql);
    mysqli_stmt_bind_param($permStmt, "s", $_SESSION["roles"]);
    mysqli_stmt_execute($permStmt);
    $permResult = mysqli_stmt_get_result($permStmt);
    $permissions = array();
    while($pRow = mysqli_fetch_assoc($permResult)){
        $permissions[$pRow['category']][$pRow['module_name']][] = $pRow['permission_name'];
    }
    mysqli_stmt_close($permStmt);

    $_SESSION['permissions'] = $permissions;
}

$isScssconverted = false;

require_once ("scssphp/scss.inc.php");

use ScssPhp\ScssPhp\Compiler;

if($isScssconverted){

    global $compiler;
    $compiler = new Compiler();

    $compine_css = "assets/css/app.min.css";

    $source_scss = "assets/scss/config/default/app.scss";

    $scssContents = file_get_contents($source_scss);

    $import_path = "assets/scss/config/default";
    $compiler->addImportPath($import_path);
    $target_css = $compine_css;

    $css = $compiler->compileString($scssContents);

    if (!empty($css) && is_string($css)) {
        file_put_contents($target_css, $css);
    }
}
?>
<!DOCTYPE html>
<html lang="<?=$language?>" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">