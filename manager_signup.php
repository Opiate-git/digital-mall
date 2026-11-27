<?php
include 'connect.php';
csrf_check();

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$shop = trim($_POST['address'] ?? '');   // form alanı "Shop Name"

if ($fullname === '' || $shop === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    header("Location: index.php?error=signup");
    exit();
}

// Aynı isimde mağaza açılırsa o mağazanın paneline erişilir; bu yüzden isim benzersiz olmalı.
if (shop_name_taken($conn, $shop)) {
    header("Location: index.php?error=shop");
    exit();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // approved=0: admin onaylayana kadar giriş yapamaz (migration_p0.sql gerekli)
    db_run($conn,
        "INSERT INTO users (fullname, email, password, role, address, approved) VALUES (?, ?, ?, 'manager', ?, 0)",
        'ssss', [$fullname, $email, $hashed_password, $shop]);
} catch (mysqli_sql_exception $ex) {
    if ($ex->getCode() === 1062) {
        header("Location: index.php?error=exists");
        exit();
    }
    throw $ex;
}

header("Location: index.php?pending=1");
exit();
