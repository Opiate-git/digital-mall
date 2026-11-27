<?php
include 'connect.php';
require_role('admin');

if (isset($_POST['approve_manager'])) {
    $mid = filter_input(INPUT_POST, 'manager_id', FILTER_VALIDATE_INT);
    if ($mid) {
        db_run($conn, "UPDATE users SET approved=1 WHERE id=? AND role='manager'", 'i', [$mid]);
    }
    header("Location: admin.php?tab=managers");
    exit();
}

if (isset($_POST["delete_user"])) {
    $del_id = filter_input(INPUT_POST, 'delete_user', FILTER_VALIDATE_INT);

    // Sadece müşteri ve manager silinebilir (admin kendini/başka admini silemez)
    $user_row = $del_id ? db_one($conn, "SELECT email, role, address FROM users WHERE id=? AND role IN ('customer','manager')", 'i', [$del_id]) : null;

    if ($user_row) {
        $u_email = $user_row['email'];
        $u_role = $user_row['role'];
        $u_shop = $user_row['address'];
        
        if ($u_role == 'manager') {
            // Mağazanın tüm verisi: resimler (FK ile ürünle birlikte), ürünler, kategoriler, siparişler, logo
            db_run($conn, "DELETE pi FROM pictures pi JOIN products p ON p.id = pi.product_id WHERE p.shop_name=?", 's', [$u_shop]);
            db_run($conn, "DELETE FROM products WHERE shop_name=?", 's', [$u_shop]);
            db_run($conn, "DELETE FROM categories WHERE shop_name=?", 's', [$u_shop]);
            db_run($conn, "DELETE FROM orders WHERE shop_name=?", 's', [$u_shop]);
            $logo = shop_logo($u_shop);
            if ($logo !== 'logo.png') @unlink($logo);
        } elseif ($u_role == 'customer') {
            db_run($conn, "DELETE FROM orders WHERE customer_email=?", 's', [$u_email]);
        }
        db_run($conn, "DELETE FROM users WHERE id=?", 'i', [$del_id]);
    }
    header("Location: admin.php?tab=users");
    exit();
}

$chart_labels = [];
$chart_data = [];

$chart_sql = "SELECT u.address as shop_name, COALESCE(SUM(o.total_price), 0) as total_revenue FROM users u LEFT JOIN orders o ON u.address = o.shop_name AND o.status='Approved' WHERE u.role='manager' GROUP BY u.address";
$chart_result = mysqli_query($conn, $chart_sql);

if ($chart_result && mysqli_num_rows($chart_result) > 0) {
    while ($row = mysqli_fetch_assoc($chart_result)) {
        $chart_labels[] = $row['shop_name'];
        $chart_data[] = $row['total_revenue'];
    }
}

$chart2_labels = [];
$chart2_data = [];

$chart2_sql = "SELECT u.address as shop_name, COUNT(p.id) as p_count FROM users u LEFT JOIN products p ON u.address = p.shop_name WHERE u.role='manager' GROUP BY u.address";
$chart2_result = mysqli_query($conn, $chart2_sql);

if ($chart2_result && mysqli_num_rows($chart2_result) > 0) {
    while ($row2 = mysqli_fetch_assoc($chart2_result)) {
        $chart2_labels[] = $row2['shop_name'];
        $chart2_data[] = $row2['p_count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Mall Portal</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .action-link { text-decoration: none; padding: 5px 10px; border-radius: 4px; color: white; font-weight: bold; font-size: 0.8rem; }
        .bg-red { background-color: #dc3545; }
        .chart-wrapper { background: rgba(0,0,0,0.2); padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); margin-top: 30px; margin-bottom: 30px;}
    </style>
</head>
<body>

    <div id="adminPanel">
        <nav>
            <div class="nav-left">
                <img src="logo.png" class="nav-logo" alt="Logo">
                <strong>ADMIN PANEL</strong>
            </div>
            <div class="nav-links">
                <span class="nav-link" onclick="show('home')">Home</span>
                <span class="nav-link" onclick="show('users')">Manage Users</span>
                <span class="nav-link" onclick="show('managers')">Manage Managers</span>
                <?php echo post_button('logout.php', [], 'Log Out'); ?>
            </div>
        </nav>
        
        <div class="content">
            <div id="home" class="view" style="display: block;">
                <h1>Dashboard</h1>
                <div class="stats-grid">
                    <?php
                        $cat_sql = "SELECT * FROM users WHERE role='manager'";
                        $cat_result = mysqli_query($conn, $cat_sql);
                        $total_shops = 0;
                        if ($cat_result) {
                            $total_shops = mysqli_num_rows($cat_result);
                        }

                        $prod_sql = "SELECT * FROM products";
                        $prod_result = mysqli_query($conn, $prod_sql);
                        $total_products = 0;
                        $total_value = 0;
                        
                        if ($prod_result) {
                            $total_products = mysqli_num_rows($prod_result);
                            while ($p_row = mysqli_fetch_assoc($prod_result)) {
                                $total_value = $total_value + $p_row['price'];
                            }
                        }
                    ?>
                    <div class="stat-card"><h3><?php echo $total_shops; ?></h3><p>Active Shops</p></div>
                    <div class="stat-card"><h3><?php echo $total_products; ?></h3><p>Total Products</p></div>
                    <div class="stat-card"><h3 style="color: var(--accent);"><?php echo "$" . e($total_value); ?></h3><p>Inventory Value</p></div>
                </div>

                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div class="chart-wrapper" style="flex: 1; min-width: 300px;">
                        <h3>Approved Revenue by Shop (Real-Time)</h3>
                        <div style="height: 350px; width: 100%;">
                            <?php if(empty($chart_data)): ?>
                                <div style="height: 100%; display: flex; align-items: center; justify-content: center; border: 1px dashed #444; color: #888;">
                                    No approved sales data available yet.
                                </div>
                            <?php else: ?>
                                <canvas id="revenueChart"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="chart-wrapper" style="flex: 1; min-width: 300px;">
                        <h3>Total Products per Shop</h3>
                        <div style="height: 350px; width: 100%;">
                            <?php if(empty($chart2_data)): ?>
                                <div style="height: 100%; display: flex; align-items: center; justify-content: center; border: 1px dashed #444; color: #888;">
                                    No product data available yet.
                                </div>
                            <?php else: ?>
                                <canvas id="productsChart"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <h3>Mall Inventory Overview</h3>
                    <table>
                        <tr>
                            <th>Product Name</th>
                            <th>Shop (Category)</th>
                            <th>Price</th>
                        </tr>
                        <?php
                            $inv_sql = "SELECT * FROM products";
                            $inv_result = mysqli_query($conn, $inv_sql);
                            
                            if ($inv_result && mysqli_num_rows($inv_result) > 0) {
                                while ($inv_row = mysqli_fetch_assoc($inv_result)) {
                                    echo "<tr>";
                                    echo "<td>" . e($inv_row['name']) . "</td>";
                                    echo "<td>" . e($inv_row['shop_name']) . " (" . e($inv_row['category']) . ")</td>";
                                    echo "<td>$" . e($inv_row['price']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No products found in the system.</td></tr>";
                            }
                        ?>
                    </table>
                </div>
            </div>

            <div id="users" class="view" style="display: none;">
                <h1>Manage Users</h1>
                <table>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr>
                    <?php
                        $c_sql = "SELECT * FROM users WHERE role='customer'";
                        $c_res = mysqli_query($conn, $c_sql);
                        
                        if ($c_res && mysqli_num_rows($c_res) > 0) {
                            while ($c = mysqli_fetch_assoc($c_res)) {
                                echo "<tr>";
                                echo "<td>" . (int)$c['id'] . "</td>";
                                echo "<td>" . e($c['fullname']) . "</td>";
                                echo "<td>" . e($c['email']) . "</td>";
                                echo "<td>" . post_button('admin.php', ['delete_user' => (int)$c['id']], 'Delete', "class='action-link bg-red' style='border:none; cursor:pointer;'", 'Are you sure you want to delete this user? All their orders will be removed.') . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No customers found.</td></tr>";
                        }
                    ?>
                </table>
            </div>

            <div id="managers" class="view" style="display: none;">
                <h1>Manage Managers & Shops</h1>
                <table>
                    <tr><th>ID</th><th>Manager Name</th><th>Shop Name</th><th>Email</th><th>Status</th><th>Action</th></tr>
                    <?php
                        $m_sql = "SELECT * FROM users WHERE role='manager'";
                        $m_res = mysqli_query($conn, $m_sql);
                        
                        if ($m_res && mysqli_num_rows($m_res) > 0) {
                            while ($m = mysqli_fetch_assoc($m_res)) {
                                echo "<tr>";
                                echo "<td>" . (int)$m['id'] . "</td>";
                                echo "<td>" . e($m['fullname']) . "</td>";
                                echo "<td><strong style='color: var(--accent);'>" . e($m['address']) . "</strong></td>";
                                echo "<td>" . e($m['email']) . "</td>";
                                if ((int)$m['approved'] === 1) {
                                    echo "<td style='color:#28a745; font-weight:bold;'>Approved</td>";
                                } else {
                                    echo "<td><form method='POST' style='display:inline;'>" . csrf_field() . "<input type='hidden' name='manager_id' value='" . (int)$m['id'] . "'><button type='submit' name='approve_manager' style='background:#28a745; padding:5px 10px;'>Approve</button></form></td>";
                                }
                                echo "<td>" . post_button('admin.php', ['delete_user' => (int)$m['id']], 'Delete', "class='action-link bg-red' style='border:none; cursor:pointer;'", 'Are you sure you want to delete this manager? All their products and orders will be removed.') . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No managers found.</td></tr>";
                        }
                    ?>
                </table>
            </div>
        </div>
    </div>

    <script>
        function show(id) {
            document.getElementById('home').style.display = 'none';
            document.getElementById('users').style.display = 'none';
            document.getElementById('managers').style.display = 'none';
            document.getElementById(id).style.display = 'block';
        }
        
        window.onload = function() {
            let urlParams = new URLSearchParams(window.location.search);
            let tab = urlParams.get('tab');
            if (tab) {
                show(tab);
            }
        }

        <?php if(!empty($chart_data)): ?>
            let ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chart_labels); ?>,
                    datasets: [{
                        label: 'Total Real Revenue ($)',
                        data: <?php echo json_encode($chart_data); ?>,
                        backgroundColor: 'rgba(139, 0, 0, 0.7)',
                        borderColor: '#ff4d4d',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#e0e0e0' },
                            grid: { color: '#333' }
                        },
                        x: {
                            ticks: { color: '#e0e0e0', font: { weight: 'bold' } },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { labels: { color: '#e0e0e0' } }
                    }
                }
            });
        <?php endif; ?>

        <?php if(!empty($chart2_data)): ?>
            let ctx2 = document.getElementById('productsChart').getContext('2d');
            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($chart2_labels); ?>,
                    datasets: [{
                        label: 'Number of Products',
                        data: <?php echo json_encode($chart2_data); ?>,
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderColor: '#28a745',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#e0e0e0', stepSize: 1 },
                            grid: { color: '#333' }
                        },
                        x: {
                            ticks: { color: '#e0e0e0', font: { weight: 'bold' } },
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { labels: { color: '#e0e0e0' } }
                    }
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>