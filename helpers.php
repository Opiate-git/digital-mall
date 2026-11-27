<?php
// Ortak yardımcılar: HTML kaçışlama, prepared statement, rol kontrolü, resim yükleme.

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Prepared statement çalıştırır. $types: "s", "i", "d" ... ; $params: değerler.
function db_run($conn, $sql, $types = '', array $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($types !== '') {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    return $stmt;
}

function db_all($conn, $sql, $types = '', array $params = []) {
    $res = mysqli_stmt_get_result(db_run($conn, $sql, $types, $params));
    return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
}

function db_one($conn, $sql, $types = '', array $params = []) {
    $rows = db_all($conn, $sql, $types, $params);
    return $rows ? $rows[0] : null;
}

function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function require_role($role) {
    start_secure_session();
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== $role) {
        header('Location: index.php');
        exit();
    }
    // Yetkili sayfalardaki her POST otomatik CSRF kontrolünden geçer
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_check();
        demo_guard();
    }
}

// ---- Public demo accounts ----
// Shared demo logins must not be locked or vandalised: only these POST actions are allowed.
const DEMO_EMAILS = ['customer@demo.com', 'manager@demo.com'];
const DEMO_ALLOWED_POST = ['approve_order', 'reject_order', 'set_target', 'checkout_data'];

function is_demo_user() {
    return in_array(strtolower($_SESSION['email'] ?? ''), DEMO_EMAILS, true);
}

function demo_guard() {
    if (!is_demo_user()) return;
    foreach (DEMO_ALLOWED_POST as $key) {
        if (isset($_POST[$key])) return;
    }
    http_response_code(403);
    echo '<!DOCTYPE html><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<link rel="stylesheet" href="style.css"><div class="container" style="text-align:center;">'
       . '<h2>Demo mode</h2><p>This action is disabled for the shared demo account, so the demo stays usable for everyone.</p>'
       . '<p>Create your own customer account to try everything.</p>'
       . '<p><a class="nav-link" href="javascript:history.back()">&larr; Back</a></p></div>';
    exit();
}

// ---- CSRF ----
function csrf_token() {
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

// POST isteğini ve token'ı doğrular; geçersizse 403 ile durur.
function csrf_check() {
    start_secure_session();
    $sent = $_POST['csrf_token'] ?? '';
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['csrf_token'])
        || !is_string($sent) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(403);
        echo 'Geçersiz istek (oturum süresi dolmuş olabilir). Sayfayı yenileyip tekrar deneyin.';
        exit();
    }
}

// Küçük POST butonu (silme, çıkış vb. için GET linki yerine)
function post_button($action, array $fields, $label, $attrs = '', $confirm = '') {
    $html = '<form method="POST" action="' . e($action) . '" style="display:inline;"'
          . ($confirm !== '' ? ' onsubmit="return confirm(' . e(json_encode($confirm)) . ');"' : '') . '>'
          . csrf_field();
    foreach ($fields as $k => $v) {
        $html .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }
    return $html . '<button type="submit" ' . $attrs . '>' . e($label) . '</button></form>';
}

// Yüklenen resmi doğrular ve rastgele isimle kaydeder. Başarısızsa null döner.
// $basename verilirse (ör. "logo_shop") o isim kullanılır.
function save_uploaded_image($tmp, $error, $size, $basename = null) {
    $allowed = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];
    if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($tmp)) return null;
    if ($size <= 0 || $size > 5 * 1024 * 1024) return null;       // en fazla 5 MB
    $info = @getimagesize($tmp);
    if ($info === false || !isset($allowed[$info[2]])) return null;

    $name = ($basename ?? bin2hex(random_bytes(16))) . '.' . $allowed[$info[2]];
    $dest = './upload/' . $name;
    return move_uploaded_file($tmp, $dest) ? $dest : null;
}

// Mağaza logosunun dosya adı tabanı (upload/logo_<güvenli_ad>.<uzantı>)
function shop_logo_base($shop) {
    return 'logo_' . preg_replace('/[^A-Za-z0-9]/', '', $shop);
}

// Mağazanın logosunu bulur; yoksa varsayılan logo.png
function shop_logo($shop) {
    $files = glob('upload/' . shop_logo_base($shop) . '.*') ?: [];
    return $files ? $files[0] : 'logo.png';
}

// pictures satırlarını product_id => [path, ...] olarak tek sorguda getirir
function pictures_by_product($conn, array $product_ids) {
    $map = [];
    $ids = array_values(array_filter(array_map('intval', $product_ids)));
    if (!$ids) return $map;
    $in = implode(',', array_fill(0, count($ids), '?'));
    foreach (db_all($conn, "SELECT product_id, path FROM pictures WHERE product_id IN ($in) ORDER BY id",
                    str_repeat('i', count($ids)), $ids) as $row) {
        $map[(int)$row['product_id']][] = $row['path'];
    }
    return $map;
}

function shop_name_taken($conn, $shop, $exclude_user_id = 0) {
    return db_one($conn,
        "SELECT id FROM users WHERE role='manager' AND address=? AND id<>?",
        'si', [$shop, $exclude_user_id]) !== null;
}
