<?php
session_start();
require_once '../../db_connect.php';

if (!isset($_SESSION['id'])) {
    echo '<script type="text/javascript">location.href = "../login.php";</script>';
} else {
    $username = $_SESSION["username"];
}

try {
    if (!isset($_POST['speciesName'])) {
        throw new Exception("Please fill in all the fields");
    }

    $speciesId = empty($_POST["id"]) ? null : trim($_POST["id"]);
    $speciesName = empty($_POST["speciesName"]) ? null : trim($_POST["speciesName"]);

    $db->begin_transaction();

    // Check for duplicate name (exclude current record when updating)
    $duplicateCheck = $db->prepare("SELECT id FROM Sawn_Timber_Species WHERE name = ? AND status = 0" . (!empty($speciesId) ? " AND id != ?" : ""));
    if (!empty($speciesId)) {
        $duplicateCheck->bind_param('si', $speciesName, $speciesId);
    } else {
        $duplicateCheck->bind_param('s', $speciesName);
    }
    $duplicateCheck->execute();
    $duplicateCheck->store_result();

    if ($duplicateCheck->num_rows > 0) {
        $duplicateCheck->close();
        throw new Exception("Species already exists");
    }
    $duplicateCheck->close();

    if (!empty($speciesId)) {
        $stmt = $db->prepare("UPDATE Sawn_Timber_Species SET name=?, modified_by=? WHERE id=?");
        $stmt->bind_param('sss', $speciesName, $username, $speciesId);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();
        $db->commit();
        $db->close();

        echo json_encode(["status" => "success", "message" => "Updated Successfully!!"]);
    } else {
        $stmt = $db->prepare("INSERT INTO Sawn_Timber_Species (name, created_by) VALUES (?, ?)");
        $stmt->bind_param('ss', $speciesName, $username);

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $stmt->close();
        $db->commit();
        $db->close();

        echo json_encode(["status" => "success", "message" => "Added Successfully!!"]);
    }
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(["status" => "failed", "message" => $e->getMessage()]);
}
?>
