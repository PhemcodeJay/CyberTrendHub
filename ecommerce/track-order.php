<?php
function trackOrder($trackingNumber, $accessToken) {
    $url = "https://api.cjdropshipping.com/v1/orders/track/$trackingNumber"; // The actual URL

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
$trackingInfo = trackOrder('tracking_number_here', $accessToken);

if (isset($trackingInfo['status'], $trackingInfo['estimated_delivery'])) {
    echo "Tracking Status: {$trackingInfo['status']}, Estimated Delivery: {$trackingInfo['estimated_delivery']}\n";
} else {
    // Handle error
    echo "Error retrieving tracking information: " . json_encode($trackingInfo);
}
