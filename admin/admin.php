<?php
include '../tools/session.php'; 

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../db_config.php'; 

try {
    // Create a new PDO instance (single connection)
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the logged-in user's details
    $user_id = $_SESSION['user_id']; // Ensure this is set during login
    $user_query = "SELECT first_name, last_name FROM account WHERE account_id = :account_id";
    $user_stmt = $pdo->prepare($user_query);
    $user_stmt->bindParam(':account_id', $user_id, PDO::PARAM_INT);
    $user_stmt->execute();
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit();
    }

    // Greeting message
    $greeting = "Hello, " . htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']) . "!"; 

    // SQL query to fetch data from the `account` table
    $query = "SELECT * FROM account";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total records and completed records
    $total_records = count($data);
    $completed_records = 0; // Assuming you have some column to check for completed records, for now it's 0

} catch (PDOException $e) {
    // Handle any errors
    echo "Error: " . $e->getMessage();
    die();
}

// Get the search query, selected divisions, and selected BERF Cycle
$search = isset($_GET['search']) ? $_GET['search'] : '';
$selected_columns = isset($_GET['columns']) ? $_GET['columns'] : ['BERF_Cycle', 'Research_Title', 'Division', 'Date_Completed'];
$selected_divisions = isset($_GET['division']) ? $_GET['division'] : []; // Array of selected divisions
$selected_berf_cycles = isset($_GET['berf_cycle']) ? $_GET['berf_cycle'] : []; // Array of selected BERF Cycles

// Start the SQL query
$query = "SELECT * FROM berf_masterlist";

// Initialize an array to store bind values
$bindValues = [];

// Add the search filter if a search term is provided
if ($search) {
    $query .= " WHERE CONCAT_WS(' ', BERF_Cycle, Research_Title, Division, Date_Completed) LIKE :search";
    $bindValues['search'] = '%' . $search . '%'; // Store the bind value for search
}

// Add the division filter if divisions are selected
if (!empty($selected_divisions)) {
    // If the search filter is already applied, add AND to combine conditions
    if ($search) {
        $query .= " AND Division IN (" . implode(", ", array_fill(0, count($selected_divisions), "?")) . ")";
    } else {
        $query .= " WHERE Division IN (" . implode(", ", array_fill(0, count($selected_divisions), "?")) . ")";
    }
    $bindValues = array_merge($bindValues, $selected_divisions); // Merge selected divisions into bindValues
}

// Add the BERF Cycle filter if BERF Cycles are selected
if (!empty($selected_berf_cycles)) {
    // If both search and division filters are applied, use AND to combine
    if ($search || !empty($selected_divisions)) {
        $query .= " AND BERF_Cycle IN (" . implode(", ", array_fill(0, count($selected_berf_cycles), "?")) . ")";
    } else {
        $query .= " WHERE BERF_Cycle IN (" . implode(", ", array_fill(0, count($selected_berf_cycles), "?")) . ")";
    }
    $bindValues = array_merge($bindValues, $selected_berf_cycles); // Merge selected BERF Cycles into bindValues
}

// Prepare the SQL query
$stmt = $pdo->prepare($query);

// Bind the search parameter if the search term is provided
if ($search) {
    $stmt->bindValue(':search', $bindValues['search']);
    unset($bindValues['search']); // Remove search from the array after binding
}

// Bind division and BERF Cycle parameters dynamically
$bindIndex = 1;
foreach ($bindValues as $value) {
    $stmt->bindValue($bindIndex++, $value);
}

// Execute the query
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Admin Panel</title>
</head>
<body>

<div class="sidebar">
    <h1><i class="fa-solid fa-user-tie"></i></h1>
    <h3>Admin Account</h3>
    <button onclick="location.href='../admin/admin.php'" style="background-color: #1abc9c; color: white; border: none; border-radius: 5px; cursor: pointer;">
    <i class="fa-solid fa-house"></i>‎ Dashboard</button>
    <button onclick="location.href='../admin/search.php'"><i class="fa-solid fa-file"></i>‎ ‎ Records</button>
    <button onclick="location.href='../admin/update_account.php?id=<?php echo $_SESSION['user_id']; ?>'">
    <i class="fa-solid fa-gear"></i> Account Settings</button>
    <button onclick="location.href='../tools/logout.php'"><i class="fa-solid fa-right-from-bracket"></i>‎  Logout</button>
</div>

<!-- Main Content -->
<div class="content">
<h1><?php echo $greeting; ?></h1>
<!-- Search filter form -->
<form method="GET" action="">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search..." />
    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
</form>
<div class="divider">
<!-- Column, Division, and BERF Cycle selection form -->
<form method="GET" action="" class="column-selection">
    <div class="parent">
        <div class="div1"><h3>Columns</h3>
            <label><input type="checkbox" name="columns[]" value="No" <?php echo in_array('No', $selected_columns) ? 'checked' : ''; ?>> No</label>
            <label><input type="checkbox" name="columns[]" value="BERF_Cycle" <?php echo in_array('BERF_Cycle', $selected_columns) ? 'checked' : ''; ?>> BERF Cycle</label>
            <label><input type="checkbox" name="columns[]" value="Research_Title" <?php echo in_array('Research_Title', $selected_columns) ? 'checked' : ''; ?>> Research Title</label>
            <label><input type="checkbox" name="columns[]" value="Division" <?php echo in_array('Division', $selected_columns) ? 'checked' : ''; ?>> Division</label>
            <label><input type="checkbox" name="columns[]" value="Abstract" <?php echo in_array('Abstract', $selected_columns) ? 'checked' : ''; ?>> Abstract</label>
            <label><input type="checkbox" name="columns[]" value="Author_1" <?php echo in_array('Author_1', $selected_columns) ? 'checked' : ''; ?>> Author 1</label>
            <label><input type="checkbox" name="columns[]" value="Email_1" <?php echo in_array('Email_1', $selected_columns) ? 'checked' : ''; ?>> Email 1</label>
        </div>

        <div class="div2"><h3>‎ </h3>
        <label><input type="checkbox" name="columns[]" value="Author_2" <?php echo in_array('Author_2', $selected_columns) ? 'checked' : ''; ?>> Author 2</label>
            <label><input type="checkbox" name="columns[]" value="Email_2" <?php echo in_array('Email_2', $selected_columns) ? 'checked' : ''; ?>> Email 2</label>
            <label><input type="checkbox" name="columns[]" value="Author_3" <?php echo in_array('Author_3', $selected_columns) ? 'checked' : ''; ?>> Author 3</label>
            <label><input type="checkbox" name="columns[]" value="Email_3" <?php echo in_array('Email_3', $selected_columns) ? 'checked' : ''; ?>> Email 3</label>
            <label><input type="checkbox" name="columns[]" value="Status" <?php echo in_array('Status', $selected_columns) ? 'checked' : ''; ?>> Status</label>
            <label><input type="checkbox" name="columns[]" value="Date_Completed" <?php echo in_array('Date_Completed', $selected_columns) ? 'checked' : ''; ?>> Date Completed</label>
        </div>

        <div class="div3"><h3>Divisions</h3>
            <label><input type="checkbox" name="division[]" value="Caloocan City" <?php echo in_array('Caloocan City', $selected_divisions) ? 'checked' : ''; ?>> Caloocan City</label>
            <label><input type="checkbox" name="division[]" value="Las Piñas City" <?php echo in_array('Las Piñas City', $selected_divisions) ? 'checked' : ''; ?>> Las Piñas City</label>
            <label><input type="checkbox" name="division[]" value="Makati City" <?php echo in_array('Makati City', $selected_divisions) ? 'checked' : ''; ?>> Makati City</label>
            <label><input type="checkbox" name="division[]" value="Malabon City" <?php echo in_array('Malabon City', $selected_divisions) ? 'checked' : ''; ?>> Malabon City</label>
            <label><input type="checkbox" name="division[]" value="Mandaluyong City" <?php echo in_array('Mandaluyong City', $selected_divisions) ? 'checked' : ''; ?>> Mandaluyong City</label>
            <label><input type="checkbox" name="division[]" value="Manila City" <?php echo in_array('Manila City', $selected_divisions) ? 'checked' : ''; ?>> Manila City</label>
            <label><input type="checkbox" name="division[]" value="Marikina City" <?php echo in_array('Marikina City', $selected_divisions) ? 'checked' : ''; ?>> Marikina City</label>
            
        </div>

        <div class="div4"><h3>‎</h3>
            <label><input type="checkbox" name="division[]" value="Muntinlupa City" <?php echo in_array('Muntinlupa City', $selected_divisions) ? 'checked' : ''; ?>> Muntinlupa City</label>
            <label><input type="checkbox" name="division[]" value="Navotas City" <?php echo in_array('Navotas City', $selected_divisions) ? 'checked' : ''; ?>> Navotas City</label>
            <label><input type="checkbox" name="division[]" value="Parañaque City" <?php echo in_array('Parañaque City', $selected_divisions) ? 'checked' : ''; ?>> Parañaque City</label>
            <label><input type="checkbox" name="division[]" value="Pasay City" <?php echo in_array('Pasay City', $selected_divisions) ? 'checked' : ''; ?>> Pasay City</label>
            <label><input type="checkbox" name="division[]" value="Pasig City" <?php echo in_array('Pasig City', $selected_divisions) ? 'checked' : ''; ?>> Pasig City</label>
            <label><input type="checkbox" name="division[]" value="Quezon City" <?php echo in_array('Quezon City', $selected_divisions) ? 'checked' : ''; ?>> Quezon City</label>
            <label><input type="checkbox" name="division[]" value="San Juan City" <?php echo in_array('San Juan City', $selected_divisions) ? 'checked' : ''; ?>> San Juan City</label>
        </div>

        <div class="div5"><h3>‎</h3>
            <label><input type="checkbox" name="division[]" value="Taguig City and Pateros" <?php echo in_array('Taguig City and Pateros', $selected_divisions) ? 'checked' : ''; ?>> Taguig City and Pateros</label>
            <label><input type="checkbox" name="division[]" value="Valenzuela City" <?php echo in_array('Valenzuela City', $selected_divisions) ? 'checked' : ''; ?>> Valenzuela City</label>
            <label><input type="checkbox" name="division[]" value="Regional Office" <?php echo in_array('Regional Office', $selected_divisions) ? 'checked' : ''; ?>> Regional Office</label>   
        </div>

        <div class="div6"><h3>BERF Cycle</h3>
            <label><input type="checkbox" name="berf_cycle[]" value="2016" <?php echo in_array('2016', $selected_berf_cycles) ? 'checked' : ''; ?>> 2016</label>
            <label><input type="checkbox" name="berf_cycle[]" value="2017" <?php echo in_array('2017', $selected_berf_cycles) ? 'checked' : ''; ?>> 2017</label>
            <label><input type="checkbox" name="berf_cycle[]" value="2018" <?php echo in_array('2018', $selected_berf_cycles) ? 'checked' : ''; ?>> 2018</label>
            <label><input type="checkbox" name="berf_cycle[]" value="2019" <?php echo in_array('2019', $selected_berf_cycles) ? 'checked' : ''; ?>> 2019</label>
            <label><input type="checkbox" name="berf_cycle[]" value="2020" <?php echo in_array('2020', $selected_berf_cycles) ? 'checked' : ''; ?>> 2020</label>
            <label><input type="checkbox" name="berf_cycle[]" value="2021" <?php echo in_array('2021', $selected_berf_cycles) ? 'checked' : ''; ?>> 2021</label>
            <label><input type="checkbox" name="berf_cycle[]" value="2022" <?php echo in_array('2022', $selected_berf_cycles) ? 'checked' : ''; ?>> 2022</label>
        </div>

        <div class="div7"><h3>‎</h3>
            <label><input type="checkbox" name="berf_cycle[]" value="2023" <?php echo in_array('2023', $selected_berf_cycles) ? 'checked' : ''; ?>> 2023</label>
            <label><input type="checkbox" name="berf_cycle[]" value="2024" <?php echo in_array('2024', $selected_berf_cycles) ? 'checked' : ''; ?>> 2024</label>
            <label><input type="checkbox" name="berf_cycle[]" value="2025" <?php echo in_array('2025', $selected_berf_cycles) ? 'checked' : ''; ?>> 2025</label>
            <label><input type="checkbox" name="berf_cycle[]" value="2026" <?php echo in_array('2026', $selected_berf_cycles) ? 'checked' : ''; ?>> 2026</label>
        </div>
    </div>
    <button class="add-bttn" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
    <a href="add_record.php">
        <button type="button" class="add-bttn"><i class="fa-solid fa-plus"></i> Add a Research</button>
    </a>
</form>



<?php if (empty($data)) { ?>
    <p>No data found.</p>
<?php } else { ?>
    <!-- Table to display selected data -->
<div class='table-container'>
    <table>
        <thead>
            <tr>
                <?php if (in_array('No', $selected_columns)) { ?><th>No</th><?php } ?>
                <?php if (in_array('BERF_Cycle', $selected_columns)) { ?><th>Berf Cycle</th><?php } ?>
                <?php if (in_array('Research_Title', $selected_columns)) { ?><th>Research Title</th><?php } ?>
                <?php if (in_array('Division', $selected_columns)) { ?><th>Division</th><?php } ?>
                <?php if (in_array('Abstract', $selected_columns)) { ?><th>Abstract</th><?php } ?>
                <?php if (in_array('Author_1', $selected_columns)) { ?><th>Author 1</th><?php } ?>
                <?php if (in_array('Email_1', $selected_columns)) { ?><th>Email 1</th><?php } ?>
                <?php if (in_array('Author_2', $selected_columns)) { ?><th>Author 2</th><?php } ?>
                <?php if (in_array('Email_2', $selected_columns)) { ?><th>Email 2</th><?php } ?>
                <?php if (in_array('Author_3', $selected_columns)) { ?><th>Author 3</th><?php } ?> <!-- Include Author_3 -->
                <?php if (in_array('Email_3', $selected_columns)) { ?><th>Email 3</th><?php } ?> <!-- Include Email_3 -->
                <?php if (in_array('Status', $selected_columns)) { ?><th>Status</th><?php } ?>
                <?php if (in_array('Date_Completed', $selected_columns)) { ?><th>Date Completed</th><?php } ?>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $counter = 0;
            foreach ($data as $row) {
                $rowClass = ($counter % 2 == 0) ? 'even' : 'odd'; // Even or odd class
                echo "<tr class='$rowClass'>";

                if (in_array('No', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['No']) . "</td>";
                }
                if (in_array('BERF_Cycle', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['BERF_Cycle']) . "</td>";
                }
                if (in_array('Research_Title', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['Research_Title']) . "</td>";
                }
                if (in_array('Division', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['Division']) . "</td>";
                }
                if (in_array('Abstract', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['Abstract']) . "</td>";
                }
                if (in_array('Author_1', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['Author_1']) . "</td>";
                }
                if (in_array('Email_1', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['Email_1']) . "</td>";
                }
                if (in_array('Author_2', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['Author_2']) . "</td>";
                }
                if (in_array('Email_2', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['Email_2']) . "</td>";
                }
                if (in_array('Author_3', $selected_columns)) {  
                    echo "<td>" . htmlspecialchars($row['Author_3']) . "</td>";
                }
                if (in_array('Email_3', $selected_columns)) {  
                    echo "<td>" . htmlspecialchars($row['Email_3']) . "</td>";
                }
                if (in_array('Status', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                }
                if (in_array('Date_Completed', $selected_columns)) {
                    echo "<td>" . htmlspecialchars($row['Date_Completed']) . "</td>";
                }

                // Edit and Delete buttons
                $record_id = $row['Research_Title']; 
                echo "<td style='display: flex; align-items: center; gap: 10px; padding-top: 0px;'>"; 
                echo "<a href='edit_record.php?id=$record_id'><button class='edit-btn'><i class='fa-solid fa-pen-to-square'></i></button></a> ";
                echo "<a href='delete_record.php?id=$record_id' onclick='return confirm(\"Are you sure you want to delete this record?\")'><button class='delete-btn'><i class='fa-solid fa-trash'></i></button></a>";
                echo "</td>";
                echo "</tr>";
                $counter++;
            }
            ?>
        </tbody>
    </table>
</div>

<?php } ?>
</div>



<script>
    
</script>
</body>
</html>
