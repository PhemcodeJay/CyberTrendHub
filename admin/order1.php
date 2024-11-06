<?php require_once('header.php'); ?>
<?php
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

// Function to refresh the access token using the refresh token
function refreshAccessToken($refreshToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/refreshAccessToken';

    $data = json_encode([
        'refreshToken' => $refreshToken,
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

// Example usage: replace these with your credentials
$email = 'phemcodejay@gmail.com';
$password = '42667d2d1d1a4dd7bb1f563b8eb7fc8c'; // Use your actual password
$token = getAccessToken($email, $password);

if (isset($token['access_token'])) {
    $accessToken = $token['access_token'];
    $refreshToken = $token['refresh_token']; // Assuming the response contains a refresh_token
    echo "Access Token: " . $accessToken . "\n";
    echo "Refresh Token: " . $refreshToken . "\n";

    // You may want to store the access token and refresh token for future use
    // Example: save them to a session or database
} else {
    // Handle error
    echo "Error retrieving access token: " . json_encode($token) . "\n";
}

// Example of refreshing the token (only needed when the access token expires)
if (isset($refreshToken)) {
    $newToken = refreshAccessToken($refreshToken);
    if (isset($newToken['access_token'])) {
        $accessToken = $newToken['access_token'];
        echo "New Access Token: " . $accessToken . "\n";
        // Use the new access token as needed
    } else {
        // Handle error
        echo "Error refreshing access token: " . json_encode($newToken) . "\n";
    }
}

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


$error_message = '';
$success_message = '';
if(isset($_POST['form1'])) {
    $valid = 1;
    if(empty($_POST['subject_text'])) {
        $valid = 0;
        $error_message .= 'Subject can not be empty\n';
    }
    if(empty($_POST['message_text'])) {
        $valid = 0;
        $error_message .= 'Message can not be empty\n';
    }
    if($valid == 1) {

        $subject_text = strip_tags($_POST['subject_text']);
        $message_text = strip_tags($_POST['message_text']);

        // Getting Customer Email Address
        $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE cust_id=?");
        $statement->execute(array($_POST['cust_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            

        foreach ($result as $row) {
            $cust_email = $row['cust_email'];
        }

        // Getting Admin Email Address
        $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            

        foreach ($result as $row) {
            $admin_email = $row['contact_email'];
        }

        $order_detail = '';
        $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_id=?");
        $statement->execute(array($_POST['payment_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            

        foreach ($result as $row) {
        	if($row['payment_method'] == 'PayPal'):
        		$payment_details = 'Transaction Id: '.$row['txnid'].'<br>';
        	elseif($row['payment_method'] == 'Stripe'):
				$payment_details = 'Transaction Id: '.$row['txnid'].'<br>Card number: '.$row['card_number'].'<br>Card CVV: '.$row['card_cvv'].'<br>Card Month: '.$row['card_month'].'<br>Card Year: '.$row['card_year'].'<br>';
        	elseif($row['payment_method'] == 'Bank Deposit'):
				$payment_details = 'Transaction Details: <br>'.$row['bank_transaction_info'];
        	endif;

            $order_detail .= 'Customer Name: '.$row['customer_name'].'<br>Customer Email: '.$row['customer_email'].'<br>Payment Method: '.$row['payment_method'].'<br>Payment Date: '.$row['payment_date'].'<br>Payment Details: <br>'.$payment_details.'<br>Paid Amount: '.$row['paid_amount'].'<br>Payment Status: '.$row['payment_status'].'<br>Shipping Status: '.$row['shipping_status'].'<br>Payment Id: '.$row['payment_id'].'<br>';
        }

        $i = 0;
        $statement = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
        $statement->execute(array($_POST['payment_id']));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            

        foreach ($result as $row) {
            $i++;
            $order_detail .= '<br><b><u>Product Item '.$i.'</u></b><br>Product Name: '.$row['product_name'].'<br>Size: '.$row['size'].'<br>Color: '.$row['color'].'<br>Quantity: '.$row['quantity'].'<br>Unit Price: '.$row['unit_price'].'<br>';
        }

        $statement = $pdo->prepare("INSERT INTO tbl_customer_message (subject,message,order_detail,cust_id) VALUES (?,?,?,?)");
        $statement->execute(array($subject_text, $message_text, $order_detail, $_POST['cust_id']));

        // Sending email
        $to_customer = $cust_email;
        $message = '<html><body><h3>Message: </h3>'.$message_text.'<h3>Order Details: </h3>'.$order_detail.'</body></html>';
        $headers = 'From: ' . $admin_email . "\r\n" .
                   'Reply-To: ' . $admin_email . "\r\n" .
                   'X-Mailer: PHP/' . phpversion() . "\r\n" .
                   "MIME-Version: 1.0\r\n" . 
                   "Content-Type: text/html; charset=ISO-8859-1\r\n";

        // Sending email to admin                  
        mail($to_customer, $subject_text, $message, $headers);
        
        $success_message = 'Your email to customer is sent successfully.';
    }
}
?>

<?php
if($error_message != '') {
    echo "<script>alert('".$error_message."')</script>";
}
if($success_message != '') {
    echo "<script>alert('".$success_message."')</script>";
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>View Orders</h1>
	</div>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="box box-info">
        <div class="box-body table-responsive">
          <table id="example1" class="table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Product Details</th>
                    <th>Payment Information</th>
                    <th>Paid Amount</th>
                    <th>Payment Status</th>
                    <th>Shipping Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                $statement = $pdo->prepare("SELECT * FROM tbl_payment ORDER by id DESC");
                $statement->execute();
                $result = $statement->fetchAll(PDO::FETCH_ASSOC);                            

                foreach ($result as $row) {
                    $i++;
                    ?>
                    <tr class="<?php if($row['payment_status']=='Pending'){echo 'bg-r';}else{echo 'bg-g';} ?>">
                        <td><?php echo $i; ?></td>
                        <td>
                            <b>Id:</b> <?php echo $row['customer_id']; ?><br>
                            <b>Name:</b><br> <?php echo $row['customer_name']; ?><br>
                            <b>Email:</b><br> <?php echo $row['customer_email']; ?><br><br>
                            <a href="#" data-toggle="modal" data-target="#model-<?php echo $i; ?>"class="btn btn-warning btn-xs" style="width:100%;margin-bottom:4px;">Send Message</a>
                            <div id="model-<?php echo $i; ?>" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title" style="font-weight: bold;">Send Message</h4>
                                        </div>
                                        <div class="modal-body" style="font-size: 14px">
                                            <form action="" method="post">
                                                <input type="hidden" name="cust_id" value="<?php echo $row['customer_id']; ?>">
                                                <input type="hidden" name="payment_id" value="<?php echo $row['payment_id']; ?>">
                                                <table class="table table-bordered">
                                                    <tr>
                                                        <td>Subject</td>
                                                        <td>
                                                            <input type="text" name="subject_text" class="form-control" style="width: 100%;">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Message</td>
                                                        <td>
                                                            <textarea name="message_text" class="form-control" cols="30" rows="10" style="width:100%;height: 200px;"></textarea>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td><input type="submit" value="Send Message" name="form1"></td>
                                                    </tr>
                                                </table>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                           <?php
                           $statement1 = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
                           $statement1->execute(array($row['payment_id']));
                           $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                           foreach ($result1 as $row1) {
                                echo '<b>Product:</b> '.$row1['product_name'];
                                echo '<br>(<b>Size:</b> '.$row1['size'];
                                echo ', <b>Color:</b> '.$row1['color'].')';
                                echo '<br>(<b>Quantity:</b> '.$row1['quantity'];
                                echo ', <b>Unit Price:</b> '.$row1['unit_price'].')';
                                echo '<br><br>';
                           }
                           ?>
                        </td>
                        <td>
                            <b>Payment Method:</b> <?php echo $row['payment_method']; ?><br>
                            <b>Transaction ID:</b> <?php echo $row['txnid']; ?>
                        </td>
                        <td><b>$</b><?php echo $row['paid_amount']; ?></td>
                        <td><b>Status:</b> <?php echo $row['payment_status']; ?></td>
                        <td><b>Status:</b> <?php echo $row['shipping_status']; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require_once('footer.php'); ?>
