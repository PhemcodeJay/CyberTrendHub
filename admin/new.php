<?php
// Include configuration and helper functions
include 'inc/config.php';

// Initialize messages
$errorMessage = "";
$successMessage = "";

// Get or refresh the access token function
function getAccessToken($email, $password) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/getAccessToken';
    $data = json_encode(['email' => $email, 'password' => $password]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

// Call API with a token function
function callApi($url, $data, $accessToken) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json",
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $productSource = $_POST['product_source']; // 'cj' or 'aliexpress'
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $productUrl = $_POST['product_url'];
    $imageUrl = $_POST['image_url'];

    // Use credentials from a secure place
    $email = 'phemcodejay@gmail.com'; // CJ email
    $password = '42667d2d1d1a4dd7bb1f563b8eb7fc8c'; // CJ password

    // Get access token
    $tokenData = getAccessToken($email, $password);
    $accessToken = $tokenData['access_token'] ?? null;

    if (!$accessToken) {
        $errorMessage = "Error retrieving access token: " . json_encode($tokenData);
    } else {
        // Set API endpoint and data based on product source
        if ($productSource === 'cj') {
            $url = "https://developers.cjdropshipping.com/api2.0/v1/product/add";
            $data = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'url' => $productUrl,
                'image_url' => $imageUrl,
            ];
        } else {
            $url = "https://api.aliexpress.com/product/add";
            $data = [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'url' => $productUrl,
                'image_url' => $imageUrl,
            ];
        }

        // Add product via API
        $response = callApi($url, $data, $accessToken);

        // Process the response
        if (isset($response['success']) && $response['success']) {
            $successMessage = "Product added successfully!";
        } else {
            $errorMessage = "Error adding product: " . ($response['message'] ?? 'Unknown error');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Dropshipping Product</title>
    <style>
        /* Basic styling */
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f9f9f9; }
        form { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); max-width: 400px; margin: auto; }
        input, textarea, select { width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 5px; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .message { margin: 10px 0; padding: 10px; color: #d9534f; }
        .success { color: #5cb85c; }
    </style>
</head>
<body>
    <h1>Add Dropshipping Product</h1>

    <?php if ($errorMessage): ?>
        <div class="message"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <label for="product_source">Product Source:</label>
        <select name="product_source" required>
            <option value="cj">CJ Dropshipping</option>
            <option value="aliexpress">AliExpress</option>
        </select>

        <label for="name">Product Name:</label>
        <input type="text" name="name" required>

        <label for="description">Description:</label>
        <textarea name="description" required></textarea>

        <label for="price">Price:</label>
        <input type="number" name="price" step="0.01" required>

        <label for="product_url">Product URL:</label>
        <input type="text" name="product_url" required>

        <label for="image_url">Image URL:</label>
        <input type="text" name="image_url" required>

        <button type="submit">Add Product</button>
    </form>
</body>
</html>
