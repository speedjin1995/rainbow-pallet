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
if ($searchValue != '') {
    $searchQuery = " and (name like '%".$searchValue."%' or plant_code like '%".$searchValue."%')";
}

$sel = mysqli_query($db, "select count(*) as allcount from Plant");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

$sel = mysqli_query($db, "select count(*) as allcount from Plant WHERE status IN (0)".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

$empQuery = "select * from Plant WHERE status IN (0)".$searchQuery."order by status ASC, ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {
    $data[] = array(
        "id"             => $row['id'],
        "plant_code"     => $row['plant_code'],
        "name"           => $row['name'],
        "address_line_1" => $row['address_line_1'],
        "address_line_2" => $row['address_line_2'],
        "address_line_3" => $row['address_line_3'],
        "phone_no"       => $row['phone_no'],
        "fax_no"         => $row['fax_no'],
        "status"         => $row['status']
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
