<?php
include '../tools/session.php'; 

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: super-login.php');
    exit();
}

include '../db_config.php';

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT first_name, last_name FROM super_admin WHERE account_id = :account_id";
    $user_stmt = $pdo->prepare($user_query);
    $user_stmt->bindParam(':account_id', $user_id, PDO::PARAM_INT);
    $user_stmt->execute();
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit();
    }

    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'account'; // Default to 'account' (Admins)
    
    $query = "SELECT 'Super Admin' AS role, account_id, first_name, last_name, email, user_name FROM super_admin
              UNION ALL 
              SELECT 'Admin' AS role, account_id, first_name, last_name, email, user_name FROM account
              UNION ALL 
              SELECT 'Editor' AS role, account_id, first_name, last_name, email, user_name FROM editor
              UNION ALL 
              SELECT 'Encoder' AS role, account_id, first_name, last_name, email, user_name FROM encoder
              UNION ALL
              SELECT 'Viewer' AS role, account_id, first_name, last_name, email, user_name FROM viewer
              ";
    
    $conditions = [];
    
    if ($search) {
        $conditions[] = "(first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR user_name LIKE :search)";
    }
    
    // Apply filter conditions
    if ($filter === 'super_admin') {
        $conditions[] = "role = 'Super Admin'";
    } elseif ($filter === 'account') {
        $conditions[] = "role = 'Admin'";
    } elseif ($filter === 'editor') {
        $conditions[] = "role = 'Editor'";
    } elseif ($filter === 'encoder') {
        $conditions[] = "role = 'Encoder'";
    } elseif ($filter === 'viewer') {
        $conditions[] = "role = 'Viewer'";
    }
    
    // Apply WHERE clause if conditions exist
    if (!empty($conditions)) {
        $query = "SELECT * FROM ($query) AS filtered_data WHERE " . implode(" AND ", $conditions);
    }
    
    $stmt = $pdo->prepare($query);
    
    if ($search) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
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
.table-container {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    border-radius: 10px;
    overflow-y: auto;
    max-height: 400px;
    scrollbar-width: thin; /* For Firefox */
    -ms-overflow-style: auto; /* For Internet Explorer and Edge */
}
/* Style The Dropdown Button */
.dropbtn {
  background-color: #1abc9c;
  color: white;
  padding: 10px;
  font-size: 16px;
  border: none;
  cursor: pointer;
}

/* The container <div> - needed to position the dropdown content */
.dropdown-account {
  position: relative;
  display: inline-block;
}

/* Dropdown Content (Hidden by Default) */
.dropdown-content {
  display: none;
  position: absolute;
  background-color:  #2c3e50;
  border-radius: 5px;
  min-width: 170px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
}

/* Links inside the dropdown */
.dropdown-content a {
    color: white;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
  font-size: 13px;
}

/* Change color of dropdown links on hover */
.dropdown-content a:hover {
    
    color: #1abc9c;
}

/* Show the dropdown menu on hover */
.dropdown-account:hover .dropdown-content {
  display: block;
}

/* Change the background color of the dropdown button when the dropdown content is shown */
.dropdown-account:hover .dropbtn {
  background-color:rgb(20, 150, 124);
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

<!-- Main Content -->
<div class="content">
    <h1>Account Manager</h1>
    
    <form method="GET" action="">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search..." />
        <button type="submit">Search</button>

        <a href="add-super-admin.php">
        <button type="button" class="add-bttn" style="background-color:#f44336;"><i class="fa-solid fa-plus"></i>  Super Admin </button>
    </a>

<div class="dropdown-account">
  <button class="dropbtn"><i class="fa-solid fa-plus"></i> Add an Account</button>
  <div class="dropdown-content">
    <a href="add-user.php">Admin Account</a>
    <a href="add-editor.php">Editor Account</a>
    <a href="add-encoder.php">Encoder Account</a>
    <a href="add-viewer.php">Viewer Account</a>
  </div>
</div>

<select name="filter" onchange="this.form.submit()">
    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All</option>
    <option value="super_admin" <?php echo $filter === 'super_admin' ? 'selected' : ''; ?>>Super Admins</option>
    <option value="account" <?php echo $filter === 'account' ? 'selected' : ''; ?>>Admins</option>
    <option value="editor" <?php echo $filter === 'editor' ? 'selected' : ''; ?>>Editor</option>
    <option value="encoder" <?php echo $filter === 'encoder' ? 'selected' : ''; ?>>Encoder</option>
    <option value="viewer" <?php echo $filter === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
</select>
    </form>

   
    <div class="table-container">
    <?php if (empty($data)) { ?>
        <p>No data found.</p>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th> </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td>
                            <a href='edit-accounts.php?id=<?php echo $row['account_id']; ?>'>
                                <button class='edit-btn'><i class='fa-solid fa-pen-to-square'></i></button>
                            </a>
                            <a href='super-delete.php?id=<?php echo $row['account_id']; ?>' 
                               onclick='return confirm("Are you sure you want to delete this record?");'>
                                <button class='delete-btn'><i class='fa-solid fa-trash'></i></button>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
</div>

</div>
<script>
    // Open Modal Function
    function openModal() {
            document.getElementById("myModal").style.display = "block";
        }

        // Close Modal Function
        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }

        // Close Modal if user clicks outside the modal content
        window.onclick = function(event) {
            let modal = document.getElementById("myModal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
</script>
</body>
</html>
