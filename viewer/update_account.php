<?php
include '../tools/session.php';

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if the account ID is provided in the URL
if (!isset($_GET['id'])) {
    header('Location: viewer.php');
    exit();
}

$account_id = $_GET['id'];

include '../db_config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

// Fetch the account details
$query = "SELECT * FROM viewer WHERE account_id = :account_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
$stmt->execute();
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    echo "Account not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $user_name = !empty($_POST['user_name']) ? $_POST['user_name'] : $account['user_name']; // Keep original username if not changed
    $password = $_POST['password'];

    // Check if username has actually changed before checking for duplicates
    if ($user_name !== $account['user_name']) {
        $check_query = "
            SELECT 
                (SELECT COUNT(*) FROM account WHERE user_name = :user_name AND account_id != :account_id) +
                (SELECT COUNT(*) FROM super_admin WHERE user_name = :user_name) +
                (SELECT COUNT(*) FROM encoder WHERE user_name = :user_name AND account_id != :account_id) +
                (SELECT COUNT(*) FROM editor WHERE user_name = :user_name) +
                (SELECT COUNT(*) FROM viewer WHERE user_name = :user_name AND account_id != :account_id)
            AS total_count
        ";

        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->bindParam(':user_name', $user_name, PDO::PARAM_STR);
        $check_stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $check_stmt->execute();
        $user_exists = $check_stmt->fetchColumn();

        if ($user_exists) {
            echo '<div class="alert-box" id="alertBox">
                <span class="alert-message"><i class="fa-solid fa-circle-info"></i> Username already exists.</span>
            </div>';
            exit(); // Stop further execution if username is taken
        }
    }

    try {
        // Fetch the current password from the database
        $query = "SELECT password FROM viewer WHERE account_id = :account_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
        $stmt->execute();
        $currentPassword = $stmt->fetchColumn();

        // Check if the user provided a new password
        if (!empty($password) && $password !== $currentPassword) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash new password
        } else {
            $hashedPassword = $currentPassword; // Keep existing hashed password
        }

        // Update user data
        $update_query = "UPDATE viewer SET first_name = :first_name, last_name = :last_name, email = :email, user_name = :user_name, password = :password WHERE account_id = :account_id";
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->bindParam(':first_name', $first_name);
        $update_stmt->bindParam(':last_name', $last_name);
        $update_stmt->bindParam(':email', $email);
        $update_stmt->bindParam(':user_name', $user_name);
        $update_stmt->bindParam(':password', $hashedPassword);
        $update_stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);

        $update_stmt->execute();

        echo "<script>
        window.onload = function() {
            alert('Account updated successfully.');
            window.location.href = 'viewer.php';
        };
      </script>";

        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="icon" href="imgs/PPRD LOGO.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Account Update</title>
</head>
<style> 
.content {
    margin-left: 0px; /* Adjusted to avoid overlap with sidebar */
    margin-top: 10%;
    padding: 20px;
}
        .menu-icon{
            display: none;
        }
        .close-icon{
            display: none;
        }
        @media (max-width: 768px) {
            body, html {
    margin: 0;
    padding: 0;
    overflow-x: hidden; /* Prevent horizontal scroll */
}
.menu-icon{
            display: flex;
        }
        .close-icon{
            display: flex;
        }
        .menu-icon {
            font-size: 24px;
            cursor: pointer;
            padding: 10px;
            position: fixed;
            top: 10px;
            left: 10px;
            background: #1abc9c;
            color: white;
            border-radius: 5px;
            z-index: 1000;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100%;
            background-color: #2c3e50;
            color: white;
            padding-top: 60px;
            transition: 0.3s;
            z-index: 999;
        }
        .sidebar.active {
            left: 0;
        }
        .sidebar h1, .sidebar h3 {
            text-align: center;
        }
        .close-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: white;
        }
        .parent {
    text-align: left;
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 columns for the middle row */
    grid-template-rows: auto auto auto; /* 3 rows */
    column-gap: 0px;
    row-gap: 15px; /* Add row gap for better separation */
    padding:0px;
    margin:0px;
}

/* Position each div in the grid */
.div1 { grid-column: 1; grid-row: 1; }
.div2 { grid-column: 2; grid-row: 1; }
.div3 { grid-column: 1; grid-row: 2; }
.div4 { grid-column: 2; grid-row: 2; }
.div5 { grid-column: 3; grid-row: 2; }
.div6 { grid-column: 1; grid-row: 3; }
.div7 { grid-column: 2; grid-row: 3; }

/* Common styles for all div headings */
.div1 h3, .div2 h3, .div3 h3, .div4 h3, .div5 h3, .div6 h3, .div7 h3 {
    padding: 0px;
}

/* Common styles for all labels */
.div1 label, .div2 label, .div3 label, .div4 label, .div5 label, .div6 label, .div7 label {
    display: block;
    padding: 0;
    font-size: 9px;
    cursor: pointer;
}

/* Style for the search form */
form {
    margin: 3%;
    text-align: center;
}

form input[type="text"],
form input[type="email"],
form input[type="password"]{
    width: 100%;
    padding: 10px;
    font-size: 11px;
}

form button {
    font-size: 11px;
}
.content h1{
    font-size: 2rem;
}

.div1 h3, .div3 h3, .div6 h3{
    font-size: 13px;
}

th, td {
    padding: 10px 10px;
}

td {
    font-size: 10px;
}

th {
    font-size: 11.5px;
}
        }

        @media (max-width: 480px) {
            body, html {
    margin: 0;
    padding: 0;
    overflow-x: hidden; /* Prevent horizontal scroll */
}
.menu-icon{
            display: flex;
        }
        .close-icon{
            display: flex;
        }
        .menu-icon {
            font-size: 24px;
            cursor: pointer;
            padding: 10px;
            position: fixed;
            top: 10px;
            left: 10px;
            background: #1abc9c;
            color: white;
            border-radius: 5px;
            z-index: 1000;
        }
        .close-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: white;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100%;
            background-color: #2c3e50;
            color: white;
            padding-top: 60px;
            transition: 0.3s;
            z-index: 999;
        }
        .parent {
    text-align: left;
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 columns for the middle row */
    grid-template-rows: auto auto auto; /* 3 rows */
    column-gap: 0px;
    row-gap: 15px; /* Add row gap for better separation */
    padding:0px;
    margin:0px;
}

/* Position each div in the grid */
.div1 { grid-column: 1; grid-row: 1; }
.div2 { grid-column: 2; grid-row: 1; }
.div3 { grid-column: 1; grid-row: 2; }
.div4 { grid-column: 2; grid-row: 2; }
.div5 { grid-column: 3; grid-row: 2; }
.div6 { grid-column: 1; grid-row: 3; }
.div7 { grid-column: 2; grid-row: 3; }

/* Common styles for all div headings */
.div1 h3, .div2 h3, .div3 h3, .div4 h3, .div5 h3, .div6 h3, .div7 h3 {
    padding: 0px;
}

/* Common styles for all labels */
.div1 label, .div2 label, .div3 label, .div4 label, .div5 label, .div6 label, .div7 label {
    display: block;
    padding: 0;
    font-size: 9px;
    cursor: pointer;
}

/* Style for the search form */
form {
    margin: 3%;
    text-align: center;
}

form input[type="text"],
form input[type="email"],
form input[type="password"]{
    width: 100%;
    padding: 10px;
    font-size: 11px;
}

form button {
    font-size: 11px;
}
.content h1{
    font-size: 2rem;
}

.div1 h3, .div3 h3, .div6 h3{
    font-size: 13px;
}

th, td {
    padding: 10px 10px;
}

td {
    font-size: 10px;
}

th {
    font-size: 11.5px;
}
        }

    </style>
<body>
<div class="menu-icon" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </div>
    <div class="sidebar" id="sidebar">
        <span class="close-icon" onclick="toggleSidebar()">&times;</span>
        <h1><i class="fa-solid fa-user-tie"></i></h1>
        <h3>Viewer Account</h3>
        <button onclick="location.href='../viewer/viewer.php'"><i class="fa-solid fa-house"></i> Dashboard</button>
        <button onclick="location.href='../viewer/update_account.php?id=<?php echo $_SESSION['user_id']; ?>'" style="background-color: #1abc9c; color: white; border: none; border-radius: 5px; cursor: pointer;">
        <i class="fa-solid fa-gear"></i> Account Settings</button>
        <button onclick="location.href='../tools/logout.php'"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
    </div>
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

    <div class="content">
        <h1>Update Account</h1>
        <form method="POST" action="">
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" value="<?php echo htmlspecialchars($account['first_name']); ?>" required>

            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" value="<?php echo htmlspecialchars($account['last_name']); ?>" required>

            <label for="email">Email:</label>
            <input type="text" name="email" value="<?php echo htmlspecialchars($account['email']); ?>" required>

            <label for="user_name">User name:</label>
            <input type="text" name="user_name" value="<?php echo htmlspecialchars($account['user_name']); ?>" required>
            
            <label for="password">Password:</label>
            <input type="password" id="myInput" name="password" placeholder="Set a new password or leave blank for default">
            
            <label for="show_password">
                <input type="checkbox" id="show_password" onclick="myFunction()"> Show Password
            </label>

            <button type="submit">Update Account</button>
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