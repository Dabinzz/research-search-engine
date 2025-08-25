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
        <title>Admin Login</title>
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
                height: 345px;
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
                margin-top: 40px;
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

        <a href="password-auth/forgot_password.php" class="register-link">Forgot Password?</a> 
        <button type="submit" class="login-btn">Login</button>
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