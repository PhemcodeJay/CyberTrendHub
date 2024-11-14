<?php
// Database connection
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'cybertrendhub';

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch digital products
    $stmt = $pdo->query("SELECT * FROM tbl_digital_products WHERE is_digital = 1");
    $digital_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Dummy user ID for demonstration, replace with actual user session ID
    $user_id = 1;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Products</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Inline CSS for styling */
        body {
            background-color: #f5f8fa;
            font-family: Arial, sans-serif;
        }
        h1 {
            font-weight: bold;
            color: #007bff;
        }
        .product-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            background-color: #ffffff;
            transition: transform 0.3s ease-in-out;
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .card-img-top {
            max-height: 200px;
            object-fit: cover;
            border-bottom: 2px solid #007bff;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #333;
        }
        .card-text {
            font-size: 0.9rem;
            color: #555;
        }
        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-primary:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .text-success {
            color: #28a745 !important;
        }
    </style>
</head>
<body>

<div class="container my-5">
    <h1 class="text-center text-primary mb-4">Our Digital Products</h1>
    <div class="row">
        <?php if(!empty($digital_products)): ?>
            <?php foreach ($digital_products as $product): ?>
                <?php
                // Check payment status for each product
                $product_id = $product['id'];
                $paymentCheckStmt = $pdo->prepare("SELECT * FROM tbl_payment WHERE user_id = :user_id AND product_id = :product_id AND status = 'completed'");
                $paymentCheckStmt->execute(['user_id' => $user_id, 'product_id' => $product_id]);
                $is_paid = $paymentCheckStmt->rowCount() > 0;
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm product-card">
                        <img src="<?= htmlspecialchars($product['product_image']) ?>" class="card-img-top" alt="Product Image">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                            <h6 class="text-success">$<?= number_format($product['price'], 2) ?></h6>
                            <?php if ($is_paid): ?>
                                <a href="<?= htmlspecialchars($product['file_url']) ?>" class="btn btn-primary" download>
                                    <i class="fas fa-download"></i> Download
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-lock"></i> Payment Required
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No digital products available at the moment.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
