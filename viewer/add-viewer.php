<?php
include '../db_config.php'; // Ensure this file contains the correct database credentials
use PHPMailer\PHPMailer\PHPMailer;
                use PHPMailer\PHPMailer\Exception;
session_start(); // Start the session for OTP handling

$error = $success = ""; // Initialize error/success messages

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $user_name = trim($_POST['user_name']);
    $user_password = trim($_POST['password']);

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

            // Check if email or user_name already exists in any of the tables
            $stmt = $pdo->prepare("
                SELECT * FROM account WHERE email = :email OR user_name = :user_name
                UNION 
                SELECT * FROM super_admin WHERE email = :email OR user_name = :user_name
                UNION 
                SELECT * FROM editor WHERE email = :email OR user_name = :user_name
                UNION 
                SELECT * FROM encoder WHERE email = :email OR user_name = :user_name
                UNION 
                SELECT * FROM viewer WHERE email = :email OR user_name = :user_name
            ");
            $stmt->execute(['email' => $email, 'user_name' => $user_name]);

            if ($stmt->rowCount() > 0) {
                $error = "Email or username already exists in the system.";
            } else {
                // Store the user's information in session to use in OTP verification
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['email'] = $email;
                $_SESSION['user_name'] = $user_name;
                $_SESSION['password'] = password_hash($user_password, PASSWORD_DEFAULT);

                // Generate OTP
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;

                // Send OTP via PHPMailer
                require 'vendor/autoload.php';
                

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'in-v3.mailjet.com'; // Use your mail server (e.g., Mailjet SMTP)
                    $mail->SMTPAuth = true;
                    $mail->Username = '4d4793aa9670d8abbb5a05391c416da7';
                    $mail->Password = '840e3536a2a3fb67d81ad575df9a11d7';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('hassanalshino89@gmail.com', 'BERF Verification Tool');
                    $mail->addAddress($email);
                    $mail->Subject = 'BVT Account Email Verification';
                    $mail->Body = "Your OTP code is: $otp";

                    $mail->send();

                    // Redirect to OTP verification page
                    echo "<script>alert('OTP sent! Check your email.'); window.location='verify-otp.php';</script>";
                } catch (Exception $e) {
                    $error = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
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
    <title>Create an Account</title>
</head>
<style>
    /* Styling for the label and checkbox */
label[for="show_password"] {
    display: flex;
    align-items: center;
    font-size: 16px;
    color: #555;
    margin-top: 5px;
    margin-left: 5px;
}

#show_password {
    margin-right: 5px;
    cursor: pointer;
}

.content input[type="password"] {
    width: 100%; /* Ensure the width is within the container */
    padding: 10px;
    font-size: 16px;
    border: 2px solid #ccc;
    border-radius: 5px;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.3s ease;
}

form input[type="text"],
form input[type="email"],
form input[type="password"]{
    width: 100%;
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
.content {
    margin-left: 0px; /* Adjusted to avoid overlap with sidebar */
    padding: 20px;
}
button {
    padding: 10px 20px;
    font-size: 16px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-left: 10px;
}

button:hover {
    background-color:rgb(0, 93, 192);
}

button:active {
    background-color:rgb(0, 93, 192);
}
.xx{
    margin:3%;
}
@media (max-width: 768px) {
    .login-container {
                width: 250px;
                height: 300px;
            }
            .txt h2 {
                font-size: 2.5rem;
            }
            .input-group label {
                font-size: 11px;
            }
            .border-text {
        padding: 15px;
        font-size: 1.5rem;
    }
    input::placeholder {
                font-size: 11px; 
            }
            .input-group input {
                padding: 11px;
                font-size: 11px;
            }
            .content input[type="password"] {
    width: 100%;
    padding: 11px;
    font-size: 11px;
}
/* Styling for the label and checkbox */
label[for="show_password"] {
    font-size: 10px;
    margin-left: 5px;
}
.login-btn {
                width: 60%;
                padding: 8px;
                font-size: 11px;
                margin-top: 10%;
}
.register-link {
                margin-top: 10px;
                font-size: 11px;
            }
.create-acc{
                margin-top: 30px;
                font-size: 11px;
}
.alert-box {
        bottom: 10px;
        right: 10px;

    }

}
@media (max-width: 480px) {
    label[for="show_password"] {
    font-size: 11px;
}
.content input[type="password"] {
    font-size: 11px;
}

form input[type="text"],
form input[type="email"],
form input[type="password"]{
    font-size: 11px;
}
.desc p{
    font-size: 11px;
}

.desc h3{
    font-size: 13px;
}
label {
    font-size: 11px;
}
form button{
    font-size: 13px;
}

button {
    font-size: 13px;
    background-color: #007bff;
    margin-top: 5px;
}

.content h1{
    margin-bottom: 15%;
    font-size: 2rem;
}
}
</style>
<body>
    
<!-- Main Content -->
<div class="content">
        <h1>Create an Account</h1>

        <div class="desc">
        <h3><i class="fa-solid fa-circle-info"></i></h3>
        <p>This account can see all the lists of research in the database.</p>
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
            <button type="submit">Create an account</button>
            
        </form>
        
        <a href="../login.php"><button>Back to Login</button></a>
        
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

