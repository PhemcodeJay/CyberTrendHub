<?php
function getProductDetail($productId, $accessToken) {
    $url = "https://api.cjdropshipping.com/v1/products/$productId"; // The actual URL

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
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

// Usage
$productDetail = getProductDetail('product_id_here', $accessToken);

if (isset($productDetail['name'], $productDetail['description'], $productDetail['price'])) {
    echo "Product Name: {$productDetail['name']}, Description: {$productDetail['description']}, Price: {$productDetail['price']}\n";
} else {
    // Handle error
    echo "Error retrieving product details: " . json_encode($productDetail);
}
