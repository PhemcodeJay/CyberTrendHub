<?php
// Include database configuration
require_once __DIR__ . '/inc/config.php';

// Query to fetch digital products
$query = "SELECT * FROM tbl_digital_products WHERE is_digital = 1";
$result = mysqli_query($conn, $query);

// Check if products are available
if (!$result) {
    echo "Error fetching products: " . mysqli_error($conn);
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
    if (mysqli_num_rows($result) > 0) {
        // Loop through the results and display each product
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<div class="product-card">';
            echo '<img src="' . $row['file_url'] . '" alt="Product Image">';
            echo '<h3>' . $row['name'] . '</h3>';
            echo '<p>' . $row['description'] . '</p>';
            echo '<p class="price">$' . number_format($row['price'], 2) . '</p>';
            echo '<a href="' . $row['file_url'] . '" class="download-btn" download>Download Now</a>';
            echo '</div>';
        }
    } else {
        echo "<p>No digital products available.</p>";
    }

    // Close the database connection
    mysqli_close($conn);
    ?>
</div>

</body>
</html>
