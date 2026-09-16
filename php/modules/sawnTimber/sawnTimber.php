<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['id'])) {
    echo '<script type="text/javascript">location.href = "../login.php";</script>';
} else {
    $username = $_SESSION["username"];
}

try {
    if (!isset($_POST['transactionId'])) {
        throw new Exception("Please fill in all the fields");
    }

    $id = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $companyId = empty($_POST["companyId"]) ? null : trim($_POST["companyId"]);
    $plantId = empty($_POST["plantId"]) ? null : trim($_POST["plantId"]);
    $weightId = empty($_POST["weightId"]) ? null : trim($_POST["weightId"]);
    $transactionId = empty($_POST["transactionId"]) ? null : trim($_POST["transactionId"]);
    $sawnTimberDate = empty($_POST["sawnTimberDate"]) ? null : date('Y-m-d H:i:s', strtotime(trim($_POST["sawnTimberDate"])));
    $remarks = empty($_POST["remarks"]) ? null : trim($_POST["remarks"]);

    // Get detail arrays from form
    $speciesArr = isset($_POST['species']) ? $_POST['species'] : [];
    $lotArr = isset($_POST['lot']) ? $_POST['lot'] : [];
    $bundleArr = isset($_POST['bundle']) ? $_POST['bundle'] : [];
    $thickArr = isset($_POST['thick']) ? $_POST['thick'] : [];
    $widthArr = isset($_POST['width']) ? $_POST['width'] : [];
    $lengthArr = isset($_POST['length']) ? $_POST['length'] : [];
    $piecesArr = isset($_POST['pieces']) ? $_POST['pieces'] : [];
    $tonsArr = isset($_POST['tons']) ? $_POST['tons'] : [];
    $kdChargesArr = isset($_POST['kdCharges']) ? $_POST['kdCharges'] : [];
    $bundlingChargesArr = isset($_POST['bundlingCharges']) ? $_POST['bundlingCharges'] : [];
    $graderFeesArr = isset($_POST['graderFees']) ? $_POST['graderFees'] : [];

    if (!$companyId || !$plantId || !$weightId || !$transactionId || !$sawnTimberDate || empty($speciesArr)) {
        throw new Exception("Please fill in all the fields");
    }

    $db->begin_transaction();

    // Check for duplicate transaction_id
    $duplicateCheck = $db->prepare("SELECT id FROM Sawn_Timber_Header WHERE transaction_id = ? AND status = 0" . (!empty($id) ? " AND id != ?" : ""));
    if (!empty($id)) {
        $duplicateCheck->bind_param('si', $transactionId, $id);
    } else {
        $duplicateCheck->bind_param('s', $transactionId);
    }
    $duplicateCheck->execute();
    $duplicateCheck->store_result();

    if ($duplicateCheck->num_rows > 0) {
        $duplicateCheck->close();
        throw new Exception("Transaction ID already exists");
    }
    $duplicateCheck->close();

    $actionByStmt = $db->prepare("SET @sawn_timber_action_by=?");
    $actionByStmt->bind_param('s', $username);
    $actionByStmt->execute();
    $actionByStmt->close();

    if (!empty($id)) {
        $stmt = $db->prepare("UPDATE Sawn_Timber_Header SET company_id=?, plant_id=?, weight_id=?, transaction_id=?, record_date=?, remarks=?, modified_by=? WHERE id=?");
        $stmt->bind_param('iiissssi', $companyId, $plantId, $weightId, $transactionId, $sawnTimberDate, $remarks, $username, $id);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();

        $deleteStmt = $db->prepare("DELETE FROM Sawn_Timber_Detail WHERE header_id=?");
        $deleteStmt->bind_param('i', $id);
        $deleteStmt->execute();
        $deleteStmt->close();
    } else {
        $stmt = $db->prepare("INSERT INTO Sawn_Timber_Header (company_id, plant_id, weight_id, transaction_id, record_date, remarks, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiisssss', $companyId, $plantId, $weightId, $transactionId, $sawnTimberDate, $remarks, $username, $username);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $id = $stmt->insert_id;
        $stmt->close();
    }

    $detailStmt = $db->prepare("INSERT INTO Sawn_Timber_Detail (header_id, species, lot, bundle, thick, width, length, pieces, tons, kd_charges, bundling_charges, grader_fees) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($speciesArr as $index => $species) {
        $lot = isset($lotArr[$index]) ? $lotArr[$index] : null;
        $bundle = isset($bundleArr[$index]) ? $bundleArr[$index] : null;
        $thick = isset($thickArr[$index]) ? $thickArr[$index] : 0;
        $width = isset($widthArr[$index]) ? $widthArr[$index] : 0;
        $length = isset($lengthArr[$index]) ? $lengthArr[$index] : 0;
        $pieces = isset($piecesArr[$index]) ? $piecesArr[$index] : 0;
        $tons = isset($tonsArr[$index]) ? $tonsArr[$index] : 0;
        $kdCharges = isset($kdChargesArr[$index]) ? $kdChargesArr[$index] : 0;
        $bundlingCharges = isset($bundlingChargesArr[$index]) ? $bundlingChargesArr[$index] : 0;
        $graderFees = isset($graderFeesArr[$index]) ? $graderFeesArr[$index] : 0;

        $detailStmt->bind_param('isssdddddddd', $id, $species, $lot, $bundle, $thick, $width, $length, $pieces, $tons, $kdCharges, $bundlingCharges, $graderFees);
        $detailStmt->execute();
    }

    $detailStmt->close();
    $db->commit();
    $db->close();

    echo json_encode(["status" => "success", "message" => (!empty($_POST["id"]) ? "Updated" : "Added") . " Successfully!!"]);
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(["status" => "failed", "message" => $e->getMessage()]);
}
?>
