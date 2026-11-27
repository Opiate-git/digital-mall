<?php
include 'connect.php';
require_role('manager');   // POST ise CSRF token'ı da kontrol eder

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manager.php?tab=products");
    exit();
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$shop = $_SESSION['address'];

if ($id && db_one($conn, "SELECT id FROM products WHERE id=? AND shop_name=?", 'is', [$id, $shop])) {
    // Ürünün resim dosyalarını da diskten sil (başka satır aynı dosyayı kullanmıyorsa)
    $paths = array_column(db_all($conn, "SELECT path FROM pictures WHERE product_id=?", 'i', [$id]), 'path');
    db_run($conn, "DELETE FROM pictures WHERE product_id=?", 'i', [$id]);
    db_run($conn, "DELETE FROM products WHERE id=? AND shop_name=?", 'is', [$id, $shop]);

    foreach ($paths as $p) {
        $still_used = db_one($conn, "SELECT id FROM pictures WHERE path=? LIMIT 1", 's', [$p]);
        if (!$still_used && strpos($p, './upload/') === 0 && is_file($p)) {
            @unlink($p);
        }
    }
}

header("Location: manager.php?tab=products");
exit();
