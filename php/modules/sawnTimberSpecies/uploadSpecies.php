<?php
session_start();
require_once '../../db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$uid = $_SESSION['username'];

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) {
    $errorArray = [];
    $status = '0';

    foreach ($data as $rows) {
        $SpeciesName = !empty($rows['Name']) ? trim($rows['Name']) : '';

        if ($SpeciesName != null && $SpeciesName != '') {
            $check = $db->prepare("SELECT id FROM Sawn_Timber_Species WHERE name = ? AND status = ?");
            $check->bind_param('ss', $SpeciesName, $status);
            $check->execute();
            $speciesRow = $check->get_result()->fetch_assoc();
            $check->close();

            if (empty($speciesRow)) {
                if ($insert_stmt = $db->prepare("INSERT INTO Sawn_Timber_Species (name, created_by) VALUES (?, ?)")) {
                    $insert_stmt->bind_param('ss', $SpeciesName, $uid);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }
            } else {
                $errorArray[] = "Species: " . $SpeciesName . " already exist in master data.";
                continue;
            }
        }
    }

    $db->close();

    if (!empty($errorArray)) {
        echo json_encode(array("status" => "error", "message" => $errorArray));
    } else {
        echo json_encode(array("status" => "success", "message" => "Added Successfully!!"));
    }
} else {
    echo json_encode(array("status" => "failed", "message" => "Please fill in all the fields"));
}
?>
