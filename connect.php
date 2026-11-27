<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// Hatalar exception olarak fırlar; kullanıcıya detay gösterilmez, sadece loglanır.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
set_exception_handler(function ($ex) {
    // Sadece mesaj+konum; stack trace argümanlarında DB şifresi olabilir.
    error_log('[digital-mall] ' . get_class($ex) . ': ' . $ex->getMessage() . ' @ ' . $ex->getFile() . ':' . $ex->getLine());
    http_response_code(500);
    echo 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
    exit();
});

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
mysqli_set_charset($conn, 'utf8mb4');
