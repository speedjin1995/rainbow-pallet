<?php
$license = include(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'license.php');

$company = array_key_first($license);

// Database connection
$host = 'srv597.hstgr.io';
$dbname = 'u664110560_'.$company;
$username = 'u664110560_'.$company;
$password = '@Sync5500';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to select records where due_date is in the next month
    $query = "SELECT * FROM Plant";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($records)) {
        echo "No records found for processing.";
        exit;
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        foreach ($records as $record) {
            $plantId = $record['id'];
            // Update Plant sales, purchase, locals, misc back to 1
            $updateQuery = "UPDATE Plant SET sales = 1, purchase = 1, locals = 1, misc = 1 WHERE id = :id";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->bindParam(':id', $plantId);
            $stmt->execute();
            $stmt = null;
        }

        // Commit transaction
        $pdo->commit();

        echo count($records) . " records updated successfully.";
    }catch (Exception $e) {
        // Rollback transaction in case of error
        $pdo->rollBack();
        throw $e;
    }
} 
catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close connection
$pdo = null;
?>