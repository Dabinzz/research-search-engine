<?php
session_start();
include 'db_config.php';


// Secure database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

// Ensure form submission is via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate input
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        echo '<div class="alert-box"><span class="alert-message"><i class="fa-solid fa-circle-info"></i> Username and Password are required.</span></div>';
        exit();
    }

    // List of roles to check
    $roles = ['account', 'super_admin', 'encoder', 'editor' ,'viewer'];
    
    foreach ($roles as $role) {
        // Prepare SQL statement
        $stmt = $conn->prepare("SELECT account_id, user_name, password FROM `$role` WHERE user_name = ?");
        
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error); // Debugging
        }
    
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
    
            if (hash_equals($user['user_name'], $username) && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
    
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['account_id'];
                $_SESSION['username'] = $user['user_name'];
                $_SESSION['role'] = $role;
    
                // Redirect based on role
                if ($role === 'super_admin') {
                    header("Location: super_admin/super-admin.php");
                } elseif ($role === 'encoder') {
                    header("Location: encoder/encoder.php");
                } elseif ($role === 'editor') {
                    header("Location: editor/editor.php");
                } elseif ($role === 'viewer') {
                    header("Location: viewer/viewer.php");
                }else {
                    header("Location: admin/admin.php");
                }
                exit();
            }
        }
        $stmt->close();
    }
    
    // Show error message if login fails
    echo '<div class="alert-box"><span class="alert-message"><i class="fa-solid fa-circle-info"></i> Invalid Username or Password</span></div>';
}

// Close DB connection
$conn->close();
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
        <title>Login</title>
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
                padding: 1rem;
                border-radius: 10px;
                width: 300px;
                height: 365px;
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
                font-size: 13px;
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
        content: "Login";
        position: absolute;
        top: -25px; 
        left: 50%;
        transform: translateX(-50%);
        background: white;
        padding: 0 10px;
        font-weight: bold;
    }

    /* Styling for the label and checkbox */
label[for="show_password"] {
    display: flex;
    align-items: center;
    font-size: 16px;
    color: #555;
    margin-top: 0px;
    margin-left: 10px;
}

#show_password {
    margin-right: 5px;
    cursor: pointer;
}

.content input[type="password"] {
    width: 100%;
    padding: 13px;
    font-size: 13px;
    border: 1px solid #ccc;
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

.create-acc {
                margin-top: 40px;
                display: block;
                color: black;
                text-decoration: none;
                font-size: 15px;
            }
            .create-acc p{
                font-size: 15px;
            }
            .create-acc:hover {
                text-decoration: underline;
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
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="ex. admin_123" required>
        </div>
        
        <div class="input-group">
        <label for="password">Password:</label>
        <input type="password" id="myInput" name="password" placeholder="******">
        </div>

        <label for="show_password">
                <input type="checkbox" id="show_password" onclick="myFunction()"> Show Password
            </label>
        
        <a href="viewer/add-viewer.php" class="create-acc">Create an Account</a>
        <button type="submit" class="login-btn">Login</button>
        <a href="password-auth/forgot_password.php" class="register-link">Forgot Password?</a> 
    </form>
    </div>
        </div>
        
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