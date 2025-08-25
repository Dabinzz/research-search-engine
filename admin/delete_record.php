<?php
include '../db_config.php'; 

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the 'id' parameter is set in the URL
    if (isset($_GET['id'])) {
        // Get the 'id' from the URL
        $record_id = $_GET['id'];

        // Prepare the DELETE SQL query
        $stmt = $pdo->prepare("DELETE FROM berf_masterlist WHERE Research_Title = :id");

        // Bind the 'id' parameter to the query
        $stmt->bindParam(':id', $record_id);

        // Execute the query to delete the record
        $stmt->execute();

        // Redirect to the page where the records are displayed after successful deletion
        echo "<script>alert('Deleted successfully!'); window.location='admin.php';</script>";
        exit();
    } else {
        // If no 'id' is provided in the URL, redirect to the index page or show an error
        echo "No record ID specified.";
    }
} catch (PDOException $e) {
    // Handle any errors
    echo "Error: " . $e->getMessage();
    die();
}
?>
