<?php
// Start session to manage access tokens and other session data
session_start();

// Include database configuration and helper files
include 'inc/config.php';

// Establish PDO connection to your MySQL database
$dsn = 'mysql:host=db5016602507.hosting-data.io;dbname=dbs13461311';
$username = 'dbu3393405';
$password = 'Kokochulo1.';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $conn = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Example product data from CJ Dropshipping or another API source
$productData = [
    'p_name' => 'Sample Product Name',
    'p_old_price' => 29.99,
    'p_current_price' => 19.99,
    'p_qty' => 100,
    'p_featured_photo' => 'sample-photo.jpg',
    'p_description' => 'This is a sample product description.',
    'p_short_description' => 'Short description of the sample product.',
    'p_feature' => 'Sample features of the product.',
    'p_condition' => 'New',
    'p_return_policy' => '30-day return policy.',
    'ecat_id' => 1, // Category ID; replace with an actual category ID from your database
    'aliexpress_product_id' => '1234567890',
    'cj_product_id' => 'CJ12345',
    'source_url' => 'https://example.com/product-url',
    'vendor_name' => 'CJ Dropshipping',
    'inventory_sync' => 1
];

// Function to add a product to the database
function addProduct($productData, $conn) {
    $query = "INSERT INTO tbl_product 
                (p_name, p_old_price, p_current_price, p_qty, p_featured_photo, 
                p_description, p_short_description, p_feature, p_condition, 
                p_return_policy, p_total_view, p_is_featured, p_is_active, 
                ecat_id, aliexpress_product_id, cj_product_id, source_url, 
                vendor_name, inventory_sync) 
              VALUES 
                (:p_name, :p_old_price, :p_current_price, :p_qty, :p_featured_photo, 
                :p_description, :p_short_description, :p_feature, :p_condition, 
                :p_return_policy, 0, 0, 1, 
                :ecat_id, :aliexpress_product_id, :cj_product_id, :source_url, 
                :vendor_name, :inventory_sync)";
                
    $stmt = $conn->prepare($query);

    // Bind parameters to prevent SQL injection
    $stmt->bindParam(':p_name', $productData['p_name']);
    $stmt->bindParam(':p_old_price', $productData['p_old_price']);
    $stmt->bindParam(':p_current_price', $productData['p_current_price']);
    $stmt->bindParam(':p_qty', $productData['p_qty']);
    $stmt->bindParam(':p_featured_photo', $productData['p_featured_photo']);
    $stmt->bindParam(':p_description', $productData['p_description']);
    $stmt->bindParam(':p_short_description', $productData['p_short_description']);
    $stmt->bindParam(':p_feature', $productData['p_feature']);
    $stmt->bindParam(':p_condition', $productData['p_condition']);
    $stmt->bindParam(':p_return_policy', $productData['p_return_policy']);
    $stmt->bindParam(':ecat_id', $productData['ecat_id']);
    $stmt->bindParam(':aliexpress_product_id', $productData['aliexpress_product_id']);
    $stmt->bindParam(':cj_product_id', $productData['cj_product_id']);
    $stmt->bindParam(':source_url', $productData['source_url']);
    $stmt->bindParam(':vendor_name', $productData['vendor_name']);
    $stmt->bindParam(':inventory_sync', $productData['inventory_sync']);

    return $stmt->execute();
}

// Call the function to add the product and handle the response
if (addProduct($productData, $conn)) {
    echo "Product '{$productData['p_name']}' added successfully!";
} else {
    echo "Failed to add the product to the database.";
}
?>
