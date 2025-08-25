<?php
session_start();
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

    // Check if an ID is passed
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Fetch the record from the database
        $stmt = $pdo->prepare("SELECT * FROM berf_masterlist WHERE Research_Title = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        // Fetch the record
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // If no record is found, display an error message
            echo "Record not found!";
            exit;
        }
    } else {
        echo "No ID provided!";
        exit;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// Handle form submission for updating the record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated values from the form
    $no = $_POST['No'];
    $berf_cycle = $_POST['BERF_Cycle'];
    $research_title = $_POST['Research_Title'];
    $division = $_POST['Division'];
    $abstract = $_POST['Abstract'];
    $author_1 = $_POST['Author_1'];
    $email_1 = $_POST['Email_1'];
    $author_2 = $_POST['Author_2'];
    $email_2 = $_POST['Email_2'];
    $author_3 = $_POST['Author_3'];
    $email_3 = $_POST['Email_3'];
    $status = $_POST['Status'];
    $date_completed = $_POST['Date_Completed'];

    // Add any other fields you want to update

    try {
        // Update the record in the database
        $stmt = $pdo->prepare("UPDATE berf_masterlist SET No = :No, BERF_Cycle = :BERF_Cycle, Research_Title = :Research_Title, Division = :Division, Abstract = :Abstract, Author_1 = :Author_1, Email_1 = :Email_1, Author_2 = :Author_2, Email_2 = :Email_2, Author_3 = :Author_3, Email_3 = :Email_3, Status = :Status, Date_Completed = :Date_Completed WHERE Research_Title = :id");
        $stmt->bindParam(':No', $no);
        $stmt->bindParam(':BERF_Cycle', $berf_cycle);
        $stmt->bindParam(':Research_Title', $research_title);
        $stmt->bindParam(':Division', $division);
        $stmt->bindParam(':Abstract', $abstract);
        $stmt->bindParam(':Author_1', $author_1);
        $stmt->bindParam(':Email_1', $email_1);
        $stmt->bindParam(':Author_2', $author_2);
        $stmt->bindParam(':Email_2', $email_2);
        $stmt->bindParam(':Author_3', $author_3);
        $stmt->bindParam(':Email_3', $email_3);
        $stmt->bindParam(':Status', $status);
        $stmt->bindParam(':Date_Completed', $date_completed);
        $stmt->bindParam(':id', $id);
    
        // Execute the statement
        if ($stmt->execute()) {
            // Show success message before redirecting
            echo "<script>alert('Updated Successfully'); window.location.href='super-admin.php';</script>";
            exit();
        } else {
            echo "<p>Error updating record.</p>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        die();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="add-forms.css">
    <link rel="icon" href="imgs/PPRD LOGO.png" type="image/x-icon">
    <title>Update Record</title>
</head>
<style>
    button {
    margin-top: 2px;
    padding: 10px 15px;
    background-color: #1abc9c;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s ease;   
}

.content {
    padding-top: 2%;
}

label{
    font-size: 12px;
    text-align: left;
    margin-left: 12%;
}

.content textarea {
    width: 100%;  /* Adjust width as needed */
    max-width: 400px; /* Optional: Limits width */
    height: 220px; /* Set a fixed height */
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 12px;
    resize: none; 
    font-family: Arial, sans-serif;

    
}
</style>
<body>
    <h1>Update Record</h1>

    <div class="content">
    <!-- Edit Form -->
    <form method="POST" action="" class="add">       
    <div class="left">
        <label for="">No:</label>
        <input type="text" name="No" id="No" placeholder="No" value="<?php echo isset($row['No']) ? htmlspecialchars($row['No']) : ''; ?>" required><br><br>
        
        <label for="">BERF Cycle:</label>
        <input type="text" name="BERF_Cycle" id="BERF_Cycle" placeholder="BERF Cycle" value="<?php echo isset($row['BERF_Cycle']) ? htmlspecialchars($row['BERF_Cycle']) : ''; ?>" required><br><br>
        
        <label for="">Research Title:</label>
        <input type="text" name="Research_Title" id="Research_Title" placeholder="Research Title" value="<?php echo isset($row['Research_Title']) ? htmlspecialchars($row['Research_Title']) : ''; ?>" required><br><br>
        
        <label for="">Division:</label>
        <input type="text" name="Division" id="Division" placeholder="Division" value="<?php echo isset($row['Division']) ? htmlspecialchars($row['Division']) : ''; ?>" required><br><br>

        <label for="">Abstract:</label>
        <textarea name="Abstract" id="Abstract" placeholder="Abstract" required><?php echo isset($row['Abstract']) ? htmlspecialchars($row['Abstract']) : ''; ?></textarea><br><br>
    </div>
    
    <div class="right">
        <label for="">Author 1:</label>
        <input type="text" name="Author_1" id="Author_1" placeholder="Author 1" value="<?php echo isset($row['Author_1']) ? htmlspecialchars($row['Author_1']) : ''; ?>" required><br><br>

        <label for="">Email 1:</label>
        <input type="email" name="Email_1" id="Email_1" placeholder="Email 1" value="<?php echo isset($row['Email_1']) ? htmlspecialchars($row['Email_1']) : ''; ?>" required><br><br>

        <label for="">Author 2:</label>
        <input type="text" name="Author_2" id="Author_2" placeholder="Author 2" value="<?php echo isset($row['Author_2']) ? htmlspecialchars($row['Author_2']) : ''; ?>"><br><br>

        <label for="">Email 2:</label>
        <input type="email" name="Email_2" id="Email_2" placeholder="Email 2" value="<?php echo isset($row['Email_2']) ? htmlspecialchars($row['Email_2']) : ''; ?>"><br><br>

        <label for="">author 3:</label>
        <input type="text" name="Author_3" id="Author_3" placeholder="Author 3" value="<?php echo isset($row['Author_3']) ? htmlspecialchars($row['Author_3']) : ''; ?>"><br><br>

        <label for="">Email 3:</label>
        <input type="email" name="Email_3" id="Email_3" placeholder="Email 3" value="<?php echo isset($row['Email_3']) ? htmlspecialchars($row['Email_3']) : ''; ?>"><br><br>

        <label for="">Status:</label>
        <select name="Status" id="Status" required>
            <option value="Completed" <?php echo (isset($row['Status']) && $row['Status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
            <option value="Ongoing" <?php echo (isset($row['Status']) && $row['Status'] == 'Ongoing') ? 'selected' : ''; ?>>Ongoing</option>
            <option value="For Refund" <?php echo (isset($row['Status']) && $row['Status'] == 'For Refund') ? 'selected' : ''; ?>>For Refund</option>
            <option value="Refunded" <?php echo (isset($row['Status']) && $row['Status'] == 'Refunded') ? 'selected' : ''; ?>>Refunded</option>
            <option value="Contact your DRC" <?php echo (isset($row['Status']) && $row['Status'] == 'Contact your DRC') ? 'selected' : ''; ?>>Contact your DRC</option>
            <option value="Archived" <?php echo (isset($row['Status']) && $row['Status'] == 'Archived') ? 'selected' : ''; ?>>Archived</option>
        </select><br><br>

        <label for="">Data Completed:</label>
        <input type="text" name="Date_Completed" id="Date_Completed" placeholder="Date Completed" value="<?php echo isset($row['Date_Completed']) ? htmlspecialchars($row['Date_Completed']) : ''; ?>" required><br><br>
        
        <button type="submit">Update Record</button>
    </div>
    
</form>
</div>
</body>
</html>
