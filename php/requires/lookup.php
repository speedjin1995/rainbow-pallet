<?php
function convertDatetimeToDate($datetime){
    $date = new DateTime($datetime);
  
    return $date->format('d/m/Y'); 
}

function searchPlantById($value, $db) {
    $result = null;

    if(isset($value)){
        if ($select_stmt = $db->prepare("SELECT * FROM Plant WHERE id=? AND status = '0'")) {
            $select_stmt->bind_param('s', $value);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
        }
    }

    return $result;
}

function searchPlantCodeById($value, $db) {
    $id = '0';

    if(isset($value)){
        if ($select_stmt = $db->prepare("SELECT * FROM Plant WHERE id=?")) {
            $select_stmt->bind_param('s', $value);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $id = $row['plant_code'];
            }
            $select_stmt->close();
        }
    }

    return $id;
}

function searchPlantNameById($value, $db) {
    $id = '';

    if(isset($value)){
        if ($select_stmt = $db->prepare("SELECT * FROM Plant WHERE id=?")) {
            $select_stmt->bind_param('s', $value);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $id = $row['name'];
            }
            $select_stmt->close();
        }
    }

    return $id;
}

function searchPlantNameByCode($value, $db) {
    $id = '';

    if(isset($value)){
        if ($select_stmt = $db->prepare("SELECT * FROM Plant WHERE plant_code=? AND status = '0'")) {
            $select_stmt->bind_param('s', $value);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $id = $row['name'];
            }
            $select_stmt->close();
        }
    }

    return $id;
}

function searchProjectByCode($value, $db) {
    $id = '0';

    if(isset($value)){
        if ($select_stmt = $db->prepare("SELECT * FROM Site WHERE site_code=?")) {
            $select_stmt->bind_param('s', $value);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $id = $row['name'];
            }
            $select_stmt->close();
        }
    }

    return $id;
}

function searchTransporterNameByCode($value, $db) {
    $id = '';

    if(isset($value)){
        if ($select_stmt = $db->prepare("SELECT * FROM Transporter WHERE transporter_code=? AND status = '0'")) {
            $select_stmt->bind_param('s', $value);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $id = $row['name'];
            }
            $select_stmt->close();
        }
    }

    return $id;
}

function searchAgentNameByCode($value, $db) {
    $id = '';

    if(isset($value)){
        if ($select_stmt = $db->prepare("SELECT * FROM Agents WHERE agent_code=? AND status = '0'")) {
            $select_stmt->bind_param('s', $value);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $id = $row['name'];
            }
            $select_stmt->close();
        }
    }

    return $id;
}

function searchDestinationCodeByName($value, $db) {
    $id = '0';

    if(isset($value)){
        if ($select_stmt = $db->prepare("SELECT * FROM Destination WHERE name=? AND status = '0'")) {
            $select_stmt->bind_param('s', $value);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $id = $row['destination_code'];
            }
            $select_stmt->close();
        }
    }

    return $id;
}

function searchFilePathById($value, $db) {
    $id = null;

    if ($select_stmt = $db->prepare("SELECT * FROM files WHERE id=?")) {
        $select_stmt->bind_param('s', $value);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id = $row['filepath'];
        }
        $select_stmt->close();
    }

    return $id;
}

function searchNamebyId($value, $db) {
    $id = null;

    if ($select_stmt = $db->prepare("SELECT * FROM Users WHERE username=?")) {
        $select_stmt->bind_param('s', $value);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id = $row['name'];
        }
        $select_stmt->close();
    }

    return $id;
}

function searchActionNameById($value, $db) {
    $id = null;

    if ($select_stmt = $db->prepare("SELECT * FROM Log_Action WHERE id=?")) {
        $select_stmt->bind_param('s', $value);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id = $row['description'];
        }
        $select_stmt->close();
    }

    return $id;
}

function searchSupplierById($value, $db) {
    $id = null;

    if ($select_stmt = $db->prepare("SELECT * FROM Supplier WHERE id=?")) {
        $select_stmt->bind_param('s', $value);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id = $row;
        }
        $select_stmt->close();
    }

    return $id;
}

function searchSupplierNameById($value, $db) {
    $id = '';

    if ($select_stmt = $db->prepare("SELECT * FROM Supplier WHERE id=?")) {
        $select_stmt->bind_param('s', $value);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id = $row['name'];
        }
        $select_stmt->close();
    }

    return $id;
}

function searchCompanyById($value, $db) {
    $id = null;

    if ($select_stmt = $db->prepare("SELECT * FROM Company WHERE id=?")) {
        $select_stmt->bind_param('s', $value);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id = $row;
        }
        $select_stmt->close();
    }

    return $id;
}

function searchProductIdByCode($value, $db) {
    $id = '';

    if(isset($value)){
        if ($select_stmt = $db->prepare("SELECT * FROM Product WHERE product_code=? AND status = '0'")) {
            $select_stmt->bind_param('s', $value);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $id = $row['id'];
            }
            $select_stmt->close();
        }
    }

    return $id;
}

function excelSerialToDate($serial) {
    // Excel date starts from 1900-01-01, subtract 1 for correct calculation
    $baseDate = strtotime('1899-12-30');
    return date('Y-m-d', strtotime("+$serial days", $baseDate));
}

function searchSawnTimberSpeciesNameById($value, $db) {
    $name = null;
    if ($value != null && $value != '') {
        if ($select_stmt = $db->prepare("SELECT name FROM Sawn_Timber_Species WHERE id=? AND status = '0'")) {
            $select_stmt->bind_param('i', $value);
            $select_stmt->execute();
            $result = $select_stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $name = $row['name'];
            }
            $select_stmt->close();
        }
    }
    return $name;
}

function getCompanyIdByName($db, $name) {
    $id = null;
    if ($name != null && $name != '') {
        $stmt = $db->prepare("SELECT id FROM Company WHERE name = ? AND status = '0'");
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id = $row['id'];
        }
        $stmt->close();
    }
    return $id;
}

function getPlantIdByName($db, $name) {
    $id = null;
    if ($name != null && $name != '') {
        $stmt = $db->prepare("SELECT id FROM Plant WHERE name = ? AND status = '0'");
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id = $row['id'];
        }
        $stmt->close();
    }
    return $id;
}

function getSupplierIdByName($db, $name) {
    $id = null;
    if ($name != null && $name != '') {
        $stmt = $db->prepare("SELECT id FROM Supplier WHERE name = ? AND status = '0'");
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id = $row['id'];
        }
        $stmt->close();
    }
    return $id;
}

function getSpeciesIdByName($db, $name) {
    $id = null;
    if ($name != null && $name != '') {
        $stmt = $db->prepare("SELECT id FROM Sawn_Timber_Species WHERE name = ? AND status = '0'");
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $id = $row['id'];
        }
        $stmt->close();
    }
    return $id;
}

?>