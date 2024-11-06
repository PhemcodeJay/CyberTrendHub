<?php require_once('header.php'); ?>

<?php
// Common cURL request function
function makeCurlRequest($url, $headers = [], $data = null, $method = 'POST') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    } elseif ($method == 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    }

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
    }

    curl_close($ch);

    return json_decode($result, true);
}

// Function to refresh the access token using the refresh token
function refreshAccessToken($refreshToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/authentication/refreshAccessToken';
    $data = json_encode(['refreshToken' => $refreshToken]);
    $headers = ['Content-Type: application/json', 'Content-Length: ' . strlen($data)];

    return makeCurlRequest($url, $headers, $data);
}

// Function to get product details by product ID
function getProductDetail($productId, $accessToken) {
    $url = "https://developers.cjdropshipping.com/api2.0/v1/product/query?pid=$productId";
    $headers = ["CJ-Access-Token: $accessToken"];

    return makeCurlRequest($url, $headers, null, 'GET');
}

// Function to get the list of products
function getProductList($accessToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/product/list';
    $headers = ["CJ-Access-Token: $accessToken"];

    return makeCurlRequest($url, $headers, null, 'GET');
}

// Function to get product categories
function getProductCategories($accessToken) {
    $url = 'https://developers.cjdropshipping.com/api2.0/v1/product/getCategory';
    $headers = ["CJ-Access-Token: $accessToken"];

    return makeCurlRequest($url, $headers, null, 'GET');
}

// Example usage: replace these with your access and refresh tokens
$accessToken = 'eyJhbGciOiJIUzI1NiJ9.eyJqdGkiOiIxOTczMSIsInR5cGUiOiJBQ0NFU1NfVE9LRU4iLCJzdWIiOi
            JicUxvYnFRMGxtTm55UXB4UFdMWnlpa0hoOEFVU3ErcFZGdXpKdDZlTGRiMkJBRFVOOUtXTVVPR3ViejA2TnBUU2FnajAyMnR3WEZZczBYW
            WdMRnExWUxycHlGVmxqdzl4SXBZTi9zcXRKa05aUHNrODF4TVlaRm9LTG9GblF5WEVEQjhFM1RSM3RhVndDeFZmbjJSb3UraTVIUGNOUzMz
            KzBBWUhqNGtqRS8wdTRnSXBvZmZ0VGhmOHY1bGVJQUYxdFhPSDFUT1dWTDJiazd3MnFMbkZ3WURNWXFzRm9sWElnMEdtQm1CbUNGNVQzcXV
            2MWxCbkIwTVFnWjl5SDVSTFhrWFd0MWFXNzVxa3hSdXBaaXNnbndWY2tpYWpzRnZOVUV2TFNZQzYxUT0ifQ.GZLU2i3jDKp5HXEdGvaif3K
            9p8sZnVRCEbJH0TpIVjk';  // Use your actual access token
$refreshToken = 'eyJhbGciOiJIUzI1NiJ9.ey
            JqdGkiOiIxOTczMSIsInR5cGUiOiJSRUZSRVNIX1RPS0VOIiwic3ViIjoiYnFMb2JxUTBsbU5ueVFweFBXTFp5aWtIaDhBVVNxK3BWRnV6S
            nQ2ZUxkYjJCQURVTjlLV01VT0d1YnowNk5wVGhkekxRVWtyUHpvTFFlRmZUUmdmTmhkL1V1cjBMWjVzcnJ0OTdYMnljRTBOWlBzazgxeE1Z
            WkZvS0xvRm5ReVhFREI4RTNUUjN0YVZ3Q3hWZm4yUm91K2k1SFBjTlMzMyswQVlIajRrakUvMHU0Z0lwb2ZmdFRoZjh2NWxlSUFGMXRYT0g
            xVE9XVkwyYms3dzJxTG5Gd1lETVlxc0ZvbFhJZzBHbUJtQm1DRjVUM3F1djFsQm5CME1RZ1o5eUg1UkxYa1hXdDFhVzc1cWt4UnVwWmlzZ2
            53VmNraWFqc0Z2TlVFdkxTWUM2MVE9In0.KD-PGrORs_7cFyaLQmOrHCArm2MyBXPaknMTsmVEAp0';  // Use your actual refresh token

echo "Access Token: " . $accessToken . "\n";
echo "Refresh Token: " . $refreshToken . "\n";

// Example of refreshing the token (only needed when the access token expires)
if (isset($refreshToken)) {
    $newToken = refreshAccessToken($refreshToken);
    if (isset($newToken['access_token'])) {
        $accessToken = $newToken['access_token'];
        echo "New Access Token: " . $accessToken . "\n";
    } else {
        echo "Error refreshing access token: " . json_encode($newToken) . "\n";
    }
}

// Example: Get product details, list, and categories
$productDetail = getProductDetail('000B9312-456A-4D31-94BD-B083E2A198E8', $accessToken);
if (isset($productDetail['name'], $productDetail['description'], $productDetail['price'])) {
    echo "Product Name: {$productDetail['name']}, Description: {$productDetail['description']}, Price: {$productDetail['price']}\n";
} else {
    echo "Error retrieving product details: " . json_encode($productDetail) . "\n";
}

$products = getProductList($accessToken);
if (isset($products['data'])) {
    foreach ($products['data'] as $product) {
        echo "Product ID: {$product['id']}, Name: {$product['name']}, Price: {$product['price']}\n";
    }
} else {
    echo "Error retrieving product list: " . json_encode($products) . "\n";
}

$categories = getProductCategories($accessToken);
if (isset($categories['data'])) {
    foreach ($categories['data'] as $category) {
        echo "Category ID: {$category['id']}, Name: {$category['name']}\n";
    }
} else {
    echo "Error retrieving product categories: " . json_encode($categories) . "\n";
}

// Fetch local products from the database
$statement = $pdo->prepare("SELECT
    t1.p_id, t1.p_name, t1.p_old_price, t1.p_current_price, t1.p_qty, t1.p_featured_photo, t1.p_is_featured, 
    t1.p_is_active, t1.ecat_id, t2.ecat_id, t2.ecat_name, t3.mcat_id, t3.mcat_name, t4.tcat_id, t4.tcat_name
FROM tbl_product t1
JOIN tbl_end_category t2 ON t1.ecat_id = t2.ecat_id
JOIN tbl_mid_category t3 ON t2.mcat_id = t3.mcat_id
JOIN tbl_top_category t4 ON t3.tcat_id = t4.tcat_id
ORDER BY t1.p_id DESC");

$statement->execute();
$localProducts = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch dropshipping product data
$dropshippingProducts = [];
if ($accessToken) {
    $dropshippingData = getProductList($accessToken);
    $dropshippingProducts = $dropshippingData['data'] ?? [];
}


?>

<section class="content-header">
    <div class="content-header-left">
        <h1>View Products</h1>
    </div>
    <div class="content-header-right">
        <a href="product-add.php" class="btn btn-primary btn-sm">Add Product</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th width="10">#</th>
                                <th>Photo</th>
                                <th width="160">Product Name</th>
                                <th width="60">Old Price</th>
                                <th width="60">(C) Price</th>
                                <th width="60">Quantity</th>
                                <th>Featured?</th>
                                <th>Active?</th>
                                <th>Category</th>
                                <th width="80">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Display Local Products
                            $i = 0;
                            foreach ($localProducts as $row) {
                                $i++;
                            ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td style="width:82px;">
                                        <img src="../assets/uploads/<?php echo $row['p_featured_photo']; ?>" alt="<?php echo $row['p_name']; ?>" style="width:80px;">
                                    </td>
                                    <td><?php echo $row['p_name']; ?></td>
                                    <td>$<?php echo $row['p_old_price']; ?></td>
                                    <td>$<?php echo $row['p_current_price']; ?></td>
                                    <td><?php echo $row['p_qty']; ?></td>
                                    <td>
                                        <?php echo $row['p_is_featured'] == 1 ? '<span class="badge badge-success" style="background-color:green;">Yes</span>' : '<span class="badge badge-danger" style="background-color:red;">No</span>'; ?>
                                    </td>
                                    <td>
                                        <?php echo $row['p_is_active'] == 1 ? '<span class="badge badge-success" style="background-color:green;">Yes</span>' : '<span class="badge badge-danger" style="background-color:red;">No</span>'; ?>
                                    </td>
                                    <td><?php echo $row['tcat_name']; ?><br><?php echo $row['mcat_name']; ?><br><?php echo $row['ecat_name']; ?></td>
                                    <td>
                                        <a href="product-edit.php?id=<?php echo $row['p_id']; ?>" class="btn btn-primary btn-xs">Edit</a>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="product-delete.php?id=<?php echo $row['p_id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
                                    </td>
                                </tr>
                            <?php
                            }

                            // Display Dropshipping Products
                            foreach ($dropshippingProducts as $product) {
                                $i++;
                            ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td style="width:82px;">
                                        <img src="<?php echo $product['main_img']; ?>" alt="<?php echo $product['name']; ?>" style="width:80px;">
                                    </td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td>$<?php echo $product['price']; ?></td>
                                    <td>$<?php echo $product['price']; ?></td>
                                    <td>N/A</td>
                                    <td>
                                        <span class="badge badge-warning" style="background-color:yellow;">Dropship</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success" style="background-color:green;">Yes</span>
                                    </td>
                                    <td><?php echo $product['category_name']; ?></td>
                                    <td>
                                        <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-xs">Edit</a>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="product-delete.php?id=<?php echo $product['id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
                                    </td>
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

<!-- Modal for Delete Confirmation -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Delete Confirmation</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this item?</p>
                <p style="color:red;">Be careful! This product will be deleted from the order table, payment table, size table, color table, and rating table also.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>