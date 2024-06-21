<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Processed Image Data</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <?php
    // Function to generate a unique job ID
    function generateJobID() {
        return uniqid('JOB_', true); // Example: JOB_5f76c5a3f3246
    }

    // Check if the form was submitted using POST method
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Check if the file is uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = $_FILES['image'];

            // Save the uploaded image to a temporary file
            $tmpFilePath = $image['tmp_name'];
            $tmpFileName = basename($image['name']);

            // Send the image to the Python script for processing
            $url = 'http://localhost:5000/process_image'; // Replace with your actual processing URL
            $cfile = new CURLFile($tmpFilePath, $image['type'], $tmpFileName);
            $data = array('file' => $cfile);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);

            // Decode the JSON response from the Python script
            $responseData = json_decode($response, true);

            // Check if processing was successful
            if ($responseData && $responseData['status'] == 'success') {
                // Generate unique job ID
                $jobID = generateJobID();

                // Display the processed image data using Bootstrap components
                echo '<h3 class="mb-4">Processed Image Data</h3>';
                echo '<div class="card mb-3">';
                echo '<div class="card-body">';
                echo '<p><strong>Job ID:</strong> ' . htmlspecialchars($jobID) . '</p>';
                echo '<p><strong>PO Number:</strong> ' . htmlspecialchars($responseData['po_number']) . '</p>';
                echo '<p><strong>Challan Date:</strong> ' . htmlspecialchars($responseData['challan_date']) . '</p>';
                echo '<p><strong>Manufacturer Name:</strong> ' . htmlspecialchars($responseData['manufacturer_name']) . '</p>';
                if (!empty($responseData['gst_numbers'])) {
                    echo '<p><strong>GST Number (Manufacturer):</strong> ' . htmlspecialchars($responseData['gst_numbers'][0]) . '</p>';
                }
                if (isset($responseData['gst_numbers'][1])) {
                    echo '<p><strong>Process manufacturer:</strong> GEM Enterprises</p>';
                    echo '<p><strong>GST Number (Process Manufacturer):</strong> ' . htmlspecialchars($responseData['gst_numbers'][1]) . '</p>';
                }
                echo '<p><strong>Challan Numbers:</strong> ' . htmlspecialchars(implode(', ', $responseData['challan_numbers'])) . '</p>';
                echo '</div>';
                echo '</div>';

                // Initialize total quantity and total weight
                $totalQuantity = 0;
                $totalWeight = 0;

                // Display table for detailed data
                echo '<h4 class="mb-4">Data</h4>';
                echo '<div class="table-responsive">';
                echo '<table class="table table-bordered">';
                echo '<thead class="thead-light">';
                echo '<tr>';
                echo '<th>Sr No</th>';
                echo '<th>Material Code</th>';
                echo '<th>Quantity</th>';
                echo '<th>Unit/Wt</th>';
                echo '<th>Est Value</th>';
                echo '<th>HSN Code</th>';
                echo '<th>Scope</th>';
                echo '<th>Desc. of Goods</th>';
                echo '<th>Total Wt</th>';
                echo '<th>Due Date</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                foreach ($responseData['data'] as $item) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($item['Sr No']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['Material Code']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['Quantity']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['Unit/Wt']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['Est value']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['HSN Code']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['Scope']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['Desc. of Goods']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['Total Wt']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['Due date']) . '</td>';
                    echo '</tr>';

                    // Add quantity to total quantity
                    $totalQuantity += floatval($item['Quantity']);

                    // Extract numeric part of the total weight and add to total weight
                    if (preg_match('/(\d+(\.\d+)?)/', $item['Total Wt'], $matches)) {
                        $totalWeight += floatval($matches[0]);
                    }
                }
                echo '</tbody>';
                echo '</table>';
                echo '</div>';

                // Display total quantity and total weight
                echo '<div class="mt-4">';
                echo '<p><strong>Total Quantity:</strong> ' . htmlspecialchars($totalQuantity) . '</p>';
                echo '<p><strong>Total Weight:</strong> ' . htmlspecialchars($totalWeight) . '</p>';
                echo '</div>';
                echo '<pre>';
                echo json_encode($responseData, JSON_PRETTY_PRINT);
                echo '</pre>';

                // Create Job button with hidden form for inserting into database
                echo '
                    <form action="insert.php" method="POST">
                        <input type="hidden" name="job_id" value="' . htmlspecialchars($jobID) . '">
                        <input type="hidden" name="po_number" value="' . htmlspecialchars($responseData['po_number']) . '">
                        <input type="hidden" name="challan_date" value="' . htmlspecialchars($responseData['challan_date']) . '">
                        <input type="hidden" name="manufacturer_name" value="' . htmlspecialchars($responseData['manufacturer_name']) . '">
                        <input type="hidden" name="total_quantity" value="' . htmlspecialchars($totalQuantity) . '">
                        <input type="hidden" name="total_weight" value="' . htmlspecialchars($totalWeight) . '">';

                // Loop through the data array to create hidden inputs for each item
                foreach ($responseData['data'] as $index => $item) {
                    echo '
                        <input type="hidden" name="data[' . $index . '][Sr No]" value="' . htmlspecialchars($item['Sr No']) . '">
                        <input type="hidden" name="data[' . $index . '][Material Code]" value="' . htmlspecialchars($item['Material Code']) . '">
                        <input type="hidden" name="data[' . $index . '][Quantity]" value="' . htmlspecialchars($item['Quantity']) . '">
                        <input type="hidden" name="data[' . $index . '][Unit/Wt]" value="' . htmlspecialchars($item['Unit/Wt']) . '">
                        <input type="hidden" name="data[' . $index . '][Est value]" value="' . htmlspecialchars($item['Est value']) . '">
                        <input type="hidden" name="data[' . $index . '][HSN Code]" value="' . htmlspecialchars($item['HSN Code']) . '">
                        <input type="hidden" name="data[' . $index . '][Scope]" value="' . htmlspecialchars($item['Scope']) . '">
                        <input type="hidden" name="data[' . $index . '][Desc. of Goods]" value="' . htmlspecialchars($item['Desc. of Goods']) . '">
                        <input type="hidden" name="data[' . $index . '][Total Wt]" value="' . htmlspecialchars($item['Total Wt']) . '">
                        <input type="hidden" name="data[' . $index . '][Due date]" value="' . htmlspecialchars($item['Due date']) . '">
                    ';
                }

                echo '
                        <button type="submit" class="btn btn-primary">Create Job</button>
                    </form>
                ';

            } else {
                // Display error message if processing failed
                echo '<div class="alert alert-danger" role="alert">Error processing image</div>';
            }
        } else {
            // Display error message if no file uploaded or upload error
            echo '<div class="alert alert-danger" role="alert">No file uploaded or upload error</div>';
        }
    } else {
        // Display error message if invalid request method
        echo '<div class="alert alert-danger" role="alert">Invalid request method</div>';
    }
    ?>
</div>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
