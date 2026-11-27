<?php
// DB ayarları. Canlıda ortam değişkenlerinden (DB_HOST, DB_USER, DB_PASS, DB_NAME) okunur.
// Ortam değişkeni ayarlanamıyorsa bu klasörün DIŞINA bir config.local.php koyup
// aynı anahtarları return eden bir dizi döndürebilirsin (bkz. aşağıdaki yol).
$local = __DIR__ . '/../config.local.php';
if (!is_file($local)) $local = __DIR__ . '/config.local.php';   // fallback if host forbids files outside webroot
$cfg = is_file($local) ? (require $local) : [];

define('DB_HOST', getenv('DB_HOST') ?: ($cfg['DB_HOST'] ?? 'localhost'));
define('DB_USER', getenv('DB_USER') ?: ($cfg['DB_USER'] ?? 'root'));   // yerel XAMPP varsayılanı
define('DB_PASS', getenv('DB_PASS') ?: ($cfg['DB_PASS'] ?? ''));       // canlıda MUTLAKA değiştir
define('DB_NAME', getenv('DB_NAME') ?: ($cfg['DB_NAME'] ?? 'products_db'));
