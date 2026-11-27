<?php
include 'connect.php';
csrf_check();

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$address = trim($_POST['address'] ?? '');

if ($fullname === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    header("Location: index.php?error=signup");
    exit();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    db_run($conn,
        "INSERT INTO users (fullname, email, password, role, address) VALUES (?, ?, ?, 'customer', ?)",
        'ssss', [$fullname, $email, $hashed_password, $address]);
} catch (mysqli_sql_exception $ex) {
    if ($ex->getCode() === 1062) {             // e-posta zaten kayıtlı
        header("Location: index.php?error=exists");
        exit();
    }
    throw $ex;
}

header("Location: index.php?signup=success");
exit();
