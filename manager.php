<?php
include 'connect.php';
require_role('manager');

$m_shop = $_SESSION['address'];

if (isset($_POST['update_account'])) {
    $uid = (int)$_SESSION['user_id'];
    $old_shop = $_SESSION['address'];
    $new_shop = trim($_POST['new_shop_name'] ?? '');
    $new_pw = $_POST['new_password'] ?? '';

    if ($new_pw !== '' && strlen($new_pw) >= 6) {
        $me = db_one($conn, "SELECT password FROM users WHERE id=?", 'i', [$uid]);
        if (!$me || !password_verify($_POST['current_password'] ?? '', $me['password'])) {
            header("Location: manager.php?tab=account&pwerror=1");
            exit();
        }
        $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
        db_run($conn, "UPDATE users SET password=? WHERE id=?", 'si', [$hashed, $uid]);
    }

    if ($new_shop !== '' && $new_shop !== $old_shop && !shop_name_taken($conn, $new_shop, $uid)) {
        db_run($conn, "UPDATE users SET address=? WHERE id=?", 'si', [$new_shop, $uid]);
        db_run($conn, "UPDATE products SET shop_name=? WHERE shop_name=?", 'ss', [$new_shop, $old_shop]);
        db_run($conn, "UPDATE categories SET shop_name=? WHERE shop_name=?", 'ss', [$new_shop, $old_shop]);
        db_run($conn, "UPDATE orders SET shop_name=? WHERE shop_name=?", 'ss', [$new_shop, $old_shop]);

        // Logo dosyasını yeni mağaza adına taşı (yoksa logo kaybolurdu)
        $old_logo = shop_logo($old_shop);
        if ($old_logo !== 'logo.png' && shop_logo_base($old_shop) !== shop_logo_base($new_shop)) {
            foreach (glob('upload/' . shop_logo_base($new_shop) . '.*') ?: [] as $f) @unlink($f);
            @rename($old_logo, 'upload/' . shop_logo_base($new_shop) . '.' . pathinfo($old_logo, PATHINFO_EXTENSION));
        }
        $_SESSION['address'] = $new_shop;
        $old_shop = $new_shop;
    }

    if (isset($_FILES["shop_logo"]) && $_FILES["shop_logo"]["size"] > 0) {
        $base = shop_logo_base($old_shop);
        $old_logos = glob("upload/" . $base . ".*") ?: [];
        $target = save_uploaded_image($_FILES["shop_logo"]["tmp_name"], $_FILES["shop_logo"]["error"],
                                      $_FILES["shop_logo"]["size"], $base);
        if ($target) {
            foreach ($old_logos as $f) {
                if ('./' . $f !== $target) @unlink($f);
            }
        }
    }
    header("Location: manager.php?tab=account");
    exit();
}

if (isset($_POST['set_target'])) {
    $_SESSION['target_sales'] = max(0, (float)($_POST['target_val'] ?? 0));
    header("Location: manager.php?tab=home");
    exit();
}

if (isset($_POST['approve_order']) || isset($_POST['reject_order'])) {
    $o_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $new_status = isset($_POST['approve_order']) ? 'Approved' : 'Rejected';
    if ($o_id) {
        // Sadece kendi mağazasının bekleyen siparişi
        db_run($conn, "UPDATE orders SET status=? WHERE id=? AND shop_name=? AND status='Pending'",
               'sis', [$new_status, $o_id, $m_shop]);
    }
    header("Location: manager.php?tab=" . ($new_status === 'Approved' ? 'deliveries' : 'orders'));
    exit();
}

if (isset($_POST["submit"])) {
    $p_name = trim($_POST["p_name"] ?? '');
    $desc = $_POST["desc"] ?? '';
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category = $_POST["category"] ?? '';

    if ($p_name !== '' && $price !== false && $price >= 0) {
        db_run($conn,
            "INSERT INTO products (name, price, category, shop_name, description) VALUES (?, ?, ?, ?, ?)",
            'sdsss', [$p_name, round($price, 2), $category, $m_shop, $desc]);
        $new_pid = mysqli_insert_id($conn);

        if (isset($_FILES["images"]) && is_array($_FILES["images"]["name"])) {
            $pictures = $_FILES["images"];
            $file_count = count($pictures["name"]);
            for ($i = 0; $i < $file_count; $i++) {
                if (empty($pictures["name"][$i])) continue;
                $destination = save_uploaded_image($pictures["tmp_name"][$i], $pictures["error"][$i], $pictures["size"][$i]);
                if ($destination) {
                    db_run($conn, "INSERT INTO pictures (product_id, path) VALUES (?, ?)", 'is', [$new_pid, $destination]);
                }
            }
        }
    }
    header("Location: manager.php?tab=products");
    exit();
}

if (isset($_POST["add_category"])) {
    $cat_name = trim($_POST["cat_name"] ?? '');
    if ($cat_name !== '') {
        db_run($conn, "INSERT INTO categories (name, shop_name) VALUES (?, ?)", 'ss', [$cat_name, $m_shop]);
    }
    header("Location: manager.php?tab=products");
    exit();
}

$rev_row = db_one($conn, "SELECT SUM(total_price) as tr FROM orders WHERE shop_name=? AND status='Approved'", 's', [$m_shop]);
$total_revenue = $rev_row['tr'] ? $rev_row['tr'] : 0;

$pend_row = db_one($conn, "SELECT COUNT(id) as pc FROM orders WHERE shop_name=? AND status='Pending'", 's', [$m_shop]);
$pending_orders = $pend_row['pc'] ? $pend_row['pc'] : 0;

$rate_row = db_one($conn, "SELECT COUNT(id) as tc FROM products WHERE shop_name=?", 's', [$m_shop]);
$total_products = $rate_row['tc'] ? $rate_row['tc'] : 0;

$target = isset($_SESSION['target_sales']) ? (float)$_SESSION['target_sales'] : 5000;
$target_percent = ($target > 0) ? round(($total_revenue / $target) * 100) : 0;

$cat_chart_labels = [];
$cat_chart_data = [];
foreach (db_all($conn, "SELECT category, COUNT(*) as cc FROM products WHERE shop_name=? GROUP BY category", 's', [$m_shop]) as $cc_row) {
    $cat_chart_labels[] = $cc_row['category'];
    $cat_chart_data[] = $cc_row['cc'];
}

$ord_chart_labels = [];
$ord_chart_data = [];
// Son 7 satış günü, tarihe göre sıralı (order_date "gg.aa.yyyy" metni olarak tutuluyor)
$oc_rows = db_all($conn,
    "SELECT order_date, SUM(total_price) as tp FROM orders WHERE shop_name=? AND status='Approved'
     GROUP BY order_date ORDER BY STR_TO_DATE(order_date, '%d.%m.%Y') DESC LIMIT 7", 's', [$m_shop]);
foreach (array_reverse($oc_rows) as $oc_row) {
    $ord_chart_labels[] = $oc_row['order_date'];
    $ord_chart_data[] = $oc_row['tp'];
}

$shop_logo_path = shop_logo($m_shop);   // her girişte doğru logo (önceden sadece yüklendiği oturumda görünüyordu)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Digital Mall - Manager Dashboard</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .product-card { 
            display: flex; 
            flex-direction: column; 
            height: 100%; 
        }
        .product-img { 
            width: 100%; 
            height: 160px; 
            background: #fff; 
            border-radius: 5px; 
            margin-bottom: 10px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            overflow: hidden; 
        }
        .product-img .carousel {
            width: 100%;
            height: 100%;
        }
        .product-img .carousel-inner {
            height: 100%;
        }
        .product-img img { 
            width: 100%; 
            height: 100%; 
            object-fit: contain; 
        }
        .product-actions {
            margin-top: auto; 
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .action-btn { 
            text-decoration: none; 
            padding: 6px 12px; 
            border-radius: 4px; 
            color: white; 
            font-weight: bold; 
            font-size: 0.9rem; 
            border: none; 
            cursor: pointer; 
            text-align: center; 
            background-color: var(--accent);
            flex: 1;
        }
        .action-btn:hover {
            color: white;
            opacity: 0.8;
        }
        .product-actions > * { flex: 1 1 50%; min-width: 0; }
        .product-actions form { display: flex !important; }
        .product-actions form button { flex: 1; }
    </style>
</head>
<body>

    <nav>
        <div class="nav-left">
            <img src="<?php echo e($shop_logo_path); ?>" class="nav-logo" alt="Logo" style="border-radius:50%; object-fit:cover;">
            <strong>MANAGER PANEL - <?php echo e($_SESSION['address']); ?></strong>
        </div>
        <div class="nav-links">
            <span class="nav-link" onclick="show('home')">Home</span>
            <span class="nav-link" onclick="show('products')">Products</span>
            <span class="nav-link" onclick="show('orders')">Orders</span>
            <span class="nav-link" onclick="show('deliveries')">Deliveries</span>
            <span class="nav-link" onclick="show('account')">Account</span>
            <?php echo post_button('logout.php', [], 'Log Out'); ?>
        </div>
    </nav>

    <div class="content">
        <div id="home" class="view" style="display: block;">
            <h1>Dashboard Overview</h1>
            
            <form action="" method="POST" style="margin-bottom: 20px;"><?php echo csrf_field(); ?>
                <input type="number" name="target_val" placeholder="Set Target ($)" value="<?php echo e($target); ?>" required style="padding: 5px; border-radius: 4px; border: 1px solid var(--accent); background: transparent; color: white;">
                <button type="submit" name="set_target" style="padding: 5px 15px;">Update Target</button>
            </form>

            <div class="stats-grid">
                <div class="stat-card"><h3>$<?php echo number_format($total_revenue, 2); ?></h3><p>Total Revenue</p></div>
                <div class="stat-card"><h3><?php echo $total_products; ?></h3><p>Total Products</p></div>
                <div class="stat-card"><h3><?php echo $pending_orders; ?></h3><p>Pending Orders</p></div>
                <div class="stat-card"><h3><?php echo $target_percent; ?>%</h3><p>Sales Target</p></div>
            </div>
            
            <div class="dashboard-tables" style="display:flex; gap:20px; margin-top:30px; flex-wrap:wrap;">
                <div class="revenue-chart-container" style="flex:1; min-width:300px; background: rgba(0,0,0,0.2); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <h3>Revenue by Date</h3>
                    <div style="height: 250px;">
                        <canvas id="dateChart"></canvas>
                    </div>
                </div>

                <div class="revenue-chart-container" style="flex:1; min-width:300px; background: rgba(0,0,0,0.2); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <h3>Products by Category</h3>
                    <div style="height: 250px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div id="products" class="view" style="display: none;">
            <h1>Products for <?php echo e($_SESSION['address']); ?></h1>
            <div class="product-grid" id="productGrid">
                <?php
                $p_results = db_all($conn, "SELECT * FROM products WHERE shop_name=?", 's', [$m_shop]);
                $pics = pictures_by_product($conn, array_column($p_results, 'id'));
                if ($p_results) {
                    foreach ($p_results as $product_row) {
                        echo '<div class="product-card">';
                        
                        echo '<div class="product-img">';
                        $carousel_id = "carousel_" . $product_row['id'];
                        echo '<div id="' . $carousel_id . '" class="carousel slide" data-bs-ride="carousel">';
                        echo '<div class="carousel-inner">';
                        
                        $pic_result = $pics[(int)$product_row['id']] ?? [];

                        $is_first = true;
                        if ($pic_result) {
                            foreach ($pic_result as $pic_path) {
                                $active_class = $is_first ? "active" : "";
                                echo '<div class="carousel-item ' . $active_class . ' h-100">';
                                echo '<img class="d-block w-100 h-100" src="' . e($pic_path) . '">';
                                echo '</div>';
                                $is_first = false;
                            }
                        } else {
                            echo '<div class="carousel-item active h-100"><div style="height:100%; width:100%; background:#444;"></div></div>';
                        }
                        echo '</div>';
                        echo '<button class="carousel-control-prev" type="button" data-bs-target="#' . $carousel_id . '" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>';
                        echo '<button class="carousel-control-next" type="button" data-bs-target="#' . $carousel_id . '" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>';
                        echo '</div>';
                        echo '</div>';
                        
                        echo "<h3 style='margin-top:10px; margin-bottom:5px; font-size:1.2rem;'>" . e($product_row['name']) . "</h3>";
                        echo "<p class='price' style='margin-bottom:5px; font-weight:bold;'>$" . e($product_row['price']) . "</p>";
                        echo '<p style="font-size:0.85rem; color:#aaa; margin-bottom:15px;">Category: ' . e($product_row['category']) . '</p>';
                        
                        echo '<div class="product-actions">';
                        echo "<a href='update.php?id=" . (int)$product_row['id'] . "' class='action-btn btn-update'>Update</a>";
                        echo post_button('delete.php', ['id' => (int)$product_row['id']], 'Delete', "class='action-btn btn-delete'", 'Delete this product?');
                        echo '</div>';
                        
                        echo '</div>';
                    }
                }
                ?>
            </div>
            
            <div class="bottom-actions">
                <button onclick="openModal('categoryModal')">Category Adjust</button>
                <button onclick="openModal('productModal')">Add Product</button>
            </div>
        </div>

        <div id="orders" class="view" style="display: none;">
            <h1>Orders</h1>
            <table>
                <thead><tr><th>ID</th><th>Date</th><th>Customer</th><th>Items</th><th>Total</th><th>Action</th></tr></thead>
                <tbody>
                    <?php
                    $o_res = db_all($conn, "SELECT * FROM orders WHERE shop_name=? AND status='Pending'", 's', [$m_shop]);
                    if($o_res){
                        foreach($o_res as $o){
                            echo "<tr>";
                            echo "<td>#".(int)$o['id']."</td>";
                            echo "<td>".e($o['order_date'])."</td>";
                            echo "<td>".e($o['customer_email'])."</td>";
                            echo "<td>".e($o['items'])."</td>";
                            echo "<td>$".e($o['total_price'])."</td>";
                            echo "<td>
                                <form action='' method='POST' style='display:inline-block;'>" . csrf_field() . "
                                    <input type='hidden' name='order_id' value='".(int)$o['id']."'>
                                    <button type='submit' name='approve_order' style='background:#28a745; padding:5px 10px; margin-right:5px;'>Approve</button>
                                    <button type='submit' name='reject_order' style='background:#dc3545; padding:5px 10px;'>Reject</button>
                                </form>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>No pending orders.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="deliveries" class="view" style="display: none;">
            <h1>Logistics & Deliveries</h1>
            <table>
                <thead><tr><th>Tracking ID</th><th>Date</th><th>Customer</th><th>Items</th><th>Status</th></tr></thead>
                <tbody>
                    <?php
                    $d_res = db_all($conn, "SELECT * FROM orders WHERE shop_name=? AND status='Approved'", 's', [$m_shop]);
                    if($d_res){
                        foreach($d_res as $d){
                            echo "<tr>";
                            echo "<td>#TRK-".(int)$d['id']."</td>";
                            echo "<td>".e($d['order_date'])."</td>";
                            echo "<td>".e($d['customer_email'])."</td>";
                            echo "<td>".e($d['items'])."</td>";
                            echo "<td><span style='color:#ffd700; font-weight:bold;'>On the way</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;'>No active deliveries.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="account" class="view" style="display: none;">
            <h1>Shop Account Settings</h1>
            <?php if (isset($_GET['pwerror'])): ?><p style="color:#ff6b6b; font-weight:bold;">Current password is wrong. Nothing was changed.</p><?php endif; ?>
            <div style="background: rgba(0,0,0,0.2); padding: 30px; border-radius: 12px; border: 1px solid var(--border-color); max-width: 400px;">
                <form action="" method="POST" enctype="multipart/form-data"><?php echo csrf_field(); ?>
                    <label>Shop Logo</label>
                    <input type="file" name="shop_logo" accept="image/jpeg,image/png,image/gif,image/webp" style="margin-bottom:15px; width:100%; color:white;">
                    
                    <label>Shop Name</label>
                    <input type="text" name="new_shop_name" value="<?php echo e($_SESSION['address']); ?>" required>
                    
                    <label>New Password (leave blank to keep current)</label>
                    <input type="password" name="new_password" minlength="6">
                    <label>Current Password (required to change password)</label>
                    <input type="password" name="current_password">
                    
                    <button type="submit" name="update_account" style="width: 100%; margin-top: 15px;">Save Changes</button>
                </form>
            </div>
        </div>
        
        <div id="categoryModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h2>Category Adjust</h2>
                <?php if (isset($_GET['caterror'])): ?><p style="color:#ff6b6b; font-weight:bold;">This category still has products. Move or delete them first.</p><?php endif; ?>
                <form action="" method="POST"><?php echo csrf_field(); ?>
                    <input type="text" name="cat_name" placeholder="Category Name (e.g. Shoes)" required>
                    <button type="submit" name="add_category" style="width:100%; margin-top:5px;">Add Category</button>
                </form>
                <hr style="border-color: #444; margin: 15px 0;">
                <h3 style="font-size: 1.1rem; margin-bottom: 10px;">Your Categories</h3>
                <ul style="list-style: none; padding: 0; margin: 0; max-height: 150px; overflow-y: auto;">
                    <?php
                        $cat_list_result = db_all($conn, "SELECT * FROM categories WHERE shop_name=?", 's', [$m_shop]);
                        if ($cat_list_result) {
                            foreach ($cat_list_result as $cat_row) {
                                echo "<li style='display:flex; justify-content:space-between; margin-bottom:8px; background:rgba(0,0,0,0.2); padding:5px 10px; border-radius:4px;'>";
                                echo "<span>" . e($cat_row['name']) . "</span>";
                                echo post_button('delete_category.php', ['name' => $cat_row['name']], 'Delete', "style='background:none; border:none; padding:0; color:#dc3545; font-weight:bold; cursor:pointer;'", 'Delete this category?');
                                echo "</li>";
                            }
                        }
                    ?>
                </ul>
                <br>
                <button onclick="closeModal('categoryModal')" style="background:#555; width:100%;">Close</button>
            </div>
        </div>

        <div id="productModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h2 id="productModalTitle">Add Product</h2>
                <form action="" method="POST" enctype="multipart/form-data" id="productForm"><?php echo csrf_field(); ?>
                    <input type="text" name="p_name" id="pName" placeholder="Product Name" required>
                    <input type="file" name="images[]" id="pImg" accept="image/jpeg,image/png,image/gif,image/webp" multiple required>
                    <select name="category" id="productCatSelect" required>
                        <option value="">Select Category</option>
                        <?php
                            $cat_result = db_all($conn, "SELECT * FROM categories WHERE shop_name=?", 's', [$m_shop]);
                            if ($cat_result) {
                                foreach ($cat_result as $c_row) {
                                    echo "<option value='" . e($c_row['name']) . "'>" . e($c_row['name']) . "</option>";
                                }
                            }
                        ?>
                    </select>
                    <input type="number" name="price" id="pPrice" placeholder="Price" step="0.01" min="0" required>
                    <textarea name="desc" id="pDesc" placeholder="Description"></textarea>
                    <div class="modal-buttons" style="margin-top: 10px; display:flex; gap:10px;">
                        <button type="submit" name="submit" style="flex:2;">Done</button>
                        <button type="button" onclick="closeModal('productModal')" style="background:#555; flex:1;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- /.content -->

    <script>
        function show(id) {
            document.getElementById('home').style.display = 'none';
            document.getElementById('products').style.display = 'none';
            document.getElementById('orders').style.display = 'none';
            document.getElementById('deliveries').style.display = 'none';
            document.getElementById('account').style.display = 'none';
            
            document.getElementById(id).style.display = 'block';
        }
        function openModal(id) { 
            document.getElementById(id).style.display = 'flex'; 
        }
        function closeModal(id) { 
            document.getElementById(id).style.display = 'none'; 
        }

        window.onload = function() {
            let urlParams = new URLSearchParams(window.location.search);
            let tab = urlParams.get('tab');
            if (tab && document.getElementById(tab)) {
                show(tab);
            }
            if (urlParams.has('caterror')) {
                openModal('categoryModal');
            }
        }

        <?php if(!empty($ord_chart_data)): ?>
            new Chart(document.getElementById('dateChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($ord_chart_labels); ?>,
                    datasets: [{
                        label: 'Daily Revenue',
                        data: <?php echo json_encode($ord_chart_data); ?>,
                        borderColor: '#8b0000',
                        backgroundColor: 'rgba(139,0,0, 0.2)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { color: '#fff' } }, x: { ticks: { color: '#fff' } } }, plugins: { legend: { labels: { color: '#fff' } } } }
            });
        <?php endif; ?>

        <?php if(!empty($cat_chart_data)): ?>
            new Chart(document.getElementById('categoryChart').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($cat_chart_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($cat_chart_data); ?>,
                        backgroundColor: ['#8b0000', '#dc3545', '#ff4d4d', '#ff9999', '#cc0000']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { color: '#fff' } } } }
            });
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>