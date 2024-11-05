<?php
function getAccessToken($email, $password) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/getAccessToken';

    $data = json_encode([
        'email' => $email,
        'password' => $password,
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data),
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

// Usage
$email = 'v0pjsw5t@linshiyouxiang.net';
$password = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'; // Use your actual password
$token = getAccessToken($email, $password);

if (isset($token['access_token'])) {
    $accessToken = $token['access_token'];
    // Use the access token as needed
} else {
    // Handle error
    echo "Error retrieving access token: " . json_encode($token);
}
