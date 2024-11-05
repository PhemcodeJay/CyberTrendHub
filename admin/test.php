<?php require_once('header.php'); ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form1'])) {
    // Initialize validation and error handling
    $valid = 1;
    $error_message = '';

    // Validation checks for required fields
    $required_fields = [
        'tcat_id' => 'You must select a top-level category',
        'mcat_id' => 'You must select a mid-level category',
        'ecat_id' => 'You must select an end-level category',
        'p_name' => 'Product name cannot be empty',
        'p_current_price' => 'Current price cannot be empty',
        'p_qty' => 'Quantity cannot be empty'
    ];

    foreach ($required_fields as $field => $error) {
        if (empty($_POST[$field])) {
            $valid = 0;
            $error_message .= $error . "<br>";
        }
    }

    // Validate featured photo
    if (empty($_FILES['p_featured_photo']['name'])) {
        $valid = 0;
        $error_message .= 'You must select a featured photo<br>';
    } else {
        $file_info = pathinfo($_FILES['p_featured_photo']['name']);
        $file_ext = strtolower($file_info['extension']);
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_ext, $allowed_exts)) {
            $valid = 0;
            $error_message .= 'Featured photo must be a jpg, jpeg, png, or gif file<br>';
        }
    }

    if ($valid) {
        try {
            // Generate unique ID for product
            $product_id = $pdo->query("SHOW TABLE STATUS LIKE 'tbl_product'")->fetch(PDO::FETCH_ASSOC)['Auto_increment'];

            // Handle photo uploads and save other photos
            $uploaded_photos = handleMultipleUploads($_FILES['photo'] ?? [], '../assets/uploads/product_photos/');
            foreach ($uploaded_photos as $photo) {
                $pdo->prepare("INSERT INTO tbl_product_photo (photo, p_id) VALUES (?, ?)")
                    ->execute([$photo, $product_id]);
            }

            // Save main product image
            $featured_photo = 'product-featured-' . $product_id . '.' . $file_ext;
            move_uploaded_file($_FILES['p_featured_photo']['tmp_name'], '../assets/uploads/' . $featured_photo);

            // Insert product data
            $pdo->prepare("INSERT INTO tbl_product (p_name, p_old_price, p_current_price, p_qty, p_featured_photo, 
                            p_description, p_short_description, p_feature, p_condition, p_return_policy, 
                            p_total_view, p_is_featured, p_is_active, ecat_id)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?)")
                ->execute([
                    $_POST['p_name'], $_POST['p_old_price'], $_POST['p_current_price'], $_POST['p_qty'], $featured_photo,
                    $_POST['p_description'], $_POST['p_short_description'], $_POST['p_feature'], $_POST['p_condition'],
                    $_POST['p_return_policy'], $_POST['p_is_featured'], $_POST['p_is_active'], $_POST['ecat_id']
                ]);

            // Save product sizes and colors
            insertProductAttributes($pdo, 'tbl_product_size', 'size_id', $_POST['size'] ?? [], $product_id);
            insertProductAttributes($pdo, 'tbl_product_color', 'color_id', $_POST['color'] ?? [], $product_id);

            $success_message = 'Product added successfully.';
        } catch (Exception $e) {
            $error_message = 'An error occurred: ' . $e->getMessage();
        }
    }
}

// Helper function to handle multiple photo uploads
function handleMultipleUploads(array $photos, string $target_directory): array {
    $uploaded_files = [];
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
    foreach ($photos['name'] as $index => $name) {
        if (!empty($name)) {
            $file_ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($file_ext, $allowed_exts)) {
                $file_name = uniqid() . '.' . $file_ext;
                move_uploaded_file($photos['tmp_name'][$index], $target_directory . $file_name);
                $uploaded_files[] = $file_name;
            }
        }
    }
    return $uploaded_files;
}

// Helper function to insert product attributes (e.g., sizes or colors)
function insertProductAttributes(PDO $pdo, string $table, string $column, array $values, int $product_id) {
    foreach ($values as $value) {
        $pdo->prepare("INSERT INTO $table ($column, p_id) VALUES (?, ?)")
            ->execute([$value, $product_id]);
    }
}
?>

<!-- JavaScript for toggling fields based on product type -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productTypeSelector = document.getElementById('productType');

    productTypeSelector.addEventListener('change', toggleFields);

    function toggleFields() {
        const selectedType = productTypeSelector.value;
        document.getElementById('digitalFields').style.display = (selectedType === 'digital') ? 'block' : 'none';
        document.getElementById('dropshippingFields').style.display = (selectedType === 'dropshipping') ? 'block' : 'none';
        document.getElementById('personalFields').style.display = (selectedType === 'personal') ? 'block' : 'none';
    }
    
    // Initial toggle based on pre-selected value
    toggleFields();
});
</script>

<?php if (!empty($error_message)): ?>
    <div class="error"><?= $error_message ?></div>
<?php elseif (!empty($success_message)): ?>
    <div class="success"><?= $success_message ?></div>
<?php endif; ?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Add Product</h1>
    </div>
    <div class="content-header-right">
        <a href="product.php" class="btn btn-primary btn-sm">View All</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- Error and Success Messages -->
            <?php if(!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if(!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <!-- Form Start -->
            <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                <!-- Product Type -->
                <div class="form-group">
                    <label class="col-sm-3 control-label">Product Type</label>
                    <div class="col-sm-4">
                        <select name="product_type" id="productType" class="form-control" onchange="toggleFields()">
                            <option value="goods">Physical Goods</option>
                            <option value="digital">Digital Product</option>
                            <option value="dropshipping">Dropshipping</option>
                        </select>
                    </div>
                </div>

                <!-- General Product Fields -->
                <div class="box box-info">
                    <div class="box-body">
                        <!-- Top-Level Category Selection -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Top Level Category <span>*</span></label>
                            <div class="col-sm-4">
                                <select name="tcat_id" class="form-control select2">
                                    <option value="">Select Top Level Category</option>
                                    <?php
                                        $categories = $pdo->query("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC")->fetchAll();
                                        foreach ($categories as $category) {
                                            echo "<option value='{$category['tcat_id']}'>{$category['tcat_name']}</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Other Fields (Quantity, Price, Name, etc.) -->
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Product Name <span>*</span></label>
                            <div class="col-sm-4">
                                <input type="text" name="p_name" class="form-control" required>
                            </div>
                        </div>
                        <!-- Other general fields like price, quantity, size, color go here -->
                    </div>
                </div>

                <!-- Dropshipping Fields -->
                <div id="dropshippingFields" style="display:none;">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Supplier Name <span>*</span></label>
                        <div class="col-sm-4">
                            <input type="text" name="supplier_name" class="form-control" required>
                        </div>
                    </div>
                    <!-- Additional dropshipping fields (Supplier Contact, Website, etc.) -->
                </div>

                <!-- Digital Product Fields -->
                <div id="digitalFields" style="display:none;">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Digital Product File <span>*</span></label>
                        <div class="col-sm-4">
                            <input type="file" name="digital_file" required>
                        </div>
                    </div>
                    <!-- Additional digital product fields (Digital Type, License, etc.) -->
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-4">
                        <button type="submit" class="btn btn-success" name="form1">Add Product</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>


<script>
    // Function to toggle fields based on the selected product type
    function toggleFields() {
        const productType = document.getElementById('productType').value || document.getElementById('product_type').value;
        
        const digitalFields = document.getElementById('digitalFields');
        const dropshippingFields = document.getElementById('dropshippingFields');
        const personalFields = document.getElementById('personalFields');
        
        // Hide all sections initially
        digitalFields.style.display = 'none';
        dropshippingFields.style.display = 'none';
        if (personalFields) personalFields.style.display = 'none'; // Check if personalFields exists

        // Show the relevant section based on the selected product type
        if (productType === 'digital') {
            digitalFields.style.display = 'block';
        } else if (productType === 'dropshipping') {
            dropshippingFields.style.display = 'block';
        } else if (productType === 'personal' && personalFields) {
            personalFields.style.display = 'block';
        }
    }

    // Event listener for product type selection change
    document.getElementById('productType')?.addEventListener('change', toggleFields);
    document.getElementById('product_type')?.addEventListener('change', toggleFields);
</script>


<?php require_once('footer.php'); ?>