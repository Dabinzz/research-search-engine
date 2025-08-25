

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BERF Verification Tool</title>
    <link rel="icon" href="imgs/PPRD LOGO.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Banner -->
    <div class="banner">
        <h1 class="banner-text">RESULT</h1>
        <div class="home-bttn">
    <a href="home"><button>Back to Search</button></a>
</div>
    </div>

    <div class="custom-shape-divider-top-1740728296">
    <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" class="shape-fill"></path>
        <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" class="shape-fill"></path>
        <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" class="shape-fill"></path>
    </svg>
</div>


<?php
include 'db_config.php'; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the 'berf_cycle' from the URL parameter
$research_title = isset($_GET['research_title']) ? $_GET['research_title'] : ''; // Corrected parameter name to lowercase

if (!empty($research_title)) {
    // Prepare the SQL query to fetch all details for the specific berf_cycle
    $sql = "SELECT * FROM berf_masterlist WHERE Research_Title = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        // Bind the 'berf_cycle' value to the query
        $stmt->bind_param('s', $research_title);  // Use $berf_cycle (lowercase)

        // Execute the statement
        $stmt->execute();
        
        // Get the result
        $result = $stmt->get_result();
        
        // Check if any data was returned
        if ($result->num_rows > 0) {
            // Output the full details of the berf_cycle
            $row = $result->fetch_assoc();
            echo "
                                    <div class='head-txt'>" . htmlspecialchars($row['Research_Title']) . "</div><div class='output'>
                                    
    <div class='tab'>
        <button class='tablinks' onclick='openCity(event, \"home\")' id='defaultOpen'><i class='fa-solid fa-house'></i> Home</button>
        <button class='tablinks' onclick='openCity(event, \"authors\")'><i class='fa-solid fa-circle-user'></i>Authors</button>
        <button class='tablinks' onclick='openCity(event, \"abstract\")'><i class='fa-solid fa-book'></i>Abstract</button>
        
    </div>
    <div id='home' class='tabcontent'>
        <div class='firsttab'>
            <div class='headings'>
            <h3>BERF Cycle</h3>
            <div class='ans'>" . htmlspecialchars($row['BERF_Cycle']) . "</div>
            </div>   

            <div class='headings'>
            <h3>Division:</h3>
            <div class='ans'>" . htmlspecialchars($row['Division']) . "</div>
            </div>
        
            <div class='headings'>
            <h3>Research Title:</h3>
            <div class='ans'>" . htmlspecialchars($row['Research_Title']) . "</div>
            </div>

            <div class='headings'>
            <h3>Date Completed:</h3>
            <div class='ans'>" . nl2br(htmlspecialchars($row['Date_Completed'])) . "</div>
            </div>
        
            <div class='headings'>
            <h3>Status:</h3>
            <div class='ans g'>" . htmlspecialchars($row['Status']) . "</div>
            </div>

            </div>
        </div>
  
    <div id='authors' class='tabcontent'>
        <div class='headings'>
            <h3>Author 1:</h3>
            <div class='ans'>" . htmlspecialchars($row['Author_1']) . "</div>
        </div>

        <div class='headings'>
            <h3>Author 2:</h3>
            <div class='ans'>" . htmlspecialchars($row['Author_2']) . "</div>
        </div>

        <div class='headings'>
            <h3>Author 3:</h3>
            <div class='ans'>" . htmlspecialchars($row['Author_3']) . "</div>
        </div>
        
    </div>
  
    <div id='abstract' class='tabcontent'>
        <div class='headings'>
            <h3>Abstract:</h3>
            <div class='ans-right abs'>" . nl2br(htmlspecialchars($row['Abstract'])) . "</div>
        </div>
    </div>
</div>";
        } else {
            echo "No details found.";
        }
        
        // Close the statement
        $stmt->close();
    } else {
        echo "Error in preparing the SQL query.";
    }
} else {
    echo "Invalid or missing details.";
}

// Close the connection
$conn->close();
?>

<script src="scripts.js"></script>
</body>
</html>