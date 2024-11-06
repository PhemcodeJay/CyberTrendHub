<?php
// Start session to store the token
session_start();

// Include configuration file
include 'inc/config.php';

// API credentials for CJ Dropshipping
$email = 'phemcodejay@gmail.com';
$password = '42667d2d1d1a4dd7bb1f563b8eb7fc8c'; // Replace with your actual password

// Function to get the access token using email and password
function getAccessToken($email, $password) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/getAccessToken';

    $data = json_encode([
        'email' => $email,
        'password' => $password,
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data),
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

// Example usage: replace these with your credentials
$email = 'phemcodejay@gmail.com';
$password = '42667d2d1d1a4dd7bb1f563b8eb7fc8c';
$response = getAccessToken($email, $password);

if (isset($response['data'])) {
    $accessToken = $response['data']['accessToken'];
    $refreshToken = $response['data']['refreshToken'];

    echo "Access Token: " . $accessToken . "\n";
    echo "Refresh Token: " . $refreshToken . "\n";

    // You may want to store the access token and refresh token for future use
    $_SESSION['accessToken'] = $accessToken;
    $_SESSION['refreshToken'] = $refreshToken;
} else {
    // Handle error
    echo "Error retrieving access token: " . json_encode($response) . "\n";
}

// Function to refresh the access token using the refresh token
function refreshAccessToken($refreshToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/refreshAccessToken';
    $data = json_encode(['refreshToken' => $refreshToken]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

// Retrieve and store tokens in session
$token = getAccessToken($email, $password);

if (isset($token['access_token'])) {
    $_SESSION['access_token'] = $token['access_token'];
    $_SESSION['refresh_token'] = $token['refresh_token'] ?? null;
} else {
    die("Error retrieving access token: " . json_encode($token));
}

// Function to add a product to the database
function addProduct($name, $description, $price, $productUrl, $imageUrl, $conn) {
    $query = "INSERT INTO tbl_product (name, description, price, source_url, image_url, vendor_name, inventory_sync) 
              VALUES ('$name', '$description', '$price', '$productUrl', '$imageUrl', 'CJ Dropshipping', 1)";
    return mysqli_query($conn, $query);
}

// Function to call the CJ API
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

// Function to fetch product list from CJ Dropshipping
function getProductList($accessToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/product/list';
    return callApi($url, [], $accessToken, 'POST');
}

// Handle product addition form submission
$errorMessage = $successMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $productUrl = $_POST['product_url'];
    $imageUrl = $_POST['image_url'];

    if (addProduct($name, $description, $price, $productUrl, $imageUrl, $conn)) {
        $successMessage = "Product '$name' added successfully!";
    } else {
        $errorMessage = "Error adding product to database: " . mysqli_error($conn);
    }
}

// Retrieve product list to display
$accessToken = $_SESSION['access_token'];
$productList = getProductList($accessToken);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage CJ Dropshipping Products</title>
    <style>
        /* Your CSS styles */
    </style>
</head>
<body>
<div class="container">
    <h1>Manage CJ Dropshipping Products</h1>

    <!-- Display messages -->
    <div class="message-container">
        <?php if ($errorMessage): ?>
            <div class="message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <?php if ($successMessage): ?>
            <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
    </div>

    <!-- Product list display -->
    <div class="product-list">
        <h2>Available Products from CJ Dropshipping</h2>
        <?php if (isset($productList['data']) && !empty($productList['data'])): ?>
            <?php foreach ($productList['data'] as $product): ?>
                <div class="product-item">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Product Image">
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p>Price: $<?= htmlspecialchars($product['price']) ?></p>
                        <p><?= htmlspecialchars($product['description']) ?></p>
                    </div>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="add_product">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']) ?>">
                        <input type="hidden" name="description" value="<?= htmlspecialchars($product['description']) ?>">
                        <input type="hidden" name="price" value="<?= htmlspecialchars($product['price']) ?>">
                        <input type="hidden" name="product_url" value="<?= htmlspecialchars($product['product_url'] ?? '') ?>">
                        <input type="hidden" name="image_url" value="<?= htmlspecialchars($product['image_url']) ?>">
                        <button type="submit">Add Product</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products found or an error occurred while fetching products.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
