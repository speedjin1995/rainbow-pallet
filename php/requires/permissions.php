<?php
$role = $_SESSION['roles'] ?? null;

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

/**
 * A small info icon for a table's Action column header, shown only when at least one of the
 * action buttons that column could show (edit / delete / print / reactivate / ...) is hidden
 * for the current user's role. Pass the same permission booleans already used on the page to
 * decide which buttons to render, e.g. actionPermissionNote([$canEdit, $canDelete]).
 */
function actionPermissionNote($flags) {
    if (!in_array(false, $flags, true)) {
        return '';
    }
    $languageArray = $_SESSION['languageArray'] ?? [];
    $language = $_SESSION['language'] ?? 'en';
    $text = $languageArray['action_permission_note_code'][$language] ?? 'Note: Some actions are unavailable due to your role permissions';
    // No data-bs-toggle: the tooltip is delegated in assets/js/additional.js so copies of this icon
    // (e.g. DataTables Responsive putting the header into a collapsed row) work too
    return ' <i class="ri-information-line text-muted action-permission-note" data-bs-placement="top" data-bs-title="' . htmlspecialchars($text, ENT_QUOTES) . '"></i>';
}
