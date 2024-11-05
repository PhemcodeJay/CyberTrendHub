<?php
// Include your database configuration
require_once 'config.php';

// Handle form submission for adding products
if (isset($_POST['form1'])) {
    // Handle normal product submission (for personal, digital, etc.)
    $product_name = $_POST['p_name'];
    $product_price = $_POST['p_price'];
    $product_description = $_POST['p_description'];
    $category = $_POST['category'];
    $is_featured = isset($_POST['p_is_featured']) ? 1 : 0;
    $is_active = isset($_POST['p_is_active']) ? 1 : 0;
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;
    $file_url = $is_digital ? $_POST['file_url'] : null;

    // Handle featured image upload
    $featured_image = null;
    if (isset($_FILES['p_featured_photo'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["p_featured_photo"]["name"]);
        if (move_uploaded_file($_FILES["p_featured_photo"]["tmp_name"], $target_file)) {
            $featured_image = $target_file;
        }
    }

    // Handle additional images upload
    $additional_photos = [];
    if (isset($_FILES['photo'])) {
        foreach ($_FILES['photo']['tmp_name'] as $key => $tmp_name) {
            $photo_path = "uploads/" . basename($_FILES['photo']['name'][$key]);
            if (move_uploaded_file($tmp_name, $photo_path)) {
                $additional_photos[] = $photo_path;
            }
        }
    }

    // Insert product into database
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category_id, is_featured, is_active, is_digital, file_url, featured_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_name, $product_price, $product_description, $category, $is_featured, $is_active, $is_digital, $file_url, $featured_image]);

        // Get the last inserted product ID
        $product_id = $pdo->lastInsertId();

        // Insert additional images if any
        foreach ($additional_photos as $photo) {
            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
            $stmt->execute([$product_id, $photo]);
        }

        echo "Product added successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle API request to add dropshipping product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['api'])) {
    // Parse JSON payload
    $data = json_decode(file_get_contents("php://input"));

    // Check if data is valid
    if (isset($data->name) && isset($data->price) && isset($data->description)) {
        try {
            $name = $data->name;
            $price = $data->price;
            $description = $data->description;
            $category = $data->category;

            // Insert dropshipping product into database
            $stmt = $pdo->prepare("INSERT INTO tbl_dropshipping_products (name, price, description, category) 
            VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $price, $description, $category]);

            echo json_encode(['status' => 'success', 'message' => 'Dropshipping product added successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    }
    exit;
}

// Example cURL request to add a dropshipping product
if (isset($_POST['curl_request'])) {
    $ch = curl_init();

    $data = [
        'name' => 'Smartphone XYZ',
        'price' => 299.99,
        'description' => 'Latest Smartphone with cutting-edge features.',
        'category' => 'Electronics'
    ];

    $json_data = json_encode($data);

    curl_setopt($ch, CURLOPT_URL, 'http://yourdomain.com/yourfile.php?api=true');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    curl_close($ch);

    echo $response;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>Add Product</h2>
<form method="post" action="" enctype="multipart/form-data">
    <!-- Product Name -->
    <div class="form-group">
        <label for="p_name" class="col-sm-3 control-label">Product Name</label>
        <div class="col-sm-4">
            <input type="text" name="p_name" class="form-control" required>
        </div>
    </div>

    <!-- Product Price -->
    <div class="form-group">
        <label for="p_price" class="col-sm-3 control-label">Product Price</label>
        <div class="col-sm-4">
            <input type="number" name="p_price" class="form-control" required>
        </div>
    </div>

    <!-- Product Description -->
    <div class="form-group">
        <label for="p_description" class="col-sm-3 control-label">Description</label>
        <div class="col-sm-4">
            <textarea name="p_description" class="form-control" required></textarea>
        </div>
    </div>

    <!-- Category -->
    <div class="form-group">
        <label for="category" class="col-sm-3 control-label">Category</label>
        <div class="col-sm-4">
            <select name="category" class="form-control" required>
                <option value="1">Electronics</option>
                <option value="2">Clothing</option>
                <option value="3">Home & Kitchen</option>
                <!-- Add more categories as needed -->
            </select>
        </div>
    </div>

    <!-- Featured Product Checkbox -->
    <div class="form-group">
        <label for="p_is_featured" class="col-sm-3 control-label">Featured</label>
        <div class="col-sm-4">
            <input type="checkbox" name="p_is_featured" value="1">
        </div>
    </div>

    <!-- Active Product Checkbox -->
    <div class="form-group">
        <label for="p_is_active" class="col-sm-3 control-label">Active</label>
        <div class="col-sm-4">
            <input type="checkbox" name="p_is_active" value="1">
        </div>
    </div>

    <!-- Featured Image Upload -->
    <div class="form-group">
        <label for="p_featured_photo" class="col-sm-3 control-label">Featured Photo</label>
        <div class="col-sm-4">
            <input type="file" name="p_featured_photo" class="form-control" required>
        </div>
    </div>

    <!-- Additional Photos Upload -->
    <div class="form-group">
        <label for="photo" class="col-sm-3 control-label">Additional Photos</label>
        <div class="col-sm-4">
            <input type="file" name="photo[]" class="form-control" multiple>
        </div>
    </div>

    <!-- Digital Product Option -->
    <div class="form-group">
        <label for="is_digital" class="col-sm-3 control-label">Digital Product</label>
        <div class="col-sm-4">
            <input type="checkbox" name="is_digital" value="1">
        </div>
    </div>

    <!-- Digital Product File URL -->
    <div class="form-group">
        <label for="file_url" class="col-sm-3 control-label">Digital Product File URL</label>
        <div class="col-sm-4">
            <input type="text" name="file_url" class="form-control">
        </div>
    </div>

    <button type="submit" name="form1" class="btn btn-primary">Submit</button>
</form>

<h3>Test API (Curl)</h3>
<form method="post" action="">
    <button type="submit" name="curl_request" class="btn btn-secondary">Send API Request</button>
</form>

</body>
</html>
