<?php
$role = $_SESSION['roles'];

function hasPermission($category, $permission = 'view') {
    global $role;
    if ($role === 'SADMIN') return true;
    if (!isset($_SESSION['permissions'][$category])) return false;
    $permissions = is_array($permission) ? $permission : [$permission];
    foreach ($_SESSION['permissions'][$category] as $module => $perms) {
        if (array_intersect($permissions, $perms)) return true;
    }
    return false;
}

function hasModulePermission($category, $module, $permission = 'view') {
    global $role;
    if ($role === 'SADMIN') return true;
    if (!isset($_SESSION['permissions'][$category][$module])) return false;
    $permissions = is_array($permission) ? $permission : [$permission];
    return !empty(array_intersect($permissions, $_SESSION['permissions'][$category][$module]));
}
