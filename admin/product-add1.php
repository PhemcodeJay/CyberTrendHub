<?php
// Include your database configuration
require_once 'inc/config.php';

// Handle form submission for adding products
if (isset($_POST['form1'])) {
    // Handle product submission
    $product_name = $_POST['p_name'];
    $product_price = $_POST['p_price'];
    $product_description = $_POST['p_description'];
    $category = $_POST['category'];
    $is_featured = isset($_POST['p_is_featured']) ? 1 : 0;
    $is_active = isset($_POST['p_is_active']) ? 1 : 0;
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;
    $is_personal = isset($_POST['is_personal']) ? 1 : 0;
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
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category_id, is_featured, is_active, is_digital, is_personal, file_url, featured_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_name, $product_price, $product_description, $category, $is_featured, $is_active, $is_digital, $is_personal, $file_url, $featured_image]);

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
    <script>
        function toggleProductType() {
            const productType = document.getElementById('product_type').value;
            const isDigitalFields = document.getElementById('digitalFields');
            const isDropshippingFields = document.getElementById('dropshippingFields');
            const isPersonalFields = document.getElementById('personalFields');

            isDigitalFields.style.display = 'none';
            isDropshippingFields.style.display = 'none';
            isPersonalFields.style.display = 'none';

            if (productType === 'digital') {
                isDigitalFields.style.display = 'block';
            } else if (productType === 'dropshipping') {
                isDropshippingFields.style.display = 'block';
            } else if (productType === 'personal') {
                isPersonalFields.style.display = 'block';
            }
        }
    </script>
</head>
<body>

<h2>Add Product</h2>
<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
    <div class="box box-info">
        <div class="box-body">
            <!-- Top Level Category -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Top Level Category Name <span>*</span></label>
                <div class="col-sm-4">
                    <select name="tcat_id" class="form-control select2 top-cat">
                        <option value="">Select Top Level Category</option>
                        <?php
                        $statement = $pdo->prepare("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC");
                        $statement->execute();
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);	
                        foreach ($result as $row) {
                            ?>
                            <option value="<?php echo $row['tcat_id']; ?>"><?php echo $row['tcat_name']; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <!-- Mid Level Category -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Mid Level Category Name <span>*</span></label>
                <div class="col-sm-4">
                    <select name="mcat_id" class="form-control select2 mid-cat">
                        <option value="">Select Mid Level Category</option>
                    </select>
                </div>
            </div>
            <!-- End Level Category -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">End Level Category Name <span>*</span></label>
                <div class="col-sm-4">
                    <select name="ecat_id" class="form-control select2 end-cat">
                        <option value="">Select End Level Category</option>
                    </select>
                </div>
            </div>
            <!-- Product Name -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Product Name <span>*</span></label>
                <div class="col-sm-4">
                    <input type="text" name="p_name" class="form-control" required>
                </div>
            </div>
            <!-- Old Price -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Old Price <br><span style="font-size:10px;font-weight:normal;">(In USD)</span></label>
                <div class="col-sm-4">
                    <input type="text" name="p_old_price" class="form-control">
                </div>
            </div>
            <!-- Current Price -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Current Price <span>*</span><br><span style="font-size:10px;font-weight:normal;">(In USD)</span></label>
                <div class="col-sm-4">
                    <input type="text" name="p_current_price" class="form-control" required>
                </div>
            </div>	
            <!-- Quantity -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Quantity <span>*</span></label>
                <div class="col-sm-4">
                    <input type="text" name="p_qty" class="form-control" required>
                </div>
            </div>
            <!-- Select Size -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Select Size</label>
                <div class="col-sm-4">
                    <select name="size[]" class="form-control select2" multiple="multiple">
                        <?php
                        $statement = $pdo->prepare("SELECT * FROM tbl_size ORDER BY size_id ASC");
                        $statement->execute();
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);			
                        foreach ($result as $row) {
                            ?>
                            <option value="<?php echo $row['size_id']; ?>"><?php echo $row['size_name']; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <!-- Select Color -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Select Color</label>
                <div class="col-sm-4">
                    <select name="color[]" class="form-control select2" multiple="multiple">
                        <?php
                        $statement = $pdo->prepare("SELECT * FROM tbl_color ORDER BY color_id ASC");
                        $statement->execute();
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);			
                        foreach ($result as $row) {
                            ?>
                            <option value="<?php echo $row['color_id']; ?>"><?php echo $row['color_name']; ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <!-- Featured Photo -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Featured Photo <span>*</span></label>
                <div class="col-sm-4" style="padding-top:4px;">
                    <input type="file" name="p_featured_photo" required>
                </div>
            </div>
            <!-- Other Photos -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Other Photos</label>
                <div class="col-sm-4" style="padding-top:4px;">
                    <table id="ProductTable" style="width:100%;">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="upload-btn">
                                        <input type="file" name="photo[]" style="margin-bottom:5px;">
                                    </div>
                                </td>
                                <td style="width:28px;"><a href="javascript:void()" class="Delete btn btn-danger btn-xs">X</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-sm-2">
                    <input type="button" id="btnAddNew" value="Add Item" style="margin-top: 5px;margin-bottom:10px;border:0;color: #fff;font-size: 14px;border-radius:3px;" class="btn btn-warning btn-xs">
                </div>
            </div>
            <!-- Description -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Description</label>
                <div class="col-sm-8">
                    <textarea name="p_description" class="form-control" cols="30" rows="10" id="editor1"></textarea>
                </div>
            </div>
            <!-- Short Description -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Short Description</label>
                <div class="col-sm-8">
                    <textarea name="p_short_description" class="form-control" cols="30" rows="10" id="editor2"></textarea>
                </div>
            </div>
            <!-- Features -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Features</label>
                <div class="col-sm-8">
                    <textarea name="p_feature" class="form-control" cols="30" rows="10" id="editor3"></textarea>
                </div>
            </div>
            <!-- Conditions -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Conditions</label>
                <div class="col-sm-8">
                    <textarea name="p_condition" class="form-control" cols="30" rows="10" id="editor4"></textarea>
                </div>
            </div>
            <!-- Return Policy -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Return Policy</label>
                <div class="col-sm-8">
                    <textarea name="p_return_policy" class="form-control" cols="30" rows="10" id="editor5"></textarea>
                </div>
            </div>
            <!-- Is Featured -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Is Featured?</label>
                <div class="col-sm-8">
                    <select name="p_is_featured" class="form-control" style="width:auto;">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select> 
                </div>
            </div>
            <!-- Is Active -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Is Active?</label>
                <div class="col-sm-8">
                    <select name="p_is_active" class="form-control" style="width:auto;">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select> 
                </div>
            </div>

            <!-- Digital Product Fields -->
            <div id="digitalFields" style="display:none;">
                <div class="form-group">
                    <label for="" class="col-sm-3 control-label">Upload Digital Product File <span>*</span></label>
                    <div class="col-sm-4">
                        <input type="file" name="digital_file" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-3 control-label">Digital Product Type <span>*</span></label>
                    <div class="col-sm-4">
                        <input type="text" name="digital_type" class="form-control" required>
                    </div>
                </div>
            </div>

            <!-- Dropshipping Fields -->
            <div id="dropshippingFields" style="display:none;">
                <div class="form-group">
                    <label for="" class="col-sm-3 control-label">Supplier Name <span>*</span></label>
                    <div class="col-sm-4">
                        <input type="text" name="supplier_name" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-3 control-label">Supplier Contact <span>*</span></label>
                    <div class="col-sm-4">
                        <input type="text" name="supplier_contact" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-3 control-label">Supplier Website <span>*</span></label>
                    <div class="col-sm-4">
                        <input type="text" name="supplier_website" class="form-control" required>
                    </div>
                </div>
            </div>

            <!-- Product Type -->
            <div class="form-group">
                <label for="" class="col-sm-3 control-label">Product Type</label>
                <div class="col-sm-4">
                    <select name="product_type" id="productType" class="form-control">
                        <option value="goods">Physical Goods</option>
                        <option value="digital">Digital Product</option>
                        <option value="dropshipping">Dropshipping</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <input type="submit" name="btnSave" value="Add Product" class="btn btn-info pull-right">
        </div>
    </div>
</form>

<script>
    document.getElementById('productType').addEventListener('change', function () {
        var selectedType = this.value;
        document.getElementById('digitalFields').style.display = selectedType === 'digital' ? 'block' : 'none';
        document.getElementById('dropshippingFields').style.display = selectedType === 'dropshipping' ? 'block' : 'none';
    });
</script>

</body>
</html>
