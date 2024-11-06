<?php
// Start session to store the token and rate limit data
session_start();

// Include configuration file
include 'inc/config.php';

// API credentials for CJ Dropshipping
$email = 'phemcodejay@gmail.com';
$password = '42667d2d1d1a4dd7bb1f563b8eb7fc8c'; // Replace with your actual password

// Define rate limit constants
define('RATE_LIMIT_INTERVAL', 300); // 300 seconds = 5 minutes

// PDO connection settings
$dsn = 'mysql:host=localhost;dbname=your_database_name'; // Update with your database details
$username = 'your_db_username';
$password = 'your_db_password';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Create PDO instance
try {
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

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

// Function to handle rate limiting
function checkRateLimit() {
    // Check if the last request time is set
    if (isset($_SESSION['last_request_time'])) {
        $timeSinceLastRequest = time() - $_SESSION['last_request_time'];

        if ($timeSinceLastRequest < RATE_LIMIT_INTERVAL) {
            // Wait for the remaining time to pass
            $waitTime = RATE_LIMIT_INTERVAL - $timeSinceLastRequest;
            echo "Rate limit exceeded. Waiting for {$waitTime} seconds before making the next request...\n";
            sleep($waitTime); // Sleep for the remaining time
        }
    }

    // Update the last request time
    $_SESSION['last_request_time'] = time();
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
    $_SESSION['eyJhbGciOiJIUzI1NiJ9.eyJqdGkiOiIxOTczMSIsInR5cGUiOiJBQ0NFU1NfVE9LRU4iLCJzdWIiOi
            JicUxvYnFRMGxtTm55UXB4UFdMWnlpa0hoOEFVU3ErcFZGdXpKdDZlTGRiMkJBRFVOOUtXTVVPR3ViejA2TnBUU2FnajAyMnR3WEZZczBYW
            WdMRnExWUxycHlGVmxqdzl4SXBZTi9zcXRKa05aUHNrODF4TVlaRm9LTG9GblF5WEVEQjhFM1RSM3RhVndDeFZmbjJSb3UraTVIUGNOUzMz
            KzBBWUhqNGtqRS8wdTRnSXBvZmZ0VGhmOHY1bGVJQUYxdFhPSDFUT1dWTDJiazd3MnFMbkZ3WURNWXFzRm9sWElnMEdtQm1CbUNGNVQzcXV
            2MWxCbkIwTVFnWjl5SDVSTFhrWFd0MWFXNzVxa3hSdXBaaXNnbndWY2tpYWpzRnZOVUV2TFNZQzYxUT0ifQ.GZLU2i3jDKp5HXEdGvaif3K
            9p8sZnVRCEbJH0TpIVjk'] = $accessToken;
    $_SESSION['eyJhbGciOiJIUzI1NiJ9.ey
            JqdGkiOiIxOTczMSIsInR5cGUiOiJSRUZSRVNIX1RPS0VOIiwic3ViIjoiYnFMb2JxUTBsbU5ueVFweFBXTFp5aWtIaDhBVVNxK3BWRnV6S
            nQ2ZUxkYjJCQURVTjlLV01VT0d1YnowNk5wVGhkekxRVWtyUHpvTFFlRmZUUmdmTmhkL1V1cjBMWjVzcnJ0OTdYMnljRTBOWlBzazgxeE1Z
            WkZvS0xvRm5ReVhFREI4RTNUUjN0YVZ3Q3hWZm4yUm91K2k1SFBjTlMzMyswQVlIajRrakUvMHU0Z0lwb2ZmdFRoZjh2NWxlSUFGMXRYT0g
            xVE9XVkwyYms3dzJxTG5Gd1lETVlxc0ZvbFhJZzBHbUJtQm1DRjVUM3F1djFsQm5CME1RZ1o5eUg1UkxYa1hXdDFhVzc1cWt4UnVwWmlzZ2
            53VmNraWFqc0Z2TlVFdkxTWUM2MVE9In0.KD-PGrORs_7cFyaLQmOrHCArm2MyBXPaknMTsmVEAp0'] = $refreshToken;
} else {
    // Handle error
    echo "Error retrieving access token: " . json_encode($response) . "\n";
}

// Function to call the CJ API with rate limit check
function callApi($url, $data = [], $accessToken = '', $method = 'POST') {
    // Check and handle rate limit before making the API call
    checkRateLimit();

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

// Example function to fetch product list
function getProductList($accessToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/product/list';
    return callApi($url, [], $accessToken, 'POST');
}

// Function to add a product to the database using PDO
function addProduct($name, $description, $price, $productUrl, $imageUrl, $conn) {
    // Prepare the SQL query with placeholders to avoid SQL injection
    $query = "INSERT INTO tbl_product (name, description, price, source_url, image_url, vendor_name, inventory_sync) 
              VALUES (:name, :description, :price, :product_url, :image_url, 'CJ Dropshipping', 1)";
    
    $stmt = $conn->prepare($query);
    
    // Bind the parameters to prevent SQL injection
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':product_url', $productUrl);
    $stmt->bindParam(':image_url', $imageUrl);
    
    return $stmt->execute();
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
        $errorMessage = "Error adding product to database.";
    }
}

// Retrieve product list to display
$accessToken = $_SESSION['access_token'];
$productList = getProductList($accessToken);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>View Products</h1>
    </div>
    <div class="content-header-right">
        <a href="product-add.php" class="btn btn-primary btn-sm">Add Product</a>
    </div>
</section>
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
