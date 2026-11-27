<?php
require_once __DIR__ . '/helpers.php';
start_secure_session();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
