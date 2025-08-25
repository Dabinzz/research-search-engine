<?php
include '../tools/session.php'; 
// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: login.php');
    exit();
}

include '../db_config.php'; 

try {
    // Create a new PDO instance (single connection)
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to fetch data from the `account` table (assuming you want to fetch the first name and the number of records)
    $query = "SELECT * FROM editor";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total records and completed records (you can modify this based on your actual logic)
    $total_records = count($data);
    $completed_records = 0; // Assuming you have some column to check for completed records, for now it's 0

    // Assuming you want to show the first user's name in the greeting (you can modify this logic)
    $first_name = isset($data[0]['first_name']) ? $data[0]['first_name'] : "Guest"; // Get the first user's first name (or "Guest")
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
    <title>Records</title>
</head>
<body>

<div class="sidebar">
    <h1><i class="fa-solid fa-user-tie"></i></h1>
    <h3>Editor Account</h3>
    <button onclick="location.href='../editor/editor.php'"><i class="fa-solid fa-house"></i>‎ Dashboard</button>
    <button onclick="location.href='../editor/search.php'" style="background-color: #1abc9c; color: white; border: none; border-radius: 5px; cursor: pointer;">
    <i class="fa-solid fa-file"></i>‎ ‎ Records</button>
    <button onclick="location.href='../editor/update_account.php?id=<?php echo $_SESSION['user_id']; ?>'">
    <i class="fa-solid fa-gear"></i> Account Settings</button>
    <button onclick="location.href='../tools/logout.php'"><i class="fa-solid fa-right-from-bracket"></i>‎  Logout</button>
</div>

<!-- Main Content -->
<div class="content">
<?php
include '../db_config.php';

try {
    // Create a new PDO instance (single connection)
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get unique BERF_Cycles for dropdown
    $cycle_query = "SELECT DISTINCT BERF_Cycle FROM berf_masterlist ORDER BY BERF_Cycle ASC";
    $cycle_stmt = $pdo->prepare($cycle_query);
    $cycle_stmt->execute();
    $cycles = $cycle_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get selected BERF_Cycle from user input
    $selected_cycle = isset($_GET['cycle']) ? $_GET['cycle'] : '';

    // Modify query based on selected BERF_Cycle
    $query = "SELECT TRIM(Division) AS Division, COUNT(Research_Title) AS Research_Count FROM berf_masterlist";
    if ($selected_cycle) {
        $query .= " WHERE BERF_Cycle = :cycle";
    }
    $query .= " GROUP BY Division";
    
    $stmt = $pdo->prepare($query);
    if ($selected_cycle) {
        $stmt->bindParam(':cycle', $selected_cycle, PDO::PARAM_STR);
    }
    $stmt->execute();

    // Fetch results and trim division names
    $division_counts = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $division_counts[trim($row['Division'])] = $row['Research_Count'];
    }

    // Get overall total
    $overall_query = "SELECT COUNT(Research_Title) AS overall_total FROM berf_masterlist";
    if ($selected_cycle) {
        $overall_query .= " WHERE BERF_Cycle = :cycle";
    }
    
    $overall_stmt = $pdo->prepare($overall_query);
    if ($selected_cycle) {
        $overall_stmt->bindParam(':cycle', $selected_cycle, PDO::PARAM_STR);
    }
    $overall_stmt->execute();
    $overall_total = $overall_stmt->fetch(PDO::FETCH_ASSOC)['overall_total'];

    // Count Completed Research Titles
    $completed_query = "SELECT COUNT(*) AS completed_total FROM berf_masterlist WHERE Status = 'Completed'";
    if ($selected_cycle) {
        $completed_query .= " AND BERF_Cycle = :cycle";
    }
    
    $completed_stmt = $pdo->prepare($completed_query);
    if ($selected_cycle) {
        $completed_stmt->bindParam(':cycle', $selected_cycle, PDO::PARAM_STR);
    }
    $completed_stmt->execute();
    $completed_total = $completed_stmt->fetch(PDO::FETCH_ASSOC)['completed_total'];

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}
?>

<!-- Display total counts per division -->
<div class="division-counts">
    <h3>Admin Panel Dashboard</h3>

    <div class="line">
    <h4>Number of Research per Division</h4>
    <form method="GET" id="filterForm">
        <select name="cycle" id="cycle">
            <option value="">All</option>
            <?php foreach ($cycles as $cycle): ?>
                <option value="<?php echo $cycle; ?>" <?php echo ($selected_cycle == $cycle) ? 'selected' : ''; ?>>
                    <?php echo $cycle; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form> 
    </div>
    
<div class="divider">
<div class="parent-">
<div class="div-1">Caloocan City: <br><br>
    <span class="count"><?php echo isset($division_counts['Caloocan City']) ? $division_counts['Caloocan City'] : 0; ?></span>
</div>

<div class="div-2">Las Piñas City: <br><br>
    <span class="count"><?php echo isset($division_counts['Las Piñas City']) ? $division_counts['Las Piñas City'] : 0; ?></span>
</div>

<div class="div-3">Makati City: <br><br>
    <span class="count"><?php echo isset($division_counts['Makati City']) ? $division_counts['Makati City'] : 0; ?></span>
</div>

<div class="div-4">Malabon City: <br><br>
    <span class="count"><?php echo isset($division_counts['Malabon City']) ? $division_counts['Malabon City'] : 0; ?></span>
</div>

<div class="div-5">Mandaluyong City: <br><br>
    <span class="count"><?php echo isset($division_counts['Mandaluyong City']) ? $division_counts['Mandaluyong City'] : 0; ?></span>
</div>

<div class="div-6">Manila City: <br><br>
    <span class="count"><?php echo isset($division_counts['Manila City']) ? $division_counts['Manila City'] : 0; ?></span>
</div>

<div class="div-7 hidden">Marikina City: <br><br>
    <span class="count"><?php echo isset($division_counts['Marikina City']) ? $division_counts['Marikina City'] : 0; ?></span>
</div>

<div class="div-8 hidden">Muntinlupa City: <br><br>
    <span class="count"><?php echo isset($division_counts['Muntinlupa City']) ? $division_counts['Muntinlupa City'] : 0; ?></span>
</div>

<div class="div-9 hidden">Navotas City: <br><br>
    <span class="count"><?php echo isset($division_counts['Navotas City']) ? $division_counts['Navotas City'] : 0; ?></span>
</div>

<div class="div-10 hidden">Parañaque City: <br><br>
    <span class="count"><?php echo isset($division_counts['Parañaque City']) ? $division_counts['Parañaque City'] : 0; ?></span>
</div>

<div class="div-11 hidden">Pasay City: <br><br>
    <span class="count"><?php echo isset($division_counts['Pasay City']) ? $division_counts['Pasay City'] : 0; ?></span>
</div>

<div class="div-12 hidden">Pasig City: <br><br>
    <span class="count"><?php echo isset($division_counts['Pasig City']) ? $division_counts['Pasig City'] : 0; ?></span>
</div>

<div class="div-13 hidden">Quezon City: <br><br>
    <span class="count"><?php echo isset($division_counts['Quezon City']) ? $division_counts['Quezon City'] : 0; ?></span>
</div>

<div class="div-14 hidden">San Juan City: <br><br>
    <span class="count"><?php echo isset($division_counts['San Juan City']) ? $division_counts['San Juan City'] : 0; ?></span>
</div>

<div class="div-15 hidden">Taguig City & Pateros: <br><br>
    <span class="count"><?php echo isset($division_counts['Taguig City and Pateros']) ? $division_counts['Taguig City and Pateros'] : 0; ?></span>
</div>

<div class="div-16 hidden">Valenzuela City: <br><br>
    <span class="count"><?php echo isset($division_counts['Valenzuela City']) ? $division_counts['Valenzuela City'] : 0; ?></span>
</div>

<div class="div-17 hidden">Regional Office: <br><br>
    <span class="count"><?php echo isset($division_counts['Regional Office']) ? $division_counts['Regional Office'] : 0; ?></span>
</div>

    <div class="button-container">
    <button id="toggleBtn" class="toggle-btn">Show More <i class="fa-solid fa-plus"></i></button>
</div>
</div>
</div>
<br>
</div>

<div class="parent-total">
<div class="div-18">Total No. of Research: <br><br>
    <span class="count"><?php echo $overall_total; ?></span>
</div>
<div class="div-19">Total Completed: <br><br>
    <span class="count"><?php echo $completed_total; ?></span>
</div>
</div>
</div>


<script>
document.getElementById("toggleBtn").addEventListener("click", function () {
    const hiddenDivs = document.querySelectorAll(".parent- .hidden");
    const isExpanded = this.getAttribute("data-expanded") === "true";

    hiddenDivs.forEach(div => {
        div.style.display = isExpanded ? "none" : "block";
    });

    if (isExpanded) {
        this.innerHTML = 'Show More <i class="fa-solid fa-plus"></i>';
        this.setAttribute("data-expanded", "false");
    } else {
        this.innerHTML = 'Show Less <i class="fa-solid fa-minus"></i>';
        this.setAttribute("data-expanded", "true");
    }
});

document.getElementById('cycle').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
</script>
</body>
</html>
