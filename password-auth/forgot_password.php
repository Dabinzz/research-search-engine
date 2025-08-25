<?php
session_start();
include '../db_config.php';
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check all tables for the email
    $tables = ['account', 'super_admin', 'encoder', 'editor', 'viewer'];
    $userFound = false;

    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT email FROM `$table` WHERE email = ?");
        if (!$stmt) {
            die("Error preparing statement for $table: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $userFound = true;
            break;
        }
    }
    
    if ($userFound) {
        // Generate OTP
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        // Send OTP via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'in-v3.mailjet.com';  // Use your mail server (e.g., Mailjet SMTP)
            $mail->SMTPAuth = true;
            $mail->Username = '';
            $mail->Password = '';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('', '');
            $mail->addAddress($email);
            $mail->Subject = 'BVT Account Password Reset';
            $mail->Body = "Your OTP code is: $otp";

            $mail->send();
            echo "<script>alert('OTP sent! Check your email.'); window.location='verify_otp.php';</script>";
        } catch (Exception $e) {
            echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "<script>alert('Email not found!');</script>";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
    <html lang="en">
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="imgs/PPRD LOGO.png" type="image/x-icon">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <title>Password Reset</title>
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            body {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-image: url('');
        background-size: cover; 
        background-position: center; 
        background-repeat: no-repeat; 
    }

            .login-container {
                background: white;
                padding: 2rem;
                border-radius: 10px;
                width: 550px;
                height: 235px;
                text-align: center;
                background-color: transparent;
            }
            .txt {
        text-align: center;
    }

            .txt h2 {
                margin-bottom: 70px;
                color: #333;
                font-size: 3.5rem;
                color: #2c3e50;
                font-family: "Playfair Display", serif;
            }
            .input-group {
                margin-bottom: 15px;
                text-align: left;
                font-family: 'Poppins', sans-serif;
            }
            .input-group label {
                display: block;
                margin-bottom: 5px;
                color: white;
                font-size: 15px;
                color: black;
            }
            input::placeholder {
                color: #2c3e50;
                font-style: italic; 
                font-size: 13px; 
                opacity: 50%;
            }
            .input-group input {
                width: 100%;
                padding: 13px;
                border: 1px solid #ccc;
                border-radius: 5px;
                font-size: 16px;
            }
            .login-btn {
                width: 80%;
                padding: 15px;
                background: #1abc9c;
                border: none;
                color: white;
                font-size: 18px;
                border-radius: 5px;
                cursor: pointer;
                transition: background 0.3s ease;
                margin-top: 5%;

            }
            .login-btn:hover {
                background:rgb(19, 133, 110);
            }
            .register-link {
                margin-top: 10px;
                display: block;
                color: black;
                text-decoration: none;
                font-size: 15px;
            }
            .register-link p{
                font-size: 15px;
            }
            .register-link:hover {
                text-decoration: underline;
            }
            /* Styles for the alert box */
    .alert-box {
        display: none; 
        width: auto;
        padding: 10px;
        background-color:rgb(248, 61, 28);
        color: white;
        border-radius: 5px;
        position: fixed;
        bottom: 20px;
        right: 20px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 999;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: Arial, sans-serif;
    }

    .border-text {
        position: relative;
        padding: 20px;
        border: 1px solid #2c3e50;
        width: auto;
        text-align: center;
        font-size: 2rem;
        color: #2c3e50;
        border-radius: 15px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.7);
    }

    .border-text::before {
        content: "Password Reset";
        position: absolute;
        top: -25px; 
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 0 10px;
        font-weight: bold;
    }
    @media (max-width: 414px) {
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
                font-size: 9px; 
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

        </style>
    </head>
    <body>
        <div class="txt">
        <h2><a href="index.php" style="color: #1abc9c; text-decoration: none;">BERF</a> Verification Tool</h2>
        </div>

        <div class="border-text">
        <div class="content">
        <div class="login-container">

    <form action="#" method="POST">
        <div class="input-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your registered email address" required>
        </div>
         
        <button type="submit" class="login-btn">Send OTP</button>
    </form>
    </div>
        </div>
    </div>


    </body>
    </html>


