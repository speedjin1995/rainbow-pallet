<?php
session_start();
require_once '../../db_connect.php';

if(!isset($_SESSION['id'])){
    echo '<script type="text/javascript">window.location.href = "../login.php";</script>';
} else{
	$username = $_SESSION["username"];
}

// Check if the user is already logged in, if yes then redirect him to index page
$id = $_SESSION['id'];

// Processing form data when form is submitted
if (isset($_POST['moduleName'], $_POST['moduleCategory'])) {

    if (empty($_POST["moduleId"])) {
        $moduleId = null;
    } else {
        $moduleId = trim($_POST["moduleId"]);
    }

    if (empty($_POST["moduleName"])) {
        $moduleName = null;
    } else {
        $moduleName = trim($_POST["moduleName"]);
    }

    if (empty($_POST["moduleCategory"])) {
        $moduleCategory = null;
    } else {
        $moduleCategory = trim($_POST["moduleCategory"]);
    }

    if(! empty($moduleId))
    {
        if ($update_stmt = $db->prepare("UPDATE modules SET name=?, category=? WHERE id=?")) 
        {
            $update_stmt->bind_param('sss', $moduleName, $moduleCategory, $moduleId);

            // Execute the prepared query.
            if (! $update_stmt->execute()) {
                echo json_encode(
                    array(
                        "status"=> "failed", 
                        "message"=> $update_stmt->error
                    )
                );
            }
            else{
                echo json_encode(
                    array(
                        "status"=> "success", 
                        "message"=> "Updated Successfully!!" 
                    )
                );
            }

            $update_stmt->close();
            $db->close();
        }
    }
    else
    {
        if ($insert_stmt = $db->prepare("INSERT INTO modules (name, category) VALUES (?, ?)")) {
            $insert_stmt->bind_param('ss', $moduleName, $moduleCategory);

            // Execute the prepared query.
            if (! $insert_stmt->execute()) {
                echo json_encode(
                    array(
                        "status"=> "failed", 
                        "message"=> $insert_stmt->error
                    )
                );
            }
            else{
                echo json_encode(
                    array(
                        "status"=> "success", 
                        "message"=> "Added Successfully!!" 
                    )
                );
            }
            
            $insert_stmt->close();
            $db->close();
        }
    }
    
}
else
{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    );
}
?>