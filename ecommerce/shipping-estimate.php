<?php
function estimateShippingAndTrack($shippingData, $trackingNumbers, $accessToken) {
    // Freight Calculate URL
    $freightUrl = 'https://developers.cjdropshipping.com/api2.0/v1/logistic/freightCalculate'; 

    // Prepare data for the freight calculation request
    $freightData = json_encode([
        'startCountryCode' => $shippingData['startCountryCode'],
        'endCountryCode' => $shippingData['endCountryCode'],
        'products' => $shippingData['products'],
    ]);

    // Initialize cURL for the freight calculation
    $ch = curl_init($freightUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "CJ-Access-Token: $accessToken",
        "Content-Type: application/json",
        'Content-Length: ' . strlen($freightData),
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $freightData);
    
    // Execute freight calculation request
    $freightResult = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'Curl error (Freight Calculation): ' . curl_error($ch);
        curl_close($ch);
        return;
    }

    // Parse freight calculation result
    $freightEstimate = json_decode($freightResult, true);

    // Track info URL
    $trackUrl = 'https://developers.cjdropshipping.com/api2.0/v1/logistic/trackInfo';
    
    // Prepare tracking numbers for the request
    $trackQuery = http_build_query(['trackNumber' => $trackingNumbers]);

    // Initialize cURL for tracking info
    $trackCh = curl_init("{$trackUrl}?$trackQuery");
    curl_setopt($trackCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($trackCh, CURLOPT_HTTPHEADER, [
        "CJ-Access-Token: $accessToken"
    ]);

    // Execute tracking info request
    $trackResult = curl_exec($trackCh);
    
    // Check for cURL errors
    if (curl_errno($trackCh)) {
        echo 'Curl error (Tracking): ' . curl_error($trackCh);
        curl_close($trackCh);
        return;
    }

    // Parse tracking info result
    $trackingInfo = json_decode($trackResult, true);

    // Close cURL handles
    curl_close($ch);
    curl_close($trackCh);

    return [
        'freightEstimate' => $freightEstimate,
        'trackingInfo' => $trackingInfo
    ];
}

// Usage
$shippingData = [
    'startCountryCode' => 'US',
    'endCountryCode' => 'US',
    'products' => [
        ['quantity' => 2, 'vid' => '439FC05B-1311-4349-87FA-1E1EF942C418'],
    ],
];

$trackingNumbers = ['CJPKL7160102171YQ', 'CJPKL7160102171YQ']; // Array of tracking numbers

$accessToken = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'; // Your CJ Dropshipping Access Token

$shippingAndTracking = estimateShippingAndTrack($shippingData, $trackingNumbers, $accessToken);

// Output the results
if (isset($shippingAndTracking['freightEstimate']['cost'])) {
    echo "Estimated Shipping Cost: {$shippingAndTracking['freightEstimate']['cost']}\n";
} else {
    echo "Error estimating shipping: " . json_encode($shippingAndTracking['freightEstimate']);
}

if (isset($shippingAndTracking['trackingInfo'])) {
    echo "Tracking Info: " . json_encode($shippingAndTracking['trackingInfo']);
} else {
    echo "Error retrieving tracking information: " . json_encode($shippingAndTracking['trackingInfo']);
}
?>
