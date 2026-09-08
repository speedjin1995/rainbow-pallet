<?php
session_start();
require_once '../../db_connect.php';

$languageArray = $_SESSION['languageArray'];
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length'];
$columnIndex = $_POST['order'][0]['column'];
$columnName = $_POST['columns'][$columnIndex]['data'];
$columnSortOrder = $_POST['order'][0]['dir'];
$searchValue = mysqli_real_escape_string($db, $_POST['search']['value']);

// Build module id => category + name lookup
$modResult = mysqli_query($db, "SELECT id, name, category FROM modules");
$moduleLookup = array();
while($mr = mysqli_fetch_assoc($modResult)) {
    if ($mr['category'] == 'Weighing'){
        if ($mr['name'] == 'Sales') {
            $name = $languageArray['dispatch_code']['en'];
        } else if ($mr['name'] == 'Purchase') {
            $name = $languageArray['receiving_code']['en'];
        } else if ($mr['name'] == 'Local') {
            $name = $languageArray['internal_transfer_code']['en'];
        } else if ($mr['name'] == 'Port') {
            $name = $languageArray['trx_to_port_code']['en'];
        } else if ($mr['name'] == 'Misc') {
            $name = $languageArray['miscellaneous_code']['en'];
        } else {
            $name = $mr['name'];
        }

        $lookup = $mr['category'] . ' - ' . $name;
    }else{
        $lookup = $mr['category'] . ' - ' . $mr['name'];
    }
    $moduleLookup[$mr['id']] = $lookup;
}

$searchQuery = " ";
if($searchValue != ''){
    $searchQuery = " and (name like '%".$searchValue."%' OR modules like '%".$searchValue."%')";
}

$sel = mysqli_query($db, "select count(*) as allcount from permissions");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

$sel = mysqli_query($db, "select count(*) as allcount from permissions WHERE 1=1".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

$empQuery = "select * from permissions WHERE 1=1".$searchQuery."order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();

while($row = mysqli_fetch_assoc($empRecords)) {
    $modulesArr = json_decode($row['modules'], true) ?: ['All'];

    // Resolve ids to names
    if(count($modulesArr) === 1 && $modulesArr[0] === 'All'){
        $displayModules = 'All';
    } else {
        $names = array();
        foreach($modulesArr as $mid){
            $names[] = isset($moduleLookup[$mid]) ? $moduleLookup[$mid] : $mid;
        }
        $displayModules = implode('<br>', $names);
    }

    $data[] = array(
        "id" => $row['id'],
        "name" => ucwords(str_replace('_', ' ', $row['name'])),
        "modules" => $displayModules
    );
}

$response = array(
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    "aaData" => $data
);

echo json_encode($response);
?>
