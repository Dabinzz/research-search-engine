<?php
include '../tools/session.php'; 

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: super-login.php');
    exit();
}

// Check if the account ID is provided in the URL
if (!isset($_GET['id'])) {
    header('Location: super-admin.php');
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
$query = "SELECT * FROM super_admin WHERE account_id = :account_id";
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
    $user_name = $_POST['user_name'];
    $password = $_POST['password'];

    // Check if the username already exists (excluding the current user's ID)
    $check_query = "SELECT COUNT(*) FROM account WHERE user_name = :user_name AND account_id != :account_id";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':user_name', $user_name);
    $check_stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $user_exists = $check_stmt->fetchColumn();

    if ($user_exists) {
        echo '<div class="alert-box" id="alertBox">
        <span class="alert-message"><i class="fa-solid fa-circle-info"></i> Username is already exist.</span>
    </div>';
    } else {
        try {
            // Fetch the current password from the database
            $query = "SELECT password FROM super_admin WHERE account_id = :account_id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
            $stmt->execute();
            $currentPassword = $stmt->fetchColumn();

            // If password field is empty, use the current password (no changes)
            if (empty($password)) {
                $hashedPassword = $currentPassword; // Keep existing password
            } else {
                // If a new password is provided, hash it
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            }

            // Update user data
            $update_query = "UPDATE super_admin SET first_name = :first_name, last_name = :last_name, email = :email, user_name = :user_name, password = :password WHERE account_id = :account_id";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':first_name', $first_name);
            $update_stmt->bindParam(':last_name', $last_name);
            $update_stmt->bindParam(':email', $email);
            $update_stmt->bindParam(':user_name', $user_name);
            $update_stmt->bindParam(':password', $hashedPassword); // Use current or new hashed password
            $update_stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);

            $update_stmt->execute();

            $_SESSION['success_message'] = "Account updated successfully.";
            header('Location: super-admin.php');
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../admin/admin-styles.css">
    <link rel="icon" href="imgs/PPRD LOGO.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<style>
    /* Prevent horizontal scrolling */
body, html {
    margin: 0;
    padding: 0;
    overflow-x: hidden; /* Prevent horizontal scroll */
}

/* Styling for the label and checkbox */
label[for="show_password"] {
    display: flex;
    align-items: center;
    font-size: 16px;
    color: #555;
    margin-top: 0px;
    margin-left: 90px;
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
    <button onclick="location.href='../tools/logout.php'"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
</div>

    <div class="content">
        <h1>Update User Account</h1>
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
            <input type="password" id="myInput" name="password" placeholder="Enter new password (Optional)">
            
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