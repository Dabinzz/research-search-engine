<?php
require '../vendor/autoload.php'; // Include PhpSpreadsheet
require '../db_config.php'; // Your database connection file

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $file = $_FILES["file"]["tmp_name"];

    // Load the Excel file
    $spreadsheet = IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    if (empty($rows)) {
        die("The uploaded file is empty!");
    }

    // Remove header row (assuming the first row contains column names)
    array_shift($rows);

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO berf_masterlist (No, BERF_Cycle, Division, Research_Title, Abstract, Author_1, Email_1, Author_2, Email_2, Author_3, Email_3, Status, Date_Completed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($rows as $row) {
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }

        // Ensure all required values exist before inserting
        if (count($row) < 13) {
            continue;
        }

        $stmt->bind_param("issssssssssss", $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9], $row[10], $row[11], $row[12]);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    echo "<script>alert('File Added successfully!'); window.location='super-admin.php';</script>";
} else {
    echo "Invalid file!";
}
?>

