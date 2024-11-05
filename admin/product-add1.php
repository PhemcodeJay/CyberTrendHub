<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;
	$error_message = '';

	// Validation for top, mid, end level categories
	if(empty($_POST['tcat_id'])) {
		$valid = 0;
		$error_message .= "You must select a top-level category<br>";
	}
	if(empty($_POST['mcat_id'])) {
		$valid = 0;
		$error_message .= "You must select a mid-level category<br>";
	}
	if(empty($_POST['ecat_id'])) {
		$valid = 0;
		$error_message .= "You must select an end-level category<br>";
	}

	// Validation for general product details
	if(empty($_POST['p_name'])) {
		$valid = 0;
		$error_message .= "Product name cannot be empty<br>";
	}
	if(empty($_POST['p_current_price'])) {
		$valid = 0;
		$error_message .= "Current price cannot be empty<br>";
	}
	if(empty($_POST['p_qty'])) {
		$valid = 0;
		$error_message .= "Quantity cannot be empty<br>";
	}

	// File validation for featured photo
	$path = $_FILES['p_featured_photo']['name'];
	$path_tmp = $_FILES['p_featured_photo']['tmp_name'];
	if($path != '') {
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		if(!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
			$valid = 0;
			$error_message .= "You must upload a jpg, jpeg, png, or gif file<br>";
		}
	} else {
		$valid = 0;
		$error_message .= "You must select a featured photo<br>";
	}

	// Proceed if all validations pass
	if($valid == 1) {
		// Get auto-increment ID for new product
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_product'");
		$statement->execute();
		$result = $statement->fetchAll();
		$ai_id = $result[0][10];

		// Handle multiple photos
		$photo = $_FILES['photo']['name'] ?? [];
		$photo_temp = $_FILES['photo']['tmp_name'] ?? [];
		if(!empty($photo)) {
			$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_product_photo'");
			$statement->execute();
			$result = $statement->fetchAll();
			$next_id1 = $result[0][10];
			foreach($photo as $key => $p) {
				$ext1 = pathinfo($p, PATHINFO_EXTENSION);
				if(in_array($ext1, ['jpg', 'jpeg', 'png', 'gif'])) {
					$final_name = $next_id1 . '.' . $ext1;
					move_uploaded_file($photo_temp[$key], "../assets/uploads/product_photos/" . $final_name);
					$statement = $pdo->prepare("INSERT INTO tbl_product_photo (photo, p_id) VALUES (?, ?)");
					$statement->execute([$final_name, $ai_id]);
					$next_id1++;
				}
			}
		}

		// Upload and move featured photo
		$final_name = 'product-featured-' . $ai_id . '.' . $ext;
		move_uploaded_file($path_tmp, '../assets/uploads/' . $final_name);

		// Insert product details into main product table
		$statement = $pdo->prepare("INSERT INTO tbl_product(
										p_name, p_old_price, p_current_price, p_qty, p_featured_photo,
										p_description, p_short_description, p_feature, p_condition, 
										p_return_policy, p_total_view, p_is_featured, p_is_active, ecat_id,
										product_source, product_url
									) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$statement->execute([
			$_POST['p_name'], $_POST['p_old_price'], $_POST['p_current_price'], $_POST['p_qty'], $final_name,
			$_POST['p_description'], $_POST['p_short_description'], $_POST['p_feature'], $_POST['p_condition'], 
			$_POST['p_return_policy'], 0, $_POST['p_is_featured'], $_POST['p_is_active'], $_POST['ecat_id'],
			$_POST['product_source'], $_POST['product_url']
		]);

		// Add product sizes
		if(isset($_POST['size'])) {
			foreach($_POST['size'] as $size) {
				$statement = $pdo->prepare("INSERT INTO tbl_product_size (size_id, p_id) VALUES (?, ?)");
				$statement->execute([$size, $ai_id]);
			}
		}

		// Add product colors
		if(isset($_POST['color'])) {
			foreach($_POST['color'] as $color) {
				$statement = $pdo->prepare("INSERT INTO tbl_product_color (color_id, p_id) VALUES (?, ?)");
				$statement->execute([$color, $ai_id]);
			}
		}

		$success_message = 'Product is added successfully.';
	}
}
?>

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
			<?php if($error_message): ?>
				<div class="callout callout-danger"><p><?php echo $error_message; ?></p></div>
			<?php endif; ?>

			<?php if($success_message): ?>
				<div class="callout callout-success"><p><?php echo $success_message; ?></p></div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info">
					<div class="box-body">
						<!-- Add form elements as before -->
						
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Product Source <span>*</span></label>
							<div class="col-sm-4">
								<select name="product_source" class="form-control">
									<option value="">Select Source</option>
									<option value="cj">CJ</option>
									<option value="aliexpress">AliExpress</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Product URL <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="product_url" class="form-control">
							</div>
						</div>

						<!-- Continue with existing form elements -->
						<button type="submit" class="btn btn-success pull-left" name="form1">Add Product</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>