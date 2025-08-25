<?php
include '../db_config.php';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the 'id' parameter is set in the URL
    if (isset($_GET['id'])) {
        $record_id = $_GET['id'];

        // Start a transaction
        $pdo->beginTransaction();

        // Delete from 'super_admin' table first (to avoid foreign key issues)
        $stmt1 = $pdo->prepare("DELETE FROM super_admin WHERE account_id = :id");
        $stmt1->bindParam(':id', $record_id);
        $stmt1->execute();

        // Delete from 'account' table
        $stmt2 = $pdo->prepare("DELETE FROM account WHERE account_id = :id");
        $stmt2->bindParam(':id', $record_id);
        $stmt2->execute();

        // Delete from 'account' table
        $stmt2 = $pdo->prepare("DELETE FROM encoder WHERE account_id = :id");
        $stmt2->bindParam(':id', $record_id);
        $stmt2->execute();

        // Delete from 'account' table
        $stmt2 = $pdo->prepare("DELETE FROM editor WHERE account_id = :id");
        $stmt2->bindParam(':id', $record_id);
        $stmt2->execute();

        // Delete from 'account' table
        $stmt2 = $pdo->prepare("DELETE FROM viewer WHERE account_id = :id");
        $stmt2->bindParam(':id', $record_id);
        $stmt2->execute();

        // Commit the transaction
        $pdo->commit();

        // Redirect after successful deletion
        header("Location: account.php");
        exit();
    } else {
        echo "No record ID specified.";
    }
} catch (PDOException $e) {
    // Rollback the transaction in case of error
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
