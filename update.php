<?php
include 'connect.php';
require_role('manager');

$shop = $_SESSION['address'];

if (isset($_POST["update_submit"])) {
    $id = filter_input(INPUT_POST, 'p_id', FILTER_VALIDATE_INT);
    $p_name = trim($_POST["p_name"] ?? '');
    $desc = $_POST["desc"] ?? '';
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category = $_POST["category"] ?? '';

    // Sadece kendi mağazasının ürünü
    $old = $id ? db_one($conn, "SELECT name FROM products WHERE id=? AND shop_name=?", 'is', [$id, $shop]) : null;

    if ($old && $p_name !== '' && $price !== false && $price >= 0) {
        db_run($conn,
            "UPDATE products SET name=?, price=?, category=?, description=? WHERE id=? AND shop_name=?",
            'sdssis', [$p_name, round($price, 2), $category, $desc, $id, $shop]);

        
    }
    header("Location: manager.php?tab=products");
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$product = $id ? db_one($conn, "SELECT * FROM products WHERE id=? AND shop_name=?", 'is', [$id, $shop]) : null;
if (!$product) {
    header("Location: manager.php?tab=products");
    exit();
}
$categories = db_all($conn, "SELECT name FROM categories WHERE shop_name=?", 's', [$shop]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>Update Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <nav>
        <div class="nav-left">
            <strong>DIGITAL MALL - UPDATE PANEL</strong>
        </div>
        <div class="nav-links">
            <button onclick="window.location.href='manager.php?tab=products'">Back to Dashboard</button>
        </div>
    </nav>

    <div class="content">
        <div class="view" style="max-width: 500px; margin: 0 auto; display: block;">
            <h1>Update Product</h1>
            <form action="" method="POST"><?php echo csrf_field(); ?>
                <input type="hidden" name="p_id" value="<?php echo (int)$product['id']; ?>">

                <label>Product Name</label>
                <input type="text" name="p_name" value="<?php echo e($product['name']); ?>" required>

                <label>Category</label>
                <select name="category" required>
                    <option value="<?php echo e($product['category']); ?>"><?php echo e($product['category']); ?> (Current)</option>
                    <?php foreach ($categories as $c_row): ?>
                        <option value="<?php echo e($c_row['name']); ?>"><?php echo e($c_row['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Price ($)</label>
                <input type="number" name="price" step="0.01" min="0" value="<?php echo e($product['price']); ?>" required>

                <label>Description</label>
                <textarea name="desc" rows="4"><?php echo e($product['description']); ?></textarea>

                <button type="submit" name="update_submit" style="width: 100%; margin-top: 15px;">Save Changes</button>
            </form>
        </div>
    </div>

</body>
</html>
