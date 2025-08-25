<?php
include '../tools/session.php'; 

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: login.php');
    exit();
}

include '../db_config.php'; 

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get the data from the form and sanitize it
        $no = filter_var($_POST['No'], FILTER_SANITIZE_NUMBER_INT);
        $berf_cycle = filter_var($_POST['berf_cycle'], FILTER_SANITIZE_STRING);
        $research_title = filter_var($_POST['research_title'], FILTER_SANITIZE_STRING);
        $division = filter_var($_POST['division'], FILTER_SANITIZE_STRING);
        $abstract = filter_var($_POST['abstract'], FILTER_SANITIZE_STRING);
        $author_1 = filter_var($_POST['author_1'], FILTER_SANITIZE_STRING);
        $email_1 = filter_var($_POST['email_1'], FILTER_SANITIZE_EMAIL);
        $author_2 = filter_var($_POST['author_2'], FILTER_SANITIZE_STRING);
        $email_2 = filter_var($_POST['email_2'], FILTER_SANITIZE_EMAIL);
        $author_3 = filter_var($_POST['author_3'], FILTER_SANITIZE_STRING);
        $email_3 = filter_var($_POST['email_3'], FILTER_SANITIZE_EMAIL);
        $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
        $date_completed = $_POST['date_completed'];  // Assuming it's a valid date format
    
        // Insert the new record into the database
        $sql = "INSERT INTO berf_masterlist (No, BERF_Cycle, Research_Title, Division, Abstract, Author_1, Email_1, Author_2, Email_2, Author_3, Email_3, Status, Date_Completed)
                VALUES (:no, :berf_cycle, :research_title, :division, :abstract, :author_1, :email_1, :author_2, :email_2, :author_3, :email_3, :status, :date_completed)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':no', $no);
        $stmt->bindParam(':berf_cycle', $berf_cycle);
        $stmt->bindParam(':research_title', $research_title);
        $stmt->bindParam(':division', $division);
        $stmt->bindParam(':abstract', $abstract);
        $stmt->bindParam(':author_1', $author_1);
        $stmt->bindParam(':email_1', $email_1);
        $stmt->bindParam(':author_2', $author_2);
        $stmt->bindParam(':email_2', $email_2);
        $stmt->bindParam(':author_3', $author_3);
        $stmt->bindParam(':email_3', $email_3);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':date_completed', $date_completed);
    
        // Execute the statement
        if ($stmt->execute()) {
            // Show success message before redirecting
            echo "<script>alert('Success! Research has been added.'); window.location.href='super-admin.php';</script>";
            exit();
        } else {
            echo "<p>Error adding record.</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Record</title>
    <link rel="stylesheet" href="add-forms.css">
    <link rel="icon" href="imgs/PPRD LOGO.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <h1>Add New Record</h1>

    
<div class="content">
<form action="upload.php" method="post" enctype="multipart/form-data" class="file">
    <label for="file">Choose an excel file (.xlsx):</label>
    <input type="file" name="file" id="file" required>
    <button type="submit">Upload</button>
</form>

    <form method="POST" action="" class="add">       
        <div class="left">
        <input type="text" name="no" id="no" placeholder="No" required><br><br>

       
        <input type="text" name="berf_cycle" id="berf_cycle" placeholder="BERF Cycle" required><br><br>

      
        <input type="text" name="research_title" id="research_title" placeholder="Research Title" required><br><br>

        
        <input type="text" name="division" id="division" placeholder="Division" required><br><br>

 
        <textarea type="textarea" name="abstract" id="abstract" placeholder="Abstract" required></textarea><br><br>
        </div>
        
        <div class="right">
    
        <input type="text" name="author_1" id="author_1" placeholder="Author 1" required><br><br>

     
        <input type="email" name="email_1" id="email_1" placeholder="Email 1" required><br><br>

      
        <input type="text" name="author_2" id="author_2" placeholder="Author 2" ><br><br>

    
        <input type="email" name="email_2" id="email_2" placeholder="Email 2"><br><br>


        <input type="text" name="author_3" id="author_3" placeholder="Author 3" ><br><br>

        <input type="email" name="email_3" id="email_3" placeholder="Email 3"><br><br>

        <select name="status" id="status" required>
            <option value="Completed" <?php if (isset($status) && $status == 'Completed') echo 'selected'; ?>>Completed</option>
            <option value="Ongoing" <?php if (isset($status) && $status == 'Ongoin') echo 'selected'; ?>>Ongoing</option>
            <option value="For Refund" <?php if (isset($status) && $status == 'For Refund') echo 'selected'; ?>>For Refund</option>
            <option value="Refunded" <?php if (isset($status) && $status == 'Refunded') echo 'selected'; ?>>Refunded</option>
            <option value="Contact your DRC" <?php if (isset($status) && $status == 'Contact your DRC') echo 'selected'; ?>>Contact your DRC</option>
            <option value="Archived" <?php if (isset($status) && $status == 'Archived') echo 'selected'; ?>>Archived</option>
        </select><br><br>

        <input type="text" name="date_completed" id="date_completed" placeholder="Date Completed" required><br><br>
        <button type="submit">Add Record</button>
    </div>
        

        
</div>
    
</body>
</html>
