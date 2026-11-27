<?php
include 'connect.php';
csrf_check();

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: index.php?error=login");
    exit();
}

$user = db_one($conn, "SELECT * FROM users WHERE email=?", 's', [$email]);

if (!$user || !password_verify($password, $user['password'])) {
    header("Location: index.php?error=login");
    exit();
}

if ($user['role'] === 'manager' && isset($user['approved']) && (int)$user['approved'] !== 1) {
    header("Location: index.php?pending=1");
    exit();
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['role'] = $user['role'];
$_SESSION['email'] = $user['email'];
$_SESSION['address'] = $user['address'];

if ($user['role'] === 'admin') {
    header("Location: admin.php");
} elseif ($user['role'] === 'manager') {
    header("Location: manager.php");
} else {
    header("Location: customer_side.php");
}
exit();
