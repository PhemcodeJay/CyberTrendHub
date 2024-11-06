<?php
// Include your configuration file for database and API credentials
include 'inc/config.php';

// Initialize messages
$errorMessage = "";
$successMessage = "";

// API credentials for CJ Dropshipping
$cjAccessToken = '42667d2d1d1a4dd7bb1f563b8eb7fc8c'; // Replace with your CJ access token

// Handle form submission for adding a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    // Retrieve form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $productUrl = $_POST['product_url'];
    $imageUrl = $_POST['image_url'];

    $url = "https://developers.cjdropshipping.com/api2.0/v1/product/add";

    $data = [
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'url' => $productUrl,
        'image_url' => $imageUrl,
    ];

    $response = callApi($url, $data, $cjAccessToken);

    if (isset($response['success']) && $response['success']) {
        $successMessage = "Product added successfully!";
    } else {
        $errorMessage = "Error adding product: " . ($response['message'] ?? 'Unknown error');
    }
}

// Fetch product list from CJ Dropshipping
function getProductList($accessToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/product/list';
    return callApi($url, [], $accessToken, 'GET');
}

// Function to call API
function callApi($url, $data = [], $accessToken = '', $method = 'POST') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json",
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

// Retrieve the product list to display
$productList = getProductList($cjAccessToken);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage CJ Dropshipping Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        form, .product-list {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
            margin-bottom: 20px;
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            color: #d9534f;
        }
        .success {
            color: #5cb85c;
        }
        .product-list h2 {
            font-size: 18px;
        }
        .product-item {
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }
        .product-item img {
            max-width: 100px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Manage CJ Dropshipping Products</h1>

    <?php if ($errorMessage): ?>
        <div class="message"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>
    <?php if ($successMessage): ?>
        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <input type="hidden" name="action" value="add_product">
        
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

    <div class="product-list">
        <h2>Product List from CJ Dropshipping</h2>
        <?php if (isset($productList['data']) && !empty($productList['data'])): ?>
            <?php foreach ($productList['data'] as $product): ?>
                <div class="product-item">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Product Image">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p>Price: $<?= htmlspecialchars($product['price']) ?></p>
                    <p><?= htmlspecialchars($product['description']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
