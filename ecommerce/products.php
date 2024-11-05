<?php
function getProductList($accessToken) {
    $url = 'https://api.cjdropshipping.com/v1/products'; // The actual URL

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
$products = getProductList($accessToken);

if (isset($products['data'])) {
    foreach ($products['data'] as $product) {
        echo "Product ID: {$product['id']}, Name: {$product['name']}, Price: {$product['price']}\n";
    }
} else {
    // Handle error
    echo "Error retrieving product list: " . json_encode($products);
}
