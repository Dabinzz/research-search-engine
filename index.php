<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_config.php'; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$search = "";
$result = null;

// Check if the search form is submitted and the search term is not empty
if (isset($_POST['search']) && !empty($_POST['search'])) {
    // Sanitize the search input
    $search = trim($_POST['search']);

    // Prepare the SQL query using a prepared statement to prevent SQL injection
    $sql = "SELECT * FROM berf_masterlist WHERE Research_Title LIKE ? LIMIT 7";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind the search parameter to the query
        $searchTerm = "%$search%";
        $stmt->bind_param("s", $searchTerm);

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Check for errors
        if (!$result) {
            die("Query failed: " . $stmt->error);
        }

        // Close the statement
        $stmt->close();
    } else {
        die("Failed to prepare the SQL statement: " . $conn->error);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BERF Verification Tool</title>
    <link rel="icon" href="imgs/PPRD LOGO.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<style>
        /* Styling for the trigger div */
        .open-modal {
            cursor: pointer;
        }

        /* Modal Background */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: transparent;
    font-family: Arial, sans-serif;
}

/* Modal Content - Positioned at the Top */
.modal-content {
    background-color: #2C3E50;
    padding: 10px;
    width: 50%;
    height: auto; /* Adjust height based on content */
    margin: 20px auto; /* Push it to the top */
    text-align: center;
    border-radius: 10px;
    position: relative;
    opacity: 90%;
}


        .updates{
            padding: 15px;
        }
        
        .modal-content h2 {
            color: white;
        }

        .modal-content h5 {
            color: white;
            margin: 2%;
            text-align: left;
        }

        .modal-content p {
            text-align: left;
            color: white;
            font-size: 12px;
            padding: 0;
            margin: 0;
        }

        /* Close Button */
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
            color: white;
        }

        /* styles.css */
        .login-button {
    background-color: #3EB489;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s ease;
}

    .login-button:hover {
    background-color:rgb(47, 138, 104);
}

        @media (max-width: 414px){
            .modal-content h2 {
            color: white;
            font-size: 14px;
        }

        .modal-content h5 {
            color: white;
            margin: 2%;
            text-align: left;
            font-size: 12px;
        }

        .modal-content p {
            text-align: left;
            color: white;
            font-size: 10px;
            padding: 0;
            margin: 0;
        }

        .login-button {
    background-color: #3EB489;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 15px;
    transition: background 0.3s ease;
}

    .login-button:hover {
    background-color:rgb(47, 138, 104);
}
        }
    </style>
<body>
    <!-- Banner -->
    <div class="banner">
        <a class="clickable-berf" href="home"><h1 class="banner-text"><span class="highlight-berf">BERF</span></a> Verification Tool</h1>
       
    
    </div>

    <div class="custom-shape-divider-top-1740728296">
    <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25" class="shape-fill"></path>
        <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5" class="shape-fill"></path>
        <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" class="shape-fill"></path>
    </svg>
</div>

    <!-- Search form -->
     <div class="aa">
    <form class="search" method="POST">
        <input type="text" name="search" placeholder="Enter DepEd Email Address or Research Title" value="<?php echo $search; ?>">
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
    <a href="login"><button class="login-button">Login</button></a>
    </div>


    <?php
$search = "";

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    // Sanitize input to prevent SQL injection
    $search = mysqli_real_escape_string($conn, $_POST['search']); 

    // Check if the search term contains only "the", "a", or "an"
    if (in_array(strtolower(trim($search)), [
        'the', 'a', 'an', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 
        '!', '"', '#', '$', '%', '&', "'", '(', ')', '*', '+', ',', '-', '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~'
    ])) {
        // Trigger JavaScript alert if the search term is only "the", "a", or "an"
        echo ' 
    <div class="alert-box" id="alertBox">
        <span class="alert-message"><i class="fa-solid fa-circle-info"></i> Please be specific in your search. Try searching for something more meaningful.</span>
    </div>

';
    } else {
        // If the search term is empty, don't execute the SQL query or display the table
        if (!empty($search)) {
            // First SQL query to fetch research titles and their corresponding dates based on the search keyword
            $sql = "SELECT Berf_Cycle, Research_Title, Division, Date_Completed FROM berf_masterlist 
        WHERE Research_Title LIKE ? AND (Status = 'Completed' OR Status = 'Completed and Archived')";

            // Prepare the statement
if ($stmt = $conn->prepare($sql)) {
    // Bind the search term with wildcards for LIKE
    $searchTerm = '%' . $search . '%';
    $stmt->bind_param('s', $searchTerm); // 's' stands for string, adjust if your search term is another type

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Display results
    if ($result->num_rows > 0) {
        echo "<div class='table-container'>
                <table>
                <thead>
                    <tr>
                        <th>Berf Cycle</th>
                        <th>Research Title</th>
                        <th>Division</th>
                        <th>Date Completed</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>";

        $counter = 0;
        // Output each row of results
        while ($row = $result->fetch_assoc()) {
            // Alternate the background color using the counter
            $rowClass = ($counter % 2 == 0) ? 'even' : 'odd'; // Even or odd class
            echo "<tr class='$rowClass'>
                    <td>" . htmlspecialchars($row['Berf_Cycle'] ?? '') . "</td>
                    <td>" . htmlspecialchars($row['Research_Title'] ?? '') . "</td>
                    <td>" . htmlspecialchars($row['Division'] ?? '') . "</td>
                    <td>" . htmlspecialchars($row['Date_Completed'] ?? '') . "</td>
                    <td><a href='result?research_title=" . urlencode($row['Research_Title'] ?? '') . "'>
                            <button><i class='fa-solid fa-location-arrow'></i></button>
                        </a>
                    </td>
                  </tr>";
            $counter++;
        }
        echo "</tbody></table>
        </div>";
        } else {
            echo '';
        }

    // Close the statement
    $stmt->close();
} else {
    echo "Error in preparing the SQL query.";
}


            // Now, execute a second SQL query to fetch details based on the search term
if (!empty($search)) {
    // Check if the search is a valid email
    if (filter_var($search, FILTER_VALIDATE_EMAIL)) {
        // Exclude emails that start with a single character (like a@gmail.com, b@gmail.com, etc.)
        if (!preg_match('/^[a-zA-Z]{1}@/', $search)) {
            // Check if the email is specifically from @deped.edu.ph, @gmail.com, or @yahoo.com
            if (preg_match('/@deped\.edu\.ph$|@gmail\.com$|@yahoo\.com$/', $search)) {
                // Email is valid, not a single character, and matches the expected domain
                $sqlDetails = "SELECT Berf_Cycle, Division, Research_Title, Author_1, Author_2, Author_3, Date_Completed, Abstract, Status 
               FROM berf_masterlist 
               WHERE CONCAT(Email_1, Email_2, Email_3) LIKE ? 
               AND (Status = 'Completed' OR Status = 'Completed and Archived')";
            }

                        // Prepare the statement
                        if ($stmt = $conn->prepare($sqlDetails)) {
                            // Bind the search term with wildcards for LIKE
                            $searchTerm = '%' . $search . '%';
                            $stmt->bind_param('s', $searchTerm);

                            // Execute the statement
                            $stmt->execute();

                            // Get the result
                            $resultDetails = $stmt->get_result();

                            // Display results
                            if ($resultDetails && $resultDetails->num_rows > 0) {
                                // Output data for each row
                                while ($row = $resultDetails->fetch_assoc()) {
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
            <div class='ans'>" . htmlspecialchars($row['Berf_Cycle']) . "</div>
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

                                }
                            } else {
                                echo "";
                            }

                            // Close the statement
                            $stmt->close();
                        } else {
                            echo "Error in preparing the SQL query.";
                        }
                    } else {
                        // If the email is not from @deped.edu.ph
                        echo ' 
    <div class="alert-box" id="alertBox">
        <span class="alert-message"><i class="fa-solid fa-circle-info"></i> Please check your email address.</span>
    </div>

';
                    }
                } 
            }
        }
    }
}
?>  



<!-- Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2><i class="fa-solid fa-circle-info"></i><span class="highlight-berf"> BERF</span></a> Verification Tool</h2>
        <div class="updates">
        <h5>Whats New in v1.3?</h5>
        <p>☑ Viewer Account is added to view all the encoded research in the system.</p>
        <p>☑ Responsiveness of the website.</p>
        <p>☑ Fixed Bugs.</p>
        <p>☑ Back-End Improvements</p>
        <br>

        <h5>BERF Verification Tool v1.2</h5>
        <p>☑ 2018 BERF Cycle is encoded in the database.</p>
        <p>☑ Fixed Bugs.</p>
        <p>☑ Back-End Improvements.</p>
        <br>

        <h5>BERF Verification Tool v1.1</h5>
        <p>☑ 2017 BERF Cycle is encoded in the database.</p>
        <p>☑ Clickable "BERF" word to refresh the page.</p>
        <p>☑ Back-End Improvements.</p>
        <br>
        </div>
       
    </div>
</div>

    <footer class="footer">
    <div class="footer-container">
        <div class="footer-column">
            <img src="imgs/404_nobg.png" alt="">
            <br>
            <a href="https://hassancreates.website/" class="name" target="_blank">Hassan Alshino</a>
            <p class="title">Developer</p>
        </div>
        <div class="footer-column">
            <a href="https://www.jaijavier.com/" class="name" target="_blank">Jai Javier</a>
            <p class="title">System Creator</p>
        </div>
        
        
    </div>
    <hr>
    <p class="aa " >&copy; 2025 BERF Verification Tool. All Rights Reserved.</p>
    <div class="open-modal" onclick="openModal()"><p class="aa " >BERF Verification Tool v1.3</p></div>

    
</footer>
    
    <script src="scripts.js"></script>
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
