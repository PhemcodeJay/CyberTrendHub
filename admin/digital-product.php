<?php
// Include database configuration

require_once __DIR__ . '/inc/config.php';

// Define secure files directory
$file_base_path = realpath(__DIR__ . '/../assets/uploads/files'); // Directory for digital products (outside web root)


// Check if a download code is provided for file downloads
if (isset($_GET['download_code'])) {
    $download_code = $_GET['download_code'];

    // Verify download code
    $stmt = $pdo->prepare("SELECT p.file_url FROM orders o JOIN products p ON o.product_id = p.id WHERE o.download_code = ?");
    $stmt->execute([$download_code]);
    $file = $stmt->fetch();

    if ($file) {
        $file_path = $file_base_path . '/' . basename($file['file_url']); // Ensure safe file path

        if (file_exists($file_path)) {
            // Serve the file as a download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            echo "File not found.";
            exit;
        }
    } else {
        echo "Invalid download code.";
        exit;
    }
}

// If no download code, display products and process purchases

// Retrieve digital products
$products = $pdo->query("SELECT * FROM tbl_product WHERE is_digital = TRUE")->fetchAll();

// Handle purchase form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['customer_email'])) {
    $product_id = $_POST['product_id'];
    $customer_email = $_POST['customer_email'];
    $download_code = bin2hex(random_bytes(16)); // Generate a unique download code

    // Insert order into the database
    $stmt = $pdo->prepare("INSERT INTO orders (product_id, customer_email, download_code) VALUES (?, ?, ?)");
    $stmt->execute([$product_id, $customer_email, $download_code]);

    echo "Purchase successful! Your download code is: <strong>$download_code</strong>";
    echo "<br><a href=\"digital-product.php?download_code=$download_code\">Click here to download your product</a>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Digital Products</title>
</head>
<body>
    <h1>Available Digital Products</h1>

    <?php foreach ($products as $product): ?>
        <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px;">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <p>Price: $<?php echo number_format($product['price'], 2); ?></p>
            <form method="POST" action="digital-product.php">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <label for="customer_email">Your Email:</label>
                <input type="email" name="customer_email" required>
                <button type="submit">Buy Now</button>
            </form>
        </div>
    <?php endforeach; ?>
</body>
</html>
