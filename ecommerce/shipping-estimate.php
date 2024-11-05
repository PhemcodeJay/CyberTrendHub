<?php
function estimateShipping($shippingData, $accessToken) {
    $url = 'https://api.cjdropshipping.com/v1/shipping/estimate'; // The actual URL

    $data = json_encode($shippingData);

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
$shippingData = [
    'products' => [
        ['product_id' => 'product_id_here', 'quantity' => 1],
    ],
    'destination' => [
        'country' => 'Country',
        'state' => 'State',
        'city' => 'City',
        'zip' => 'Zip Code',
    ],
];

$shippingEstimate = estimateShipping($shippingData, $accessToken);

if (isset($shippingEstimate['cost'])) {
    echo "Estimated Shipping Cost: {$shippingEstimate['cost']}\n";
} else {
    // Handle error
    echo "Error estimating shipping: " . json_encode($shippingEstimate);
}
