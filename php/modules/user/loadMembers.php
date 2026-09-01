<?php
session_start();
require_once '../../db_connect.php';
require_once '../../requires/lookup.php';

$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
$columnIndex = $_POST['order'][0]['column'];
$columnName = $_POST['columns'][$columnIndex]['data'];
$columnSortOrder = $_POST['order'][0]['dir'];
$searchValue = mysqli_real_escape_string($db, $_POST['search']['value']);

$searchQuery = " ";
if ($searchValue != '') {
    $searchQuery = " and (Users.username like '%".$searchValue."%' or
        Users.useremail like '%".$searchValue."%' or
        roles.role_name like'%".$searchValue."%' ) ";
}

$allQuery = "select count(*) as allcount from Users where status IN (0)";
if ($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN') {
    $username = implode("', '", $_SESSION["plant_id"]);
    $allQuery = "select count(*) as allcount from Users, roles WHERE Users.role = roles.role_code AND Users.status IN (0) and Users.plant_id IN ('$username')";
}

$sel = mysqli_query($db, $allQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

$filteredQuery = "select count(*) as allcount from Users, roles WHERE Users.role = roles.role_code AND Users.status IN (0)".$searchQuery;
if ($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN') {
    $username = implode("', '", $_SESSION["plant_id"]);
    $filteredQuery = "select count(*) as allcount from Users, roles WHERE Users.role = roles.role_code AND Users.status IN (0) AND Users.plant_id IN ('$username')".$searchQuery;
}

$sel = mysqli_query($db, $filteredQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

$empQuery = "select Users.id, Users.employee_code, Users.username, Users.useremail, Users.name, roles.role_name, Users.plant_id, Users.status from Users, roles WHERE
Users.role = roles.role_code AND Users.status IN (0) AND Users.role <> 'SADMIN'".$searchQuery."
order by status ASC, ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;

if ($_SESSION["roles"] != 'ADMIN' && $_SESSION["roles"] != 'SADMIN') {
    $plantIds = $_SESSION["plant_id"];

    if (!empty($plantIds) && is_array($plantIds)) {
        $conditions = [];
        foreach ($plantIds as $plant) {
            $conditions[] = "JSON_CONTAINS(Users.plant_id, '\"$plant\"')";
        }
        $jsonCondition = implode(" OR ", $conditions);

        $empQuery = "SELECT Users.name AS empname, Users.id, Users.employee_code, Users.username, Users.useremail, Users.name,
                        roles.role_name, Users.plant_id, Users.status
                     FROM Users
                     JOIN roles ON Users.role = roles.role_code
                     WHERE Users.status IN (0)
                     AND Users.role <> 'SADMIN'
                     AND ($jsonCondition)
                     $searchQuery
                     ORDER BY status ASC, $columnName $columnSortOrder
                     LIMIT $row, $rowperpage";
    }
}

$empRecords = mysqli_query($db, $empQuery);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {
    $plant = array();

    if ($row['plant_id'] != null) {
        $plant_ids = json_decode($row['plant_id'], true);
        for ($i = 0; $i < count($plant_ids); $i++) {
            $plant[] = searchPlantNameById($plant_ids[$i], $db);
        }
    }

    $data[] = array(
        "id"            => $row['id'],
        "employee_code" => $row['employee_code'],
        "username"      => $row['username'],
        "name"          => $row['name'] ?? '',
        "useremail"     => $row['useremail'],
        "role"          => $row['role_name'],
        "plant"         => $plant,
        "status"        => $row['status']
    );
}

$response = array(
    "draw"                 => intval($draw),
    "iTotalRecords"        => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData"               => $data
);

echo json_encode($response);
?>
