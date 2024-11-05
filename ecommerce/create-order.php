<?php
function createOrder($orderData, $accessToken) {
    $url = 'https://api.cjdropshipping.com/v1/orders'; // The actual URL

    $data = json_encode($orderData);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json",
        'Content-Length: ' . strlen($data),
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }

    curl_close($ch);

    return json_decode($result, true);
}

// Usage
$orderData = [
    'order_id' => 'order_id_here',
    'products' => [
        ['product_id' => 'product_id_here', 'quantity' => 1],
    ],
    'shipping_address' => [
        'name' => 'Customer Name',
        'address' => 'Customer Address',
        'city' => 'City',
        'country' => 'Country',
        'zip' => 'Zip Code',
    ],
];

$orderResponse = createOrder($orderData, $accessToken);

if (isset($orderResponse['order_id'])) {
    echo "Order ID: {$orderResponse['order_id']}, Status: {$orderResponse['status']}\n";
} else {
    // Handle error
    echo "Error creating order: " . json_encode($orderResponse);
}
