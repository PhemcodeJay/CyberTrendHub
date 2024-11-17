<?php
// Start session to store token and rate limit data
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration file
include 'admin/inc/config.php';

// Define constants
define('RATE_LIMIT_INTERVAL', 300); // 5 minutes

// Function to fetch or refresh access token
function getAccessToken() {
    if (isset($_SESSION['accessToken'], $_SESSION['token_expiry']) && $_SESSION['token_expiry'] > time()) {
        return ['data' => ['accessToken' => $_SESSION['accessToken']]];
    }

    $url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/getAccessToken';
    $data = json_encode(['email' => 'phemcodejay@gmail.com', 'password' => '42667d2d1d1a4dd7bb1f563b8eb7fc8c']);
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($result, true);

    if (isset($response['data']['accessToken'])) {
        $_SESSION['accessToken'] = $response['data']['accessToken'];
        $_SESSION['token_expiry'] = time() + 300; // Valid for 5 minutes
    }

    return $response;
}

// Function to make API requests
function callApi($url, $accessToken) {
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
if (!isset($accessTokenResponse['data']['accessToken'])) {
    die("Error fetching access token: " . json_encode($accessTokenResponse));
}

$accessToken = $accessTokenResponse['data']['accessToken'];

// Fetch categories and products
$categories = callApi('https://developers.cjdropshipping.com/api2.0/v1/product/getCategory', $accessToken)['data'] ?? [];
$productList = callApi('https://developers.cjdropshipping.com/api2.0/v1/product/list', $accessToken)['data'] ?? [];

// Database connection
try {
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Function to add product to the database
function addProduct($productData, $conn) {
    $query = "INSERT INTO tbl_product 
                (p_name, p_old_price, p_current_price, p_qty, p_featured_photo, 
                 p_description, p_short_description, p_feature, p_condition, 
                 p_return_policy, p_total_view, p_is_featured, p_is_active, 
                 ecat_id, cj_product_id, source_url, vendor_name, inventory_sync) 
              VALUES 
                (:p_name, :p_old_price, :p_current_price, :p_qty, :p_featured_photo, 
                 :p_description, :p_short_description, :p_feature, :p_condition, 
                 :p_return_policy, 0, 0, 1, :ecat_id, :cj_product_id, :source_url, 
                 :vendor_name, :inventory_sync)";
    $stmt = $conn->prepare($query);

    foreach ($productData as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }

    return $stmt->execute();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productData = [
        'p_name' => $_POST['p_name'] ?? '',
        'p_old_price' => $_POST['p_old_price'] ?? 0.00,
        'p_current_price' => $_POST['p_current_price'] ?? 0.00,
        'p_qty' => $_POST['p_qty'] ?? 0,
        'p_featured_photo' => $_POST['p_featured_photo'] ?? '',
        'p_description' => $_POST['p_description'] ?? '',
        'p_short_description' => $_POST['p_short_description'] ?? '',
        'p_feature' => $_POST['p_feature'] ?? '',
        'p_condition' => $_POST['p_condition'] ?? '',
        'p_return_policy' => $_POST['p_return_policy'] ?? '',
        'ecat_id' => $_POST['ecat_id'] ?? 1, // Default to category 1
        'cj_product_id' => $_POST['cj_product_id'] ?? '',
        'source_url' => $_POST['source_url'] ?? '',
        'vendor_name' => $_POST['vendor_name'] ?? '',
        'inventory_sync' => $_POST['inventory_sync'] ?? 1,
    ];

    if (addProduct($productData, $conn)) {
        echo "Product '{$productData['p_name']}' added successfully!";
    } else {
        echo "Failed to add the product to the database.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CJ Dropshipping Products</title>
    <style>
        /* Simplified styling */
        body {
            font-family: Arial, sans-serif;
            background: #f4f8fb;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: auto;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .product-item {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: calc(33.33% - 20px);
            text-align: center;
        }
        .product-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-info {
            padding: 15px;
        }
        .add-to-store-btn {
            background-color: #3498db;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>CJ Dropshipping Products</h1>
    <div class="product-list">
        <?php foreach ($productList as $product): ?>
            <div class="product-item">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Product Image">
                <div class="product-info">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p>Price: $<?= htmlspecialchars($product['price']) ?></p>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                        <input type="hidden" name="p_name" value="<?= htmlspecialchars($product['name']) ?>">
                        <input type="hidden" name="p_old_price" value="<?= htmlspecialchars($product['old_price']) ?>">
                        <input type="hidden" name="p_current_price" value="<?= htmlspecialchars($product['price']) ?>">
                        <input type="hidden" name="source_url" value="<?= htmlspecialchars($product['product_url'] ?? '') ?>">
                        <input type="hidden" name="p_featured_photo" value="<?= htmlspecialchars($product['image_url']) ?>">
                        <button type="submit" class="add-to-store-btn">Add to My Store</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
