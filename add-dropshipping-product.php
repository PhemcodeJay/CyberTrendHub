<?php
// Start session to store token and rate limit data
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Include configuration file
include 'admin/inc/config.php';

// Define rate limit constants
define('RATE_LIMIT_INTERVAL', 300); // 5 minutes



function getAccessToken() {
    // Check if token exists in session and is still valid
    if (isset($_SESSION['accessToken'], $_SESSION['token_expiry']) && $_SESSION['token_expiry'] > time()) {
        return [
            'data' => ['accessToken' => $_SESSION['accessToken']],
        ];
    }

    // If no valid token, fetch a new one
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/getAccessToken';
    $data = json_encode([
        'email' => 'phemcodejay@gmail.com',
        'password' => '42667d2d1d1a4dd7bb1f563b8eb7fc8c',
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

    $response = json_decode($result, true);

    // Save token in session if successful
    if (isset($response['data']['accessToken'])) {
        $_SESSION['accessToken'] = $response['data']['accessToken'];
        $_SESSION['token_expiry'] = time() + 300; // Set expiry to 5 minutes
    }

    return $response;
}

// Function to make API requests
function callApi($url, $accessToken = '') {
    if (isset($_SESSION['last_request_time'])) {
        $timeSinceLastRequest = time() - $_SESSION['last_request_time'];
        if ($timeSinceLastRequest < RATE_LIMIT_INTERVAL) {
            sleep(RATE_LIMIT_INTERVAL - $timeSinceLastRequest);
        }
    }
    $_SESSION['last_request_time'] = time();

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "CJ-Access-Token: $accessToken",
        "Content-Type: application/json",
    ]);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

// Fetch access token
$accessTokenResponse = getAccessToken();
if (isset($accessTokenResponse['data']['accessToken'])) {
    $accessToken = $accessTokenResponse['data']['accessToken'];
    $_SESSION['accessToken'] = $accessToken;

    // Fetch Product Categories
    $categoryResponse = callApi('https://developers.cjdropshipping.com/api2.0/v1/product/getCategory', $accessToken);
    $categories = $categoryResponse['data'] ?? [];

    // Fetch Product List
    $productListResponse = callApi('https://developers.cjdropshipping.com/api2.0/v1/product/list', $accessToken);
    $productList = $productListResponse['data'] ?? [];
} else {
    echo "Error fetching access token: " . json_encode($accessTokenResponse);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CJ Dropshipping Products</title>
    <style>
        /* Reset some default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Container styling */
        body {
            font-family: Arial, sans-serif;
            background: #f4f8fb;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        /* Product list styling */
        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .product-item {
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            max-width: 300px;
            width: 100%;
            text-align: center;
        }

        .product-item:hover {
            transform: translateY(-5px);
        }

        .product-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }

        /* Product info styling */
        .product-info {
            padding: 15px;
        }

        .product-info h3 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .price {
            font-size: 16px;
            color: #27ae60;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        /* Button styling */
        .add-to-store-btn {
            display: inline-block;
            padding: 10px 15px;
            font-size: 14px;
            color: #fff;
            background-color: #3498db;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .add-to-store-btn:hover {
            background-color: #2980b9;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .product-list {
                gap: 15px;
            }
            .product-item {
                max-width: 100%;
            }
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            font-size: 16px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>CJ Dropshipping Products</h1>

        <h2>Product Categories</h2>
        <div class="category-list">
            <?php foreach ($categories as $category): ?>
                <p><?= htmlspecialchars($category['name']) ?></p>
            <?php endforeach; ?>
        </div>

        <h2>Product List</h2>
        <div class="product-list">
            <?php if (!empty($productList)): ?>
                <?php foreach ($productList as $product): ?>
                    <div class="product-item">
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Product Image">
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="price">Price: $<?= htmlspecialchars($product['price']) ?></p>
                            <p class="description"><?= htmlspecialchars($product['description']) ?></p>
                            <form action="add-dropshipp.php" method="post">
                                <input type="hidden" name="name" value="<?= htmlspecialchars($product['name']) ?>">
                                <input type="hidden" name="description" value="<?= htmlspecialchars($product['description']) ?>">
                                <input type="hidden" name="price" value="<?= htmlspecialchars($product['price']) ?>">
                                <input type="hidden" name="product_url" value="<?= htmlspecialchars($product['product_url'] ?? '') ?>">
                                <input type="hidden" name="image_url" value="<?= htmlspecialchars($product['image_url']) ?>">
                                <button type="submit" class="add-to-store-btn">Add to My Store</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="error-message">No products available or an error occurred while fetching products.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
