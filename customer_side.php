<?php
include 'connect.php';
require_role('customer');

if (isset($_POST['change_password'])) {
    $uid = (int)$_SESSION['user_id'];
    $current_pw = $_POST['current_password'] ?? '';
    $new_pw = $_POST['new_password'] ?? '';

    $user = db_one($conn, "SELECT password FROM users WHERE id=?", 'i', [$uid]);
    if ($user && strlen($new_pw) >= 6 && password_verify($current_pw, $user['password'])) {
        $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
        db_run($conn, "UPDATE users SET password=? WHERE id=?", 'si', [$hashed, $uid]);
    }
    header("Location: customer_side.php");
    exit();
}

if (isset($_POST['checkout_data'])) {
    // İstemciden sadece ürün id ve adet gelir; ad, fiyat ve mağaza DB'den okunur.
    $cart = json_decode($_POST['checkout_data'], true);
    $c_email = $_SESSION['email'];
    $date = date('d.m.Y');
    $orders_by_shop = [];

    if (is_array($cart)) {
        foreach ($cart as $item) {
            $pid = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
            $qty = filter_var($item['qty'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
            if (!$pid || !$qty) continue;

            $p = db_one($conn, "SELECT name, price, shop_name FROM products WHERE id=?", 'i', [$pid]);
            if (!$p) continue;

            $shop = $p['shop_name'];
            if (!isset($orders_by_shop[$shop])) {
                $orders_by_shop[$shop] = ['items' => [], 'total' => 0];
            }
            $orders_by_shop[$shop]['items'][] = $p['name'] . " (x" . $qty . ")";
            $orders_by_shop[$shop]['total'] += $p['price'] * $qty;
        }
    }

    foreach ($orders_by_shop as $shop => $data) {
        db_run($conn,
            "INSERT INTO orders (customer_email, shop_name, items, total_price, status, order_date) VALUES (?, ?, ?, ?, 'Pending', ?)",
            'sssds', [$c_email, $shop, implode(", ", $data['items']), round($data['total'], 2), $date]);
    }

    header("Location: customer_side.php?ordered=" . ($orders_by_shop ? '1' : '0'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Mall - Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .hidden { display: none !important; }
        .carousel-control-prev, .carousel-control-next { opacity: 0.8; }
        .carousel-control-prev-icon, .carousel-control-next-icon { background-color: rgba(0,0,0,0.5); border-radius: 50%; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .product-card { background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); text-align: center; overflow: hidden; }
        .carousel-item img { height: 200px; object-fit: cover; }
        .btn-cart-added { background: #28a745 !important; transition: 0.3s; }
        
        .smart-title { overflow: hidden; white-space: nowrap; width: 100%; }
        .marquee-div { display: inline-block; white-space: nowrap; animation: marqueeAnim 8s linear infinite; }
        .marquee-div:hover { animation-play-state: paused; }
        @keyframes marqueeAnim {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav-left">
            <img src="logo.png" class="nav-logo" alt="Logo">
            <strong>DIGITAL MALL - CUSTOMER</strong>
        </div>
        <div class="nav-links">
            <span class="nav-link" onclick="showView('home')">Home</span>
            <span class="nav-link" id="nav-cart-btn" onclick="showView('cart')">Cart (0)</span>
            <span class="nav-link" onclick="showView('orders')">Orders</span>
            <span class="nav-link" onclick="showView('account')">Account</span>
            <?php echo post_button('logout.php', [], 'Log Out', 'style="background-color: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer;"'); ?>
        </div>
    </nav>

    <div class="content">
        <div id="home" class="view" style="display: block;">
            <h1>Available Shops</h1>
            <p>Select a store to browse its products.</p>
            
            <div class="product-grid">
                <?php
                $shop_query = "SELECT DISTINCT address FROM users WHERE role = 'manager' AND approved = 1 AND address IS NOT NULL AND address != ''";
                $shop_result = mysqli_query($conn, $shop_query);
                
                if ($shop_result && mysqli_num_rows($shop_result) > 0) {
                    while ($shop = mysqli_fetch_assoc($shop_result)) {
                        $s_name = $shop['address'];
                        $shop_img = shop_logo($s_name);

                        echo '<div class="product-card" style="padding-bottom: 15px;">';
                        echo '<div style="height: 200px; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.2); border-radius: 4px; margin-bottom: 15px;">';
                        echo '<img src="' . e($shop_img) . '" style="max-width: 100%; max-height: 180px; object-fit: contain;">';
                        echo '</div>';
                        echo '<h3 class="smart-title" data-text="' . htmlspecialchars($s_name) . '" style="margin-top:10px;">' . htmlspecialchars($s_name) . '</h3>';
                        echo '<button style="margin-top:15px; width: 100%;" data-shop="' . e($s_name) . '" onclick="openStore(this.dataset.shop)">View Store</button>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>No shops available.</p>";
                }
                ?>
            </div>
        </div>

        <div id="store-view" class="view" style="display: none;">
            <button onclick="showView('home')" style="background: transparent; border: 1px solid var(--border-color); color: white; margin-bottom: 20px;">← Back to Shops</button>
            <h1 id="store-title" class="smart-title" data-text="" style="max-width: 100%;"></h1>
            
            <div class="product-grid">
                <?php
                $prod_rows = db_all($conn, "SELECT p.* FROM products p JOIN users u ON u.role='manager' AND u.approved=1 AND u.address=p.shop_name");
                $pics = pictures_by_product($conn, array_column($prod_rows, 'id'));
                if ($prod_rows) {
                    foreach ($prod_rows as $p) {
                        $p_name = $p['name'];
                        echo '<div class="product-card store-product" data-shop="'.e($p['shop_name']).'" style="padding-bottom: 15px;">';
                        
                        $pic_result = $pics[(int)$p['id']] ?? [];
                        $carousel_id = "carousel_cust_" . $p['id'];
                        
                        echo '<div id="' . $carousel_id . '" class="carousel slide" data-bs-ride="carousel">';
                        echo '<div class="carousel-inner">';
                        $is_first = true;
                        
                        if ($pic_result) {
                            foreach ($pic_result as $pic_path) {
                                $active_class = $is_first ? "active" : "";
                                echo '<div class="carousel-item ' . $active_class . '">';
                                echo '<img class="d-block w-100" src="' . e($pic_path) . '">';
                                echo '</div>';
                                $is_first = false;
                            }
                        } else {
                            echo '<div class="carousel-item active"><div style="height:200px; background:#444;"></div></div>';
                        }
                        echo '</div>';
                        echo '<button class="carousel-control-prev" type="button" data-bs-target="#' . $carousel_id . '" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>';
                        echo '<button class="carousel-control-next" type="button" data-bs-target="#' . $carousel_id . '" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>';
                        echo '</div>';

                        echo '<h3 class="smart-title" data-text="' . htmlspecialchars($p['name']) . '" style="margin-top: 15px;">' . htmlspecialchars($p['name']) . '</h3>';
                        echo '<p style="font-size: 0.85rem; color: #bbb; min-height: 40px; margin-bottom: 10px;">'.e($p['description']).'</p>';
                        echo '<p style="font-size: 1.2rem; font-weight: bold; color: var(--accent);">$'.e($p['price']).'</p>';
                        
                        echo '<div style="display: flex; gap: 10px; margin-top: 10px;">';
                        echo '<input type="number" id="qty_'.(int)$p['id'].'" value="1" min="1" max="100" style="width: 60px; padding: 5px; border-radius: 4px; border: 1px solid var(--border-color); background: rgba(0,0,0,0.3); color: white; text-align: center;">';
                        echo '<button id="btn_'.(int)$p['id'].'" style="flex: 1;" data-id="'.(int)$p['id'].'" data-name="'.e($p['name']).'" data-price="'.e($p['price']).'" data-shop="'.e($p['shop_name']).'" onclick="addToCart(this)">Add to Cart</button>';
                        echo '</div>';
                        
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>

        <div id="cart" class="view" style="display: none;">
            <h1>Your Shopping Cart</h1>
            <table class="table text-white">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Shop</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="cart-tbody"></tbody>
            </table>
            <div class="cart-summary" style="margin-top: 20px; float: right; background: rgba(0,0,0,0.2); padding: 20px; border-radius: 8px; border: 1px solid var(--border-color); width: 300px;">
                <h3 style="display: flex; justify-content: space-between;"><span>Total:</span> <span id="cart-total" style="color: var(--accent);">$0.00</span></h3>
                <button onclick="confirmOrder()" style="width: 100%; margin-top:15px;">Confirm Order</button>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div id="orders" class="view" style="display: none;">
            <h1>My Orders</h1>
            <table class="table text-white">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Shop</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $c_email = $_SESSION['email'];
                    $my_orders = db_all($conn, "SELECT * FROM orders WHERE customer_email=? ORDER BY id DESC", 's', [$c_email]);
                    if ($my_orders) {
                        foreach($my_orders as $o){
                            echo "<tr>";
                            echo "<td>#".(int)$o['id']."</td>";
                            echo "<td>".e($o['order_date'])."</td>";
                            echo "<td>".e($o['shop_name'])."</td>";
                            echo "<td>".e($o['items'])."</td>";
                            echo "<td>$".e($o['total_price'])."</td>";
                            $color = ($o['status'] == 'Pending') ? '#ffc107' : '#28a745';
                            if($o['status'] == 'Rejected') $color = '#dc3545';
                            echo "<td style='color: ".$color."; font-weight: bold;'>".e($o['status'])."</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>You haven't placed any orders yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="account" class="view" style="display: none;">
            <h1>Account Settings</h1>
            <div style="background: rgba(0,0,0,0.2); padding: 30px; border-radius: 12px; border: 1px solid var(--border-color); max-width: 400px;">
                <label>Full Name</label>
                <input type="text" value="<?php echo e($_SESSION['fullname']); ?>" readonly>
                <label>Email</label>
                <input type="email" value="<?php echo e($_SESSION['email']); ?>" readonly>
                
                <hr style="border-color: var(--border-color); margin: 25px 0;">
                <h3 style="margin-bottom: 15px; color: var(--accent);">Change Password</h3>
                <form action="" method="POST"><?php echo csrf_field(); ?>
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                    <button type="submit" name="change_password" style="width: 100%; margin-top: 15px;">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <form id="checkoutForm" action="" method="POST" style="display:none;"><?php echo csrf_field(); ?>
        <input type="hidden" name="checkout_data" id="checkoutData">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let userEmail = <?php echo json_encode($_SESSION['email']); ?>;
        let cartKey = 'mall_cart_' + userEmail;
        let cartStr = localStorage.getItem(cartKey);
        let cartItems = [];
        
        try { cartItems = cartStr ? JSON.parse(cartStr) : []; } catch (err) { cartItems = []; }
        // Eski sürümden kalan id'siz sepet öğelerini at
        cartItems = cartItems.filter(function (it) { return it && it.id; });
        if (new URLSearchParams(window.location.search).get('ordered') === '1') {
            cartItems = [];
            localStorage.removeItem(cartKey);
        }

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, function (c) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
            });
        }

        function applySmartTitles() {
            let titles = document.querySelectorAll('.smart-title');
            titles.forEach(title => {
                let raw = title.getAttribute('data-text');
                if (!raw) return;
                let text = escapeHtml(raw);
                
                title.innerHTML = '<span style="display:inline-block;">' + text + '</span>';
                
                if (title.firstChild.scrollWidth > title.clientWidth) {
                    let repeatText = ' ✦ ' + text + ' &nbsp;&nbsp;&nbsp; ✦ ' + text + ' &nbsp;&nbsp;&nbsp;';
                    title.innerHTML = '<div class="marquee-div">' + repeatText + repeatText + '</div>';
                } else {
                    title.innerHTML = text;
                }
            });
        }

        function showView(viewId) {
            let views = document.getElementsByClassName('view');
            for(let i=0; i<views.length; i++){
                views[i].style.display = 'none';
            }
            document.getElementById(viewId).style.display = 'block';
            applySmartTitles();
        }

        function openStore(storeName) {
            let titleContainer = document.getElementById('store-title');
            titleContainer.setAttribute('data-text', storeName);
            
            let products = document.getElementsByClassName('store-product');
            for (let i = 0; i < products.length; i++) {
                if (products[i].getAttribute('data-shop') === storeName) {
                    products[i].style.display = 'block';
                } else {
                    products[i].style.display = 'none';
                }
            }
            showView('store-view');
        }

        function addToCart(btn) {
            let id = parseInt(btn.dataset.id);
            let name = btn.dataset.name;
            let price = parseFloat(btn.dataset.price);
            let shop = btn.dataset.shop;
            let qtyInput = document.getElementById('qty_' + id);
            let qtyVal = parseInt(qtyInput.value);
            if (isNaN(qtyVal) || qtyVal < 1) {
                qtyVal = 1;
            }

            let found = false;
            for (let i = 0; i < cartItems.length; i++) {
                if (cartItems[i].id === id) {
                    cartItems[i].qty += qtyVal;
                    found = true;
                    break;
                }
            }
            if (!found) {
                let newItem = { id: id, name: name, price: price, qty: qtyVal, shop: shop };
                cartItems.push(newItem);
            }
            
            localStorage.setItem(cartKey, JSON.stringify(cartItems));
            updateCartUI();

            let originalText = btn.innerText;
            btn.innerText = "Added ✓";
            btn.classList.add("btn-cart-added");
            setTimeout(function() {
                btn.innerText = originalText;
                btn.classList.remove("btn-cart-added");
            }, 1000);
        }

        function removeFromCart(index) {
            cartItems.splice(index, 1);
            localStorage.setItem(cartKey, JSON.stringify(cartItems));
            updateCartUI();
        }

        function updateCartUI() {
            let tbody = document.getElementById('cart-tbody');
            tbody.innerHTML = '';
            
            let total = 0;
            let totalQty = 0;

            for (let i = 0; i < cartItems.length; i++) {
                let item = cartItems[i];
                let rowTotal = item.price * item.qty;
                total += rowTotal;
                totalQty += item.qty;

                let tr = document.createElement('tr');
                tr.innerHTML = '<td>' + escapeHtml(item.name) + '</td>' +
                               '<td>' + escapeHtml(item.shop) + '</td>' +
                               '<td>' + parseInt(item.qty) + '</td>' +
                               '<td>$' + parseFloat(item.price) + '</td>' +
                               '<td>$' + rowTotal.toFixed(2) + '</td>' +
                               '<td><button style="background: #dc3545; padding: 5px; border:none; color:white; border-radius:4px;" onclick="removeFromCart(' + i + ')">Remove</button></td>';
                tbody.appendChild(tr);
            }

            document.getElementById('cart-total').innerText = '$' + total.toFixed(2);
            document.getElementById('nav-cart-btn').innerText = 'Cart (' + totalQty + ')';
        }

        function confirmOrder() {
            if (cartItems.length === 0) {
                return;
            }
            
            // Sadece id ve adet gönderilir; fiyatı sunucu hesaplar. Sepet, sipariş başarılı olunca temizlenir.
            let payload = cartItems.map(function (it) { return { id: it.id, qty: it.qty }; });
            document.getElementById('checkoutData').value = JSON.stringify(payload);
            document.getElementById('checkoutForm').submit();
        }

        window.onload = function() {
            updateCartUI();
            applySmartTitles();
        }
    </script>
</body>
</html>