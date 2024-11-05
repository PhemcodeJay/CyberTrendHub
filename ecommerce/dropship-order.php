<?php
<?php

// Function to create a new order
function createOrder($orderData, $accessToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/shopping/order/createOrderV2'; // Updated URL

    $data = json_encode($orderData);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "CJ-Access-Token: $accessToken", // Updated header
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

// Usage for createOrder
$orderData = [
    "orderNumber" => "1234", // Example order number
    "shippingZip" => "12345",
    "shippingCountry" => "USA",
    "shippingCountryCode" => "US",
    "shippingProvince" => "CA",
    "shippingCity" => "Los Angeles",
    "shippingCounty" => "",
    "shippingPhone" => "1234567890",
    "shippingCustomerName" => "John Doe",
    "shippingAddress" => "123 Main St",
    "shippingAddress2" => "",
    "taxId" => "123-45-6789",
    "remark" => "Urgent order",
    "products" => [
        [
            "vid" => "92511400-C758-4474-93CA-66D442F5F787", // Example product ID
            "quantity" => 1
        ]
    ]
];

$orderResponse = createOrder($orderData, $accessToken);

if (isset($orderResponse['code']) && $orderResponse['code'] == 200) {
    echo "Order ID: {$orderResponse['data']['orderId']}, Status: {$orderResponse['data']['status']}\n";
} else {
    // Handle error
    echo "Error creating order: " . json_encode($orderResponse);
}

// Function to get order status
function getOrderStatus($orderId, $accessToken) {
    $url = "https://developers.cjdropshipping.com/api2.0/v1/shopping/order/getOrderDetail?orderId=$orderId"; // Updated URL

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

// Usage for getOrderStatus
$orderStatusResponse = getOrderStatus('210711100018043276', $accessToken); // Example order ID

if (isset($orderStatusResponse['data'])) {
    echo "Order Status: {$orderStatusResponse['data']['status']}\n";
} else {
    // Handle error
    echo "Error retrieving order status: " . json_encode($orderStatusResponse);
}

// Function to track an order
function trackOrder($trackingNumber, $accessToken) {
    $url = "https://developers.cjdropshipping.com/api2.0/v1/shopping/order/track/$trackingNumber"; // Updated URL

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

// Usage for trackOrder
$trackingInfo = trackOrder('tracking_number_here', $accessToken); // Replace with a valid tracking number

if (isset($trackingInfo['data'])) {
    echo "Tracking Status: {$trackingInfo['data']['status']}, Estimated Delivery: {$trackingInfo['data']['estimatedDelivery']}\n";
} else {
    // Handle error
    echo "Error retrieving tracking information: " . json_encode($trackingInfo);
}
?>
