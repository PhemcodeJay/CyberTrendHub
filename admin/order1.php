<?php 
require_once('header.php'); 
require_once('inc/config.php'); // Include configuration file for constants like admin email if not already included

// Function to get the access token using email and password
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

// Function to create a new dropshipping order (for CJ Dropshipping)
function createDropshippingOrder($orderData, $accessToken) {
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

// Process payment and order automation
if (isset($_POST['payment_id']) && isset($_POST['cust_id'])) {

    // Database and customer details
    $payment_id = $_POST['payment_id'];
    $cust_id = $_POST['cust_id'];
    $subject_text = "Order Confirmation for Payment ID: $payment_id";

    try {
        // Fetch customer details
        $stmtCustomer = $pdo->prepare("SELECT cust_email FROM tbl_customer WHERE cust_id=?");
        $stmtCustomer->execute([$cust_id]);
        $customer = $stmtCustomer->fetch(PDO::FETCH_ASSOC);

        // Fetch payment details
        $stmtPayment = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_id=?");
        $stmtPayment->execute([$payment_id]);
        $payment = $stmtPayment->fetch(PDO::FETCH_ASSOC);

        // Fetch admin email
        $stmtAdmin = $pdo->prepare("SELECT contact_email FROM tbl_settings WHERE id=1");
        $stmtAdmin->execute();
        $admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

        if ($customer && $payment && $admin) {

            // Prepare order details
            $order_detail = "<b>Customer Name:</b> {$payment['customer_name']}<br>
                             <b>Customer Email:</b> {$payment['customer_email']}<br>
                             <b>Payment Method:</b> {$payment['payment_method']}<br>
                             <b>Payment Date:</b> {$payment['payment_date']}<br>
                             <b>Paid Amount:</b> {$payment['paid_amount']}<br>
                             <b>Payment Status:</b> {$payment['payment_status']}<br>
                             <b>Shipping Status:</b> {$payment['shipping_status']}<br>";

            // Fetch order items
            $stmtOrder = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
            $stmtOrder->execute([$payment_id]);
            $order_items = $stmtOrder->fetchAll(PDO::FETCH_ASSOC);

            foreach ($order_items as $index => $item) {
                $order_detail .= "<br><b>Product {$index}:</b><br>
                                  Name: {$item['product_name']}<br>
                                  Size: {$item['size']}<br>
                                  Color: {$item['color']}<br>
                                  Quantity: {$item['quantity']}<br>
                                  Unit Price: {$item['unit_price']}<br>";
                
                // Handling dropshipping order via CJ API
                if ($item['product_source'] === 'dropshipping') {
                    // Example order data for CJ Dropshipping
                    $dropshippingOrderData = [
                        "orderNumber" => $payment['payment_id'], 
                        "shippingZip" => $payment['shipping_zip'],
                        "shippingCountry" => $payment['shipping_country'],
                        "shippingPhone" => $payment['shipping_phone'],
                        "shippingCustomerName" => $payment['customer_name'],
                        "shippingAddress" => $payment['shipping_address'],
                        "remark" => "Dropshipping order",
                        "products" => [
                            [
                                "vid" => $item['dropshipping_product_id'], // Example product ID
                                "quantity" => $item['quantity']
                            ]
                        ]
                    ];

                    // Assuming access token has been fetched previously
                    $accessToken = getAccessToken('your-email@example.com', 'your-password')['data']['access_token']; // Update as needed

                    $orderResponse = createDropshippingOrder($dropshippingOrderData, $accessToken);

                    if (isset($orderResponse['code']) && $orderResponse['code'] == 200) {
                        $order_detail .= "<br><b>Dropshipping Order ID:</b> {$orderResponse['data']['orderId']}, Status: {$orderResponse['data']['status']}<br>";
                    } else {
                        $order_detail .= "<br><b>Dropshipping Order Error:</b> " . json_encode($orderResponse) . "<br>";
                    }
                }
            }

            // Save message to customer
            $stmtMessage = $pdo->prepare("INSERT INTO tbl_customer_message (subject, message, order_detail, cust_id) VALUES (?, ?, ?, ?)");
            $stmtMessage->execute([$subject_text, "Thank you for your order!", $order_detail, $cust_id]);

            // Send email to customer
            $to_customer = $customer['cust_email'];
            $from_email = $admin['contact_email'];
            $message = "<html><body>
                        <h3>Thank you for your order!</h3>
                        <p>Order Confirmation Details:</p>
                        {$order_detail}
                        </body></html>";
            $headers = "From: {$from_email}\r\n";
            $headers .= "Reply-To: {$from_email}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            if (mail($to_customer, $subject_text, $message, $headers)) {
                echo "<script>alert('Order processed and email sent successfully!');</script>";
            } else {
                echo "<script>alert('Order processed, but email sending failed.');</script>";
            }
        } else {
            echo "<script>alert('Order processing failed. Invalid customer or payment details.');</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error processing order: {$e->getMessage()}');</script>";
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Order Automation</h1>
    </div>
</section>

<?php require_once('footer.php'); ?>
