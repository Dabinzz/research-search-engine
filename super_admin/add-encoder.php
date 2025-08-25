<?php
include '../tools/session.php'; 

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: super-login.php');
    exit();
}

include '../db_config.php'; // Ensure this file contains the correct database credentials

$error = $success = ""; // Initialize error/success messages

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $user_name = trim($_POST['user_name']);
    $user_password = trim($_POST['password']); // Renamed to prevent overwriting the database password variable

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($user_name) || empty($user_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($user_name) < 5) {
        $error = "Username must be at least 5 characters long.";
    } else {
        try {
            // Create a new PDO instance
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if email or user_name already exists in any of the tables (account, super_admin, editor, encoder)
            $stmt = $pdo->prepare("
                SELECT * FROM account WHERE email = :email OR user_name = :user_name
                UNION 
                SELECT * FROM super_admin WHERE email = :email OR user_name = :user_name
                UNION 
                SELECT * FROM editor WHERE email = :email OR user_name = :user_name
                UNION 
                SELECT * FROM encoder WHERE email = :email OR user_name = :user_name
            ");
            $stmt->execute(['email' => $email, 'user_name' => $user_name]);

            if ($stmt->rowCount() > 0) {
                $error = "Email or username already exists in the system.";
            } else {
                // Hash the password
                $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);

                // Insert new user into 'encoder' table
                $stmt = $pdo->prepare("INSERT INTO encoder (first_name, last_name, email, user_name, password) 
                                            VALUES (:first_name, :last_name, :email, :user_name, :password)");
                $stmt->execute([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'user_name' => $user_name,
                    'password' => $hashed_password
                ]);

                $success = "Account successfully created.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../admin/admin-styles.css">
    <link rel="icon" href="imgs/PPRD LOGO.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Super Admin Panel</title>
</head>
<style>
    /* Styling for the label and checkbox */
label[for="show_password"] {
    display: flex;
    align-items: center;
    font-size: 16px;
    color: #555;
    margin-top: 5px;
    margin-left: 80px;
}

#show_password {
    margin-right: 5px;
    cursor: pointer;
}

.content input[type="password"] {
    width: 80%; /* Ensure the width is within the container */
    padding: 10px;
    font-size: 16px;
    border: 2px solid #ccc;
    border-radius: 5px;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

/* Add hover effect on checkbox */
#show_password:hover {
    transform: scale(1.1);
    transition: 0.3s;
}

.desc{
    text-align: left;
    padding: 1%;
    border-left: 10px solid #007bff;
    color: #2c3e50;
    background: lightgrey;
}

.desc p{
    font-size: 12px;
    padding: 0;
    margin: 0;
}

.desc h3{
    font-size: 16px;
    padding: 0;
    margin: 0;
    color: #007bff;
}
</style>
<body>
<div class="sidebar">
    <h1><i class="fa-solid fa-user-tie"></i></h1>
    <h3><span style="color: #1abc9c;">Super</span> Admin Account</h3>
    <button onclick="location.href='super-admin.php'"><i class="fa-solid fa-house"></i>‎ Dashboard</button>
    <button onclick="location.href='super-search.php'"><i class="fa-solid fa-file"></i>‎ ‎ Records</button>
    <button onclick="location.href='account.php'" style="background-color: #1abc9c; color: white; border: none; border-radius: 5px; cursor: pointer;">
        <i class="fa-solid fa-user"></i> ‎ ‎Accounts
    </button>
    <button onclick="location.href='super-update-account.php?id=<?php echo $_SESSION['user_id']; ?>'">
        <i class="fa-solid fa-gear"></i> Account Settings
    </button>
    <button onclick="location.href='../tools/logout.php'"><i class="fa-solid fa-right-from-bracket"></i>‎  Logout</button>
</div>

<!-- Main Content -->
<div class="content">
        <h1>Add a New Encoder Account</h1>

        <div class="desc">
        <h3><i class="fa-solid fa-circle-info"></i></h3>
        <p>The Encoder Account in the BERF Verification Tool is designed for users who are responsible for inputting new research records into the system. Encoders have the ability to add research entries but cannot edit or delete existing records, ensuring data integrity and minimizing unauthorized changes.</p>
        </div>

        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
        <form method="POST" action="">  
        <label for="first_name">First Name:</label>
            <input type="text" name="first_name"  required>

            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name"  required>

            <label for="email">Email:</label>
            <input type="email" name="email"  required>

            <label for="user_name">User name:</label>
            <input type="text" name="user_name" required>
            
            <label for="password">Password:</label>
            <input type="password" id="myInput" name="password" placeholder=" ">
            
            <label for="show_password">
                <input type="checkbox" id="show_password" onclick="myFunction()"> Show Password
            </label>

            <button type="submit">Add User</button>
        </form>
    </div>

<script>
        function myFunction() {
            var x = document.getElementById("myInput");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }
    </script>
</body>
</html>

