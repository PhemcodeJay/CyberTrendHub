<?php
// Include the database configuration file
include 'inc/config.php';

// Initialize variables for errors and success messages
$errorMessage = "";
$successMessage = "";

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $is_digital = 1; // Assuming all uploads are digital

    // Handle file upload
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = "Error uploading file. Error code: " . $file['error'];
        } else {
            // Define the target directory and file name
            $targetDir = "uploads/";
            $targetFile = $targetDir . basename($file['name']);

            // Move the uploaded file to the target directory
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                try {
                    // Prepare SQL statement to insert product data into the database
                    $stmt = $pdo->prepare("INSERT INTO tbl_digital_products (name, description, price, file_url, is_digital) VALUES (:name, :description, :price, :file_url, :is_digital)");
                    
                    // Bind parameters
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':price', $price);
                    $stmt->bindParam(':file_url', $targetFile);
                    $stmt->bindParam(':is_digital', $is_digital, PDO::PARAM_INT);
                    
                    // Execute the statement
                    if ($stmt->execute()) {
                        $successMessage = "Digital product uploaded successfully.";
                    } else {
                        $errorMessage = "Error inserting data into database.";
                    }
                } catch (PDOException $e) {
                    $errorMessage = "Database error: " . $e->getMessage();
                }
            } else {
                $errorMessage = "Error moving uploaded file.";
            }
        }
    } else {
        $errorMessage = "No file uploaded.";
    }
}

// No need to close the PDO connection, as it will be closed automatically when the script ends
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Digital Product</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
        }
        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        input[type="file"] {
            margin-bottom: 15px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<h1>Upload Digital Product</h1>

<?php if ($errorMessage): ?>
    <div class="message error"><?php echo $errorMessage; ?></div>
<?php endif; ?>

<?php if ($successMessage): ?>
    <div class="message success"><?php echo $successMessage; ?></div>
<?php endif; ?>

<form action="upload_digital_product.php" method="post" enctype="multipart/form-data">
    <label for="name">Product Name:</label>
    <input type="text" name="name" required>

    <label for="description">Description:</label>
    <textarea name="description" required></textarea>

    <label for="price">Price:</label>
    <input type="number" name="price" step="0.01" required>

    <label for="file">Upload File:</label>
    <input type="file" name="file" accept=".pdf,.zip,.jpg,.png" required>

    <button type="submit">Upload Product</button>
</form>

</body>
</html>
