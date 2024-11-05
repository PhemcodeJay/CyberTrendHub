<?php
<?php

// Function to get product details by product ID
function getProductDetail($productId, $accessToken) {
    $url = "https://developers.cjdropshipping.com/api2.0/v1/product/query?pid=$productId"; // Updated URL

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "CJ-Access-Token: $accessToken", // Updated header
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $result = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }

    curl_close($ch);

    return json_decode($result, true);
}

// Function to get the list of products
function getProductList($accessToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/product/list'; // Updated URL

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "CJ-Access-Token: $accessToken", // Updated header
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $result = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }

    curl_close($ch);

    return json_decode($result, true);
}

// Function to get product categories
function getProductCategories($accessToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/product/getCategory'; // Updated URL

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "CJ-Access-Token: $accessToken", // Updated header
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $result = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }

    curl_close($ch);

    return json_decode($result, true);
}

// Example usage
$accessToken = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'; // Your access token

// Get product detail
$productDetail = getProductDetail('000B9312-456A-4D31-94BD-B083E2A198E8', $accessToken);
if (isset($productDetail['name'], $productDetail['description'], $productDetail['price'])) {
    echo "Product Name: {$productDetail['name']}, Description: {$productDetail['description']}, Price: {$productDetail['price']}\n";
} else {
    // Handle error
    echo "Error retrieving product details: " . json_encode($productDetail) . "\n";
}

// Get product list
$products = getProductList($accessToken);
if (isset($products['data'])) {
    foreach ($products['data'] as $product) {
        echo "Product ID: {$product['id']}, Name: {$product['name']}, Price: {$product['price']}\n";
    }
} else {
    // Handle error
    echo "Error retrieving product list: " . json_encode($products) . "\n";
}

// Get product categories
$categories = getProductCategories($accessToken);
if (isset($categories['data'])) {
    foreach ($categories['data'] as $category) {
        echo "Category ID: {$category['id']}, Name: {$category['name']}\n";
    }
} else {
    // Handle error
    echo "Error retrieving product categories: " . json_encode($categories) . "\n";
}
?>
