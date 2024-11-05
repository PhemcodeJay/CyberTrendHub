<?php 
require_once('header.php'); 
require_once('config.php'); // Include configuration file for constants like admin email if not already included

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

            // Include additional payment-specific details
            if ($payment['payment_method'] === 'PayPal') {
                $order_detail .= "<b>Transaction ID:</b> {$payment['txnid']}<br>";
            } elseif ($payment['payment_method'] === 'Stripe') {
                $order_detail .= "<b>Card Number:</b> {$payment['card_number']}<br>
                                  <b>Card CVV:</b> {$payment['card_cvv']}<br>
                                  <b>Expiry:</b> {$payment['card_month']}/{$payment['card_year']}<br>";
            } elseif ($payment['payment_method'] === 'Bank Deposit') {
                $order_detail .= "<b>Transaction Info:</b> {$payment['bank_transaction_info']}<br>";
            }

            // Fetch order items
            $stmtOrder = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id=?");
            $stmtOrder->execute([$payment_id]);
            $order_items = $stmtOrder->fetchAll(PDO::FETCH_ASSOC);

            foreach ($order_items as $index => $item) {
                $order_detail .= "<br><b>Product {$index + 1}:</b><br>
                                  Name: {$item['product_name']}<br>
                                  Size: {$item['size']}<br>
                                  Color: {$item['color']}<br>
                                  Quantity: {$item['quantity']}<br>
                                  Unit Price: {$item['unit_price']}<br>";
                // Add tracking details for dropshipping orders
                if ($item['tracking_number']) {
                    $order_detail .= "<b>Tracking Number:</b> {$item['tracking_number']}<br>
                                      <b>Carrier:</b> {$item['carrier']}<br>";
                }
                // Add download code for digital products
                if ($item['download_code']) {
                    $order_detail .= "<b>Download Code:</b> {$item['download_code']}<br>";
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
