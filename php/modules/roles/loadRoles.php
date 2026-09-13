<?php
session_start();
require_once '../../db_connect.php';

$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
$columnIndex = $_POST['order'][0]['column'];
$columnName = $_POST['columns'][$columnIndex]['data'];
$columnSortOrder = $_POST['order'][0]['dir'];
$searchValue = mysqli_real_escape_string($db, $_POST['search']['value']);

$searchQuery = " ";
if($searchValue != ''){
    $searchQuery .= " AND (role_code LIKE '%".$searchValue."%' OR role_name LIKE '%".$searchValue."%') ";
}

// if($_SESSION['roles'] != 'SADMIN'){
//     $searchQuery .= "";
// }

$sel = mysqli_query($db, "SELECT COUNT(*) as allcount FROM roles WHERE deleted IN (0,1) AND role_code <> 'SADMIN'");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

$sel = mysqli_query($db, "SELECT COUNT(*) as allcount FROM roles WHERE deleted IN (0) AND role_code <> 'SADMIN'".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

$empQuery = "SELECT id, role_code, role_name, deleted FROM roles WHERE deleted IN (0) AND role_code <> 'SADMIN' ".$searchQuery." ORDER BY deleted ASC, ".$columnName." ".$columnSortOrder." LIMIT ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();

while($r = mysqli_fetch_assoc($empRecords)) {
    $data[] = array(
        "id" => $r['id'],
        "role_code" => $r['role_code'],
        "role_name" => $r['role_name'],
        "deleted" => ($r['deleted'] == '0') ? 'Active' : 'Inactive'
    );
}

echo json_encode(array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
));
?>
