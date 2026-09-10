<?php
function optionalPost($key, $default = null) {
    return isset($_POST[$key]) && $_POST[$key] !== '' ? trim($_POST[$key]) : $default;
}

function optionalJson($row, $key, $default = null) {
    return isset($row[$key]) && $row[$key] !== '' ? trim((string)$row[$key]) : $default;
}

function calculateSawnTimberTons($thick, $width, $length, $pieces) {
    return round(((float)$thick * (float)$width * (float)$length * (float)$pieces) / 7200, 4);
}

function getSawnTimberOptions($db, $table, $valueColumn, $codeColumn = null) {
    $items = array();
    $columns = $codeColumn ? "$valueColumn, $codeColumn" : $valueColumn;

    $where = " WHERE status='0'";

    if ($stmt = $db->prepare("SELECT $columns FROM $table".$where." ORDER BY $valueColumn ASC")) {
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $items[] = array(
                "value" => $row[$valueColumn],
                "code" => $codeColumn ? $row[$codeColumn] : ''
            );
        }

        $stmt->close();
    }

    return $items;
}
?>
