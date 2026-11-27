<?php
include 'connect.php';
require_role('manager');   // POST ise CSRF token'ı da kontrol eder

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manager.php?tab=products");
    exit();
}

$cat_name = $_POST['name'] ?? '';
$shop = $_SESSION['address'];

if ($cat_name !== '') {
    // Kategoride ürün varsa silme (yoksa ürünler var olmayan bir kategoride kalırdı)
    $used = db_one($conn, "SELECT id FROM products WHERE category=? AND shop_name=? LIMIT 1", 'ss', [$cat_name, $shop]);
    if ($used) {
        header("Location: manager.php?tab=products&caterror=1");
        exit();
    }
    // Sadece kendi mağazasının kategorisi
    db_run($conn, "DELETE FROM categories WHERE name=? AND shop_name=?", 'ss', [$cat_name, $shop]);
}

header("Location: manager.php?tab=products");
exit();
