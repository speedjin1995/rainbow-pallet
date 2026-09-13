<?php
session_start();
require_once '../../db_connect.php';

if(!isset($_SESSION['id'])){
	echo '<script type="text/javascript">location.href = "../login.php";</script>'; 
} else{
	$username = $_SESSION["username"];
}
// Check if the user is already logged in, if yes then redirect him to index page
$id = $_SESSION['id'];

// Processing form data when form is submitted
if (isset($_POST['roleCode'], $_POST['roleName'])) {

    if (empty($_POST["roleId"])) {
        $roleId = null;
    } else {
        $roleId = trim($_POST["roleId"]);
    }

    if (empty($_POST["roleCode"])) {
        $roleCode = null;
    } else {
        $roleCode = trim($_POST["roleCode"]);
    }

    if (empty($_POST["roleName"])) {
        $roleName = null;
    } else {
        $roleName = trim($_POST["roleName"]);
    }
    
    if(!empty($roleId))
    {
        if ($update_stmt = $db->prepare("UPDATE roles SET role_code=?, role_name=? WHERE id=?")) 
        {
            $update_stmt->bind_param('sss', $roleCode, $roleName, $roleId);

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
                $update_stmt->close();
                $db->close();

                echo json_encode(
                    array(
                        "status"=> "success", 
                        "message"=> "Updated Successfully!!" 
                    )
                );
            }
        }
    }
    else
    {
        if ($insert_stmt = $db->prepare("INSERT INTO roles (role_code, role_name) VALUES (?, ?)")) {
            $insert_stmt->bind_param('ss', $roleCode, $roleName);

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
                $insert_stmt->close();
                $db->close();
                
                
                echo json_encode(
                    array(
                        "status"=> "success", 
                        "message"=> "Added Successfully!!" 
                    )
                );

            }
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