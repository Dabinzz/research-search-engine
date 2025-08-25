<?php
session_start(); // Start the session to get the stored user information and OTP

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = trim($_POST['otp']); // The OTP entered by the user

    // Check if the OTP entered matches the one stored in the session
    if ($entered_otp == $_SESSION['otp']) {
        try {
            // Insert the user into the database
            include '../db_config.php'; // Include database credentials

            // Create a new PDO instance
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Insert the user data into the 'viewer' table
            $stmt = $pdo->prepare("INSERT INTO viewer (first_name, last_name, email, user_name, password) 
                                   VALUES (:first_name, :last_name, :email, :user_name, :password)");

            $stmt->execute([
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name'],
                'email' => $_SESSION['email'],
                'user_name' => $_SESSION['user_name'],
                'password' => $_SESSION['password']
            ]);

            // Clear session data
            session_unset();
            session_destroy();

            // Redirect to the login page or success page
            echo "<script>alert('Account successfully created!'); window.location='../login.php';</script>";

        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="icon" href="imgs/PPRD LOGO.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Verify OTP</title>
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
    <h1>Verify OTP</h1>
    <form method="POST" action="">
        <label for="otp">Enter OTP:</label>
        <input type="text" name="otp" required>
        <button type="submit">Verify OTP</button>
    </form>
</body>
</html>
