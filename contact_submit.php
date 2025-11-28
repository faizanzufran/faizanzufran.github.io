<?php
// --------------------
// Database Configuration - !!! CHANGE THESE VALUES !!!
// --------------------
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "portfolio_db"; 

// --------------------
// Establish Database Connection
// --------------------

// Example for a changed port (e.g., to 3307)
// $conn = new mysqli($servername, $username, $password, $dbname, 3307);
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection and immediately redirect if failed
if ($conn->connect_error) {
    // We use urlencode() to safely pass the error message via the URL
    $error_msg = urlencode("Database Connection failed: " . $conn->connect_error);
    header("Location: index.html?status=error&msg={$error_msg}#contact");
    exit();
}

// --------------------
// Handle Form Submission (INSERT Data)
// --------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if all fields are set before accessing them
    if (!isset($_POST['Name'], $_POST['email'], $_POST['Message'])) {
        header("Location: index.html?status=error&msg=Missing form fields.#contact");
        exit();
    }
    
    // Collect and sanitize input data
    // mysqli_real_escape_string is used here, though prepare/bind_param is safer.
    $name = trim($_POST['Name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['Message']);

    // Server-side Validation
    if (empty($name) || empty($email) || empty($message)) {
        header("Location: index.html?status=error&msg=All fields are required.#contact");
        exit();
    }
    
    // Check for valid Gmail (as per your requirement)
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $email)) {
        header("Location: index.html?status=error&msg=Only Gmail addresses are allowed.#contact");
        exit();
    }

    // Use Prepared Statements for secure insertion
    $sql = "INSERT INTO messages (name, email, message) VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // 'sss' indicates all three parameters are strings
    $stmt->bind_param("sss", $name, $email, $message); 

    if ($stmt->execute()) {
        // Success: Redirect back to index.html with success status
        header("Location: index.html?status=success&msg=Message sent successfully! Thank you.#contact");
    } else {
        // Failure: Redirect back with error status
        $error_msg = urlencode("Database Error: " . $stmt->error);
        header("Location: index.html?status=error&msg={$error_msg}#contact");
    }
    
    $stmt->close();
}


// --------------------
// Example Data Retrieval (Associative Array)
// --------------------
// This function demonstrates how to retrieve data into an associative array, 
// which is useful for building a dashboard or admin view.

function getRecentMessages($conn) {
    $recentMessages = [];
    $sql = "SELECT name, email, message, submission_date FROM messages ORDER BY submission_date DESC LIMIT 10";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Store the row as an associative array element
            $recentMessages[] = [
                'sender_name' => htmlspecialchars($row['name']),
                'sender_email' => htmlspecialchars($row['email']),
                'preview' => htmlspecialchars(substr($row['message'], 0, 50)) . '...',
                'time' => $row['submission_date']
            ];
        }
    }
    return $recentMessages;
}

// Example usage (if you created a separate admin/dashboard.php file):
// $dashboardData = getRecentMessages($conn);
// print_r($dashboardData); 


// Close connection if it was opened successfully
if ($conn) {
    $conn->close();
}

// If the script is accessed directly without a POST request, redirect
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.html");
    exit();
}
?>