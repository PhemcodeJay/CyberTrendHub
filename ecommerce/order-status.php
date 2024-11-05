<?php
function getOrderStatus($orderId, $accessToken) {
    $url = "https://api.cjdropshipping.com/v1/orders/$orderId/status"; // The actual URL

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
$orderStatus = getOrderStatus('order_id_here', $accessToken);

if (isset($orderStatus['status'])) {
    echo "Order Status: {$orderStatus['status']}\n";
} else {
    // Handle error
    echo "Error retrieving order status: " . json_encode($orderStatus);
}
