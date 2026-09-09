<?php

######### Update relevant table when updating master data code with modules #########
function updateMasterDataCodeValue($db, $oldValue, $newValue, $modules)
{
    $sql = "";
    $containerSql = "";
    $vehicleSql = "";

    if($modules == 'Customer'){
        // Update Weight Table
        $sql = "UPDATE Weight SET customer_code = ? WHERE customer_code = ?";
        
        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET customer_code = ? WHERE customer_code = ?";

        // Update Vehicle Table
        $vehicleSql = "UPDATE Vehicle SET customer_code = ? WHERE customer_code = ?";
    }
    else if($modules == 'Supplier'){
        // Update Weight Table
        $sql = "UPDATE Weight SET supplier_code = ? WHERE supplier_code = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET supplier_code = ? WHERE supplier_code = ?";

        // Update Vehicle Table
        $vehicleSql = "UPDATE Vehicle SET supplier_code = ? WHERE supplier_code = ?";
    }
    else if($modules == 'Destination'){
        // Update Weight Table
        $sql = "UPDATE Weight SET destination_code = ? WHERE destination_code = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET destination_code = ? WHERE destination_code = ?";
    }
    else if($modules == 'Product'){
        // Update Weight Table
        $sql = "UPDATE Weight SET product_code = ? WHERE product_code = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET product_code = ? WHERE product_code = ?";
    }
    else if($modules == 'Raw Material'){
        // Update Weight Table
        $sql = "UPDATE Weight SET raw_mat_code = ? WHERE raw_mat_code = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET raw_mat_code = ? WHERE raw_mat_code = ?";
    }
    else if($modules == 'Plant'){
        // Update Weight Table
        $sql = "UPDATE Weight SET plant_code = ? WHERE plant_code = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET plant_code = ? WHERE plant_code = ?";
    }

    if($sql != ""){
        $weight_stmt = $db->prepare($sql);
        $weight_stmt->bind_param("ss", $newValue, $oldValue);
        $weight_stmt->execute();
        $weight_stmt->close();
    }

    if($containerSql != ""){
        $container_stmt = $db->prepare($containerSql);
        $container_stmt->bind_param("ss", $newValue, $oldValue);
        $container_stmt->execute();
        $container_stmt->close();
    }

    if($vehicleSql != ""){
        $vehicle_stmt = $db->prepare($vehicleSql);
        $vehicle_stmt->bind_param("ss", $newValue, $oldValue);
        $vehicle_stmt->execute();
        $vehicle_stmt->close();
    }
}
################################################

######### Update relevant table when updating master data name with modules #########
function updateMasterDataNameValue($db, $oldValue, $newValue, $modules)
{
    $sql = "";
    $containerSql = "";
    $vehicleSql = "";

    if($modules == 'Customer'){
        // Update Weight Table
        $sql = "UPDATE Weight SET customer_name = ? WHERE customer_name = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET customer_name = ? WHERE customer_name = ?";

        // Update Vehicle Table
        $vehicleSql = "UPDATE Vehicle SET customer_name = ? WHERE customer_name = ?";
    }
    else if($modules == 'Supplier'){
        // Update Weight Table
        $sql = "UPDATE Weight SET supplier_name = ? WHERE supplier_name = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET supplier_name = ? WHERE supplier_name = ?";

        // Update Vehicle Table
        $vehicleSql = "UPDATE Vehicle SET supplier_name = ? WHERE supplier_name = ?";
    }
    else if($modules == 'Destination'){
        // Update Weight Table
        $sql = "UPDATE Weight SET destination = ? WHERE destination = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET destination = ? WHERE destination = ?";
    }
    else if($modules == 'Product'){
        // Update Weight Table
        $sql = "UPDATE Weight SET product_name = ? WHERE product_name = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET product_name = ? WHERE product_name = ?";
    }
    else if($modules == 'Raw Material'){
        // Update Weight Table
        $sql = "UPDATE Weight SET raw_mat_name = ? WHERE raw_mat_name = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET raw_mat_name = ? WHERE raw_mat_name = ?";
    }
    else if($modules == 'Plant'){
        // Update Weight Table
        $sql = "UPDATE Weight SET plant_name = ? WHERE plant_name = ?";

        // Update Weight_Container Table
        $containerSql = "UPDATE Weight_Container SET plant_name = ? WHERE plant_name = ?";
    }

    if($sql != ""){
        $weight_stmt = $db->prepare($sql);
        $weight_stmt->bind_param("ss", $newValue, $oldValue);
        $weight_stmt->execute();
        $weight_stmt->close();
    }

    if($containerSql != ""){
        $container_stmt = $db->prepare($containerSql);
        $container_stmt->bind_param("ss", $newValue, $oldValue);
        $container_stmt->execute();
        $container_stmt->close();
    }

    if($vehicleSql != ""){
        $vehicle_stmt = $db->prepare($vehicleSql);
        $vehicle_stmt->bind_param("ss", $newValue, $oldValue);
        $vehicle_stmt->execute();
        $vehicle_stmt->close();
    }
}
################################################