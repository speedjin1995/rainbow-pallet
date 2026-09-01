<?php
session_start();
require_once 'db_connect.php';

if(!isset($_SESSION['id'])){
	echo '<script type="text/javascript">location.href = "../login.php";</script>'; 
} else{
	$username = $_SESSION["username"];
}
// Check if the user is already logged in, if yes then redirect him to index page
$id = $_SESSION['id'];
// Processing form data when form is submitted
if (isset($_POST['orderNo'])) {

    if (empty($_POST["id"])) {
        $poId = null;
    } else {
        $poId = trim($_POST["id"]);
    }

    if (empty($_POST["company"])) {
        $companyCode = null;
    } else {
        $companyCode = trim($_POST["company"]);
    }

    if (empty($_POST["companyName"])) {
        $companyName = null;
    } else {
        $companyName = trim($_POST["companyName"]);
    }

    if (empty($_POST["customer"])) {
        $customerCode = null;
    } else {
        $customerCode = trim($_POST["customer"]);
    }

    if (empty($_POST["customerName"])) {
        $customerName = null;
    } else {
        $customerName = trim($_POST["customerName"]);
    }

    if (empty($_POST["site"])) {
        $siteCode = null;
    } else {
        $siteCode = trim($_POST["site"]);
    }

    if (empty($_POST["siteName"])) {
        $siteName = null;
    } else {
        $siteName = trim($_POST["siteName"]);
    }

    if (empty($_POST["orderDate"])) {
        $orderDate = null;
    } else {
        $orderDate = DateTime::createFromFormat('d-m-Y', $_POST["orderDate"])->format('Y-m-d H:i:s');
    }

    if (empty($_POST["orderNo"])) {
        $orderNo = null;
    } else {
        $orderNo = trim($_POST["orderNo"]);
    }

    if (empty($_POST["soNo"])) {
        $soNo = null;
    } else {
        $soNo = trim($_POST["soNo"]);
    }

    if (empty($_POST["deliveryDate"])) {
        $deliveryDate = null;
    } else {
        $deliveryDate = DateTime::createFromFormat('d-m-Y', $_POST["deliveryDate"])->format('Y-m-d H:i:s');
    }

    if (empty($_POST["agent"])) {
        $agentCode = null;
    } else {
        $agentCode = trim($_POST["agent"]);
    }
    
    if (empty($_POST["agentName"])) {
        $agentName = null;
    } else {
        $agentName = trim($_POST["agentName"]);
    }

    if (empty($_POST["destinationCode"])) {
        $destinationCode = null;
    } else {
        $destinationCode = trim($_POST["destinationCode"]);
    }

    if (empty($_POST["destinationName"])) {
        $destinationName = null;
    } else {
        $destinationName = trim($_POST["destinationName"]);
    }

    if (empty($_POST["deliverToName"])) {
        $deliverToName = null;
    } else {
        $deliverToName = trim($_POST["deliverToName"]);
    }
    
    if (empty($_POST["product"])) {
        $productCode = null;
    } else {
        $productCode = trim($_POST["product"]);
    }

    if (empty($_POST["productName"])) {
        $productName = null;
    } else {
        $productName = trim($_POST["productName"]);
    }
    
    if (empty($_POST["plant"])) {
        $plantCode = null;
    } else {
        $plantCode = trim($_POST["plant"]);
    }

    if (empty($_POST["plantName"])) {
        $plantName = null;
    } else {
        $plantName = trim($_POST["plantName"]);
    }

    if (empty($_POST["transporter"])) {
        $transporterCode = null;
    } else {
        $transporterCode = trim($_POST["transporter"]);
    }

    if (empty($_POST["transporterName"])) {
        $transporterName = null;
    } else {
        $transporterName = trim($_POST["transporterName"]);
    }

    if (empty($_POST["vehicle"])) {
        $vehicle = null;
    } else {
        $vehicle = trim($_POST["vehicle"]);
    }

    if (empty($_POST["exDel"])) {
        $exDel = null;
    } else {
        $exDel = trim($_POST["exDel"]);
    }

    if (empty($_POST["orderLoad"])) {
        $orderLoad = null;
    } else {
        $orderLoad = trim($_POST["orderLoad"]);
    }

    if (empty($_POST["orderQty"])) {
        $orderQty = null;
    } else {
        $orderQty = trim($_POST["orderQty"]);
    }

    if (empty($_POST["remarks"])) {
        $remarks = null;
    } else {
        $remarks = trim($_POST["remarks"]);
    }

    if(!empty($poId))
    {
        if ($update_stmt = $db->prepare("UPDATE Sales_Order SET company_code=?, company_name=?, customer_code=?, customer_name=?, site_code=?, site_name=?, order_date=?, order_no=?, so_no=?, delivery_date=?, agent_code=?, agent_name=?, destination_code=?, destination_name=?, deliver_to_name=?, product_code=?, product_name=?, plant_code=?, plant_name=?, transporter_code=?, transporter_name=?, veh_number=?, exquarry_or_delivered=?, order_load=?, order_quantity=?, remarks=?, created_by=?, modified_by=? WHERE id=?")) 
        {
            $update_stmt->bind_param('sssssssssssssssssssssssssssss', $companyCode, $companyName, $customerCode, $customerName, $siteCode, $siteName, $orderDate, $orderNo, $soNo, $deliveryDate, $agentCode, $agentName, $destinationCode, $destinationName, $deliverToName, $productCode, $productName, $plantCode, $plantName, $transporterCode, $transporterName, $vehicle, $exDel, $orderLoad, $orderQty, $remarks, $username, $username, $poId);

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
                        "message"=> "Updated Successfully!!",
                    )
                );
            }
        }
    }
    else
    {
        $status = 'Open';
        if ($insert_stmt = $db->prepare("INSERT INTO Sales_Order (company_code, company_name, customer_code, customer_name, site_code, site_name, order_date, order_no, so_no, delivery_date, agent_code, agent_name, destination_code, destination_name, deliver_to_name, product_code, product_name, plant_code, plant_name, transporter_code, transporter_name, veh_number, exquarry_or_delivered, order_load, order_quantity, balance, remarks, status, created_by, modified_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
            $insert_stmt->bind_param('ssssssssssssssssssssssssssssss', $companyCode, $companyName, $customerCode, $customerName, $siteCode, $siteName, $orderDate, $orderNo, $soNo, $deliveryDate, $agentCode, $agentName, $destinationCode, $destinationName, $deliverToName, $productCode, $productName, $plantCode, $plantName, $transporterCode, $transporterName, $vehicle, $exDel, $orderLoad, $orderQty, $orderQty, $remarks, $status, $username, $username);

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