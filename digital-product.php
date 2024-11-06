<?php
// Include database configuration
require_once __DIR__ . '/admin/inc/config.php';

try {
    // Query to fetch digital products
    $query = "SELECT * FROM tbl_digital_products WHERE is_digital = 1";
    $stmt = $pdo->query($query);

    // Fetch all products
    $digitalProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if products are available
    if (!$digitalProducts) {
        echo "No digital products found.";
    } else {
        // Process the products if needed
        foreach ($digitalProducts as $product) {
            // Handle each product as required
            // Example: echo $product['product_name'];
        }
    }
} catch (PDOException $exception) {
    echo "Error fetching products: " . $exception->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        .product-list {
            display: flex;
            flex-wrap: wrap;
        }
        .product-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 10px;
            padding: 15px;
            width: 300px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }
        .product-card h3 {
            margin: 15px 0 10px;
            font-size: 18px;
        }
        .product-card p {
            color: #555;
            font-size: 14px;
        }
        .product-card .price {
            font-size: 16px;
            font-weight: bold;
            color: #27a745;
        }
        .product-card .download-btn {
            display: inline-block;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin-top: 10px;
        }
        .product-card .download-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<h1>Digital Products</h1>

<div class="product-list">
<?php
// Check if there are any products in the result
if (!empty($digitalProducts)) {
    // Loop through the results and display each product
    foreach ($digitalProducts as $row) {
        echo '<div class="product-card">';
        echo '<img src="' . htmlspecialchars($row['file_url']) . '" alt="Product Image">';
        echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
        echo '<p class="price">$' . number_format($row['price'], 2) . '</p>';
        echo '<a href="' . htmlspecialchars($row['file_url']) . '" class="download-btn" download>Download Now</a>';
        echo '</div>';
    }
} else {
    echo "<p>No digital products available.</p>";
}
?>

</div>

</body>
</html>
