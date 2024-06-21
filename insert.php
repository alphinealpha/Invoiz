<?php
$servername = "localhost";  // Replace with your database server name
$username = "root";  // Replace with your database username
$password = "";  // Replace with your database password
$dbname = "invoiz";  // Replace with your database name

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $jobID = $_POST['job_id'];
    $poNumber = $_POST['po_number'];
    $challanDate = $_POST['challan_date'];
    $manufacturerName = $_POST['manufacturer_name'];
    $totalQuantity = floatval($_POST['total_quantity']);
    $totalWeight = floatval($_POST['total_weight']);

    // Prepare the SQL statement to insert the job data into the database
    $sql = "INSERT INTO new_job (job_id, po_number, challan_date, manufacturer_name, total_quantity, total_weight)
            VALUES (?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error preparing the statement: " . $conn->error);
    }

    // Bind the parameters to the SQL statement
    $stmt->bind_param("ssssdd", $jobID, $poNumber, $challanDate, $manufacturerName, $totalQuantity, $totalWeight);

    // Execute the statement
    if ($stmt->execute()) {
        echo '<div class="alert alert-success" role="alert">Job created successfully!</div>';
        
        // Now insert the detailed data into job_items table
        foreach ($_POST['data'] as $item) {
            $sqlItem = "INSERT INTO job_items (job_id, sr_no, material_code, quantity, unit_wt, est_value, hsn_code, scope, desc_of_goods, total_wt, due_date)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            // Prepare the statement
            $stmtItem = $conn->prepare($sqlItem);

            if ($stmtItem === false) {
                die("Error preparing the item statement: " . $conn->error);
            }

            // Bind the parameters to the SQL statement
            $stmtItem->bind_param(
                "sssssssssss",
                $jobID,
                $item['Sr No'],
                $item['Material Code'],
                $item['Quantity'],
                $item['Unit/Wt'],
                $item['Est value'],
                $item['HSN Code'],
                $item['Scope'],
                $item['Desc. of Goods'],
                $item['Total Wt'],
                $item['Due date']
            );

            // Execute the statement
            if (!$stmtItem->execute()) {
                echo '<div class="alert alert-danger" role="alert">Error creating job item: ' . htmlspecialchars($stmtItem->error) . '</div>';
            }

            // Close the item statement
            $stmtItem->close();
        }

    } else {
        echo '<div class="alert alert-danger" role="alert">Error creating job: ' . htmlspecialchars($stmt->error) . '</div>';
    }

    // Close the main statement
    $stmt->close();
} else {
    // Display error message if invalid request method
    echo '<div class="alert alert-danger" role="alert">Invalid request method</div>';
}

// Close the database connection
$conn->close();
?>
