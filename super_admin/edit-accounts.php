<?php
include '../tools/session.php'; 

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

include '../db_config.php'; 

$error = $success = ""; 

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $error = "Error: User ID is missing.";
} else {
    $account_id = $_GET['id'];

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // **Check if the user is a Super Admin**
        $stmt = $pdo->prepare("SELECT * FROM super_admin WHERE account_id = :account_id");
        $stmt->execute(['account_id' => $account_id]);
        $super_admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // After checking for super_admin, check for other roles
if ($super_admin) {
    $table = "super_admin";
} else {
    // Check if user is an encoder
    $stmt = $pdo->prepare("SELECT * FROM encoder WHERE account_id = :account_id");
    $stmt->execute(['account_id' => $account_id]);
    $encoder = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($encoder) {
        $table = "encoder";
    } else {
        // Check if user is an editor
        $stmt = $pdo->prepare("SELECT * FROM editor WHERE account_id = :account_id");
        $stmt->execute(['account_id' => $account_id]);
        $editor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($editor) {
            $table = "editor";
        } else {
            // Check if user is a viewer
            $stmt = $pdo->prepare("SELECT * FROM viewer WHERE account_id = :account_id");
            $stmt->execute(['account_id' => $account_id]);
            $viewer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($viewer) {
                $table = "viewer";
            } else {
                // Default to account table
                $table = "account";
            }
        }
    }
}

        // **Fetch user details from the determined table**
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE account_id = :account_id");
        $stmt->execute(['account_id' => $account_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Error: User not found.";
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $user_name = trim($_POST['user_name']);
            $user_password = trim($_POST['password']);

            if (empty($first_name) || empty($last_name) || empty($email) || empty($user_name)) {
                $error = "All fields except password are required.";
            } else {
                // Check for existing email/username excluding the current user
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table 
                                       WHERE (email = :email OR user_name = :user_name) 
                                       AND account_id != :account_id");
                $stmt->execute([
                    'email' => $email,
                    'user_name' => $user_name,
                    'account_id' => $account_id
                ]);
                $exists = $stmt->fetchColumn();

                if ($exists > 0) {
                    $error = "Email or username already exists.";
                } else {
                    // Update query (conditionally including password)
                    if (!empty($user_password)) {
                        $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
                        $update_sql = "UPDATE $table SET first_name = :first_name, last_name = :last_name, 
                                       email = :email, user_name = :user_name, password = :password 
                                       WHERE account_id = :account_id";
                        $params = [
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'user_name' => $user_name,
                            'password' => $hashed_password,
                            'account_id' => $account_id
                        ];
                    } else {
                        $update_sql = "UPDATE $table SET first_name = :first_name, last_name = :last_name, 
                                       email = :email, user_name = :user_name 
                                       WHERE account_id = :account_id";
                        $params = [
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'user_name' => $user_name,
                            'account_id' => $account_id
                        ];
                    }

                    // Execute update
                    $stmt = $pdo->prepare($update_sql);
                    $stmt->execute($params);

                    $success = "Account updated successfully.";

                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM $table WHERE account_id = :account_id");
                    $stmt->execute(['account_id' => $account_id]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<title>Edit User Account</title>
</head>

<style>
label[for="show_password"] {
    display: flex;
    align-items: center;
    font-size: 16px;
    color: #555;
    margin-left: 90px;
}
#show_password {
    margin-right: 5px;
    cursor: pointer;
}
.content input[type="password"] {
    width: 80%;
    padding: 10px;
    font-size: 16px;
    border: 2px solid #ccc;
    border-radius: 5px;
}
#show_password:hover {
    transform: scale(1.1);
    transition: 0.3s;
}
</style>

<body>
<div class="sidebar">
    <h1><i class="fa-solid fa-user-tie"></i></h1>
    <h3><span style="color: #1abc9c;">Super</span> Admin Account</h3>
    <button onclick="location.href='../super_admin/super-admin.php'"><i class="fa-solid fa-house"></i>‎ Dashboard</button>
    <button onclick="location.href='../super_admin/super-search.php'"><i class="fa-solid fa-file"></i>‎ ‎ Records</button>
    <button onclick="location.href='../super_admin/account.php'" style="background-color: #1abc9c; color: white; border: none; border-radius: 5px;">
        <i class="fa-solid fa-user"></i> ‎ ‎Accounts
    </button>
    <?php if (isset($_SESSION['user_id'])): ?>
    <button onclick="location.href='../super_admin/super-update-account.php?id=<?php echo $_SESSION['user_id']; ?>'">
        <i class="fa-solid fa-gear"></i> Account Settings
    </button>
    <?php endif; ?>
    <button onclick="location.href='../tools/logout.php'"><i class="fa-solid fa-right-from-bracket"></i>‎  Logout</button>
</div>

<div class="content">
    <h1>Edit User Account</h1>
    <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <?php if (!empty($success)) echo "<p style='color: green;'>$success</p>"; ?>

    <form method="POST">
        <label for="first_name">First Name:</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>

        <label for="last_name">Last Name:</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>

        <label for="email">Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>

        <label for="user_name">User Name:</label>
        <input type="text" name="user_name" value="<?php echo htmlspecialchars($user['user_name'] ?? ''); ?>" required>

        <label for="password">Password:</label>
        <input type="password" id="myInput" name="password" placeholder="Enter new password">

        <label for="show_password">
            <input type="checkbox" id="show_password" onclick="myFunction()"> Show Password
        </label>

        <button type="submit">Edit User Profile</button>
    </form>
</div>

<script>
function myFunction() {
    var x = document.getElementById("myInput");
    x.type = (x.type === "password") ? "text" : "password";
}
</script>

</body>
</html>
