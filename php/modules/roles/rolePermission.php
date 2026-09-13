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
if (isset($_POST['permRoleId'])) {
	$roleId = filter_input(INPUT_POST, 'permRoleId', FILTER_SANITIZE_STRING);

    if (!empty($roleId)) {
        if(isset($_POST['permissions']) && !empty($_POST['permissions'])){
            // Delete old permissions
            if ($delete_stmt = $db->prepare("DELETE FROM role_permissions WHERE role_id=?")) {
                $delete_stmt->bind_param("s", $roleId);

                if($delete_stmt->execute()){
                    $delete_stmt->close();

                    foreach ($_POST['permissions'] as $moduleId => $permIds){
                        foreach ($permIds as $permId){
                            $insert_stmt = $db->prepare("INSERT INTO role_permissions (role_id, module_id, permission_id) VALUES (?, ?, ?)");
                            $insert_stmt->bind_param("sss", $roleId, $moduleId, $permId);
                            $insert_stmt->execute();
                            $insert_stmt->close();
                        }
                    }

                    // Load user permissions
                    $roles = $_SESSION['roles'];

                    $permSql = "SELECT m.category, m.name AS module_name, p.name AS permission_name 
                        FROM role_permissions rp 
                        JOIN roles r ON r.id = rp.role_id 
                        JOIN modules m ON m.id = rp.module_id 
                        JOIN permissions p ON p.id = rp.permission_id 
                        WHERE r.role_code = ?";
                    $permStmt = mysqli_prepare($db, $permSql);
                    mysqli_stmt_bind_param($permStmt, "s", $roles);
                    mysqli_stmt_execute($permStmt);
                    $permResult = mysqli_stmt_get_result($permStmt);
                    $permissions = array();
                    while ($pRow = mysqli_fetch_assoc($permResult)) {
                        $permissions[$pRow['category']][$pRow['module_name']][] = $pRow['permission_name'];
                    }
                    mysqli_stmt_close($permStmt);
                    $_SESSION['permissions'] = $permissions;


                    echo json_encode(
                        array(
                            "status"=> "success", 
                            "message"=> "Permissions Updated Successfully"
                        )
                    );

                    $db->close();
                } 
                else{
                    echo json_encode(
                        array(
                            "status"=> "failed", 
                            "message"=> $stmt2->error
                        )
                    );

                    $delete_stmt->close();
                    $db->close();
                }
            }
        }else{
            echo json_encode(
                array(
                    "status"=> "failed", 
                    "message"=> "Please select at least 1 permission"
                )
            );
        }
    } else {
        echo json_encode(
            array(
                "status"=> "failed", 
                "message"=> "Missing Role ID"
            )
        );
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